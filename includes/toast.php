<?php
/**
 * Gerenciador de Mensagens Toast (Notificações Flutuantes)
 * Integração PHP Session com Frontend Tailwind
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Adiciona um Toast na sessão do usuário
 * @param string $type 'success' | 'error' | 'warning' | 'info'
 * @param string $message Mensagem clara para o usuário
 */
function set_toast($type, $message) {
    if (!isset($_SESSION['flash_toasts']) || !is_array($_SESSION['flash_toasts'])) {
        $_SESSION['flash_toasts'] = [];
    }
    $_SESSION['flash_toasts'][] = [
        'id'      => uniqid('toast_'),
        'type'    => $type,
        'message' => $message,
        'time'    => date('H:i:s')
    ];
}

/**
 * Recupera e limpa os Toasts da sessão
 * @return array
 */
function get_and_clear_toasts() {
    $toasts = $_SESSION['flash_toasts'] ?? [];
    unset($_SESSION['flash_toasts']);
    return $toasts;
}

/**
 * Imprime script JS para inicializar toasts pendentes da sessão
 */
function render_flash_toasts_script() {
    $toasts = get_and_clear_toasts();
    if (empty($toasts)) {
        return '';
    }
    $json = json_encode($toasts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    return "<script>document.addEventListener('DOMContentLoaded', function() { if (window.ToastManager) { window.ToastManager.loadFromSession($json); } });</script>";
}
