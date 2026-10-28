<?php
/**
 * Gerenciamento e Verificação de Sessão de Autenticação
 * Projeto: Calculando a Sorte
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/toast.php';

/**
 * Verifica se há usuário logado na sessão atual
 * @return bool
 */
function is_logged_in() {
    return !empty($_SESSION['user']['id']);
}

/**
 * Retorna os dados do usuário autenticado
 * @return array|null
 */
function get_logged_user() {
    return $_SESSION['user'] ?? null;
}

/**
 * Exige login obrigatório para acesso a páginas protegidas
 */
function require_login() {
    if (!is_logged_in()) {
        set_toast('warning', 'Você precisa estar logado para acessar esta área.');
        header('Location: login.php');
        exit;
    }
}

/**
 * Atualiza os dados do usuário na sessão a partir do banco de dados
 * @param PDO $pdo
 */
function refresh_user_session($pdo) {
    if (!is_logged_in() || !$pdo) {
        return;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, name, email, is_premium, coins, score, ads_watched, current_drawn_op, unlocked_operations FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $_SESSION['user']['id']]);
        $fresh = $stmt->fetch();

        if ($fresh) {
            $unlocked = json_decode($fresh['unlocked_operations'], true);
            if (!is_array($unlocked)) {
                $unlocked = [$fresh['current_drawn_op'] ?: '+'];
            }

            // Se for premium, tem todas as 4 operações sempre liberadas
            if ((int)$fresh['is_premium'] === 1) {
                $unlocked = ['+', '-', '*', '/'];
            }

            $_SESSION['user'] = [
                'id'                  => (int)$fresh['id'],
                'name'                => $fresh['name'],
                'email'               => $fresh['email'],
                'is_premium'          => (int)$fresh['is_premium'] === 1,
                'coins'               => (int)$fresh['coins'],
                'score'               => (int)$fresh['score'],
                'ads_watched'         => (int)$fresh['ads_watched'],
                'current_drawn_op'    => $fresh['current_drawn_op'],
                'unlocked_operations' => $unlocked
            ];
        }
    } catch (Exception $e) {
        error_log("Erro ao recarregar sessão do usuário: " . $e->getMessage());
    }
}

/**
 * Retorna se o usuário logado possui status VIP Premium
 * @return bool
 */
function is_premium() {
    return !empty($_SESSION['user']['is_premium']);
}
