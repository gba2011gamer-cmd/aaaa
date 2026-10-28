<?php
/**
 * View de Históricos e Logs de Auditoria do Sistema
 * Permite auditorias completas de qualquer alteração feita por qualquer usuário
 */

$user = get_logged_user();
$db = null;
$dbError = null;

try {
    $db = getDB();
} catch (Exception $e) {
    $dbError = $e->getMessage();
}

// Filtros de busca
$filterAction = trim($_GET['action'] ?? '');
$filterSearch = trim($_GET['q'] ?? '');
$filterUser   = trim($_GET['user_id'] ?? '');

$totalLogs = 0;
$totalCalcs = 0;
$totalAds = 0;
$totalUsers = 0;
$logs = [];

if ($db) {
    // Constrói query dinâmica com PDO prepared statements
    $where = [];
    $params = [];

    if ($filterAction !== '') {
        $where[] = "action = :action";
        $params[':action'] = $filterAction;
    }

    if ($filterSearch !== '') {
        $where[] = "(details LIKE :search OR user_name LIKE :search2 OR ip_address LIKE :search3)";
        $params[':search']  = "%{$filterSearch}%";
        $params[':search2'] = "%{$filterSearch}%";
        $params[':search3'] = "%{$filterSearch}%";
    }

    if ($filterUser !== '') {
        $where[] = "user_id = :uid";
        $params[':uid'] = (int)$filterUser;
    }

    $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    // Contagens para os cards de estatísticas
    $totalLogs = (int)$db->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
    $totalCalcs = (int)$db->query("SELECT COUNT(*) FROM calculations")->fetchColumn();
    $totalAds   = (int)($db->query("SELECT SUM(ads_watched) FROM users")->fetchColumn() ?: 0);
    $totalUsers = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();

    // Busca os logs paginados (últimos 50)
    $sqlLogs = "SELECT * FROM audit_logs {$whereSql} ORDER BY id DESC LIMIT 50";
    $stmtLogs = $db->prepare($sqlLogs);
    $stmtLogs->execute($params);
    $logs = $stmtLogs->fetchAll();
}

// Lista de ações distintas para o filtro select
$availableActions = [
    'REGISTRO'            => 'Cadastro de Usuário',
    'LOGIN_SUCESSO'       => 'Login Bem-Sucedido',
    'LOGIN_FALHA'         => 'Falha de Autenticação',
    'LOGOUT'              => 'Encerramento de Sessão',
    'CALCULO_REALIZADO'   => 'Cálculo Efetuado',
    'CALCULO_BLOQUEADO'   => 'Cálculo com Operação Bloqueada',
    'SORTEIO_OPERACAO'    => 'Sorteio de Operação da Sorte',
    'DESBLOQUEIO_ANUNCIO' => 'Desbloqueio via Anúncio',
    'PREMIUM_ALTERADO'    => 'Alteração do Passe VIP',
    'DESAFIO_VENCEU'      => 'Vitória no Desafio Matemático'
];
?>

