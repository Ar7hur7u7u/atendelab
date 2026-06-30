<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Pessoas - AtendeLab</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/Css/style.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php require __DIR__ . '/../partials/navbar.php'; ?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-0">Pessoas atendidas</h1>
            <p class="text-muted small mb-0">Alunos, professores ou visitantes que ja foram atendidos pelo laboratorio.</p>
        </div>

        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPessoa" onclick="novaPessoa()">
            + Nova pessoa
        </button>
    </div>

    <div id="alertaPessoas"></div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Documento</th>
                        <th>Curso</th>
                        <th>Periodo</th>
                        <th>Status</th>
                        <th class="text-end">Acoes</th>
                    </tr>
                </thead>
                <tbody id="tabelaPessoas">
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Carregando...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de cadastro/edicao de pessoa -->
<div class="modal fade" id="modalPessoa" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formPessoa">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModalPessoa">Nova pessoa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="pessoaId">

                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" id="pessoaNome" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Documento (CPF)</label>
                            <input type="text" name="documento" id="pessoaDocumento" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">E-mail</label>
                            <input type="email" name="email" id="pessoaEmail" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone" id="pessoaTelefone" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Curso</label>
                            <input type="text" name="curso" id="pessoaCurso" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Periodo</label>
                            <input type="text" name="periodo" id="pessoaPeriodo" class="form-control" placeholder="Ex: 5o">
                        </div>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Status</label>
                        <select name="status" id="pessoaStatus" class="form-select">
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
    const modalPessoa = new bootstrap.Modal(document.getElementById('modalPessoa'));

    async function carregarPessoas() {
        const tabela = document.getElementById('tabelaPessoas');

        try {
            const pessoas = await apiFetch('?controller=pessoas&action=listar');

            if (pessoas.length === 0) {
                tabela.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Nenhuma pessoa cadastrada ainda.</td></tr>';
                return;
            }

            tabela.innerHTML = pessoas.map((pessoa) => `
                <tr>
                    <td>${pessoa.nome}</td>
                    <td>${pessoa.documento}</td>
                    <td>${pessoa.curso ?? '-'}</td>
                    <td>${pessoa.periodo ?? '-'}</td>
                    <td>${badgeStatus(pessoa.status)}</td>
                    <td class="text-end tabela-acoes">
                        <button class="btn btn-sm btn-outline-primary" onclick='editarPessoa(${JSON.stringify(pessoa)})'>Editar</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="inativarPessoa(${pessoa.id})">Inativar</button>
                    </td>
                </tr>
            `).join('');
        } catch (erro) {
            exibirAlerta('alertaPessoas', erro.message, 'danger');
        }
    }

    function novaPessoa() {
        document.getElementById('formPessoa').reset();
        document.getElementById('pessoaId').value = '';
        document.getElementById('tituloModalPessoa').textContent = 'Nova pessoa';
    }

    function editarPessoa(pessoa) {
        document.getElementById('pessoaId').value = pessoa.id;
        document.getElementById('pessoaNome').value = pessoa.nome;
        document.getElementById('pessoaDocumento').value = pessoa.documento;
        document.getElementById('pessoaEmail').value = pessoa.email;
        document.getElementById('pessoaTelefone').value = pessoa.telefone ?? '';
        document.getElementById('pessoaCurso').value = pessoa.curso ?? '';
        document.getElementById('pessoaPeriodo').value = pessoa.periodo ?? '';
        document.getElementById('pessoaStatus').value = pessoa.status;
        document.getElementById('tituloModalPessoa').textContent = 'Editar pessoa';

        modalPessoa.show();
    }

    async function inativarPessoa(id) {
        if (!confirm('Deseja realmente inativar esta pessoa?')) {
            return;
        }

        try {
            const corpo = new URLSearchParams({ id });
            const resultado = await apiFetch('?controller=pessoas&action=inativar', { method: 'POST', body: corpo });

            exibirAlerta('alertaPessoas', resultado.mensagem, 'success');
            carregarPessoas();
        } catch (erro) {
            exibirAlerta('alertaPessoas', erro.message, 'danger');
        }
    }

    document.getElementById('formPessoa').addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const id = document.getElementById('pessoaId').value;
        const acao = id ? 'atualizar' : 'criar';
        const corpo = new URLSearchParams(new FormData(evento.target));

        try {
            const resultado = await apiFetch(`?controller=pessoas&action=${acao}`, { method: 'POST', body: corpo });

            exibirAlerta('alertaPessoas', resultado.mensagem, 'success');
            modalPessoa.hide();
            carregarPessoas();
        } catch (erro) {
            exibirAlerta('alertaPessoas', erro.message, 'danger');
        }
    });

    carregarPessoas();
</script>

</body>
</html>
