<?php include 'conexao.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Segoe UI', sans-serif;
    }

    body {
      background: linear-gradient(to right, #1f1c2c, #928dab);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .login-container {
      background-color: #ffffff;
      padding: 60px 50px;
      border-radius: 16px;
      box-shadow: 0 0 40px rgba(0, 0, 0, 0.3);
      width: 100%;
      max-width: 500px;
      text-align: center;
    }

    h2 {
      margin-bottom: 35px;
      font-size: 32px;
      color: #333;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 18px 20px;
      margin: 15px 0;
      font-size: 18px;
      border: 1px solid #ccc;
      border-radius: 10px;
      transition: 0.3s;
    }

    input:focus {
      border-color: #007bff;
      outline: none;
    }

    button {
      width: 100%;
      padding: 18px;
      font-size: 18px;
      background-color: #007bff;
      border: none;
      border-radius: 10px;
      color: white;
      cursor: pointer;
      margin-top: 20px;
      transition: background 0.3s;
    }

    button:hover {
      background-color: #0056b3;
    }

    .error-msg {
      margin-top: 20px;
      color: red;
      font-weight: bold;
      font-size: 16px;
    }

    @media screen and (max-width: 600px) {
      .login-container {
        padding: 40px 20px;
      }

      h2 {
        font-size: 26px;
      }

      input,
      button {
        font-size: 16px;
        padding: 14px;
      }
    }
  </style>
</head>
<body>

<div class="login-container">
  <h2>Login</h2>
  <form method="post">
    <input type="text" name="usuario" placeholder="Usuário" required>
    <input type="password" name="senha" placeholder="Senha" required>
    <button type="submit">Entrar</button>
  </form>

  <?php
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario=?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
      $user = $res->fetch_assoc();
      if (password_verify($senha, $user['senha'])) {
        session_start();
        $_SESSION['usuario'] = $user['usuario'];
        $_SESSION['id'] = $user['id'];
        header("Location: index.php");
        exit;
      }
    }
    echo '<div class="error-msg">Usuário ou senha inválidos.</div>';
  }
  ?>
</div>

</body>
</html>
