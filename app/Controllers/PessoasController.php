<?php
// Controller da entidade de pessoas atendidas.
// As queries ficam no Model; aqui tratamos a requisicao, validamos
// os dados recebidos e montamos a resposta (JSON ou tela HTML).
require_once __DIR__ . '/../Models/Pessoa.php';

class PessoasController
{
    private Pessoa $pessoaModel;

    private const STATUS_VALIDOS = ['ativo', 'inativo'];

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pessoaModel = new Pessoa($pdo);
    }

    // Renderiza a tela de gerenciamento de pessoas (HTML + JS que consome o JSON abaixo).
    public function tela(): void
    {
        require __DIR__ . '/../Views/pessoas/index.php';
    }

    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($this->pessoaModel->todas(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function buscarPorId(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        $pessoa = $this->pessoaModel->porId($id);

        if (!$pessoa) {
            http_response_code(404);
            echo json_encode(['erro' => 'Pessoa não encontrada.']);
            return;
        }

        echo json_encode($pessoa, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $dados = $this->dadosDoFormulario();
        $erro = $this->validar($dados);

        if ($erro) {
            http_response_code(422);
            echo json_encode(['erro' => $erro]);
            return;
        }

        if ($this->pessoaModel->documentoEmUsoPorOutraPessoa($dados['documento'])) {
            http_response_code(409);
            echo json_encode(['erro' => 'Já existe uma pessoa cadastrada com este documento.']);
            return;
        }

        try {
            $id = $this->pessoaModel->inserir($dados);

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Pessoa cadastrada com sucesso.',
                'id' => $id
            ], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao cadastrar pessoa.']);
        }
    }

    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        $dados = $this->dadosDoFormulario();
        $erro = $this->validar($dados);

        if ($erro) {
            http_response_code(422);
            echo json_encode(['erro' => $erro]);
            return;
        }

        if ($this->pessoaModel->documentoEmUsoPorOutraPessoa($dados['documento'], $id)) {
            http_response_code(409);
            echo json_encode(['erro' => 'Já existe outra pessoa cadastrada com este documento.']);
            return;
        }

        try {
            $this->pessoaModel->atualizar($id, $dados);

            echo json_encode(['mensagem' => 'Pessoa atualizada com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar pessoa.']);
        }
    }

    public function inativar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        $this->pessoaModel->inativar($id);

        echo json_encode(['mensagem' => 'Pessoa inativada com sucesso.'], JSON_UNESCAPED_UNICODE);
    }

    private function dadosDoFormulario(): array
    {
        return [
            'nome' => trim($_POST['nome'] ?? ''),
            'documento' => trim($_POST['documento'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'telefone' => trim($_POST['telefone'] ?? '') !== '' ? trim($_POST['telefone']) : null,
            'curso' => trim($_POST['curso'] ?? '') !== '' ? trim($_POST['curso']) : null,
            'periodo' => trim($_POST['periodo'] ?? '') !== '' ? trim($_POST['periodo']) : null,
            'status' => $_POST['status'] ?? 'ativo',
        ];
    }

    private function validar(array $dados): ?string
    {
        if ($dados['nome'] === '' || $dados['documento'] === '' || $dados['email'] === '') {
            return 'Nome, documento e e-mail são obrigatórios.';
        }

        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            return 'E-mail inválido.';
        }

        if (!in_array($dados['status'], self::STATUS_VALIDOS, true)) {
            return 'Status inválido.';
        }

        return null;
    }
}
