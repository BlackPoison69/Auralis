<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: usuario/login.php");
    exit;
}
require_once 'config/conexao.php';

$usuario_id = $_SESSION['usuario_id'];
$carteiras = [];
$categorias = [];
$erro = null;

// 1. Busca carteiras do usuário
try {
    $sqlCarteiras = '
        SELECT DISTINCT c."IDCarteira", c."TipoCarteira"
        FROM "Carteira" c
        LEFT JOIN "MembroCarteira" mc ON mc."FKCarteira" = c."IDCarteira" AND mc."FKUsuario" = :uid_membro AND mc."StatusConvite" = true
        WHERE c."FKUsuarioDono" = :uid_dono OR mc."FKCarteira" IS NOT NULL
        ORDER BY c."TipoCarteira" ASC
    ';
    $stmtC = $pdo->prepare($sqlCarteiras);
    $stmtC->execute([':uid_dono' => $usuario_id, ':uid_membro' => $usuario_id]);
    $carteiras = $stmtC->fetchAll();

    // 2. Busca TODAS as categorias globais do usuário (Nova Arquitetura)
    $sqlCategorias = 'SELECT "IDCategoria", "NomeCategoria" FROM "Categoria" WHERE "FKUsuario" = :uid ORDER BY "NomeCategoria" ASC';
    $stmtCat = $pdo->prepare($sqlCategorias);
    $stmtCat->execute([':uid' => $usuario_id]);
    $categorias = $stmtCat->fetchAll();

} catch (PDOException $e) {
    $carteiras = [];
    $categorias = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipoRegistro = trim($_POST['tipo_registro'] ?? '');
    $valorRaw = trim($_POST['valor'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $dataRegistro = trim($_POST['data_registro'] ?? '');
    $dataVencimento = trim($_POST['data_vencimento'] ?? '');
    $statusRegistro = trim($_POST['status_registro'] ?? '');
    $carteiraId = trim($_POST['carteira_id'] ?? '');
    $categoriaId = trim($_POST['categoria_id'] ?? '') ?: null;
    $subCategoriaId = trim($_POST['subcategoria_id'] ?? '') ?: null;
    $recorrente = isset($_POST['recorrente']) ? 1 : 0; // Fix para PDO Booleans
    $diaVencimento = $recorrente ? intval($_POST['dia_vencimento'] ?? 0) : null;

    // Ajuste da Validação de Acordo com o Banco
    if (!in_array($tipoRegistro, ['receita', 'despesa'])) {
        $erro = "Tipo de registro inválido.";
    } elseif (empty($valorRaw) || !is_numeric(str_replace(',', '.', $valorRaw))) {
        $erro = "Informe um valor numérico válido.";
    } elseif (floatval(str_replace(',', '.', $valorRaw)) <= 0) {
        $erro = "O valor deve ser maior que zero.";
    } elseif (empty($descricao)) {
        $erro = "A descrição não pode ficar em branco.";
    } elseif (empty($dataRegistro)) {
        $erro = "Selecione a data do registro.";
    } elseif (!in_array($statusRegistro, ['pendente', 'efetivado'])) { // <--- CORREÇÃO AQUI
        $erro = "Status inválido.";
    } elseif (empty($carteiraId)) {
        $erro = "Selecione uma carteira.";
    } elseif ($recorrente && ($diaVencimento < 1 || $diaVencimento > 31)) {
        $erro = "Dia de vencimento inválido (1 a 31).";
    }

    if (!$erro) {
        $valor = str_replace(',', '.', $valorRaw);
        $dataVencimento = !empty($dataVencimento) ? $dataVencimento : null;
        try {
            $sql = '
                INSERT INTO "Registro" (
                    "TipoRegistro", "Valor", "Descricao",
                    "MomentoRegistro", "DataVencimento",
                    "StatusRegistro", "Recorrente", "DiaVencimento",
                    "FKCarteira", "FKUsuario", "FKCategoria", "FKSubCategoria"
                ) VALUES (
                    :tipo, :valor, :descricao,
                    :momento, :vencimento,
                    :status, :recorrente, :dia,
                    :carteira, :usuario, :categoria, :subcategoria
                )
            ';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':tipo' => $tipoRegistro,
                ':valor' => $valor,
                ':descricao' => $descricao,
                ':momento' => $dataRegistro,
                ':vencimento' => $dataVencimento,
                ':status' => $statusRegistro,
                ':recorrente' => $recorrente,
                ':dia' => $diaVencimento,
                ':carteira' => $carteiraId,
                ':usuario' => $usuario_id,
                ':categoria' => $categoriaId,
                ':subcategoria' => $subCategoriaId,
            ]);
            header("Location: dashboard.php?sucesso=registro");
            exit;
        } catch (PDOException $e) {
            $erro = "Erro ao salvar o registro. Verifique os dados.";
        }
    }
}

