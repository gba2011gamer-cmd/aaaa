/**
 * Calculando a Sorte - JavaScript Principal
 * Gerenciador de Toasts, Modais Nativos Customizados e Efeitos Sonoros via Web Audio
 */

// ==========================================
// 1. MOTOR DE EFEITOS SONOROS (WEB AUDIO API)
// Não necessita de arquivos externos de áudio
// ==========================================
const SoundFX = {
    ctx: null,
    enabled: true,

    init() {
        if (!this.ctx && (window.AudioContext || window.webkitAudioContext)) {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            this.ctx = new AudioCtx();
        }
    },

    playTone(freq, type, duration, delay = 0) {
        if (!this.enabled) return;
        try {
            this.init();
            if (this.ctx && this.ctx.state === 'suspended') {
                this.ctx.resume();
            }
            setTimeout(() => {
                if (!this.ctx) return;
                const osc = this.ctx.createOscillator();
                const gain = this.ctx.createGain();
                osc.type = type;
                osc.frequency.setValueAtTime(freq, this.ctx.currentTime);
                gain.gain.setValueAtTime(0.12, this.ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, this.ctx.currentTime + duration);
                osc.connect(gain);
                gain.connect(this.ctx.destination);
                osc.start();
                osc.stop(this.ctx.currentTime + duration);
            }, delay * 1000);
        } catch (e) {
            // Silencioso em caso de restrição do navegador
        }
    },

    btnClick() {
        this.playTone(520, 'sine', 0.06);
    },

    luckyRoll() {
        this.playTone(350, 'triangle', 0.08, 0);
        this.playTone(450, 'triangle', 0.08, 0.08);
        this.playTone(600, 'triangle', 0.08, 0.16);
        this.playTone(850, 'sine', 0.25, 0.24);
    },

    winReward() {
        this.playTone(523.25, 'sine', 0.12, 0);       // C5
        this.playTone(659.25, 'sine', 0.12, 0.1);     // E5
        this.playTone(783.99, 'sine', 0.15, 0.2);     // G5
        this.playTone(1046.50, 'triangle', 0.35, 0.32); // C6
    },

    lockedError() {
        this.playTone(180, 'sawtooth', 0.2);
    }
};

// ==========================================
// 2. SISTEMA DE TOASTS RESPONSIVOS
// ==========================================
const ToastManager = {
    container: null,

    getContainer() {
        if (!this.container) {
            this.container = document.getElementById('toast-container');
            if (!this.container) {
                this.container = document.createElement('div');
                this.container.id = 'toast-container';
                this.container.className = 'fixed top-4 right-4 left-4 sm:left-auto sm:w-96 z-50 flex flex-col gap-2.5 pointer-events-none';
                document.body.appendChild(this.container);
            }
        }
        return this.container;
    },

    show(message, type = 'info', duration = 4500) {
        const container = this.getContainer();
        const toast = document.createElement('div');
        toast.className = `pointer-events-auto transform transition-all duration-300 translate-y-[-10px] opacity-0 flex items-start p-4 rounded-xl shadow-2xl border backdrop-blur-md`;

        // Cores e ícones conforme o tipo
        let bgBorder = 'bg-slate-900/95 border-slate-700 text-white';
        let iconSvg = '';

        if (type === 'success') {
            bgBorder = 'bg-emerald-950/90 border-emerald-500/50 text-emerald-100';
            iconSvg = `<svg class="w-5 h-5 text-emerald-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
        } else if (type === 'error') {
            bgBorder = 'bg-rose-950/90 border-rose-500/50 text-rose-100';
            iconSvg = `<svg class="w-5 h-5 text-rose-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
            SoundFX.lockedError();
        } else if (type === 'warning') {
            bgBorder = 'bg-amber-950/90 border-amber-500/50 text-amber-100';
            iconSvg = `<svg class="w-5 h-5 text-amber-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>`;
        } else {
            bgBorder = 'bg-sky-950/90 border-sky-500/50 text-sky-100';
            iconSvg = `<svg class="w-5 h-5 text-sky-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
        }

        toast.className += ` ${bgBorder}`;
        toast.innerHTML = `
            ${iconSvg}
            <div class="ml-3 flex-1 text-sm font-medium leading-relaxed">${message}</div>
            <button type="button" class="ml-3 text-slate-400 hover:text-white shrink-0" onclick="this.parentElement.remove()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        `;

        container.appendChild(toast);

        // Animação de entrada
        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-[-10px]', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
        });

        // Remoção com timeout
        setTimeout(() => {
            if (toast.parentElement) {
                toast.classList.add('opacity-0', 'translate-y-[-10px]');
                setTimeout(() => toast.remove(), 300);
            }
        }, duration);
    },

    loadFromSession(toasts) {
        if (Array.isArray(toasts)) {
            toasts.forEach((t, index) => {
                setTimeout(() => {
                    this.show(t.message, t.type);
                }, index * 200);
            });
        }
    }
};

