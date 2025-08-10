<?php
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
    $stmt = $pdo->prepare("INSERT INTO turmas (nome, escola_id) VALUES (?, ?)");
    $stmt->execute([$nome, $escola_id]);
    $sucesso = "Turma cadastrada com sucesso!";
}
?>

<div class="container mt-4">
  <h2>👥 Cadastro de Turma</h2>
  <form method="POST">
    <div class="mb-3">
      <label>Nome da Turma:</label>
      <input type="text" name="nome" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Escola:</label>
      <select name="escola_id" class="form-control" required>
        <?php
        $escolas = $pdo->query("SELECT * FROM escolas")->fetchAll();
        foreach ($escolas as $escola) {
            echo "<option value='{$escola['id']}'>{$escola['nome']}</option>";
        }
        ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Cadastrar</button>
  </form>

  <?php if ($sucesso): ?>
    <div class="alert alert-success mt-3"><?= $sucesso ?></div>
  <?php endif; ?>
</div>

<?php require 'includes/footer.php'; ?>