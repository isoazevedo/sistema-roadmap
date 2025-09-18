<?php
require_once 'config.php';

if ($_POST && !empty($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    $project_id = $_POST['project_id'] ?? null;
    $milestone_id = $_POST['milestone_id'] ?? null;
    $task_id = $_POST['task_id'] ?? null;

    try {
        $stmt = $db->prepare("INSERT INTO comments (project_id, milestone_id, task_id, comment) VALUES (?, ?, ?, ?)");
        $success = $stmt->execute([$project_id, $milestone_id, $task_id, $comment]);

        if ($success) {
            if ($milestone_id) {
                header('Location: marco_detalhes.php?id=' . $milestone_id . '&msg=comentario_adicionado');
            } elseif ($project_id) {
                header('Location: projeto_detalhes.php?id=' . $project_id . '&msg=comentario_adicionado');
            }
        } else {
            // Redirect back with error
            if ($milestone_id) {
                header('Location: marco_detalhes.php?id=' . $milestone_id . '&msg=erro_comentario');
            } elseif ($project_id) {
                header('Location: projeto_detalhes.php?id=' . $project_id . '&msg=erro_comentario');
            }
        }
    } catch (Exception $e) {
        // Redirect back with error
        if ($milestone_id) {
            header('Location: marco_detalhes.php?id=' . $milestone_id . '&msg=erro_comentario');
        } elseif ($project_id) {
            header('Location: projeto_detalhes.php?id=' . $project_id . '&msg=erro_comentario');
        }
    }
}

// Redirect to index if no valid action
header('Location: index.php');
exit;
?>
