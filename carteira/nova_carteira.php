<?php
// 1. Verificação de Segurança
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../usuario/login.php");
    exit;
}

// 2. Puxa o cabeçalho (voltando uma pasta para achar a geral)
require_once '../geral/header.php';
?>

<main class="container py-5 mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            
            <div class="mb-4">
                <a href="../dashboard.php" class="text-secondary text-decoration-none custom-link">
                    <i class="bi bi-arrow-left me-1"></i> Voltar para o Painel
                </a>
            </div>

            <div class="card bg-body-tertiary border-secondary-subtle shadow-lg p-4 p-md-5 rounded-4">
                
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 rounded-circle mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-wallet-fill text-primary" style="font-size: 2.5rem;"></i>
                    </div>
                    <h2 class="fw-bold text-light">Nova Carteira</h2>
                    <p class="text-light opacity-75">Crie um novo espaço para gerenciar suas finanças.</p>
                </div>

                <form action="processa_carteira.php" method="POST">
                    
                    <div class="mb-4">
                        <label for="tipo_carteira" class="form-label text-light opacity-75 fw-semibold">Nome ou Tipo da Carteira</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-secondary">
                                <i class="bi bi-tag-fill"></i>
                            </span>
                            <input type="text" class="form-control form-control-lg bg-dark border-secondary text-light" 
                                   id="tipo_carteira" name="tipo_carteira" required 
                                   placeholder="Ex: Conta Pessoal, Conta Famíliar">
                        </div>
                        <div class="form-text text-secondary mt-2">
                            Use um nome que facilite a identificação no seu dia a dia.
                        </div>
                    </div>

                    <div class="d-grid mt-5">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold text-dark fs-5 cardCentral py-3">
                            <i class="bi bi-check-lg me-2"></i> Salvar Carteira
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</main>

<?php 
// Puxa o rodapé
require_once '../geral/footer.php'; 
?>