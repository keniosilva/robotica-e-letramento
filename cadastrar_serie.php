<?php
require 'includes/auth.php';
require 'config/db.php';
require 'includes/header.php';

// Cadastro de série
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];

    $stmt = $pdo->prepare("INSERT INTO series (nome) VALUES (?)");
    $stmt->execute([$nome]);

    echo "<p>✅ Série cadastrada com sucesso!</p>";
}
?>

<h2>Cadastrar Série</h2>
<form method="POST">
  <input type="text" name="nome" placeholder="Nome da série" required>
  <button type="submit">Cadastrar</button>
</form>

<hr>

<h2>Séries Cadastradas</h2>
<table border="1" cellpadding="8" cellspacing="0">
  <tr>
    <th>Nome</th>
    <th>Ações</th>
  </tr>
  <?php
  $stmt = $pdo->query("SELECT id, nome FROM series");
  $series = $stmt->fetchAll();

  foreach ($series as $serie) {
      echo "<tr>
              <td>{$serie['nome']}</td>
              <td>
                <a href='editar_serie.php?id={$serie['id']}'>✏️ Editar</a>
              </td>
            </tr>";
  }
  ?>
</table>

<?php require 'includes/footer.php'; ?>