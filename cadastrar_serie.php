<?php
require 'includes/auth.php';
require 'config/db.php';
require 'includes/header.php';

if ($_SESSION['tipo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$sucesso = '';
$erro = '';

// Cadastro de série
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];

    // Verifica se a série já existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM series WHERE nome = ?");
    $stmt->execute([$nome]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $erro = "❌ Série '" . htmlspecialchars($nome) . "' já existe.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO series (nome) VALUES (?)");
        $stmt->execute([$nome]);
        $sucesso = "✅ Série cadastrada com sucesso!";
    }
}
?>

<div class="container mt-4">
  <h2>📚 Cadastrar Série</h2>
  <form method="POST">
    <div class="mb-3">
      <label>Nome da Série:</label>
      <input type="text" name="nome" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Cadastrar</button>
  </form>

  <?php if ($sucesso): ?>
    <div class="alert alert-success mt-3"><?= $sucesso ?></div>
  <?php endif; ?>

  <?php if ($erro): ?>
    <div class="alert alert-danger mt-3"><?= $erro ?></div>
  <?php endif; ?>

  <h2 class="mt-4">Séries Cadastradas</h2>
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>Nome</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $stmt = $pdo->query("SELECT id, nome FROM series ORDER BY nome");
      $series = $stmt->fetchAll();

      foreach ($series as $serie) {
          echo "<tr>
                  <td>" . htmlspecialchars($serie['nome']) . "</td>
                  <td>
                    <a href='editar_serie.php?id={$serie['id']}' class='btn btn-sm btn-info'>✏️ Editar</a>
                  </td>
                </tr>";
      }
      ?>
    </tbody>
  </table>
</div>

<?php require 'includes/footer.php'; ?>


