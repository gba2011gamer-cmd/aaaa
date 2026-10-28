<?php
/**
 * Processamento Seguro de Cálculos Matemáticos
 * Valida regras de operações bloqueadas/sorteadas e registra auditoria completa
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/toast.php';
require_once __DIR__ . '/../includes/logger.php';
require_once __DIR__ . '/../includes/auth_check.php';

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Faça login para realizar cálculos.']);
    exit;
}

$user = get_logged_user();
$rawExpression = trim($_POST['expression'] ?? '');

if (empty($rawExpression)) {
    echo json_encode(['success' => false, 'message' => 'Nenhuma expressão matemática informada.']);
    exit;
}

// Normaliza símbolos (× para *, ÷ para /)
$normalized = str_replace(['×', 'x', 'X', '÷', ','], ['*', '*', '*', '/', '.'], $rawExpression);

// Validação estrita de caracteres (apenas números, espaços, pontos e operadores básicos)
if (!preg_match('/^[0-9\.\+\-\*\/\s\(\)]+$/', $normalized)) {
    echo json_encode(['success' => false, 'message' => 'Expressão contém caracteres inválidos ou não suportados.']);
    exit;
}

try {
    $db = getDB();

    // Consulta permissões atuais do usuário no banco
    $stmt = $db->prepare("SELECT is_premium, unlocked_operations, current_drawn_op, coins FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $user['id']]);
    $dbUser = $stmt->fetch();

    $isPrem = (int)$dbUser['is_premium'] === 1;
    $unlocked = json_decode($dbUser['unlocked_operations'] ?? '[]', true);
    if (!is_array($unlocked)) {
        $unlocked = [$dbUser['current_drawn_op'] ?: '+'];
    }

    // REGRA DE NEGÓCIO CORE: Verificar se os operadores utilizados estão permitidos
    $operatorsFound = [];
    foreach (['+', '-', '*', '/'] as $op) {
        if (strpos($normalized, $op) !== false) {
            $operatorsFound[] = $op;
        }
    }

    if (!$isPrem) {
        foreach ($operatorsFound as $op) {
            if (!in_array($op, $unlocked) && $op !== $dbUser['current_drawn_op']) {
                $opNames = ['+' => 'Adição (+)', '-' => 'Subtração (-)', '*' => 'Multiplicação (×)', '/' => 'Divisão (÷)'];
                $nomeOp = $opNames[$op] ?? $op;
                
                // Registra tentativa bloqueada no log de auditoria
                log_audit($db, $user['id'], 'CALCULO_BLOQUEADO', [
                    'expressao'            => $rawExpression,
                    'operador_bloqueado'   => $op,
                    'operacoes_permitidas' => $unlocked
                ], $user['name']);

                echo json_encode([
                    'success'         => false,
                    'blocked'         => true,
                    'blocked_op'      => $op,
                    'blocked_op_name' => $nomeOp,
                    'message'         => "A operação {$nomeOp} está bloqueada pela Sorte! Assista a um anúncio de 5s ou ative o Passe Premium para desbloquear."
                ]);
                exit;
            }
        }
    }

    // Avaliação matemática segura sem eval()
    $result = safeCalculate($normalized);

    if ($result === null || is_nan($result) || is_infinite($result)) {
        echo json_encode(['success' => false, 'message' => 'Erro no cálculo. Não é permitida divisão por zero.']);
        exit;
    }

    // Formata o resultado
    if (floor($result) == $result) {
        $formattedResult = (string)(int)$result;
    } else {
        $formattedResult = rtrim(rtrim(number_format($result, 6, '.', ''), '0'), '.');
    }

    // Bonificação por cálculo realizado com a sorte
    $rewardCoins = 5;
    $newCoins = (int)$dbUser['coins'] + $rewardCoins;

    // Salva na tabela calculations
    $usedOp = !empty($operatorsFound) ? implode(', ', $operatorsFound) : 'Número';
    $stmtCalc = $db->prepare("
        INSERT INTO calculations (user_id, expression, result, operation_used, mode, created_at)
        VALUES (:uid, :exp, :res, :op, 'calculadora_livre', NOW())
    ");
    $stmtCalc->execute([
        ':uid' => $user['id'],
        ':exp' => $rawExpression,
        ':res' => $formattedResult,
        ':op'  => $usedOp
    ]);

    // Atualiza moedas do usuário
    $stmtCoins = $db->prepare("UPDATE users SET coins = :coins WHERE id = :id");
    $stmtCoins->execute([':coins' => $newCoins, ':id' => $user['id']]);
    $_SESSION['user']['coins'] = $newCoins;

    // REGRA DE NEGÓCIO CORE: Auditoria completa
    log_audit($db, $user['id'], 'CALCULO_REALIZADO', [
        'expressao'      => $rawExpression,
        'resultado'      => $formattedResult,
        'operacoes_usadas'=> $usedOp,
        'moedas_ganhas'  => $rewardCoins
    ], $user['name']);

    echo json_encode([
        'success'    => true,
        'expression' => $rawExpression,
        'result'     => $formattedResult,
        'coins'      => $newCoins,
        'reward'     => $rewardCoins,
        'message'    => "Cálculo efetuado com sucesso! +{$rewardCoins} moedas da sorte."
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no processamento matemático: ' . $e->getMessage()]);
    exit;
}

/**
 * Avaliador de expressões matemáticas seguro (Shunting-yard / Tokenizer)
 * Não utiliza eval() para máxima segurança
 */
function safeCalculate($expr) {
    // Remove espaços
    $expr = preg_replace('/\s+/', '', $expr);

    // Substitui múltiplos sinais (+- ou --)
    $expr = str_replace(['++', '--'], '+', $expr);
    $expr = str_replace('+-', '-', $expr);
    $expr = str_replace('-+', '-', $expr);

    // Divisão por zero explícita
    if (preg_match('/\/0+(?![0-9\.])/', $expr)) {
        return null;
    }

    // Tokenização
    $tokens = [];
    $len = strlen($expr);
    $num = '';
    
    for ($i = 0; $i < $len; $i++) {
        $c = $expr[$i];
        if (is_numeric($c) || $c === '.') {
            $num .= $c;
        } elseif (in_array($c, ['+', '-', '*', '/', '(', ')'])) {
            if ($num !== '') {
                $tokens[] = (float)$num;
                $num = '';
            }
            // Trata números negativos iniciais ou após operador
            if ($c === '-' && ($i === 0 || in_array($expr[$i - 1], ['+', '-', '*', '/', '(']))) {
                $num = '-';
            } else {
                $tokens[] = $c;
            }
        }
    }
    if ($num !== '') {
        $tokens[] = (float)$num;
    }

    // Processamento de precedência: Multiplicação e Divisão primeiro, depois Adição e Subtração
    // 1. Simplifica multiplicações e divisões
    $pass1 = [];
    $i = 0;
    while ($i < count($tokens)) {
        $token = $tokens[$i];
        if ($token === '*' || $token === '/') {
            $prev = array_pop($pass1);
            $next = $tokens[++$i];
            if ($token === '*') {
                $pass1[] = $prev * $next;
            } else {
                if ($next == 0) return null;
                $pass1[] = $prev / $next;
            }
        } else {
            $pass1[] = $token;
        }
        $i++;
    }

    // 2. Simplifica adições e subtrações
    if (empty($pass1)) return 0;
    $res = $pass1[0];
    $i = 1;
    while ($i < count($pass1)) {
        $op = $pass1[$i++];
        $val = $pass1[$i++];
        if ($op === '+') {
            $res += $val;
        } elseif ($op === '-') {
            $res -= $val;
        }
    }

    return $res;
}
