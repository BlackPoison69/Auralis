<?php
// 1. Inicia a sessão e verifica a segurança
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: usuario/login.php");
    exit;
}

// 2. Conecta ao banco de dados
require_once 'config/conexao.php';

$usuario_id = $_SESSION['usuario_id'];
$carteiras = [];

try {
    $sql = 'SELECT "IDCarteira", "TipoCarteira" FROM "Carteira" WHERE "FKUsuarioDono" = :usuario_id ORDER BY "TipoCarteira" ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id]);
    $carteiras = $stmt->fetchAll();
} catch (PDOException $e) {
    $carteiras = [];
}

$totalCarteiras = count($carteiras);

// --- LÓGICA DE NAVEGAÇÃO DE TEMPO (MESES) ---
$mes_atual = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
$ano_atual = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');

// Cálculo para os botões Voltar/Avançar
$mes_ant = $mes_atual - 1; $ano_ant = $ano_atual;
if ($mes_ant < 1) { $mes_ant = 12; $ano_ant--; }

$mes_prox = $mes_atual + 1; $ano_prox = $ano_atual;
if ($mes_prox > 12) { $mes_prox = 1; $ano_prox++; }

$meses_pt = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
$nome_mes = $meses_pt[$mes_atual];


// --- LÓGICA DE AÇÃO: ALTERAR STATUS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    $id_registro = $_POST['registro_id'];
    $novo_status = $_POST['novo_status'];

    if (in_array($novo_status, ['pendente', 'efetivado'])) {
        try {
            $sqlToggle = 'UPDATE "Registro" SET "StatusRegistro" = :status WHERE "IDRegistro" = :id AND "FKUsuario" = :uid';
            $stmtToggle = $pdo->prepare($sqlToggle);
            $stmtToggle->execute([':status' => $novo_status, ':id' => $id_registro, ':uid' => $usuario_id]);
            
            // Mantém os filtros ao recarregar
            $redirectUrl = "dashboard.php?mes={$mes_atual}&ano={$ano_atual}";
            if (isset($_GET['carteira'])) $redirectUrl .= "&carteira=" . $_GET['carteira'];
            header("Location: " . $redirectUrl);
            exit;
        } catch (PDOException $e) {}
    }
}

// --- LÓGICA DO FILTRO DE CARTEIRA ---
if (isset($_GET['carteira'])) {
    $carteira_selecionada = $_GET['carteira'];
} else {
    $carteira_selecionada = ($totalCarteiras > 0) ? $carteiras[0]['IDCarteira'] : null;
}

$nome_carteira_atual = "Carteira";
foreach ($carteiras as $cart) {
    if ($cart['IDCarteira'] == $carteira_selecionada) {
        $nome_carteira_atual = $cart['TipoCarteira'];
        break;
    }
}

// Constrói os links de navegação mantendo a carteira selecionada
$link_ant = "?mes={$mes_ant}&ano={$ano_ant}" . ($carteira_selecionada ? "&carteira={$carteira_selecionada}" : "");
$link_prox = "?mes={$mes_prox}&ano={$ano_prox}" . ($carteira_selecionada ? "&carteira={$carteira_selecionada}" : "");

// --- LÓGICA DE DADOS REAIS DO DASHBOARD ---
$saldoAtual = 0.00;
$receitasMes = 0.00;
$despesasMes = 0.00;
$transacoes = [];

