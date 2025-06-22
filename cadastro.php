<?php include 'conexao.php'; ?>
<link rel="stylesheet" href="assets/style.css">
<div class="container">

<h2>Cadastro</h2>
<form method="post">
  <input type="text" name="usuario" placeholder="Usuário" required>
  <input type="password" name="senha" placeholder="Senha" required>
  <button type="submit">Cadastrar</button>
</form>
<p><a href="login.php">Já tem conta? Faça login</a></p>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $usuario = $_POST['usuario'];
  $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
  $stmt = $conn->prepare("INSERT INTO usuarios (usuario, senha) VALUES (?, ?)");
  $stmt->bind_param("ss", $usuario, $senha);
  if ($stmt->execute()) {
    echo "Cadastro realizado com sucesso. <a href='login.php'>Login</a>";
  } else {
    echo "Erro: usuário já existe.";
  }
}
?>
</div>