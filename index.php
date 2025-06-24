<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}

// Busca id e tipo do usuário
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

// Busca tópicos com filtro de visibilidade
$sql = "SELECT * FROM topicos WHERE visibilidade = 'publico' OR FIND_IN_SET(?, visibilidade)";
$stmtTopicos = $conn->prepare($sql);
$stmtTopicos->bind_param("i", $userId);
$stmtTopicos->execute();
$resTopicos = $stmtTopicos->get_result();

// Busca notificação de mensagem privada nova (não lida)
$stmtNot = $conn->prepare("
  SELECT m.de_id, u.usuario 
  FROM mensagens_privadas m
  LEFT JOIN mensagens_lidas l ON m.id = l.mensagem_id AND l.usuario_id = ?
  JOIN usuarios u ON m.de_id = u.id
  WHERE m.para_id = ? AND l.id IS NULL
  ORDER BY m.data DESC
  LIMIT 1
");
$stmtNot->bind_param("ii", $userId, $userId);
$stmtNot->execute();
$resNot = $stmtNot->get_result();
$notificacao = $resNot->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ComuniQ</title>
  <link rel="stylesheet" href="assets/style.css" />
  <style>
    * {
      box-sizing: border-box;
    }
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #0e0e0e;
      color: #fff;
    }

    .whatsapp-wrapper {
      max-width: 400px;
      height: 100vh;
      margin: auto;
      display: flex;
      flex-direction: column;
      background-color: #1f1f1f;
    }

    header {
      background-color: #202c33;
      padding: 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      color: #fff;
      font-weight: bold;
      gap: 15px;
      position: relative;
    }

    header span {
      font-size: 1.1rem;
    }

    header button,
    header a {
      color: #25d366;
      text-decoration: none;
      font-size: 14px;
      background: none;
      border: none;
      cursor: pointer;
      font-weight: 600;
      padding: 6px 12px;
      border-radius: 20px;
      transition: background-color 0.3s;
    }

    header button:hover,
    header a:hover {
      background-color: #128c34;
      color: #fff;
    }

    /* Botão Mensagens Privadas fixo fora do menu */
    #btnMensagens {
      margin-right: 10px;
    }

    /* Menu de três pontos */
    #menuBtn {
      background: none; 
      border: none; 
      color: #25d366; 
      font-size: 24px; 
      cursor: pointer;
      padding: 0 8px;
    }

    #dropdownMenu {
      display: none;
      position: absolute;
      right: 15px;
      top: 56px; /* abaixo do header */
      background-color: #202c33;
      border-radius: 8px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.5);
      width: 180px;
      z-index: 1000;
      flex-direction: column;
    }

    #dropdownMenu button {
      width: 100%;
      background: none;
      border: none;
      color: #dcf8c6;
      padding: 12px 15px;
      text-align: left;
      font-size: 14px;
      cursor: pointer;
      border-bottom: 1px solid #128c34;
      transition: background-color 0.3s;
    }

    #dropdownMenu button:last-child {
      border-bottom: none;
    }

    #dropdownMenu button:hover {
      background-color: #128c34;
      color: #fff;
    }

    .topico-lista {
      flex: 1;
      overflow-y: auto;
      background-color: #111;
    }

    .topico-item {
      padding: 15px;
      display: flex;
      align-items: center;
      border-bottom: 1px solid #2a2a2a;
      cursor: pointer;
      transition: background 0.3s;
    }

    .topico-item:hover {
      background-color: #292929;
    }

    .topico-item .emoji {
      font-size: 1.5rem;
      margin-right: 12px;
    }

    .topico-item a {
      color: #dcf8c6;
      text-decoration: none;
      font-weight: 600;
      font-size: 1rem;
      flex: 1;
    }

    .criar-topico {
      background-color: #25d366;
      color: #fff;
      padding: 10px;
      border: none;
      border-radius: 50px;
      margin: 10px;
      font-weight: bold;
      cursor: pointer;
      font-size: 14px;
      align-self: flex-end;
    }

    .criar-topico:hover {
      background-color: #128c34;
    }

    /* Modal styles */
    #modalOverlay {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background-color: rgba(0,0,0,0.6);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }
    #modal {
      background-color: #202c33;
      color: #dcf8c6;
      padding: 20px;
      border-radius: 12px;
      max-width: 320px;
      width: 90%;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.8);
      text-align: center;
      font-size: 1rem;
    }
    #modal h2 {
      margin-top: 0;
      font-weight: 700;
      font-size: 1.3rem;
    }
    #modal p {
      margin: 15px 0;
    }
    #modal button {
      background-color: #25d366;
      border: none;
      color: #fff;
      padding: 10px 20px;
      margin: 0 8px;
      border-radius: 50px;
      font-weight: 700;
      cursor: pointer;
      transition: background-color 0.3s;
      font-size: 1rem;
    }
    #modal button:hover {
      background-color: #128c34;
    }
    #modal button.close-btn {
      background-color: #555;
    }
    #modal button.close-btn:hover {
      background-color: #333;
    }
  </style>
