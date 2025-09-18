<?php
require_once 'config.php';

$editing = isset($_GET['id']);
$projeto = null;

if ($editing) {
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $projeto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$projeto) {
        header('Location: index.php');
        exit;
    }
}

// Processar formulário
if ($_POST) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $start_date = $_POST['start_date'] ?: null;
    $end_date = $_POST['end_date'] ?: null;
    $status = $_POST['status'];

    if ($editing) {
        $stmt = $db->prepare("UPDATE projects SET name = ?, description = ?, start_date = ?, end_date = ?, status = ? WHERE id = ?");
        $success = $stmt->execute([$name, $description, $start_date, $end_date, $status, $projeto['id']]);
        $message = $success ? 'Projeto atualizado com sucesso!' : 'Erro ao atualizar projeto.';
    } else {
        $stmt = $db->prepare("INSERT INTO projects (name, description, start_date, end_date, status) VALUES (?, ?, ?, ?, ?)");
        $success = $stmt->execute([$name, $description, $start_date, $end_date, $status]);
        $message = $success ? 'Projeto criado com sucesso!' : 'Erro ao criar projeto.';

        if ($success) {
            $projeto_id = $db->lastInsertId();
            header("Location: projeto_detalhes.php?id=$projeto_id");
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
    <title><?= $editing ? 'Editar' : 'Novo' ?> Projeto - Sistema de Roadmap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a href="index.php" class="navbar-brand">
            <i class="fas fa-road"></i> Sistema de Roadmap
        </a>
        <a href="index.php" class="btn btn-outline-light btn-sm">
            <i class="fas fa-arrow-left"></i> Voltar
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
                        <?= $editing ? 'Editar Projeto' : 'Novo Projeto' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($message)): ?>
                        <div class="alert alert-<?= $success ? 'success' : 'danger' ?> alert-dismissible fade show">
                            <?= $message ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome do Projeto *</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                           value="<?= htmlspecialchars($projeto['name'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="planejamento" <?= ($projeto['status'] ?? '') == 'planejamento' ? 'selected' : '' ?>>
                                            Planejamento
                                        </option>
                                        <option value="em_andamento" <?= ($projeto['status'] ?? '') == 'em_andamento' ? 'selected' : '' ?>>
                                            Em Andamento
                                        </option>
                                        <option value="pausado" <?= ($projeto['status'] ?? '') == 'pausado' ? 'selected' : '' ?>>
                                            Pausado
                                        </option>
                                        <option value="concluido" <?= ($projeto['status'] ?? '') == 'concluido' ? 'selected' : '' ?>>
                                            Concluído
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea class="form-control" id="description" name="description" rows="4"
                                      placeholder="Descreva o objetivo e escopo do projeto..."><?= htmlspecialchars($projeto['description'] ?? '') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Data de Início</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date"
                                           value="<?= $projeto['start_date'] ?? '' ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">Data Prevista de Término</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date"
                                           value="<?= $projeto['end_date'] ?? '' ?>">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= $editing ? 'projeto_detalhes.php?id=' . $projeto['id'] : 'index.php' ?>"
                               class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                <?= $editing ? 'Atualizar' : 'Criar' ?> Projeto
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
