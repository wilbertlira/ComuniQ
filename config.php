<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}

// Busca os dados do usuário
$stmt = $conn->prepare("SELECT id, usuario, data_criacao FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $_SESSION['usuario']);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $novoNome = $_POST['nome'] ?? '';
  $novaSenha = $_POST['senha'] ?? '';

  if (!empty($novoNome)) {
    $stmt = $conn->prepare("UPDATE usuarios SET usuario = ? WHERE id = ?");
    $stmt->bind_param("si", $novoNome, $usuario['id']);
    $stmt->execute();
    $_SESSION['usuario'] = $novoNome;
    $mensagem = "Nome alterado com sucesso!";
  }

  if (!empty($novaSenha)) {
    $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
    $stmt->bind_param("si", $senhaHash, $usuario['id']);
    $stmt->execute();
    $mensagem = "Senha alterada com sucesso!";
  }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Configurações da Conta</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      background: #0e0e0e;
      font-family: 'Segoe UI', sans-serif;
      color: #fff;
      margin: 0;
    }

    .header {
      background-color: #202c33;
      color: #fff;
      padding: 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-weight: bold;
    }

    .header span {
      font-size: 1rem;
    }

    .header a {
      background-color: #25d366;
      color: #fff;
      text-decoration: none;
      padding: 6px 12px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 0.9rem;
      transition: background-color 0.3s;
    }

    .header a:hover {
      background-color: #128c34;
    }

    .config-box {
      background-color: #1f1f1f;
      padding: 30px;
      border-radius: 12px;
      max-width: 400px;
      margin: 30px auto;
      box-shadow: 0 0 10px rgba(0,0,0,0.8);
    }

    h2 {
      margin-top: 0;
      color: #25d366;
      text-align: center;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 10px;
      border-radius: 6px;
      border: none;
      margin-top: 5px;
      font-size: 1rem;
    }

    .btn {
      margin-top: 20px;
      padding: 10px;
      width: 100%;
      background: #25d366;
      border: none;
      color: #fff;
      font-weight: bold;
      font-size: 1rem;
      border-radius: 6px;
      cursor: pointer;
    }

    .btn:hover {
      background: #128c34;
    }

    .info {
      margin-top: 20px;
      font-size: 0.9rem;
      color: #aaa;
      text-align: center;
    }

    .mensagem {
      margin-top: 15px;
      color: #55efc4;
      font-weight: bold;
      text-align: center;
    }
  </style>
</head>
<body>

  <div class="header">
    <span>⚙️ Configurações</span>
    <a href="index.php">Sair</a>
  </div>

  <div class="config-box">
    <h2>Minha Conta</h2>

    <?php if (isset($mensagem)) echo "<div class='mensagem'>$mensagem</div>"; ?>

    <form method="POST">
      <label for="nome">Alterar Nome:</label>
      <input type="text" name="nome" id="nome" value="<?= htmlspecialchars($usuario['usuario']) ?>">

      <label for="senha">Nova Senha:</label>
      <input type="password" name="senha" id="senha" placeholder="Deixe em branco para não alterar">

      <button type="submit" class="btn">Salvar Alterações</button>
    </form>

    <div class="info">
      <p><strong>Conta criada em:</strong><br><?= date('d/m/Y H:i', strtotime($usuario['data_criacao'])) ?></p>
    </div>
  </div>

</body>
</html>
