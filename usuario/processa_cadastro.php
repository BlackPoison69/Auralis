<?php
// 1. Puxa a conexão com o banco
require_once '../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 2. Receber e limpar os dados
        $nome           = trim($_POST['nome']);
        $documento      = trim($_POST['documento']);
        $nascimento     = trim($_POST['nascimento']);
        $telefone       = trim($_POST['telefone']);
        $email          = trim($_POST['email']);
        $senha          = $_POST['senha'];
        $confirma_senha = $_POST['confirma_senha'];

        if ($senha !== $confirma_senha) {
            die("Erro: As senhas não conferem.");
        }

        $tipoPessoa = (strlen($documento) > 14) ? 'PJ' : 'PF';
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $nivelAcesso = 'Titular';

        // 3. Inserção do Usuário com RETURNING para pegar o ID gerado na hora
        $sql = "INSERT INTO \"Usuario\" (
                    \"Nome\", \"Documento\", \"DataNascimento\", \"Telefone\", \"Email\", \"Senha\", \"TipoPessoa\", \"NivelAcesso\"
                ) VALUES (
                    :nome, :documento, :nascimento, :telefone, :email, :senha, :tipoPessoa, :nivelAcesso
                ) RETURNING \"IDUsuario\"";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome'         => $nome,
            ':documento'    => $documento,
            ':nascimento'   => $nascimento,
            ':telefone'     => $telefone,
            ':email'        => $email,
            ':senha'        => $senhaHash,
            ':tipoPessoa'   => $tipoPessoa,
            ':nivelAcesso'  => $nivelAcesso
        ]);

        // 4. Recupera o ID gerado pelo banco
        $id_novo_usuario = $stmt->fetchColumn();

        if ($id_novo_usuario) {
            // ==============================================================================
            // LÓGICA DE NEGÓCIO: INJEÇÃO DO KIT INICIAL DE CATEGORIAS
            // ==============================================================================
            $kitInicial = [
                ['nome' => 'Alimentação', 'tipo' => 'despesa', 'icone' => 'bi-cart3'],
                ['nome' => 'Moradia',     'tipo' => 'despesa', 'icone' => 'bi-house-door'],
                ['nome' => 'Transporte',  'tipo' => 'despesa', 'icone' => 'bi-car-front'],
                ['nome' => 'Saúde',       'tipo' => 'despesa', 'icone' => 'bi-heart-pulse'],
                ['nome' => 'Lazer',       'tipo' => 'despesa', 'icone' => 'bi-controller'],
                ['nome' => 'Salário',     'tipo' => 'receita', 'icone' => 'bi-cash-stack'],
                ['nome' => 'Investimentos','tipo' => 'receita', 'icone' => 'bi-graph-up-arrow'],
                ['nome' => 'Outros',      'tipo' => 'receita', 'icone' => 'bi-plus-circle-dotted']
            ];

            $sqlCat = "INSERT INTO \"Categoria\" (\"NomeCategoria\", \"TipoCategoria\", \"IconeCategoria\", \"FKUsuario\") 
                       VALUES (:nome, :tipo, :icone, :uid)";
            $stmtCat = $pdo->prepare($sqlCat);

            foreach ($kitInicial as $cat) {
                $stmtCat->execute([
                    ':nome'  => $cat['nome'],
                    ':tipo'  => $cat['tipo'],
                    ':icone' => $cat['icone'],
                    ':uid'   => $id_novo_usuario
                ]);
            }
            // ==============================================================================
        }

        header("Location: login.php?cadastro=sucesso");
        exit;

    } catch (PDOException $e) {
        die("Erro ao salvar no banco de dados: " . $e->getMessage());
    }
} else {
    header("Location: cadastro.php");
    exit;
}