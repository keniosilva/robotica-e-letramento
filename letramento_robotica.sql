-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 21/08/2025 às 17:39
-- Versão do servidor: 8.0.33
-- Versão do PHP: 8.2.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `letramento_robotica`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `alunos`
--

CREATE TABLE `alunos` (
  `id` int NOT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `serie_id` int DEFAULT NULL,
  `turma_id` int DEFAULT NULL,
  `turno_id` int DEFAULT NULL,
  `escola_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `alunos`
--

INSERT INTO `alunos` (`id`, `nome`, `serie_id`, `turma_id`, `turno_id`, `escola_id`) VALUES
(1, 'Antonio Jose', 1, 2, NULL, 2),
(2, 'Carlos Silva', 1, 2, NULL, 2),
(3, 'Maria Jose', 2, 1, NULL, 2),
(4, 'Jose Maria', 4, 3, NULL, 3);

-- --------------------------------------------------------

--
-- Estrutura para tabela `aulas`
--

CREATE TABLE `aulas` (
  `id` int NOT NULL,
  `titulo` varchar(255) NOT NULL DEFAULT 'Sem título',
  `descricao` text,
  `data` datetime NOT NULL,
  `professor_id` int DEFAULT NULL,
  `escola_id` int DEFAULT NULL,
  `turma_id` int DEFAULT NULL,
  `serie_id` int DEFAULT NULL,
  `conteudo` text NOT NULL,
  `segmento` enum('robotica','letramento_digital') NOT NULL DEFAULT 'robotica'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `aulas`
--

INSERT INTO `aulas` (`id`, `titulo`, `descricao`, `data`, `professor_id`, `escola_id`, `turma_id`, `serie_id`, `conteudo`, `segmento`) VALUES
(1, 'Sem título', NULL, '2025-08-07 22:29:44', 4, 2, 2, 2, 'internet', 'letramento_digital'),
(2, 'Sem título', NULL, '2025-08-07 22:55:29', 4, 2, 2, 2, 'inclusao', 'robotica'),
(3, 'Sem título', NULL, '2025-08-07 22:56:53', 4, 2, 3, 2, 'inclusao', 'robotica'),
(4, 'Sem título', NULL, '2025-08-07 22:57:35', 4, 2, 3, 2, 'inclusao', 'robotica'),
(5, 'Sem título', NULL, '2025-08-07 22:57:47', 4, 2, 3, 2, 'inclusao', 'robotica'),
(6, 'Sem título', NULL, '2025-08-07 19:59:22', 3, 2, 1, 1, 'inclusao', 'robotica'),
(7, 'Sem título', NULL, '2025-08-07 19:59:44', 4, 2, 1, 1, 'word', 'letramento_digital'),
(8, 'Sem título', NULL, '2025-08-07 19:59:59', 3, 2, 3, 1, 'inclusao', 'robotica'),
(9, 'Sem título', NULL, '2025-08-07 20:00:44', 3, 2, 1, 1, 'inclusao', 'robotica'),
(10, 'Sem título', NULL, '2025-08-07 20:10:41', 3, 2, 2, 1, 'word', 'letramento_digital'),
(11, 'Sem título', NULL, '2025-08-07 20:17:13', 3, 3, 3, 4, 'excel', 'letramento_digital'),
(12, 'Sem título', NULL, '2025-08-07 20:17:25', 4, 2, 2, 1, 'excel', 'letramento_digital'),
(13, 'Sem título', NULL, '2025-08-08 08:04:12', 3, 2, 2, 1, 'Introduçlão a robotica inclusiva', 'robotica'),
(14, 'Sem título', NULL, '2025-08-08 08:15:05', 3, 2, 2, 1, 'Conhecimento eletricos', 'robotica'),
(15, 'Sem título', NULL, '2025-08-08 08:16:30', 3, 2, 2, 1, 'Explorando arquivos', 'letramento_digital'),
(16, 'Sem título', NULL, '2025-08-08 09:44:28', 3, 2, 2, 1, 'internet', 'letramento_digital');

-- --------------------------------------------------------

--
-- Estrutura para tabela `escolas`
--

CREATE TABLE `escolas` (
  `id` int NOT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `endereco` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `escolas`
--

INSERT INTO `escolas` (`id`, `nome`, `endereco`) VALUES
(1, 'sme', 'rua santa teresa, 77'),
(2, 'Airton Ciraulo', NULL),
(3, 'Berenice Ribeiro', NULL),
(4, 'Assis Chateaubriand', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `presencas`
--

CREATE TABLE `presencas` (
  `id` int NOT NULL,
  `aula_id` int NOT NULL,
  `aluno_id` int NOT NULL,
  `presente` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `presencas`
--

INSERT INTO `presencas` (`id`, `aula_id`, `aluno_id`, `presente`) VALUES
(1, 6, 3, 0),
(2, 7, 3, 1),
(3, 9, 3, 0),
(4, 10, 1, 1),
(5, 10, 2, 1),
(6, 11, 4, 0),
(7, 12, 1, 1),
(8, 12, 2, 0),
(9, 13, 1, 1),
(10, 13, 2, 1),
(11, 14, 1, 0),
(12, 14, 2, 1),
(13, 15, 1, 1),
(14, 15, 2, 0),
(15, 16, 1, 1),
(16, 16, 2, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `professores`
--

CREATE TABLE `professores` (
  `id` int NOT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `escola_id` int DEFAULT NULL,
  `tipo` enum('admin','professor') DEFAULT 'professor'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `professores`
--

INSERT INTO `professores` (`id`, `nome`, `email`, `senha`, `escola_id`, `tipo`) VALUES
(1, 'Administrador', 'admin@admin.com', '$2y$10$eW5zvQ9kzYxg0U1JZkXKxeuKJzjzQYxYzYxYzYxYzYxYzYxYzYxYz', NULL, 'admin'),
(2, 'kenio', 'kenioele@gmail.com', '$2y$10$83864Nw6t9wAmvK3dQt9X.SHe14SbUpYLOdZd9WZCbxQllS5QEayW', 1, 'admin'),
(3, 'eronildes', 'teste@teste', '$2y$10$H8InwYnINt1GCA/7AD5T4.ewVTtmPRLYY9ujp6t64vg5GZYDGHa4W', 1, 'professor'),
(4, 'cleone', 'cleone@sme', '$2y$10$uQYansU1F9T235M.XWEY1eXPzMGhrgQ0bGvovIYfvM0Yt5dsHRaOW', 1, 'professor');

-- --------------------------------------------------------

--
-- Estrutura para tabela `series`
--

CREATE TABLE `series` (
  `id` int NOT NULL,
  `nome` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `series`
--

INSERT INTO `series` (`id`, `nome`) VALUES
(1, '1 ano'),
(2, '2 ano'),
(3, '3 ano'),
(4, '4 ano');

-- --------------------------------------------------------

--
-- Estrutura para tabela `turmas`
--

CREATE TABLE `turmas` (
  `id` int NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `escola_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `turmas`
--

INSERT INTO `turmas` (`id`, `nome`, `escola_id`) VALUES
(1, 'A', 1),
(2, 'A', 2),
(3, 'B', 2),
(4, 'A', 3),
(5, 'A', 4);

-- --------------------------------------------------------

--
-- Estrutura para tabela `turnos`
--

CREATE TABLE `turnos` (
  `id` int NOT NULL,
  `nome` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `alunos`
--
ALTER TABLE `alunos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `serie_id` (`serie_id`),
  ADD KEY `turma_id` (`turma_id`),
  ADD KEY `turno_id` (`turno_id`),
  ADD KEY `escola_id` (`escola_id`);

--
-- Índices de tabela `aulas`
--
ALTER TABLE `aulas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `professor_id` (`professor_id`),
  ADD KEY `escola_id` (`escola_id`),
  ADD KEY `turma_id` (`turma_id`);

--
-- Índices de tabela `escolas`
--
ALTER TABLE `escolas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `presencas`
--
ALTER TABLE `presencas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aula_id` (`aula_id`),
  ADD KEY `aluno_id` (`aluno_id`);

--
-- Índices de tabela `professores`
--
ALTER TABLE `professores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `escola_id` (`escola_id`);

--
-- Índices de tabela `series`
--
ALTER TABLE `series`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `turmas`
--
ALTER TABLE `turmas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `escola_id` (`escola_id`);

--
-- Índices de tabela `turnos`
--
ALTER TABLE `turnos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `alunos`
--
ALTER TABLE `alunos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `aulas`
--
ALTER TABLE `aulas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `escolas`
--
ALTER TABLE `escolas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `presencas`
--
ALTER TABLE `presencas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `professores`
--
ALTER TABLE `professores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `series`
--
ALTER TABLE `series`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `turmas`
--
ALTER TABLE `turmas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `turnos`
--
ALTER TABLE `turnos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `alunos`
--
ALTER TABLE `alunos`
  ADD CONSTRAINT `alunos_ibfk_1` FOREIGN KEY (`serie_id`) REFERENCES `series` (`id`),
  ADD CONSTRAINT `alunos_ibfk_2` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`),
  ADD CONSTRAINT `alunos_ibfk_3` FOREIGN KEY (`turno_id`) REFERENCES `turnos` (`id`),
  ADD CONSTRAINT `alunos_ibfk_4` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`);

--
-- Restrições para tabelas `aulas`
--
ALTER TABLE `aulas`
  ADD CONSTRAINT `aulas_ibfk_1` FOREIGN KEY (`professor_id`) REFERENCES `professores` (`id`),
  ADD CONSTRAINT `aulas_ibfk_2` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`),
  ADD CONSTRAINT `aulas_ibfk_3` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`);

--
-- Restrições para tabelas `presencas`
--
ALTER TABLE `presencas`
  ADD CONSTRAINT `presencas_ibfk_1` FOREIGN KEY (`aula_id`) REFERENCES `aulas` (`id`),
  ADD CONSTRAINT `presencas_ibfk_2` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`);

--
-- Restrições para tabelas `professores`
--
ALTER TABLE `professores`
  ADD CONSTRAINT `professores_ibfk_1` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`);

--
-- Restrições para tabelas `turmas`
--
ALTER TABLE `turmas`
  ADD CONSTRAINT `turmas_ibfk_1` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
