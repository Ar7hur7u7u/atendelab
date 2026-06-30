<?php
// Controller responsavel pelos indicadores exibidos no dashboard.
class DashboardController
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    public function indicadores(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $pessoasAtivas = (int) $this->pdo
            ->query("SELECT COUNT(*) FROM pessoas WHERE status = 'ativo'")
            ->fetchColumn();

        $tiposAtivos = (int) $this->pdo
            ->query("SELECT COUNT(*) FROM tipos_atendimentos WHERE status = 'ativo'")
            ->fetchColumn();

        $atendimentosEmAberto = (int) $this->pdo
            ->query("SELECT COUNT(*) FROM atendimentos WHERE status IN ('aberto', 'em_andamento')")
            ->fetchColumn();

        $atendimentosConcluidos = (int) $this->pdo
            ->query("SELECT COUNT(*) FROM atendimentos WHERE status = 'concluido'")
            ->fetchColumn();

        $sql = "SELECT a.id, a.data, a.hora, a.status, p.nome AS pessoa_nome, t.nome AS tipo_nome
                FROM atendimentos a
                INNER JOIN pessoas p             ON p.id = a.pessoa_id
                INNER JOIN tipos_atendimentos t  ON t.id = a.tipo_atendimento_id
                ORDER BY a.id DESC
                LIMIT 5";

        $ultimosAtendimentos = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'pessoas_ativas' => $pessoasAtivas,
            'tipos_ativos' => $tiposAtivos,
            'atendimentos_em_aberto' => $atendimentosEmAberto,
            'atendimentos_concluidos' => $atendimentosConcluidos,
            'ultimos_atendimentos' => $ultimosAtendimentos,
        ], JSON_UNESCAPED_UNICODE);
    }
}
