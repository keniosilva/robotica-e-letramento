<?php
require 'config/db.php';

header('Content-Type: application/json');

if (!isset($_GET['escola_id']) || empty($_GET['escola_id'])) {
    echo json_encode(['error' => 'ID da escola é obrigatório']);
    exit;
}

$escola_id = $_GET['escola_id'];

try {
    // Buscar turmas relacionadas à escola específica
    $stmt = $pdo->prepare("SELECT id, nome FROM turmas WHERE escola_id = ? ORDER BY nome");
    $stmt->execute([$escola_id]);
    $turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($turmas);
} catch (Exception $e) {
    echo json_encode(['error' => 'Erro ao buscar turmas: ' . $e->getMessage()]);
}
?>

