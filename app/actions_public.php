<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
function register_person(array $input): array {
 rate_limit('register',8,600);
 $name=trim(preg_replace('/\s+/u',' ',(string)($input['name']??''))??'');
 $phone=digits((string)($input['phone']??''));
 $cpf=digits((string)($input['cpf']??''));
 if(!preg_match("/^[\p{L}\p{M} .'-]{3,120}$/u",$name)) json_out(['error'=>'Informe seu nome completo (3 a 120 caracteres).'],422);
 if(!preg_match('/^\d{10,11}$/',$phone)) json_out(['error'=>'Informe um telefone com DDD valido.'],422);
 if(!valid_cpf($cpf)) json_out(['error'=>'CPF invalido.'],422);
 if(($input['consent']??false)!==true) json_out(['error'=>'Confirme a autorizacao para participar.'],422);
 try {
  $stmt=db()->prepare('INSERT INTO participants (name,phone,cpf_hash,cpf_ciphertext,consented_at) VALUES (?,?,?,?,NOW())');
  $stmt->execute([$name,$phone,cpf_hash($cpf),encrypt_cpf($cpf)]);
 } catch(PDOException $e) {
  if(($e->errorInfo[1]??null)===1062) json_out(['error'=>'Este CPF ja esta cadastrado.'],409);
  throw $e;
 }
 return ['ok'=>true,'number'=>(int)db()->lastInsertId(),'name'=>$name];
}
function admin_login(array $input): array {
 rate_limit('login',15,900);
 $username=trim((string)($input['username']??''));
 $password=(string)($input['password']??'');
 $stmt=db()->prepare('SELECT id,password_hash FROM admins WHERE username=?');
 $stmt->execute([$username]);
 $admin=$stmt->fetch();
 if(!$admin || !password_verify($password,$admin['password_hash'])) {
  json_out(['error'=>'Usuario ou senha incorretos.'],401);
 }
 session_regenerate_id(true);
 $_SESSION['admin_id']=(int)$admin['id'];
 $_SESSION['csrf']=bin2hex(random_bytes(32));
 return ['ok'=>true];
}
