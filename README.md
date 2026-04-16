# Auralis - Gestão Financeira Inteligente 🪙

O **Auralis** é um ecossistema de gestão financeira pessoal desenvolvido para oferecer controlo total sobre fluxos de caixa, contas recorrentes e análise de património. O sistema foca-se em usabilidade, segurança e automação de processos repetitivos.

---

## 🚀 Funcionalidades Principais

- **Painel de Controlo (Dashboard):** Visão em tempo real de saldos, receitas e despesas do mês corrente.
- **Motor de Recorrência Inteligente:** Sistema automático que detecta a viragem do mês e clona transações marcadas como recorrentes, poupando trabalho manual ao utilizador.
- **Análises Visuais:** Gráficos interactivos de distribuição de gastos e ganhos utilizando **Chart.js**.
- **Segurança de Acesso:** - Criptografia de senhas com `password_hash`.
    - Sistema "Lembrar-me" (Persistent Login) utilizando Cookies assinados com HMAC para prevenir falsificações.
    - Validação de formulários em tempo real (Front-end) e reforço de segurança no Back-end.
- **Gestão de Perfil:** Edição de dados pessoais, alteração de senha e "Zona de Perigo" para exclusão definitiva de conta (em conformidade com a LGPD).
- **Interface Premium:** Design responsivo e moderno construído com **Bootstrap 5** em modo escuro (Dark Mode).

---

## 🛠️ Tecnologias Utilizadas

- **Linguagem:** PHP 8.x
- **Base de Dados:** PostgreSQL (Hospedado via Supabase)
- **Front-end:** HTML5, CSS3, JavaScript (ES6+)
- **Framework CSS:** Bootstrap 5.3
- **Bibliotecas Externas:**
  - [Chart.js](https://www.chartjs.org/) (Gráficos)
  - [Cleave.js](https://nosir.github.io/cleave.js/) (Máscaras de inputs)
  - [Bootstrap Icons](https://icons.getbootstrap.com/)

---

## 📦 Estrutura do Projeto

```text
/Auralis
├── config/             # Configurações de conexão (PDO)
├── geral/              # Componentes globais (Header, Footer, Index)
├── usuario/            # Lógica de Autenticação (Login, Cadastro, Logout)
├── carteira/           # Gestão de contas e ativos
├── dashboard.php       # Página principal e Motor de Recorrência
├── analises.php        # Visualização de dados e gráficos
└── configuracoes.php   # Perfil e Segurança do utilizador
