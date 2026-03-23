<?php
// 1. Inicia a "memória" da sessão antes de qualquer outra coisa
session_start();

// 2. Puxa a conexão com o banco
require_once '../config/conexao.php';

// Verifica se a requisição veio mesmo do botão de "Entrar"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Limpa o e-mail e pega a senha digitada
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    // Validação de segurança: se algum campo vier vazio, barra na hora
    if (empty($email) || empty($senha)) {
        header("Location: login.php?erro=vazio");
        exit;
    }

    try {
        // 3. Busca apenas o usuário que tem esse e-mail no Supabase
        // (Sempre coloque os nomes das colunas entre aspas duplas no Postgres)
        $sql = "SELECT \"IDUsuario\", \"Nome\", \"Senha\", \"NivelAcesso\" FROM \"Usuario\" WHERE \"Email\" = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        
        // Pega o resultado (vai retornar um array com os dados ou 'false' se não achar)
        $usuario = $stmt->fetch();

        // 4. A Mágica da Criptografia: O usuário existe e a senha bate?
        // A função password_verify compara a senha digitada com aquele código embaralhado salvo no banco
        if ($usuario && password_verify($senha, $usuario['Senha'])) {
            
            // 5. Segurança Avançada: Troca a "identidade" da sessão para evitar roubo de cookies
            session_regenerate_id(true);

            // 6. Cria o "Crachá" do usuário preenchendo as variáveis de sessão
            $_SESSION['usuario_id']   = $usuario['IDUsuario'];
            $_SESSION['usuario_nome'] = $usuario['Nome'];
            $_SESSION['nivel_acesso'] = $usuario['NivelAcesso'];

            // 7. Redireciona para o Painel de Controle (Dashboard)
            // Usamos ../ para sair da pasta usuario e ir para a raiz onde ficará o dashboard
            header("Location: ../dashboard.php"); 
            exit;
            
        } else {
            // Segurança Básica: Nunca diga se o que está errado é o e-mail ou a senha.
            // Diga apenas "Credenciais inválidas" para não dar dicas a invasores.
            header("Location: login.php?erro=invalido");
            exit;
        }

    } catch (PDOException $e) {
        // Se o banco cair ou a consulta der erro
        die("Erro ao tentar fazer login: " . $e->getMessage());
    }

} else {
    // Se tentarem acessar esse arquivo pela URL sem preencher o formulário
    header("Location: login.php");
    exit;
}
?>