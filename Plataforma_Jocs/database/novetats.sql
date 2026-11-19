-- ============================================================================
-- Migració: CRUD de jocs + rols + sistema d'amics + estadístiques + badges
-- Base de dades: plataforma_videojocs (MariaDB 10.11) - Mòdul 5023
--
-- Es pot executar sobre la BD creada amb init.sql. És idempotent (es pot
-- executar diverses vegades). Ús:
--   mariadb -u plataforma_user -p plataforma_videojocs < database/novetats.sql
-- ============================================================================
USE plataforma_videojocs;

-- ----------------------------------------------------------------------------
-- 1. Usuaris: columna avatar (el codi ja la feia servir) i columna rol
--    VULNERABILITAT INTENCIONADA: el rol es desa codificat en Base64
--    usuari = dXN1YXJp | admin = YWRtaW4= | developer = ZGV2ZWxvcGVy
-- ----------------------------------------------------------------------------
ALTER TABLE usuaris ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) NULL;
ALTER TABLE usuaris ADD COLUMN IF NOT EXISTS rol VARCHAR(100) NOT NULL DEFAULT 'dXN1YXJp';

UPDATE usuaris SET rol = 'dXN1YXJp';                                   -- tothom: usuari
UPDATE usuaris SET rol = 'YWRtaW4='     WHERE nom_usuari = 'admin';    -- admin
UPDATE usuaris SET rol = 'ZGV2ZWxvcGVy' WHERE nom_usuari = 'retro_player'; -- developer

-- ----------------------------------------------------------------------------
-- 2. Jocs: propietari (developer) i fitxer HTML principal dels jocs pujats en ZIP
--    ruta_index = NULL  -> joc "de la plataforma" (index.php propi, p.ex. el joc 1)
--    ruta_index != NULL -> joc pujat en ZIP (public/js/jocs/<id>/<ruta_index>)
-- ----------------------------------------------------------------------------
ALTER TABLE jocs ADD COLUMN IF NOT EXISTS developer_id INT NULL;
ALTER TABLE jocs ADD COLUMN IF NOT EXISTS ruta_index VARCHAR(255) NULL;
ALTER TABLE jocs ADD COLUMN IF NOT EXISTS data_creacio DATETIME DEFAULT CURRENT_TIMESTAMP;

-- ----------------------------------------------------------------------------
-- 3. Partides: resultat (victoria / derrota)
--    Les partides existents són nivells superats, per tant 'victoria'.
-- ----------------------------------------------------------------------------
ALTER TABLE partides ADD COLUMN IF NOT EXISTS resultat VARCHAR(10) NOT NULL DEFAULT 'victoria';