require_once 'geral/header.php';
?>

<main class="container py-4 mt-3 flex-grow-1">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-7">

            <div
                class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary-subtle pb-3">
                <h2 class="fw-bold text-light mb-0">Nova Transação</h2>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Voltar
                </a>
            </div>

            <?php if ($erro): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                    <span><?= htmlspecialchars($erro) ?></span>
                </div>
            <?php endif; ?>

            <?php if (empty($carteiras)): ?>
                <div class="alert alert-warning rounded-3">
                    <i class="bi bi-wallet2 me-2"></i>
                    Você não tem nenhuma carteira cadastrada.
                    <a href="carteira/nova_carteira.php" class="alert-link">Criar carteira</a>.
                </div>
            <?php else: ?>

                <div class="card bg-body-tertiary border-secondary-subtle shadow-sm rounded-4">
                    <form method="POST" action="" novalidate class="auralis-premium-form" style="border-radius: 200px !important;">

<?php $tipo_sugerido = $_POST['tipo_registro'] ?? $_GET['tipo'] ?? 'despesa'; ?>

<input type="hidden" name="tipo_registro" value="<?= $tipo_sugerido ?>">

<div class="text-center my-4">
    <span class="badge badge-tipo rounded-pill d-inline-flex align-items-center justify-content-center gap-2 px-5 py-3 shadow-sm">

        <?php if ($tipo_sugerido === 'receita'): ?>
            <span class="fw-bold text-success fs-5">
                💰 Receita
            </span>
        <?php else: ?>
            <span class="fw-bold text-danger fs-5">
                💸 Despesa
            </span>
        <?php endif; ?>

    </span>
