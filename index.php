<?php
require_once 'config.php';

// Buscar projetos
$stmt = $db->query("SELECT * FROM projects ORDER BY created_at DESC");
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar estatísticas
$stats_query = "
    SELECT 
        COUNT(DISTINCT p.id) as total_projects,
        COUNT(DISTINCT m.id) as total_milestones,
        COUNT(DISTINCT t.id) as total_tasks,
        AVG(m.progress) as avg_progress
    FROM projects p 
    LEFT JOIN milestones m ON p.id = m.project_id 
    LEFT JOIN tasks t ON m.id = t.milestone_id
";
$stats = $db->query($stats_query)->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Roadmap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-road"></i> Sistema de Roadmap
            </span>
        <div class="d-flex">
            <a href="projeto_form.php" class="btn btn-success btn-sm me-2">
                <i class="fas fa-plus"></i> Novo Projeto
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid mt-4">
    <!-- Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5>Projetos</h5>
                            <h3><?= $stats['total_projects'] ?></h3>
                        </div>
                        <i class="fas fa-project-diagram fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5>Marcos</h5>
                            <h3><?= $stats['total_milestones'] ?></h3>
                        </div>
                        <i class="fas fa-flag fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5>Tarefas</h5>
                            <h3><?= $stats['total_tasks'] ?></h3>
                        </div>
                        <i class="fas fa-tasks fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5>Progresso Médio</h5>
                            <h3><?= round($stats['avg_progress'] ?? 0) ?>%</h3>
                        </div>
                        <i class="fas fa-chart-line fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Projetos -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Meus Projetos</h5>
                    <a href="projeto_form.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Novo Projeto
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($projects)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhum projeto encontrado</h5>
                            <p class="text-muted">Comece criando seu primeiro roadmap!</p>
                            <a href="projeto_form.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Criar Primeiro Projeto
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($projects as $project): ?>
                                <div class="col-lg-4 col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title"><?= htmlspecialchars($project['name']) ?></h6>
                                                <span class="badge bg-<?= getStatusColor($project['status']) ?>">
                                                        <?= ucfirst($project['status']) ?>
                                                    </span>
                                            </div>
                                            <p class="card-text text-muted small">
                                                <?= htmlspecialchars(substr($project['description'], 0, 100)) ?>
                                                <?= strlen($project['description']) > 100 ? '...' : '' ?>
                                            </p>
                                            <div class="small text-muted mb-3">
                                                <i class="fas fa-calendar"></i>
                                                <?= formatDate($project['start_date']) ?> - <?= formatDate($project['end_date']) ?>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <a href="projeto_detalhes.php?id=<?= $project['id'] ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-eye"></i> Ver Detalhes
                                                </a>
                                                <div class="btn-group">
                                                    <a href="projeto_form.php?id=<?= $project['id'] ?>" class="btn btn-outline-secondary btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-outline-danger btn-sm" onclick="confirmarExclusao(<?= $project['id'] ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function confirmarExclusao(id) {
        if (confirm('Tem certeza que deseja excluir este projeto? Esta ação não pode ser desfeita.')) {
            window.location.href = 'projeto_excluir.php?id=' + id;
        }
    }
</script>
</body>
</html>
