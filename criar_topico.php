<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// Verifica se é admin
$stmtUser = $conn->prepare("SELECT tipo, id FROM usuarios WHERE usuario = ?");
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
$userId = $user['id'];

$erro = '';
$sucesso = '';

// Busca todos usuários (exceto admin atual, opcional)
$usuarios = [];
$resUsuarios = $conn->query("SELECT id, usuario FROM usuarios WHERE id != $userId ORDER BY usuario ASC");
if ($resUsuarios) {
    while ($u = $resUsuarios->fetch_assoc()) {
        $usuarios[] = $u;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $visibilidade = $_POST['visibilidade'] ?? [];

    if ($nome === '') {
        $erro = "O nome do tópico é obrigatório.";
    } else {
        // Se visibilidade for array e vazio = publico
        if (is_array($visibilidade) && count($visibilidade) > 0) {
            // Sanitizar e montar string ids separados por vírgula
            $visibilidade = array_map('intval', $visibilidade);
            $visibilidade = implode(',', $visibilidade);
        } else {
            $visibilidade = 'publico';
        }

        $stmt = $conn->prepare("INSERT INTO topicos (nome, visibilidade) VALUES (?, ?)");
        $stmt->bind_param("ss", $nome, $visibilidade);
        if ($stmt->execute()) {
            $sucesso = "Tópico criado com sucesso!";
        } else {
            $erro = "Erro ao criar tópico.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Criar Tópico - Fórum</title>
  <link rel="stylesheet" href="assets/style.css" />
  <style>
    body {
      background-color: #121212;
      color: #eee;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      padding: 25px;
      margin: 0;
      min-height: 100vh;
      display: flex;
      justify-content: center;
    }
    .container {
      max-width: 600px;
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
      margin-bottom: 25px;
      font-weight: 700;
      user-select: none;
      text-align: center;
    }
    label {
      margin-top: 15px;
      margin-bottom: 8px;
      font-weight: 600;
      display: block;
    }
    input[type="text"], textarea {
      width: 100%;
      padding: 10px 12px;
      border-radius: 6px;
      border: 1.5px solid #444;
      background-color: #292929;
      color: #eee;
      font-size: 1rem;
      font-family: inherit;
      resize: vertical;
    }
    .checkbox-list {
      background-color: #292929;
      border-radius: 6px;
      max-height: 180px;
      overflow-y: auto;
      padding: 10px 12px;
      border: 1.5px solid #444;
    }
    .checkbox-list label {
      display: flex;
      align-items: center;
      margin-bottom: 6px;
      font-weight: 500;
      cursor: pointer;
      user-select: none;
    }
    .checkbox-list input[type="checkbox"] {
      margin-right: 10px;
      cursor: pointer;
      width: 18px;
      height: 18px;
    }
    button {
      margin-top: 25px;
      background-color: #4ecdc4;
      border: none;
      color: #121212;
      padding: 14px;
      font-weight: 700;
      font-size: 1.1rem;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s ease;
      user-select: none;
    }
    button:hover {
      background-color: #38b5ae;
    }
    .message {
      margin-top: 15px;
      padding: 12px 15px;
      border-radius: 8px;
      font-weight: 600;
      text-align: center;
      user-select: none;
    }
    .error {
      background-color: #c0392b;
      color: #fff;
    }
    .success {
      background-color: #27ae60;
      color: #fff;
    }
    .back-link {
      margin-top: 20px;
      text-align: center;
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
<body>

<div class="container">
  <h2>Criar Novo Tópico</h2>

  <?php if ($erro): ?>
    <div class="message error"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>
  <?php if ($sucesso): ?>
    <div class="message success"><?= htmlspecialchars($sucesso) ?></div>
  <?php endif; ?>

  <form method="post" action="">
    <label for="nome">Nome do Tópico</label>
    <input type="text" id="nome" name="nome" required maxlength="100" autofocus />

    <label>Visibilidade</label>
    <div class="checkbox-list">
      <label>
        <input type="checkbox" name="visibilidade[]" value="publico" id="chk_publico" checked onchange="togglePublico(this)">
        Público (visível para todos)
      </label>
      <hr style="border-color:#444;margin:8px 0;">
      <?php foreach ($usuarios as $u): ?>
        <label>
          <input type="checkbox" name="visibilidade[]" value="<?= intval($u['id']) ?>" class="chk-usuario" onchange="toggleUsuario()">
          <?= htmlspecialchars($u['usuario']) ?>
        </label>
      <?php endforeach; ?>
    </div>

    <button type="submit">Criar Tópico</button>
  </form>

  <div class="back-link">
    <a href="index.php">← Voltar para tópicos</a>
  </div>
</div>

<script>
  // Se marcar público, desmarca os usuários
  function togglePublico(chk) {
    if (chk.checked) {
      document.querySelectorAll('.chk-usuario').forEach(chkUser => {
        chkUser.checked = false;
      });
    }
  }
  // Se marcar qualquer usuário, desmarca público
  function toggleUsuario() {
    const anyChecked = Array.from(document.querySelectorAll('.chk-usuario')).some(chk => chk.checked);
    if (anyChecked) {
      document.getElementById('chk_publico').checked = false;
    } else {
      // Se não selecionou nenhum usuário, volta a marcar público para não ficar sem visibilidade
      document.getElementById('chk_publico').checked = true;
    }
  }
</script>

</body>
</html>
