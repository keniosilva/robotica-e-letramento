<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Sistema de Frequência</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="admin.php">Painel</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="cadastro_professor.php">Professores</a></li>
        <li class="nav-item"><a class="nav-link" href="cadastro_aluno.php">Alunos</a></li>
        
      </ul>
      <span class="navbar-text text-white me-3">
        <?= $_SESSION['nome'] ?? 'Visitante' ?>
      </span>
      <a class="btn btn-outline-light" href="logout.php">Sair</a>
    </div>
  </div>
</nav>
<div class="container mt-4">