<?php
require 'includes/auth.php';
require 'config/db.php';
require 'vendor/autoload.php'; // Assuming Dompdf is installed via Composer

use Dompdf\Dompdf;
use Dompdf\Options;

if ($_SESSION['tipo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch filter options
$professores = $pdo->query("SELECT id, nome FROM professores WHERE tipo = 'professor' ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$escolas = $pdo->query("SELECT id, nome FROM escolas ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$series = $pdo->query("SELECT id, nome FROM series ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$turnos = $pdo->query("SELECT id, nome FROM turnos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Initialize filter variables
$professor_id = filter_input(INPUT_GET, 'professor_id', FILTER_VALIDATE_INT) ?: '';
$escola_id = filter_input(INPUT_GET, 'escola_id', FILTER_VALIDATE_INT) ?: '';
$turma_id = filter_input(INPUT_GET, 'turma_id', FILTER_VALIDATE_INT) ?: '';
$serie_id = filter_input(INPUT_GET, 'serie_id', FILTER_VALIDATE_INT) ?: '';
$turno_id = filter_input(INPUT_GET, 'turno_id', FILTER_VALIDATE_INT) ?: '';
$aluno_id = filter_input(INPUT_GET, 'aluno_id', FILTER_VALIDATE_INT) ?: '';

// Fetch turmas based on selected escola_id
$turmas = [];
if ($escola_id) {
    $stmt = $pdo->prepare("SELECT id, nome FROM turmas WHERE escola_id = ? ORDER BY nome");
    $stmt->execute([$escola_id]);
    $turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $turmas = $pdo->query("SELECT id, nome FROM turmas ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch alunos based on provided filters
$alunos = [];
$aluno_query = "SELECT id, nome FROM alunos WHERE 1=1";
$aluno_params = [];
if ($escola_id) {
    $aluno_query .= " AND escola_id = ?";
    $aluno_params[] = $escola_id;
}
if ($turma_id) {
    $aluno_query .= " AND turma_id = ?";
    $aluno_params[] = $turma_id;
}
if ($serie_id) {
    $aluno_query .= " AND serie_id = ?";
    $aluno_params[] = $serie_id;
}
if ($turno_id) {
    $aluno_query .= " AND turno_id = ?";
    $aluno_params[] = $turno_id;
}
$aluno_query .= " ORDER BY nome";
$stmt = $pdo->prepare($aluno_query);
$stmt->execute($aluno_params);
$alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch attendance records
$alunosFrequencia = [];
$query = "
    SELECT a.id AS aluno_id, a.nome,
           COUNT(p.id) AS total_registros,
           SUM(p.presente) AS total_presente,
           ROUND((SUM(p.presente) / COUNT(p.id)) * 100, 2) AS frequencia
    FROM alunos a
    JOIN presencas p ON p.aluno_id = a.id
    JOIN aulas au ON au.id = p.aula_id
    WHERE 1=1
";
$params = [];

if ($professor_id) {
    $query .= " AND au.professor_id = ?";
    $params[] = $professor_id;
}
if ($escola_id) {
    $query .= " AND a.escola_id = ? AND au.escola_id = ?";
    $params[] = $escola_id;
    $params[] = $escola_id;
}
if ($turma_id) {
    $query .= " AND a.turma_id = ? AND au.turma_id = ?";
    $params[] = $turma_id;
    $params[] = $turma_id;
}
if ($serie_id) {
    $query .= " AND a.serie_id = ? AND au.serie_id = ?";
    $params[] = $serie_id;
    $params[] = $serie_id;
}
if ($turno_id) {
    $query .= " AND a.turno_id = ?";
    $params[] = $turno_id;
}
if ($aluno_id) {
    $query .= " AND a.id = ?";
    $params[] = $aluno_id;
}

$query .= " GROUP BY a.id, a.nome ORDER BY a.nome";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$alunosFrequencia = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Depuração: verificar se há registros de frequência
$debugMessage = empty($alunosFrequencia) && !empty($params) ? "Nenhum registro de frequência encontrado para os filtros selecionados." : null;

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
        <h2>Relatório de Frequência de Alunos</h2>
        <div class="filters">
            <strong>Filtros Aplicados:</strong> ';

    $filtrosTexto = [];
    if ($professor_id) {
        $stmt = $pdo->prepare("SELECT nome FROM professores WHERE id = ?");
        $stmt->execute([$professor_id]);
        $professor = $stmt->fetch();
        $filtrosTexto[] = "Professor: " . ($professor['nome'] ?? 'Desconhecido');
    }
    if ($escola_id) {
        $stmt = $pdo->prepare("SELECT nome FROM escolas WHERE id = ?");
        $stmt->execute([$escola_id]);
        $escola = $stmt->fetch();
        $filtrosTexto[] = "Escola: " . ($escola['nome'] ?? 'Desconhecida');
    }
    if ($turma_id) {
        $stmt = $pdo->prepare("SELECT nome FROM turmas WHERE id = ?");
        $stmt->execute([$turma_id]);
        $turma = $stmt->fetch();
        $filtrosTexto[] = "Turma: " . ($turma['nome'] ?? 'Desconhecida');
    }
    if ($serie_id) {
        $stmt = $pdo->prepare("SELECT nome FROM series WHERE id = ?");
        $stmt->execute([$serie_id]);
        $serie = $stmt->fetch();
        $filtrosTexto[] = "Série: " . ($serie['nome'] ?? 'Desconhecida');
    }
    if ($turno_id) {
        $stmt = $pdo->prepare("SELECT nome FROM turnos WHERE id = ?");
        $stmt->execute([$turno_id]);
        $turno = $stmt->fetch();
        $filtrosTexto[] = "Turno: " . ($turno['nome'] ?? 'Desconhecido');
    }
    if ($aluno_id) {
        $stmt = $pdo->prepare("SELECT nome FROM alunos WHERE id = ?");
        $stmt->execute([$aluno_id]);
        $aluno = $stmt->fetch();
        $filtrosTexto[] = "Aluno: " . ($aluno['nome'] ?? 'Desconhecido');
    }
    $html .= empty($filtrosTexto) ? "Nenhum filtro aplicado." : implode(", ", $filtrosTexto);
    $html .= '
        </div>
        <table>
            <thead>
                <tr>
                    <th>Aluno</th>
                    <th>Total de Aulas</th>
                    <th>Presenças</th>
                    <th>Frequência (%)</th>
                </tr>
            </thead>
            <tbody>';

    if (empty($alunosFrequencia)) {
        $html .= '<tr><td colspan="4" style="text-align: center;">Nenhum registro de frequência encontrado para os filtros selecionados.</td></tr>';
    } else {
        foreach ($alunosFrequencia as $aluno) {
            $nome = htmlspecialchars($aluno['nome']);
            $total_registros = $aluno['total_registros'];
            $total_presente = $aluno['total_presente'];
            $frequencia = $aluno['frequencia'];
            $html .= "<tr><td>$nome</td><td>$total_registros</td><td>$total_presente</td><td>$frequencia%</td></tr>";
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
    $dompdf->stream("relatorio_frequencia.pdf", ["Attachment" => false]);

    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Frequência</title>
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
    <h2 class="mb-4">📊 Relatório de Frequência de Alunos</h2>

    <?php if ($debugMessage): ?>
        <div class="alert alert-warning"><?= htmlspecialchars($debugMessage) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
    <?php endif; ?>

    <!-- 🔍 Filtro -->
    <form method="GET" action="frequencia.php" class="row g-3 mb-4">
        <div class="col-md-2">
            <label for="professor_id" class="form-label">Professor:</label>
            <select name="professor_id" id="professor_id" class="form-control">
                <option value="">Todos</option>
                <?php foreach ($professores as $professor): ?>
                    <option value="<?= $professor['id'] ?>" <?= $professor_id == $professor['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($professor['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label for="escola_id" class="form-label">Escola:</label>
            <select name="escola_id" id="escola_id" class="form-control">
                <option value="">Todas</option>
                <?php foreach ($escolas as $escola): ?>
                    <option value="<?= $escola['id'] ?>" <?= $escola_id == $escola['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($escola['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label for="serie_id" class="form-label">Série:</label>
            <select name="serie_id" id="serie_id" class="form-control">
                <option value="">Todas</option>
                <?php foreach ($series as $serie): ?>
                    <option value="<?= $serie['id'] ?>" <?= $serie_id == $serie['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($serie['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label for="turma_id" class="form-label">Turma:</label>
            <select name="turma_id" id="turma_id" class="form-control">
                <option value="">Todas</option>
                <?php foreach ($turmas as $turma): ?>
                    <option value="<?= $turma['id'] ?>" <?= $turma_id == $turma['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($turma['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label for="turno_id" class="form-label">Turno:</label>
            <select name="turno_id" id="turno_id" class="form-control">
                <option value="">Todos</option>
                <?php foreach ($turnos as $turno): ?>
                    <option value="<?= $turno['id'] ?>" <?= $turno_id == $turno['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($turno['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label for="aluno_id" class="form-label">Aluno:</label>
            <select name="aluno_id" id="aluno_id" class="form-control">
                <option value="">Todos</option>
                <?php foreach ($alunos as $aluno): ?>
                    <option value="<?= $aluno['id'] ?>" <?= $aluno_id == $aluno['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($aluno['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-12 text-end">
            <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
            <a href="frequencia.php?gerar_pdf=1<?= !empty($professor_id) ? '&professor_id=' . urlencode($professor_id) : '' ?><?= !empty($escola_id) ? '&escola_id=' . urlencode($escola_id) : '' ?><?= !empty($turma_id) ? '&turma_id=' . urlencode($turma_id) : '' ?><?= !empty($serie_id) ? '&serie_id=' . urlencode($serie_id) : '' ?><?= !empty($turno_id) ? '&turno_id=' . urlencode($turno_id) : '' ?><?= !empty($aluno_id) ? '&aluno_id=' . urlencode($aluno_id) : '' ?>" class="btn btn-success">📄 Gerar PDF</a>
        </div>
    </form>

    <!-- 📋 Tabela de frequência -->
    <?php if (!empty($alunosFrequencia)): ?>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Aluno</th>
                    <th>Total de Aulas</th>
                    <th>Presenças</th>
                    <th>Frequência (%)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alunosFrequencia as $aluno): ?>
                    <tr>
                        <td><?= htmlspecialchars($aluno['nome']) ?></td>
                        <td><?= $aluno['total_registros'] ?></td>
                        <td><?= $aluno['total_presente'] ?></td>
                        <td><?= $aluno['frequencia'] ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">Nenhum registro de frequência encontrado para os filtros selecionados.</div>
    <?php endif; ?>
</div>

<footer class="bg-light text-center text-muted py-3 mt-5" style="box-shadow: 0 -4px 8px rgba(0, 0, 0, 0.1);">
    <small>&copy; <?= date('Y') ?> Robótica e Letramento Digital</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>