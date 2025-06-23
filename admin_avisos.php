<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}

// Verifica se é admin
$stmt = $conn->prepare("SELECT tipo FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $_SESSION['usuario']);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
if ($user['tipo'] !== 'admin') {
  die("Acesso negado.");
}

// ID do remetente fixo
$equipeId = 8; // <-- Coloque aqui o ID do usuário "Equipe de Desenvolvimento"

$mensagem = '';
$sucesso = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $mensagem = trim($_POST['mensagem']);
  if ($mensagem === '') {
    $erro = "Mensagem não pode estar vazia.";
  } else {
    // Buscar todos os usuários exceto a própria equipe
    $usuarios = $conn->query("SELECT id FROM usuarios WHERE id != $equipeId");
    while ($row = $usuarios->fetch_assoc()) {
      $stmt = $conn->prepare("INSERT INTO mensagens_privadas (de_id, para_id, mensagem) VALUES (?, ?, ?)");
      $stmt->bind_param("iis", $equipeId, $row['id'], $mensagem);
      $stmt->execute();
    }
    $sucesso = "Aviso enviado com sucesso para todos os usuários!";
  }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Enviar Aviso Global</title>
  <style>
    body {
      background: #111;
      color: #eee;
      font-family: Arial, sans-serif;
      display: flex;
      justify-content: center;
      padding: 40px;
    }

    .box {
      max-width: 500px;
      width: 100%;
      background: #1e1e1e;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px #000;
    }

    textarea {
      width: 100%;
      height: 120px;
      resize: none;
      padding: 10px;
      font-size: 1rem;
      background: #292929;
      color: #eee;
      border: 1px solid #444;
      border-radius: 6px;
    }

    button {
      margin-top: 15px;
      padding: 12px 20px;
      background: #4ecdc4;
      border: none;
      color: #111;
      font-weight: bold;
      border-radius: 6px;
      cursor: pointer;
    }

    .sucesso {
      color: #2ecc71;
      margin-top: 10px;
      font-weight: bold;
    }

    .erro {
      color: #e74c3c;
      margin-top: 10px;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="box">
    <h2>Enviar Aviso Global</h2>
    <form method="post">
      <textarea name="mensagem" placeholder="Digite a mensagem de aviso..." required><?= htmlspecialchars($mensagem) ?></textarea>
      <button type="submit">Enviar aviso</button>
    </form>

    <?php if (!empty($sucesso)): ?>
      <div class="sucesso"><?= $sucesso ?></div>
    <?php endif; ?>
    <?php if (!empty($erro)): ?>
      <div class="erro"><?= $erro ?></div>
    <?php endif; ?>
  </div>
</body>
</html>
