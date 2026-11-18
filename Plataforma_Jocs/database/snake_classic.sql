-- ============================================================================
-- Snake Classic · assoliments (badges) del joc
-- Només fa INSERT al catàleg `badges` que ja existeix (vegeu README de la
-- plataforma: "Per afegir-ne un de nou basta fer un INSERT a badges").
-- NO crea taules, NO toca credencials ni l'esquema.
--
-- NOTA: aquests badges també els insereix automàticament core/Migrator.php
-- (BADGES_PER_JOC) quan el joc existeix; aquest fitxer és l'alternativa manual.
--
-- PRERREQUISIT: el joc ja ha d'estar creat a "Gestió jocs" amb el nom exacte
-- "Snake Classic" (el seu id el genera la BD; els badges l'enllacen per nom).
-- És idempotent (INSERT IGNORE sobre uq_badge_codi). Ús:
--   mariadb -u plataforma_user -p plataforma_videojocs < database/snake_classic.sql
--
-- Puntuació de Snake: 1 punt per poma. Criteris usats (tots ja existents a
-- BadgeModel): partides | puntuacio_partida.
-- ============================================================================
SET NAMES utf8mb4;  -- imprescindible: sense això els emojis s'hi desen amb doble codificació
USE plataforma_videojocs;

SET @snake_id = (SELECT id FROM jocs WHERE nom_joc = 'Snake Classic' ORDER BY id LIMIT 1);

INSERT IGNORE INTO badges (codi, nom, descripcio, icona, tipus, llindar, joc_id, ordre)
SELECT * FROM (
  SELECT 'snake_primera'  AS codi, 'Primera mossegada' AS nom, 'Juga la teva primera partida de Snake.' AS descripcio, '🐍' AS icona, 'partides'          AS tipus, 1   AS llindar, @snake_id AS joc_id, 200 AS ordre UNION ALL
  SELECT 'snake_10_pomes',  'Gana de pomes',  'Menja 10 pomes en una partida de Snake.', '🍎', 'puntuacio_partida', 10,  @snake_id, 210 UNION ALL
  SELECT 'snake_50',        'Mig centenar',   'Fes 50 punts en una partida de Snake.',   '⭐', 'puntuacio_partida', 50,  @snake_id, 220 UNION ALL
  SELECT 'snake_100',       'Centenari',      'Fes 100 punts en una partida de Snake.',  '🏆', 'puntuacio_partida', 100, @snake_id, 230 UNION ALL
  SELECT 'snake_habitual',  'Serp habitual',  'Juga 10 partides de Snake.',              '🔁', 'partides',          10,  @snake_id, 240
) AS nous
WHERE @snake_id IS NOT NULL;