-- ----------------------------------------------------------------------------
-- 4. Sistema d'amics
--    estat: pendent | acceptada | rebutjada
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS amistats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    solicitant_id INT NOT NULL,
    receptor_id INT NOT NULL,
    estat VARCHAR(10) NOT NULL DEFAULT 'pendent',
    data_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_resposta DATETIME NULL,
    UNIQUE KEY uq_amistat (solicitant_id, receptor_id),
    FOREIGN KEY (solicitant_id) REFERENCES usuaris(id) ON DELETE CASCADE,
    FOREIGN KEY (receptor_id) REFERENCES usuaris(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 5. Badges / assoliments
--    badges:         catàleg (tipus de criteri + llindar; joc_id NULL = qualsevol joc)
--    usuari_badges:  badges obtinguts per cada usuari
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS badges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codi VARCHAR(50) NOT NULL,
    nom VARCHAR(80) NOT NULL,
    descripcio VARCHAR(255) NOT NULL,
    icona VARCHAR(16) NOT NULL DEFAULT '🏆',
    tipus VARCHAR(30) NOT NULL,
    llindar INT NOT NULL DEFAULT 1,
    joc_id INT NULL,
    ordre INT NOT NULL DEFAULT 0,
    UNIQUE KEY uq_badge_codi (codi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuari_badges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuari_id INT NOT NULL,
    badge_id INT NOT NULL,
    data_obtencio DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usuari_badge (usuari_id, badge_id),
    FOREIGN KEY (usuari_id) REFERENCES usuaris(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Catàleg inicial (joc 1 = Nau vs Ovnis). Els badges es concedeixen sols quan
-- l'usuari juga o entra a /badges.
INSERT IGNORE INTO badges (codi, nom, descripcio, icona, tipus, llindar, joc_id, ordre) VALUES
('primer_contacte', 'Primer contacte', 'Juga la teva primera partida.', '👽', 'partides', 1, NULL, 10),
('pilot_constant', 'Pilot constant', 'Juga 10 partides.', '🚀', 'partides', 10, NULL, 20),
('veteran_espai', 'Veterà de l''espai', 'Juga 50 partides.', '🛰️', 'partides', 50, NULL, 30),
('primera_victoria', 'Primera victòria', 'Supera el teu primer nivell.', '🏁', 'victories', 1, NULL, 40),
('imparable', 'Imparable', 'Aconsegueix 10 victòries.', '🔥', 'victories', 10, NULL, 50),
('cacador_ovnis', 'Caçador d''ovnis', 'Suma 1.000 punts a Nau vs Ovnis.', '🎯', 'puntuacio_total', 1000, 1, 60),
('exterminador', 'Exterminador', 'Suma 5.000 punts a Nau vs Ovnis.', '💥', 'puntuacio_total', 5000, 1, 70),
('llegenda', 'Llegenda galàctica', 'Suma 20.000 punts entre tots els jocs.', '🌌', 'puntuacio_total', 20000, NULL, 80),
('bona_punteria', 'Bona punteria', 'Fes 1.000 punts en una sola partida.', '⭐', 'puntuacio_partida', 1000, NULL, 90),
('punteria_mestra', 'Punteria mestra', 'Fes 2.000 punts en una sola partida.', '🌟', 'puntuacio_partida', 2000, NULL, 100),
('segon_nivell', 'Pujant de nivell', 'Supera el nivell 2 a Nau vs Ovnis.', '📈', 'nivell', 2, 1, 110),
('defensor_terra', 'Defensor de la Terra', 'Completa tots els nivells de Nau vs Ovnis.', '🌍', 'joc_completat', 1, 1, 120),
('explorador', 'Explorador', 'Juga a 2 jocs diferents.', '🧭', 'jocs_diferents', 2, NULL, 130),
('maratonia', 'Maratonià', 'Acumula 1 hora de joc.', '⏱️', 'temps_total', 3600, NULL, 140),
('sociable', 'Sociable', 'Fes el teu primer amic.', '🤝', 'amics', 1, NULL, 150),
('popular', 'Popular', 'Tingues 3 amics.', '🎉', 'amics', 3, NULL, 160);

-- ----------------------------------------------------------------------------
-- 6. Dades de prova (INSERT IGNORE: no duplica si ja existeixen)
--    Usuaris (contrasenya de tots: 123456789a):
--      admin (1) = admin | gamer1 (2) = usuari | retro_player (3) = developer
-- ----------------------------------------------------------------------------
INSERT IGNORE INTO partides (id, usuari_id, joc_id, nivell_jugat, puntuacio_obtinguda, durada_segons, data_partida, resultat) VALUES
(6,  2, 1, 1, 250, 60,  '2026-02-16 10:00:00', 'victoria'),
(7,  2, 1, 2, 480, 120, '2026-02-17 10:30:00', 'victoria'),
(8,  2, 1, 3, 300, 95,  '2026-02-18 11:00:00', 'derrota'),
(9,  2, 2, 1, 410, 150, '2026-02-19 19:00:00', 'victoria'),
(10, 3, 1, 1, 260, 70,  '2026-02-16 18:00:00', 'victoria'),
(11, 3, 1, 3, 120, 40,  '2026-02-17 18:30:00', 'derrota'),
(12, 3, 2, 2, 600, 200, '2026-02-18 20:00:00', 'victoria'),
(13, 3, 3, 1, 220, 80,  '2026-02-19 21:00:00', 'victoria'),
(14, 1, 1, 2, 520, 130, '2026-02-17 09:00:00', 'victoria'),
(15, 1, 2, 1, 150, 55,  '2026-02-18 09:30:00', 'derrota');

INSERT IGNORE INTO amistats (id, solicitant_id, receptor_id, estat, data_solicitud, data_resposta) VALUES
(1, 2, 3, 'acceptada', '2026-02-15 12:00:00', '2026-02-15 12:30:00'),
(2, 1, 2, 'pendent',   '2026-02-20 09:00:00', NULL),
(3, 3, 1, 'acceptada', '2026-02-16 08:00:00', '2026-02-16 08:10:00');
