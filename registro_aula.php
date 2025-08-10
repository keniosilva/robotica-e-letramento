<?php
require 'includes/auth.php';
require 'config/db.php';
require 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST['data'];
    $turma_id = $_POST['turma_id'];
    $conteudo = $_POST['conteudo'];
    $professor_id = $_SESSION['usuario_id'];

    $stmt = $pdo->prepare("INSERT INTO aulas (data, turma_id, professor_id, conteudo) VALUES (?, ?, ?, ?)");
    $stmt->execute([$data, $turma_id, $professor_id, $conteudo]);
    $aula_id = $pdo->lastInsertId();

    foreach ($_POST['presenca'] as $aluno_id => $presente) {
        $stmt = $pdo->prepare("INSERT INTO frequencias (aula_id, aluno_id, presente) VALUES (?, ?, ?)");
        $stmt->execute([$aula_id, $aluno_id, $presente]);
    }

    echo "Aula registrada!";
}
?>
<?php require 'includes/footer.php'; ?>