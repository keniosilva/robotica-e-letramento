<?php
//session_start();
require 'includes/auth.php';
require 'config/db.php';
require 'includes/header.php';

if ($_SESSION['tipo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$sucesso = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $escola_id = $_POST['escola_id'];
    $turma_id = $_POST['turma_id'];
    $serie_id = $_POST['serie_id'];

    $stmt = $pdo->prepare("INSERT INTO alunos (nome, escola_id, turma_id, serie_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nome, $escola_id, $turma_id, $serie_id]);
    $sucesso = "Aluno cadastrado com sucesso!";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Cadastro de Aluno</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
  <h2>👦 Cadastro de Aluno</h2>
  <form method="POST">
    <div class="mb-3">
      <label>Nome do Aluno:</label>
      <input type="text" name="nome" class="form-control" required>
    </div>

    <div class="mb-3">
      <label>Escola:</label>
      <select name="escola_id" class="form-control" required>
        <option value="">Selecione</option>
        <?php
        $escolas = $pdo->query("SELECT * FROM escolas")->fetchAll();
        foreach ($escolas as $escola) {
            echo "<option value='{$escola['id']}'>{$escola['nome']}</option>";
        }
        ?>
      </select>
    </div>

    <div class="mb-3">
      <label>Turma:</label>
      <select name="turma_id" class="form-control" required>
        <option value="">Selecione</option>
        <?php
        $turmas = $pdo->query("SELECT * FROM turmas")->fetchAll();
        foreach ($turmas as $turma) {
            echo "<option value='{$turma['id']}'>{$turma['nome']}</option>";
        }
        ?>
      </select>
    </div>

    <div class="mb-3">
      <label>Série:</label>
      <select name="serie_id" class="form-control" required>
        <option value="">Selecione</option>
        <?php
        $series = $pdo->query("SELECT * FROM series")->fetchAll();
        foreach ($series as $serie) {
            echo "<option value='{$serie['id']}'>{$serie['nome']}</option>";
        }
        ?>
      </select>
    </div>

    <button type="submit" class="btn btn-primary">Cadastrar</button>
  </form>

  <?php if ($sucesso): ?>
    <div class="alert alert-success mt-3"><?= $sucesso ?></div>
  <?php endif; ?>

  <hr>
  <h3>👥 Alunos Cadastrados por Escola</h3>

  <?php
  $escolas = $pdo->query("SELECT * FROM escolas")->fetchAll();
  foreach ($escolas as $escola):
    echo "<h4 class='mt-4'>{$escola['nome']}</h4>";

    $stmt = $pdo->prepare("
      SELECT alunos.nome AS aluno_nome, turmas.nome AS turma_nome, series.nome AS serie_nome
      FROM alunos
      LEFT JOIN turmas ON alunos.turma_id = turmas.id
      LEFT JOIN series ON alunos.serie_id = series.id
      WHERE alunos.escola_id = ?
    ");
    $stmt->execute([$escola['id']]);
    $alunos = $stmt->fetchAll();

    if ($alunos):
  ?>
    <table class="table table-bordered table-striped">
      <thead class="table-secondary">
        <tr>
          <th>Nome</th>
          <th>Turma</th>
          <th>Série</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($alunos as $aluno): ?>
          <tr>
            <td><?= htmlspecialchars($aluno['aluno_nome']) ?></td>
            <td><?= htmlspecialchars($aluno['turma_nome']) ?></td>
            <td><?= htmlspecialchars($aluno['serie_nome']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p class="text-muted">Nenhum aluno cadastrado nesta escola.</p>
  <?php endif; endforeach; ?>
</div>

<footer class="bg-light text-center text-muted py-3 mt-5">
  <small>&copy; <?= date('Y') ?> Robótica Escolar</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>