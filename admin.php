<?php
require 'includes/auth.php';
require 'config/db.php';
require 'includes/header.php';

if ($_SESSION['tipo'] !== 'admin') {
    header("Location: professor.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <title>Painel do Administrador</title>
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
  <h2>👨‍💼 Painel do Administrador</h2>
  <div class="row row-cols-1 row-cols-md-3 g-4">
    <div class="col">
      <div class="card h-100 text-center bg-light shadow">
        <div class="card-body">
          <i class="bi bi-building-fill" style="font-size: 2rem; color: #007bff;"></i>
          <h5 class="card-title mt-3">Cadastrar Escola</h5>
          <a href="cadastro_escola.php" class="btn btn-primary">Acessar</a>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card h-100 text-center bg-light shadow">
        <div class="card-body">
          <i class="bi bi-book-fill" style="font-size: 2rem; color: #007bff;"></i>
          <h5 class="card-title mt-3">Cadastrar Série</h5>
          <a href="cadastrar_serie.php" class="btn btn-primary">Acessar</a>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card h-100 text-center bg-light shadow">
        <div class="card-body">
          <i class="bi bi-people-fill" style="font-size: 2rem; color: #007bff;"></i>
          <h5 class="card-title mt-3">Cadastrar Turma</h5>
          <a href="cadastro_turma.php" class="btn btn-primary">Acessar</a>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card h-100 text-center bg-light shadow">
        <div class="card-body">
          <i class="bi bi-person-workspace" style="font-size: 2rem; color: #007bff;"></i>
          <h5 class="card-title mt-3">Cadastrar Professor</h5>
          <a href="cadastro_professor.php" class="btn btn-primary">Acessar</a>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card h-100 text-center bg-light shadow">
        <div class="card-body">
          <i class="bi bi-person-fill" style="font-size: 2rem; color: #007bff;"></i>
          <h5 class="card-title mt-3">Cadastrar Aluno</h5>
          <a href="cadastro_aluno.php" class="btn btn-primary">Acessar</a>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card h-100 text-center bg-light shadow">
        <div class="card-body">
          <i class="bi bi-calendar-check-fill" style="font-size: 2rem; color: #007bff;"></i>
          <h5 class="card-title mt-3">Ver Aulas Registradas</h5>
          <a href="dashboard.php" class="btn btn-primary">Acessar</a>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card h-100 text-center bg-light shadow">
        <div class="card-body">
          <i class="bi bi-check-square-fill" style="font-size: 2rem; color: #007bff;"></i>
          <h5 class="card-title mt-3">Frequências</h5>
          <a href="frequencia.php" class="btn btn-primary">Acessar</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
</body>
</html>