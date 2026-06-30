<?php
// Model da entidade de tipos de atendimento.
class TipoAtendimento
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function todos(): array
    {
        $sql = 'SELECT id, nome, descricao, status, criado_em
                FROM tipos_atendimentos
                ORDER BY nome ASC';

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ativos(): array
    {
        $sql = "SELECT id, nome, descricao
                FROM tipos_atendimentos
                WHERE status = 'ativo'
                ORDER BY nome ASC";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function porId(int $id): ?array
    {
        $sql = 'SELECT id, nome, descricao, status, criado_em
                FROM tipos_atendimentos
                WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $tipo = $stmt->fetch(PDO::FETCH_ASSOC);

        return $tipo ?: null;
    }

    public function inserir(array $dados): int
    {
        $sql = 'INSERT INTO tipos_atendimentos (nome, descricao, status)
                VALUES (:nome, :descricao, :status)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($dados);

        return (int) $this->pdo->lastInsertId();
    }

    public function atualizar(int $id, array $dados): void
    {
        $sql = 'UPDATE tipos_atendimentos
                SET nome = :nome,
                    descricao = :descricao,
                    status = :status
                WHERE id = :id';

        $dados['id'] = $id;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($dados);
    }

    // Assim como pessoas, um tipo de atendimento ja usado em registros antigos
    // nao pode sumir do historico, entao a remocao e sempre uma inativacao.
    public function inativar(int $id): void
    {
        $stmt = $this->pdo->prepare("UPDATE tipos_atendimentos SET status = 'inativo' WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
