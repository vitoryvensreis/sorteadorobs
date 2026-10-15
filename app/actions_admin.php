<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
function admin_participants(): array {
 $q=trim(substr((string)($_GET['q']??''),0,120));
 $page=max(1,min(10000,(int)($_GET['page']??1)));
 $where='';$args=[];
 if($q!=='') {
  $like='%'.$q.'%';
  $cpf=digits($q);
  $where=' WHERE name LIKE ? OR CAST(id AS CHAR) LIKE ? OR phone LIKE ? OR cpf_hash=?';
  $args=[$like,$like,$cpf!==''?'%'.$cpf.'%':'__NO_PHONE_MATCH__',strlen($cpf)===11?cpf_hash($cpf):str_repeat('0',64)];
 }
 $pdo=db();
 $stmt=$pdo->prepare('SELECT COUNT(*) FROM participants'.$where);
 $stmt->execute($args);$total=(int)$stmt->fetchColumn();
 $offset=($page-1)*50;
 $stmt=$pdo->prepare('SELECT id,name,phone,cpf_ciphertext,won_at,created_at FROM participants'.$where.' ORDER BY id DESC LIMIT 50 OFFSET '.$offset);
 $stmt->execute($args);
 $people=array_map(static function(array $p):array {
  return ['id'=>(int)$p['id'],'name'=>$p['name'],'phone'=>$p['phone'],
   'cpf'=>masked_cpf($p['cpf_ciphertext']),'won'=>$p['won_at']!==null,
   'registered_at'=>$p['created_at']];
 },$stmt->fetchAll());
 return ['items'=>$people,'total'=>$total,'page'=>$page,'pages'=>max(1,(int)ceil($total/50))];
}
function admin_history(): array {
 $stmt=db()->query('SELECT d.id,p.id AS number,p.name,d.drawn_at FROM draws d
 JOIN participants p ON p.id=d.participant_id ORDER BY d.id DESC LIMIT 30');
 return ['items'=>array_map(static fn($r)=>['id'=>(int)$r['id'],
   'number'=>(int)$r['number'],'name'=>$r['name'],'at'=>$r['drawn_at']],$stmt->fetchAll())];
}
