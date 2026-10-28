<?php
/**
 * Página Principal: Calculadora da Sorte
 * Controlador de Acesso e Apresentação
 */

require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/includes/auth_check.php';

// Exige autenticação
require_login();

$pageTitle = 'Calculadora da Sorte';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/views/calculator_view.php';
require_once __DIR__ . '/includes/footer.php';