if ($carteira_selecionada) {
    try {
        // 1. Saldo Histórico (Todo o dinheiro real da conta até hoje)
        $sqlSaldo = '
            SELECT 
                COALESCE(SUM(CASE WHEN "TipoRegistro" = \'receita\' THEN "Valor" ELSE 0 END), 0) as total_rec_hist,
                COALESCE(SUM(CASE WHEN "TipoRegistro" = \'despesa\' THEN "Valor" ELSE 0 END), 0) as total_des_hist
            FROM "Registro"
            WHERE "FKCarteira" = :carteira_id 
              AND "FKUsuario" = :usuario_id
              AND "StatusRegistro" = \'efetivado\'
        ';
        $stmtSaldo = $pdo->prepare($sqlSaldo);
        $stmtSaldo->execute([':carteira_id' => $carteira_selecionada, ':usuario_id' => $usuario_id]);
        $resultSaldo = $stmtSaldo->fetch();
        
        if ($resultSaldo) {
            $saldoAtual = (float)$resultSaldo['total_rec_hist'] - (float)$resultSaldo['total_des_hist'];
        }

        // 2. Calcula Receitas/Despesas FILTRANDO PELO MÊS E ANO SELECIONADOS
        $sqlMes = '
            SELECT 
                COALESCE(SUM(CASE WHEN "TipoRegistro" = \'receita\' THEN "Valor" ELSE 0 END), 0) as total_receitas,
                COALESCE(SUM(CASE WHEN "TipoRegistro" = \'despesa\' THEN "Valor" ELSE 0 END), 0) as total_despesas
            FROM "Registro"
            WHERE "FKCarteira" = :carteira_id 
              AND "FKUsuario" = :usuario_id
              AND "StatusRegistro" = \'efetivado\'
              AND EXTRACT(MONTH FROM "MomentoRegistro") = :mes
              AND EXTRACT(YEAR FROM "MomentoRegistro") = :ano
        ';
        $stmtMes = $pdo->prepare($sqlMes);
        $stmtMes->execute([
            ':carteira_id' => $carteira_selecionada, 
            ':usuario_id' => $usuario_id,
            ':mes' => $mes_atual,
            ':ano' => $ano_atual
        ]);
        $resultMes = $stmtMes->fetch();

        if ($resultMes) {
            $receitasMes = (float) $resultMes['total_receitas'];
            $despesasMes = (float) $resultMes['total_despesas'];
        }

        // 3. Busca as Transações DO MÊS SELECIONADO
        $sqlTransacoes = '
            SELECT 
                r."IDRegistro", r."MomentoRegistro", r."Valor", r."Descricao", r."TipoRegistro", r."StatusRegistro",
                r."DataVencimento", r."Recorrente", r."DiaVencimento",
                c."NomeCategoria"
            FROM "Registro" r
            LEFT JOIN "Categoria" c ON r."FKCategoria" = c."IDCategoria"
            WHERE r."FKCarteira" = :carteira_id 
              AND r."FKUsuario" = :usuario_id
              AND EXTRACT(MONTH FROM r."MomentoRegistro") = :mes
              AND EXTRACT(YEAR FROM r."MomentoRegistro") = :ano
            ORDER BY r."MomentoRegistro" DESC
            LIMIT 50
        ';
        $stmtTrans = $pdo->prepare($sqlTransacoes);
        $stmtTrans->execute([
            ':carteira_id' => $carteira_selecionada,
            ':usuario_id' => $usuario_id,
            ':mes' => $mes_atual,
            ':ano' => $ano_atual
        ]);
        $transacoes = $stmtTrans->fetchAll();

    } catch (PDOException $e) {}
}

require_once 'geral/header.php';
$primeiroNome = explode(' ', $_SESSION['usuario_nome'])[0];
?>

<main class="container py-4 mt-3 flex-grow-1" style="min-height: 100vh;">

    <?php if ($totalCarteiras == 0): ?>
        <div class="row justify-content-center mt-5 pt-5">
            <div class="col-md-8 text-center">
                <div class="mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-dark border border-secondary-subtle rounded-circle" style="width: 120px; height: 120px;">
                        <i class="bi bi-wallet2 text-secondary opacity-50" style="font-size: 3rem;"></i>
                    </div>
                </div>
                <h2 class="fw-bold text-light mb-3">Nenhuma carteira encontrada</h2>
                <p class="text-secondary mb-5 fs-5 px-md-5">Para começar a controlar o seu dinheiro, você precisa criar o seu primeiro espaço.</p>
                <a href="carteira/nova_carteira.php" class="btn btn-primary btn-lg fw-bold text-dark px-5 py-3 shadow cardCentral">
                    <i class="bi bi-plus-circle-fill me-2"></i> Criar Minha Primeira Carteira
                </a>
            </div>
        </div>
    <?php else: ?>

        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary-subtle pb-3 flex-wrap gap-3">
            <div class="d-flex align-items-center gap-4">
                <h2 class="fw-bold text-light mb-0">Visão Geral</h2>
                


                
                <div class="d-flex align-items-center bg-dark border border-secondary-subtle rounded-pill px-2 py-1 shadow-sm">
<a href="<?= $link_ant ?>" class="btn btn-sm btn-link text-white transition-hover text-decoration-none">
    <i class="bi bi-chevron-left"></i>
</a>

<span class="text-light fw-semibold px-3" style="min-width: 130px; text-align: center;">
    <?= $nome_mes ?> <?= $ano_atual ?>
</span>

<a href="<?= $link_prox ?>" class="btn btn-sm btn-link text-white transition-hover text-decoration-none">
    <i class="bi bi-chevron-right"></i>
</a>
                </div>
            </div>


            <div class="d-flex gap-2">
                <div class="d-flex align-items-center gap-3">
                    <select class="form-select bg-dark border-secondary text-light shadow-sm fw-semibold" style="width: 200px;"
                        id="seletor_carteira" onchange="window.location.href='?mes=<?= $mes_atual ?>&ano=<?= $ano_atual ?>&carteira=' + this.value;">
                        <?php foreach ($carteiras as $cart): ?>
                            <option value="<?= htmlspecialchars($cart['IDCarteira']); ?>"
                                <?= $carteira_selecionada == $cart['IDCarteira'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cart['TipoCarteira']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="vr bg-secondary opacity-25 mx-1 d-none d-md-block"></div>

                    <div class="d-flex gap-2">
                        <a href="nova_transacao.php?carteira_id=<?= urlencode($carteira_selecionada) ?>&tipo=receita"
                            class="btn btn-outline-success fw-bold d-flex align-items-center px-3 rounded-pill transition-hover shadow-sm">
                            <i class="bi bi-arrow-up-short fs-5"></i> <span class="d-none d-sm-inline ms-1">Receita</span>
                        </a>

                        <a href="nova_transacao.php?carteira_id=<?= urlencode($carteira_selecionada) ?>&tipo=despesa"
                            class="btn btn-outline-danger fw-bold d-flex align-items-center px-3 rounded-pill transition-hover shadow-sm">
                            <i class="bi bi-arrow-down-short fs-5"></i> <span class="d-none d-sm-inline ms-1">Despesa</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card bg-body-tertiary border-secondary-subtle shadow-sm h-100 rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="card-title text-secondary mb-0 fw-semibold">Saldo: <?= htmlspecialchars($nome_carteira_atual); ?></h6>
                            <div class="p-2 bg-primary bg-opacity-10 rounded-3">
                                <i class="bi bi-wallet2 text-primary fs-5"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-1 <?= $saldoAtual < 0 ? 'text-danger' : 'text-light' ?>">
                            R$ <?= number_format($saldoAtual, 2, ',', '.') ?>
                        </h3>
                        <p class="text-secondary small mb-0">Total disponível hoje</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-body-tertiary border-secondary-subtle shadow-sm h-100 rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="card-title text-secondary mb-0 fw-semibold">Receitas (<?= $nome_mes ?>)</h6>
                            <div class="p-2 bg-success bg-opacity-10 rounded-3">
                                <i class="bi bi-graph-up-arrow text-success fs-5"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold text-success mb-1">
                            R$ <?= number_format($receitasMes, 2, ',', '.') ?>
                        </h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-body-tertiary border-secondary-subtle shadow-sm h-100 rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="card-title text-secondary mb-0 fw-semibold">Despesas (<?= $nome_mes ?>)</h6>
                            <div class="p-2 bg-danger bg-opacity-10 rounded-3">
                                <i class="bi bi-graph-down-arrow text-danger fs-5"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold text-danger mb-1">
                            R$ <?= number_format($despesasMes, 2, ',', '.') ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="fw-bold text-light mb-4">Transações de <?= $nome_mes ?></h4>
        <div class="card bg-dark border-secondary-subtle shadow-sm rounded-4 overflow-hidden">
            
            <?php if (empty($transacoes)): ?>
                <div class="card-body p-5 text-center">
                    <i class="bi bi-receipt text-secondary opacity-50 display-1 mb-3"></i>
                    <h5 class="text-light fw-bold">Nenhum registro em <?= $nome_mes ?></h5>
                    <p class="text-secondary mb-0">Esta carteira não tem movimentações neste mês.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive" style="overflow-x: visible;">
                    <table class="table table-dark table-hover align-middle mb-0 auralis-table">
                        <thead class="table-active border-secondary-subtle text-secondary small text-uppercase">
                            <tr>
                                <th class="ps-4 py-3 border-0">Descrição</th>
                                <th class="py-3 border-0">Categoria</th>
                                <th class="py-3 border-0">Data</th>
                                <th class="py-3 border-0">Status</th>
                                <th class="text-end pe-4 py-3 border-0">Valor</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <?php foreach ($transacoes as $index => $t): 
                                $isDespesa = ($t['TipoRegistro'] === 'despesa');
                                $sinalValor = $isDespesa ? '-' : '+';
                                $corValor = $isDespesa ? 'text-danger' : 'text-success';
                                $dataFormatada = date('d/m/Y', strtotime($t['MomentoRegistro']));
                                $iconeTipo = $isDespesa ? '<i class="bi bi-arrow-down-short fs-5 text-danger bg-danger bg-opacity-10 rounded-circle p-1 me-3"></i>' 
                                                        : '<i class="bi bi-arrow-up-short fs-5 text-success bg-success bg-opacity-10 rounded-circle p-1 me-3"></i>';
                                
                                $rowId = "transacao-" . $index;
                                $isPendente = ($t['StatusRegistro'] === 'pendente');
                                $textoAcaoStatus = $isDespesa ? 'Marcar como Pago' : 'Marcar como Recebido';
                            ?>
                            <tr data-bs-toggle="collapse" data-bs-target="#<?= $rowId ?>" class="cursor-pointer transition-hover" style="cursor: pointer;">
                                <td class="ps-4 py-3 border-secondary-subtle">
                                    <div class="d-flex align-items-center">
                                        <?= $iconeTipo ?>
                                        <span class="text-light fw-semibold"><?= htmlspecialchars($t['Descricao']) ?></span>
                                    </div>
                                </td>
                                <td class="py-3 border-secondary-subtle text-secondary small">
                                    <?= htmlspecialchars($t['NomeCategoria'] ?? 'Sem categoria') ?>
                                </td>
                                <td class="py-3 border-secondary-subtle text-secondary small">
                                    <?= $dataFormatada ?>
                                </td>
                                <td class="py-3 border-secondary-subtle">
                                    <?php if ($isPendente): ?>
                                        <span class="badge bg-warning text-dark px-2 py-1 rounded-pill fw-semibold shadow-sm"><i class="bi bi-clock-history me-1"></i> Pendente</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-25 text-light px-2 py-1 rounded-pill"><i class="bi bi-check2-circle me-1"></i> Efetivado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 py-3 border-secondary-subtle fw-bold <?= $corValor ?>">
                                    <?= $sinalValor ?> R$ <?= number_format($t['Valor'], 2, ',', '.') ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="5" class="p-0 border-0">
                                    <div class="collapse" id="<?= $rowId ?>">
                                        <div class="p-4 bg-charcoal-analysis border-bottom border-secondary-subtle d-flex justify-content-between align-items-start">
                                            <div class="d-flex gap-4">
                                                <div>
                                                    <span class="d-block text-secondary small text-uppercase mb-1">Vencimento</span>
                                                    <span class="text-light fs-6">
                                                        <?= !empty($t['DataVencimento']) ? date('d/m/Y', strtotime($t['DataVencimento'])) : '<span class="text-muted">Não definido</span>' ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="d-block text-secondary small text-uppercase mb-1">Recorrência</span>
                                                    <span class="text-light fs-6">
                                                        <?= $t['Recorrente'] ? 'Sim (Dia ' . htmlspecialchars($t['DiaVencimento']) . ')' : 'Não' ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <form method="POST" action="">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="registro_id" value="<?= $t['IDRegistro'] ?>">

                                                    <?php if ($isPendente): ?>
                                                        <input type="hidden" name="novo_status" value="efetivado">
                                                        <button type="submit" class="btn btn-sm btn-outline-success rounded-pill fw-semibold px-3 d-inline-flex align-items-center gap-1">
                                                            <i class="bi bi-check-circle"></i>
                                                            <?= $textoAcaoStatus ?>
                                                        </button>
                                                    <?php else: ?>
                                                        <input type="hidden" name="novo_status" value="pendente">
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3 d-inline-flex align-items-center gap-1">
                                                            <i class="bi bi-arrow-counterclockwise"></i>
                                                            Desfazer
                                                        </button>
                                                    <?php endif; ?>
                                                </form>

                                                <button class="btn btn-sm btn-outline-warning rounded-pill fw-semibold px-3 d-inline-flex align-items-center gap-1 transition-hover">
                                                    <i class="bi bi-pencil-square"></i>
                                                    Editar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</main>

<style>
    .bg-charcoal-analysis { background-color: #1a1d21; }
    .auralis-table > tbody > tr.cursor-pointer:hover > td { background-color: rgba(255, 255, 255, 0.03) !important; }
    .table-active { background-color: #1a1d21 !important; }
</style>

<?php require_once 'geral/footer.php'; ?>