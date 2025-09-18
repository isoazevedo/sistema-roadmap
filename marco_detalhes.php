<?php
require_once 'config.php';

$marco_id = $_GET['id'] ?? null;
if (!$marco_id) {
    header('Location: index.php');
    exit;
}

// Buscar marco com informações do projeto
$stmt = $db->prepare("
    SELECT m.*, p.name as project_name, p.id as project_id 
    FROM milestones m 
    JOIN projects p ON m.project_id = p.id 
    WHERE m.id = ?
");
$stmt->execute([$marco_id]);
$marco = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$marco) {
    header('Location: index.php');
    exit;
}

// Buscar tarefas do marco
$stmt = $db->prepare("SELECT * FROM tasks WHERE milestone_id = ? ORDER BY priority DESC, due_date ASC");
$stmt->execute([$marco_id]);
$tarefas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular estatísticas
$total_tarefas = count($tarefas);
$tarefas_concluidas = count(array_filter($tarefas, fn($t) => $t['status'] == 'concluido'));
$tarefas_em_progresso = count(array_filter($tarefas, fn($t) => $t['status'] == 'em_progresso'));
$tarefas_pendentes = count(array_filter($tarefas, fn($t) => $t['status'] == 'pendente'));

$total_horas_estimadas = array_sum(array_column($tarefas, 'estimated_hours'));
$total_horas_reais = array_sum(array_column($tarefas, 'actual_hours'));

// Buscar comentários
$stmt = $db->prepare("SELECT * FROM comments WHERE milestone_id = ? ORDER BY created_at DESC");
$stmt->execute([$marco_id]);
$comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($marco['title']) ?> - Sistema de Roadmap</title>
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
            <a href="tarefa_form.php?marco_id=<?= $marco['id'] ?>" class="btn btn-success btn-sm me-2">
                <i class="fas fa-plus"></i> Nova Tarefa
            </a>
            <a href="projeto_detalhes.php?id=<?= $marco['project_id'] ?>" class="btn btn-outline-light btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar ao Projeto
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid mt-4">
    <!-- Cabeçalho do Marco -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="flex-grow-1">
                            <h2 class="mb-2"><?= htmlspecialchars($marco['title']) ?></h2>
                            <p class="text-muted mb-2"><?= htmlspecialchars($marco['description']) ?></p>
                            <small class="text-muted">
                                <i class="fas fa-project-diagram"></i>
                                Projeto: <a href="projeto_detalhes.php?id=<?= $marco['project_id'] ?>"><?= htmlspecialchars($marco['project_name']) ?></a>
                            </small>
                        </div>
                        <div class="d-flex flex-column align-items-end">
                            <div class="mb-2">
                                    <span class="badge bg-<?= getPriorityColor($marco['priority']) ?> me-1">
                                        <?= ucfirst($marco['priority']) ?>
                                    </span>
                                <span class="badge bg-<?= getStatusColor($marco['status']) ?>">
                                        <?= ucfirst(str_replace('_', ' ', $marco['status'])) ?>
                                    </span>
                            </div>
                            <div class="btn-group">
                                <a href="marco_form.php?id=<?= $marco['id'] ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-2">
                                <span class="me-2">Progresso:</span>
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
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Estatísticas -->
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Estatísticas</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total de Tarefas:</span>
                            <strong><?= $total_tarefas ?></strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-success">
                            <span>Concluídas:</span>
                            <strong><?= $tarefas_concluidas ?></strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-warning">
                            <span>Em Progresso:</span>
                            <strong><?= $tarefas_em_progresso ?></strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-secondary">
                            <span>Pendentes:</span>
                            <strong><?= $tarefas_pendentes ?></strong>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span>Horas Estimadas:</span>
                            <strong><?= $total_horas_estimadas ?></strong>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span>Horas Reais:</span>
                            <strong><?= $total_horas_reais ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulário de Comentário -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-comment"></i> Adicionar Nota</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="comentario_add.php">
                        <input type="hidden" name="milestone_id" value="<?= $marco['id'] ?>">
                        <div class="mb-3">
                            <textarea class="form-control" name="comment" rows="3" placeholder="Digite sua nota ou comentário..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-plus"></i> Adicionar Nota
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Lista de Tarefas -->
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-tasks"></i> Tarefas</h6>
                    <a href="tarefa_form.php?marco_id=<?= $marco['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Nova Tarefa
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($tarefas)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhuma tarefa definida</h5>
                            <p class="text-muted">Comece criando tarefas para este marco!</p>
                            <a href="tarefa_form.php?marco_id=<?= $marco['id'] ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Criar Primeira Tarefa
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>Tarefa</th>
                                    <th>Responsável</th>
                                    <th>Prazo</th>
                                    <th>Prioridade</th>
                                    <th>Status</th>
                                    <th>Horas</th>
                                    <th>Ações</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($tarefas as $tarefa): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($tarefa['title']) ?></strong>
                                            <?php if ($tarefa['description']): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars(substr($tarefa['description'], 0, 80)) ?>...</small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($tarefa['assigned_to'] ?: '-') ?></td>
                                        <td><?= formatDate($tarefa['due_date']) ?></td>
                                        <td>
                                                    <span class="badge bg-<?= getPriorityColor($tarefa['priority']) ?>">
                                                        <?= ucfirst($tarefa['priority']) ?>
                                                    </span>
                                        </td>
                                        <td>
                                                    <span class="badge bg-<?= getStatusColor($tarefa['status']) ?>">
                                                        <?= ucfirst(str_replace('_', ' ', $tarefa['status'])) ?>
                                                    </span>
                                        </td>
                                        <td>
                                            <small>
                                                <?= $tarefa['actual_hours'] ?: 0 ?>/<?= $tarefa['estimated_hours'] ?: 0 ?>h
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="tarefa_form.php?id=<?= $tarefa['id'] ?>" class="btn btn-outline-secondary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-outline-danger btn-sm" onclick="confirmarExclusaoTarefa(<?= $tarefa['id'] ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Comentários/Notas -->
            <?php if (!empty($comentarios)): ?>
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-comments"></i> Notas e Comentários</h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($comentarios as $comentario): ?>
                            <div class="border-bottom pb-2 mb-2">
                                <p class="mb-1"><?= nl2br(htmlspecialchars($comentario['comment'])) ?></p>
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($comentario['created_at'])) ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function confirmarExclusaoTarefa(id) {
        if (confirm('Tem certeza que deseja excluir esta tarefa?')) {
            window.location.href = 'tarefa_excluir.php?id=' + id;
        }
    }
</script>
</body>
</html>
