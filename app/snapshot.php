<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
function snapshot(): array {
 $pdo=db();
 $c=$pdo->query('SELECT COUNT(*) AS total,COALESCE(SUM(won_at IS NULL),0) AS eligible,COALESCE(SUM(won_at IS NOT NULL),0) AS winners FROM participants')->fetch();
 $rows=$pdo->query('SELECT id,name,(won_at IS NOT NULL) AS won FROM participants ORDER BY id ASC')->fetchAll();
 $s=$pdo->query('SELECT s.draw_seq,s.reset_seq,s.last_winner_id,p.name AS winner_name,CASE WHEN s.last_draw_at IS NULL THEN NULL ELSE TIMESTAMPDIFF(MICROSECOND,s.last_draw_at,NOW(3))/1000 END AS draw_age_ms FROM lottery_state s LEFT JOIN participants p ON p.id=s.last_winner_id WHERE s.id=1')->fetch();
 return [
  'counts'=>['total'=>(int)$c['total'],'eligible'=>(int)$c['eligible'],'winners'=>(int)$c['winners']],
  'participants'=>array_map(static fn($r)=>['id'=>(int)$r['id'],'name'=>$r['name'],'won'=>(bool)$r['won']],$rows),
  'state'=>[
   'draw_seq'=>(int)$s['draw_seq'],'reset_seq'=>(int)$s['reset_seq'],
   'draw_age_ms'=>$s['draw_age_ms']===null?null:(float)$s['draw_age_ms'],
   'winner'=>$s['last_winner_id']===null?null:['id'=>(int)$s['last_winner_id'],'name'=>$s['winner_name']]
  ]
 ];
}
