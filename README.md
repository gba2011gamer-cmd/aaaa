# 🎲 Calculando a Sorte

> Plataforma de jogos matemáticos e calculadora da sorte, desenvolvida com **PHP estruturado**, **MySQL via PDO**, **Tailwind CSS (Mobile-First)** e sessões nativas.

---

## 📋 Sumário
1. [Sobre o Projeto](#-sobre-o-projeto)
2. [Stack Tecnológico](#-stack-tecnológico)
3. [Regras de Negócio (CORE)](#-regras-de-negócio-core)
4. [Estrutura de Arquivos (Separação Lógica)](#-estrutura-de-arquivos-separação-lógica)
5. [Banco de Dados & Auditoria](#-banco-de-dados--auditoria)
6. [Contas de Teste Pré-Configuradas](#-contas-de-teste-pré-configuradas)
7. [Como Executar o Projeto](#-como-executar-o-projeto)

---

## 🎯 Sobre o Projeto
O **Calculando a Sorte** transforma o ato de calcular em uma experiência lúdica e de sorte:
- O usuário possui uma **calculadora completa** com números `0` a `9`, decimal e funções normais.
- A cada rodada ou sorteio, uma das operações básicas (**+**, **-**, **×**, **÷**) é **sorteada pela Sorte**.
- As demais operações ficam bloqueadas por padrão.
- O jogador pode **desbloquear operações** assistindo a um anúncio de 5 segundos ou ativando o **Passe VIP Premium**.
- Inclui o jogo **Desafio da Sorte**, onde o jogador deve atingir um número alvo com a operação sorteada para ganhar pontos e moedas.

---

## 🛠 Stack Tecnológico
- **Backend:** PHP estruturado com sessões nativas (`session_start()`, sem frameworks monolíticos).
- **Banco de Dados:** MySQL com **PDO** e *prepared statements* em todas as queries.
- **Frontend:** HTML5 semântico, PHP Views, CSS customizado e **Tailwind CSS** (design responsivo priorizando **Mobile-First**).
- **Feedback ao Usuário:** Notificações flutuantes **TOAST** e **MODAL** customizado para substituição de `alert()` e `confirm()`.

---

## ⚖️ Regras de Negócio (CORE)

### 1. Senhas com Hash Bcrypt
Todas as senhas de usuários são tratadas e criptografadas exclusivamente com o algoritmo `PASSWORD_BCRYPT` do PHP (`cost => 10`), garantindo proteção contra vazamento e rainbow tables.

### 2. Auditoria e Logs de Alterações
O sistema possui a tabela `audit_logs` e uma página dedicada de auditoria (`history.php`), mantendo o histórico de **qualquer alteração** realizada por qualquer usuário:
- Cadastros de novos usuários
- Logins bem-sucedidos e tentativas falhas
- Encerramentos de sessão (Logout)
- Sorteios de novas operações
- Desbloqueios mediante anúncios assistidos
- Alterações de status do Passe VIP Premium
- Cálculos efetuados e bloqueios por operações travadas
- Vitórias no jogo de Desafio Matemático

### 3. Mecânica da Calculadora da Sorte
- O usuário sempre pode usar os dígitos e a pontuação normalmente.
- Uma operação matemática fica ativa como a **Operação da Rodada**.
- Operações bloqueadas exibem um indicador 🔒 e impedem o cálculo indevido no servidor.
- Ao clicar em uma operação bloqueada, um **Modal Customizado** oferece:
  - **Opção Gratuita:** Assistir a um anúncio interativo de 5 segundos com barra de progresso para desbloquear a operação e faturar moedas.
  - **Opção VIP:** Ativar o Passe Premium para desbloquear todas as 4 operações de forma permanente.
- O usuário pode clicar em **Girar Sorte Novamente 🎲** a qualquer momento.

---

## 📁 Estrutura de Arquivos (Separação Lógica)

O sistema segue separação modular estrita, evitando código monolítico:

```
calculando-a-sorte/
├── bd.php                         # Arquivo de conexão principal (atalho na raiz conforme requisito)
├── config/
│   └── bd.php                     # Conexão PDO com tratamento de erros e auto-provisionamento
├── database/
│   └── schema.sql                 # Script SQL completo com tabelas, índices e seeds
├── includes/
│   ├── auth_check.php             # Middleware de autenticação e helpers de sessão
│   ├── logger.php                 # Função central de registro de auditoria (log_audit)
│   ├── toast.php                  # Helper de notificações Toast em sessão
│   ├── header.php                 # Cabeçalho global responsivo Mobile-First (Tailwind)
│   └── footer.php                 # Rodapé global, container Toast e componente Modal
├── actions/                       # Scripts de backend isolados (Controllers / Handlers)
│   ├── auth_register.php          # Cadastro com hash Bcrypt e auditoria
│   ├── auth_login.php             # Login com password_verify e auditoria
│   ├── auth_logout.php            # Encerramento de sessão e auditoria
│   ├── calc_roll_operation.php    # Sorteio aleatório de operação da sorte
│   ├── calc_unlock_ad.php         # Recompensa e desbloqueio por anúncio (5s)
│   ├── calc_upgrade_premium.php   # Ativação do Passe VIP Premium
│   ├── game_calculate.php         # Processamento de cálculos seguro e validação de regras
│   └── game_challenge.php         # Enigmas do Desafio Matemático e pontuação
├── views/                         # Telas em HTML5/PHP puras
│   ├── calculator_view.php        # Visor LCD, botões táteis, sorteios e status das operações
│   ├── game_view.php              # Desafio "Número Alvo" com ranking
│   ├── history_view.php           # Tabela e cards de auditoria com filtros por ação/IP/termo
│   ├── premium_view.php           # Loja de desbloqueios por anúncio e Passe VIP
│   ├── login_view.php             # Tela de login com preenchimento rápido para testes
│   └── register_view.php          # Tela de cadastro com avisos de segurança
├── assets/
│   ├── css/
│   │   └── custom.css             # Efeitos neon, glassmorphism e animações da roleta
│   └── js/
│       ├── main.js                # ToastManager, ModalManager e sintetizador Web Audio FX
│       └── calculator.js          # Controle do teclado, modais de bloqueio e AJAX
├── index.php                      # Ponto de entrada (redireciona para login/calculadora)
├── login.php                      # Rota de login
├── register.php                   # Rota de cadastro
├── logout.php                     # Rota de logout
├── calculator.php                 # Rota da calculadora
├── game.php                       # Rota do jogo matemático
├── history.php                    # Rota do histórico de auditoria
└── premium.php                    # Rota da loja VIP
```

---

## 🗄 Banco de Dados & Auditoria

O arquivo [database/schema.sql](file:///C:/Users/Aluno/.gemini/antigravity/scratch/calculando-a-sorte/database/schema.sql) cria o banco `calculando_a_sorte` com as seguintes tabelas:
- **`users`**: Dados do jogador, hash Bcrypt da senha, status VIP (`is_premium`), moedas, pontuação e operações desbloqueadas em formato JSON.
- **`audit_logs`**: Tabela central de auditoria (`user_id`, `user_name`, `action`, `details`, `ip_address`, `user_agent`, `created_at`).
- **`calculations`**: Histórico detalhado de contas calculadas na calculadora.
- **`game_challenges`**: Histórico de partidas e enigmas do jogo de desafio.

---

## 👥 Contas de Teste Pré-Configuradas

Para testar imediatamente as duas experiências do sistema:

| Perfil | E-mail | Senha | Operações Iniciais |
| :--- | :--- | :--- | :--- |
| **👑 Administrador VIP** | `admin@sorte.com` | `123456` | Todas as 4 (+, -, ×, ÷) liberadas |
| **🎮 Jogador Gratuito** | `jogador@sorte.com` | `123456` | Apenas a operação sorteada (desbloqueie as outras via anúncio) |

Na tela de login há botões de **preenchimento com 1 clique** para facilitar os testes.

---

## 🚀 Como Executar o Projeto

### Opção 1: Servidor Embutido do PHP (Recomendado para Desenvolvimento)
1. Certifique-se de que o **MySQL** esteja iniciado (via XAMPP, WampServer ou serviço local do Windows na porta 3306).
2. Abra o terminal na pasta do projeto:
   ```bash
   cd C:\Users\Aluno\.gemini\antigravity\scratch\calculando-a-sorte
   ```
3. Inicie o servidor PHP:
   ```bash
   php -S 127.0.0.1:8000
   ```
4. Acesse no navegador:
   ```
   http://127.0.0.1:8000
   ```

### Opção 2: XAMPP / WampServer / Laragon
1. Copie ou aponte a pasta `calculando-a-sorte` para o diretório `htdocs` (ou `www`).
2. Acesse `http://localhost/phpmyadmin` e importe o arquivo `database/schema.sql`.
3. Abra `http://localhost/calculando-a-sorte` no seu navegador.