// ==========================================
// 3. SISTEMA DE MODAIS NATIVOS CUSTOMIZADOS
// Substitui alert() e confirm() do navegador
// ==========================================
const ModalManager = {
    modalEl: null,

    init() {
        this.modalEl = document.getElementById('custom-modal-root');
    },

    open({ title, body, confirmText = 'Entendido', cancelText = null, onConfirm = null, icon = 'info' }) {
        if (!this.modalEl) this.init();
        if (!this.modalEl) return;

        const titleEl = document.getElementById('custom-modal-title');
        const bodyEl  = document.getElementById('custom-modal-body');
        const btnConfirm = document.getElementById('custom-modal-confirm');
        const btnCancel  = document.getElementById('custom-modal-cancel');

        if (titleEl) titleEl.innerText = title;
        if (bodyEl) bodyEl.innerHTML = body;

        if (btnConfirm) {
            btnConfirm.innerText = confirmText;
            btnConfirm.onclick = () => {
                this.close();
                if (typeof onConfirm === 'function') onConfirm();
            };
        }

        if (btnCancel) {
            if (cancelText) {
                btnCancel.classList.remove('hidden');
                btnCancel.innerText = cancelText;
                btnCancel.onclick = () => this.close();
            } else {
                btnCancel.classList.add('hidden');
            }
        }

        this.modalEl.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    },

    close() {
        if (!this.modalEl) this.init();
        if (this.modalEl) {
            this.modalEl.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    },

    // Modal para Assistir Anúncio de 5 Segundos
    openAdRewardModal(operation, onRewardClaimed) {
        const opLabels = {
            '+': 'Adição (+)',
            '-': 'Subtração (-)',
            '*': 'Multiplicação (×)',
            '/': 'Divisão (÷)'
        };
        const opName = opLabels[operation] || operation;

        const adBody = `
            <div class="text-center py-2">
                <div class="inline-flex p-3 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-400 mb-3 animate-pulse">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h4 class="text-base font-bold text-white mb-1">Patrocinador Calculando a Sorte</h4>
                <p class="text-xs text-slate-400 mb-4">Assista a este anúncio de 5 segundos para liberar a operação <span class="text-amber-400 font-bold">${opName}</span> e ganhar +20 moedas!</p>
                
                <div class="relative bg-slate-950 rounded-xl p-4 border border-slate-800 overflow-hidden mb-3">
                    <div class="text-xs font-semibold uppercase tracking-wider text-emerald-400 mb-1">⭐ Mega Sorte Games ⭐</div>
                    <p class="text-sm text-slate-300 font-medium">Multiplique sua sorte com cálculos diários e ganhe prêmios instantâneos!</p>
                    
                    <!-- Barra de progresso do anúncio -->
                    <div class="w-full bg-slate-800 rounded-full h-2 mt-4 overflow-hidden">
                        <div id="ad-progress-bar" class="bg-gradient-to-r from-amber-500 to-emerald-400 h-2 w-0 transition-all duration-1000 ease-linear"></div>
                    </div>
                    <div id="ad-timer-text" class="text-xs text-slate-400 font-mono mt-2 font-bold">Aguarde 5s para resgatar...</div>
                </div>
            </div>
        `;

        this.open({
            title: `Desbloquear Operação: ${opName}`,
            body: adBody,
            confirmText: 'Aguardando Anúncio...',
            cancelText: 'Cancelar',
            onConfirm: null
        });

        const btnConfirm = document.getElementById('custom-modal-confirm');
        if (btnConfirm) {
            btnConfirm.disabled = true;
            btnConfirm.classList.add('opacity-50', 'cursor-not-allowed');
        }

        let secondsLeft = 5;
        const progressBar = document.getElementById('ad-progress-bar');
        const timerText   = document.getElementById('ad-timer-text');

        const interval = setInterval(() => {
            secondsLeft--;
            if (progressBar) {
                const percent = ((5 - secondsLeft) / 5) * 100;
                progressBar.style.width = `${percent}%`;
            }
            if (timerText) {
                timerText.innerText = secondsLeft > 0 ? `Aguarde ${secondsLeft}s para resgatar...` : '🎉 Anúncio Concluído!';
            }

            if (secondsLeft <= 0) {
                clearInterval(interval);
                SoundFX.winReward();
                if (btnConfirm) {
                    btnConfirm.disabled = false;
                    btnConfirm.classList.remove('opacity-50', 'cursor-not-allowed');
                    btnConfirm.innerText = '🎁 Resgatar Recompensa!';
                    btnConfirm.onclick = () => {
                        this.close();
                        if (typeof onRewardClaimed === 'function') onRewardClaimed();
                    };
                }
            }
        }, 1000);
    }
};

// Global shortcuts
window.ToastManager = ToastManager;
window.ModalManager = ModalManager;
window.SoundFX = SoundFX;
