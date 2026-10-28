<?php
/**
 * Página de Auditoria e Histórico Completo
 * Controlador de Acesso e Apresentação
 */

require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/includes/auth_check.php';

// Exige autenticação
require_login();

$pageTitle = 'Histórico & Auditoria de Alterações';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/views/history_view.php';
require_once __DIR__ . '/includes/footer.php';
