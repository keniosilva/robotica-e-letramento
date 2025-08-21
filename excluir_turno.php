<?php
require 'includes/auth.php';
require 'config/db.php';

if ($_SESSION['tipo'] !== 'admin') {
    header("Location: professor.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header("Location: cadastro_turno.php");
    exit;
}

$id = (int) $_POST['id'];

try {
    // Verificar se o turno está sendo usado por algum aluno ou aula
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM alunos WHERE turno_id = ?");
    $stmt->execute([$id]);
    $alunosCount = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM aulas WHERE turno_id = ?");
    $stmt->execute([$id]);
    $aulasCount = $stmt->fetchColumn();
    
    if ($alunosCount > 0 || $aulasCount > 0) {
        $mensagem = 'Não é possível excluir este turno pois ele está sendo usado por alunos ou aulas.';
        $tipo = 'warning';
    } else {
        // Excluir o turno
        $stmt = $pdo->prepare("DELETE FROM turnos WHERE id = ?");
        $stmt->execute([$id]);
        
        $mensagem = 'Turno excluído com sucesso!';
        $tipo = 'success';
    }
} catch (Exception $e) {
    $mensagem = 'Erro ao excluir turno: ' . $e->getMessage();
    $tipo = 'danger';
}

// Redirecionar de volta para a página de cadastro com a mensagem
header("Location: cadastro_turno.php?msg=" . urlencode($mensagem) . "&tipo=" . $tipo);
exit;
?>

