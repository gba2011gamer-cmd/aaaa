<?php
/**
 * Assinatura / Ativação do Passe VIP Premium
 * Desbloqueia permanentemente todas as 4 operações (+, -, *, /)
 * Registra auditoria completa no banco MySQL via PDO
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/toast.php';
require_once __DIR__ . '/../includes/logger.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Faça login para assinar o Passe Premium.']);
    exit;
}

$user = get_logged_user();
$actionType = $_POST['action_type'] ?? 'activate'; // 'activate' ou 'toggle'

try {
    $db = getDB();

    $stmt = $db->prepare("SELECT is_premium, coins FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $user['id']]);
    $row = $stmt->fetch();

    $currentStatus = (int)$row['is_premium'];
    $newStatus = ($actionType === 'toggle') ? ($currentStatus === 1 ? 0 : 1) : 1;

    $allOps = ['+', '-', '*', '/'];
    $unlockedJson = ($newStatus === 1) ? json_encode($allOps) : json_encode(['+']);
    $bonusCoins = ($newStatus === 1 && $currentStatus === 0) ? 500 : 0;
    $newCoins = (int)$row['coins'] + $bonusCoins;

    $stmtUpdate = $db->prepare("
        UPDATE users 
        SET is_premium = :prem, 
            unlocked_operations = :unlocked, 
            coins = :coins 
        WHERE id = :id
    ");

    $stmtUpdate->execute([
        ':prem'     => $newStatus,
        ':unlocked' => $unlockedJson,
        ':coins'    => $newCoins,
        ':id'       => $user['id']
    ]);

    // REGRA DE NEGÓCIO CORE: Auditoria
    log_audit($db, $user['id'], 'PREMIUM_ALTERADO', [
        'novo_status_premium' => ($newStatus === 1 ? 'ATIVO_VIP' : 'CANCELADO_GRATIS'),
        'operacoes_liberadas' => ($newStatus === 1 ? $allOps : ['+']),
        'bonus_moedas'        => $bonusCoins
    ], $user['name']);

    // Atualiza sessão
    $_SESSION['user']['is_premium'] = ($newStatus === 1);
    $_SESSION['user']['unlocked_operations'] = ($newStatus === 1) ? $allOps : ['+'];
    $_SESSION['user']['coins'] = $newCoins;

    $msg = ($newStatus === 1)
        ? "Parabéns! Passe VIP Premium ativado! Todas as 4 operações (+, -, *, /) estão liberadas permanentemente!"
        : "Passe VIP desativado. Modo padrão gratuito restaurado.";

    echo json_encode([
        'success'    => true,
        'message'    => $msg,
        'is_premium' => ($newStatus === 1),
        'unlocked'   => ($newStatus === 1 ? $allOps : ['+']),
        'coins'      => $newCoins
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao processar assinatura VIP: ' . $e->getMessage()]);
    exit;
}
