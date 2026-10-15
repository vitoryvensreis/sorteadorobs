<?php
declare(strict_types=1);
if (!is_file(__DIR__ . '/config.php')) {
    http_response_code(503);
    exit('Instalacao pendente: configure app/config.php conforme o README.');
}
$config = require __DIR__ . '/config.php';
if (!is_array($config) || !preg_match('/^[a-f0-9]{64}$/i', (string)($config['app_key'] ?? ''))) {
    http_response_code(503);
    exit('Configuracao incompleta: gere uma APP KEY valida.');
}
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; connect-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'self'");
session_name('sorteadorobs_sid');
session_set_cookie_params([
    'lifetime'=>0,'path'=>'/',
    'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly'=>true,'samesite'=>'Lax',
]);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function settings(string $key): string {
    global $config;
    return (string)($config[$key] ?? '');
}
function db(): PDO {
    static $pdo;
    if (!$pdo) {
        $dsn = 'mysql:host=' . settings('db_host') . ';port=' . settings('db_port') .
            ';dbname=' . settings('db_name') . ';charset=utf8mb4';
        $pdo = new PDO($dsn, settings('db_user'), settings('db_pass'), [
            PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES=>false,
        ]);
    }
    return $pdo;
}
function csrf(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function require_csrf(): void {
    $provided = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($provided) || !hash_equals(csrf(), $provided)) {
        json_out(['error'=>'Sessao expirada. Atualize a pagina.'], 419);
    }
}
function is_admin(): bool { return isset($_SESSION['admin_id']); }
function require_admin(): void {
    if (!is_admin()) json_out(['error'=>'Acesso restrito ao administrador.'], 401);
}
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function json_out(array $data, int $status=200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, private, max-age=0');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
function post_data(): array {
    $data = json_decode(file_get_contents('php://input') ?: '{}', true);
    if (!is_array($data)) json_out(['error'=>'Dados invalidos.'], 400);
    return $data;
}
function digits(string $s): string { return preg_replace('/\D+/', '', $s) ?? ''; }
function valid_cpf(string $cpf): bool {
    if (!preg_match('/^\d{11}$/', $cpf) || preg_match('/^(\d)\1{10}$/', $cpf)) return false;
    for ($size=9; $size<=10; $size++) {
        $sum=0;
        for ($i=0;$i<$size;$i++) $sum+=(int)$cpf[$i]*($size+1-$i);
        $digit=($sum*10)%11;
        if ($digit===10) $digit=0;
        if ($digit!==(int)$cpf[$size]) return false;
    }
    return true;
}
function cpf_hash(string $cpf): string { return hash_hmac('sha256', $cpf, hex2bin(settings('app_key'))); }
function encrypt_cpf(string $cpf): string {
    $iv=random_bytes(12);
    $cipher=openssl_encrypt($cpf,'aes-256-gcm',hex2bin(settings('app_key')),OPENSSL_RAW_DATA,$iv,$tag);
    if ($cipher===false) throw new RuntimeException('Erro de criptografia.');
    return base64_encode($iv.$tag.$cipher);
}
function masked_cpf(string $encrypted): string {
    $raw=base64_decode($encrypted,true);
    if (!$raw || strlen($raw)<28) return '***.***.***-**';
    $plain=openssl_decrypt(substr($raw,28),'aes-256-gcm',hex2bin(settings('app_key')),
        OPENSSL_RAW_DATA,substr($raw,0,12),substr($raw,12,16));
    if (!$plain || strlen($plain)!==11) return '***.***.***-**';
    return '***.***.'.substr($plain,6,3).'-'.substr($plain,9,2);
}
function rate_limit(string $action,int $limit,int $window): void {
    $windowId=intdiv(time(),$window);
    $bucket=hash('sha256',$action.':'.($_SERVER['REMOTE_ADDR'] ?? 'unknown').':'.$windowId);
    $expires=gmdate('Y-m-d H:i:s',($windowId+1)*$window);
    $stmt=db()->prepare('INSERT INTO rate_limits(bucket,hits,expires_at) VALUES (?,1,?)
        ON DUPLICATE KEY UPDATE hits=hits+1');
    $stmt->execute([$bucket,$expires]);
    $stmt=db()->prepare('SELECT hits FROM rate_limits WHERE bucket=?');
    $stmt->execute([$bucket]);
    if ((int)$stmt->fetchColumn()>$limit) json_out(['error'=>'Muitas tentativas. Tente novamente mais tarde.'],429);
    if (random_int(1,100)===1) db()->exec('DELETE FROM rate_limits WHERE expires_at < NOW()');
}
