-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 22/06/2025 às 06:56
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `forum`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `mensagens`
--

CREATE TABLE `mensagens` (
  `id` int(11) NOT NULL,
  `id_topico` int(11) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `mensagem` text DEFAULT NULL,
  `data` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `mensagens_lidas`
--

CREATE TABLE `mensagens_lidas` (
  `id` int(11) NOT NULL,
  `mensagem_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `data_lida` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `mensagens_lidas`
--

INSERT INTO `mensagens_lidas` (`id`, `mensagem_id`, `usuario_id`, `data_lida`) VALUES
(1, 1, 5, '2025-06-22 01:33:55'),
(2, 2, 1, '2025-06-22 01:34:44'),
(3, 3, 1, '2025-06-22 01:34:44'),
(4, 4, 1, '2025-06-22 01:38:00'),
(5, 5, 6, '2025-06-22 01:44:10'),
(6, 6, 5, '2025-06-22 01:45:44');

-- --------------------------------------------------------

--
-- Estrutura para tabela `mensagens_privadas`
--

CREATE TABLE `mensagens_privadas` (
  `id` int(11) NOT NULL,
  `de_id` int(11) NOT NULL,
  `para_id` int(11) NOT NULL,
  `mensagem` text NOT NULL,
  `data` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `mensagens_privadas`
--

INSERT INTO `mensagens_privadas` (`id`, `de_id`, `para_id`, `mensagem`, `data`) VALUES
(1, 1, 5, 'salve tudo bem?', '2025-06-22 01:08:04'),
(2, 5, 1, 'tudo sim e voce?', '2025-06-22 01:18:27'),
(3, 5, 1, 'meu ovo uriel', '2025-06-22 01:34:20'),
(4, 5, 1, 'meu ovo esquerdo', '2025-06-22 01:37:09'),
(5, 5, 6, 'oi  bixA', '2025-06-22 01:43:53'),
(6, 6, 5, 'sai mona', '2025-06-22 01:44:19');

-- --------------------------------------------------------

--
-- Estrutura para tabela `topicos`
--

CREATE TABLE `topicos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `visibilidade` varchar(255) NOT NULL DEFAULT 'publico',
  `mensagem_fixada` text DEFAULT NULL,
  `trancado` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `topicos`
--

INSERT INTO `topicos` (`id`, `nome`, `visibilidade`, `mensagem_fixada`, `trancado`) VALUES
(1, 'Chat Global', 'publico', 'Teste', 1),
(5, 'Elegazza - Gestão Administrativa', '5,6,1', NULL, 0),
(6, '🍿Cinemora', '5,7,1', NULL, 0),
(7, 'Ramo Hot', '5,6,1', NULL, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('user','admin') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `senha`, `tipo`) VALUES
(1, 'Uriel', '$2y$10$A0k9aW63ms2FPdP.RsxIX.zIEW7NEJQ3jZgAvfAXbuTsLJ2YJ2yvK', 'admin'),
(5, 'caua', '$2y$10$AlXUMOpjER6/5aZ9GQmRbuBERzrrO0DAFJYwfq7tdXrYkrNqUe/wK', 'user'),
(6, 'miguel', '$2y$10$t1KLfmaT7WAngiSXKssxa.dJZxp9ep4tFr.Vrw0fn0zC3SKfB9CpK', 'user'),
(7, 'Rian', '$2y$10$l9slzHIDlSw93bJqOBJtnOESbpT5lxSUVzfebkdHYI1DBU5EcAlhO', 'user');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `mensagens`
--
ALTER TABLE `mensagens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_topico` (`id_topico`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices de tabela `mensagens_lidas`
--
ALTER TABLE `mensagens_lidas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mensagem_id` (`mensagem_id`,`usuario_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `mensagens_privadas`
--
ALTER TABLE `mensagens_privadas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `topicos`
--
ALTER TABLE `topicos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `mensagens`
--
ALTER TABLE `mensagens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de tabela `mensagens_lidas`
--
ALTER TABLE `mensagens_lidas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `mensagens_privadas`
--
ALTER TABLE `mensagens_privadas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `topicos`
--
ALTER TABLE `topicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `mensagens`
--
ALTER TABLE `mensagens`
  ADD CONSTRAINT `mensagens_ibfk_1` FOREIGN KEY (`id_topico`) REFERENCES `topicos` (`id`),
  ADD CONSTRAINT `mensagens_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `mensagens_lidas`
--
ALTER TABLE `mensagens_lidas`
  ADD CONSTRAINT `mensagens_lidas_ibfk_1` FOREIGN KEY (`mensagem_id`) REFERENCES `mensagens_privadas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mensagens_lidas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
