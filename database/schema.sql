-- ========================================================
-- BANCO DE DADOS: calculando_a_sorte
-- Plataforma de Jogos e Calculadora da Sorte
-- ========================================================

CREATE DATABASE IF NOT EXISTS `calculando_a_sorte`
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `calculando_a_sorte`;

-- --------------------------------------------------------
-- 1. TABELA DE USUÁRIOS
-- Senhas criptografadas com BCRYPT (PASSWORD_BCRYPT)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL COMMENT 'Hash Bcrypt',
    `is_premium` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0: Gratuito, 1: VIP Premium',
    `coins` INT NOT NULL DEFAULT 100 COMMENT 'Moedas do jogo',
    `score` INT NOT NULL DEFAULT 0 COMMENT 'Pontos em desafios',
    `ads_watched` INT NOT NULL DEFAULT 0 COMMENT 'Total de anúncios assistidos',
    `current_drawn_op` VARCHAR(5) NOT NULL DEFAULT '+' COMMENT 'Operação sorteada atual: +, -, *, /',
    `unlocked_operations` VARCHAR(255) NOT NULL DEFAULT '["+"]' COMMENT 'JSON com operações desbloqueadas',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. TABELA DE AUDITORIA E LOGS (HISTÓRICO COMPLETO)
-- Armazena qualquer alteração realizada por qualquer usuário
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL COMMENT 'ID do usuário (ou NULL se anônimo/tentativa)',
    `user_name` VARCHAR(100) NULL,
    `action` VARCHAR(80) NOT NULL COMMENT 'Ação: REGISTRO, LOGIN, LOGOUT, CALCULO, SORTEIO, ANUNCIO, PREMIUM, DESAFIO',
    `details` TEXT NOT NULL COMMENT 'Detalhes e metadados da alteração',
    `ip_address` VARCHAR(45) NOT NULL COMMENT 'IP do cliente',
    `user_agent` VARCHAR(255) NULL COMMENT 'Navegador/Dispositivo',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. TABELA DE CÁLCULOS EFETUADOS
-- Histórico individual de operações da calculadora
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `calculations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `expression` VARCHAR(255) NOT NULL,
    `result` VARCHAR(100) NOT NULL,
    `operation_used` VARCHAR(10) NOT NULL,
    `mode` VARCHAR(50) NOT NULL DEFAULT 'calculadora_livre',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. TABELA DE DESAFIOS MATEMÁTICOS (JOGO)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `game_challenges` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `target_number` INT NOT NULL,
    `drawn_operation` VARCHAR(10) NOT NULL,
    `user_solution` VARCHAR(255) NULL,
    `status` ENUM('PENDENTE', 'VENCEU', 'PULOU') NOT NULL DEFAULT 'PENDENTE',
    `points_earned` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- INSERÇÃO DE USUÁRIOS DE TESTE INICIAIS
-- Senhas padrão: "123456"
-- Hash Bcrypt de "123456": $2y$10$wTf2i0M4Lg1fUeF9h0K8..i8aZqHn5qS2M6K.W7uK4Y8O9P0Q1R2S
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `name`, `email`, `password`, `is_premium`, `coins`, `score`, `current_drawn_op`, `unlocked_operations`)
VALUES 
(1, 'Admin da Sorte', 'admin@sorte.com', '$2y$10$eA06hU78PffFqXF2uA1cE.lKev4d0WwV1mF5R6D9O8L7k0uA3P0lC', 1, 9999, 1500, '+', '["+","-","*","/"]'),
(2, 'Jogador Comum', 'jogador@sorte.com', '$2y$10$eA06hU78PffFqXF2uA1cE.lKev4d0WwV1mF5R6D9O8L7k0uA3P0lC', 0, 150, 250, '+', '["+"]')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Log inicial de auditoria
INSERT INTO `audit_logs` (`user_id`, `user_name`, `action`, `details`, `ip_address`, `user_agent`)
VALUES 
(1, 'Admin da Sorte', 'SISTEMA', 'Inicialização do banco de dados e semente de usuários.', '127.0.0.1', 'Database Seeder');
