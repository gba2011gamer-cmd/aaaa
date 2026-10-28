<?php
/**
 * View da Calculadora da Sorte (Mobile First)
 * Projeto: Calculando a Sorte
 */

$user = get_logged_user();
$currentOp = $user['current_drawn_op'] ?? '+';
$unlocked = $user['unlocked_operations'] ?? ['+'];
$isVip = !empty($user['is_premium']);

$opLabels = [
    '+' => ['nome' => 'Adição', 'simbolo' => '+'],
    '-' => ['nome' => 'Subtração', 'simbolo' => '-'],
    '*' => ['nome' => 'Multiplicação', 'simbolo' => '×'],
    '/' => ['nome' => 'Divisão', 'simbolo' => '÷']
];
?>

<div class="max-w-4xl mx-auto px-4 py-6" id="calculator-app"
     data-is-premium="<?= $isVip ? '1' : '0' ?>"
     data-drawn-op="<?= htmlspecialchars($currentOp) ?>"
     data-unlocked="<?= htmlspecialchars(json_encode($unlocked)) ?>">

    <!-- Banner Superior: Operação da Sorte Sorteada -->
    <div class="glass-card-lucky rounded-2xl p-4 sm:p-5 mb-6 shadow-xl relative overflow-hidden">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            
            <div class="flex items-center gap-3.5 w-full sm:w-auto">
                <div class="w-14 h-14 rounded-2xl bg-amber-500/20 border-2 border-amber-500 flex items-center justify-center glow-amber shrink-0">
                    <span id="drawn-op-badge" class="text-3xl font-black text-amber-300 font-mono-calc"><?= htmlspecialchars($currentOp) ?></span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-400">Operação da Rodada</span>
                        <?php if ($isVip): ?>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-purple-600 text-white">VIP ATIVO</span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300 border border-slate-700">GRÁTIS</span>
                        <?php endif; ?>
                    </div>
                    <h2 class="text-lg font-bold text-white leading-tight">
                        <?= $opLabels[$currentOp]['nome'] ?? 'Matemática' ?> está Sorteada!
                    </h2>
                    <p class="text-xs text-slate-400">
                        <?= $isVip ? 'Como assinante VIP, todas as operações estão liberadas simultaneamente!' : 'Você pode usar todos os números livremente. Outras operações exigem anúncio ou Passe VIP.' ?>
                    </p>
                </div>
            </div>

            <!-- Botão de Girar Nova Sorte -->
            <button id="btn-roll-luck" type="button" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-extrabold text-sm flex items-center justify-center gap-2 shadow-lg shadow-amber-950/40 active:scale-95 transition-all">
                <span class="text-lg">🎲</span>
                <span>Girar Sorte Novamente</span>
            </button>

        </div>

        <!-- Painel de Status das 4 Operações -->
        <div class="grid grid-cols-4 gap-2 mt-4 pt-4 border-t border-slate-800">
            <?php foreach (['+', '-', '*', '/'] as $op): 
                $unlockedThis = $isVip || in_array($op, $unlocked) || $op === $currentOp;
            ?>
                <div class="p-2 rounded-xl text-center border <?= $unlockedThis ? 'bg-emerald-950/40 border-emerald-500/40 text-emerald-300' : 'bg-slate-900/60 border-slate-800 text-slate-500' ?>">
                    <div class="text-base font-black font-mono-calc"><?= $opLabels[$op]['simbolo'] ?></div>
                    <div class="text-[10px] font-bold uppercase mt-0.5"><?= $unlockedThis ? '🔓 Livre' : '🔒 Bloqueada' ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Layout Grid: Calculadora + Fita de Histórico Lateral -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Calculadora Principal (Mobile First) -->
        <div class="lg:col-span-2 glass-panel rounded-3xl p-5 sm:p-7 shadow-2xl border border-slate-800">
            
            <!-- Visor Digital LCD -->
            <div class="bg-slate-950 rounded-2xl p-4 sm:p-5 border border-slate-800/80 mb-5 text-right shadow-inner">
                <div id="calc-display-expression" class="h-6 text-sm sm:text-base font-mono-calc text-slate-400 overflow-x-auto whitespace-nowrap"></div>
                <div id="calc-display-result" class="text-3xl sm:text-4xl font-black font-mono-calc text-emerald-400 tracking-wider truncate mt-1">0</div>
            </div>

            <!-- Teclado da Calculadora -->
            <div class="grid grid-cols-4 gap-2.5 sm:gap-3.5">

                <!-- Linha 1: Funções de Limpeza e Operador / -->
                <button type="button" data-action="clear" class="calc-btn py-4 sm:py-5 rounded-2xl bg-rose-950/60 hover:bg-rose-900/80 border border-rose-800/50 text-rose-300 font-extrabold text-base sm:text-lg">
                    AC
                </button>
                <button type="button" data-action="backspace" class="calc-btn py-4 sm:py-5 rounded-2xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 font-bold text-base sm:text-lg flex items-center justify-center">
                    ⌫
                </button>
                <button type="button" data-action="clear" class="calc-btn py-4 sm:py-5 rounded-2xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 font-bold text-base sm:text-lg">
                    C
                </button>
                <button type="button" data-op="/" class="calc-btn calc-op-btn relative py-4 sm:py-5 rounded-2xl bg-slate-800 hover:bg-slate-700 border text-slate-200 font-black text-xl font-mono-calc">
                    ÷
                    <span class="lock-indicator absolute top-1.5 right-2 text-xs text-amber-400">🔒</span>
                </button>

                <!-- Linha 2: 7, 8, 9 e Operador * -->
                <button type="button" data-val="7" class="calc-btn py-4 sm:py-5 rounded-2xl bg-slate-900/90 hover:bg-slate-800 border border-slate-800 text-white font-bold text-xl sm:text-2xl font-mono-calc">
                    7
                </button>
                <button type="button" data-val="8" class="calc-btn py-4 sm:py-5 rounded-2xl bg-slate-900/90 hover:bg-slate-800 border border-slate-800 text-white font-bold text-xl sm:text-2xl font-mono-calc">
                    8
                </button>
                <button type="button" data-val="9" class="calc-btn py-4 sm:py-5 rounded-2xl bg-slate-900/90 hover:bg-slate-800 border border-slate-800 text-white font-bold text-xl sm:text-2xl font-mono-calc">
                    9
                </button>
                <button type="button" data-op="*" class="calc-btn calc-op-btn relative py-4 sm:py-5 rounded-2xl bg-slate-800 hover:bg-slate-700 border text-slate-200 font-black text-xl font-mono-calc">
                    ×
                    <span class="lock-indicator absolute top-1.5 right-2 text-xs text-amber-400">🔒</span>
                </button>

                <!-- Linha 3: 4, 5, 6 e Operador - -->
                <button type="button" data-val="4" class="calc-btn py-4 sm:py-5 rounded-2xl bg-slate-900/90 hover:bg-slate-800 border border-slate-800 text-white font-bold text-xl sm:text-2xl font-mono-calc">
                    4
                </button>
                <button type="button" data-val="5" class="calc-btn py-4 sm:py-5 rounded-2xl bg-slate-900/90 hover:bg-slate-800 border border-slate-800 text-white font-bold text-xl sm:text-2xl font-mono-calc">
                    5
                </button>
                <button type="button" data-val="6" class="calc-btn py-4 sm:py-5 rounded-2xl bg-slate-900/90 hover:bg-slate-800 border border-slate-800 text-white font-bold text-xl sm:text-2xl font-mono-calc">
                    6
                </button>
                <button type="button" data-op="-" class="calc-btn calc-op-btn relative py-4 sm:py-5 rounded-2xl bg-slate-800 hover:bg-slate-700 border text-slate-200 font-black text-xl font-mono-calc">
                    -
                    <span class="lock-indicator absolute top-1.5 right-2 text-xs text-amber-400">🔒</span>
                </button>

                <!-- Linha 4: 1, 2, 3 e Operador + -->
                <button type="button" data-val="1" class="calc-btn py-4 sm:py-5 rounded-2xl bg-slate-900/90 hover:bg-slate-800 border border-slate-800 text-white font-bold text-xl sm:text-2xl font-mono-calc">
                    1
                </button>
                <button type="button" data-val="2" class="calc-btn py-4 sm:py-5 rounded-2xl bg-slate-900/90 hover:bg-slate-800 border border-slate-800 text-white font-bold text-xl sm:text-2xl font-mono-calc">
                    2
                </button>
                <button type="button" data-val="3" class="calc-btn py-4 sm:py-5 rounded-2xl bg-slate-900/90 hover:bg-slate-800 border border-slate-800 text-white font-bold text-xl sm:text-2xl font-mono-calc">
                    3
                </button>
                <button type="button" data-op="+" class="calc-btn calc-op-btn relative py-4 sm:py-5 rounded-2xl bg-slate-800 hover:bg-slate-700 border text-slate-200 font-black text-xl font-mono-calc">
                    +
                    <span class="lock-indicator absolute top-1.5 right-2 text-xs text-amber-400">🔒</span>
                </button>

                <!-- Linha 5: 0, Ponto Decimal e Igual (=) -->
                <button type="button" data-val="0" class="col-span-2 calc-btn py-4 sm:py-5 rounded-2xl bg-slate-900/90 hover:bg-slate-800 border border-slate-800 text-white font-bold text-xl sm:text-2xl font-mono-calc">
                    0
                </button>
                <button type="button" data-val="." class="calc-btn py-4 sm:py-5 rounded-2xl bg-slate-900/90 hover:bg-slate-800 border border-slate-800 text-white font-black text-xl sm:text-2xl font-mono-calc">
                    .
                </button>
                <button type="button" data-action="equals" class="calc-btn py-4 sm:py-5 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black text-2xl font-mono-calc shadow-lg shadow-emerald-950/40">
                    =
                </button>

            </div>

        </div>

        <!-- Fita de Histórico Lateral Recente -->
        <div class="glass-panel rounded-3xl p-5 shadow-xl border border-slate-800 flex flex-col h-full">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-amber-400">🧾</span>
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Fita de Cálculos</h3>
                </div>
                <a href="history.php" class="text-xs text-amber-400 hover:underline font-semibold">Ver Tudo</a>
            </div>

            <div id="calc-history-tape" class="flex-1 space-y-2 overflow-y-auto max-h-[380px] pr-1">
                <?php
                try {
                    $db = getDB();
                    $stmtRecent = $db->prepare("SELECT expression, result, created_at FROM calculations WHERE user_id = :uid ORDER BY id DESC LIMIT 6");
                    $stmtRecent->execute([':uid' => $user['id']]);
                    $recentCalcs = $stmtRecent->fetchAll();

                    if (!empty($recentCalcs)):
                        foreach ($recentCalcs as $calc):
                ?>
                            <div class="py-2 px-3 bg-slate-900/70 rounded-xl border border-slate-800 text-right">
                                <div class="text-xs text-slate-400 font-mono-calc"><?= htmlspecialchars($calc['expression']) ?> =</div>
                                <div class="text-base font-bold text-emerald-400 font-mono-calc"><?= htmlspecialchars($calc['result']) ?></div>
                            </div>
                <?php
                        endforeach;
                    else:
                ?>
                        <div class="text-center py-8 text-xs text-slate-500">
                            Nenhum cálculo registrado ainda.<br>Gire a sorte e faça sua primeira conta!
                        </div>
                <?php
                    endif;
                } catch (Exception $e) {
                    echo '<div class="text-xs text-rose-400">Histórico temporariamente indisponível.</div>';
                }
                ?>
            </div>

            <div class="pt-4 mt-auto border-t border-slate-800">
                <div class="bg-amber-500/10 rounded-xl p-3 border border-amber-500/20 text-xs text-amber-300">
                    💡 <strong>Dica da Sorte:</strong> Cada cálculo correto rende <strong>+5 moedas</strong>. Se a operação desejada estiver com o cadeado 🔒, basta clicar nela para assistir a um anúncio de 5s!
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Script Específico da Calculadora -->
<script src="assets/js/calculator.js"></script>
