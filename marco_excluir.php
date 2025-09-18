<?php
require_once 'config.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Buscar o projeto do marco para redirecionamento
$stmt = $db->prepare("SELECT project_id FROM milestones WHERE id = ?");
$stmt->execute([$id]);
$marco = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$marco) {
    header('Location: index.php');
    exit;
}

try {
    $stmt = $db->prepare("DELETE FROM milestones WHERE id = ?");
    $success = $stmt->execute([$id]);

    if ($success) {
        header('Location: projeto_detalhes.php?id=' . $marco['project_id'] . '&msg=marco_excluido');
    } else {
        header('Location: projeto_detalhes.php?id=' . $marco['project_id'] . '&msg=erro_excluir');
    }
} catch (Exception $e) {
    header('Location: projeto_detalhes.php?id=' . $marco['project_id'] . '&msg=erro_excluir');
}
exit;
?>
