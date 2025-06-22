<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// Buscar dados do usuário
$stmtUser = $conn->prepare("SELECT id, tipo FROM usuarios WHERE usuario = ?");
$stmtUser->bind_param("s", $_SESSION['usuario']);
$stmtUser->execute();
$resUser = $stmtUser->get_result();
if ($resUser->num_rows === 0) {
    header("Location: logout.php");
    exit;
}
$user = $resUser->fetch_assoc();
$userId = $user['id'];
$isAdmin = $user['tipo'] === 'admin';

// Pega o ID do tópico
$topicoId = intval($_GET['id'] ?? 0);
if ($topicoId <= 0) {
    die("Tópico inválido.");
}

// Busca o tópico e verifica visibilidade (como antes)
$stmt = $conn->prepare("SELECT * FROM topicos WHERE id = ?");
$stmt->bind_param("i", $topicoId);
$stmt->execute();
$resTopico = $stmt->get_result();
if ($resTopico->num_rows === 0) {
    die("Tópico não encontrado.");
}
$topico = $resTopico->fetch_assoc();

// Verifica se o usuário tem permissão de ver o tópico
// visibilidade = publico OR userId in visibilidade
$visibilidade = $topico['visibilidade'];
if (
    $visibilidade !== 'publico' &&
    !in_array($userId, array_map('intval', explode(',', $visibilidade)))
) {
    die("Você não tem permissão para ver este tópico.");
}

$trancado = (bool)$topico['trancado'];
$mensagemFixada = $topico['mensagem_fixada'];

// Processar envio de mensagem
$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$trancado) {
    $msg = trim($_POST['mensagem'] ?? '');
    if ($msg === '') {
        $erro = "Mensagem vazia não pode ser enviada.";
    } else {
        $stmtInsert = $conn->prepare("INSERT INTO mensagens (id_topico, id_usuario, mensagem, data) VALUES (?, ?, ?, NOW())");
        $stmtInsert->bind_param("iis", $topicoId, $userId, $msg);
        if ($stmtInsert->execute()) {
            $sucesso = "Mensagem enviada!";
        } else {
            $erro = "Erro ao enviar mensagem.";
        }
        $stmtInsert->close();
    }
}

// Pegar mensagens para exibir
$stmtMsgs = $conn->prepare("SELECT m.*, u.usuario FROM mensagens m JOIN usuarios u ON m.id_usuario = u.id WHERE m.id_topico = ? ORDER BY m.data ASC");
$stmtMsgs->bind_param("i", $topicoId);
$stmtMsgs->execute();
$resMsgs = $stmtMsgs->get_result();

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($topico['nome']) ?> - Fórum</title>
  <link rel="stylesheet" href="assets/style.css" />
  <style>
    body {
      background-color: #121212;
      color: #eee;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 20px;
      min-height: 100vh;
      display: flex;
      justify-content: center;
    }
    .container {
      max-width: 700px;
      width: 100%;
      background-color: #1f1f1f;
      padding: 30px 40px;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.85);
      display: flex;
      flex-direction: column;
    }
    h2 {
      color: #4ecdc4;
      margin-bottom: 15px;
    }
    .mensagem-fixada {
      background: #383838;
      border-left: 6px solid #4ecdc4;
      padding: 15px 20px;
      margin-bottom: 20px;
      font-style: italic;
      user-select: none;
    }
    .mensagens {
      flex-grow: 1;
      max-height: 400px;
      overflow-y: auto;
      margin-bottom: 20px;
      border: 1px solid #444;
      border-radius: 8px;
      padding: 15px;
      background-color: #222;
    }
    .mensagem {
      margin-bottom: 12px;
      padding: 10px 15px;
      background-color: #292929;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.5);
    }
    .mensagem strong {
      color: #4ecdc4;
    }
    .form-mensagem textarea {
      width: 100%;
      height: 70px;
      border-radius: 6px;
      border: 1.5px solid #444;
      background-color: #292929;
      color: #eee;
      padding: 10px;
      font-family: inherit;
      resize: none;
    }
    .form-mensagem button {
      margin-top: 10px;
      background-color: #4ecdc4;
      color: #121212;
      font-weight: 700;
      border: none;
      padding: 12px 20px;
      border-radius: 6px;
      cursor: pointer;
      user-select: none;
      transition: background-color 0.3s ease;
    }
    .form-mensagem button:hover {
      background-color: #38b5ae;
    }
    .alert {
      color: #c0392b;
      font-weight: 700;
      margin-bottom: 10px;
      user-select: none;
    }
    .sucesso {
      color: #27ae60;
      font-weight: 700;
      margin-bottom: 10px;
      user-select: none;
    }
    .admin-actions {
      margin-top: 15px;
      padding-top: 15px;
      border-top: 1px solid #444;
      display: flex;
      gap: 10px;
    }
    .admin-actions form {
      display: inline-block;
      margin: 0;
    }
    .admin-actions button {
      background-color: #e67e22;
      border: none;
      color: #121212;
      padding: 8px 14px;
      border-radius: 6px;
      font-weight: 700;
      cursor: pointer;
      user-select: none;
      transition: background-color 0.3s ease;
    }
    .admin-actions button:hover {
      background-color: #d35400;
    }
    .back-link {
      margin-top: 20px;
      text-align: left;
    }
    .back-link a {
      color: #4ecdc4;
      text-decoration: none;
      font-weight: 600;
    }
    .back-link a:hover {
      text-decoration: underline;
    }
  </style>
