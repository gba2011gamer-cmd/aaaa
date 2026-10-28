<?php
/**
 * Sorteio da Operação da Sorte
 * Gira a roleta de operações matemáticas (+, -, *, /)
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/toast.php';
require_once __DIR__ . '/../includes/logger.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

$user = get_logged_user();
$availableOps = ['+', '-', '*', '/'];

// Sorteia uma operação aleatoriamente
$drawnOp = $availableOps[array_rand($availableOps)];

try {
    $db = getDB();

    // Obtém lista de operações já desbloqueadas
    $stmt = $db->prepare("SELECT unlocked_operations, is_premium, coins FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $user['id']]);
    $dbUser = $stmt->fetch();

    $unlocked = json_decode($dbUser['unlocked_operations'] ?? '[]', true);
    if (!is_array($unlocked)) {
        $unlocked = [];
    }

    // A operação sorteada fica imediatamente utilizável
    if (!in_array($drawnOp, $unlocked)) {
        $unlocked[] = $drawnOp;
    }

    if ((int)$dbUser['is_premium'] === 1) {
        $unlocked = ['+', '-', '*', '/'];
    }

    $unlockedJson = json_encode(array_values(array_unique($unlocked)));

    // Atualiza no banco
    $stmtUpdate = $db->prepare("
        UPDATE users 
        SET current_drawn_op = :op, unlocked_operations = :unlocked 
        WHERE id = :id
    ");
    $stmtUpdate->execute([
        ':op'       => $drawnOp,
        ':unlocked' => $unlockedJson,
        ':id'       => $user['id']
    ]);

    // REGRA DE NEGÓCIO CORE: Log de auditoria
    log_audit($db, $user['id'], 'SORTEIO_OPERACAO', [
        'operacao_sorteada' => $drawnOp,
        'operacoes_ativas'  => $unlocked
    ], $user['name']);

    // Atualiza sessão
    $_SESSION['user']['current_drawn_op'] = $drawnOp;
    $_SESSION['user']['unlocked_operations'] = $unlocked;

    // Tradução amigável
    $opNames = [
        '+' => 'Adição (+)',
        '-' => 'Subtração (-)',
        '*' => 'Multiplicação (×)',
        '/' => 'Divisão (÷)'
    ];

    echo json_encode([
        'success'         => true,
        'operation'       => $drawnOp,
        'operation_label' => $opNames[$drawnOp] ?? $drawnOp,
        'unlocked'        => $unlocked,
        'message'         => "Sorte Grande! Sua operação da rodada agora é: {$opNames[$drawnOp]}"
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao sortear operação: ' . $e->getMessage()
    ]);
    exit;
}
