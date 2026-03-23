<?php
// O header precisa iniciar a sessão no topo para conseguir ler os dados do usuário
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#212529">
    <title>Auralis | Gestão Financeira</title>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="/Auralis/geral/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="/Auralis/geral/style.css">
</head>

<body class="d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="/Auralis/geral/index.php">
                Aura<span style="color: white">lis</span>
            </a>

            <div class="d-flex ms-auto gap-3 align-items-center">

                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <?php
                    // Truque PHP: Pega o nome completo, divide pelos espaços e salva só a primeira palavra
                    $primeiroNome = explode(' ', $_SESSION['usuario_nome'])[0];
                    ?>

                    <div class="dropdown">
                        <a href="#"
                            class="d-flex align-items-center text-light text-decoration-none dropdown-toggle custom-link"
                            id="menuUsuario" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="me-2 d-none d-md-inline">Olá,
                                <strong><?= htmlspecialchars($primeiroNome); ?></strong></span>
                            <i class="bi bi-person-circle fs-3 text-primary cardCentral"></i>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow mt-2" aria-labelledby="menuUsuario">
                            <li>
                                <a class="dropdown-item d-flex align-items-center py-2" href="/Auralis/dashboard.php">
                                    <i class="bi bi-speedometer2 me-2 text-secondary"></i> Meu Painel
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center py-2" href="#">
                                    <i class="bi bi-gear me-2 text-secondary"></i> Configurações
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider border-secondary-subtle">
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center py-2 text-danger"
                                    href="/Auralis/usuario/logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i> Sair
                                </a>
                            </li>
                        </ul>
                    </div>

                <?php else: ?>
                    <a href="/Auralis/usuario/login.php" class="btn btn-outline-light px-4">Login</a>
                    <a href="/Auralis/usuario/cadastro.php"
                        class="btn btn-primary px-4 fw-semibold text-dark cardCentral">Cadastro</a>
                <?php endif; ?>

            </div>
        </div>
    </nav>