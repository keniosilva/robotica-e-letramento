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
    $turno_id = $_POST['turno_id'];

    $stmt = $pdo->prepare("INSERT INTO alunos (nome, escola_id, turma_id, serie_id, turno_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nome, $escola_id, $turma_id, $serie_id, $turno_id]);
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
      <select name="escola_id" id="escola_select" class="form-control" required>
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
      <div class="position-relative">
        <select name="turma_id" id="turma_select" class="form-control" required>
          <option value="">Selecione uma escola primeiro</option>
        </select>
        <div class="loading-spinner position-absolute top-50 end-0 translate-middle-y me-3" style="display: none;">
          <div class="spinner-border spinner-border-sm text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
          </div>
        </div>
      </div>
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

    <div class="mb-3">
      <label>Turno:</label>
      <select name="turno_id" class="form-control" required>
        <option value="">Selecione</option>
        <?php
        $turnos = $pdo->query("SELECT * FROM turnos")->fetchAll();
        foreach ($turnos as $turno) {
            echo "<option value='{$turno['id']}'>{$turno['nome']}</option>";
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
      SELECT alunos.nome AS aluno_nome, turmas.nome AS turma_nome, series.nome AS serie_nome, turnos.nome AS turno_nome
      FROM alunos
      LEFT JOIN turmas ON alunos.turma_id = turmas.id
      LEFT JOIN series ON alunos.serie_id = series.id
      LEFT JOIN turnos ON alunos.turno_id = turnos.id
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
          <th>Turno</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($alunos as $aluno): ?>
          <tr>
            <td><?= htmlspecialchars($aluno['aluno_nome']) ?></td>
            <td><?= htmlspecialchars($aluno['turma_nome']) ?></td>
            <td><?= htmlspecialchars($aluno['serie_nome']) ?></td>
            <td><?= htmlspecialchars($aluno['turno_nome']) ?></td>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const escolaSelect = document.getElementById('escola_select');
    const turmaSelect = document.getElementById('turma_select');
    const loadingSpinner = document.querySelector('.loading-spinner');

    escolaSelect.addEventListener('change', function() {
        const escolaId = this.value;
        
        // Limpar turmas e mostrar mensagem padrão
        turmaSelect.innerHTML = '<option value="">Selecione uma escola primeiro</option>';
        
        if (escolaId === '') {
            return;
        }

        // Mostrar loading
        loadingSpinner.style.display = 'block';
        turmaSelect.disabled = true;

        // Fazer requisição AJAX
        fetch(`get_turmas_by_escola.php?escola_id=${escolaId}`)
            .then(response => response.json())
            .then(data => {
                // Esconder loading
                loadingSpinner.style.display = 'none';
                turmaSelect.disabled = false;

                if (data.error) {
                    turmaSelect.innerHTML = '<option value="">Erro ao carregar turmas</option>';
                    console.error('Erro:', data.error);
                    return;
                }

                // Limpar e popular o select de turmas
                turmaSelect.innerHTML = '<option value="">Selecione uma turma</option>';
                
                if (data.length === 0) {
                    turmaSelect.innerHTML = '<option value="">Nenhuma turma encontrada</option>';
                } else {
                    data.forEach(turma => {
                        const option = document.createElement('option');
                        option.value = turma.id;
                        option.textContent = turma.nome;
                        turmaSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                loadingSpinner.style.display = 'none';
                turmaSelect.disabled = false;
                turmaSelect.innerHTML = '<option value="">Erro ao carregar turmas</option>';
            });
    });
});
</script>
</body>
</html>