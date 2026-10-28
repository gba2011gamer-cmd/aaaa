<?php
/**
 * View do Jogo Matemático: Desafio da Sorte
 * Objetivo: Atingir o número alvo com a operação da sorte
 */

$user = get_logged_user();
$topPlayers = [];

try {
    $db = getDB();
    // Top 5 Ranking de Jogadores
    $stmtRanking = $db->query("SELECT name, score, coins, is_premium FROM users ORDER BY score DESC LIMIT 5");
    $topPlayers = $stmtRanking->fetchAll();
} catch (Exception $e) {
    // Tratamento gracioso se banco desconectado
}
?>

<div class="max-w-4xl mx-auto px-4 py-6" id="game-app">

    <!-- Cabeçalho do Jogo -->
    <div class="text-center mb-6">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold mb-2">
            <span>🎯</span> Modo Desafio Matemático
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
            Desafio da Sorte
        </h1>
        <p class="text-xs sm:text-sm text-slate-400 max-w-md mx-auto mt-1">
            Resolva o enigma matemático: atinja o <strong>Número Alvo</strong> obrigatoriamente usando a <strong>Operação da Sorte</strong> da rodada!
        </p>
    </div>

    <!-- Card Principal do Desafio -->
    <div class="glass-panel rounded-3xl p-6 sm:p-8 border border-slate-800 shadow-2xl relative overflow-hidden mb-6">
        
        <div class="flex flex-col sm:flex-row items-center justify-between gap-6 pb-6 border-b border-slate-800">
            
            <!-- Número Alvo -->
            <div class="text-center sm:text-left">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Número Alvo</span>
                <div id="target-number-display" class="text-5xl sm:text-6xl font-black font-mono-calc text-emerald-400 mt-1 tracking-wider glow-emerald">
                    --
                </div>
            </div>

            <!-- Operação Sorteada Obrigatória -->
            <div class="flex items-center gap-3 bg-slate-900/90 p-4 rounded-2xl border border-amber-500/30 shadow-lg">
                <div class="w-12 h-12 rounded-xl bg-amber-500/20 border border-amber-500 flex items-center justify-center glow-amber shrink-0">
                    <span id="game-drawn-op" class="text-2xl font-black text-amber-300 font-mono-calc">?</span>
                </div>
                <div>
                    <div class="text-[10px] font-bold uppercase text-amber-400">Operação Obrigatória</div>
                    <div id="game-op-name" class="text-sm font-bold text-white">Carregando...</div>
                </div>
            </div>

            <!-- Pontuação Atual -->
            <div class="text-center sm:text-right">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Sua Pontuação</span>
                <div id="user-score-display" class="text-3xl sm:text-4xl font-black font-mono-calc text-amber-400 mt-1">
                    <?= (int)($user['score'] ?? 0) ?> pts
                </div>
            </div>

        </div>

        <!-- Área de Resolução Interativa -->
        <div class="py-6 space-y-4">
            
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 text-center sm:text-left">
                Monte sua Expressão Matemática:
            </label>

            <!-- Números Sugeridos (Clicáveis para Inserir) -->
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                <span class="text-xs text-slate-400 font-medium mr-1">Números da Sorte:</span>
                <div id="hint-numbers-container" class="flex flex-wrap gap-2">
                    <!-- Gerados via JS -->
                </div>
            </div>

            <!-- Campo de Entrada da Solução -->
            <div class="flex flex-col sm:flex-row gap-2.5">
                <input type="text" id="game-solution-input" placeholder="Ex: 12 * 3" class="flex-1 bg-slate-950 border-2 border-slate-700 rounded-2xl px-5 py-3.5 text-lg font-mono-calc text-emerald-300 placeholder-slate-600 focus:outline-none focus:border-emerald-500 text-center sm:text-left">
                
                <button type="button" id="btn-submit-solution" class="px-7 py-3.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black text-sm uppercase tracking-wider shadow-lg shadow-emerald-950/50 active:scale-95 transition-all">
                    Verificar Resposta
                </button>
            </div>

        </div>

        <!-- Rodapé do Card do Jogo -->
        <div class="pt-4 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
            <button type="button" id="btn-skip-challenge" class="hover:text-amber-400 transition-colors font-medium">
                ⏭️ Pular este desafio
            </button>
            <span>Vitória: <strong>+50 pts</strong> e <strong>+25 moedas</strong></span>
        </div>

    </div>

    <!-- Ranking dos Melhores Jogadores -->
    <div class="glass-panel rounded-3xl p-5 sm:p-6 border border-slate-800 shadow-xl">
        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-800">
            <span class="text-xl">🏆</span>
            <h3 class="text-base font-bold text-white tracking-wide">Mestres do Cálculo (Ranking)</h3>
        </div>

        <div class="space-y-2.5">
            <?php 
            $pos = 1;
            foreach ($topPlayers as $top): 
                $medal = $pos === 1 ? '🥇' : ($pos === 2 ? '🥈' : ($pos === 3 ? '🥉' : '#' . $pos));
            ?>
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-900/60 border border-slate-800 hover:bg-slate-800/40 transition-colors">
                    <div class="flex items-center gap-3">
                        <span class="w-8 text-center text-sm font-bold font-mono-calc text-amber-400"><?= $medal ?></span>
                        <div>
                            <div class="text-xs sm:text-sm font-bold text-slate-200 flex items-center gap-1.5">
                                <span><?= htmlspecialchars($top['name']) ?></span>
                                <?php if ($top['is_premium']): ?>
                                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-purple-900 text-purple-300 font-bold">VIP</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs sm:text-sm font-extrabold text-emerald-400 font-mono-calc"><?= number_format($top['score']) ?> pts</div>
                        <div class="text-[10px] text-amber-400 font-semibold font-mono-calc">🪙 <?= number_format($top['coins']) ?></div>
                    </div>
                </div>
            <?php 
                $pos++;
            endforeach; 
            ?>
        </div>
    </div>

