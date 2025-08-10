<?php
require 'includes/auth.php';
require 'config/db.php';
require 'vendor/autoload.php'; // Assuming Dompdf is installed via Composer

use Dompdf\Dompdf;
use Dompdf\Options;

// Inicializa variáveis de filtro
$filtros = [];
$params = [];

// Monta a cláusula WHERE dinamicamente
if (!empty($_GET['professor_id'])) {
    $filtros[] = 'aulas.professor_id = ?';
    $params[] = $_GET['professor_id'];
}
if (!empty($_GET['escola_id'])) {
    $filtros[] = 'aulas.escola_id = ?';
    $params[] = $_GET['escola_id'];
}
if (!empty($_GET['turma_id'])) {
    $filtros[] = 'aulas.turma_id = ?';
    $params[] = $_GET['turma_id'];
}
if (!empty($_GET['data_inicio'])) {
    $filtros[] = 'aulas.data >= ?';
    $params[] = $_GET['data_inicio'] . ' 00:00:00';
}
if (!empty($_GET['data_fim'])) {
    $filtros[] = 'aulas.data <= ?';
    $params[] = $_GET['data_fim'] . ' 23:59:59';
}
if (!empty($_GET['segmento'])) {
    $filtros[] = 'aulas.segmento = ?';
    $params[] = $_GET['segmento'];
}

$where = $filtros ? 'WHERE ' . implode(' AND ', $filtros) : '';

