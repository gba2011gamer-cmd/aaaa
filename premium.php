<?php
/**
 * Página da Loja VIP & Desbloqueio por Anúncios
 * Controlador de Acesso e Apresentação
 */

require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/includes/auth_check.php';

// Exige autenticação
require_login();

$pageTitle = 'Loja VIP & Desbloqueio de Operações';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/views/premium_view.php';
require_once __DIR__ . '/includes/footer.php';
