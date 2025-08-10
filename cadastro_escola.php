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
$edit_id = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;
$edit_nome = '';

if ($edit_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT nome FROM escolas WHERE id = ?");
        $stmt->execute([$edit_id]);
        $escola = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($escola) {
            $edit_nome = $escola['nome'];
        } else {
            $erro = "Escola não encontrada.";
            $edit_id = 0;
        }
    } catch (PDOException $e) {
        $erro = "Erro ao buscar escola: " . $e->getMessage();
        $edit_id = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['edit_id'])) {
    $nome = trim($_POST['nome']);
    if (!empty($nome)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO escolas (nome) VALUES (?)");
            $stmt->execute([$nome]);
            $sucesso = "Escola cadastrada com sucesso!";
        } catch (PDOException $e) {
            $erro = "Erro ao cadastrar escola: " . $e->getMessage();
        }
    } else {
        $erro = "O nome da escola é obrigatório.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id = (int)$_POST['edit_id'];
    $nome = trim($_POST['nome']);
    if (!empty($nome)) {
        try {
            $stmt = $pdo->prepare("UPDATE escolas SET nome = ? WHERE id = ?");
            $stmt->execute([$nome, $id]);
            $sucesso = "Escola atualizada com sucesso!";
            $edit_id = 0;
        } catch (PDOException $e) {
            $erro = "Erro ao atualizar escola: " . $e->getMessage();
        }
    } else {
        $erro = "O nome da escola é obrigatório.";
    }
}

// Fetch all registered schools
try {
    $stmt = $pdo->query("SELECT id, nome FROM escolas");
    $escolas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $erro = "Erro ao listar escolas: " . $e->getMessage();
    $escolas = [];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <title>Cadastro de Escola</title>
    <style>
        body {
            background-color: #e9ecef;
        }
        .card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
<div class="container mt-4">
  <h2><i class="bi bi-building-fill me-2" style="color: #007bff;"></i>Cadastro de Escola</h2>
  <div class="row">
    <div class="col-md-6">
      <div class="card bg-light shadow mb-4">
        <div class="card-body">
          <h5 class="card-title">Nova Escola</h5>
          <form method="POST" action="">
            <div class="mb-3">
              <label for="nome" class="form-label">Nome da Escola:</label>
              <input type="text" name="nome" id="nome" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Cadastrar</button>
          </form>
        </div>
      </div>
    </div>
    <?php if ($edit_id > 0): ?>
      <div class="col-md-6">
        <div class="card bg-light shadow mb-4">
          <div class="card-body">
            <h5 class="card-title">Editar Escola</h5>
            <form method="POST" action="">
              <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
              <div class="mb-3">
                <label for="nomeEdit" class="form-label">Nome da Escola:</label>
                <input type="text" name="nome" id="nomeEdit" class="form-control" value="<?= htmlspecialchars($edit_nome) ?>" required>
              </div>
              <button type="submit" class="btn btn-primary">Salvar</button>
              <a href="cadastro_escola.php" class="btn btn-secondary">Cancelar</a>
            </form>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <?php if ($sucesso): ?>
    <div class="alert alert-success" role="alert"><?= htmlspecialchars($sucesso) ?></div>
  <?php endif; ?>
  <?php if ($erro): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>

  <h3 class="mt-4">Escolas Cadastradas</h3>
  <div class="card bg-light shadow">
    <div class="card-body">
      <?php if (empty($escolas)): ?>
        <p>Nenhuma escola cadastrada.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Nome da Escola</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($escolas as $escola): ?>
                <tr>
                  <td><?= htmlspecialchars($escola['nome']) ?></td>
                  <td>
                    <a href="cadastro_escola.php?edit_id=<?= $escola['id'] ?>" class="btn btn-sm btn-warning">
                      <i class="bi bi-pencil-fill"></i> Editar
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
</body>
</html>