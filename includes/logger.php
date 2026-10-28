<?php
/**
 * Sistema de Auditoria e Logs em Banco de Dados
 * Registra todas as ações e alterações realizadas por qualquer usuário
 */

if (!function_exists('log_audit')) {
    /**
     * Registra um evento de auditoria no banco de dados MySQL via PDO
     *
     * @param PDO $pdo Instância ativa do PDO
     * @param int|null $userId ID do usuário responsável (ou null se anônimo)
     * @param string $action Identificador da ação (ex: 'REGISTRO', 'LOGIN_SUCESSO', 'CALCULO')
     * @param string|array $details Descrição textual ou array associativo com detalhes da alteração
     * @param string|null $userName Nome opcional do usuário para desnormalização de auditoria
     * @return bool Sucesso do registro
     */
    function log_audit($pdo, $userId, $action, $details, $userName = null) {
        if (!$pdo) {
            return false;
        }

        try {
            // Se detalhes for array, converte para JSON legível
            if (is_array($details)) {
                $detailsText = json_encode($details, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            } else {
                $detailsText = (string)$details;
            }

            // Identifica IP do cliente
            $ip = '127.0.0.1';
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
            } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            $ip = trim(substr($ip, 0, 45));

            // User Agent
            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : 'Desconhecido';

            // Se o nome não for fornecido, tenta buscar do usuário logado se $userId existir
            if (!$userName && $userId) {
                if (isset($_SESSION['user']['name']) && $_SESSION['user']['id'] == $userId) {
                    $userName = $_SESSION['user']['name'];
                } else {
                    $stmtUser = $pdo->prepare("SELECT name FROM users WHERE id = :id LIMIT 1");
                    $stmtUser->execute([':id' => $userId]);
                    $row = $stmtUser->fetch();
                    $userName = $row ? $row['name'] : 'Usuário #' . $userId;
                }
            }

            $stmt = $pdo->prepare("
                INSERT INTO audit_logs (user_id, user_name, action, details, ip_address, user_agent, created_at)
                VALUES (:user_id, :user_name, :action, :details, :ip, :user_agent, NOW())
            ");

            return $stmt->execute([
                ':user_id'    => $userId,
                ':user_name'  => $userName ?: 'Visitante',
                ':action'     => strtoupper(trim($action)),
                ':details'    => $detailsText,
                ':ip'         => $ip,
                ':user_agent' => $userAgent
            ]);
        } catch (Exception $e) {
            // Em caso de erro na gravação do log, mantém a integridade da aplicação
            error_log("Falha ao registrar log de auditoria: " . $e->getMessage());
            return false;
        }
    }
}
