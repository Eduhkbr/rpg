-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 28/06/2025 às 02:43
-- Versão do servidor: 10.11.10-MariaDB-log
-- Versão do PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `u880170041_central_rpg`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs_jogo`
--

CREATE TABLE `logs_jogo` (
                             `id` int(10) UNSIGNED NOT NULL,
                             `id_sala` int(10) UNSIGNED NOT NULL,
                             `autor_nome` varchar(50) NOT NULL,
                             `tipo_log` enum('mestre','jogador','sistema') NOT NULL,
                             `mensagem` text NOT NULL,
                             `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `participantes`
--

CREATE TABLE `participantes` (
                                 `id` int(10) UNSIGNED NOT NULL,
                                 `id_sala` int(10) UNSIGNED NOT NULL,
                                 `id_usuario` int(10) UNSIGNED NOT NULL,
                                 `id_personagem` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `personagens`
--

CREATE TABLE `personagens` (
                               `id` int(10) UNSIGNED NOT NULL,
                               `id_usuario` int(10) UNSIGNED NOT NULL,
                               `id_sistema` int(10) UNSIGNED NOT NULL,
                               `nome_personagem` varchar(100) NOT NULL,
                               `ficha_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`ficha_json`)),
                               `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `salas`
--

CREATE TABLE `salas` (
                         `id` int(10) UNSIGNED NOT NULL,
                         `id_mestre` int(10) UNSIGNED NOT NULL,
                         `id_sistema` int(10) UNSIGNED NOT NULL,
                         `nome_sala` varchar(100) NOT NULL,
                         `codigo_convite` varchar(8) NOT NULL,
                         `ativa` tinyint(1) NOT NULL DEFAULT 1,
                         `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `sistemas_rpg`
--

CREATE TABLE `sistemas_rpg` (
                                `id` int(10) UNSIGNED NOT NULL,
                                `nome_sistema` varchar(100) NOT NULL,
                                `descricao` text DEFAULT NULL,
                                `ficha_template_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Um modelo JSON para a criação de fichas de personagem neste sistema.' CHECK (json_valid(`ficha_template_json`)),
                                `id_criador` int(10) UNSIGNED DEFAULT NULL COMMENT 'Permite que usuários criem seus próprios sistemas no futuro.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                                                                                   (2, 'Fantasia Medieval', 'Um sistema clássico de masmorras, dragões, magia e aventura em reinos distantes.', '{\"nome_personagem\":\"\",\"raca\":\"\",\"classe\":\"\",\"nivel\":1,\"alinhamento\":\"\",\"idade\":0,\"sexo\":\"\",\"altura\":\"\",\"peso\":\"\",\"divindade\":\"\",\"historia_personal\":\"\",\"atributos\":{\"forca\":0,\"destreza\":0,\"constituicao\":0,\"inteligencia\":0,\"sabedoria\":0,\"carisma\":0},\"modificadores\":{\"forca\":0,\"destreza\":0,\"constituicao\":0,\"inteligencia\":0,\"sabedoria\":0,\"carisma\":0},\"vida\":{\"pontos_de_vida_maximo\":0,\"pontos_de_vida_atual\":0,\"classe_armadura\":0,\"iniciativa\":0,\"deslocamento\":\"9m\"},\"habilidades_classe\":[],\"pericias\":[],\"proficiencias\":{\"armas\":[],\"armaduras\":[],\"ferramentas\":[],\"idiomas\":[]},\"magias\":{\"espacos_magia_por_nivel\":{\"nivel_1\":0,\"nivel_2\":0,\"nivel_3\":0},\"magias_conhecidas\":[]},\"equipamentos\":[],\"moedas\":{\"ouro\":0,\"prata\":0,\"cobre\":0},\"notas\":\"\"}', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
                            `id` int(10) UNSIGNED NOT NULL,
                            `nome_usuario` varchar(50) NOT NULL,
                            `email` varchar(255) NOT NULL,
                            `senha_hash` varchar(255) NOT NULL,
                            `codigo_verificacao` varchar(10) DEFAULT NULL,
                            `email_verificado` tinyint(1) NOT NULL DEFAULT 0,
                            `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `logs_jogo`
--
ALTER TABLE `logs_jogo`
    ADD PRIMARY KEY (`id`),
  ADD KEY `fk_logs_jogo_salas_idx` (`id_sala`);

--
-- Índices de tabela `participantes`
--
ALTER TABLE `participantes`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sala_usuario_UNIQUE` (`id_sala`,`id_usuario`),
  ADD KEY `fk_participantes_salas_idx` (`id_sala`),
  ADD KEY `fk_participantes_usuarios_idx` (`id_usuario`),
  ADD KEY `fk_participantes_personagens_idx` (`id_personagem`);

--
-- Índices de tabela `personagens`
--
ALTER TABLE `personagens`
    ADD PRIMARY KEY (`id`),
  ADD KEY `fk_personagens_usuarios_idx` (`id_usuario`),
  ADD KEY `fk_personagens_sistemas_idx` (`id_sistema`);

--
-- Índices de tabela `salas`
--
ALTER TABLE `salas`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_convite_UNIQUE` (`codigo_convite`),
  ADD KEY `fk_salas_usuarios_idx` (`id_mestre`),
  ADD KEY `fk_salas_sistemas_idx` (`id_sistema`);

--
-- Índices de tabela `sistemas_rpg`
--
ALTER TABLE `sistemas_rpg`
    ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sistemas_usuarios_idx` (`id_criador`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_UNIQUE` (`email`),
  ADD UNIQUE KEY `nome_usuario_UNIQUE` (`nome_usuario`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `logs_jogo`
--
ALTER TABLE `logs_jogo`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `participantes`
--
ALTER TABLE `participantes`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `personagens`
--
ALTER TABLE `personagens`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `salas`
--
ALTER TABLE `salas`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `sistemas_rpg`
--
ALTER TABLE `sistemas_rpg`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `logs_jogo`
--
ALTER TABLE `logs_jogo`
    ADD CONSTRAINT `fk_logs_jogo_salas` FOREIGN KEY (`id_sala`) REFERENCES `salas` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Restrições para tabelas `participantes`
--
ALTER TABLE `participantes`
    ADD CONSTRAINT `fk_participantes_personagens` FOREIGN KEY (`id_personagem`) REFERENCES `personagens` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_participantes_salas` FOREIGN KEY (`id_sala`) REFERENCES `salas` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_participantes_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Restrições para tabelas `personagens`
--
ALTER TABLE `personagens`
    ADD CONSTRAINT `fk_personagens_sistemas` FOREIGN KEY (`id_sistema`) REFERENCES `sistemas_rpg` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_personagens_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Restrições para tabelas `salas`
--
ALTER TABLE `salas`
    ADD CONSTRAINT `fk_salas_mestre` FOREIGN KEY (`id_mestre`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_salas_sistemas` FOREIGN KEY (`id_sistema`) REFERENCES `sistemas_rpg` (`id`) ON UPDATE NO ACTION;

--
-- Restrições para tabelas `sistemas_rpg`
--
ALTER TABLE `sistemas_rpg`
    ADD CONSTRAINT `fk_sistemas_usuarios` FOREIGN KEY (`id_criador`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;