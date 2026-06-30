<?php
// O usuario logado e quem fica registrado como responsavel pelo atendimento criado.
$usuarioLogado = usuarioAtual();
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Atendimentos - AtendeLab</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/Css/style.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php require __DIR__ . '/../partials/navbar.php'; ?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-0">Atendimentos</h1>
            <p class="text-muted small mb-0">Registros de atendimento realizados pela equipe do laboratorio.</p>
        </div>

        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAtendimento" onclick="novoAtendimento()">
            + Novo atendimento
        </button>
    </div>

    <div id="alertaAtendimentos"></div>

    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-secondary filtro-status active" data-status="">Todos</button>
                <button type="button" class="btn btn-outline-secondary filtro-status" data-status="aberto">Abertos</button>
                <button type="button" class="btn btn-outline-secondary filtro-status" data-status="em_andamento">Em andamento</button>
                <button type="button" class="btn btn-outline-secondary filtro-status" data-status="concluido">Concluidos</button>
                <button type="button" class="btn btn-outline-secondary filtro-status" data-status="cancelado">Cancelados</button>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Pessoa</th>
                        <th>Tipo</th>
                        <th>Atendente</th>
                        <th>Status</th>
                        <th class="text-end">Acoes</th>
                    </tr>
                </thead>
                <tbody id="tabelaAtendimentos">
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Carregando...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de novo atendimento -->
<div class="modal fade" id="modalAtendimento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formAtendimento">
                <div class="modal-header">
                    <h5 class="modal-title">Novo atendimento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="usuario_id" value="<?= (int) $usuarioLogado['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label">Pessoa</label>
                        <select name="pessoa_id" id="atendimentoPessoa" class="form-select" required>
                            <option value="">Selecione...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipo de atendimento</label>
                        <select name="tipo_atendimento_id" id="atendimentoTipo" class="form-select" required>
                            <option value="">Selecione...</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data</label>
                            <input type="date" name="data" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hora</label>
                            <input type="time" name="hora" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Descricao</label>
                        <textarea name="descricao" class="form-control" rows="3" placeholder="Resumo do que sera tratado no atendimento"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de alteracao de status -->
<div class="modal fade" id="modalStatus" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formStatus">
                <div class="modal-header">
                    <h5 class="modal-title">Atualizar status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="statusAtendimentoId">

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="statusNovo" class="form-select">
                            <option value="aberto">Aberto</option>
                            <option value="em_andamento">Em andamento</option>
                            <option value="concluido">Concluido</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Observacao final</label>
                        <textarea name="observacao_final" id="statusObservacao" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Atualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/Js/script.js"></script>
<script>
    const modalAtendimento = new bootstrap.Modal(document.getElementById('modalAtendimento'));
    const modalStatus = new bootstrap.Modal(document.getElementById('modalStatus'));

    let listaAtendimentos = [];
    let filtroStatusAtual = '';

    async function carregarSelects() {
        try {
            const [pessoas, tipos] = await Promise.all([
                apiFetch('?controller=pessoas&action=listar'),
                apiFetch('?controller=tipos&action=listarAtivos'),
            ]);

            const selectPessoa = document.getElementById('atendimentoPessoa');
            pessoas
                .filter((pessoa) => pessoa.status === 'ativo')
                .forEach((pessoa) => {
                    selectPessoa.insertAdjacentHTML('beforeend', `<option value="${pessoa.id}">${pessoa.nome}</option>`);
                });

            const selectTipo = document.getElementById('atendimentoTipo');
            tipos.forEach((tipo) => {
                selectTipo.insertAdjacentHTML('beforeend', `<option value="${tipo.id}">${tipo.nome}</option>`);
            });
        } catch (erro) {
            exibirAlerta('alertaAtendimentos', erro.message, 'danger');
        }
    }

    function renderizarTabela() {
        const tabela = document.getElementById('tabelaAtendimentos');

        const atendimentosFiltrados = filtroStatusAtual
            ? listaAtendimentos.filter((atendimento) => atendimento.status === filtroStatusAtual)
            : listaAtendimentos;

        if (atendimentosFiltrados.length === 0) {
            tabela.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Nenhum atendimento encontrado.</td></tr>';
            return;
        }

        tabela.innerHTML = atendimentosFiltrados.map((atendimento) => `
            <tr>
                <td>${formatarDataBr(atendimento.data)} ${atendimento.hora ?? ''}</td>
                <td>${atendimento.pessoa_nome}</td>
                <td>${atendimento.tipo_atendimento_nome}</td>
                <td>${atendimento.usuario_nome}</td>
                <td>${badgeStatus(atendimento.status)}</td>
                <td class="text-end tabela-acoes">
                    <button class="btn btn-sm btn-outline-primary" onclick="abrirModalStatus(${atendimento.id}, '${atendimento.status}')">
                        Status
                    </button>
                </td>
            </tr>
        `).join('');
    }

    async function carregarAtendimentos() {
        try {
            listaAtendimentos = await apiFetch('?controller=atendimentos&action=listar');
            renderizarTabela();
        } catch (erro) {
            exibirAlerta('alertaAtendimentos', erro.message, 'danger');
        }
    }

    document.querySelectorAll('.filtro-status').forEach((botao) => {
        botao.addEventListener('click', () => {
            document.querySelectorAll('.filtro-status').forEach((b) => b.classList.remove('active'));
            botao.classList.add('active');
            filtroStatusAtual = botao.dataset.status;
            renderizarTabela();
        });
    });

    function novoAtendimento() {
        document.getElementById('formAtendimento').reset();
    }

    function abrirModalStatus(id, statusAtual) {
        document.getElementById('statusAtendimentoId').value = id;
        document.getElementById('statusNovo').value = statusAtual;
        document.getElementById('statusObservacao').value = '';
        modalStatus.show();
    }

    document.getElementById('formAtendimento').addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const corpo = new URLSearchParams(new FormData(evento.target));

        try {
            const resultado = await apiFetch('?controller=atendimentos&action=criar', { method: 'POST', body: corpo });

            exibirAlerta('alertaAtendimentos', resultado.mensagem, 'success');
            modalAtendimento.hide();
            carregarAtendimentos();
        } catch (erro) {
            exibirAlerta('alertaAtendimentos', erro.message, 'danger');
        }
    });

    document.getElementById('formStatus').addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const corpo = new URLSearchParams(new FormData(evento.target));

        try {
            const resultado = await apiFetch('?controller=atendimentos&action=atualizarStatus', { method: 'POST', body: corpo });

            exibirAlerta('alertaAtendimentos', resultado.mensagem, 'success');
            modalStatus.hide();
            carregarAtendimentos();
        } catch (erro) {
            exibirAlerta('alertaAtendimentos', erro.message, 'danger');
        }
    });

    carregarSelects();
    carregarAtendimentos();
</script>

</body>
</html>
