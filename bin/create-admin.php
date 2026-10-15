<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
require __DIR__ . '/../app/bootstrap.php';
$username=trim((string)($argv[1]??'admin'));
$password=(string)(getenv('ADMIN_PASSWORD')?:'');
if(!preg_match('/^[a-zA-Z0-9._-]{3,80}$/',$username)||strlen($password)<12){
 fwrite(STDERR,"Uso: ADMIN_PASSWORD='senha-de-12-ou-mais-caracteres' php bin/create-admin.php admin\n");
 exit(1);
}
$stmt=db()->prepare('INSERT INTO admins(username,password_hash) VALUES (?,?)
 ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash)');
$stmt->execute([$username,password_hash($password,PASSWORD_DEFAULT)]);
echo "Administrador criado/atualizado: {$username}\n";
