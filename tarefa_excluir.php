<?php
require_once 'config.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Buscar o marco da tarefa para redirecionamento
$stmt = $db->prepare("SELECT milestone_id FROM tasks WHERE id = ?");
$stmt->execute([$id]);
$tarefa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tarefa) {
    header('Location: index.php');
    exit;
}

try {
    $stmt = $db->prepare("DELETE FROM tasks WHERE id = ?");
    $success = $stmt->execute([$id]);

    if ($success) {
        header('Location: marco_detalhes.php?id=' . $tarefa['milestone_id'] . '&msg=tarefa_excluida');
    } else {
        header('Location: marco_detalhes.php?id=' . $tarefa['milestone_id'] . '&msg=erro_excluir');
    }
} catch (Exception $e) {
    header('Location: marco_detalhes.php?id=' . $tarefa['milestone_id'] . '&msg=erro_excluir');
}
exit;
?>
