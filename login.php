<?php
/**
 * Página de Autenticação / Login
 * Controlador de Acesso e Apresentação
 */

require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/includes/auth_check.php';

// Se já autenticado, redireciona para a calculadora
if (is_logged_in()) {
    header('Location: calculator.php');
    exit;
}

$pageTitle = 'Entrar';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/views/login_view.php';
require_once __DIR__ . '/includes/footer.php';
