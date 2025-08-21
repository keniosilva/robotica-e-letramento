<?php
require 'config/db.php';

header('Content-Type: application/json');

if (!isset($_GET['escola_id']) || empty($_GET['escola_id'])) {
    echo json_encode(['error' => 'ID da escola é obrigatório']);
    exit;
}

$escola_id = $_GET['escola_id'];

try {
    // Buscar turnos relacionados à escola (assumindo que existe uma relação)
    // Se não houver relação direta, buscar todos os turnos
    $stmt = $pdo->prepare("SELECT id, nome FROM turnos ORDER BY nome");
    $stmt->execute();
    $turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($turnos);
} catch (Exception $e) {
    echo json_encode(['error' => 'Erro ao buscar turnos: ' . $e->getMessage()]);
}
?>

