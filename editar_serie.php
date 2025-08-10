<?php
require 'includes/auth.php';
require 'config/db.php';
require 'includes/header.php';

// Verifica se o ID foi passado
if (!isset($_GET['id'])) {
    echo "<p>❌ ID da série não informado.</p>";
    exit;
}

$id = $_GET['id'];

// Atualiza os dados se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];

    $stmt = $pdo->prepare("UPDATE series SET nome = ? WHERE id = ?");
    $stmt->execute([$nome, $id]);

    echo "<p>✅ Série atualizada com sucesso!</p>";
}

// Busca os dados atuais da série
$stmt = $pdo->prepare("SELECT * FROM series WHERE id = ?");
$stmt->execute([$id]);
$serie = $stmt->fetch();

if (!$serie) {
    echo "<p>❌ Série não encontrada.</p>";
    exit;
}
?>

<h2>Editar Série</h2>
<form method="POST">
  <input type="text" name="nome" value="<?= htmlspecialchars($serie['nome']) ?>" required>
  <button type="submit">Salvar Alterações</button>
</form>

<p><a href="cadastrar_serie.php">⬅️ Voltar para a lista de séries</a></p>

<?php require 'includes/footer.php'; ?>