<?php
/**
 * Ponto de Entrada Principal (index.php)
 * Redireciona para Calculadora ou Tela de Login
 */

require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/includes/auth_check.php';

if (is_logged_in()) {
    header('Location: calculator.php');
} else {
    header('Location: login.php');
}
exit;
