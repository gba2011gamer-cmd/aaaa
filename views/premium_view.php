<?php
/**
 * View da Loja da Sorte: Desbloqueio via Anúncios & Passe VIP Premium
 * Mobile-First e Responsiva
 */

$user = get_logged_user();
$isVip = !empty($user['is_premium']);
$unlocked = $user['unlocked_operations'] ?? ['+'];
$currentOp = $user['current_drawn_op'] ?? '+';

$operations = [
    '+' => ['nome' => 'Adição', 'simbolo' => '+', 'desc' => 'Some valores sem limites.'],
    '-' => ['nome' => 'Subtração', 'simbolo' => '-', 'desc' => 'Subtraia números para ajustar totais.'],
    '*' => ['nome' => 'Multiplicação', 'simbolo' => '×', 'desc' => 'Multiplique resultados e desafios.'],
    '/' => ['nome' => 'Divisão', 'simbolo' => '÷', 'desc' => 'Divida e resolva frações e proporções.']
];
?>

<div class="max-w-4xl mx-auto px-4 py-6" id="premium-store-app">

    <!-- Cabeçalho -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-500/10 border border-purple-500/30 text-purple-400 text-xs font-bold mb-2">
            <span>⭐</span> Centro de Desbloqueios da Sorte
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
            Liberte Todas as Operações
        </h1>
        <p class="text-xs sm:text-sm text-slate-400 max-w-lg mx-auto mt-1">
            Escolha como desbloquear: assista a anúncios rápidos de 5 segundos ou ative o Passe VIP para acesso total ilimitado!
        </p>
    </div>

    <!-- Banner de Status Atual -->
    <div class="glass-panel rounded-2xl p-4 sm:p-5 mb-8 border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl <?= $isVip ? 'bg-purple-600/30 border-2 border-purple-500 text-purple-300 glow-purple' : 'bg-slate-800 border border-slate-700 text-slate-400' ?> flex items-center justify-center text-xl shrink-0">
                <?= $isVip ? '👑' : '🎮' ?>
            </div>
            <div>
                <div class="text-xs font-semibold text-slate-400">Seu Status Atual:</div>
                <div class="text-base font-bold <?= $isVip ? 'text-purple-300' : 'text-slate-200' ?>">
                    <?= $isVip ? 'Assinante VIP Premium (Acesso Total)' : 'Jogador Gratuito (Operação Sorteada: ' . $currentOp . ')' ?>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="px-3 py-1.5 rounded-xl bg-slate-900 border border-amber-500/30 text-xs font-bold text-amber-300 flex items-center gap-1.5">
                <span>🪙 Moedas:</span>
                <span id="store-coins-val" class="font-mono-calc"><?= (int)($user['coins'] ?? 0) ?></span>
            </div>
            <div class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-700 text-xs font-bold text-slate-300 flex items-center gap-1.5">
                <span>📺 Anúncios:</span>
                <span class="font-mono-calc"><?= (int)($user['ads_watched'] ?? 0) ?></span>
            </div>
        </div>
    </div>

    <!-- Seção 1: Passe VIP Premium -->
    <div class="mb-10">
        <div class="relative rounded-3xl p-6 sm:p-8 bg-gradient-to-br from-purple-950/80 via-slate-900/95 to-slate-950 border-2 border-purple-500/40 shadow-2xl overflow-hidden glow-purple">
            <div class="absolute -right-8 -top-8 w-40 h-40 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="flex flex-col md:flex-row items-center justify-between gap-6 relative z-10">
                <div class="space-y-3 text-center md:text-left">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-purple-500/20 text-purple-300 border border-purple-500/40">
                        ⭐ Oferta Especial VIP
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black text-white">
                        Passe Calculando a Sorte VIP
                    </h2>
                    <ul class="text-xs sm:text-sm text-slate-300 space-y-1.5 max-w-md">
                        <li class="flex items-center gap-2">
                            <span class="text-emerald-400 font-bold">✓</span> Todas as 4 operações (+, -, ×, ÷) liberadas permanentemente
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-emerald-400 font-bold">✓</span> Zero bloqueios de sorte ou anúncios obrigatórios
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-emerald-400 font-bold">✓</span> Emblema Dourado VIP no Ranking e Histórico
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-emerald-400 font-bold">✓</span> Bônus instantâneo de <strong>+500 Moedas</strong>
                        </li>
                    </ul>
                </div>

                <div class="w-full md:w-auto text-center shrink-0">
                    <button type="button" id="btn-toggle-vip" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-gradient-to-r from-purple-600 via-indigo-600 to-purple-600 hover:from-purple-500 hover:to-indigo-500 text-white font-black text-base shadow-xl shadow-purple-950/60 active:scale-95 transition-all">
                        <?= $isVip ? 'Alternar/Gerenciar VIP' : '⚡ Ativar Passe VIP Agora' ?>
                    </button>
                    <div class="text-[10px] text-slate-400 mt-2">Ativação instantânea via sistema</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Seção 2: Desbloqueio Individual por Anúncios (Grátis) -->
    <div>
        <div class="flex items-center gap-2 mb-4">
            <span class="text-xl">📺</span>
            <h3 class="text-lg font-bold text-white tracking-wide">
                Desbloqueio Gratuito por Anúncio (5s)
            </h3>
        </div>
        <p class="text-xs text-slate-400 mb-5">
            Prefere não pagar? Assista a um anúncio de 5 segundos para liberar qualquer uma das operações e ainda ganhar +20 moedas!
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php foreach ($operations as $opKey => $opData): 
                $isLiberada = $isVip || in_array($opKey, $unlocked) || $opKey === $currentOp;
            ?>
                <div class="glass-panel p-5 rounded-2xl border <?= $isLiberada ? 'border-emerald-500/30 bg-slate-900/40' : 'border-slate-800' ?> flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl <?= $isLiberada ? 'bg-emerald-500/10 border border-emerald-500/40 text-emerald-400' : 'bg-slate-800 border border-slate-700 text-slate-400' ?> flex items-center justify-center font-mono-calc font-black text-2xl shrink-0">
                            <?= $opData['simbolo'] ?>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-white flex items-center gap-2">
                                <span><?= $opData['nome'] ?></span>
                                <?php if ($isLiberada): ?>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold">Desbloqueada</span>
                                <?php else: ?>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 border border-slate-700">Bloqueada</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-xs text-slate-400 mt-0.5"><?= $opData['desc'] ?></div>
                        </div>
                    </div>

                    <div>
                        <?php if ($isLiberada): ?>
                            <button type="button" disabled class="px-3.5 py-2 rounded-xl bg-slate-800/80 text-emerald-400 text-xs font-bold cursor-default border border-slate-700">
                                ✓ Ativa
                            </button>
                        <?php else: ?>
                            <button type="button" onclick="watchAdForOp('<?= $opKey ?>')" class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-md shadow-amber-950/40 active:scale-95 transition-all flex items-center gap-1.5 whitespace-nowrap">
                                <span>📺 Ver 5s</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- Script Específico da Loja VIP -->
