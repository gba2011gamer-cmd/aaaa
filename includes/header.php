<?php
/**
 * Cabeçalho Global Responsivo (Mobile-First)
 * Projeto: Calculando a Sorte
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/toast.php';

// Tenta sincronizar a sessão com o banco se o usuário estiver autenticado
if (is_logged_in() && isset($pdo)) {
    refresh_user_session($pdo);
}

$currentUser = get_logged_user();
$pageTitle = $pageTitle ?? 'Calculando a Sorte';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($pageTitle) ?> | Calculando a Sorte</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                        },
                        lucky: {
                            amber: '#f59e0b',
                            gold: '#fbbf24',
                            purple: '#8b5cf6'
                        }
                    }
                }
            }
        }
    </script>

    <!-- Estilos Customizados & Fontes -->
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col antialiased selection:bg-amber-500 selection:text-slate-950">

    <!-- Topbar de Navegação (Desktop & Mobile) -->
    <header class="sticky top-0 z-40 bg-slate-900/90 backdrop-blur-md border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Logo & Marca -->
                <a href="calculator.php" class="flex items-center gap-2.5 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-emerald-400 flex items-center justify-center shadow-lg shadow-amber-500/20 group-hover:scale-105 transition-transform">
                        <span class="text-xl">🎲</span>
                    </div>
                    <div>
                        <div class="text-base font-extrabold tracking-tight bg-gradient-to-r from-amber-400 via-emerald-300 to-emerald-500 bg-clip-text text-transparent">
                            Calculando a Sorte
                        </div>
                        <div class="text-[10px] text-slate-400 -mt-1 font-medium flex items-center gap-1">
                            <span>Jogos & Matemática</span>
                            <span class="w-1 h-1 rounded-full bg-emerald-500 inline-block"></span>
                            <span class="text-emerald-400">Ao Vivo</span>
                        </div>
                    </div>
                </a>

                <!-- Navegação Central (Desktop) -->
                <nav class="hidden md:flex items-center gap-1">
                    <a href="calculator.php" class="px-3 py-2 rounded-lg text-sm font-semibold transition-colors <?= $currentPage === 'calculator.php' ? 'bg-slate-800 text-amber-400 border border-slate-700' : 'text-slate-300 hover:text-white hover:bg-slate-800/50' ?>">
                        🧮 Calculadora
                    </a>
                    <a href="game.php" class="px-3 py-2 rounded-lg text-sm font-semibold transition-colors <?= $currentPage === 'game.php' ? 'bg-slate-800 text-emerald-400 border border-slate-700' : 'text-slate-300 hover:text-white hover:bg-slate-800/50' ?>">
                        🎯 Desafio da Sorte
                    </a>
                    <a href="history.php" class="px-3 py-2 rounded-lg text-sm font-semibold transition-colors <?= $currentPage === 'history.php' ? 'bg-slate-800 text-sky-400 border border-slate-700' : 'text-slate-300 hover:text-white hover:bg-slate-800/50' ?>">
                        📜 Histórico & Auditoria
                    </a>
                    <a href="premium.php" class="px-3 py-2 rounded-lg text-sm font-semibold transition-colors <?= $currentPage === 'premium.php' ? 'bg-slate-800 text-purple-400 border border-slate-700' : 'text-slate-300 hover:text-white hover:bg-slate-800/50' ?>">
                        ⭐ Loja VIP
                    </a>
                </nav>

                <!-- Área do Usuário / Autenticação -->
                <div class="flex items-center gap-3">
                    <?php if ($currentUser): ?>
                        <!-- Saldo de Moedas -->
                        <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-slate-800/80 rounded-full border border-amber-500/30 text-amber-300 text-xs font-bold">
                            <span>🪙</span>
                            <span id="header-coins-val"><?= (int)($currentUser['coins'] ?? 0) ?></span>
                        </div>

                        <!-- Status VIP / Free -->
                        <?php if (!empty($currentUser['is_premium'])): ?>
                            <span class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-sm glow-purple">
                                ⭐ VIP
                            </span>
                        <?php else: ?>
                            <a href="premium.php" class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-800 hover:bg-slate-700 text-amber-400 border border-amber-500/40 transition-colors">
                                Desbloquear +
                            </a>
                        <?php endif; ?>

                        <!-- Menu de Usuário -->
                        <div class="flex items-center gap-2 pl-2 border-l border-slate-800">
                            <div class="text-right hidden lg:block">
                                <div class="text-xs font-bold text-slate-200"><?= htmlspecialchars($currentUser['name']) ?></div>
                                <div class="text-[10px] text-slate-400"><?= htmlspecialchars($currentUser['email']) ?></div>
                            </div>
                            <a href="logout.php" title="Sair do Sistema" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center gap-2">
                            <a href="login.php" class="px-3.5 py-1.5 text-xs sm:text-sm font-semibold text-slate-300 hover:text-white transition-colors">
                                Entrar
                            </a>
                            <a href="register.php" class="px-3.5 py-1.5 text-xs sm:text-sm font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg transition-all shadow-md shadow-emerald-900/40">
                                Criar Conta
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </header>

    <!-- Barra de Navegação Inferior Fixa (Mobile First) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-slate-900/95 backdrop-blur-lg border-t border-slate-800 px-2 py-2 flex items-center justify-around">
        <a href="calculator.php" class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl <?= $currentPage === 'calculator.php' ? 'text-amber-400 font-bold bg-slate-800/80' : 'text-slate-400' ?>">
            <span class="text-lg">🧮</span>
            <span class="text-[10px]">Calculadora</span>
        </a>
        <a href="game.php" class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl <?= $currentPage === 'game.php' ? 'text-emerald-400 font-bold bg-slate-800/80' : 'text-slate-400' ?>">
            <span class="text-lg">🎯</span>
            <span class="text-[10px]">Desafio</span>
        </a>
        <a href="history.php" class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl <?= $currentPage === 'history.php' ? 'text-sky-400 font-bold bg-slate-800/80' : 'text-slate-400' ?>">
            <span class="text-lg">📜</span>
            <span class="text-[10px]">Auditoria</span>
        </a>
        <a href="premium.php" class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl <?= $currentPage === 'premium.php' ? 'text-purple-400 font-bold bg-slate-800/80' : 'text-slate-400' ?>">
            <span class="text-lg">⭐</span>
            <span class="text-[10px]">Loja VIP</span>
        </a>
    </nav>

    <!-- Conteúdo Principal -->
    <main class="flex-1 pb-20 md:pb-8">