$stmt = $pdo->prepare("
    SELECT aulas.*, 
           turmas.nome AS turma_nome, 
           escolas.nome AS escola_nome, 
           professores.nome AS professor_nome
    FROM aulas
    JOIN turmas ON aulas.turma_id = turmas.id
    JOIN escolas ON aulas.escola_id = escolas.id
    JOIN professores ON aulas.professor_id = professores.id
    $where
    ORDER BY aulas.data DESC
");
$stmt->execute($params);
$aulas = $stmt->fetchAll();

// Depuração: verificar se as aulas estão sendo carregadas
$debugMessage = empty($aulas) && !empty($filtros) ? "Nenhuma aula encontrada com os filtros selecionados." : null;

// Geração do PDF com Dompdf
if (isset($_GET['gerar_pdf']) && $_GET['gerar_pdf'] == 1) {
    // Configurar opções do Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);

    // Construir o conteúdo HTML para o PDF
    $html = '
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; }
            h1 { text-align: center; font-size: 16px; }
            h2 { font-size: 14px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #000; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .filters { margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <h1>PREFEITURA MUNICIPAL DE BAYEUX<br>SECRETARIA MUNICIPAL DE EDUCAÇÃO<br>COORDENAÇÃO DE INFORMÁTICA EDUCACIONAL<br>ROBÓTICA INCLUSIVA</h1>
        <h2>Relatório de Aulas</h2>
        <div class="filters">
            <strong>Filtros Aplicados:</strong> ';

    $filtrosTexto = [];
    if (!empty($_GET['professor_id'])) {
        $stmt = $pdo->prepare("SELECT nome FROM professores WHERE id = ?");
        $stmt->execute([$_GET['professor_id']]);
        $professor = $stmt->fetch();
        $filtrosTexto[] = "Professor: " . ($professor['nome'] ?? 'Desconhecido');
    }
    if (!empty($_GET['escola_id'])) {
        $stmt = $pdo->prepare("SELECT nome FROM escolas WHERE id = ?");
        $stmt->execute([$_GET['escola_id']]);
        $escola = $stmt->fetch();
        $filtrosTexto[] = "Escola: " . ($escola['nome'] ?? 'Desconhecida');
    }
    if (!empty($_GET['turma_id'])) {
        $stmt = $pdo->prepare("SELECT nome FROM turmas WHERE id = ?");
        $stmt->execute([$_GET['turma_id']]);
        $turma = $stmt->fetch();
        $filtrosTexto[] = "Turma: " . ($turma['nome'] ?? 'Desconhecida');
    }
    if (!empty($_GET['data_inicio'])) {
        $filtrosTexto[] = "Data Inicial: " . date('d/m/Y', strtotime($_GET['data_inicio']));
    }
    if (!empty($_GET['data_fim'])) {
        $filtrosTexto[] = "Data Final: " . date('d/m/Y', strtotime($_GET['data_fim']));
    }
    if (!empty($_GET['segmento'])) {
        $filtrosTexto[] = "Segmento: " . ($_GET['segmento'] === 'robotica' ? 'Robótica' : 'Letramento Digital');
    }
    $html .= empty($filtrosTexto) ? "Nenhum filtro aplicado." : implode(", ", $filtrosTexto);
    $html .= '
        </div>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Segmento</th>
                    <th>Professor</th>
                    <th>Escola</th>
                    <th>Turma</th>
                    <th>Conteúdo</th>
                </tr>
            </thead>
            <tbody>';

    if (empty($aulas)) {
        $html .= '<tr><td colspan="6" style="text-align: center;">Nenhuma aula encontrada com os filtros selecionados.</td></tr>';
    } else {
        foreach ($aulas as $aula) {
            $data = date('d/m/Y H:i', strtotime($aula['data']));
            $segmento = $aula['segmento'] === 'robotica' ? 'Robótica' : 'Letramento Digital';
            $professor = htmlspecialchars($aula['professor_nome']);
            $escola = htmlspecialchars($aula['escola_nome']);
            $turma = htmlspecialchars($aula['turma_nome']);
            $conteudo = htmlspecialchars($aula['conteudo']);
            $html .= "<tr><td>$data</td><td>$segmento</td><td>$professor</td><td>$escola</td><td>$turma</td><td>$conteudo</td></tr>";
        }
    }

    $html .= '
            </tbody>
        </table>
    </body>
    </html>';

    // Carregar o HTML no Dompdf
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    // Enviar o PDF para o navegador
    $dompdf->stream("relatorio_aulas.pdf", ["Attachment" => false]);

    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Relatório de Aulas</title>
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
    h2 {
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
    }
    .alert {
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    footer {
      box-shadow: 0 -4px 8px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>
<body>

<?php require 'includes/header.php'; ?>

<div class="container mt-4">
  <h2 class="mb-4">📚 Aulas Registradas</h2>

  <?php if ($debugMessage): ?>
    <div class="alert alert-warning"><?= htmlspecialchars($debugMessage) ?></div>
  <?php endif; ?>
  <?php if (isset($_GET['erro'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
  <?php endif; ?>

  <!-- 🔍 Filtro -->
  <form method="GET" class="row g-3 mb-4">
    <div class="col-md-3">
      <label>Professor:</label>
      <select name="professor_id" class="form-select">
        <option value="">Todos</option>
        <?php
        $professores = $pdo->query("SELECT id, nome FROM professores")->fetchAll();
        foreach ($professores as $prof) {
            $selected = ($_GET['professor_id'] ?? '') == $prof['id'] ? 'selected' : '';
            echo "<option value='{$prof['id']}' $selected>{$prof['nome']}</option>";
        }
        ?>
      </select>
    </div>

    <div class="col-md-3">
      <label>Escola:</label>
      <select name="escola_id" class="form-select">
        <option value="">Todas</option>
        <?php
        $escolas = $pdo->query("SELECT id, nome FROM escolas")->fetchAll();
        foreach ($escolas as $escola) {
            $selected = ($_GET['escola_id'] ?? '') == $escola['id'] ? 'selected' : '';
            echo "<option value='{$escola['id']}' $selected>{$escola['nome']}</option>";
        }
        ?>
      </select>
    </div>

    <div class="col-md-3">
      <label>Turma:</label>
      <select name="turma_id" class="form-select">
        <option value="">Todas</option>
        <?php
        $turmas = $pdo->query("SELECT id, nome FROM turmas")->fetchAll();
        foreach ($turmas as $turma) {
            $selected = ($_GET['turma_id'] ?? '') == $turma['id'] ? 'selected' : '';
            echo "<option value='{$turma['id']}' $selected>{$turma['nome']}</option>";
        }
        ?>
      </select>
    </div>

    <div class="col-md-3">
      <label>Segmento:</label>
      <select name="segmento" class="form-select">
        <option value="">Todos</option>
        <option value="robotica" <?= ($_GET['segmento'] ?? '') == 'robotica' ? 'selected' : '' ?>>Robótica</option>
        <option value="letramento_digital" <?= ($_GET['segmento'] ?? '') == 'letramento_digital' ? 'selected' : '' ?>>Letramento Digital</option>
      </select>
    </div>

    <div class="col-md-3">
      <label>Data Inicial:</label>
      <input type="date" name="data_inicio" class="form-control" value="<?= htmlspecialchars($_GET['data_inicio'] ?? '') ?>">
    </div>

    <div class="col-md-3">
      <label>Data Final:</label>
      <input type="date" name="data_fim" class="form-control" value="<?= htmlspecialchars($_GET['data_fim'] ?? '') ?>">
    </div>

    <div class="col-md-3 align-self-end">
      <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
    </div>
  </form>

  <!-- 📋 Tabela de aulas -->
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>Data</th>
        <th>Segmento</th>
        <th>Professor</th>
        <th>Escola</th>
        <th>Turma</th>
        <th>Conteúdo</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($aulas)): ?>
        <tr>
          <td colspan="6" class="text-center">Nenhuma aula encontrada com os filtros selecionados.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($aulas as $aula): ?>
          <tr>
            <td><?= date('d/m/Y H:i', strtotime($aula['data'])) ?></td>
            <td><?= $aula['segmento'] === 'robotica' ? 'Robótica' : 'Letramento Digital' ?></td>
            <td><?= htmlspecialchars($aula['professor_nome']) ?></td>
            <td><?= htmlspecialchars($aula['escola_nome']) ?></td>
            <td><?= htmlspecialchars($aula['turma_nome']) ?></td>
            <td><?= htmlspecialchars($aula['conteudo']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- Botão para gerar PDF com os filtros atuais -->
  <a href="relatorio.php?gerar_pdf=1<?= !empty($_GET['professor_id']) ? '&professor_id=' . urlencode($_GET['professor_id']) : '' ?><?= !empty($_GET['escola_id']) ? '&escola_id=' . urlencode($_GET['escola_id']) : '' ?><?= !empty($_GET['turma_id']) ? '&turma_id=' . urlencode($_GET['turma_id']) : '' ?><?= !empty($_GET['data_inicio']) ? '&data_inicio=' . urlencode($_GET['data_inicio']) : '' ?><?= !empty($_GET['data_fim']) ? '&data_fim=' . urlencode($_GET['data_fim']) : '' ?><?= !empty($_GET['segmento']) ? '&segmento=' . urlencode($_GET['segmento']) : '' ?>" class="btn btn-success mt-3">📄 Gerar PDF</a>
</div>

<?php require 'includes/footer.php'; ?>
</body>
</html>