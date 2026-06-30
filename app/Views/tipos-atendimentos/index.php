<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tipos de atendimento - AtendeLab</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/Css/style.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php require __DIR__ . '/../partials/navbar.php'; ?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-0">Tipos de atendimento</h1>
            <p class="text-muted small mb-0">Categorias usadas para classificar cada atendimento registrado.</p>
        </div>

        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTipo" onclick="novoTipo()">
            + Novo tipo
        </button>
    </div>

    <div id="alertaTipos"></div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Descricao</th>
                        <th>Status</th>
                        <th class="text-end">Acoes</th>
                    </tr>
                </thead>
                <tbody id="tabelaTipos">
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Carregando...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de cadastro/edicao de tipo -->
<div class="modal fade" id="modalTipo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formTipo">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModalTipo">Novo tipo de atendimento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="tipoId">

                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" id="tipoNome" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descricao</label>
                        <textarea name="descricao" id="tipoDescricao" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Status</label>
                        <select name="status" id="tipoStatus" class="form-select">
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/Js/script.js"></script>
<script>
    const modalTipo = new bootstrap.Modal(document.getElementById('modalTipo'));

    async function carregarTipos() {
        const tabela = document.getElementById('tabelaTipos');

        try {
            const tipos = await apiFetch('?controller=tipos&action=listar');

            if (tipos.length === 0) {
                tabela.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Nenhum tipo cadastrado ainda.</td></tr>';
                return;
            }

            tabela.innerHTML = tipos.map((tipo) => `
                <tr>
                    <td>${tipo.nome}</td>
                    <td>${tipo.descricao ?? '-'}</td>
                    <td>${badgeStatus(tipo.status)}</td>
                    <td class="text-end tabela-acoes">
                        <button class="btn btn-sm btn-outline-primary" onclick='editarTipo(${JSON.stringify(tipo)})'>Editar</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="inativarTipo(${tipo.id})">Inativar</button>
                    </td>
                </tr>
            `).join('');
        } catch (erro) {
            exibirAlerta('alertaTipos', erro.message, 'danger');
        }
    }

    function novoTipo() {
        document.getElementById('formTipo').reset();
        document.getElementById('tipoId').value = '';
        document.getElementById('tituloModalTipo').textContent = 'Novo tipo de atendimento';
    }

    function editarTipo(tipo) {
        document.getElementById('tipoId').value = tipo.id;
        document.getElementById('tipoNome').value = tipo.nome;
        document.getElementById('tipoDescricao').value = tipo.descricao ?? '';
        document.getElementById('tipoStatus').value = tipo.status;
        document.getElementById('tituloModalTipo').textContent = 'Editar tipo de atendimento';

        modalTipo.show();
    }

    async function inativarTipo(id) {
        if (!confirm('Deseja realmente inativar este tipo de atendimento?')) {
            return;
        }

        try {
            const corpo = new URLSearchParams({ id });
            const resultado = await apiFetch('?controller=tipos&action=inativar', { method: 'POST', body: corpo });

            exibirAlerta('alertaTipos', resultado.mensagem, 'success');
            carregarTipos();
        } catch (erro) {
            exibirAlerta('alertaTipos', erro.message, 'danger');
        }
    }

    document.getElementById('formTipo').addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const id = document.getElementById('tipoId').value;
        const acao = id ? 'atualizar' : 'criar';
        const corpo = new URLSearchParams(new FormData(evento.target));

        try {
            const resultado = await apiFetch(`?controller=tipos&action=${acao}`, { method: 'POST', body: corpo });

            exibirAlerta('alertaTipos', resultado.mensagem, 'success');
            modalTipo.hide();
            carregarTipos();
        } catch (erro) {
            exibirAlerta('alertaTipos', erro.message, 'danger');
        }
    });

    carregarTipos();
</script>

</body>
</html>
