<?php
/**
 * Desbloqueio de Operação via Recompensa de Anúncio
 * Atualiza operações desbloqueadas e registra auditoria completa
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/toast.php';
require_once __DIR__ . '/../includes/logger.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Faça login para desbloquear operações.']);
    exit;
}

$user = get_logged_user();
$operation = trim($_POST['operation'] ?? '');
$validOps = ['+', '-', '*', '/'];

if (!in_array($operation, $validOps)) {
    echo json_encode(['success' => false, 'message' => 'Operação matemática inválida informada.']);
    exit;
}

try {
    $db = getDB();

    $stmt = $db->prepare("SELECT unlocked_operations, ads_watched, coins, is_premium FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $user['id']]);
    $row = $stmt->fetch();

    $unlocked = json_decode($row['unlocked_operations'] ?? '[]', true);
    if (!is_array($unlocked)) {
        $unlocked = [];
    }

    $alreadyUnlocked = in_array($operation, $unlocked);
    if (!$alreadyUnlocked) {
        $unlocked[] = $operation;
    }

    $newAdsCount = (int)$row['ads_watched'] + 1;
    $bonusCoins  = 20; // Bônus por assistir anúncio
    $newCoins    = (int)$row['coins'] + $bonusCoins;

    $stmtUpdate = $db->prepare("
        UPDATE users 
        SET unlocked_operations = :unlocked, 
            ads_watched = :ads, 
            coins = :coins 
        WHERE id = :id
    ");

    $stmtUpdate->execute([
        ':unlocked' => json_encode(array_values(array_unique($unlocked))),
        ':ads'      => $newAdsCount,
        ':coins'    => $newCoins,
        ':id'       => $user['id']
    ]);

    // REGRA DE NEGÓCIO CORE: Log de auditoria para auditorias futuras
    log_audit($db, $user['id'], 'DESBLOQUEIO_ANUNCIO', [
        'operacao_desbloqueada' => $operation,
        'total_anuncios'        => $newAdsCount,
        'moedas_ganhas'         => $bonusCoins
    ], $user['name']);

    // Atualiza sessão
    $_SESSION['user']['unlocked_operations'] = $unlocked;
    $_SESSION['user']['ads_watched'] = $newAdsCount;
    $_SESSION['user']['coins'] = $newCoins;

    $opLabels = [
        '+' => 'Adição (+)',
        '-' => 'Subtração (-)',
        '*' => 'Multiplicação (×)',
        '/' => 'Divisão (÷)'
    ];

    echo json_encode([
        'success'   => true,
        'message'   => "Parabéns! Operação {$opLabels[$operation]} desbloqueada com sucesso! Você ganhou +{$bonusCoins} moedas!",
        'unlocked'  => $unlocked,
        'coins'     => $newCoins,
        'ads_count' => $newAdsCount
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao processar recompensa: ' . $e->getMessage()]);
    exit;
}
