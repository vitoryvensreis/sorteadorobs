<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1"><meta name="csrf-token" content="<?= e(csrf()) ?>">
<title>Sorteador OBS • Painel</title><link rel="stylesheet" href="assets/style.css">
<script src="assets/admin.js" defer></script></head><body class="app">
<header class="topbar"><a class="brand" href="index.php"><span class="brand-mark">✳</span> SORTEADOR<span class="brand-accent">OBS</span></a>
<nav><a href="index.php">Cadastro</a><a href="overlay.php">Tela verde ↗</a>
<?php if(is_admin()): ?><button id="logout-btn" class="text-button">Sair</button><?php endif; ?></nav></header>
<?php if(!is_admin()): ?>
<main class="auth-wrap"><section class="register-panel auth-panel"><span class="step">AREA RESTRITA</span><h1>Acesso ao painel</h1><p>Entre para controlar o sorteio ao vivo.</p>
<form id="login-form"><label for="username">Usuario</label><input id="username" name="username" autocomplete="username" required>
<label for="password">Senha</label><input id="password" name="password" type="password" autocomplete="current-password" required>
<button class="btn btn-primary" type="submit">ENTRAR NO PAINEL ↗</button><p id="admin-message" role="status"></p></form></section></main>
<?php else: ?>
<main class="dashboard"><div class="dashboard-heading"><div><div class="eyebrow"><span class="live-dot"></span> CENTRAL DE CONTROLE</div><h1>Controle do <span>sorteio.</span></h1><p>Cadastros, sorteios e visualizacao ao vivo em um so lugar.</p></div>
<div class="heading-links"><a class="btn btn-outline" href="overlay.php" target="_blank">ABRIR CONTROLE VISUAL ↗</a><a class="btn btn-outline" href="overlay.php?obs=1" target="_blank">ABRIR FONTE OBS ↗</a></div></div>
<section class="stats-row"><div class="stat-tile"><span>Inscritos</span><strong id="count-total">—</strong></div><div class="stat-tile"><span>Disponiveis</span><strong id="count-eligible">—</strong></div><div class="stat-tile"><span>Ja sorteados</span><strong id="count-winners">—</strong></div></section>
<div class="admin-grid"><section class="glass-card action-card"><div class="section-tag">CONTROLE PRINCIPAL</div><h2>Hora de fazer historia.</h2><p>Cada clique escolhe uma pessoa elegivel. O resultado aparece automaticamente na tela do OBS.</p>
<button class="btn btn-primary giant" id="draw-btn">✳ SORTEAR AGORA</button>
<div id="winner-summary" class="winner-summary">Nenhum vencedor sorteado ainda.</div><p id="admin-message" role="status"></p>
<div class="danger-zone"><h3>Gerenciamento</h3><button class="btn btn-outline" id="reset-btn">↻ Reiniciar vencedores</button><button class="btn btn-danger" id="clear-btn">Apagar todos os cadastros</button>
<small>Reiniciar mantem inscritos e permite sortea-los novamente. Apagar exclui inscricoes e historico.</small></div></section>
<section class="glass-card"><div class="section-tag">ULTIMAS REVELACOES</div><h2>Historico de ganhadores</h2><div id="history-list" class="history-list">Carregando...</div></section></div>
<section class="glass-card participants-card"><div class="participants-head"><div><div class="section-tag">CONSULTA DE INSCRICOES</div><h2>Participantes</h2><p>Pesquise por nome, numero de inscricao, telefone ou CPF completo.</p></div>
<input id="search" aria-label="Buscar participantes" placeholder="Buscar inscricao, nome, CPF, telefone..."></div>
<div class="table-wrap"><table><thead><tr><th>Nº</th><th>Nome</th><th>Telefone</th><th>CPF protegido</th><th>Status</th></tr></thead><tbody id="participants-rows"></tbody></table></div>
<div class="pager"><button class="btn btn-outline" id="prev-page">← Anterior</button><span id="page-info">Pagina 1</span><button class="btn btn-outline" id="next-page">Proxima →</button></div>
</section></main>
<?php endif; ?>
<footer class="app-footer">SORTEADOR OBS <span>•</span> Painel seguro</footer></body></html>
