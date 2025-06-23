-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 23, 2025 at 05:25 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `forum`
--

-- --------------------------------------------------------

--
-- Table structure for table `mensagens`
--

CREATE TABLE `mensagens` (
  `id` int(11) NOT NULL,
  `id_topico` int(11) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `mensagem` text DEFAULT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `data` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mensagens`
--

INSERT INTO `mensagens` (`id`, `id_topico`, `id_usuario`, `mensagem`, `imagem`, `data`) VALUES
(23, 5, 1, 'Vdd', NULL, '2025-06-22 06:05:31'),
(26, 6, 1, 'Novidades', 'img_6858739bac241.jpg', '2025-06-22 21:20:27'),
(27, 7, 1, 'Boa noite, pessoal!\r\n\r\nHoje daremos início à 2ª etapa do nosso esquema HOT, agora de forma mais organizada e segura.\r\n\r\nNa fase anterior, contávamos apenas com o grupo de prévias. A partir de agora, teremos também um grupo exclusivo para os clientes que realmente comprarem, o que nos ajudará a manter a privacidade e evitar denúncias aos nossos canais no Telegram.\r\n\r\nAlém disso, vamos cuidar melhor dos nossos canais, trazendo promoções, eventos, sorteios e muito mais novidades pra vocês!\r\n\r\nFiquem ligados e aproveitem o que vem por aí! 🔥', NULL, '2025-06-22 21:23:47'),
(28, 7, 6, 'Certo professor uma pergunta vamos todos participar do mesmo grupo e eu ficarei responsável pela avaliação dos boys?', NULL, '2025-06-22 23:06:17'),
(29, 7, 6, 'Bots', NULL, '2025-06-22 23:06:45'),
(30, 7, 1, 'Você ficará responsável pelo Esquema hot como um todo. Você irá cuidar dos bots e etc. Depois iremos call e lhe passarei as instruções tudo certinha', NULL, '2025-06-23 00:11:34'),
(31, 7, 5, 'Blz', NULL, '2025-06-23 00:24:51'),
(32, 7, 6, 'Fecho mas a call pode ser amanhã de tarde?', NULL, '2025-06-23 02:58:57'),
(33, 7, 1, 'Irei ver aqui, amanhã de manhã lhe aviso', NULL, '2025-06-23 03:10:22');

-- --------------------------------------------------------

--
-- Table structure for table `mensagens_lidas`
--

CREATE TABLE `mensagens_lidas` (
  `id` int(11) NOT NULL,
  `mensagem_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `data_lida` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mensagens_lidas`
--

INSERT INTO `mensagens_lidas` (`id`, `mensagem_id`, `usuario_id`, `data_lida`) VALUES
(27, 31, 6, '2025-06-23 02:58:01'),
(28, 32, 1, '2025-06-23 03:04:46');

-- --------------------------------------------------------

--
-- Table structure for table `mensagens_privadas`
--

CREATE TABLE `mensagens_privadas` (
  `id` int(11) NOT NULL,
  `de_id` int(11) NOT NULL,
  `para_id` int(11) NOT NULL,
  `mensagem` text NOT NULL,
  `data` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mensagens_privadas`
--

INSERT INTO `mensagens_privadas` (`id`, `de_id`, `para_id`, `mensagem`, `data`) VALUES
(31, 1, 6, 'Fala meu nobre', '2025-06-23 00:08:36'),
(32, 6, 1, 'Opa', '2025-06-23 02:58:12'),
(33, 1, 6, ':0', '2025-06-23 03:05:03');

-- --------------------------------------------------------

--
-- Table structure for table `topicos`
--

CREATE TABLE `topicos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `visibilidade` varchar(255) NOT NULL DEFAULT 'publico',
  `mensagem_fixada` text DEFAULT NULL,
  `trancado` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `topicos`
--

INSERT INTO `topicos` (`id`, `nome`, `visibilidade`, `mensagem_fixada`, `trancado`) VALUES
(1, 'Chat Global', 'publico', 'Sem Novas atualizações galera :)', 1),
(5, 'Elegazza - Gestão Administrativa', '5,6,1', NULL, 0),
(6, '🍿Cinemora', '5,7,1', 'Em Breve Novas Atualizações. FIQUEM A VONTADE PARA CONVERSAR!', 0),
(7, 'Ramo Hot', '5,6,1', 'Reunião Quarta-Feira as 22:30', 0);

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('user','admin') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `senha`, `tipo`) VALUES
(1, 'Uriel', '$2y$10$A0k9aW63ms2FPdP.RsxIX.zIEW7NEJQ3jZgAvfAXbuTsLJ2YJ2yvK', 'admin'),
(5, 'caua', '$2y$10$AlXUMOpjER6/5aZ9GQmRbuBERzrrO0DAFJYwfq7tdXrYkrNqUe/wK', 'user'),
(6, 'miguel', '$2y$10$t1KLfmaT7WAngiSXKssxa.dJZxp9ep4tFr.Vrw0fn0zC3SKfB9CpK', 'user'),
(7, 'Rian', '$2y$10$l9slzHIDlSw93bJqOBJtnOESbpT5lxSUVzfebkdHYI1DBU5EcAlhO', 'user'),
(8, 'Equipe de Desenvolvimento', '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `mensagens`
--
ALTER TABLE `mensagens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_topico` (`id_topico`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `mensagens_lidas`
--
ALTER TABLE `mensagens_lidas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mensagem_id` (`mensagem_id`,`usuario_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indexes for table `mensagens_privadas`
--
ALTER TABLE `mensagens_privadas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `topicos`
--
ALTER TABLE `topicos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `mensagens`
--
ALTER TABLE `mensagens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `mensagens_lidas`
--
ALTER TABLE `mensagens_lidas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `mensagens_privadas`
--
ALTER TABLE `mensagens_privadas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `topicos`
--
ALTER TABLE `topicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `mensagens`
--
ALTER TABLE `mensagens`
  ADD CONSTRAINT `mensagens_ibfk_1` FOREIGN KEY (`id_topico`) REFERENCES `topicos` (`id`),
  ADD CONSTRAINT `mensagens_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `mensagens_lidas`
--
ALTER TABLE `mensagens_lidas`
  ADD CONSTRAINT `mensagens_lidas_ibfk_1` FOREIGN KEY (`mensagem_id`) REFERENCES `mensagens_privadas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mensagens_lidas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
