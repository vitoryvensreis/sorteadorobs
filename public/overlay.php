<?php
require __DIR__ . '/../app/bootstrap.php';
$controls=is_admin()&&!isset($_GET['obs']);
?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="<?= e(csrf()) ?>">
<title>Sorteador OBS • Chroma</title>
<link rel="stylesheet" href="assets/style.css"><script defer src="assets/overlay.js"></script></head>
<body class="overlay-body"><main class="overlay" id="overlay-app" data-control="<?= $controls?'1':'0' ?>">
<header class="overlay-top"><div class="overlay-brand">✳ SORTEIO <b>AO VIVO</b></div><div class="onair">● AO VIVO</div></header>
<section class="overlay-stage">
<div id="idle-state" class="idle-state"><p class="stage-kicker">A SORTE PODE SER SUA</p><h1>QUEM SERÁ O <em>PRÓXIMO?</em></h1><p class="stage-sub">Acompanhe o sorteio em tempo real</p></div>
<div id="rolling-state" class="rolling-state hidden"><p class="stage-kicker">SORTEANDO AGORA...</p><div class="slot-frame" id="rolling-name">BOA SORTE!</div><div class="progress-line"><i id="progress-indicator"></i></div></div>
<div id="winner-state" class="winner-state hidden"><p class="winner-kicker">✳ TEMOS UM GANHADOR! ✳</p><div class="winner-frame"><small>NÚMERO <span id="winner-number">0000</span></small><h2 id="winner-name"></h2></div><p class="winner-sub">PARABÉNS! VOCÊ É O GRANDE VENCEDOR!</p></div>
<div id="confetti-layer" class="confetti-layer"></div>
</section>
<footer class="overlay-bottom"><div class="overlay-list-heading"><span>✦ PARTICIPANTES CADASTRADOS</span><strong id="overlay-counter">0 INSCRITOS</strong></div><div class="ticker-viewport"><div id="ticker-track" class="ticker-track"></div></div></footer>
<?php if($controls): ?><div class="overlay-controls"><button id="overlay-draw">✳ SORTEAR AGORA</button><span id="overlay-message"></span></div><?php endif; ?>
</main></body></html>
