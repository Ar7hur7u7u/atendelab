<?php
// Controller da entidade de tipos de atendimento.
require_once __DIR__ . '/../Models/TipoAtendimento.php';

class TiposAtendimentosController
{
    private TipoAtendimento $tipoModel;

    private const STATUS_VALIDOS = ['ativo', 'inativo'];

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->tipoModel = new TipoAtendimento($pdo);
    }

    // Renderiza a tela de gerenciamento de tipos de atendimento.
    public function tela(): void
    {
        require __DIR__ . '/../Views/tipos-atendimentos/index.php';
    }

    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($this->tipoModel->todos(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // Usado pela tela de atendimentos para preencher o select com os tipos ativos.
    public function listarAtivos(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($this->tipoModel->ativos(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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

        $tipo = $this->tipoModel->porId($id);

        if (!$tipo) {
            http_response_code(404);
            echo json_encode(['erro' => 'Tipo de atendimento não encontrado.']);
            return;
        }

        echo json_encode($tipo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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

        try {
            $id = $this->tipoModel->inserir($dados);

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Tipo de atendimento cadastrado com sucesso.',
                'id' => $id
            ], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao cadastrar tipo de atendimento.']);
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

        try {
            $this->tipoModel->atualizar($id, $dados);

            echo json_encode(['mensagem' => 'Tipo de atendimento atualizado com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar tipo de atendimento.']);
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

        $this->tipoModel->inativar($id);

        echo json_encode(['mensagem' => 'Tipo de atendimento inativado com sucesso.'], JSON_UNESCAPED_UNICODE);
    }

    private function dadosDoFormulario(): array
    {
        return [
            'nome' => trim($_POST['nome'] ?? ''),
            'descricao' => trim($_POST['descricao'] ?? '') !== '' ? trim($_POST['descricao']) : null,
            'status' => $_POST['status'] ?? 'ativo',
        ];
    }

    private function validar(array $dados): ?string
    {
        if ($dados['nome'] === '') {
            return 'O nome é obrigatório.';
        }

        if (!in_array($dados['status'], self::STATUS_VALIDOS, true)) {
            return 'Status inválido.';
        }

        return null;
    }
}