</div>

                        <div class="mb-5 d-flex align-items-center justify-content-center pb-3 auralis-line-input">
                            <input type="number" step="0.01" min="0.01" name="valor" id="valor"
                                class="form-control form-control-lg bg-transparent border-0 text-gold-analysis fw-bold text-center fs-1-large p-0 p-lg-1 no-spinners"
                                placeholder="Valor:" required autofocus style="box-shadow: none;"
                                value="<?= htmlspecialchars($_POST['valor'] ?? '') ?>">   
                        </div>

                        <div class="d-flex align-items-center mb-4 pb-2 auralis-line-input">
                            <i class="bi bi-paragraph text-secondary-analysis me-3 w-icon text-center"></i>
                            <input type="text" name="descricao" id="descricao"
                                class="form-control bg-transparent border-0 text-light-analysis px-0 shadow-none fs-6 fw-bold"
                                placeholder="Descrição:" maxlength="255" required
                                value="<?= htmlspecialchars($_POST['descricao'] ?? '') ?>">
                        </div>

                        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 auralis-line-input">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clock-history text-secondary-analysis me-3 w-icon text-center"></i>
                                <span class="text-secondary-analysis fs-6" id="texto_status">Não pago ainda</span>
                            </div>
                            <div class="form-check form-switch fs-4 mb-0 toggle-analysis">
                                <input type="hidden" name="status_registro" id="status_real" value="pendente">

                                <input class="form-check-input bg-dark border-border-color shadow-none" type="checkbox"
                                    role="switch" id="toggle_status">
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-4 pb-2 auralis-line-input">
                            <i class="bi bi-credit-card text-secondary-analysis me-3 w-icon text-center"></i>
                            <select name="carteira_id" id="carteira_id"
                                class="form-select bg-transparent border-0 text-light-analysis px-0 shadow-none fw-semibold fs-6"
                                required>
                                                    <?php $carteira_sugerida = $_POST['carteira_id'] ?? $_GET['carteira_id'] ?? ''; ?>
                                                    <?php foreach ($carteiras as $cart): ?>
                                    <option class="bg-card" value="<?= htmlspecialchars($cart['IDCarteira']) ?>"
                                        <?= ($carteira_sugerida === $cart['IDCarteira']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($cart['TipoCarteira']) ?>
                                    </option>
                                                    <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row g-3 mb-4 auralis-line-input">
                            <div class="col-6 d-flex align-items-center border-end border-border-color pe-3">
                                <i class="bi bi-tags text-secondary-analysis me-2 fs-7"></i>
                                <select name="categoria_id"
                                    class="form-select bg-transparent border-0 text-muted-analysis px-0 shadow-none fs-7 fw-bold">
                                    <option class="bg-card" value="">Categoria</option>
                                                        <?php foreach ($categorias as $cat): ?>
                                        <option class="bg-card" value="<?= htmlspecialchars($cat['IDCategoria']) ?>"
                                            <?= (isset($_POST['categoria_id']) && $_POST['categoria_id'] === $cat['IDCategoria']) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($cat['NomeCategoria']) ?>
                                        </option>
                                                        <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-6 d-flex align-items-center ps-3">
                                <i class="bi bi-calendar3 text-secondary-analysis me-2 fs-7"></i>
                                <input type="date" name="data_registro"
                                    class="form-control bg-transparent border-0 text-light-analysis px-0 shadow-none fs-7 fw-bold"
                                    value="<?= htmlspecialchars($_POST['data_registro'] ?? date('Y-m-d')) ?>" required>
                            </div>
                        </div>

                        <div class="accordion accordion-flush mb-5 border border-border-color rounded-3 overflow-hidden auralis-line-input"
                            id="accordionMaisDetalhes">
                            <div class="accordion-item bg-transparent">
                                <h2 class="accordion-header">
                                    <button
                                        class="accordion-button collapsed bg-transparent text-secondary-analysis shadow-none py-2 px-3 small fs-7"
                                        type="button" data-bs-toggle="collapse" data-bs-target="#collapseDetalhes">
                                        Mais detalhes (Vencimento, Recorrência)
                                    </button>
                                </h2>
                                <div id="collapseDetalhes" class="accordion-collapse collapse"
                                    data-bs-parent="#accordionMaisDetalhes">
                                    <div class="accordion-body border-top border-border-color pt-3 px-3 fs-7 bg-charcoal">

                                        <div class="mb-3">
                                            <label class="form-label text-secondary-analysis fs-7 mb-1">Data de
                                                Vencimento</label>
                                            <input type="date" name="data_vencimento"
                                                class="form-control bg-dark border-border-color text-light-analysis fs-7">
                                        </div>

                                        <div class="form-check form-switch mb-2 toggle-analysis toggle-analysis-muted">
                                            <input class="form-check-input bg-dark border-border-color shadow-none"
                                                type="checkbox" name="recorrente" id="recorrente">
                                            <label class="form-check-label text-muted-analysis fs-7" for="recorrente">Conta
                                                recorrente</label>
                                        </div>

                                        <div id="bloco_recorrencia" style="display:none;"
                                            class="ps-4 border-start border-border-color mt-2 bg-charcoal">
                                            <label class="form-label text-secondary-analysis fs-7 mb-1">Dia do mês</label>
                                            <input type="number" name="dia_vencimento" id="dia_vencimento"
                                                class="form-control bg-dark border-border-color text-light-analysis form-control-sm w-50 no-spinners fs-7"
                                                min="1" max="31" placeholder="Ex: 10">
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mt-2">
                            <button type="submit"
                                class="btn btn-primary fw-bold text-dark py-3 rounded-pill fs-6 shadow-lg d-flex align-items-center justify-content-center">
                                Salvar Transação
                            </button>
                        </div>

                    </form>

                    <style>
                        /* 1. Paleta de Cores Auralis Premium Suave / Fintech Dark Slate */
                        :root {
                            --primary-gold-analysis: #AA8C2C;
                            /* Dourado envelhecido suave */
                            --gold-glow-analysis: rgba(170, 140, 44, 0.3);
                            --bg-main-analysis: #1F1F1F;
                            /* Charcoal Slate Suave */
                            --bg-card-analysis: #2A2A2A;
                            /* Charcoal Slate Levemente Claro */
                            --bg-charcoal-analysis: #222222;
                            /* Charcoal muito suave */
                            --border-color-analysis: #333333;
                            /* Cinza escuro suave */
                            --text-light-analysis: #E0E0E0;
                            /* Branco quebrado suave */
                            --text-muted-analysis: #888888;
                            /* Cinza suave */
                            --text-gold-analysis: #D4AF37;
                            /* Aurum para destaque, mas suave */
                        }

                        /* Aplicação Global das Novas Cores */
                        .auralis-premium-form .text-light {
                            color: var(--text-light-analysis) !important;
                        }

                        .auralis-premium-form .text-secondary {
                            color: var(--text-muted-analysis) !important;
                        }

                        .bg-dark {
                            background-color: var(--bg-charcoal-analysis) !important;
                        }

                        .card {
                            background-color: var(--bg-card-analysis) !important;
                            border-color: var(--border-color-analysis) !important;
                        }

                        .alert {
                            background-color: var(--bg-card-analysis) !important;
                            border-color: var(--border-color-analysis) !important;
                            color: var(--text-light-analysis) !important;
                        }

                        .alert-link {
                            color: var(--primary-gold-analysis) !important;
                        }

                        .alert-warning .bi-wallet2 {
                            color: var(--primary-gold-analysis) !important;
                        }

                        /* 2. Utilitários de Design Line Inputs */
                        .auralis-form input[type="text"]:focus,
                        .auralis-form input[type="number"]:focus,
                        .auralis-form select:focus {
                            border-color: var(--primary-gold-analysis) !important;
                            background-color: transparent !important;
                        }

                        .w-icon {
                            width: 30px;
                        }

                        /* Ajuste de alinhamento dos ícones sutis */
                        .w-icon i {
                            font-size: 1.25rem;
                        }

                        /* Tamanho de ícone Bootstrap consistente */
                        .auralis-line-input {
                            border-bottom: 1px solid var(--border-color-analysis);
                            background-color: transparent !important;
                        }

                        .auralis-line-input .form-control,
                        .auralis-line-input .form-select {
                            color: var(--text-light-analysis) !important;
                        }

                        /* 3. Centralização e Remoção de Spinners de Número */
                        .no-spinners::-webkit-outer-spin-button,
                        .no-spinners::-webkit-inner-spin-button {
                            -webkit-appearance: none;
                            margin: 0;
                        }

                        .no-spinners {
                            -moz-appearance: textfield;
                            appearance: none;
                        }

                        /* 4. Tipografia Premium Suave */
                        .fs-1-large {
                            font-size: 3rem !important;
                        }

                        /* Valor muito grande, centralizado */
                        .fs-6 {
                            font-size: 1rem !important;
                        }

                        /* Texto padrão, negrito sutil */
                        .fs-7 {
                            font-size: 0.875rem !important;
                        }

                        /* Texto pequeno */
                        .fw-bold {
                            font-weight: 700 !important;
                        }

                        /* Negrito sutil */
                        .fw-semibold {
                            font-weight: 600 !important;
                        }

                        /* Negrito sutil */

                        /* 5. Estilo de Toggle Switch Premium */
                        .toggle-analysis .form-check-input {
                            border-color: var(--border-color-analysis);
                        }

                        .toggle-analysis .form-check-input:checked {
                            background-color: var(--primary-gold-analysis);
                            border-color: var(--primary-gold-analysis);
                        }

                        .toggle-analysis .form-check-input:focus {
                            border-color: var(--primary-gold-analysis);
                            box-shadow: 0 0 0 0.25rem var(--gold-glow-analysis);
                        }

                        /* Toggle muted para detalhes */
                        .toggle-analysis-muted .form-check-input:checked {
                            opacity: 0.6;
                        }

                        /* 6. Outros Ajustes do Protótipo */
                        .form-select.shadow-none:focus {
                            border-color: transparent !important;
                        }

                        .auralis-line-input select option {
                            background-color: var(--bg-card-analysis);
                            color: var(--text-light-analysis);
                        }

                        .no-spinners {
                            padding-left: 2rem !important;
                        }

                        /* Compensa a centralização p/ placeholder */

                        /* Botão Primary Refinado Dourado/Charcoal */
                        .btn-gold {
                            background: linear-gradient(135deg, #FFB800 0%, #D4AF37 100%);
                            border: none;
                            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.2);
                        }

                        .btn-gold:hover {
                            background: linear-gradient(135deg, #FFD04F 0%, #E7C665 100%);
                            color: #000;
                            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.5);
                        }

                        /* Efeito sutil dos ícones */
                        .w-icon .bi {
                            transition: all 0.3s ease;
                        }

                        .auralis-premium-form input:focus~i.text-muted,
                        .auralis-premium-form select:focus~i.text-muted {
                            color: var(--primary-gold-analysis) !important;
                            opacity: 0.8;

                        }
                        .badge-tipo {
    background: linear-gradient(135deg, #2a2a2a, #1f1f1f);
    border: 1px solid var(--border-color-analysis);
    font-size: 1rem;
    min-width: 180px;
    transition: all 0.25s ease;
}

.badge-tipo:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.35);
}
                    </style>

                    <script>
                        // UX INTELIGENTE: Muda apenas o visual do texto do Status e Toggle
                        const toggleStatus = document.getElementById('toggle_status');
                        const inputReal = document.getElementById('status_real');
                        const textoStatus = document.getElementById('texto_status');
                        const tipoAtual = "<?= $tipo_sugerido ?>"; // Receita ou Despesa vindo do PHP

                        toggleStatus.addEventListener('change', function () {
                            if (this.checked) {
                                inputReal.value = 'efetivado';
                                textoStatus.innerText = 'Foi ' + (tipoAtual === 'receita' ? 'recebido' : 'pago');
                                textoStatus.classList.replace('text-secondary', 'text-light');
                            } else {
                                inputReal.value = 'pendente';
                                textoStatus.innerText = 'Não ' + (tipoAtual === 'receita' ? 'recebido' : 'pago') + ' ainda';
                                textoStatus.classList.replace('text-light', 'text-secondary');
                            }
                        });

                        // Recorrência
                        const checkRecorrente = document.getElementById('recorrente');
                        const blocoRecorrencia = document.getElementById('bloco_recorrencia');
                        const inputDia = document.getElementById('dia_vencimento');

                        checkRecorrente.addEventListener('change', function () {
                            blocoRecorrencia.style.display = this.checked ? 'block' : 'none';
                            inputDia.required = this.checked;
                        });
                    </script>

                    <style>
                        /* Estilos extras para deixar os inputs transparentes bonitos */
                        .auralis-form input[type="text"]:focus,
                        .auralis-form input[type="number"]:focus,
                        .auralis-form select:focus {
                            border-color: var(--gold-primary) !important;
                        }

                        .w-20px {
                            width: 30px;
                        }

                        /* Ajuste de alinhamento dos ícones */
                    </style>
                </div>

            <?php endif; ?>
        </div>
    </div>
