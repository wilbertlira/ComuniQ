<?php
$conn = new mysqli("localhost", "root", "", "forum");
if ($conn->connect_error) {
  die("Erro na conexão: " . $conn->connect_error);
}
session_start();
?>