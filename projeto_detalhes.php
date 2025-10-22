<?php
require_once 'config/config.php';

$projeto_id = $_GET['id'] ?? null;
if (!$projeto_id) {
    header('Location: index.php');
    exit;
}

// Buscar projeto
$stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$projeto_id]);
$projeto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$projeto) {
    header('Location: index.php');
    exit;
}

$filter_status = $_GET['status'] ?? '';
$params = [$projeto_id];
$statusSql = '';
if ($filter_status !== '') {
    $statusSql = " AND m.status = ?";
    $params[] = $filter_status;
}
// Buscar marcos do projeto
$stmt = $db->prepare("
    SELECT m.*,
           COUNT(t.id) as total_tasks,
           COUNT(CASE WHEN t.status = 'concluido' THEN 1 END) as completed_tasks
    FROM milestones m
    LEFT JOIN tasks t ON m.id = t.milestone_id
    WHERE m.project_id = ? $statusSql
    GROUP BY m.id
    ORDER BY m.due_date ASC, m.created_at ASC
");
$stmt->execute($params);
$marcos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular progresso geral do projeto
$total_progress = 0;
$count_marcos = count($marcos);
if ($count_marcos > 0) {
    foreach ($marcos as $marco) {
        $total_progress += $marco['progress'];
    }
    $projeto_progress = round($total_progress / $count_marcos);
} else {
    $projeto_progress = 0;
}



?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($projeto['name']) ?> - Sistema de Roadmap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a href="index.php" class="navbar-brand">
            <i class="fas fa-road"></i> Sistema de Roadmap
        </a>
        <div class="d-flex">
            <a href="marco_form.php?projeto_id=<?= $projeto['id'] ?>" class="btn btn-success btn-sm me-2">
                <i class="fas fa-plus"></i> Novo Marco
            </a>
            <a href="index.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid mt-4">
    <!-- Cabeçalho do Projeto -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h2 class="mb-2"><?= htmlspecialchars($projeto['name']) ?></h2>
                            <p class="text-muted mb-3"><?= htmlspecialchars($projeto['description']) ?></p>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i>
                                        <?= formatDate($projeto['start_date']) ?> - <?= formatDate($projeto['end_date']) ?>
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">Progresso:</span>
                                        <div class="progress flex-grow-1 me-2">
                                            <div class="progress-bar" style="width: <?= $projeto_progress ?>%"></div>
                                        </div>
                                        <small><?= $projeto_progress ?>%</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column align-items-end">
                                <span class="badge bg-<?= getStatusColor($projeto['status']) ?> mb-2">
                                    <?= ucfirst(str_replace('_', ' ', $projeto['status'])) ?>
                                </span>
                            <div class="btn-group">
                                <a href="projeto_form.php?id=<?= $projeto['id'] ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline dos Marcos -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-flag"></i> Marcos do Projeto
                    </h5>
                    <!-- HTML: substitua o comentário pelo formulário de filtro -->
                    <form method="get" class="align-items-center">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($projeto['id']) ?>">
                        <label class="me-4 mb-0 small">Filtrar por Status:</label>
                        <select name="status" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                            <option value="" <?= $filter_status === '' ? 'selected' : '' ?>>Todos</option>
                            <option value="pendente" <?= $filter_status === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                            <option value="em_andamento" <?= $filter_status === 'em_andamento' ? 'selected' : '' ?>>Em Andamento</option>
                            <option value="concluido" <?= $filter_status === 'concluido' ? 'selected' : '' ?>>Concluído</option>
                        </select>
                        <noscript><button class="btn btn-sm btn-outline-secondary" type="submit">Filtrar</button></noscript>
                    </form>
                    <a href="marco_form.php?projeto_id=<?= $projeto['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Novo Marco
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($marcos)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-flag fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhum marco definido</h5>
                            <p class="text-muted">Comece criando marcos para organizar seu roadmap!</p>
                            <a href="marco_form.php?projeto_id=<?= $projeto['id'] ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Criar Primeiro Marco
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($marcos as $index => $marco): ?>
                                <div class="timeline-item mb-4">
                                    <div class="row">
                                        <div class="col-md-1 d-flex justify-content-center">
                                            <div class="timeline-marker">
                                                <div class="timeline-dot bg-<?= getStatusColor($marco['status']) ?>">
                                                    <?= $index + 1 ?>
                                                </div>
                                                <?php if ($index < count($marcos) - 1): ?>
                                                    <div class="timeline-line"></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-11">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div>
                                                            <h6 class="mb-1"><?= htmlspecialchars($marco['title']) ?></h6>
                                                            <p class="text-muted small mb-2"><?= htmlspecialchars($marco['description']) ?></p>
                                                        </div>
                                                        <div class="d-flex align-items-center">
                                                                <span class="badge bg-<?= getPriorityColor($marco['priority']) ?> me-2">
                                                                    <?= ucfirst($marco['priority']) ?>
                                                                </span>
                                                            <span class="badge bg-<?= getStatusColor($marco['status']) ?>">
                                                                    <?= ucfirst(str_replace('_', ' ', $marco['status'])) ?>
                                                                </span>
                                                        </div>
                                                    </div>

                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <div class="d-flex align-items-center mb-2">
                                                                <span class="me-2 small">Progresso:</span>
                                                                <div class="progress flex-grow-1 me-2">
                                                                    <div class="progress-bar" style="width: <?= $marco['progress'] ?>%"></div>
                                                                </div>
                                                                <small><?= $marco['progress'] ?>%</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <small class="text-muted">
                                                                <i class="fas fa-calendar"></i> Prazo: <?= formatDate($marco['due_date']) ?>
                                                            </small>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <small class="text-muted">
                                                                <i class="fas fa-tasks"></i>
                                                                <?= $marco['completed_tasks'] ?>/<?= $marco['total_tasks'] ?> tarefas concluídas
                                                            </small>
                                                        </div>
                                                        <div class="btn-group">
                                                            <a href="marco_detalhes.php?id=<?= $marco['id'] ?>" class="btn btn-primary btn-sm">
                                                                <i class="fas fa-eye"></i> Ver Tarefas
                                                            </a>
                                                            <a href="marco_form.php?id=<?= $marco['id'] ?>" class="btn btn-outline-secondary btn-sm">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button class="btn btn-outline-danger btn-sm" onclick="confirmarExclusaoMarco(<?= $marco['id'] ?>)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
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

<style>
    .timeline-marker {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .timeline-dot {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 14px;
        border: 3px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .timeline-line {
        width: 2px;
        height: 60px;
        background-color: #dee2e6;
        margin-top: 10px;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function confirmarExclusaoMarco(id) {
        if (confirm('Tem certeza que deseja excluir este marco? Todas as tarefas relacionadas também serão excluídas.')) {
            window.location.href = 'marco_excluir.php?id=' + id;
        }
    }
</script>
</body>
</html>
