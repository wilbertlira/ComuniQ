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

// Busca o tópico e verifica visibilidade
$stmt = $conn->prepare("SELECT * FROM topicos WHERE id = ?");
$stmt->bind_param("i", $topicoId);
$stmt->execute();
$resTopico = $stmt->get_result();
if ($resTopico->num_rows === 0) {
    die("Tópico não encontrado.");
}
$topico = $resTopico->fetch_assoc();

// Verifica se o usuário tem permissão de ver o tópico
$visibilidade = $topico['visibilidade'];
if (
    $visibilidade !== 'publico' &&
    !in_array($userId, array_map('intval', explode(',', $visibilidade)))
) {
    die("Você não tem permissão para ver este tópico.");
}

$trancado = (bool)$topico['trancado'];
$mensagemFixada = $topico['mensagem_fixada'];

// Inicializa variáveis de erro/sucesso
$erro = '';
$sucesso = '';

// Processar envio de mensagem e upload de imagem
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$trancado) {
    $msg = trim($_POST['mensagem'] ?? '');
    $imagem_nome = null;

    // Processar upload de imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $arquivo_tmp = $_FILES['imagem']['tmp_name'];
        $nome_original = basename($_FILES['imagem']['name']);
        $extensao = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION));
        $extensoes_validas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($extensao, $extensoes_validas)) {
            $novo_nome = uniqid('img_') . '.' . $extensao;
            $diretorio_upload = 'uploads/';

            if (!is_dir($diretorio_upload)) {
                mkdir($diretorio_upload, 0755, true);
            }

            $destino = $diretorio_upload . $novo_nome;
            if (move_uploaded_file($arquivo_tmp, $destino)) {
                $imagem_nome = $novo_nome;
            } else {
                $erro = "Falha ao salvar a imagem.";
            }
        } else {
            $erro = "Tipo de arquivo inválido. Apenas JPG, PNG, GIF e WEBP são permitidos.";
        }
    }

    if ($erro === '') {
        if ($msg === '' && $imagem_nome === null) {
            $erro = "Você deve enviar uma mensagem ou uma imagem.";
        } else {
            $stmtInsert = $conn->prepare("INSERT INTO mensagens (id_topico, id_usuario, mensagem, imagem, data) VALUES (?, ?, ?, ?, NOW())");
            $stmtInsert->bind_param("iiss", $topicoId, $userId, $msg, $imagem_nome);
            if ($stmtInsert->execute()) {
                $sucesso = "Mensagem enviada!";
            } else {
                $erro = "Erro ao enviar mensagem.";
            }
            $stmtInsert->close();
        }
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
  <style>
    /* Seu CSS adaptado, responsivo, resumido para foco */

* {
  box-sizing: border-box;
}

body {
  background-color: #0b141a;
  color: #e9edef;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  margin: 0;
  padding: 0;
  display: flex;
  justify-content: center;
  height: 100vh;
}

.container {
  width: 100%;
  max-width: 950px;

  margin: auto;



  height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: #111b21;
  border-left: 1px solid #2a3942;
  border-right: 1px solid #2a3942;
}

h2 {
  color: #25d366;
  margin: 15px 20px;
  font-size: 1.2rem;
}

.mensagem-fixada {
  background: #202c33;
  border-left: 5px solid #25d366;
  padding: 12px 16px;
  margin: 10px 20px;
  font-style: italic;
  color: #d1d7db;
  border-radius: 4px;
}

.mensagens {
  flex-grow: 1;
  padding: 15px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 10px;

}

.mensagem {
  max-width: 85%;
  padding: 14px 18px;
  border-radius: 12px;
  word-wrap: break-word;
  line-height: 1.6;
  position: relative;
  font-size: 2.1rem; /* AQUI aumenta o tamanho do texto */
}


.mensagem strong {
  display: block;
  font-size: 0.85rem;
  color: #8696a0;
  margin-bottom: 5px;
}

.mensagem.remetente {
  align-self: flex-end;
  background-color: #25d366;
  color: #000;
  border-bottom-right-radius: 0;
}

.mensagem.destinatario {
  align-self: flex-start;
  background-color: #202c33;
  color: #e9edef;
  border-bottom-left-radius: 0;
}

.mensagem img {
  margin-top: 8px;
  max-width: 100%;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.3);
}

.mensagem audio {
  margin-top: 8px;
  width: 100%;
}

.form-mensagem {
  padding: 12px;
  background: #202c33;
  border-top: 1px solid #2a3942;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.form-mensagem textarea {
  width: 100%;
  resize: none;
  height: 60px;
  border: none;
  border-radius: 20px;
  background-color: #2a3942;
  color: #fff;
  padding: 10px 15px;
  font-size: 1rem;
  font-family: inherit;
}

.form-mensagem input[type="file"] {
  color: #ccc;
  font-size: 0.9rem;
}

.form-mensagem button {
  align-self: flex-end;
  padding: 10px 20px;
  background-color: #25d366;
  color: #000;
  border: none;
  border-radius: 20px;
  font-weight: bold;
  cursor: pointer;
  transition: background 0.3s ease;
}

