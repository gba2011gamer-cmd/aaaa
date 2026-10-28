<?php
/**
 * View de Cadastro de Novo Jogador
 * Mobile First e Responsiva
 */

if (is_logged_in()) {
    header('Location: calculator.php');
    exit;
}
?>

<div class="max-w-md mx-auto px-4 py-8 sm:py-12">
    
    <div class="text-center mb-8">
        <div class="w-16 h-16 rounded-3xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center shadow-xl shadow-emerald-500/20 mx-auto mb-3">
            <span class="text-3xl">✨</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
            Criar Nova Conta
        </h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">
            Cadastre-se para receber sua primeira operação da sorte e 100 moedas grátis!
        </p>
    </div>

    <!-- Card de Formulário -->
    <div class="glass-panel rounded-3xl p-6 sm:p-8 border border-slate-800 shadow-2xl">
        <form action="actions/auth_register.php" method="POST" class="space-y-4">
            
            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                    Nome Completo ou Apelido
                </label>
                <input type="text" id="name" name="name" required placeholder="Ex: Lucas Ferreira" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 transition-colors">
            </div>

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                    Seu E-mail
                </label>
                <input type="email" id="email" name="email" required placeholder="lucas@exemplo.com" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 transition-colors">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                    Senha (Criptografada com Bcrypt)
                </label>
                <input type="password" id="password" name="password" required minlength="6" placeholder="Mínimo 6 caracteres" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 transition-colors">
            </div>

            <div>
                <label for="password_confirm" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                    Confirmar Senha
                </label>
                <input type="password" id="password_confirm" name="password_confirm" required minlength="6" placeholder="Repita sua senha" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 transition-colors">
            </div>

            <!-- Aviso de Segurança e Regras -->
            <div class="p-3 bg-emerald-950/40 rounded-xl border border-emerald-500/30 text-[11px] text-emerald-300 flex items-start gap-2">
                <span class="text-sm">🛡️</span>
                <span><strong>Segurança Garantida:</strong> Sua senha é tratada no padrão BCRYPT. Toda ação efetuada gera um log permanente de auditoria.</span>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black text-sm uppercase tracking-wider shadow-lg shadow-emerald-950/40 active:scale-95 transition-all mt-2">
                Cadastrar e Ganhar Minha Sorte
            </button>

        </form>

        <div class="mt-6 text-center text-xs text-slate-400 pt-4 border-t border-slate-800">
            Já possui uma conta? 
            <a href="login.php" class="text-emerald-400 hover:underline font-bold">Faça login aqui</a>
        </div>
    </div>

</div>
