<?php
require 'vendor/autoload.php';
require 'config/db.php';

use Dompdf\Dompdf;

$stmt = $pdo->query("SELECT aulas.*, turmas.nome AS turma_nome FROM aulas JOIN turmas ON aulas.turma_id = turmas.id ORDER BY data DESC");
$aulas = $stmt->fetchAll();

$html = "<h1>Relatório de Aulas</h1><table border='1'><tr><th>Data</th><th>Turma</th><th>Conteúdo</th></tr>";
foreach ($aulas as $aula) {
    $html .= "<tr><td>{$aula['data']}</td><td>{$aula['turma_nome']}</td><td>{$aula['conteudo']}</td></tr>";
}
$html .= "</table>";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("relatorio_aulas.pdf");