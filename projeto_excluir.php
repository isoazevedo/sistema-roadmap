<?php
require_once 'config.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

try {
    $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
    $success = $stmt->execute([$id]);

    if ($success) {
        header('Location: index.php?msg=projeto_excluido');
    } else {
        header('Location: index.php?msg=erro_excluir');
    }
} catch (Exception $e) {
    header('Location: index.php?msg=erro_excluir');
}
exit;
?>
