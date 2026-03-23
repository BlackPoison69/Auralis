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
    // 3. Busca TODAS as carteiras do usuário
    $sql = 'SELECT "IDCarteira", "TipoCarteira" FROM "Carteira" WHERE "FKUsuarioDono" = :usuario_id ORDER BY "TipoCarteira" ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id]);
    $carteiras = $stmt->fetchAll();
} catch (PDOException $e) {
    $carteiras = [];
}

$totalCarteiras = count($carteiras);

// --- NOVA LÓGICA DO FILTRO ---
// Pega a carteira selecionada na URL ou define 'todas' como padrão
$carteira_selecionada = isset($_GET['carteira']) ? $_GET['carteira'] : 'todas';

// Define o texto base do saldo
$texto_saldo = "Soma de todas as carteiras";

// Se não for 'todas', vamos procurar o nome da carteira escolhida para mudar o texto
if ($carteira_selecionada !== 'todas') {
    foreach ($carteiras as $cart) {
        if ($cart['IDCarteira'] == $carteira_selecionada) {
            $texto_saldo = "Saldo da carteira: " . htmlspecialchars($cart['TipoCarteira']);
            break;
        }
    }

    // NOTA: Aqui no futuro você também vai alterar a sua query (SQL) que busca o valor de R$ 0,00 
    // para filtrar pelo ID da $carteira_selecionada!
}
// -----------------------------

require_once 'geral/header.php';
$primeiroNome = explode(' ', $_SESSION['usuario_nome'])[0];
?>

<main class="container py-4 mt-3 flex-grow-1" style="min-height: 100vh;">

    <?php if ($totalCarteiras == 0): ?>
        <div class="row justify-content-center mt-5 pt-5">
            <div class="col-md-8 text-center">
                <div class="mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-dark border border-secondary-subtle rounded-circle"
                        style="width: 120px; height: 120px;">
                        <i class="bi bi-wallet2 text-secondary opacity-50" style="font-size: 3rem;"></i>
                    </div>
                </div>
                <h2 class="fw-bold text-light mb-3">Nenhuma carteira encontrada</h2>
                <p class="text-secondary mb-5 fs-5 px-md-5">
                    Para começar a controlar o seu dinheiro, você precisa criar o seu primeiro espaço.
                </p>
                <a href="carteira/nova_carteira.php"
                    class="btn btn-primary btn-lg fw-bold text-dark px-5 py-3 shadow cardCentral">
                    <i class="bi bi-plus-circle-fill me-2"></i> Criar Minha Primeira Carteira
                </a>
            </div>
        </div>

    <?php else: ?>

        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary-subtle pb-3">
            <div>
                <h2 class="fw-bold text-light mb-0">Visão Geral</h2>
            </div>

            <div class="d-flex gap-2">
                <select class="form-select bg-dark border-secondary text-light shadow-sm" style="width: 200px;"
                    id="seletor_carteira" onchange="window.location.href='?carteira=' + this.value;">
                    <option value="todas" <?= $carteira_selecionada === 'todas' ? 'selected' : '' ?>>Todas as Carteiras
                    </option>

                    <?php foreach ($carteiras as $cart): ?>
                        <option value="<?= htmlspecialchars($cart['IDCarteira']); ?>"
                            <?= $carteira_selecionada == $cart['IDCarteira'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cart['TipoCarteira']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <a href="nova_transacao.php" class="btn btn-primary fw-semibold text-dark cardCentral d-flex align-items-center">
                    <i class="bi bi-plus-lg me-1"></i> Transação
                </a>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card bg-body-tertiary border-secondary-subtle shadow-sm h-100 rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="card-title text-secondary mb-0 fw-semibold">Saldo Atual</h6>
                            <div class="p-2 bg-primary bg-opacity-10 rounded-3">
                                <i class="bi bi-wallet2 text-primary fs-5"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold text-light mb-1">R$ 0,00</h3>
                        <p class="text-secondary small mb-0"><?= $texto_saldo; ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-body-tertiary border-secondary-subtle shadow-sm h-100 rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="card-title text-secondary mb-0 fw-semibold">Receitas (Mês)</h6>
                            <div class="p-2 bg-success bg-opacity-10 rounded-3">
                                <i class="bi bi-graph-up-arrow text-success fs-5"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold text-light mb-1">R$ 0,00</h3>
                        <p class="text-success small mb-0"><i class="bi bi-arrow-up-short"></i> +0% que o mês passado</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-body-tertiary border-secondary-subtle shadow-sm h-100 rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="card-title text-secondary mb-0 fw-semibold">Despesas (Mês)</h6>
                            <div class="p-2 bg-danger bg-opacity-10 rounded-3">
                                <i class="bi bi-graph-down-arrow text-danger fs-5"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold text-light mb-1">R$ 0,00</h3>
                        <p class="text-danger small mb-0"><i class="bi bi-arrow-down-short"></i> -0% que o mês passado</p>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>

</main>

<?php require_once 'geral/footer.php'; ?>