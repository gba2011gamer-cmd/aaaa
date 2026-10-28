    </main>

    <!-- Rodapé Global -->
    <footer class="hidden md:block bg-slate-900 border-t border-slate-800 text-slate-500 py-6 text-xs text-center">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="text-amber-400 font-bold">Calculando a Sorte</span>
                <span>• Plataforma de Jogos e Cálculos Matemáticos</span>
            </div>
            <div class="flex items-center gap-4 text-slate-400">
                <span>Segurança PDO MySQL</span>
                <span>•</span>
                <span>Hash Bcrypt</span>
                <span>•</span>
                <span>Auditoria Ativa</span>
            </div>
        </div>
    </footer>

    <!-- ========================================================
         SISTEMA GLOBAL DE TOASTS & MODAIS (SUBSTITUI ALERTS)
         ======================================================== -->

    <!-- Container Flutuante para Toasts -->
    <div id="toast-container" class="fixed top-4 right-4 left-4 sm:left-auto sm:w-96 z-50 flex flex-col gap-2.5 pointer-events-none"></div>

    <!-- Estrutura do Modal Customizado Global -->
    <div id="custom-modal-root" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fade-in">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full shadow-2xl p-6 transform transition-all">
            <!-- Cabeçalho do Modal -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 id="custom-modal-title" class="text-lg font-bold text-white flex items-center gap-2">
                    Notificação do Jogo
                </h3>
                <button type="button" onclick="ModalManager.close()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Corpo do Modal -->
            <div id="custom-modal-body" class="py-4 text-sm text-slate-300">
                <!-- Conteúdo inserido dinamicamente -->
            </div>

            <!-- Rodapé / Botões de Ação do Modal -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button id="custom-modal-cancel" type="button" class="hidden px-4 py-2 text-xs font-semibold rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                    Cancelar
                </button>
                <button id="custom-modal-confirm" type="button" class="px-5 py-2.5 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-950/50 transition-all">
                    Entendido
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts JavaScript -->
    <script src="assets/js/main.js"></script>
    
    <!-- Renderiza Toasts vindos da Sessão PHP -->
    <?= render_flash_toasts_script() ?>

</body>
</html>
