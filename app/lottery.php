<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
function draw_winner(): array {
 $pdo=db(); $pdo->beginTransaction();
 try {
  $pdo->query('SELECT id FROM lottery_state WHERE id=1 FOR UPDATE')->fetch();
  $eligible=(int)$pdo->query('SELECT COUNT(*) FROM participants WHERE won_at IS NULL')->fetchColumn();
  $offset=$eligible>0?random_int(0,$eligible-1):0;
  $winner=$eligible>0?$pdo->query('SELECT id,name FROM participants WHERE won_at IS NULL ORDER BY id LIMIT 1 OFFSET '.$offset.' FOR UPDATE')->fetch():false;
  if (!$winner) { $pdo->rollBack(); json_out(['error'=>'Nao ha participantes disponiveis.'],409); }
  $stmt=$pdo->prepare('UPDATE participants SET won_at=NOW(3) WHERE id=? AND won_at IS NULL');
  $stmt->execute([$winner['id']]);
  if ($stmt->rowCount()!==1) throw new RuntimeException('Conflito de sorteio.');
  $pdo->prepare('INSERT INTO draws(participant_id,drawn_at) VALUES (?,NOW(3))')->execute([$winner['id']]);
  $pdo->prepare('UPDATE lottery_state SET last_winner_id=?,last_draw_at=NOW(3),draw_seq=draw_seq+1 WHERE id=1')->execute([$winner['id']]);
  $pdo->commit();
  return ['id'=>(int)$winner['id'],'name'=>$winner['name']];
 } catch(Throwable $e) { if($pdo->inTransaction()) $pdo->rollBack(); throw $e; }
}
function reset_lottery(bool $clear): void {
 $pdo=db(); $pdo->beginTransaction();
 try {
  $pdo->query('SELECT id FROM lottery_state WHERE id=1 FOR UPDATE')->fetch();
  $pdo->exec('DELETE FROM draws');
  if($clear) $pdo->exec('DELETE FROM participants');
  else $pdo->exec('UPDATE participants SET won_at=NULL');
  $pdo->exec('UPDATE lottery_state SET last_winner_id=NULL,last_draw_at=NULL,reset_seq=reset_seq+1,draw_seq=draw_seq+1 WHERE id=1');
  $pdo->commit();
 } catch(Throwable $e) {if($pdo->inTransaction())$pdo->rollBack();throw $e;}
}
