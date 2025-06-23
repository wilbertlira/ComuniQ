<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['usuario'])) {
    exit;
}

$user = $_SESSION['usuario'];

// Buscar id do usuário
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$res = $stmt->get_result();
$userData = $res->fetch_assoc();
$userId = $userData['id'];

$topicoId = intval($_GET['topico_id'] ?? 0);
if ($topicoId <= 0) {
    exit;
}

// Verifica se o usuário pode ver o tópico
$stmt = $conn->prepare("SELECT visibilidade FROM topicos WHERE id = ?");
$stmt->bind_param("i", $topicoId);
$stmt->execute();
$resTopico = $stmt->get_result();
if ($resTopico->num_rows === 0) {
    exit;
}
$topico = $resTopico->fetch_assoc();
$visibilidade = $topico['visibilidade'];
if (
    $visibilidade !== 'publico' &&
    !in_array($userId, array_map('intval', explode(',', $visibilidade)))
) {
    exit;
}

// Buscar mensagens
$stmt = $conn->prepare("SELECT m.*, u.usuario FROM mensagens m JOIN usuarios u ON m.id_usuario = u.id WHERE m.id_topico = ? ORDER BY m.data ASC");
$stmt->bind_param("i", $topicoId);
$stmt->execute();
$resMsgs = $stmt->get_result();

$mensagens = [];
while ($msg = $resMsgs->fetch_assoc()) {
    $mensagens[] = $msg;
}

header('Content-Type: application/json');
echo json_encode($mensagens);
