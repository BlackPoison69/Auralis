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
    // Verifica se a caixinha "Salvar neste computador" foi marcada
    $lembrar_me = isset($_POST['lembrar']) ? true : false;

    // Validação de segurança: se algum campo vier vazio, barra na hora
    if (empty($email) || empty($senha)) {
        header("Location: login.php?erro=vazio");
        exit;
    }

    try {
        // 3. Busca apenas o usuário que tem esse e-mail no Supabase
        $sql = "SELECT \"IDUsuario\", \"Nome\", \"Senha\", \"NivelAcesso\" FROM \"Usuario\" WHERE \"Email\" = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        
        // Pega o resultado
        $usuario = $stmt->fetch();

        // 4. A Mágica da Criptografia
        if ($usuario && password_verify($senha, $usuario['Senha'])) {
            
            // 5. Segurança Avançada
            session_regenerate_id(true);

            // 6. Cria o "Crachá" do usuário
            $_SESSION['usuario_id']   = $usuario['IDUsuario'];
            $_SESSION['usuario_nome'] = $usuario['Nome'];
            $_SESSION['nivel_acesso'] = $usuario['NivelAcesso'];

            // ====================================================================
            // 6.5 A MÁGICA DO "LEMBRAR-ME" (COOKIES)
            // ====================================================================
            if ($lembrar_me) {
                // Cria um token único, seguro e aleatório
                $token = bin2hex(random_bytes(32));
                
                // Salva o token no banco de dados (Vamos precisar criar essa coluna depois se não existir, mas por enquanto, salvaremos uma versão mais simples: ID do usuário e um Hash)
                // Para manter simples sem alterar o banco agora, vamos criar um cookie assinado.
                // O formato será: IDUsuario:HashSeguro
                $chave_secreta = "Auralis2026_UltraSecretKey"; // Nunca mude isso em produção
                $assinatura = hash_hmac('sha256', $usuario['IDUsuario'], $chave_secreta);
                $conteudo_cookie = $usuario['IDUsuario'] . ':' . $assinatura;

                // Envia o cookie para o computador da pessoa. Ele dura 30 dias.
                setcookie('auralis_remember', $conteudo_cookie, time() + (86400 * 30), "/"); // 86400 = 1 dia
            }
            // ====================================================================

            // 7. Redireciona para o Painel de Controle
            header("Location: ../dashboard.php"); 
            exit;
            
        } else {
            header("Location: login.php?erro=invalido");
            exit;
        }

    } catch (PDOException $e) {
        die("Erro ao tentar fazer login: " . $e->getMessage());
    }

} else {
    header("Location: login.php");
    exit;
}
?>