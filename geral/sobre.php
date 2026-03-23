<?php require_once 'header.php'; ?>

<header class="py-5 text-center mt-4 border-bottom border-secondary-subtle">
    <div class="container py-5">
        <h1 class="display-4 fw-bold mb-3 text-primary">Por dentro do Auralis</h1>
        <p class="lead text-light opacity-75 mx-auto" style="max-width: 700px;">
            Não somos apenas uma planilha online. O Auralis foi construído com uma arquitetura de dados robusta para
            oferecer controle absoluto, privacidade e colaboração em tempo real.
        </p>
    </div>
</header>

<main class="container py-5">

    <div class="row align-items-center py-5">
        <div class="col-lg-6 order-lg-1 order-2 mt-4 mt-lg-0">
            <h3 class="fw-bold mb-3">Isolamento Completo de Carteiras</h3>
            <p class="text-light opacity-75 mb-4">
                A maioria dos aplicativos mistura todo o seu dinheiro. No Auralis, cada carteira é um ecossistema
                financeiro independente. O saldo, as categorias e os registros da sua empresa nunca se cruzarão com as
                finanças da sua casa, garantindo relatórios precisos para cada área da sua vida.
            </p>
            <ul class="list-unstyled text-light opacity-75">
                <li class="mb-2">✅ Saldos individuais por carteira</li>
                <li class="mb-2">✅ Categorias exclusivas para cada negócio/projeto</li>
                <li class="mb-2">✅ Transição rápida entre carteiras com um clique</li>
            </ul>
        </div>
        <div class="col-lg-6 order-lg-2 order-1 text-center">
            <div class="p-5 bg-body-tertiary border border-secondary-subtle rounded-4 shadow-sm cardCentral">
                <div class="display-1">💼</div>
            </div>
        </div>
    </div>

    <div class="row align-items-center py-5">
        <div class="col-lg-6 text-center mb-4 mb-lg-0">
            <div class="p-5 bg-body-tertiary border border-secondary-subtle rounded-4 shadow-sm cardCentral">
                <div class="display-1">🤝</div>
            </div>
        </div>
        <div class="col-lg-6">
            <h3 class="fw-bold mb-3">Colaboração com Rastreabilidade</h3>
            <p class="text-light opacity-75 mb-4">
                Gerenciar o dinheiro em família ou com sócios não precisa ser um caos. Convide membros para a sua
                carteira e saiba exatamente quem adicionou cada despesa. O sistema registra o autor e o momento exato de
                cada transação.
            </p>
            <ul class="list-unstyled text-light opacity-75">
                <li class="mb-2">✅ Convites seguros via sistema</li>
                <li class="mb-2">✅ Identificação de quem lançou cada registro</li>
                <li class="mb-2">✅ Histórico transparente para todos os membros</li>
            </ul>
        </div>
    </div>

    <div class="row align-items-center py-5">
        <div class="col-lg-6 order-lg-1 order-2 mt-4 mt-lg-0">
            <h3 class="fw-bold mb-3">Segurança de Nível Bancário</h3>
            <p class="text-light opacity-75 mb-4">
                Seus dados financeiros são sensíveis. Por isso, o Auralis utiliza tecnologia de ponta, empregando chaves
                UUID universais e banco de dados relacional PostgreSQL blindado contra acessos não autorizados.
            </p>
            <ul class="list-unstyled text-light opacity-75">
                <li class="mb-2">🔒 Identificadores criptografados (UUID)</li>
                <li class="mb-2">🔒 Proteção contra injeção de código (SQL Injection)</li>
                <li class="mb-2">🔒 Integridade de dados garantida na nuvem</li>
            </ul>
        </div>
        <div class="col-lg-6 order-lg-2 order-1 text-center">
            <div class="p-5 bg-body-tertiary border border-secondary-subtle rounded-4 shadow-sm cardCentral">
                <div class="display-1">🛡️</div>
            </div>
        </div>
    </div>

</main>

<section class="py-5 bg-body-tertiary border-top border-secondary-subtle text-center">
    <div class="container py-4">
        <h2 class="fw-bold mb-3">Pronto para assumir o controle?</h2>
        <p class="text-light opacity-75 mb-4">Junte-se ao Auralis e transforme a maneira como você lida com o seu
            dinheiro.</p>
        <a href="../usuario/cadastro.php" class="btn btn-primary btn-lg px-5 fw-semibold text-dark cardCentral">Criar
            Minha Conta Grátis</a>
    </div>
</section>

<?php require_once 'footer.php'; ?>