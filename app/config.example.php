<?php
declare(strict_types=1);
// Copie para app/config.php, que nao deve ser versionado.
return [
 'db_host' => '127.0.0.1',
 'db_port' => '3306',
 'db_name' => 'sorteadorobs',
 'db_user' => 'sorteadorobs',
 'db_pass' => 'TROQUE_A_SENHA',
 // Gere: php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
 'app_key' => 'COLE_64_CARACTERES_HEXADECIMAIS_AQUI',
];
