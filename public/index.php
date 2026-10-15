<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
?><!doctype html>
<html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="<?= e(csrf()) ?>">
<meta name="description" content="Cadastre-se para participar do sorteio ao vivo.">
<title>Sorteador OBS • Inscricao</title>
<link rel="stylesheet" href="assets/style.css"><script src="assets/register.js" defer></script>
</head><body class="app">
<header class="topbar"><a class="brand" href="index.php"><span class="brand-mark">✳</span> SORTEADOR<span class="brand-accent">OBS</span></a>
<nav><a href="overlay.php?obs=1" target="_blank" rel="noopener">Ver transmissao</a><a href="admin.php">Administracao ↗</a></nav></header>
<main class="register-grid">
<section class="hero-copy"><div class="eyebrow"><span class="live-dot"></span> SORTEIO AO VIVO</div>
<h1>Sua chance de <span>brilhar</span> começa aqui.</h1>
<p>Participe do nosso sorteio ao vivo. Preencha seus dados e acompanhe a revelacao dos ganhadores na transmissao!</p>
<div class="hero-stats"><div><strong id="total-count">—</strong><small>Participantes inscritos</small></div><div><strong>100%</strong><small>Sorteio digital</small></div></div>
<div class="hero-orb orb-one"></div><div class="hero-orb orb-two"></div>
</section>
<section class="register-panel"><div class="panel-heading"><span class="step">01 / INSCRICAO</span><h2>Entre para a lista.</h2><p>Leva menos de um minuto.</p></div>
<form id="register-form" autocomplete="on">
<label for="name">Nome completo</label><input id="name" name="name" placeholder="Como voce se chama?" maxlength="120" autocomplete="name" required>
<label for="phone">Numero de telefone com DDD</label><input id="phone" name="phone" type="tel" inputmode="numeric" placeholder="(85) 99999-9999" maxlength="15" autocomplete="tel" required>
<label for="cpf">CPF</label><input id="cpf" name="cpf" type="text" inputmode="numeric" placeholder="000.000.000-00" maxlength="14" required>
<label class="consent"><input id="consent" type="checkbox" required><span>Autorizo o uso dos dados para este sorteio e a divulgacao do meu nome e numero de inscricao durante a transmissao. Meu CPF e telefone nao serao exibidos ao publico.</span></label>
<button class="btn btn-primary" type="submit" id="register-submit">QUERO PARTICIPAR <span>↗</span></button>
<p class="form-note">Um cadastro por CPF. Seus dados de identificacao nao aparecem no OBS.</p>
<p id="register-message" role="status" aria-live="polite"></p>
</form></section></main>
<footer class="app-footer">SORTEADOR OBS <span>•</span> Preparado para grandes momentos.</footer>
</body></html>
