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
    <meta name="theme-color" content="#121418">
    <title>Auralis | Gestão Financeira</title>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">

    <link href="/Auralis/geral/fonts/inter.css" rel="stylesheet">

    <link href="/Auralis/geral/css/bootstrap.min.css" rel="stylesheet">

    <link href="/Auralis/geral/css/bootstrap-icons.css" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="/Auralis/geral/style.css">
</head>

<body class="d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg border-bottom border-secondary-subtle sticky-top shadow-sm"
        style="background-color: rgba(18, 20, 24, 0.85); backdrop-filter: blur(12px);">
        <div class="container">

            <a class="navbar-brand fw-bold fs-3 d-flex align-items-center" href="/Auralis/geral/index.php"
                style="letter-spacing: -0.05em;">
                <i style=" color: gold !important;" class="bi bi-hexagon-half text-primary me-2"></i>
                <span style="color: gold !important;" class="text-primary">Aura</span><span class="text-light">lis</span>
            </a>

            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                aria-label="Toggle navigation">
                <i class="bi bi-list fs-1 text-light"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">

                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 fw-medium gap-2 mt-3 mt-lg-0 text-center">
                    <li class="nav-item">
                        <a class="nav-link custom-link px-3" href="/Auralis/geral/index.php">Início</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link custom-link px-3" href="/Auralis/geral/sobre.php#título">Sobre nós</a>
                    </li>
                </ul>

                <div class="d-flex flex-column flex-lg-row gap-3 align-items-center mt-3 mt-lg-0">

                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <?php
                        $primeiroNome = explode(' ', $_SESSION['usuario_nome'])[0];
                        ?>
                        <div class="dropdown w-100 text-center text-lg-start">
                            <a href="#"
                                class="d-flex align-items-center justify-content-center justify-content-lg-start text-light text-decoration-none dropdown-toggle custom-link py-2"
                                id="menuUsuario" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="me-2 d-none d-md-inline text-muted">Olá,
                                    <strong class="text-light"><?= htmlspecialchars($primeiroNome); ?></strong>
                                </span>
                                <i class="bi bi-person-circle fs-4 text-primary cardCentral"></i>
                            </a>

                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border border-secondary-subtle mt-2"
                                aria-labelledby="menuUsuario">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center py-2" href="/Auralis/dashboard.php">
                                        <i class="bi bi-speedometer2 me-2 text-primary"></i> Meu Painel
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center py-2" href="#">
                                        <i class="bi bi-wallet2 me-2 text-primary"></i> Minhas Carteiras
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center py-2" href="#">
                                        <i class="bi bi-gear me-2 text-primary"></i> Configurações
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider border-secondary-subtle">
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center py-2 text-danger custom-link"
                                        href="/Auralis/usuario/logout.php">
                                        <i class="bi bi-box-arrow-right me-2"></i> Sair
                                    </a>
                                </li>
                            </ul>
                        </div>

                    <?php else: ?>
                        <a href="/Auralis/usuario/login.php"
                            class="btn btn-link text-light text-decoration-none custom-link px-3">Login</a>
                        <a href="/Auralis/usuario/cadastro.php"
                            class="btn btn-primary px-4 rounded-pill fw-bold shadow-sm cardCentral">Criar Conta</a>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </nav>
