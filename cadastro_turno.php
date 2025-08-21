<?php
require 'includes/auth.php';
require 'config/db.php';

if ($_SESSION['tipo'] !== 'admin') {
    header("Location: professor.php");
    exit;
}

$mensagem = '';
$tipo_mensagem = '';

// Verificar se há mensagem na URL (vinda de redirecionamento)
if (isset($_GET['msg']) && isset($_GET['tipo'])) {
    $mensagem = $_GET['msg'];
    $tipo_mensagem = $_GET['tipo'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    
    if (empty($nome)) {
        $mensagem = 'O nome do turno é obrigatório.';
        $tipo_mensagem = 'danger';
    } else {
        try {
            // Verificar se o turno já existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM turnos WHERE nome = ?");
            $stmt->execute([$nome]);
            
            if ($stmt->fetchColumn() > 0) {
                $mensagem = 'Já existe um turno com este nome.';
                $tipo_mensagem = 'warning';
            } else {
                // Inserir o turno
                $stmt = $pdo->prepare("INSERT INTO turnos (nome) VALUES (?)");
                $stmt->execute([$nome]);
                
                $mensagem = 'Turno cadastrado com sucesso!';
                $tipo_mensagem = 'success';
                
                // Limpar o campo após sucesso
                $_POST['nome'] = '';
            }
        } catch (Exception $e) {
            $mensagem = 'Erro ao cadastrar turno: ' . $e->getMessage();
            $tipo_mensagem = 'danger';
        }
    }
}

// Buscar turnos existentes
try {
    $stmt = $pdo->query("SELECT * FROM turnos ORDER BY nome");
    $turnos = $stmt->fetchAll();
} catch (Exception $e) {
    $turnos = [];
    if (empty($mensagem)) {
        $mensagem = 'Erro ao carregar turnos: ' . $e->getMessage();
        $tipo_mensagem = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <title>Cadastrar Turno</title>
    <style>
        body {
            background-color: #e9ecef;
        }
        .card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Robótica Inclusiva e Letramento Digital</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="admin.php">Painel Admin</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Sair</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-clock-fill"></i> Cadastrar Turno</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($mensagem)): ?>
                        <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($mensagem) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome do Turno:</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="nome" 
                                   name="nome" 
                                   value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"
                                   placeholder="Ex: Manhã, Tarde, Noite, Integral"
                                   required>
                            <div class="form-text">Digite o nome do turno (ex: Manhã, Tarde, Noite, Integral)</div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="admin.php" class="btn btn-secondary me-md-2">
                                <i class="bi bi-arrow-left"></i> Voltar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Cadastrar Turno
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (!empty($turnos)): ?>
                <div class="card shadow mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-list"></i> Turnos Cadastrados</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($turnos as $turno): ?>
                                        <tr>
                                            <td><?= $turno['id'] ?></td>
                                            <td><?= htmlspecialchars($turno['nome']) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="confirmarExclusao(<?= $turno['id'] ?>, '<?= htmlspecialchars($turno['nome']) ?>')">
                                                    <i class="bi bi-trash"></i> Excluir
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de confirmação de exclusão -->
<div class="modal fade" id="modalExclusao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o turno <strong id="nomeTurnoExclusao"></strong>?</p>
                <p class="text-danger"><small>Esta ação não pode ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="excluir_turno.php" style="display: inline;">
                    <input type="hidden" id="idTurnoExclusao" name="id">
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function confirmarExclusao(id, nome) {
    document.getElementById('idTurnoExclusao').value = id;
    document.getElementById('nomeTurnoExclusao').textContent = nome;
    
    const modal = new bootstrap.Modal(document.getElementById('modalExclusao'));
    modal.show();
}
</script>

</body>
</html>

