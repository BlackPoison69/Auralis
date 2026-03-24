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
                    <div class="card-body p-4">
                        <form method="POST" action="" novalidate>

                            <div class="mb-4">
                                <label class="form-label text-light fw-semibold">Tipo</label>
                                <div class="d-flex gap-3">
                                    <input type="radio" class="btn-check" name="tipo_registro" id="tipo_receita"
                                        value="receita" <?= (($_POST['tipo_registro'] ?? 'despesa') === 'receita') ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-success flex-grow-1 fw-semibold" for="tipo_receita">
                                        <i class="bi bi-arrow-up-circle me-2"></i>Receita
                                    </label>

                                    <input type="radio" class="btn-check" name="tipo_registro" id="tipo_despesa"
                                        value="despesa" <?= (($_POST['tipo_registro'] ?? 'despesa') === 'despesa') ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-danger flex-grow-1 fw-semibold" for="tipo_despesa">
                                        <i class="bi bi-arrow-down-circle me-2"></i>Despesa
                                    </label>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="valor" class="form-label text-light fw-semibold">Valor (R$)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-dark border-secondary text-secondary">R$</span>
                                        <input type="number" step="0.01" min="0.01" name="valor" id="valor"
                                            class="form-control bg-dark border-secondary text-light fs-5 fw-bold"
                                            placeholder="0,00" required
                                            value="<?= htmlspecialchars($_POST['valor'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="status_registro" class="form-label text-light fw-semibold">Status</label>
                                    <select name="status_registro" id="status_registro"
                                        class="form-select bg-dark border-secondary text-light fw-semibold" required>
                                        <option value="pendente" <?= (($_POST['status_registro'] ?? 'efetivado') === 'pendente') ? 'selected' : '' ?>>⏳ Pendente</option>
                                        <option value="efetivado" id="opt_efetivado" <?= (($_POST['status_registro'] ?? 'efetivado') === 'efetivado') ? 'selected' : '' ?>>✅ Efetivado</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="descricao" class="form-label text-light fw-semibold">Descrição</label>
                                <input type="text" name="descricao" id="descricao"
                                    class="form-control bg-dark border-secondary text-light"
                                    placeholder="Ex: Conta de luz, Salário, Mercado..." maxlength="255" required
                                    value="<?= htmlspecialchars($_POST['descricao'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label for="carteira_id" class="form-label text-light fw-semibold">Carteira</label>
                                <select name="carteira_id" id="carteira_id"
                                    class="form-select bg-dark border-secondary text-light" required>

                                    <?php
                                    // Verifica se veio uma carteira pré-selecionada via URL ou via POST (se o form der erro e recarregar)
                                    $carteira_sugerida = $_POST['carteira_id'] ?? $_GET['carteira_id'] ?? '';
                                    ?>

                                    <option value="" disabled <?= empty($carteira_sugerida) ? 'selected' : '' ?>>Escolha a
                                        carteira...</option>

                                    <?php foreach ($carteiras as $cart): ?>
                                        <option value="<?= htmlspecialchars($cart['IDCarteira']) ?>"
                                            <?= ($carteira_sugerida === $cart['IDCarteira']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cart['TipoCarteira']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="categoria_id" class="form-label text-light fw-semibold">Categoria <span
                                            class="text-secondary fw-normal small">(opcional)</span></label>
                                    <select name="categoria_id" id="categoria_id"
                                        class="form-select bg-dark border-secondary text-light">
                                        <option value="">Sem categoria</option>
                                        <?php foreach ($categorias as $cat): ?>
                                            <option value="<?= htmlspecialchars($cat['IDCategoria']) ?>"
                                                <?= (isset($_POST['categoria_id']) && $_POST['categoria_id'] === $cat['IDCategoria']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat['NomeCategoria']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="subcategoria_id" class="form-label text-light fw-semibold">Subcategoria
                                        <span class="text-secondary fw-normal small">(opcional)</span></label>
                                    <select name="subcategoria_id" id="subcategoria_id"
                                        class="form-select bg-dark border-secondary text-light" disabled>
                                        <option value="">Selecione a categoria primeiro</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="data_registro" class="form-label text-light fw-semibold">Data da
                                        Movimentação</label>
                                    <input type="date" name="data_registro" id="data_registro"
                                        class="form-control bg-dark border-secondary text-light"
                                        value="<?= htmlspecialchars($_POST['data_registro'] ?? date('Y-m-d')) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="data_vencimento" class="form-label text-light fw-semibold">Data de
                                        Vencimento <span class="text-secondary fw-normal small">(opcional)</span></label>
                                    <input type="date" name="data_vencimento" id="data_vencimento"
                                        class="form-control bg-dark border-secondary text-light"
                                        value="<?= htmlspecialchars($_POST['data_vencimento'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="mb-4 p-3 bg-dark rounded-3 border border-secondary-subtle">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="recorrente" id="recorrente"
                                        <?= isset($_POST['recorrente']) ? 'checked' : '' ?>>
                                    <label class="form-check-label text-light fw-semibold" for="recorrente">Conta fixa /
                                        recorrente</label>
                                    <div class="text-secondary small mt-1">Ex: plano de internet, aluguel, assinatura.</div>
                                </div>
                                <div id="bloco_recorrencia" class="mt-3" style="display:none;">
                                    <label for="dia_vencimento" class="form-label text-light fw-semibold">Todo dia</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="number" name="dia_vencimento" id="dia_vencimento"
                                            class="form-control bg-body-tertiary border-secondary text-light"
                                            style="width:90px;" min="1" max="31" placeholder="Ex: 10"
                                            value="<?= htmlspecialchars($_POST['dia_vencimento'] ?? '') ?>">
                                        <span class="text-secondary">de cada mês</span>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit"
                                    class="btn btn-primary fw-bold text-dark py-3 fs-5 shadow cardCentral">
                                    <i class="bi bi-check-circle-fill me-2"></i> Salvar Transação
                                </button>
                            </div>

                        </form>
                    </div>
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

    // AJAX — Subcategorias (Apenas se precisar, a Categoria agora já vem carregada do PHP)
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