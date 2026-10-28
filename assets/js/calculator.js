/**
 * Calculando a Sorte - Lógica da Calculadora do Jogo
 * Integração Mobile-First com Sorteios e Operações Bloqueadas
 */

document.addEventListener('DOMContentLoaded', () => {
    const displayExpr = document.getElementById('calc-display-expression');
    const displayResult = document.getElementById('calc-display-result');
    const drawnBadge = document.getElementById('drawn-op-badge');
    const rollButton = document.getElementById('btn-roll-luck');
    const userCoinsEl = document.getElementById('header-coins-val');

    // Carrega dados iniciais do usuário passados via atributos data
    const calcContainer = document.getElementById('calculator-app');
    if (!calcContainer) return;

    let isPremium = calcContainer.dataset.isPremium === '1';
    let currentDrawnOp = calcContainer.dataset.drawnOp || '+';
    let unlockedOps = [];
    try {
        unlockedOps = JSON.parse(calcContainer.dataset.unlocked || '["+"]');
    } catch (e) {
        unlockedOps = [currentDrawnOp];
    }

    let expression = '';
    let lastCalculated = false;

    // Atualiza visual dos botões de operadores (bloqueado/liberado)
    function updateOperatorButtonsUI() {
        const opBtns = document.querySelectorAll('.calc-op-btn');
        opBtns.forEach(btn => {
            const op = btn.dataset.op;
            const isUnlocked = isPremium || unlockedOps.includes(op) || op === currentDrawnOp;
            const lockIcon = btn.querySelector('.lock-indicator');

            if (isUnlocked) {
                btn.classList.remove('opacity-60', 'border-dashed', 'border-slate-700');
                btn.classList.add('border-solid');
                if (op === currentDrawnOp) {
                    btn.classList.add('bg-amber-500/20', 'border-amber-500', 'text-amber-300', 'glow-amber');
                } else {
                    btn.classList.remove('glow-amber');
                }
                if (lockIcon) lockIcon.classList.add('hidden');
            } else {
                btn.classList.add('opacity-60', 'border-dashed', 'border-slate-700');
                btn.classList.remove('bg-amber-500/20', 'border-amber-500', 'text-amber-300', 'glow-amber');
                if (lockIcon) lockIcon.classList.remove('hidden');
            }
        });

        if (drawnBadge) {
            drawnBadge.innerText = currentDrawnOp;
        }
    }

    updateOperatorButtonsUI();

    // Manipulação do teclado numérico da calculadora
    document.querySelectorAll('.calc-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            SoundFX.btnClick();
            const action = btn.dataset.action;
            const val = btn.dataset.val;
            const op = btn.dataset.op;

            if (action === 'clear') {
                expression = '';
                displayExpr.innerText = '';
                displayResult.innerText = '0';
                lastCalculated = false;
                return;
            }

            if (action === 'backspace') {
                if (expression.length > 0) {
                    expression = expression.slice(0, -1);
                    displayExpr.innerText = expression;
                }
                return;
            }

            if (action === 'equals') {
                submitCalculation();
                return;
            }

            // Tratamento de operadores (+, -, *, /)
            if (op) {
                handleOperatorClick(op);
                return;
            }

            // Tratamento de números e ponto decimal
            if (val !== undefined) {
                if (lastCalculated) {
                    expression = '';
                    lastCalculated = false;
                }
                expression += val;
                displayExpr.innerText = expression;
            }
        });
    });

    // Tratamento de clique em operador
    function handleOperatorClick(op) {
        const isAllowed = isPremium || unlockedOps.includes(op) || op === currentDrawnOp;

        if (!isAllowed) {
            SoundFX.lockedError();
            promptUnlockModal(op);
            return;
        }

        // Se permitido, insere operador na expressão
        if (expression === '' && displayResult.innerText !== '0') {
            expression = displayResult.innerText;
        }

        // Evita operadores repetidos seguidos
        const lastChar = expression.slice(-1);
        if (['+', '-', '*', '/'].includes(lastChar)) {
            expression = expression.slice(0, -1);
        }

        expression += op;
        displayExpr.innerText = expression;
        lastCalculated = false;
    }

    // Modal customizado para desbloqueio (sem alerts nativos)
    function promptUnlockModal(op) {
        const opLabels = { '+': 'Adição (+)', '-': 'Subtração (-)', '*': 'Multiplicação (×)', '/': 'Divisão (÷)' };
        const label = opLabels[op] || op;

        ModalManager.open({
            title: `🔒 Operação ${label} Bloqueada!`,
            body: `
                <div class="text-center py-2">
                    <p class="text-sm text-slate-300 mb-4">
                        Pelas regras do <strong>Calculando a Sorte</strong>, você só pode usar números e as operações que a Sorte te deu!
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3 text-left">
                        <div class="p-3 bg-slate-800/80 rounded-xl border border-amber-500/30">
                            <div class="text-xs font-bold text-amber-400 uppercase">Opção 1 (Grátis)</div>
                            <div class="text-sm font-semibold text-white mt-1">Assistir Anúncio</div>
                            <div class="text-xs text-slate-400 mt-0.5">Assista a 5 segundos de propaganda para liberar e ganhar +20 moedas!</div>
                        </div>
                        <div class="p-3 bg-slate-800/80 rounded-xl border border-purple-500/30">
                            <div class="text-xs font-bold text-purple-400 uppercase">Opção 2 (VIP)</div>
                            <div class="text-sm font-semibold text-white mt-1">Passe Premium</div>
                            <div class="text-xs text-slate-400 mt-0.5">Desbloqueia permanentemente todas as 4 operações sem limites!</div>
                        </div>
                    </div>
                </div>
            `,
            confirmText: '📺 Assistir Anúncio (5s)',
            cancelText: '⭐ Ver Passe VIP',
            onConfirm: () => {
                // Dispara o anúncio recompensado de 5s
                ModalManager.openAdRewardModal(op, () => {
                    executeUnlockAd(op);
                });
            }
        });

        // Configura o botão de cancelar para direcionar ao Premium
        const btnCancel = document.getElementById('custom-modal-cancel');
        if (btnCancel) {
            btnCancel.onclick = () => {
                ModalManager.close();
                window.location.href = 'premium.php';
            };
        }
    }

    // Chamada AJAX para registrar anúncio assistido e desbloqueio
    function executeUnlockAd(op) {
        const formData = new FormData();
        formData.append('operation', op);

        fetch('actions/calc_unlock_ad.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                unlockedOps = data.unlocked;
                updateOperatorButtonsUI();
                if (userCoinsEl && data.coins) userCoinsEl.innerText = data.coins;
                ToastManager.show(data.message, 'success');
                // Insere a operação recém-desbloqueada
                expression += op;
                displayExpr.innerText = expression;
            } else {
                ToastManager.show(data.message, 'error');
            }
        })
        .catch(err => {
            ToastManager.show('Erro de comunicação com o servidor.', 'error');
        });
    }

    // Submissão do cálculo para validação segura no backend
    function submitCalculation() {
        if (!expression) return;

        displayResult.innerText = '...';

        const formData = new FormData();
        formData.append('expression', expression);

        fetch('actions/game_calculate.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                SoundFX.winReward();
                displayResult.innerText = data.result;
                lastCalculated = true;
                if (userCoinsEl && data.coins) userCoinsEl.innerText = data.coins;
                ToastManager.show(data.message, 'success');

                // Adiciona na fita de histórico lateral
                appendHistoryTape(data.expression, data.result);
            } else {
                displayResult.innerText = 'ERRO';
                SoundFX.lockedError();
                if (data.blocked) {
                    promptUnlockModal(data.blocked_op);
                } else {
                    ToastManager.show(data.message, 'error');
                }
            }
        })
        .catch(err => {
            displayResult.innerText = 'ERRO';
            ToastManager.show('Falha ao processar cálculo no servidor.', 'error');
        });
    }

    // Adiciona na fita visual de histórico da calculadora
    function appendHistoryTape(expr, res) {
        const tape = document.getElementById('calc-history-tape');
        if (!tape) return;

        const row = document.createElement('div');
        row.className = 'py-2 px-3 bg-slate-800/50 rounded-lg border border-slate-700/50 text-right animate-pulse';
        row.innerHTML = `
            <div class="text-xs text-slate-400 font-mono">${expr} =</div>
            <div class="text-base font-bold text-emerald-400 font-mono">${res}</div>
        `;
        tape.insertBefore(row, tape.firstChild);
    }

    // Botão de Sortear Nova Operação da Sorte
    if (rollButton) {
        rollButton.addEventListener('click', () => {
            SoundFX.luckyRoll();
            rollButton.classList.add('animate-spin-luck');
            rollButton.disabled = true;

            fetch('actions/calc_roll_operation.php', {
                method: 'POST'
            })
            .then(res => res.json())
            .then(data => {
                setTimeout(() => {
                    rollButton.classList.remove('animate-spin-luck');
                    rollButton.disabled = false;

                    if (data.success) {
                        currentDrawnOp = data.operation;
                        unlockedOps = data.unlocked;
                        updateOperatorButtonsUI();
                        SoundFX.winReward();
                        ToastManager.show(data.message, 'success');
                    } else {
                        ToastManager.show(data.message, 'error');
                    }
                }, 600);
            })
            .catch(err => {
                rollButton.classList.remove('animate-spin-luck');
                rollButton.disabled = false;
                ToastManager.show('Erro ao girar a sorte.', 'error');
            });
        });
    }
});
