<?php
/**
 * View de Autenticação / Login
 * Mobile First e Responsiva
 */

if (is_logged_in()) {
    header('Location: calculator.php');
    exit;
}
?>

<div class="max-w-md mx-auto px-4 py-8 sm:py-12">
    
    <div class="text-center mb-8">
        <div class="w-16 h-16 rounded-3xl bg-gradient-to-tr from-amber-500 to-emerald-400 flex items-center justify-center shadow-xl shadow-amber-500/20 mx-auto mb-3">
            <span class="text-3xl">🎲</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
            Entrar no Jogo
        </h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">
            Acesse sua conta para calcular a sorte e pontuar no ranking!
        </p>
    </div>

    <!-- Card de Formulário -->
    <div class="glass-panel rounded-3xl p-6 sm:p-8 border border-slate-800 shadow-2xl">
        <form action="actions/auth_login.php" method="POST" class="space-y-4">
            
            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                    Seu E-mail
                </label>
                <div class="relative">
                    <input type="email" id="email" name="email" required placeholder="exemplo@sorte.com" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition-colors">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                    Sua Senha (Bcrypt)
                </label>
                <div class="relative">
                    <input type="password" id="password" name="password" required placeholder="••••••••" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition-colors">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-sm uppercase tracking-wider shadow-lg shadow-amber-950/40 active:scale-95 transition-all mt-2">
                Entrar na Minha Conta
            </button>

        </form>

        <!-- Atalhos para Contas de Demonstração -->
        <div class="mt-6 pt-6 border-t border-slate-800">
            <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-2 text-center">
                Preenchimento Rápido (Demonstração):
            </div>
            <div class="grid grid-cols-2 gap-2">
                <button type="button" onclick="fillLogin('admin@sorte.com', '123456')" class="p-2 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-purple-500/30 text-[11px] text-purple-300 font-semibold transition-colors">
                    👑 Admin VIP
                </button>
                <button type="button" onclick="fillLogin('jogador@sorte.com', '123456')" class="p-2 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-emerald-500/30 text-[11px] text-emerald-300 font-semibold transition-colors">
                    🎮 Jogador Grátis
                </button>
            </div>
        </div>

        <div class="mt-6 text-center text-xs text-slate-400">
            Ainda não tem conta? 
            <a href="register.php" class="text-amber-400 hover:underline font-bold">Criar conta grátis</a>
        </div>
    </div>

</div>

<script>
function fillLogin(email, pass) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = pass;
    ToastManager.show('Credenciais de teste preenchidas!', 'info');
}
</script>
