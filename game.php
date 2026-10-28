<?php
/**
 * Página do Jogo: Desafio da Sorte
 * Controlador de Acesso e Apresentação
 */

require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/includes/auth_check.php';

// Exige autenticação
require_login();

$pageTitle = 'Desafio da Sorte - Jogo Matemático';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/views/game_view.php';
require_once __DIR__ . '/includes/footer.php';
