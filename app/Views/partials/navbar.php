<?php
// Barra de navegacao reaproveitada pelas telas internas.
// Espera que $usuario (sessao atual) esteja disponivel quando incluida.
$telaAtual = $_GET['controller'] ?? '';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="?controller=auth&action=dashboard">AtendeLab</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $telaAtual === 'auth' ? 'active' : '' ?>" href="?controller=auth&action=dashboard">
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $telaAtual === 'pessoas' ? 'active' : '' ?>" href="?controller=pessoas&action=tela">
                        Pessoas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $telaAtual === 'tipos' ? 'active' : '' ?>" href="?controller=tipos&action=tela">
                        Tipos de atendimento
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $telaAtual === 'atendimentos' ? 'active' : '' ?>" href="?controller=atendimentos&action=tela">
                        Atendimentos
                    </a>
                </li>
            </ul>

            <a class="btn btn-outline-light btn-sm" href="?controller=auth&action=logout">
                Sair
            </a>
        </div>
    </div>
</nav>
