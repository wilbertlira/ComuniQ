<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// Verifica se é admin
$stmtUser = $conn->prepare("SELECT tipo FROM usuarios WHERE usuario = ?");
$stmtUser->bind_param("s", $_SESSION['usuario']);
$stmtUser->execute();
$resUser = $stmtUser->get_result();
if ($resUser->num_rows === 0) {
    header("Location: logout.php");
    exit;
}
$user = $resUser->fetch_assoc();
if ($user['tipo'] !== 'admin') {
    die("Acesso negado.");
}

$id_topico = intval($_POST['id_topico'] ?? 0);
$acao = $_POST['acao'] ?? '';

if ($id_topico <= 0) {
    die("Tópico inválido.");
}

switch ($acao) {
    case 'toggle_trancar':
        // Pega status atual
        $stmt = $conn->prepare("SELECT trancado FROM topicos WHERE id = ?");
        $stmt->bind_param("i", $id_topico);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            die("Tópico não encontrado.");
        }
        $topico = $res->fetch_assoc();
        $novoStatus = $topico['trancado'] ? 0 : 1;

        $stmt = $conn->prepare("UPDATE topicos SET trancado = ? WHERE id = ?");
        $stmt->bind_param("ii", $novoStatus, $id_topico);
        $stmt->execute();
        break;

    case 'fixar':
        $msg_fixada = trim($_POST['mensagem_fixada'] ?? '');
        if ($msg_fixada === '') {
            die("Mensagem para fixar não pode ser vazia.");
        }
        $stmt = $conn->prepare("UPDATE topicos SET mensagem_fixada = ? WHERE id = ?");
        $stmt->bind_param("si", $msg_fixada, $id_topico);
        $stmt->execute();
        break;

    case 'remover_fixar':
        $stmt = $conn->prepare("UPDATE topicos SET mensagem_fixada = NULL WHERE id = ?");
        $stmt->bind_param("i", $id_topico);
        $stmt->execute();
        break;

    default:
        die("Ação inválida.");
}

header("Location: topico.php?id=$id_topico");
exit;