</main>

<script>
    // UX INTELIGENTE: Muda apenas o visual do texto do Status para não quebrar o banco
    const tipoReceita = document.getElementById('tipo_receita');
    const tipoDespesa = document.getElementById('tipo_despesa');
    const optEfetivado = document.getElementById('opt_efetivado');

    function atualizarTextoStatus() {
        if (tipoReceita.checked) {
            optEfetivado.innerHTML = '✅ Recebido';
            optEfetivado.className = 'text-success';
        } else {
            optEfetivado.innerHTML = '✅ Pago';
            optEfetivado.className = 'text-danger';
        }
    }
    tipoReceita.addEventListener('change', atualizarTextoStatus);
    tipoDespesa.addEventListener('change', atualizarTextoStatus);
    atualizarTextoStatus(); // Roda ao carregar a página

    // RECORRÊNCIA
    const checkRecorrente = document.getElementById('recorrente');
    const blocoRecorrencia = document.getElementById('bloco_recorrencia');
    const inputDia = document.getElementById('dia_vencimento');

    function toggleRecorrencia() {
        const ativo = checkRecorrente.checked;
        blocoRecorrencia.style.display = ativo ? 'block' : 'none';
        inputDia.required = ativo;
    }
    checkRecorrente.addEventListener('change', toggleRecorrencia);
    toggleRecorrencia();

    // AJAX — Subcategorias
    const selectCategoria = document.getElementById('categoria_id');
    const selectSubCategoria = document.getElementById('subcategoria_id');

    selectCategoria.addEventListener('change', function () {
        const id = this.value;
        selectSubCategoria.innerHTML = '<option value="">Carregando...</option>';
        selectSubCategoria.disabled = true;

        if (!id) {
            selectSubCategoria.innerHTML = '<option value="">Selecione a categoria primeiro</option>';
            return;
        }

        fetch(`ajax/subcategorias.php?categoria_id=${encodeURIComponent(id)}`)
            .then(r => r.json())
            .then(data => {
                selectSubCategoria.innerHTML = '<option value="">Sem subcategoria</option>';
                data.forEach(sub => selectSubCategoria.add(new Option(sub.NomeSubCategoria, sub.IDSubCategoria)));
                selectSubCategoria.disabled = false;
            })
            .catch(() => {
                selectSubCategoria.innerHTML = '<option value="">Erro ao carregar</option>';
            });
    });
</script>

<?php require_once 'geral/footer.php'; ?>