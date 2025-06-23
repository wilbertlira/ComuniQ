<?php
$conn = new mysqli("localhost", "root", "", "forum");
if ($conn->connect_error) {
  die("Erro na conexão: " . $conn->connect_error);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
