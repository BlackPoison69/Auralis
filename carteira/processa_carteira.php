<?php
// 1. Inicia a sessão e barra intrusos
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../usuario/login.php");
    exit;
}

// 2. Chama a conexão com o banco
require_once '../config/conexao.php';

// 3. Verifica se os dados vieram do botão "Salvar Carteira"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Pega o nome digitado e remove espaços em branco extras
    $tipoCarteira = trim($_POST['tipo_carteira']);
    
    // Pega o seu "Crachá" (ID) da sessão
    $usuarioId = $_SESSION['usuario_id'];

    // Pequena validação de segurança
    if (empty($tipoCarteira)) {
        die("Erro: O nome da carteira não pode ficar vazio.");
    }

    try {
        // 4. Prepara o comando SQL (Blindado com PDO)
        // Usamos aspas duplas nas colunas do PostgreSQL
        $sql = 'INSERT INTO "Carteira" ("TipoCarteira", "FKUsuarioDono") VALUES (:tipoCarteira, :usuarioId)';
        $stmt = $pdo->prepare($sql);
        
        // 5. Troca as variáveis falsas pelos dados reais e executa
        $stmt->execute([
            ':tipoCarteira' => $tipoCarteira,
            ':usuarioId'    => $usuarioId
        ]);

        // 6. Se deu tudo certo, volta voando para o Dashboard
        header("Location: ../dashboard.php?carteira=sucesso");
        exit;

    } catch (PDOException $e) {
        die("Erro ao salvar a carteira no banco: " . $e->getMessage());
    }
} else {
    // Se alguém tentar acessar esse arquivo direto pela URL, volta pro formulário
    header("Location: nova_carteira.php");
    exit;
}
?>