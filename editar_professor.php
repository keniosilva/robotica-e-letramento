<?php
require 'includes/auth.php';
require 'config/db.php';
require 'includes/header.php';

// Verifica se o ID foi passado
if (!isset($_GET['id'])) {
    echo "<p>❌ ID do professor não informado.</p>";
    exit;
}

$id = $_GET['id'];

// Atualiza os dados se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $escola_id = $_POST['escola_id'];

    // Atualiza senha apenas se foi preenchida
    if (!empty($_POST['senha'])) {
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE professores SET nome = ?, email = ?, senha = ?, escola_id = ? WHERE id = ?");
        $stmt->execute([$nome, $email, $senha, $escola_id, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE professores SET nome = ?, email = ?, escola_id = ? WHERE id = ?");
        $stmt->execute([$nome, $email, $escola_id, $id]);
    }

    echo "<p>✅ Dados atualizados com sucesso!</p>";
}

// Busca os dados atuais do professor
$stmt = $pdo->prepare("SELECT * FROM professores WHERE id = ?");
$stmt->execute([$id]);
$professor = $stmt->fetch();

if (!$professor) {
    echo "<p>❌ Professor não encontrado.</p>";
    exit;
}
?>

<h2>Editar Professor</h2>
<form method="POST">
  <input type="text" name="nome" value="<?= htmlspecialchars($professor['nome']) ?>" required>
  <input type="email" name="email" value="<?= htmlspecialchars($professor['email']) ?>" required>
  <input type="password" name="senha" placeholder="Nova senha (opcional)">
  <select name="escola_id" required>
    <option value="">Selecione a escola</option>
    <?php
    $escolas = $pdo->query("SELECT id, nome FROM escolas")->fetchAll();
    foreach ($escolas as $escola) {
        $selected = $escola['id'] == $professor['escola_id'] ? 'selected' : '';
        echo "<option value='{$escola['id']}' $selected>{$escola['nome']}</option>";
    }
    ?>
  </select>
  <button type="submit">Salvar Alterações</button>
</form>

<p><a href="professor.php">⬅️ Voltar para a lista de professores</a></p>

<?php require 'includes/footer.php'; ?>