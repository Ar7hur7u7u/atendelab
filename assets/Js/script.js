// Funcoes utilitarias compartilhadas pelas telas internas do sistema.
// Mantidas simples (sem build/bundler) porque o projeto e PHP "puro".

async function apiFetch(url, opcoes = {}) {
    const resposta = await fetch(url, opcoes);
    const dados = await resposta.json().catch(() => ({}));

    if (!resposta.ok) {
        throw new Error(dados.erro || 'Ocorreu um erro inesperado.');
    }

    return dados;
}

function exibirAlerta(containerId, mensagem, tipo = 'success') {
    const container = document.getElementById(containerId);

    if (!container) {
        return;
    }

    container.innerHTML = `
        <div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
            ${mensagem}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    `;
}

function badgeStatus(status) {
    const classesPorStatus = {
        ativo: 'bg-success',
        inativo: 'bg-secondary',
        aberto: 'bg-warning text-dark',
        em_andamento: 'bg-info text-dark',
        concluido: 'bg-success',
        cancelado: 'bg-danger',
    };

    const classe = classesPorStatus[status] || 'bg-secondary';
    const texto = (status || '').replace('_', ' ');

    return `<span class="badge ${classe}">${texto}</span>`;
}

function formatarDataBr(dataIso) {
    if (!dataIso) {
        return '-';
    }

    const [ano, mes, dia] = dataIso.split('-');
    return `${dia}/${mes}/${ano}`;
}