</head>
<body>

<div class="whatsapp-wrapper">
  <header>
    <span>Olá, <?= htmlspecialchars($_SESSION['usuario']) ?></span>

    <div style="display: flex; align-items: center; gap: 10px; position: relative;">
      <button id="btnMensagens" onclick="location.href='usuarios.php'">Mensagens Privadas</button>

      <button id="menuBtn" aria-label="Abrir menu de configurações">&#x22EE;</button>
      <div id="dropdownMenu" role="menu" aria-hidden="true" aria-label="Menu de opções">
        <button onclick="location.href='salas/sala.php'">📞 Realizar ligação</button>
        <button onclick="location.href='config.php'">⚙️ Configurações</button>
        
        <button onclick="location.href='logout.php'">🚪 Sair</button>
      </div>
    </div>
  </header>

  <?php if ($isAdmin): ?>
    <button class="criar-topico" onclick="location.href='criar_topico.php'">+ Criar Tópico</button>
    <button class="criar-topico" onclick="location.href='admin_avisos.php'">+ Criar avisos</button>
  <?php endif; ?>

  <div class="topico-lista">
    <?php if ($resTopicos->num_rows > 0): ?>
      <?php while ($topico = $resTopicos->fetch_assoc()): ?>
        <div class="topico-item">
          <div class="emoji">💬</div>
          <a href="topico.php?id=<?= intval($topico['id']) ?>">
            <?= htmlspecialchars($topico['nome']) ?>
          </a>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p style="padding: 15px; color: #888;">Nenhum tópico disponível.</p>
    <?php endif; ?>
  </div>
</div>

<?php if ($notificacao): ?>
  <div id="modalOverlay">
    <div id="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" aria-describedby="modalDesc">
      <h2 id="modalTitle">📩 Nova Mensagem Privada</h2>
      <p id="modalDesc">Você recebeu uma mensagem de <strong><?= htmlspecialchars($notificacao['usuario']) ?></strong>.</p>
      <div>
        <button id="btnOpenChat">Visualizar</button>
        <button id="btnClose" class="close-btn">Fechar</button>
      </div>
    </div>
  </div>

  <script>
    window.onload = function() {
      const modalOverlay = document.getElementById('modalOverlay');
      const btnOpenChat = document.getElementById('btnOpenChat');
      const btnClose = document.getElementById('btnClose');
      modalOverlay.style.display = 'flex';

      btnOpenChat.onclick = function() {
        window.location.href = "chat.php?para=<?= intval($notificacao['de_id']) ?>";
      };

      btnClose.onclick = function() {
        modalOverlay.style.display = 'none';
      };
    };
  </script>
<?php endif; ?>

<script>
  const menuBtn = document.getElementById('menuBtn');
  const dropdownMenu = document.getElementById('dropdownMenu');

  menuBtn.addEventListener('click', (e) => {
    e.stopPropagation(); // evitar que o clique propague e feche o menu
    const isShown = dropdownMenu.style.display === 'flex';
    dropdownMenu.style.display = isShown ? 'none' : 'flex';
    dropdownMenu.setAttribute('aria-hidden', isShown ? 'true' : 'false');
  });

  document.addEventListener('click', (e) => {
    if (!menuBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
      dropdownMenu.style.display = 'none';
      dropdownMenu.setAttribute('aria-hidden', 'true');
    }
  });
</script>

</body>
</html>