.form-mensagem button:hover {
  background-color: #1faa5f;
}

.alert, .sucesso {
  font-size: 0.9rem;
  font-weight: bold;
  padding: 8px 12px;
  border-radius: 6px;
  margin: 10px 0;
}

.alert {
  background-color: #3e2723;
  color: #ef5350;
}

.sucesso {
  background-color: #1b5e20;
  color: #66bb6a;
}

.back-link {
  padding: 15px;
  text-align: center;
  background: #111b21;
}

.back-link a {
  color: #25d366;
  text-decoration: none;
  font-weight: bold;
}

.back-link a:hover {
  text-decoration: underline;
}

@media (max-width: 600px) {
  .mensagens {
    max-height: 100vh;
    padding: 10px;
  }

  .form-mensagem textarea {
    height: 50px;
    font-size: 0.95rem;
  }

  .form-mensagem button {
    padding: 8px 16px;
  }
}

  </style>
</head>
<body>

<div class="container">
  <h2><?= htmlspecialchars($topico['nome']) ?> <?php if ($trancado) echo "(Trancado)"; ?></h2>



<?php if ($isAdmin): ?>
  <form method="post" action="admin_topico_actions.php" style="margin: 0 20px 10px;">
    <input type="hidden" name="id_topico" value="<?= $topicoId ?>">
    
    <button type="submit" name="acao" value="toggle_trancar"
      style="padding: 6px 12px; margin-right: 10px; border-radius: 5px; background-color: <?= $trancado ? '#f44336' : '#4CAF50' ?>; color: white; border: none;">
      <?= $trancado ? 'Destrancar tópico' : 'Trancar tópico' ?>
    </button>

    <button type="submit" name="acao" value="remover_fixar"
      style="padding: 6px 12px; border-radius: 5px; background-color: #FF9800; color: white; border: none;">
      Remover mensagem fixada
    </button>
  </form>

  <form method="post" action="admin_topico_actions.php" style="margin: 10px 20px;">
    <input type="hidden" name="id_topico" value="<?= $topicoId ?>">
    <textarea name="mensagem_fixada" rows="2" placeholder="Mensagem para fixar..." style="width: 100%; padding: 8px; border-radius: 6px;"></textarea>
    <button type="submit" name="acao" value="fixar"
      style="margin-top: 6px; padding: 8px 16px; background-color: #2196F3; color: white; border: none; border-radius: 6px;">
      Fixar nova mensagem
    </button>
  </form>
<?php endif; ?>


  <?php if ($mensagemFixada): ?>
    <div class="mensagem-fixada">
      <strong>Mensagem fixada:</strong> <?= nl2br(htmlspecialchars($mensagemFixada)) ?>
    </div>
  <?php endif; ?>

  <div class="mensagens" id="mensagens-container">
    <?php if ($resMsgs->num_rows === 0): ?>
      <p>Nenhuma mensagem ainda.</p>
    <?php else: ?>
      <?php while ($msg = $resMsgs->fetch_assoc()): ?>
        <div class="mensagem">
          <strong><?= htmlspecialchars($msg['usuario']) ?>:</strong>
          <?= nl2br(htmlspecialchars($msg['mensagem'])) ?>
          <?php if (!empty($msg['imagem'])): ?>
            <img src="uploads/<?= htmlspecialchars($msg['imagem']) ?>" alt="Imagem enviada" />
          <?php endif; ?>
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
    <form class="form-mensagem" method="post" action="" enctype="multipart/form-data">
      <textarea name="mensagem" placeholder="Digite sua mensagem aqui..."></textarea>
      <input type="file" name="imagem" accept="image/*" />
      <button type="submit">Enviar</button>
    </form>
  <?php endif; ?>

  <div class="back-link">
    <a href="index.php">← Voltar para tópicos</a>
  </div>
</div>

<script>
  // Auto scroll para o final das mensagens
  window.onload = function() {
    const mensagensContainer = document.getElementById('mensagens-container');
    if (mensagensContainer) {
      mensagensContainer.scrollTop = mensagensContainer.scrollHeight;
    }
  };
</script>


<script>
const mensagensContainer = document.getElementById('mensagens-container');
const topicoId = <?= $topicoId ?>;

function buscarMensagens() {
  fetch(`mensagens.php?topico_id=${topicoId}`)
    .then(res => res.json())
    .then(mensagens => {
      mensagensContainer.innerHTML = '';
      mensagens.forEach(msg => {
        const div = document.createElement('div');
        div.classList.add('mensagem');
        div.innerHTML = `
          <strong>${msg.usuario}:</strong>
          ${msg.mensagem.replace(/\n/g, "<br>")}
          ${msg.imagem ? `<img src="uploads/${msg.imagem}" alt="Imagem enviada" />` : ''}
          <br><small style="color:#888;">${msg.data}</small>
        `;
        mensagensContainer.appendChild(div);
      });

      mensagensContainer.scrollTop = mensagensContainer.scrollHeight;
    });
}

// Atualiza a cada 5 segundos
setInterval(buscarMensagens, 5000);
window.onload = buscarMensagens;
</script>


</body>
</html>
