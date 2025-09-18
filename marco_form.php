<?php
require_once 'config.php';

$editing = isset($_GET['id']);
$projeto_id = $_GET['projeto_id'] ?? null;
$marco = null;

if ($editing) {
    $stmt = $db->prepare("SELECT * FROM milestones WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $marco = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$marco) {
        header('Location: index.php');
        exit;
    }
    $projeto_id = $marco['project_id'];
} elseif (!$projeto_id) {
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

// Processar formulário
if ($_POST) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date = $_POST['due_date'] ?: null;
    $status = $_POST['status'];
    $priority = $_POST['priority'];
    $progress = (int)$_POST['progress'];

    if ($editing) {
        $stmt = $db->prepare("UPDATE milestones SET title = ?, description = ?, due_date = ?, status = ?, priority = ?, progress = ? WHERE id = ?");
        $success = $stmt->execute([$title, $description, $due_date, $status, $priority, $progress, $marco['id']]);
        $message = $success ? 'Marco atualizado com sucesso!' : 'Erro ao atualizar marco.';
    } else {
        $stmt = $db->prepare("INSERT INTO milestones (project_id, title, description, due_date, status, priority, progress) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $success = $stmt->execute([$projeto_id, $title, $description, $due_date, $status, $priority, $progress]);
        $message = $success ? 'Marco criado com sucesso!' : 'Erro ao criar marco.';

        if ($success) {
            $marco_id = $db->lastInsertId();
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
    <title><?= $editing ? 'Editar' : 'Novo' ?> Marco - Sistema de Roadmap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a href="index.php" class="navbar-brand">
            <i class="fas fa-road"></i> Sistema de Roadmap
        </a>
        <a href="projeto_detalhes.php?id=<?= $projeto_id ?>" class="btn btn-outline-light btn-sm">
            <i class="fas fa-arrow-left"></i> Voltar ao Projeto
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
                        <?= $editing ? 'Editar Marco' : 'Novo Marco' ?>
                    </h5>
                    <small class="text-muted">Projeto: <?= htmlspecialchars($projeto['name']) ?></small>
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
                            <label for="title" class="form-label">Título do Marco *</label>
                            <input type="text" class="form-control" id="title" name="title"
                                   value="<?= htmlspecialchars($marco['title'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea class="form-control" id="description" name="description" rows="4"
                                      placeholder="Descreva os objetivos e entregas deste marco..."><?= htmlspecialchars($marco['description'] ?? '') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="due_date" class="form-label">Data de Entrega</label>
                                    <input type="date" class="form-control" id="due_date" name="due_date"
                                           value="<?= $marco['due_date'] ?? '' ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Prioridade</label>
                                    <select class="form-select" id="priority" name="priority">
                                        <option value="baixa" <?= ($marco['priority'] ?? '') == 'baixa' ? 'selected' : '' ?>>
                                            Baixa
                                        </option>
                                        <option value="media" <?= ($marco['priority'] ?? 'media') == 'media' ? 'selected' : '' ?>>
                                            Média
                                        </option>
                                        <option value="alta" <?= ($marco['priority'] ?? '') == 'alta' ? 'selected' : '' ?>>
                                            Alta
                                        </option>
                                        <option value="critica" <?= ($marco['priority'] ?? '') == 'critica' ? 'selected' : '' ?>>
                                            Crítica
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="pendente" <?= ($marco['status'] ?? 'pendente') == 'pendente' ? 'selected' : '' ?>>
                                            Pendente
                                        </option>
                                        <option value="em_progresso" <?= ($marco['status'] ?? '') == 'em_progresso' ? 'selected' : '' ?>>
                                            Em Progresso
                                        </option>
                                        <option value="concluido" <?= ($marco['status'] ?? '') == 'concluido' ? 'selected' : '' ?>>
                                            Concluído
                                        </option>
                                        <option value="atrasado" <?= ($marco['status'] ?? '') == 'atrasado' ? 'selected' : '' ?>>
                                            Atrasado
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="progress" class="form-label">Progresso (%)</label>
                            <div class="d-flex align-items-center">
                                <input type="range" class="form-range me-3" id="progress" name="progress"
                                       min="0" max="100" value="<?= $marco['progress'] ?? 0 ?>"
                                       oninput="document.getElementById('progress-value').textContent = this.value + '%'">
                                <span id="progress-value" class="badge bg-primary">
                                        <?= $marco['progress'] ?? 0 ?>%
                                    </span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= $editing ? 'marco_detalhes.php?id=' . $marco['id'] : 'projeto_detalhes.php?id=' . $projeto_id ?>"
                               class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                <?= $editing ? 'Atualizar' : 'Criar' ?> Marco
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
