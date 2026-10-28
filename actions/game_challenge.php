<?php
/**
 * Lógica do Jogo de Desafio Matemático "Calculando a Sorte"
 * O jogador deve atingir o Número Alvo usando a operação da sorte
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/toast.php';
require_once __DIR__ . '/../includes/logger.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Faça login para jogar.']);
    exit;
}

$user = get_logged_user();
$action = $_POST['action'] ?? 'new_challenge';

try {
    $db = getDB();

    if ($action === 'new_challenge') {
        // Gera um desafio novo
        $operations = ['+', '-', '*', '/'];
        $drawnOp = $operations[array_rand($operations)];

        // Gera números coerentes para o cálculo
        switch ($drawnOp) {
            case '+':
                $a = rand(5, 45);
                $b = rand(3, 40);
                $target = $a + $b;
                break;
            case '-':
                $target = rand(5, 50);
                $b = rand(2, 30);
                $a = $target + $b;
                break;
            case '*':
                $a = rand(2, 12);
                $b = rand(2, 12);
                $target = $a * $b;
                break;
            case '/':
                $b = rand(2, 9);
                $target = rand(2, 15);
                $a = $target * $b;
                break;
        }

        // Salva desafio no banco
        $stmt = $db->prepare("
            INSERT INTO game_challenges (user_id, target_number, drawn_operation, status, created_at)
            VALUES (:uid, :target, :op, 'PENDENTE', NOW())
        ");
        $stmt->execute([
            ':uid'    => $user['id'],
            ':target' => $target,
            ':op'     => $drawnOp
        ]);
        $challengeId = (int)$db->lastInsertId();

        // Embaralha números sugeridos
        $options = [$a, $b, rand(1, 20), rand(1, 20)];
        shuffle($options);

        echo json_encode([
            'success'          => true,
            'challenge_id'     => $challengeId,
            'target'           => $target,
            'drawn_operation'  => $drawnOp,
            'hint_numbers'     => $options,
            'message'          => "Alcance o número alvo {$target} utilizando a operação da sorte [ {$drawnOp} ]!"
        ]);
        exit;

    } elseif ($action === 'solve') {
        $challengeId = (int)($_POST['challenge_id'] ?? 0);
        $solution    = trim($_POST['solution'] ?? '');

        if (!$challengeId || empty($solution)) {
            echo json_encode(['success' => false, 'message' => 'Envie a solução do desafio.']);
            exit;
        }

        $stmt = $db->prepare("SELECT * FROM game_challenges WHERE id = :id AND user_id = :uid AND status = 'PENDENTE' LIMIT 1");
        $stmt->execute([':id' => $challengeId, ':uid' => $user['id']]);
        $challenge = $stmt->fetch();

        if (!$challenge) {
            echo json_encode(['success' => false, 'message' => 'Desafio não encontrado ou já finalizado.']);
            exit;
        }

        // Normaliza e calcula expressão do usuário
        $normalized = str_replace(['×', 'x', '÷', ','], ['*', '*', '/', '.'], $solution);
        
        // Garante que usou a operação sorteada
        if (strpos($normalized, $challenge['drawn_operation']) === false) {
            echo json_encode([
                'success' => false, 
                'message' => "Você precisa usar a operação da sorte sorteada [ {$challenge['drawn_operation']} ] no cálculo!"
            ]);
            exit;
        }

        // Calcula com a função segura
        require_once __DIR__ . '/game_calculate.php';
        $computed = safeCalculate($normalized);

        if ($computed === null || abs($computed - $challenge['target_number']) > 0.0001) {
            echo json_encode([
                'success' => false,
                'message' => "Resultado {$computed} diferente do alvo {$challenge['target_number']}! Tente novamente."
            ]);
            exit;
        }

        // Vitória!
        $pointsEarned = 50;
        $coinsEarned = 25;

        // Atualiza desafio
        $stmtUp = $db->prepare("
            UPDATE game_challenges 
            SET status = 'VENCEU', user_solution = :sol, points_earned = :pts 
            WHERE id = :id
        ");
        $stmtUp->execute([
            ':sol' => $solution,
            ':pts' => $pointsEarned,
            ':id'  => $challengeId
        ]);

        // Atualiza perfil do usuário
        $stmtUser = $db->prepare("
            UPDATE users 
            SET score = score + :pts, coins = coins + :coins 
            WHERE id = :id
        ");
        $stmtUser->execute([
            ':pts'   => $pointsEarned,
            ':coins' => $coinsEarned,
            ':id'    => $user['id']
        ]);

        // REGRA DE NEGÓCIO CORE: Auditoria
        log_audit($db, $user['id'], 'DESAFIO_VENCEU', [
            'desafio_id'    => $challengeId,
            'alvo'          => $challenge['target_number'],
            'solucao'       => $solution,
            'pontos_ganhos' => $pointsEarned,
            'moedas_ganhas' => $coinsEarned
        ], $user['name']);

        $_SESSION['user']['score'] = (int)$_SESSION['user']['score'] + $pointsEarned;
        $_SESSION['user']['coins'] = (int)$_SESSION['user']['coins'] + $coinsEarned;

        echo json_encode([
            'success'       => true,
            'target'        => $challenge['target_number'],
            'points_earned' => $pointsEarned,
            'coins_earned'  => $coinsEarned,
            'new_score'     => $_SESSION['user']['score'],
            'new_coins'     => $_SESSION['user']['coins'],
            'message'       => "🎉 Parabéns! Você atingiu o alvo {$challenge['target_number']} e ganhou +{$pointsEarned} pontos e +{$coinsEarned} moedas!"
        ]);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no desafio: ' . $e->getMessage()]);
    exit;
}
