<?php
/**
 * Ação de Logout do Usuário com Auditoria
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/toast.php';
require_once __DIR__ . '/../includes/logger.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (is_logged_in()) {
    $user = get_logged_user();
    try {
        $db = getDB();
        log_audit($db, $user['id'], 'LOGOUT', 'Usuário encerrou sua sessão.', $user['name']);
    } catch (Exception $e) {
        // Ignora silenciosamente no encerramento
    }
}

// Limpa sessão
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Inicia nova sessão limpa apenas para o Toast
session_start();
set_toast('info', 'Você saiu da sua conta. Até logo!');
header('Location: ../login.php');
exit;
