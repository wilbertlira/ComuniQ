<?php include 'conexao.php'; ?>
<link rel="stylesheet" href="assets/style.css">
<div class="container">

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
      $_SESSION['usuario'] = $user['usuario'];
      $_SESSION['id'] = $user['id'];
      header("Location: index.php");
      exit;
    }
  }
  echo "Usuário ou senha inválidos.";
}
?>
</div>