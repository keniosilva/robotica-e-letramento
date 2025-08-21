<?php
require 'includes/auth.php';
require 'config/db.php';

if ($_SESSION['tipo'] !== 'professor') {
    header("Location: admin.php");
    exit;
}

$escolas = $pdo->query("SELECT * FROM escolas")->fetchAll();
$turmas = $pdo->query("SELECT * FROM turmas")->fetchAll();
$series = $pdo->query("SELECT * FROM series")->fetchAll();
$turnos = $pdo->query("SELECT * FROM turnos")->fetchAll();

$alunos = [];
$aulasAnteriores = [];

$escolaSelecionada = $_GET['escola_id'] ?? '';
$serieSelecionada = $_GET['serie_id'] ?? '';
$turmaSelecionada = $_GET['turma_id'] ?? '';
$turnoSelecionado = $_GET['turno_id'] ?? '';

if (!empty($escolaSelecionada) && !empty($serieSelecionada) && !empty($turmaSelecionada)) {
    // Carregar alunos - removido filtro turno_id se não houver turno selecionado
    if (!empty($turnoSelecionado)) {
        $stmt = $pdo->prepare("
            SELECT id, nome 
            FROM alunos 
            WHERE escola_id = ? AND serie_id = ? AND turma_id = ? AND turno_id = ?
        ");
        $stmt->execute([$escolaSelecionada, $serieSelecionada, $turmaSelecionada, $turnoSelecionado]);
    } else {
        $stmt = $pdo->prepare("
            SELECT id, nome 
            FROM alunos 
            WHERE escola_id = ? AND serie_id = ? AND turma_id = ?
        ");
        $stmt->execute([$escolaSelecionada, $serieSelecionada, $turmaSelecionada]);
    }
    $alunos = $stmt->fetchAll();

    // Carregar aulas anteriores da turma - CORRIGIDO: removido turno_id da query
    $stmt = $pdo->prepare("
        SELECT id, segmento, conteudo, data
        FROM aulas
        WHERE escola_id = ? AND serie_id = ? AND turma_id = ?
        ORDER BY data DESC
    ");
    $stmt->execute([$escolaSelecionada, $serieSelecionada, $turmaSelecionada]);
    $aulasAnteriores = $stmt->fetchAll();
}

// Separar aulas por segmento
$aulasRobotica = array_filter($aulasAnteriores, fn($aula) => $aula['segmento'] === 'robotica');
$aulasLetramento = array_filter($aulasAnteriores, fn($aula) => $aula['segmento'] === 'letramento_digital');
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Painel do Professor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #e8ecef;
      font-family: 'Arial', sans-serif;
    }
    .container {
      background-color: #ffffff;
      border-radius: 15px;
      padding: 20px;
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
      transform: translateZ(0);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .container:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3);
    }
    .navbar {
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      background: linear-gradient(45deg, #343a40, #495057);
    }
    .form-control, .btn {
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .form-control:focus, .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    }
    .btn-primary {
      background: linear-gradient(45deg, #007bff, #0056b3);
      border: none;
    }
    .btn-success {
      background: linear-gradient(45deg, #28a745, #1e7e34);
      border: none;
    }
    .table {
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .table thead {
      background: linear-gradient(45deg, #6c757d, #495057);
      color: white;
    }
    .table-robotica thead {
      background: linear-gradient(45deg, #007bff, #0056b3);
    }
    .table-letramento thead {
      background: linear-gradient(45deg, #17a2b8, #117a8b);
    }
    h2, h4 {
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
    }
    .alert {
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    footer {
      box-shadow: 0 -4px 8px rgba(0, 0, 0, 0.1);
    }
    .loading-spinner {
      display: none;
      color: #007bff;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Robótica Inclusiva e Letramento Digital</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="professor.php">Registrar Aula</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Sair</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <h2>👨‍🏫 Registrar Aula</h2>

  <?php if (isset($_GET['sucesso'])): ?>
    <div class="alert alert-success">✅ Aula registrada com sucesso!</div>
  <?php endif; ?>

  <!-- Formulário para carregar alunos -->
  <form method="GET" action="professor.php" class="mb-4">
    <div class="row">
      <div class="col-md-4">
        <label>Escola:</label>
        <select name="escola_id" id="escola_select" class="form-control" required>
          <option value="">Selecione</option>
          <?php foreach ($escolas as $escola): ?>
            <option value="<?= $escola['id'] ?>" <?= $escolaSelecionada == $escola['id'] ? 'selected' : '' ?>>
              <?= $escola['nome'] ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label>Série:</label>
        <select name="serie_id" class="form-control" required>
          <option value="">Selecione</option>
          <?php foreach ($series as $serie): ?>
            <option value="<?= $serie['id'] ?>" <?= $serieSelecionada == $serie['id'] ? 'selected' : '' ?>>
              <?= $serie['nome'] ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label>Turma:</label>
        <div class="position-relative">
          <select name="turma_id" id="turma_select" class="form-control" required>
            <option value="">Selecione uma escola primeiro</option>
            <?php if (!empty($escolaSelecionada)): ?>
              <?php foreach ($turmas as $turma): ?>
                <?php if ($turma['escola_id'] == $escolaSelecionada): ?>
                  <option value="<?= $turma['id'] ?>" <?= $turmaSelecionada == $turma['id'] ? 'selected' : '' ?>>
                    <?= $turma['nome'] ?>
                  </option>
                <?php endif; ?>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
          <div class="loading-spinner position-absolute top-50 end-0 translate-middle-y me-3">
            <i class="fas fa-spinner fa-spin"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-md-4">
        <label>Turno (opcional):</label>
        <div class="position-relative">
          <select name="turno_id" id="turno_select" class="form-control">
            <option value="">Selecione (opcional)</option>
            <?php foreach ($turnos as $turno): ?>
              <option value="<?= $turno['id'] ?>" <?= $turnoSelecionado == $turno['id'] ? 'selected' : '' ?>>
                <?= $turno['nome'] ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div class="loading-spinner-turno position-absolute top-50 end-0 translate-middle-y me-3">
            <i class="fas fa-spinner fa-spin"></i>
          </div>
        </div>
      </div>

      <div class="col-md-12 mt-3 text-end">
        <button type="submit" class="btn btn-primary">🔍 Carregar Alunos</button>
      </div>
    </div>
  </form>

  <!-- Exibir Escola e Série selecionadas -->
  <?php if (!empty($escolaSelecionada) && !empty($serieSelecionada) && !empty($turmaSelecionada)): ?>
    <?php
      $stmtEscola = $pdo->prepare("SELECT nome FROM escolas WHERE id = ?");
      $stmtEscola->execute([$escolaSelecionada]);
      $nomeEscola = $stmtEscola->fetchColumn();

      $stmtSerie = $pdo->prepare("SELECT nome FROM series WHERE id = ?");
      $stmtSerie->execute([$serieSelecionada]);
      $nomeSerie = $stmtSerie->fetchColumn();

      $stmtTurma = $pdo->prepare("SELECT nome FROM turmas WHERE id = ?");
      $stmtTurma->execute([$turmaSelecionada]);
      $nomeTurma = $stmtTurma->fetchColumn();

      $nomeTurno = '';
      if (!empty($turnoSelecionado)) {
        $stmtTurno = $pdo->prepare("SELECT nome FROM turnos WHERE id = ?");
        $stmtTurno->execute([$turnoSelecionado]);
        $nomeTurno = $stmtTurno->fetchColumn();
      }
    ?>
    <div class="alert alert-info">
      <strong>Escola:</strong> <?= htmlspecialchars($nomeEscola) ?><br>
      <strong>Série:</strong> <?= htmlspecialchars($nomeSerie) ?><br>
      <strong>Turma:</strong> <?= htmlspecialchars($nomeTurma) ?><br>
      <?php if (!empty($nomeTurno)): ?>
        <strong>Turno:</strong> <?= htmlspecialchars($nomeTurno) ?>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- Lista de aulas anteriores - Robótica -->
  <?php if (!empty($aulasRobotica)): ?>
    <h4>🤖 Aulas Anteriores - Robótica</h4>
    <table class="table table-bordered mb-4 table-robotica">
      <thead>
        <tr>
          <th>Data</th>
          <th>Conteúdo</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($aulasRobotica as $aula): ?>
          <tr>
            <td><?= date('d/m/Y H:i', strtotime($aula['data'])) ?></td>
            <td><?= htmlspecialchars($aula['conteudo']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php elseif (!empty($escolaSelecionada) && !empty($serieSelecionada) && !empty($turmaSelecionada)): ?>
    <p class="text-muted">Nenhuma aula de Robótica registrada para esta turma ainda.</p>
  <?php endif; ?>

  <!-- Lista de aulas anteriores - Letramento Digital -->
  <?php if (!empty($aulasLetramento)): ?>
    <h4>💻 Aulas Anteriores - Letramento Digital</h4>
    <table class="table table-bordered mb-4 table-letramento">
      <thead>
        <tr>
          <th>Data</th>
          <th>Conteúdo</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($aulasLetramento as $aula): ?>
          <tr>
            <td><?= date('d/m/Y H:i', strtotime($aula['data'])) ?></td>
            <td><?= htmlspecialchars($aula['conteudo']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php elseif (!empty($escolaSelecionada) && !empty($serieSelecionada) && !empty($turmaSelecionada)): ?>
    <p class="text-muted">Nenhuma aula de Letramento Digital registrada para esta turma ainda.</p>
  <?php endif; ?>

  <!-- Formulário para registrar aula -->
  <form method="POST" action="registrar_aula.php">
    <input type="hidden" name="escola_id" value="<?= htmlspecialchars($escolaSelecionada) ?>">
    <input type="hidden" name="serie_id" value="<?= htmlspecialchars($serieSelecionada) ?>">
    <input type="hidden" name="turma_id" value="<?= htmlspecialchars($turmaSelecionada) ?>">
    <input type="hidden" name="turno_id" value="<?= htmlspecialchars($turnoSelecionado) ?>">

    <div class="mb-3">
      <label>Segmento:</label>
      <select name="segmento" class="form-control" required>
        <option value="">Selecione o segmento</option>
        <option value="robotica">Robótica</option>
        <option value="letramento_digital">Letramento Digital</option>
      </select>
    </div>

    <div class="mb-3">
      <label>Conteúdo da Aula:</label>
      <textarea name="conteudo" class="form-control" required></textarea>
    </div>

    <?php if (!empty($alunos)): ?>
      <h4>👥 Marcar Presença</h4>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Aluno</th>
            <th>Presente</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($alunos as $aluno): ?>
            <tr>
              <td><?= htmlspecialchars($aluno['nome']) ?></td>
              <td>
                <input type="checkbox" name="presenca[<?= $aluno['id'] ?>]" value="1" checked>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <button type="submit" class="btn btn-success">✅ Registrar Aula</button>
  </form>
</div>

<footer class="bg-light text-center text-muted py-3 mt-5">
  <small>&copy; <?= date('Y') ?> Robótica Escolar</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const escolaSelect = document.getElementById('escola_select');
    const turmaSelect = document.getElementById('turma_select');
    const turnoSelect = document.getElementById('turno_select');
    const loadingSpinner = document.querySelector('.loading-spinner');
    const loadingSpinnerTurno = document.querySelector('.loading-spinner-turno');

    escolaSelect.addEventListener('change', function() {
        const escolaId = this.value;
        
        // Limpar turmas e turnos
        turmaSelect.innerHTML = '<option value="">Selecione uma escola primeiro</option>';
        turnoSelect.innerHTML = '<option value="">Selecione (opcional)</option>';
        
        if (escolaId === '') {
            return;
        }

        // Mostrar loading para turmas
        loadingSpinner.style.display = 'block';
        turmaSelect.disabled = true;

        // Mostrar loading para turnos
        loadingSpinnerTurno.style.display = 'block';
        turnoSelect.disabled = true;

        // Carregar turmas
        fetch(`get_turmas.php?escola_id=${escolaId}`)
            .then(response => response.json())
            .then(data => {
                // Esconder loading
                loadingSpinner.style.display = 'none';
                turmaSelect.disabled = false;

                if (data.error) {
                    turmaSelect.innerHTML = '<option value="">Erro ao carregar turmas</option>';
                    console.error('Erro:', data.error);
                    return;
                }

                // Limpar e popular o select de turmas
                turmaSelect.innerHTML = '<option value="">Selecione uma turma</option>';
                
                if (data.length === 0) {
                    turmaSelect.innerHTML = '<option value="">Nenhuma turma encontrada</option>';
                } else {
                    data.forEach(turma => {
                        const option = document.createElement('option');
                        option.value = turma.id;
                        option.textContent = turma.nome;
                        turmaSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                loadingSpinner.style.display = 'none';
                turmaSelect.disabled = false;
                turmaSelect.innerHTML = '<option value="">Erro ao carregar turmas</option>';
            });

        // Carregar turnos
        fetch(`get_turnos.php?escola_id=${escolaId}`)
            .then(response => response.json())
            .then(data => {
                // Esconder loading
                loadingSpinnerTurno.style.display = 'none';
                turnoSelect.disabled = false;

                if (data.error) {
                    turnoSelect.innerHTML = '<option value="">Erro ao carregar turnos</option>';
                    console.error('Erro:', data.error);
                    return;
                }

                // Limpar e popular o select de turnos
                turnoSelect.innerHTML = '<option value="">Selecione um turno (opcional)</option>';
                
                if (data.length === 0) {
                    turnoSelect.innerHTML = '<option value="">Nenhum turno encontrado</option>';
                } else {
                    data.forEach(turno => {
                        const option = document.createElement('option');
                        option.value = turno.id;
                        option.textContent = turno.nome;
                        turnoSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                loadingSpinnerTurno.style.display = 'none';
                turnoSelect.disabled = false;
                turnoSelect.innerHTML = '<option value="">Erro ao carregar turnos</option>';
            });
    });
});
</script>

</body>
</html>

