<?php
require 'includes/auth.php';
require 'config/db.php';

if ($_SESSION['tipo'] !== 'professor') {
    header("Location: admin.php");
    exit;
}

// Verifica se os dados foram enviados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $escola_id = $_POST['escola_id'] ?? '';
    $turma_id = $_POST['turma_id'] ?? '';
    $serie_id = $_POST['serie_id'] ?? '';
    $conteudo = $_POST['conteudo'] ?? '';
    $presencas = $_POST['presenca'] ?? [];

    // ✅ Verificação de campos obrigatórios
    if (empty($escola_id) || empty($turma_id) || empty($serie_id) || empty($conteudo)) {
        die("❌ Todos os campos obrigatórios devem ser preenchidos.");
    }

    // 1. Inserir aula
    $stmt = $pdo->prepare("INSERT INTO aulas (escola_id, turma_id, serie_id, conteudo, data) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$escola_id, $turma_id, $serie_id, $conteudo]);
    $aula_id = $pdo->lastInsertId();

    // 2. Buscar alunos da turma
    $stmt = $pdo->prepare("SELECT id FROM alunos WHERE escola_id = ? AND serie_id = ? AND turma_id = ?");
    $stmt->execute([$escola_id, $serie_id, $turma_id]);
    $alunos = $stmt->fetchAll();

    // 3. Registrar presença/falta
    $stmtPresenca = $pdo->prepare("INSERT INTO presencas (aula_id, aluno_id, presente) VALUES (?, ?, ?)");

    foreach ($alunos as $aluno) {
        $aluno_id = $aluno['id'];
        $presente = isset($presencas[$aluno_id]) ? 1 : 0;
        $stmtPresenca->execute([$aula_id, $aluno_id, $presente]);
    }

    // Redireciona com sucesso
    header("Location: professor.php?sucesso=1&turma_id=$turma_id");
    exit;
} else {
    header("Location: professor.php");
    exit;
}