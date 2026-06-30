<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Dashboard - AtendeLab</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/Css/style.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php require __DIR__ . '/../partials/navbar.php'; ?>

<div class="container mt-4 mb-5">
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h1 class="h4 mb-1">Ola, <?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="text-muted mb-0">
                Perfil: <?= htmlspecialchars($usuario['perfil'], ENT_QUOTES, 'UTF-8') ?>
                — aqui esta um resumo do que esta acontecendo no laboratorio.
            </p>
        </div>
    </div>

    <div id="alertaDashboard"></div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm card-indicador">
                <div class="card-body">
                    <div class="rotulo">Pessoas ativas</div>
                    <div class="valor" id="indicadorPessoas">-</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm card-indicador">
                <div class="card-body">
                    <div class="rotulo">Tipos ativos</div>
                    <div class="valor" id="indicadorTipos">-</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm card-indicador">
                <div class="card-body">
                    <div class="rotulo">Em aberto</div>
                    <div class="valor" id="indicadorAbertos">-</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm card-indicador">
                <div class="card-body">
                    <div class="rotulo">Concluidos</div>
                    <div class="valor" id="indicadorConcluidos">-</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <strong>Ultimos atendimentos</strong>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Pessoa</th>
                                <th>Tipo</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaUltimosAtendimentos">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Carregando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <strong>Acesso rapido</strong>
                </div>
                <div class="card-body d-grid gap-2">
                    <a class="btn btn-outline-primary text-start" href="?controller=atendimentos&action=tela">
                        Registrar novo atendimento
                    </a>
                    <a class="btn btn-outline-primary text-start" href="?controller=pessoas&action=tela">
                        Cadastrar pessoa
                    </a>
                    <a class="btn btn-outline-primary text-start" href="?controller=tipos&action=tela">
                        Gerenciar tipos de atendimento
                    </a>
                    <a class="btn btn-outline-secondary text-start" href="?controller=usuarios&action=listar" target="_blank">
                        Ver usuarios (JSON)
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/Js/script.js"></script>
<script>
    async function carregarIndicadores() {
        try {
            const dados = await apiFetch('?controller=dashboard&action=indicadores');

            document.getElementById('indicadorPessoas').textContent = dados.pessoas_ativas;
            document.getElementById('indicadorTipos').textContent = dados.tipos_ativos;
            document.getElementById('indicadorAbertos').textContent = dados.atendimentos_em_aberto;
            document.getElementById('indicadorConcluidos').textContent = dados.atendimentos_concluidos;

            const tabela = document.getElementById('tabelaUltimosAtendimentos');

            if (dados.ultimos_atendimentos.length === 0) {
                tabela.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Nenhum atendimento registrado ainda.</td></tr>';
                return;
            }

            tabela.innerHTML = dados.ultimos_atendimentos.map((atendimento) => `
                <tr>
                    <td>${formatarDataBr(atendimento.data)}</td>
                    <td>${atendimento.pessoa_nome}</td>
                    <td>${atendimento.tipo_nome}</td>
                    <td>${badgeStatus(atendimento.status)}</td>
                </tr>
            `).join('');
        } catch (erro) {
            exibirAlerta('alertaDashboard', erro.message, 'danger');
        }
    }

    carregarIndicadores();
</script>

</body>
</html>
