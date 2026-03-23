<?php
// ==========================================
// CREDENCIAIS DO SUPABASE (POSTGRESQL - POOLER IPv4)
// ==========================================

$host     = 'aws-1-us-east-1.pooler.supabase.com'; 
$port     = '5432'; 
$dbname   = 'postgres'; 
$user     = 'postgres.aoqtlhdmoozoqrunneyd'; 
$password = 'AuralisSenha'; // Substitua por sua senha do Supabase

// ==========================================

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       
        PDO::ATTR_EMULATE_PREPARES   => false,                  
    ];

    $pdo = new PDO($dsn, $user, $password, $options);
    
    // Deixe esta linha sem o // no início para testar
  // echo "Conexão com o Supabase realizada com sucesso no Auralis!"; 

} catch (PDOException $e) {
    die("Erro ao conectar com o banco de dados Auralis: " . $e->getMessage());
}
?>