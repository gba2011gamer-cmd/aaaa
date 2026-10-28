<?php
/**
 * Página de Cadastro de Novo Usuário
 * Controlador de Acesso e Apresentação
 */

require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/includes/auth_check.php';

// Se já autenticado, redireciona para a calculadora
if (is_logged_in()) {
    header('Location: calculator.php');
    exit;
}

$pageTitle = 'Criar Conta';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/views/register_view.php';
require_once __DIR__ . '/includes/footer.php';
