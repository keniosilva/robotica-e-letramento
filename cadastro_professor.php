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
$edit_email = '';
$edit_escola_id = '';

if ($edit_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT nome, email, escola_id FROM professores WHERE id = ?");
        $stmt->execute([$edit_id]);
        $professor = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($professor) {
            $edit_nome = $professor['nome'];
            $edit_email = $professor['email'];
            $edit_escola_id = $professor['escola_id'];
        } else {
            $erro = "Professor não encontrado.";
            $edit_id = 0;
        }
    } catch (PDOException $e) {
        $erro = "Erro ao buscar professor: " . $e->getMessage();
        $edit_id = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['edit_id'])) {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    $escola_id = (int)$_POST['escola_id'];

    if (!empty($nome) && !empty($email) && !empty($senha) && !empty($escola_id)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM professores WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $erro = "Este email já está cadastrado.";
            } else {
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO professores (nome, email, senha, escola_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nome, $email, $senha_hash, $escola_id]);
                $sucesso = "Professor cadastrado com sucesso!";
            }
        } catch (PDOException $e) {
            $erro = "Erro ao cadastrar professor: " . $e->getMessage();
        }
    } else {
        $erro = "Todos os campos são obrigatórios.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id = (int)$_POST['edit_id'];
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $escola_id = (int)$_POST['escola_id'];
    $senha = !empty($_POST['senha']) ? password_hash(trim($_POST['senha']), PASSWORD_DEFAULT) : null;

    if (!empty($nome) && !empty($email) && !empty($escola_id)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM professores WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                $erro = "Este email já está cadastrado para outro professor.";
            } else {
                if ($senha) {
                    $stmt = $pdo->prepare("UPDATE professores SET nome = ?, email = ?, senha = ?, escola_id = ? WHERE id = ?");
                    $stmt->execute([$nome, $email, $senha, $escola_id, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE professores SET nome = ?, email = ?, escola_id = ? WHERE id = ?");
                    $stmt->execute([$nome, $email, $escola_id, $id]);
                }
                $sucesso = "Professor atualizado com sucesso!";
                $edit_id = 0;
            }
        } catch (PDOException $e) {
            $erro = "Erro ao atualizar professor: " . $e->getMessage();
        }
    } else {
        $erro = "Nome, email e escola são obrigatórios.";
    }
}

// Fetch all registered schools for dropdown
try {
    $escolas = $pdo->query("SELECT id, nome FROM escolas")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $erro = "Erro ao listar escolas: " . $e->getMessage();
    $escolas = [];
}

// Fetch all registered professors
try {
    $stmt = $pdo->query("
        SELECT professores.id, professores.nome, professores.email, escolas.nome AS escola_nome
        FROM professores
        LEFT JOIN escolas ON professores.escola_id = escolas.id
    ");
    $professores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $erro = "Erro ao listar professores: " . $e->getMessage();
    $professores = [];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <title>Cadastro de Professor</title>
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
  <h2><i class="bi bi-person-workspace me-2" style="color: #007bff;"></i>Cadastro de Professor</h2>
  <div class="row">
    <div class="col-md-6">
      <div class="card bg-light shadow mb-4">
        <div class="card-body">
          <h5 class="card-title">Novo Professor</h5>
          <form method="POST" action="">
            <div class="mb-3">
              <label for="nome" class="form-label">Nome:</label>
              <input type="text" name="nome" id="nome" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email:</label>
              <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="senha" class="form-label">Senha:</label>
              <input type="password" name="senha" id="senha" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="escola_id" class="form-label">Escola:</label>
              <select name="escola_id" id="escola_id" class="form-select" required>
                <option value="">Selecione a escola</option>
                <?php foreach ($escolas as $escola): ?>
                  <option value="<?= $escola['id'] ?>"><?= htmlspecialchars($escola['nome']) ?></option>
                <?php endforeach; ?>
              </select>
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
            <h5 class="card-title">Editar Professor</h5>
            <form method="POST" action="">
              <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
              <div class="mb-3">
                <label for="nomeEdit" class="form-label">Nome:</label>
                <input type="text" name="nome" id="nomeEdit" class="form-control" value="<?= htmlspecialchars($edit_nome) ?>" required>
              </div>
              <div class="mb-3">
                <label for="emailEdit" class="form-label">Email:</label>
                <input type="email" name="email" id="emailEdit" class="form-control" value="<?= htmlspecialchars($edit_email) ?>" required>
              </div>
              <div class="mb-3">
                <label for="senhaEdit" class="form-label">Nova Senha (opcional):</label>
                <input type="password" name="senha" id="senhaEdit" class="form-control" placeholder="Deixe em branco para manter a senha atual">
              </div>
              <div class="mb-3">
                <label for="escolaEdit" class="form-label">Escola:</label>
                <select name="escola_id" id="escolaEdit" class="form-select" required>
                  <option value="">Selecione a escola</option>
                  <?php foreach ($escolas as $escola): ?>
                    <option value="<?= $escola['id'] ?>" <?= $escola['id'] == $edit_escola_id ? 'selected' : '' ?>>
                      <?= htmlspecialchars($escola['nome']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <button type="submit" class="btn btn-primary">Salvar</button>
              <a href="cadastro_professor.php" class="btn btn-secondary">Cancelar</a>
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

  <h3 class="mt-4">Professores Cadastrados</h3>
  <div class="card bg-light shadow">
    <div class="card-body">
      <?php if (empty($professores)): ?>
        <p>Nenhum professor cadastrado.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Escola</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($professores as $prof): ?>
                <tr>
                  <td><?= htmlspecialchars($prof['nome']) ?></td>
                  <td><?= htmlspecialchars($prof['email']) ?></td>
                  <td><?= htmlspecialchars($prof['escola_nome'] ?? 'Sem escola') ?></td>
                  <td>
                    <a href="cadastro_professor.php?edit_id=<?= $prof['id'] ?>" class="btn btn-sm btn-warning">
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