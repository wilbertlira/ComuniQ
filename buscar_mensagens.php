<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['usuario'])) exit;

$stmt = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $_SESSION['usuario']);
$stmt->execute();
$res = $stmt->get_result();
$me = $res->fetch_assoc();
$meuId = $me['id'];

$paraId = intval($_GET['para'] ?? 0);
if ($paraId === 0) exit;

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

// Buscar mensagens
$stmtMsg = $conn->prepare("
  SELECT m.*, u.usuario FROM mensagens_privadas m
  JOIN usuarios u ON m.de_id = u.id
  WHERE (m.de_id = ? AND m.para_id = ?) OR (m.de_id = ? AND m.para_id = ?)
  ORDER BY m.data ASC
");
$stmtMsg->bind_param("iiii", $meuId, $paraId, $paraId, $meuId);
$stmtMsg->execute();
$mensagens = $stmtMsg->get_result();

while ($m = $mensagens->fetch_assoc()):
    $classe = $m['de_id'] === $meuId ? 'remetente' : 'destinatario';
    ?>
    <div class="mensagem <?= $classe ?>">
        <?= nl2br(htmlspecialchars($m['mensagem'])) ?>
        <br>
        <small style="font-size: 0.7rem; color: #999;"><?= $m['data'] ?></small>
    </div>
<?php endwhile; ?>
