<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: usuario/login.php");
    exit;
}

require_once 'config/conexao.php';

$usuario_id = $_SESSION['usuario_id'];
$carteiras = [];

// 1. Busca as carteiras para o usuário poder escolher de onde o dinheiro sai ou entra
try {
    $sql = 'SELECT "IDCarteira", "TipoCarteira" FROM "Carteira" WHERE "FKUsuarioDono" = :usuario_id ORDER BY "TipoCarteira" ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id]);
    $carteiras = $stmt->fetchAll();
} catch (PDOException $e) {
    $carteiras = [];
}

// 2. Processamento do formulário (quando o usuário clica em Salvar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo']; // 'receita' ou 'despesa'
    $valor = str_replace(',', '.', $_POST['valor']); // Troca vírgula por ponto para o banco
    $descricao = $_POST['descricao'];
    $data_transacao = $_POST['data_transacao'];
    $carteira_id = $_POST['carteira_id'];

    try {
        // ATENÇÃO: Adapte os nomes da tabela e colunas para o seu banco de dados!
        $sqlInsert = 'INSERT INTO "Transacao" ("FkCarteira", "Tipo", "Valor", "Descricao", "DataTransacao") 
                      VALUES (:carteira_id, :tipo, :valor, :descricao, :data_transacao)';
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->execute([
            ':carteira_id' => $carteira_id,
            ':tipo' => $tipo,
            ':valor' => $valor,
            ':descricao' => $descricao,
            ':data_transacao' => $data_transacao
        ]);

        // Redireciona de volta para o dashboard após salvar com sucesso
        header("Location: index.php?sucesso=1");
        exit;
    } catch (PDOException $e) {
        $erro = "Erro ao salvar a transação: " . $e->getMessage();
    }
}

require_once 'geral/header.php';
?>

<main class="container py-4 mt-3 flex-grow-1">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div
                class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary-subtle pb-3">
                <h2 class="fw-bold text-light mb-0">Nova Transação</h2>
                <a href="index.php" class="btn btn-outline-warning">Voltar</a>
            </div>

            <?php if (isset($erro)): ?>
                <div class="alert alert-danger">
                    <?= $erro ?>
                </div>
            <?php endif; ?>

            <div class="card bg-body-tertiary border-secondary-subtle shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form method="POST" action="">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-light">Tipo de Transação</label>
                                <select name="tipo" class="form-select bg-dark border-secondary text-light" required>
                                    <option value="" disabled selected>Selecione...</option>
                                    <option value="receita">Receita (Entrada)</option>
                                    <option value="despesa">Despesa (Saída)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-light">Valor (R$)</label>
                                <input type="number" step="0.01" name="valor"
                                    class="form-control bg-dark border-secondary text-light" placeholder="0.00"
                                    required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-light">Descrição</label>
                            <input type="text" name="descricao" class="form-control bg-dark border-secondary text-light"
                                placeholder="Ex: Conta de Luz, Venda, Salário..." required>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-light">Data</label>
                                <input type="date" name="data_transacao"
                                    class="form-control bg-dark border-secondary text-light"
                                    value="<?= date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-light">Carteira</label>
                                <select name="carteira_id" class="form-select bg-dark border-secondary text-light"
                                    required>
                                    <option value="" disabled selected>Escolha a carteira...</option>
                                    <?php foreach ($carteiras as $cart): ?>
                                        <option value="<?= htmlspecialchars($cart['IDCarteira']); ?>">
                                            <?= htmlspecialchars($cart['TipoCarteira']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-bold text-dark py-2">
                                <i class="bi bi-check-lg me-2"></i> Salvar Transação
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</main>

<?php require_once 'geral/footer.php'; ?>