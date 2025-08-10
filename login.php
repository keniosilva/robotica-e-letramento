<?php
session_start();
require 'config/db.php';

$erro = '';
$sucesso = '';

// LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['acao'] === 'login') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM professores WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nome'] = $usuario['nome'];
        $_SESSION['tipo'] = $usuario['tipo'];

        if ($usuario['tipo'] === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: professor.php");
        }
        exit;
    } else {
        $erro = "Email ou senha inválidos.";
    }
}

// CADASTRO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['acao'] === 'cadastro') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $escola_id = filter_input(INPUT_POST, 'escola_id', FILTER_VALIDATE_INT);
    $tipo = 'professor'; // padrão

    // Verifica se o email já existe
    $verifica = $pdo->prepare("SELECT id FROM professores WHERE email = ?");
    $verifica->execute([$email]);

    if ($verifica->rowCount() > 0) {
        $erro = "Este email já está cadastrado.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO professores (nome, email, senha, escola_id, tipo) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $email, $senha, $escola_id, $tipo]);
        $sucesso = "Cadastro realizado com sucesso! Faça login abaixo.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Robótica Inclusiva</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto Mono', monospace;
            background-image: url('robotica.jpeg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-color: rgba(0, 0, 0, 0); /* Semi-transparent overlay */
            background-blend-mode: overlay;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .card-sm { max-width: 350px; }
        .login-header { text-align: center; padding: 1rem 0; color: #00ffff; font-family: 'Roboto Mono', monospace; }
        .login-body { padding: 1rem; font-family: 'Roboto Mono', monospace; }
        .login-card {
            background: linear-gradient(135deg, rgba(28, 37, 38, 0.9), rgba(46, 59, 62, 0.9));
            border: 1px solid #00ffff;
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.5), 0 0 20px rgba(0, 255, 255, 0.3), 0 4px 8px rgba(0, 0, 0, 0.5);
        }
        .btn-primary, .btn-success {
            border: 1px solid #00ffff;
            transition: all 0.3s ease;
        }
        .btn-primary:hover, .btn-success:hover {
            background: #00ffff;
            color: #1c2526;
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.7);
        }
        .form-label, .alert { color: #e0e0e0; }
        .alert { border: 1px solid #00ffff; }
        footer {
            background: linear-gradient(135deg, #1c2526, #2e3b3e);
            border-top: 1px solid #00ffff;
            box-shadow: 0 -2px 10px rgba(0, 255, 255, 0.3);
            margin-top: auto;
        }
        .title-robotic {
            color: #00ffff;
            font-family: 'Roboto Mono', monospace;
            text-align: center;
            font-size: 2.5rem;
            text-shadow: 0 0 5px rgba(0, 255, 255, 0.8), 0 0 10px rgba(0, 255, 255, 0.5);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { text-shadow: 0 0 5px rgba(0, 255, 255, 0.8), 0 0 10px rgba(0, 255, 255, 0.5); }
            50% { text-shadow: 0 0 10px rgba(0, 255, 255, 1), 0 0 20px rgba(0, 255, 255, 0.7); }
            100% { text-shadow: 0 0 5px rgba(0, 255, 255, 0.8), 0 0 10px rgba(0, 255, 255, 0.5); }
        }
    </style>
</head>
<body>
    <div class="container mt-3">
        <div class="row justify-content-center mb-3">
            <div class="col-auto">
              
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-sm-6 col-md-4">
                <div class="card login-card card-sm">
                    <div class="card-body login-body">
                        <div class="login-header">
                            <h4 class="card-title mb-3">🔐 Login</h4>
                        </div>
                        <form method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="acao" value="login">
                            <div class="mb-2">
                                <label for="email-login" class="form-label small">Email:</label>
                                <input type="email" name="email" id="email-login" class="form-control form-control-sm" required>
                                <div class="invalid-feedback">
                                    Por favor, insira um email válido.
                                </div>
                            </div>
                            <div class="mb-2">
                                <label for="senha-login" class="form-label small">Senha:</label>
                                <div class="input-group input-group-sm">
                                    <input type="password" name="senha" id="senha-login" class="form-control" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleSenha" onclick="togglePasswordVisibility('senha-login', 'eyeIcon')">
                                        <i class="bi bi-eye" id="eyeIcon"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    Por favor, insira sua senha.
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100 mt-2">Entrar</button>
                        </form>
                        <!--
                        <hr class="my-3">
                        <h5 class="text-center mb-3 small">Criar Conta</h5>
                        <form method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="acao" value="cadastro">
                            <div class="mb-2">
                                <label for="nome-cadastro" class="form-label small">Nome:</label>
                                <input type="text" name="nome" id="nome-cadastro" class="form-control form-control-sm" required>
                                <div class="invalid-feedback">
                                    Por favor, insira seu nome.
                                </div>
                            </div>
                            <div class="mb-2">
                                <label for="email-cadastro" class="form-label small">Email:</label>
                                <input type="email" name="email" id="email-cadastro" class="form-control form-control-sm" required>
                                <div class="invalid-feedback">
                                    Por favor, insira um email válido.
                                </div>
                            </div>
                            <div class="mb-2">
                                <label for="senha-cadastro" class="form-label small">Senha:</label>
                                <div class="input-group input-group-sm">
                                    <input type="password" name="senha" id="senha-cadastro" class="form-control" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleSenhaCadastro" onclick="togglePasswordVisibility('senha-cadastro', 'eyeIconCadastro')">
                                        <i class="bi bi-eye" id="eyeIconCadastro"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    Por favor, insira sua senha.
                                </div>
                            </div>
                            <div class="mb-2">
                                <label for="escola_id" class="form-label small">Escola:</label>
                                <select name="escola_id" id="escola_id" class="form-control form-control-sm" required>
                                    <option value="">Selecione</option>
                                    <?php
                                    $escolas = $pdo->query("SELECT * FROM escolas ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($escolas as $escola): ?>
                                        <option value="<?= htmlspecialchars($escola['id']) ?>">
                                            <?= htmlspecialchars($escola['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor, selecione uma escola.
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success btn-sm w-100 mt-2">Cadastrar</button>
                        </form>
                        -->
                    </div>
                </div>
            </div>
        </div>

        <?php if ($erro): ?>
            <div class="row justify-content-center mt-2">
                <div class="col-sm-6 col-md-4">
                    <div class="alert alert-danger alert-sm small"><?= htmlspecialchars($erro) ?></div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="row justify-content-center mt-2">
                <div class="col-sm-6 col-md-4">
                    <div class="alert alert-success alert-sm small"><?= htmlspecialchars($sucesso) ?></div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!--footer class="text-center text-muted py-3">
        <small>&copy; <?= date('Y') ?> Robótica Inclusiva</small>
    </footer>-->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function togglePasswordVisibility(inputId, iconId) {
        const senhaInput = document.getElementById(inputId);
        const eyeIcon = document.getElementById(iconId);
        
        if (senhaInput.type === 'password') {
            senhaInput.type = 'text';
            eyeIcon.className = 'bi bi-eye-slash';
        } else {
            senhaInput.type = 'password';
            eyeIcon.className = 'bi bi-eye';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const emailInput = document.getElementById('email-login');
        const senhaInput = document.getElementById('senha-login');
        
        if (emailInput.value === '') {
            emailInput.focus();
        } else {
            senhaInput.focus();
        }
    });
    </script>
</body>
</html>