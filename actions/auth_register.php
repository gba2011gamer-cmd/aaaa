<?php
/**
 * Ação de Cadastro de Novo Usuário
 * Criptografia BCRYPT e Auditoria Automática
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/toast.php';
require_once __DIR__ . '/../includes/logger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php');
    exit;
}

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['password_confirm'] ?? '';

// Validações
if (empty($name) || empty($email) || empty($password)) {
    set_toast('error', 'Por favor, preencha todos os campos obrigatórios.');
    header('Location: ../register.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_toast('error', 'Formato de e-mail inválido.');
    header('Location: ../register.php');
    exit;
}

if (strlen($password) < 6) {
    set_toast('error', 'A senha deve conter no mínimo 6 caracteres.');
    header('Location: ../register.php');
    exit;
}

if ($password !== $confirm) {
    set_toast('error', 'As senhas digitadas não coincidem.');
    header('Location: ../register.php');
    exit;
}

try {
    $db = getDB();

    // Verifica duplicidade de e-mail
    $stmtCheck = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmtCheck->execute([':email' => $email]);
    if ($stmtCheck->fetch()) {
        set_toast('error', 'Este e-mail já está em uso por outro jogador.');
        header('Location: ../register.php');
        exit;
    }

    // REGRA DE NEGÓCIO CORE: Senha tratada com HASH BCRYPT
    $hashPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

    // Sorteia a primeira operação inicial do jogador
    $initialOps = ['+', '-', '*', '/'];
    $firstLuckyOp = $initialOps[array_rand($initialOps)];
    $unlockedJson = json_encode([$firstLuckyOp]);

    // Insere o novo usuário
    $stmt = $db->prepare("
        INSERT INTO users (name, email, password, is_premium, coins, score, ads_watched, current_drawn_op, unlocked_operations, created_at)
        VALUES (:name, :email, :password, 0, 100, 0, 0, :op, :unlocked, NOW())
    ");

    $stmt->execute([
        ':name'     => $name,
        ':email'    => $email,
        ':password' => $hashPassword,
        ':op'       => $firstLuckyOp,
        ':unlocked' => $unlockedJson
    ]);

    $newUserId = (int)$db->lastInsertId();

    // REGRA DE NEGÓCIO CORE: Log de auditoria para qualquer alteração
    log_audit($db, $newUserId, 'REGISTRO', [
        'mensagem'         => 'Novo jogador registrado no sistema',
        'email'            => $email,
        'operacao_inicial' => $firstLuckyOp
    ], $name);

    // Inicia a sessão automaticamente
    $_SESSION['user'] = [
        'id'                  => $newUserId,
        'name'                => $name,
        'email'               => $email,
        'is_premium'          => false,
        'coins'               => 100,
        'score'               => 0,
        'ads_watched'         => 0,
        'current_drawn_op'    => $firstLuckyOp,
        'unlocked_operations' => [$firstLuckyOp]
    ];

    set_toast('success', "Bem-vindo, {$name}! Sua primeira operação da sorte sorteada foi: [ {$firstLuckyOp} ]");
    header('Location: ../calculator.php');
    exit;

} catch (Exception $e) {
    set_toast('error', 'Erro interno ao processar cadastro: ' . $e->getMessage());
    header('Location: ../register.php');
    exit;
}
