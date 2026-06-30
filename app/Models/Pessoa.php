<?php
// Model da entidade de pessoas atendidas.
// Concentra as queries em um unico lugar para que o Controller fique
// responsavel apenas por validar entrada e montar a resposta.
class Pessoa
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function todas(): array
    {
        $sql = 'SELECT id, nome, documento, email, telefone, curso, periodo, status, criado_em
                FROM pessoas
                ORDER BY nome ASC';

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function porId(int $id): ?array
    {
        $sql = 'SELECT id, nome, documento, email, telefone, curso, periodo, status, criado_em
                FROM pessoas
                WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

        return $pessoa ?: null;
    }

    public function inserir(array $dados): int
    {
        $sql = 'INSERT INTO pessoas (nome, documento, email, telefone, curso, periodo, status)
                VALUES (:nome, :documento, :email, :telefone, :curso, :periodo, :status)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($dados);

        return (int) $this->pdo->lastInsertId();
    }

    public function atualizar(int $id, array $dados): void
    {
        $sql = 'UPDATE pessoas
                SET nome = :nome,
                    documento = :documento,
                    email = :email,
                    telefone = :telefone,
                    curso = :curso,
                    periodo = :periodo,
                    status = :status
                WHERE id = :id';

        $dados['id'] = $id;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($dados);
    }

    // Pessoas nao sao excluidas fisicamente porque podem estar referenciadas
    // em atendimentos antigos. Por isso o "excluir" do front vira uma inativacao.
    public function inativar(int $id): void
    {
        $stmt = $this->pdo->prepare("UPDATE pessoas SET status = 'inativo' WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function documentoEmUsoPorOutraPessoa(string $documento, ?int $idAtual = null): bool
    {
        $sql = 'SELECT id FROM pessoas WHERE documento = :documento';

        if ($idAtual !== null) {
            $sql .= ' AND id != :id';
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':documento', $documento);

        if ($idAtual !== null) {
            $stmt->bindValue(':id', $idAtual, PDO::PARAM_INT);
        }

        $stmt->execute();

        return (bool) $stmt->fetch();
    }
}
