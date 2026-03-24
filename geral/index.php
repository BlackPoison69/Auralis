<?php require_once 'header.php'; ?>

<main class="container py-5 mt-4">
    <div class="row align-items-center py-5">

        <div class="col-lg-6">
            <h1 class="display-4 fw-bold mb-4">A inteligência que o seu dinheiro precisa.</h1>
            <p class="lead text-light mb-4 opacity-75">
                O Auralis organiza as suas contas, separa as despesas da casa e gerencia o fluxo de caixa do seu negócio
                em um só lugar. Simples, seguro e intuitivo.
            </p>
            <div class="d-flex gap-3 mt-4">
                <a href="cadastro.php" class="btn btn-primary btn-lg px-4 shadow">Começar Agora</a>
                <a href="sobre.php" class="btn btn-outline-light btn-lg px-4 ">Saber Mais</a>
            </div>
        </div>

        <div class="col-lg-6 text-center mt-5 mt-lg-0">
            <div
                class="p-5 bg-body-tertiary border border-secondary-subtle rounded-4 shadow-lg position-relative overflow-hidden">
                <div
                    style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background: var(--accent); filter: blur(100px); opacity: 0.2; border-radius: 50%;">
                </div>

                <div class="display-1 mb-4">
                    <img style="width: 120px; height: auto; border-radius: 20px; box-shadow: 0 10px 20px rgba(0,0,0,0.3);"
                        src="img/bolsa-de-dinheiro.gif " alt="Animação Bolsa de Dinheiro">
                </div>
                <h3 class="fw-bold text-primary">Visão Clara e Organizada</h3>
                <p class="text-light opacity-75 mb-0">Assuma o controle total de para onde vai cada centavo.</p>
            </div>
        </div>
    </div>
</main>

<section id="como-funciona" class="container py-5 mt-5 border-top border-secondary-subtle">
    <div class="text-center mb-5">
        <h2 class="fw-bold display-6">Tudo o que você precisa para crescer</h2>
        <p class="text-light opacity-75">Uma estrutura de nível corporativo adaptada para o seu dia a dia.</p>
    </div>

    <div class="row g-4">

        <div class="col-md-4 card-animado surgir-baixo">
            <div class="card h-100 bg-transparent border-0">
                <div class="feature-card h-100 text-center">
                    <i class="bi bi-briefcase-fill fs-1 mb-3 text-primary d-inline-block"></i>
                    <h4 class="fw-semibold fs-5 text-light">Múltiplos Caixa</h4>
                    <p class="text-light mb-0 opacity-75 fs-6">Nunca mais misture o dinheiro pessoal com o da empresa.
                        Tenha uma
                        carteira para casa e outra exclusiva para o fluxo de caixa do Ponto Certo, cada uma com seu
                        próprio
                        saldo.</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 card-animado surgir-baixo" style="transition-delay: 0.2s;">
            <div class="card h-100 bg-transparent border-0">
                <div class="feature-card h-100 text-center">
                    <i class="bi bi-people-fill fs-1 mb-3 text-primary d-inline-block"></i>
                    <h4 class="fw-semibold fs-5 text-light">Controle Compartilhado</h4>
                    <p class="text-light mb-0 opacity-75 fs-6">Adicione convidados à sua carteira. Acompanhe em tempo
                        real quem
                        registrou cada entrada ou saída, definindo o nível de acesso de cada participante.</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 card-animado surgir-baixo" style="transition-delay: 0.4s;">
            <div class="card h-100 bg-transparent border-0">
                <div class="feature-card h-100 text-center">
                    <i class="bi bi-tags-fill fs-1 mb-3 text-primary d-inline-block"></i>
                    <h4 class="fw-semibold fs-5 text-light">Categorias Precisas</h4>
                    <p class="text-light mb-0 opacity-75 fs-6">Chega de gastos "Não Identificados". Crie categorias e
                        subcategorias personalizadas para rastrear exatamente de onde o dinheiro vem e para onde ele
                        vai.</p>
                </div>
            </div>
        </div>

    </div>
</section>

<script>
    // Rolagem suave dos links âncora
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
</script>

<?php require_once 'footer.php'; ?>