<script>
function watchAdForOp(op) {
    ModalManager.openAdRewardModal(op, () => {
        const formData = new FormData();
        formData.append('operation', op);

        fetch('actions/calc_unlock_ad.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                ToastManager.show(data.message, 'success');
                setTimeout(() => window.location.reload(), 1200);
            } else {
                ToastManager.show(data.message, 'error');
            }
        })
        .catch(err => {
            ToastManager.show('Erro ao processar anúncio.', 'error');
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const btnVip = document.getElementById('btn-toggle-vip');
    if (btnVip) {
        btnVip.addEventListener('click', () => {
            SoundFX.luckyRoll();

            ModalManager.open({
                title: '⚡ Confirmação do Passe VIP',
                body: `
                    <div class="text-center py-2">
                        <div class="text-4xl mb-2">⭐ 👑 ⭐</div>
                        <h4 class="text-base font-bold text-white mb-1">Deseja ativar seu Passe VIP?</h4>
                        <p class="text-xs text-slate-300">
                            Você desbloqueará instantaneamente todas as 4 operações da calculadora e do jogo matemático permanentemente!
                        </p>
                    </div>
                `,
                confirmText: 'Confirmar Ativação VIP',
                cancelText: 'Voltar',
                onConfirm: () => {
                    const formData = new FormData();
                    formData.append('action_type', 'toggle');

                    fetch('actions/calc_upgrade_premium.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            SoundFX.winReward();
                            ToastManager.show(data.message, 'success');
                            setTimeout(() => window.location.reload(), 1200);
                        } else {
                            ToastManager.show(data.message, 'error');
                        }
                    })
                    .catch(err => {
                        ToastManager.show('Erro ao alterar status VIP.', 'error');
                    });
                }
            });
        });
    }
});
</script>
