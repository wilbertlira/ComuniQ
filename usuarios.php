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

// Busca lista de outros usuários
$usuarios = $conn->query("SELECT id, usuario FROM usuarios WHERE id != $meuId");

// Busca contagem de mensagens não lidas agrupadas por remetente
$sqlNovasMsg = "
  SELECT m.de_id, COUNT(*) AS nao_lidas
  FROM mensagens_privadas m
  LEFT JOIN mensagens_lidas l
    ON l.mensagem_id = m.id AND l.usuario_id = ?
  WHERE m.para_id = ? AND l.id IS NULL
  GROUP BY m.de_id
";
$stmtNovasMsg = $conn->prepare($sqlNovasMsg);
$stmtNovasMsg->bind_param("ii", $meuId, $meuId);
$stmtNovasMsg->execute();
$resNovasMsg = $stmtNovasMsg->get_result();

$mensagensNaoLidas = [];
while ($row = $resNovasMsg->fetch_assoc()) {
    $mensagensNaoLidas[$row['de_id']] = $row['nao_lidas'];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Conversas Privadas</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: #111b21;
      color: #fff;
      display: flex;
      justify-content: center;
    }
    .container {
      width: 100%;
      max-width: 450px;
      height: 100vh;
      background: #111b21;
      display: flex;
      flex-direction: column;
    }
    h2 {
      background: #202c33;
      padding: 15px;
      margin: 0;
      font-size: 1.2rem;
      text-align: center;
      color: #dcf8c6;
      border-bottom: 1px solid #1f2b30;
    }
    ul {
      list-style: none;
      padding: 0;
      margin: 0;
      flex-grow: 1;
      overflow-y: auto;
      background: #0b141a;
    }
    li {
      padding: 15px 20px;
      border-bottom: 1px solid #1f2b30;
      display: flex;
      align-items: center;
      gap: 12px;
      cursor: pointer;
    }
    li:hover {
      background: #2a3942;
    }
    li::before {
      content: "👤";
      font-size: 1.2rem;
    }
    li.nova-mensagem::before {
      content: "📩";
      color: #25d366;
    }
    a {
      text-decoration: none;
      color: #dcf8c6;
      font-weight: 500;
      font-size: 1rem;
      flex-grow: 1;
    }
    a:hover {
      text-decoration: underline;
    }
    .footer {
      text-align: center;
      padding: 15px;
      background: #202c33;
    }
    .footer a {
      color: #25d366;
      font-weight: bold;
      text-decoration: none;
    }
    .footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
<div class="container">
  <h2>💬 Conversas Privadas</h2>

  <ul>
    <?php while ($u = $usuarios->fetch_assoc()): ?>
      <?php
        $classe = isset($mensagensNaoLidas[$u['id']]) ? 'nova-mensagem' : '';
      ?>
      <li class="<?= $classe ?>">
        <a href="chat.php?para=<?= $u['id'] ?>">Conversar com <?= htmlspecialchars($u['usuario']) ?></a>
      </li>
    <?php endwhile; ?>
  </ul>

  <div class="footer">
    <a href="index.php">← Voltar ao painel</a>
  </div>
</div>
</body>
</html>
