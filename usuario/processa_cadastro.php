<?php
// 1. Puxa a conexão com o banco (voltando uma pasta para entrar em config)
require_once '../config/conexao.php';

// Verifica se os dados realmente vieram de um formulário (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 2. Receber e limpar os dados digitados (remove espaços em branco nas pontas)
        $nome           = trim($_POST['nome']);
        $documento      = trim($_POST['documento']);
        $nascimento     = trim($_POST['nascimento']);
        $telefone       = trim($_POST['telefone']);
        $email          = trim($_POST['email']);
        $senha          = $_POST['senha'];
        $confirma_senha = $_POST['confirma_senha'];

        // 3. Validação de segurança básica: As senhas batem?
        if ($senha !== $confirma_senha) {
            die("Erro: As senhas não conferem. Por favor, volte e tente novamente.");
        }

        // 4. Lógica Inteligente: É Pessoa Física ou Jurídica?
        // Se o documento tiver mais de 14 caracteres (contando pontos e traços), é CNPJ.
        $tipoPessoa = (strlen($documento) > 14) ? 'PJ' : 'PF';

        // 5. Criptografia nível bancário para a senha (NUNCA salvar senha em texto puro)
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        // Nível de acesso padrão para quem cria a conta
        $nivelAcesso = 'Titular';

        // 6. Preparar o comando SQL de Inserção (Blindado contra SQL Injection)
        // Usamos aspas duplas nas colunas porque o PostgreSQL difere maiúsculas de minúsculas
        $sql = "INSERT INTO \"Usuario\" (
                    \"Nome\", 
                    \"Documento\", 
                    \"DataNascimento\", 
                    \"Telefone\", 
                    \"Email\", 
                    \"Senha\", 
                    \"TipoPessoa\", 
                    \"NivelAcesso\"
                ) VALUES (
                    :nome, 
                    :documento, 
                    :nascimento, 
                    :telefone, 
                    :email, 
                    :senha, 
                    :tipoPessoa, 
                    :nivelAcesso
                )";

        $stmt = $pdo->prepare($sql);

        // 7. Substituir as "variáveis falsas" (:nome) pelos dados reais e executar
        $stmt->execute([
            ':nome'        => $nome,
            ':documento'   => $documento,
            ':nascimento'  => $nascimento,
            ':telefone'    => $telefone,
            ':email'       => $email,
            ':senha'       => $senhaHash,
            ':tipoPessoa'  => $tipoPessoa,
            ':nivelAcesso' => $nivelAcesso
        ]);

        // 8. Se deu tudo certo, joga o usuário para a tela de login
        header("Location: login.php?cadastro=sucesso");
        exit;

    } catch (PDOException $e) {
        // Se der erro (ex: e-mail ou documento já cadastrado), ele avisa
        die("Erro ao salvar no banco de dados: " . $e->getMessage());
    }
} else {
    // Se tentarem acessar esse arquivo direto pela URL, manda de volta pro cadastro
    header("Location: cadastro.php");
    exit;
}
?>