</head>

<script>
  window.onload = function() {
    const mensagensContainer = document.querySelector('.mensagens');
    if (mensagensContainer) {
      mensagensContainer.scrollTop = mensagensContainer.scrollHeight;
    }
  };
</script>


<script>
  window.onload = function() {
    window.scrollTo({
      top: document.body.scrollHeight,
      behavior: 'smooth' // rolagem suave, opcional
    });
  };
</script>


<body>

<div class="container">
  <h2><?= htmlspecialchars($topico['nome']) ?> <?php if ($trancado) echo "(Trancado)"; ?></h2>

  <?php if ($mensagemFixada): ?>
    <div class="mensagem-fixada">
      <strong>Mensagem fixada:</strong> <?= nl2br(htmlspecialchars($mensagemFixada)) ?>
    </div>
  <?php endif; ?>

  <div class="mensagens">
    <?php if ($resMsgs->num_rows === 0): ?>
      <p>Nenhuma mensagem ainda.</p>
    <?php else: ?>
      <?php while ($msg = $resMsgs->fetch_assoc()): ?>
        <div class="mensagem">
          <strong><?= htmlspecialchars($msg['usuario']) ?>:</strong><br>
          <?= nl2br(htmlspecialchars($msg['mensagem'])) ?>
          <br><small style="color:#888;"><?= $msg['data'] ?></small>
        </div>
      <?php endwhile; ?>
    <?php endif; ?>
  </div>

  <?php if ($erro): ?>
    <div class="alert"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>
  <?php if ($sucesso): ?>
    <div class="sucesso"><?= htmlspecialchars($sucesso) ?></div>
  <?php endif; ?>

  <?php if ($trancado): ?>
    <p>Este tópico está trancado. Você não pode enviar novas mensagens.</p>
  <?php else: ?>
    <form class="form-mensagem" method="post" action="">
      <textarea name="mensagem" placeholder="Digite sua mensagem aqui..." required></textarea>
      <button type="submit">Enviar</button>
    </form>
  <?php endif; ?>

  <?php if ($isAdmin): ?>
    <div class="admin-actions">
      <form method="post" action="admin_topico_actions.php" style="display:inline-block;">
        <input type="hidden" name="id_topico" value="<?= $topicoId ?>" />
        <input type="hidden" name="acao" value="toggle_trancar" />
        <button type="submit"><?= $trancado ? "Destrancar Tópico" : "Trancar Tópico" ?></button>
      </form>

      <form method="post" action="admin_topico_actions.php" style="display:inline-block;">
        <input type="hidden" name="id_topico" value="<?= $topicoId ?>" />
        <input type="hidden" name="acao" value="remover_fixar" />
        <button type="submit">Remover Mensagem Fixada</button>
      </form>

      <form method="post" action="admin_topico_actions.php" style="display:inline-block;">
        <input type="hidden" name="id_topico" value="<?= $topicoId ?>" />
        <input type="hidden" name="acao" value="fixar" />
        <textarea name="mensagem_fixada" placeholder="Digite a mensagem para fixar..." style="width: 300px; height: 60px; border-radius: 6px; border: 1px solid #444; margin-left: 10px; background-color: #292929; color: #eee; padding: 6px; font-family: inherit;"></textarea>
        <button type="submit">Fixar Mensagem</button>
      </form>
    </div>
  <?php endif; ?>

  <div class="back-link">
    <a href="index.php">← Voltar para tópicos</a>
  </div>
</div>

</body>
</html>
