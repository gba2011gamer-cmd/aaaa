<?php
/**
 * Conexão Segura com Banco de Dados MySQL via PDO
 * Projeto: Calculando a Sorte
 */

// Evita carregamentos múltiplos
if (defined('BD_CONNECTED')) {
    return;
}

// Configurações do Banco de Dados
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'calculando_a_sorte');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

$pdo = null;
$db_error = null;

try {
    // 1. Tenta conectar diretamente ao banco de dados especificado
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // Se o banco não existe (código 1049), conecta ao servidor MySQL e cria o banco automaticamente
        if ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false) {
            $dsnServer = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
            $pdoServer = new PDO($dsnServer, DB_USER, DB_PASS, $options);
            $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Reconecta agora com o banco criado
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            // Executa o schema SQL automaticamente
            $schemaFile = __DIR__ . '/../database/schema.sql';
            if (file_exists($schemaFile)) {
                $sql = file_get_contents($schemaFile);
                $pdo->exec($sql);
            }
        } else {
            throw $e;
        }
    }

    // Garante que as tabelas essenciais existem
    autoVerifyTables($pdo);

    define('BD_CONNECTED', true);

} catch (PDOException $e) {
    $db_error = $e->getMessage();
    // Armazena erro para exibição limpa nas telas via Toast/Modal em vez de quebrar de forma bruta
    if (!defined('BD_ERROR')) {
        define('BD_ERROR', $db_error);
    }
}

/**
 * Verifica e cria tabelas se necessário
 */
function autoVerifyTables(PDO $pdo) {
    $check = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
    if (!$check) {
        $schemaFile = __DIR__ . '/../database/schema.sql';
        if (file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            $pdo->exec($sql);
        }
    }
}

/**
 * Retorna a instância do PDO ativa ou lança exceção amigável
 */
function getDB() {
    global $pdo, $db_error;
    if (!$pdo) {
        throw new Exception("Falha na conexão com o Banco de Dados MySQL: " . ($db_error ?: 'Servidor inacessível. Inicie o MySQL/XAMPP.'));
    }
    return $pdo;
}
