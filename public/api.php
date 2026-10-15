<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/lottery.php';
require_once __DIR__ . '/../app/snapshot.php';
require_once __DIR__ . '/../app/actions_public.php';
require_once __DIR__ . '/../app/actions_admin.php';
header('Cache-Control: no-store');
$action=(string)($_GET['action']??'');
try {
 if($_SERVER['REQUEST_METHOD']==='GET') {
  if($action==='status') json_out(snapshot());
  require_admin();
  if($action==='participants') json_out(admin_participants());
  if($action==='history') json_out(admin_history());
  json_out(['error'=>'Rota nao encontrada.'],404);
 }
 if($_SERVER['REQUEST_METHOD']!=='POST') json_out(['error'=>'Metodo invalido.'],405);
 require_csrf();
 $input=post_data();
 if($action==='register') json_out(register_person($input),201);
 if($action==='login') json_out(admin_login($input));
 if($action==='logout') {
  require_admin();
  $_SESSION=[]; session_regenerate_id(true);
  json_out(['ok'=>true]);
 }
 require_admin();
 if($action==='draw') json_out(['winner'=>draw_winner()]);
 if($action==='reset') {reset_lottery(false);json_out(['ok'=>true]);}
 if($action==='clear') {
  if(($input['confirmation']??'')!=='APAGAR TUDO') json_out(['error'=>'Confirmacao invalida.'],400);
  reset_lottery(true); json_out(['ok'=>true]);
 }
 json_out(['error'=>'Rota nao encontrada.'],404);
} catch (Throwable $e) {
 error_log('[SorteadorOBS] '.$e->getMessage());
 json_out(['error'=>'Erro interno. Verifique os logs do servidor.'],500);
}