<div class="max-w-7xl mx-auto px-4 py-6">

    <!-- Cabeçalho da Página -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-2xl">📜</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Histórico & Auditoria
                </h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Registro imutável de todas as alterações, cálculos e eventos de usuários no sistema.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="calculator.php" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold border border-slate-700 transition-colors">
                ← Voltar à Calculadora
            </a>
        </div>
    </div>

    <?php if ($dbError): ?>
        <div class="mb-6 p-4 rounded-2xl bg-amber-950/60 border border-amber-500/40 text-amber-200 text-xs">
            ⚠️ <strong>Aviso de Conexão MySQL:</strong> <?= htmlspecialchars($dbError) ?><br>
            Certifique-se de que o servidor MySQL (XAMPP / MariaDB) esteja ativo na porta 3306.
        </div>
    <?php endif; ?>

    <!-- Cards de Métricas e Auditoria -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <div class="glass-panel p-4 rounded-2xl border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total de Eventos</div>
            <div class="text-2xl sm:text-3xl font-black text-amber-400 font-mono-calc mt-1"><?= number_format($totalLogs) ?></div>
            <div class="text-[10px] text-slate-500 mt-0.5">Logs de auditoria ativos</div>
        </div>

        <div class="glass-panel p-4 rounded-2xl border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Cálculos Realizados</div>
            <div class="text-2xl sm:text-3xl font-black text-emerald-400 font-mono-calc mt-1"><?= number_format($totalCalcs) ?></div>
            <div class="text-[10px] text-slate-500 mt-0.5">Operações matemáticas</div>
        </div>

        <div class="glass-panel p-4 rounded-2xl border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Anúncios Assistidos</div>
            <div class="text-2xl sm:text-3xl font-black text-purple-400 font-mono-calc mt-1"><?= number_format($totalAds) ?></div>
            <div class="text-[10px] text-slate-500 mt-0.5">Recompensas desbloqueadas</div>
        </div>

        <div class="glass-panel p-4 rounded-2xl border border-slate-800">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jogadores Cadastrados</div>
            <div class="text-2xl sm:text-3xl font-black text-sky-400 font-mono-calc mt-1"><?= number_format($totalUsers) ?></div>
            <div class="text-[10px] text-slate-500 mt-0.5">Contas com senha Bcrypt</div>
        </div>
    </div>

    <!-- Barra de Filtros de Auditoria -->
    <div class="glass-panel p-4 rounded-2xl border border-slate-800 mb-6">
        <form method="GET" action="history.php" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            
            <div class="sm:col-span-4">
                <label class="block text-xs font-semibold text-slate-400 mb-1">Buscar por termo, usuário ou IP:</label>
                <input type="text" name="q" value="<?= htmlspecialchars($filterSearch) ?>" placeholder="Ex: login, 127.0.0.1, João..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-500">
            </div>

            <div class="sm:col-span-4">
                <label class="block text-xs font-semibold text-slate-400 mb-1">Filtrar por Tipo de Ação:</label>
                <select name="action" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-amber-500">
                    <option value="">Todas as Ações</option>
                    <?php foreach ($availableActions as $actKey => $actLabel): ?>
                        <option value="<?= $actKey ?>" <?= $filterAction === $actKey ? 'selected' : '' ?>><?= $actLabel ?> (<?= $actKey ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="sm:col-span-4 flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs transition-colors flex items-center justify-center gap-1.5 shadow-md shadow-amber-950/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <span>Filtrar</span>
                </button>
                <a href="history.php" class="py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs border border-slate-700 transition-colors">
                    Limpar
                </a>
            </div>

        </form>
    </div>

    <!-- Tabela / Lista de Logs (Mobile First) -->
    <div class="glass-panel rounded-2xl border border-slate-800 overflow-hidden shadow-2xl">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                <span>Registros de Auditoria Recentes</span>
                <span class="text-xs font-normal text-slate-400">(Mostrando até 50 mais recentes)</span>
            </h2>
        </div>

        <?php if (!empty($logs)): ?>
            
            <!-- Visão Desktop (Tabela Completa) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-900/80 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-800">
                        <tr>
                            <th class="py-3 px-4">Data/Hora</th>
                            <th class="py-3 px-4">Usuário</th>
                            <th class="py-3 px-4">Ação</th>
                            <th class="py-3 px-4">Detalhes da Alteração</th>
                            <th class="py-3 px-4">IP / Origem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-medium">
                        <?php foreach ($logs as $log): 
                            // Cores de badges
                            $badgeColor = 'bg-slate-800 text-slate-300 border-slate-700';
                            if (strpos($log['action'], 'SUCESSO') !== false || strpos($log['action'], 'VENCEU') !== false) {
                                $badgeColor = 'bg-emerald-950/80 text-emerald-300 border-emerald-500/40';
                            } elseif (strpos($log['action'], 'FALHA') !== false || strpos($log['action'], 'BLOQUEADO') !== false) {
                                $badgeColor = 'bg-rose-950/80 text-rose-300 border-rose-500/40';
                            } elseif (strpos($log['action'], 'SORTEIO') !== false || strpos($log['action'], 'ANUNCIO') !== false) {
                                $badgeColor = 'bg-amber-950/80 text-amber-300 border-amber-500/40';
                            } elseif (strpos($log['action'], 'PREMIUM') !== false) {
                                $badgeColor = 'bg-purple-950/80 text-purple-300 border-purple-500/40';
                            }
                        ?>
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="py-3 px-4 font-mono-calc text-slate-400 whitespace-nowrap">
                                    <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-200"><?= htmlspecialchars($log['user_name'] ?: 'Visitante') ?></div>
                                    <div class="text-[10px] text-slate-500">ID: <?= $log['user_id'] ? '#' . $log['user_id'] : 'N/A' ?></div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="inline-block px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase border <?= $badgeColor ?>">
                                        <?= htmlspecialchars($log['action']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-300 font-mono text-[11px] max-w-md break-words">
                                    <?php 
                                    $jsonDecoded = json_decode($log['details'], true);
                                    if (is_array($jsonDecoded)) {
                                        echo '<span class="text-slate-300">' . htmlspecialchars(json_encode($jsonDecoded, JSON_UNESCAPED_UNICODE)) . '</span>';
                                    } else {
                                        echo htmlspecialchars($log['details']);
                                    }
                                    ?>
                                </td>
                                <td class="py-3 px-4 font-mono-calc text-slate-400 whitespace-nowrap">
                                    <div><?= htmlspecialchars($log['ip_address']) ?></div>
                                    <div class="text-[10px] text-slate-500 truncate max-w-[140px]" title="<?= htmlspecialchars($log['user_agent'] ?? '') ?>">
                                        <?= htmlspecialchars(substr($log['user_agent'] ?? 'N/A', 0, 30)) ?>...
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Visão Mobile (Cards Responsivos) -->
            <div class="md:hidden divide-y divide-slate-800/80">
                <?php foreach ($logs as $log): 
                    $badgeColor = 'bg-slate-800 text-slate-300 border-slate-700';
                    if (strpos($log['action'], 'SUCESSO') !== false || strpos($log['action'], 'VENCEU') !== false) {
                        $badgeColor = 'bg-emerald-950/80 text-emerald-300 border-emerald-500/40';
                    } elseif (strpos($log['action'], 'FALHA') !== false || strpos($log['action'], 'BLOQUEADO') !== false) {
                        $badgeColor = 'bg-rose-950/80 text-rose-300 border-rose-500/40';
                    } elseif (strpos($log['action'], 'SORTEIO') !== false || strpos($log['action'], 'ANUNCIO') !== false) {
                        $badgeColor = 'bg-amber-950/80 text-amber-300 border-amber-500/40';
                    } elseif (strpos($log['action'], 'PREMIUM') !== false) {
                        $badgeColor = 'bg-purple-950/80 text-purple-300 border-purple-500/40';
                    }
                ?>
                    <div class="p-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="inline-block px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold uppercase border <?= $badgeColor ?>">
                                <?= htmlspecialchars($log['action']) ?>
                            </span>
                            <span class="text-[10px] font-mono-calc text-slate-500">
                                <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-200"><?= htmlspecialchars($log['user_name'] ?: 'Visitante') ?> (ID: <?= $log['user_id'] ? '#' . $log['user_id'] : 'N/A' ?>)</span>
                            <span class="text-[10px] font-mono-calc text-slate-500"><?= htmlspecialchars($log['ip_address']) ?></span>
                        </div>
                        <div class="bg-slate-900/80 p-2.5 rounded-xl border border-slate-800 text-xs font-mono text-slate-300 break-words">
                            <?= htmlspecialchars($log['details']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <div class="p-8 text-center text-slate-500 text-sm">
                Nenhum registro de auditoria encontrado para os filtros selecionados.
            </div>
        <?php endif; ?>

    </div>

</div>
