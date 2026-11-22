-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: db:3306
-- Tiempo de generación: 22-11-2026 a las 17:21:21
-- Versión del servidor: 10.11.19-MariaDB-ubu2204
-- Versión de PHP: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `plataforma_videojocs`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `amistats`
--

CREATE TABLE `amistats` (
  `id` int(11) NOT NULL,
  `solicitant_id` int(11) NOT NULL,
  `receptor_id` int(11) NOT NULL,
  `estat` varchar(10) NOT NULL DEFAULT 'pendent',
  `data_solicitud` datetime DEFAULT current_timestamp(),
  `data_resposta` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `amistats`
--

INSERT INTO `amistats` (`id`, `solicitant_id`, `receptor_id`, `estat`, `data_solicitud`, `data_resposta`) VALUES
(1, 2, 3, 'acceptada', '2026-02-15 12:00:00', '2026-02-15 12:30:00'),
(2, 1, 2, 'pendent', '2026-02-20 09:00:00', NULL),
(3, 3, 1, 'acceptada', '2026-02-16 08:00:00', '2026-02-16 08:10:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `badges`
--

CREATE TABLE `badges` (
  `id` int(11) NOT NULL,
  `codi` varchar(50) NOT NULL,
  `nom` varchar(80) NOT NULL,
  `descripcio` varchar(255) NOT NULL,
  `icona` varchar(16) NOT NULL DEFAULT '?',
  `tipus` varchar(30) NOT NULL,
  `llindar` int(11) NOT NULL DEFAULT 1,
  `joc_id` int(11) DEFAULT NULL,
  `ordre` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `badges`
--

INSERT INTO `badges` (`id`, `codi`, `nom`, `descripcio`, `icona`, `tipus`, `llindar`, `joc_id`, `ordre`) VALUES
(1, 'primer_contacte', 'Primer contacte', 'Juga la teva primera partida.', '👽', 'partides', 1, NULL, 10),
(2, 'pilot_constant', 'Pilot constant', 'Juga 10 partides.', '🚀', 'partides', 10, NULL, 20),
(3, 'veteran_espai', 'Veterà de l\'espai', 'Juga 50 partides.', '🛰️', 'partides', 50, NULL, 30),
(4, 'primera_victoria', 'Primera victòria', 'Supera el teu primer nivell.', '🏁', 'victories', 1, NULL, 40),
(5, 'imparable', 'Imparable', 'Aconsegueix 10 victòries.', '🔥', 'victories', 10, NULL, 50),
(6, 'cacador_ovnis', 'Caçador d\'ovnis', 'Suma 1.000 punts a Nau vs Ovnis.', '🎯', 'puntuacio_total', 1000, 1, 60),
(7, 'exterminador', 'Exterminador', 'Suma 5.000 punts a Nau vs Ovnis.', '💥', 'puntuacio_total', 5000, 1, 70),
(8, 'llegenda', 'Llegenda galàctica', 'Suma 20.000 punts entre tots els jocs.', '🌌', 'puntuacio_total', 20000, NULL, 80),
(9, 'bona_punteria', 'Bona punteria', 'Fes 1.000 punts en una sola partida.', '⭐', 'puntuacio_partida', 1000, NULL, 90),
(10, 'punteria_mestra', 'Punteria mestra', 'Fes 2.000 punts en una sola partida.', '🌟', 'puntuacio_partida', 2000, NULL, 100),
(11, 'segon_nivell', 'Pujant de nivell', 'Supera el nivell 2 a Nau vs Ovnis.', '📈', 'nivell', 2, 1, 110),
(12, 'defensor_terra', 'Defensor de la Terra', 'Completa tots els nivells de Nau vs Ovnis.', '🌍', 'joc_completat', 1, 1, 120),
(13, 'explorador', 'Explorador', 'Juga a 2 jocs diferents.', '🧭', 'jocs_diferents', 2, NULL, 130),
(14, 'maratonia', 'Maratonià', 'Acumula 1 hora de joc.', '⏱️', 'temps_total', 3600, NULL, 140),
(15, 'sociable', 'Sociable', 'Fes el teu primer amic.', '🤝', 'amics', 1, NULL, 150),
(16, 'popular', 'Popular', 'Tingues 3 amics.', '🎉', 'amics', 3, NULL, 160),
(17, 'snake_primera', 'Primera mossegada', 'Juga la teva primera partida de Snake.', '🐍', 'partides', 1, 3, 200),
(18, 'snake_10_pomes', 'Gana de pomes', 'Menja 10 pomes en una partida de Snake.', '🍎', 'puntuacio_partida', 10, 3, 210),
(19, 'snake_50', 'Mig centenar', 'Fes 50 punts en una partida de Snake.', '⭐', 'puntuacio_partida', 50, 3, 220),
(20, 'snake_100', 'Centenari', 'Fes 100 punts en una partida de Snake.', '🏆', 'puntuacio_partida', 100, 3, 230),
(21, 'snake_habitual', 'Serp habitual', 'Juga 10 partides de Snake.', '🔁', 'partides', 10, 3, 240);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jocs`
--

CREATE TABLE `jocs` (
  `id` int(11) NOT NULL,
  `nom_joc` varchar(50) NOT NULL,
  `descripcio` text DEFAULT NULL,
  `puntuacio_maxima` int(11) DEFAULT 0,
  `nivells_totals` int(11) DEFAULT 1,
  `actiu` tinyint(1) DEFAULT 1,
  `developer_id` int(11) DEFAULT NULL,
  `ruta_index` varchar(255) DEFAULT NULL,
  `data_creacio` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `jocs`
--

INSERT INTO `jocs` (`id`, `nom_joc`, `descripcio`, `puntuacio_maxima`, `nivells_totals`, `actiu`, `developer_id`, `ruta_index`, `data_creacio`) VALUES
(1, 'Naus vs Ovnis', 'Joc arcade espacial en 2D. Destrueix els ovnis amb projectils abans que xoquin amb la nau.', 1500, 5, 1, NULL, NULL, '2026-11-18 20:12:10'),
(2, 'Horse Race', 'Joc de cursa de cavalls on pots apostar pel guanyador.', 850, 1, 1, NULL, NULL, '2026-11-18 20:12:10'),
(3, 'Snake Classic', 'Joc clàssic de la serp.', 850, 1, 1, NULL, 'index.html', '2026-11-18 20:12:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nivells_joc`
--

CREATE TABLE `nivells_joc` (
  `id` int(11) NOT NULL,
  `joc_id` int(11) NOT NULL,
  `nivell` int(11) NOT NULL,
  `nom_nivell` varchar(50) DEFAULT NULL,
  `configuracio_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`configuracio_json`)),
  `puntuacio_minima` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `nivells_joc`
--

INSERT INTO `nivells_joc` (`id`, `joc_id`, `nivell`, `nom_nivell`, `configuracio_json`, `puntuacio_minima`) VALUES
(1, 1, 1, 'Iniciacio Espacial', '{\"id\": 1, \"nom\": \"Naus vs Ovnis\", \"nivell\": 1, \"vides\": 3, \"maxProjectils\": 10, \"maxEnemics\": 5, \"velocitatEnemics\": 1, \"puntsNivell\": 250}', 0),
(2, 1, 2, 'Cinturo d\'Asteroides', '{\"id\": 1, \"nom\": \"Naus vs Ovnis\", \"nivell\": 2, \"vides\": 3, \"maxProjectils\": 8, \"maxEnemics\": 10, \"velocitatEnemics\": 2, \"puntsNivell\": 500}', 250),
(3, 1, 3, 'Invasio Alienigena', '{\"id\": 1, \"nom\": \"Naus vs Ovnis\", \"nivell\": 3, \"vides\": 3, \"maxProjectils\": 6, \"maxEnemics\": 16, \"velocitatEnemics\": 6, \"puntsNivell\": 800}', 500),
(4, 1, 4, 'DLC 1', '{\"id\": 1, \"nom\": \"Naus vs Ovnis\", \"nivell\": 4, \"vides\": 4, \"maxProjectils\": 6, \"maxEnemics\": 32, \"velocitatEnemics\": 10, \"puntsNivell\": 1100}', 500),
(5, 1, 5, 'DLC 2 (repte final)', '{\"id\": 1, \"nom\": \"Naus vs Ovnis\", \"nivell\": 5, \"vides\": 7, \"maxProjectils\": 6, \"maxEnemics\": 40, \"velocitatEnemics\": 20, \"puntsNivell\": 2000}', 500),
(6, 2, 1, 'Cursa cavalls', '{\"id\": 1, \"nom\": \"Cursa cavalls\", \"nivell\": 1, \"puntsNivell\": 500}', 500);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partides`
--

CREATE TABLE `partides` (
  `id` int(11) NOT NULL,
  `usuari_id` int(11) NOT NULL,
  `joc_id` int(11) NOT NULL,
  `nivell_jugat` int(11) NOT NULL,
  `puntuacio_obtinguda` int(11) NOT NULL,
  `data_partida` datetime DEFAULT current_timestamp(),
  `durada_segons` int(11) DEFAULT 0,
  `resultat` varchar(10) NOT NULL DEFAULT 'victoria'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `partides`
--

INSERT INTO `partides` (`id`, `usuari_id`, `joc_id`, `nivell_jugat`, `puntuacio_obtinguda`, `data_partida`, `durada_segons`, `resultat`) VALUES
(1, 2, 1, 3, 1500, '2026-02-10 14:20:00', 180, 'victoria'),
(2, 3, 1, 2, 950, '2026-02-11 18:05:00', 140, 'victoria'),
(3, 1, 1, 1, 450, '2026-02-12 09:40:00', 85, 'victoria'),
(4, 2, 2, 2, 850, '2026-02-13 17:15:00', 210, 'victoria'),
(5, 3, 2, 1, 320, '2026-02-14 20:30:00', 95, 'victoria'),
(6, 2, 1, 1, 250, '2026-02-16 10:00:00', 60, 'victoria'),
(7, 2, 1, 2, 480, '2026-02-17 10:30:00', 120, 'victoria'),
(8, 2, 1, 3, 300, '2026-02-18 11:00:00', 95, 'derrota'),
(9, 4, 1, 1, 300, '2026-11-13 14:35:41', 28, 'victoria'),
(10, 4, 1, 2, 500, '2026-11-13 14:36:21', 38, 'victoria'),
(11, 4, 1, 1, 300, '2026-11-13 14:40:24', 28, 'victoria'),
(12, 4, 1, 2, 500, '2026-11-13 14:41:08', 38, 'victoria'),
(13, 4, 1, 3, 800, '2026-11-13 14:42:21', 53, 'victoria'),
(14, 4, 1, 3, 800, '2026-11-13 14:49:40', 53, 'victoria'),
(15, 4, 1, 4, 1100, '2026-11-13 14:50:52', 68, 'victoria'),
(16, 4, 1, 1, 300, '2026-11-13 14:59:04', 41, 'victoria'),
(17, 4, 1, 2, 500, '2026-11-13 15:11:26', 36, 'victoria'),
(18, 4, 1, 3, 800, '2026-11-13 15:12:44', 44, 'victoria'),
(19, 4, 1, 4, 1100, '2026-11-13 15:13:49', 62, 'victoria'),
(20, 4, 3, 1, 16, '2026-11-18 20:35:28', 46, 'derrota');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `progres_usuari`
--

CREATE TABLE `progres_usuari` (
  `id` int(11) NOT NULL,
  `usuari_id` int(11) NOT NULL,
  `joc_id` int(11) NOT NULL,
  `nivell_actual` int(11) DEFAULT 1,
  `puntuacio_maxima` int(11) DEFAULT 0,
  `partides_jugades` int(11) DEFAULT 0,
  `ultima_partida` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `progres_usuari`
--

INSERT INTO `progres_usuari` (`id`, `usuari_id`, `joc_id`, `nivell_actual`, `puntuacio_maxima`, `partides_jugades`, `ultima_partida`) VALUES
(1, 1, 1, 1, 450, 1, '2026-02-12 09:40:00'),
(2, 2, 1, 3, 1500, 3, '2026-02-10 14:20:00'),
(3, 3, 1, 2, 950, 2, '2026-02-11 18:05:00'),
(4, 2, 2, 2, 850, 2, '2026-02-13 17:15:00'),
(5, 3, 2, 1, 320, 1, '2026-02-14 20:30:00'),
(9, 4, 1, 5, 1100, 4, '2026-11-13 15:13:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuaris`
--

CREATE TABLE `usuaris` (
  `id` int(11) NOT NULL,
  `nom_usuari` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nom_complet` varchar(100) DEFAULT NULL,
  `data_registre` datetime DEFAULT current_timestamp(),
  `avatar` text DEFAULT NULL,
  `rol` varchar(100) NOT NULL DEFAULT 'dXN1YXJp'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuaris`
--

INSERT INTO `usuaris` (`id`, `nom_usuari`, `email`, `password_hash`, `nom_complet`, `data_registre`, `avatar`, `rol`) VALUES
(1, 'admin', 'admin@vallbona.cat', '83422503bcfc01d303030e8a7cc80efc', 'Administrador Vallbona', '2026-01-10 10:00:00', NULL, 'YWRtaW4='),
(2, 'gamer1', 'laura.gamer@vallbona.cat', '83422503bcfc01d303030e8a7cc80efc', 'Laura Martinez', '2026-01-15 11:30:00', NULL, 'dXN1YXJp'),
(3, 'retro_player', 'marc.retro@vallbona.cat', '83422503bcfc01d303030e8a7cc80efc', 'Marc Vila', '2026-02-01 16:45:00', NULL, 'ZGV2ZWxvcGVy'),
(4, 'aaronsio', 'aaronsio@test.cat', '81dc9bdb52d04dc20036dbd8313ed055', 'aaron', '2026-11-02 14:26:53', 'public/uploads/4.webp', 'dXN1YXJp');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuari_badges`
--

CREATE TABLE `usuari_badges` (
  `id` int(11) NOT NULL,
  `usuari_id` int(11) NOT NULL,
  `badge_id` int(11) NOT NULL,
  `data_obtencio` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuari_badges`
--

INSERT INTO `usuari_badges` (`id`, `usuari_id`, `badge_id`, `data_obtencio`) VALUES
(1, 4, 1, '2026-11-18 20:35:28'),
(2, 4, 2, '2026-11-18 20:35:28'),
(3, 4, 4, '2026-11-18 20:35:28'),
(4, 4, 5, '2026-11-18 20:35:28'),
(5, 4, 6, '2026-11-18 20:35:28'),
(6, 4, 7, '2026-11-18 20:35:28'),
(7, 4, 9, '2026-11-18 20:35:28'),
(8, 4, 11, '2026-11-18 20:35:28'),
(9, 4, 13, '2026-11-18 20:35:28'),
(10, 4, 17, '2026-11-18 20:35:28'),
(11, 4, 18, '2026-11-18 20:35:28');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `amistats`
--
ALTER TABLE `amistats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_amistat` (`solicitant_id`,`receptor_id`),
  ADD KEY `receptor_id` (`receptor_id`);

--
-- Indices de la tabla `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_badge_codi` (`codi`);

--
-- Indices de la tabla `jocs`
--
ALTER TABLE `jocs`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `nivells_joc`
--
ALTER TABLE `nivells_joc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `joc_id` (`joc_id`);

--
-- Indices de la tabla `partides`
--
ALTER TABLE `partides`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuari_id` (`usuari_id`),
  ADD KEY `joc_id` (`joc_id`);

--
-- Indices de la tabla `progres_usuari`
--
ALTER TABLE `progres_usuari`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuari_id` (`usuari_id`),
  ADD KEY `joc_id` (`joc_id`);

--
-- Indices de la tabla `usuaris`
--
ALTER TABLE `usuaris`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom_usuari` (`nom_usuari`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `avatar` (`avatar`) USING HASH;

--
-- Indices de la tabla `usuari_badges`
--
ALTER TABLE `usuari_badges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuari_badge` (`usuari_id`,`badge_id`),
  ADD KEY `badge_id` (`badge_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `amistats`
--
ALTER TABLE `amistats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `badges`
--
ALTER TABLE `badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `jocs`
--
ALTER TABLE `jocs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `nivells_joc`
--
ALTER TABLE `nivells_joc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `partides`
--
ALTER TABLE `partides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `progres_usuari`
--
ALTER TABLE `progres_usuari`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `usuaris`
--
ALTER TABLE `usuaris`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `usuari_badges`
--
ALTER TABLE `usuari_badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `amistats`
--
ALTER TABLE `amistats`
  ADD CONSTRAINT `amistats_ibfk_1` FOREIGN KEY (`solicitant_id`) REFERENCES `usuaris` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `amistats_ibfk_2` FOREIGN KEY (`receptor_id`) REFERENCES `usuaris` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `nivells_joc`
--
ALTER TABLE `nivells_joc`
  ADD CONSTRAINT `nivells_joc_ibfk_1` FOREIGN KEY (`joc_id`) REFERENCES `jocs` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `partides`
--
ALTER TABLE `partides`
  ADD CONSTRAINT `partides_ibfk_1` FOREIGN KEY (`usuari_id`) REFERENCES `usuaris` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `partides_ibfk_2` FOREIGN KEY (`joc_id`) REFERENCES `jocs` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `progres_usuari`
--
ALTER TABLE `progres_usuari`
  ADD CONSTRAINT `progres_usuari_ibfk_1` FOREIGN KEY (`usuari_id`) REFERENCES `usuaris` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `progres_usuari_ibfk_2` FOREIGN KEY (`joc_id`) REFERENCES `jocs` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuari_badges`
--
ALTER TABLE `usuari_badges`
  ADD CONSTRAINT `usuari_badges_ibfk_1` FOREIGN KEY (`usuari_id`) REFERENCES `usuaris` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuari_badges_ibfk_2` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