</div>

<!-- Script Específico do Jogo Matemático -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    let currentChallengeId = null;
    let targetNumber = null;
    let drawnOp = null;

    const targetEl = document.getElementById('target-number-display');
    const drawnOpEl = document.getElementById('game-drawn-op');
    const opNameEl = document.getElementById('game-op-name');
    const hintsContainer = document.getElementById('hint-numbers-container');
    const solutionInput = document.getElementById('game-solution-input');
    const btnSubmit = document.getElementById('btn-submit-solution');
    const btnSkip = document.getElementById('btn-skip-challenge');
    const userScoreEl = document.getElementById('user-score-display');
    const headerCoinsEl = document.getElementById('header-coins-val');

    const opLabels = { '+': 'Adição (+)', '-': 'Subtração (-)', '*': 'Multiplicação (×)', '/': 'Divisão (÷)' };

    function fetchNewChallenge() {
        targetEl.innerText = '...';
        drawnOpEl.innerText = '?';
        opNameEl.innerText = 'Sorteando...';
        hintsContainer.innerHTML = '';
        solutionInput.value = '';

        const formData = new FormData();
        formData.append('action', 'new_challenge');

        fetch('actions/game_challenge.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                currentChallengeId = data.challenge_id;
                targetNumber = data.target;
                drawnOp = data.drawn_operation;

                targetEl.innerText = targetNumber;
                drawnOpEl.innerText = drawnOp;
                opNameEl.innerText = opLabels[drawnOp] || drawnOp;

                // Cria botões de números da sorte
                data.hint_numbers.forEach(num => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-mono-calc font-bold text-amber-300 transition-transform active:scale-90';
                    btn.innerText = num;
                    btn.onclick = () => {
                        SoundFX.btnClick();
                        solutionInput.value += (solutionInput.value ? ' ' : '') + num;
                        solutionInput.focus();
                    };
                    hintsContainer.appendChild(btn);
                });

                // Botão da própria operação para facilidade
                const opBtn = document.createElement('button');
                opBtn.type = 'button';
                opBtn.className = 'px-3 py-1.5 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 border border-amber-500 text-xs font-mono-calc font-black text-amber-300 transition-transform active:scale-90 glow-amber';
                opBtn.innerText = drawnOp;
                opBtn.onclick = () => {
                    SoundFX.btnClick();
                    solutionInput.value += ' ' + drawnOp + ' ';
                    solutionInput.focus();
                };
                hintsContainer.appendChild(opBtn);
            } else {
                ToastManager.show(data.message, 'error');
            }
        })
        .catch(err => {
            ToastManager.show('Erro ao obter desafio.', 'error');
        });
    }

    // Submissão da Solução
    btnSubmit.addEventListener('click', () => {
        const sol = solutionInput.value.trim();
        if (!sol) {
            ToastManager.show('Digite sua equação para resolver o desafio!', 'warning');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'solve');
        formData.append('challenge_id', currentChallengeId);
        formData.append('solution', sol);

        fetch('actions/game_challenge.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                SoundFX.winReward();
                ModalManager.open({
                    title: '🎉 Resposta Correta!',
                    body: `
                        <div class="text-center py-3">
                            <div class="text-4xl mb-2">⭐ 🎯 🏆</div>
                            <h4 class="text-lg font-bold text-white">Você é o Mestre da Sorte!</h4>
                            <p class="text-xs text-slate-300 mt-1">Você atingiu o número <strong>${data.target}</strong> com sucesso!</p>
                            <div class="flex items-center justify-center gap-3 mt-4">
                                <span class="px-3 py-1 rounded-xl bg-emerald-500/20 text-emerald-400 font-bold text-xs border border-emerald-500/30">+${data.points_earned} Pontos</span>
                                <span class="px-3 py-1 rounded-xl bg-amber-500/20 text-amber-300 font-bold text-xs border border-amber-500/30">+${data.coins_earned} Moedas</span>
                            </div>
                        </div>
                    `,
                    confirmText: 'Próximo Desafio ➔',
                    onConfirm: () => {
                        fetchNewChallenge();
                    }
                });

                if (userScoreEl) userScoreEl.innerText = `${data.new_score} pts`;
                if (headerCoinsEl) headerCoinsEl.innerText = data.new_coins;
            } else {
                SoundFX.lockedError();
                ToastManager.show(data.message, 'error');
            }
        })
        .catch(err => {
            ToastManager.show('Erro ao verificar resposta.', 'error');
        });
    });

    btnSkip.addEventListener('click', () => {
        SoundFX.btnClick();
        fetchNewChallenge();
        ToastManager.show('Desafio pulado. Novo enigma sorteado!', 'info');
    });

    // Inicia primeiro desafio
    fetchNewChallenge();
});
</script>
