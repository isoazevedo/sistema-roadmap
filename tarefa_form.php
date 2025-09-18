<?php
require_once 'config.php';

$editing = isset($_GET['id']);
$marco_id = $_GET['marco_id'] ?? null;
$tarefa = null;

if ($editing) {
    $stmt = $db->prepare("SELECT t.*, m.title as milestone_title, m.project_id FROM tasks t JOIN milestones m ON t.milestone_id = m.id WHERE t.id = ?");
    $stmt->execute([$_GET['id']]);
    $tarefa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tarefa) {
        header('Location: index.php');
        exit;
    }
    $marco_id = $tarefa['milestone_id'];
} elseif (!$marco_id) {
    header('Location: index.php');
    exit;
}

// Buscar marco
$stmt = $db->prepare("SELECT m.*, p.name as project_name FROM milestones m JOIN projects p ON m.project_id = p.id WHERE m.id = ?");
$stmt->execute([$marco_id]);
$marco = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$marco) {
    header('Location: index.php');
    exit;
}

// Processar formulário
if ($_POST) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $assigned_to = trim($_POST['assigned_to']);
    $due_date = $_POST['due_date'] ?: null;
    $status = $_POST['status'];
    $priority = $_POST['priority'];
    $estimated_hours = $_POST['estimated_hours'] ? (int)$_POST['estimated_hours'] : null;
    $actual_hours = $_POST['actual_hours'] ? (int)$_POST['actual_hours'] : null;

    if ($editing) {
        $stmt = $db->prepare("UPDATE tasks SET title = ?, description = ?, assigned_to = ?, due_date = ?, status = ?, priority = ?, estimated_hours = ?, actual_hours = ? WHERE id = ?");
        $success = $stmt->execute([$title, $description, $assigned_to, $due_date, $status, $priority, $estimated_hours, $actual_hours, $tarefa['id']]);
        $message = $success ? 'Tarefa atualizada com sucesso!' : 'Erro ao atualizar tarefa.';
    } else {
        $stmt = $db->prepare("INSERT INTO tasks (milestone_id, title, description, assigned_to, due_date, status, priority, estimated_hours, actual_hours) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $success = $stmt->execute([$marco_id, $title, $description, $assigned_to, $due_date, $status, $priority, $estimated_hours, $actual_hours]);
        $message = $success ? 'Tarefa criada com sucesso!' : 'Erro ao criar tarefa.';

        if ($success) {
            header("Location: marco_detalhes.php?id=$marco_id");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editing ? 'Editar' : 'Nova' ?> Tarefa - Sistema de Roadmap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a href="index.php" class="navbar-brand">
            <i class="fas fa-road"></i> Sistema de Roadmap
        </a>
        <a href="marco_detalhes.php?id=<?= $marco_id ?>" class="btn btn-outline-light btn-sm">
            <i class="fas fa-arrow-left"></i> Voltar ao Marco
        </a>
    </div>
</nav>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-<?= $editing ? 'edit' : 'plus' ?>"></i>
                        <?= $editing ? 'Editar Tarefa' : 'Nova Tarefa' ?>
                    </h5>
                    <small class="text-muted">
                        Projeto: <?= htmlspecialchars($marco['project_name']) ?> |
                        Marco: <?= htmlspecialchars($marco['title']) ?>
                    </small>
                </div>
                <div class="card-body">
                    <?php if (isset($message)): ?>
                        <div class="alert alert-<?= $success ? 'success' : 'danger' ?> alert-dismissible fade show">
                            <?= $message ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="title" class="form-label">Título da Tarefa *</label>
                            <input type="text" class="form-control" id="title" name="title"
                                   value="<?= htmlspecialchars($tarefa['title'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea class="form-control" id="description" name="description" rows="4"
                                      placeholder="Descreva o que precisa ser feito..."><?= htmlspecialchars($tarefa['description'] ?? '') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="assigned_to" class="form-label">Responsável</label>
                                    <input type="text" class="form-control" id="assigned_to" name="assigned_to"
                                           value="<?= htmlspecialchars($tarefa['assigned_to'] ?? '') ?>"
                                           placeholder="Nome do responsável">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="due_date" class="form-label">Data de Entrega</label>
                                    <input type="date" class="form-control" id="due_date" name="due_date"
                                           value="<?= $tarefa['due_date'] ?? '' ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Prioridade</label>
                                    <select class="form-select" id="priority" name="priority">
                                        <option value="baixa" <?= ($tarefa['priority'] ?? '') == 'baixa' ? 'selected' : '' ?>>
                                            Baixa
                                        </option>
                                        <option value="media" <?= ($tarefa['priority'] ?? 'media') == 'media' ? 'selected' : '' ?>>
                                            Média
                                        </option>
                                        <option value="alta" <?= ($tarefa['priority'] ?? '') == 'alta' ? 'selected' : '' ?>>
                                            Alta
                                        </option>
                                        <option value="critica" <?= ($tarefa['priority'] ?? '') == 'critica' ? 'selected' : '' ?>>
                                            Crítica
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="pendente" <?= ($tarefa['status'] ?? 'pendente') == 'pendente' ? 'selected' : '' ?>>
                                            Pendente
                                        </option>
                                        <option value="em_progresso" <?= ($tarefa['status'] ?? '') == 'em_progresso' ? 'selected' : '' ?>>
                                            Em Progresso
                                        </option>
                                        <option value="concluido" <?= ($tarefa['status'] ?? '') == 'concluido' ? 'selected' : '' ?>>
                                            Concluído
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="estimated_hours" class="form-label">Horas Estimadas</label>
                                    <input type="number" class="form-control" id="estimated_hours" name="estimated_hours"
                                           value="<?= $tarefa['estimated_hours'] ?? '' ?>" min="0" step="0.5">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="actual_hours" class="form-label">Horas Realizadas</label>
                                    <input type="number" class="form-control" id="actual_hours" name="actual_hours"
                                           value="<?= $tarefa['actual_hours'] ?? '' ?>" min="0" step="0.5">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="marco_detalhes.php?id=<?= $marco_id ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                <?= $editing ? 'Atualizar' : 'Criar' ?> Tarefa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
