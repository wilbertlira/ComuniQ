<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// Busca id do usuário atual
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $_SESSION['usuario']);
$stmt->execute();
$res = $stmt->get_result();
$me = $res->fetch_assoc();
$meuId = $me['id'];

// ID do usuário para quem vai enviar mensagem / conversa
$paraId = intval($_GET['para'] ?? 0);
if ($paraId === 0) {
    die("Usuário não especificado.");
}

// Marcar mensagens como lidas
$sqlNaoLidas = "
  SELECT id FROM mensagens_privadas
  WHERE de_id = ? AND para_id = ? AND id NOT IN (
    SELECT mensagem_id FROM mensagens_lidas WHERE usuario_id = ?
  )
";
$stmtNaoLidas = $conn->prepare($sqlNaoLidas);
$stmtNaoLidas->bind_param("iii", $paraId, $meuId, $meuId);
$stmtNaoLidas->execute();
$resNaoLidas = $stmtNaoLidas->get_result();

while ($msg = $resNaoLidas->fetch_assoc()) {
    $stmtInserir = $conn->prepare("INSERT IGNORE INTO mensagens_lidas (mensagem_id, usuario_id) VALUES (?, ?)");
    $stmtInserir->bind_param("ii", $msg['id'], $meuId);
    $stmtInserir->execute();
}

// Enviar mensagem (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensagem = trim($_POST['mensagem']);
    if ($mensagem !== '') {
        $stmtEnviar = $conn->prepare("INSERT INTO mensagens_privadas (de_id, para_id, mensagem) VALUES (?, ?, ?)");
        $stmtEnviar->bind_param("iis", $meuId, $paraId, $mensagem);
        $stmtEnviar->execute();
        // Redirecionar para evitar reenvio no refresh
        header("Location: chat.php?para=$paraId");
        exit;
    }
}

// Busca nome do usuário com quem conversa
$stmtNome = $conn->prepare("SELECT usuario FROM usuarios WHERE id = ?");
$stmtNome->bind_param("i", $paraId);
$stmtNome->execute();
$resNome = $stmtNome->get_result();
$nomePara = $resNome->fetch_assoc()['usuario'] ?? 'Usuário Desconhecido';

// Busca mensagens da conversa (ordem ascendente)
$stmtMsg = $conn->prepare("
  SELECT m.*, u.usuario FROM mensagens_privadas m
  JOIN usuarios u ON m.de_id = u.id
  WHERE (m.de_id = ? AND m.para_id = ?) OR (m.de_id = ? AND m.para_id = ?)
  ORDER BY m.data ASC
");
$stmtMsg->bind_param("iiii", $meuId, $paraId, $paraId, $meuId);
$stmtMsg->execute();
$mensagens = $stmtMsg->get_result();

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Chat com <?= htmlspecialchars($nomePara) ?></title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #111b21;
      color: #dcf8c6;
      display: flex;
      justify-content: center;
    }
    .container {
      width: 100%;
      max-width: 450px;
      height: 100vh;
      display: flex;
      flex-direction: column;
      background: #111b21;
    }
    header {
      background: #202c33;
      padding: 15px;
      font-weight: bold;
      color: #dcf8c6;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .chat-box {
      flex-grow: 1;
      padding: 15px;
      overflow-y: auto;
      background: #0b141a;
    }
    .mensagem {
      margin-bottom: 10px;
      padding: 10px 15px;
      border-radius: 10px;
      max-width: 70%;
      word-wrap: break-word;
    }
    .mensagem.remetente {
      background: #25d366;
      color: #000;
      margin-left: auto;
      text-align: right;
    }
    .mensagem.destinatario {
      background: #2a3942;
      text-align: left;
    }
    form {
      background: #202c33;
      padding: 10px;
      display: flex;
      gap: 10px;
    }
    textarea {
      flex-grow: 1;
      resize: none;
      border-radius: 10px;
      border: none;
      padding: 10px;
      font-family: 'Segoe UI', sans-serif;
      font-size: 1rem;
      background: #292929;
      color: #fff;
    }
    button {
      background: #25d366;
      border: none;
      border-radius: 10px;
      color: #000;
      font-weight: bold;
      font-size: 1rem;
      padding: 0 15px;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    button:hover {
      background: #128c34;
    }
  </style>
</head>
<body>
<div class="container">
  <header>
    <div>Chat com <?= htmlspecialchars($nomePara) ?></div>
    <a href="usuarios.php" style="color:#25d366; text-decoration:none;">← Voltar</a>
  </header>

  <div class="chat-box" id="chat">
    <?php while ($m = $mensagens->fetch_assoc()): ?>
      <div class="mensagem <?= $m['de_id'] === $meuId ? 'remetente' : 'destinatario' ?>">
        <?= nl2br(htmlspecialchars($m['mensagem'])) ?>
        <br>
        <small style="font-size: 0.7rem; color: #999;"><?= $m['data'] ?></small>
      </div>
    <?php endwhile; ?>
  </div>

  <form method="post">
    <textarea name="mensagem" rows="3" placeholder="Digite sua mensagem..." required></textarea>
    <button type="submit">Enviar</button>
  </form>
</div>

<script>
  // Scroll automático para o final do chat
  const chat = document.getElementById('chat');
  chat.scrollTop = chat.scrollHeight;
</script>
</body>
</html>
