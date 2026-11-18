<?php
/**
 * Migrador automàtic de l'esquema (equivalent a database/novetats.sql).
 *
 * Per què existeix: si la BD no té les taules/columnes de l'ampliació (amistats,
 * badges, partides.resultat, usuaris.rol...), pàgines com /amics, /estadistiques
 * o /badges fallen amb "Base table or view not found" / "Unknown column". Aquest
 * migrador les crea sol la primera vegada que es connecta la plataforma.
 *
 *  - És idempotent: si tot existeix només fa 2 consultes lleugeres a information_schema.
 *  - Funciona amb MariaDB i MySQL (no usa "ADD COLUMN IF NOT EXISTS").
 *  - Si no pot crear una taula amb claus foranes (error 150, tipus d'id diferents
 *    al init.sql original), la torna a crear sense les claus foranes.
 *  - Mai trenca la web: qualsevol error es registra amb error_log() i s'ignora.
 */
class Migrator
{
    private static $executat = false;

    // Taules noves que l'ampliació necessita
    private const TAULES = ['amistats', 'badges', 'usuari_badges'];

    // Columnes que l'ampliació espera: taula => [columna => definició]
    private const COLUMNES = [
        'usuaris' => [
            'avatar' => 'VARCHAR(255) NULL',
            'rol' => "VARCHAR(100) NOT NULL DEFAULT 'dXN1YXJp'",
        ],
        'jocs' => [
            'developer_id' => 'INT NULL',
            'ruta_index' => 'VARCHAR(255) NULL',
            'data_creacio' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
        ],
        'partides' => [
            'resultat' => "VARCHAR(10) NOT NULL DEFAULT 'victoria'",
        ],
    ];

    // Catàleg inicial de badges: [codi, nom, descripció, icona, tipus, llindar, joc_id, ordre]
    // Tipus: partides | victories | puntuacio_total | puntuacio_partida | nivell
    //        | joc_completat | jocs_diferents | temps_total | amics
    // joc_id NULL = val per a qualsevol joc; joc_id = 1 és "Nau vs Ovnis".
    public const BADGES = [
        ['primer_contacte',  'Primer contacte',     "Juga la teva primera partida.",                    '👽', 'partides',          1,     null, 10],
        ['pilot_constant',   'Pilot constant',      "Juga 10 partides.",                                '🚀', 'partides',          10,    null, 20],
        ['veteran_espai',    'Veterà de l\'espai',  "Juga 50 partides.",                                '🛰️', 'partides',          50,    null, 30],
        ['primera_victoria', 'Primera victòria',    "Supera el teu primer nivell.",                     '🏁', 'victories',         1,     null, 40],
        ['imparable',        'Imparable',           "Aconsegueix 10 victòries.",                        '🔥', 'victories',         10,    null, 50],
        ['cacador_ovnis',    'Caçador d\'ovnis',    "Suma 1.000 punts a Nau vs Ovnis.",                 '🎯', 'puntuacio_total',   1000,  1,    60],
        ['exterminador',     'Exterminador',        "Suma 5.000 punts a Nau vs Ovnis.",                 '💥', 'puntuacio_total',   5000,  1,    70],
        ['llegenda',         'Llegenda galàctica',  "Suma 20.000 punts entre tots els jocs.",           '🌌', 'puntuacio_total',   20000, null, 80],
        ['bona_punteria',    'Bona punteria',       "Fes 1.000 punts en una sola partida.",             '⭐', 'puntuacio_partida', 1000,  null, 90],
        ['punteria_mestra',  'Punteria mestra',     "Fes 2.000 punts en una sola partida.",             '🌟', 'puntuacio_partida', 2000,  null, 100],
        ['segon_nivell',     'Pujant de nivell',    "Supera el nivell 2 a Nau vs Ovnis.",               '📈', 'nivell',            2,     1,    110],
        ['defensor_terra',   'Defensor de la Terra', "Completa tots els nivells de Nau vs Ovnis.",     '🌍', 'joc_completat',     1,     1,    120],
        ['explorador',       'Explorador',          "Juga a 2 jocs diferents.",                         '🧭', 'jocs_diferents',    2,     null, 130],
        ['maratonia',        'Maratonià',           "Acumula 1 hora de joc.",                           '⏱️', 'temps_total',       3600,  null, 140],
        ['sociable',         'Sociable',            "Fes el teu primer amic.",                          '🤝', 'amics',             1,     null, 150],
        ['popular',          'Popular',             "Tingues 3 amics.",                                 '🎉', 'amics',             3,     null, 160],
    ];

    // Badges de jocs concrets (equivalent a database/snake_classic.sql).
    // Clau = nom EXACTE del joc a la taula `jocs`; l'id el genera la BD, per això
    // s'enllacen per nom. Només s'insereixen quan el joc ja existeix a "Gestió jocs".
    // Cada badge: [codi, nom, descripció, icona, tipus, llindar, ordre]
    public const BADGES_PER_JOC = [
        'Snake Classic' => [
            ['snake_primera', 'Primera mossegada', 'Juga la teva primera partida de Snake.',   '🐍', 'partides',          1,   200],
            ['snake_10_pomes', 'Gana de pomes',    'Menja 10 pomes en una partida de Snake.',  '🍎', 'puntuacio_partida', 10,  210],
            ['snake_50',       'Mig centenar',     'Fes 50 punts en una partida de Snake.',    '⭐', 'puntuacio_partida', 50,  220],
            ['snake_100',      'Centenari',        'Fes 100 punts en una partida de Snake.',   '🏆', 'puntuacio_partida', 100, 230],
            ['snake_habitual', 'Serp habitual',    'Juga 10 partides de Snake.',               '🔁', 'partides',          10,  240],
        ],
    ];

    public static function assegurar(PDO $pdo)
    {
        if (self::$executat) {
            return;
        }
        self::$executat = true;

        try {
            $columnesFaltants = self::columnesFaltants($pdo);
            $taulesFaltants = self::taulesFaltants($pdo);
            if ($columnesFaltants || $taulesFaltants) {
                self::aplicar($pdo, $columnesFaltants, $taulesFaltants);
            }
            // Badges d'altres jocs (Snake...): s'afegeixen sols quan el joc ja existeix
            self::badgesDeJocs($pdo);
        } catch (Throwable $e) {
            error_log('[Migrator] No s\'ha pogut actualitzar l\'esquema: ' . $e->getMessage());
        }
    }

    // Retorna [taula => [columna, ...]] amb les columnes que encara no existeixen
    private static function columnesFaltants(PDO $pdo)
    {
        $stmt = $pdo->query(
            "SELECT table_name AS t, column_name AS c FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name IN ('usuaris', 'jocs', 'partides')"
        );
        $existents = [];
        $taulesPresents = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $f) {
            $t = strtolower($f['t']);
            $taulesPresents[$t] = true;
            $existents[$t][strtolower($f['c'])] = true;
        }

        $faltants = [];
        foreach (self::COLUMNES as $taula => $columnes) {
            if (!isset($taulesPresents[$taula])) {
                continue; // la taula base no existeix: cal executar abans el init.sql
            }
            foreach ($columnes as $col => $def) {
                if (!isset($existents[$taula][$col])) {
                    $faltants[$taula][] = $col;
                }
            }
        }
        return $faltants;
    }

    private static function taulesFaltants(PDO $pdo)
    {
        $llista = "'" . implode("', '", self::TAULES) . "'";
        $stmt = $pdo->query(
            "SELECT table_name FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name IN ($llista)"
        );
        $presents = array_map('strtolower', $stmt->fetchAll(PDO::FETCH_COLUMN));
        return array_values(array_diff(self::TAULES, $presents));
    }

    private static function aplicar(PDO $pdo, array $columnesFaltants, array $taulesFaltants)
    {
        foreach ($columnesFaltants as $taula => $columnes) {
            foreach ($columnes as $col) {
                self::executar($pdo, "ALTER TABLE $taula ADD COLUMN $col " . self::COLUMNES[$taula][$col]);
            }
        }

        // Rols (Base64): usuari = dXN1YXJp | admin = YWRtaW4= | developer = ZGV2ZWxvcGVy
        if (isset($columnesFaltants['usuaris']) && in_array('rol', $columnesFaltants['usuaris'], true)) {
            self::executar($pdo, "UPDATE usuaris SET rol = 'dXN1YXJp'");
            self::executar($pdo, "UPDATE usuaris SET rol = 'YWRtaW4=' WHERE nom_usuari = 'admin'");
            self::executar($pdo, "UPDATE usuaris SET rol = 'ZGV2ZWxvcGVy' WHERE nom_usuari = 'retro_player'");
        }

        if (in_array('amistats', $taulesFaltants, true)) {
            self::crearTaula(
                $pdo,
                "CREATE TABLE IF NOT EXISTS amistats (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    solicitant_id INT NOT NULL,
                    receptor_id INT NOT NULL,
                    estat VARCHAR(10) NOT NULL DEFAULT 'pendent',
                    data_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
                    data_resposta DATETIME NULL,
                    UNIQUE KEY uq_amistat (solicitant_id, receptor_id)
                    %FK%
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                ", FOREIGN KEY (solicitant_id) REFERENCES usuaris(id) ON DELETE CASCADE,
                   FOREIGN KEY (receptor_id) REFERENCES usuaris(id) ON DELETE CASCADE"
            );
            self::executar($pdo,
                "INSERT IGNORE INTO amistats (id, solicitant_id, receptor_id, estat, data_solicitud, data_resposta) VALUES
                 (1, 2, 3, 'acceptada', '2026-02-15 12:00:00', '2026-02-15 12:30:00'),
                 (2, 1, 2, 'pendent',   '2026-02-20 09:00:00', NULL),
                 (3, 3, 1, 'acceptada', '2026-02-16 08:00:00', '2026-02-16 08:10:00')");
        }

        if (in_array('badges', $taulesFaltants, true)) {
            self::crearTaula(
                $pdo,
                "CREATE TABLE IF NOT EXISTS badges (
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                ''
            );
            foreach (self::BADGES as $b) {
                try {
                    $stmt = $pdo->prepare(
                        "INSERT IGNORE INTO badges (codi, nom, descripcio, icona, tipus, llindar, joc_id, ordre)
                         VALUES (:codi, :nom, :descripcio, :icona, :tipus, :llindar, :joc, :ordre)"
                    );
                    $stmt->execute([
                        'codi' => $b[0], 'nom' => $b[1], 'descripcio' => $b[2], 'icona' => $b[3],
                        'tipus' => $b[4], 'llindar' => $b[5], 'joc' => $b[6], 'ordre' => $b[7],
                    ]);
                } catch (PDOException $e) {
                    error_log('[Migrator] badge ' . $b[0] . ': ' . $e->getMessage());
                }
            }
        }

        if (in_array('usuari_badges', $taulesFaltants, true)) {
            self::crearTaula(
                $pdo,
                "CREATE TABLE IF NOT EXISTS usuari_badges (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    usuari_id INT NOT NULL,
                    badge_id INT NOT NULL,
                    data_obtencio DATETIME DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY uq_usuari_badge (usuari_id, badge_id)
                    %FK%
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                ", FOREIGN KEY (usuari_id) REFERENCES usuaris(id) ON DELETE CASCADE,
                   FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE"
            );
        }

        // Partides de prova per a les estadístiques (només si s'acaba d'afegir 'resultat')
        if (isset($columnesFaltants['partides']) && in_array('resultat', $columnesFaltants['partides'], true)) {
            self::executar($pdo,
                "INSERT IGNORE INTO partides (id, usuari_id, joc_id, nivell_jugat, puntuacio_obtinguda, durada_segons, data_partida, resultat) VALUES
                 (6,  2, 1, 1, 250, 60,  '2026-02-16 10:00:00', 'victoria'),
                 (7,  2, 1, 2, 480, 120, '2026-02-17 10:30:00', 'victoria'),
                 (8,  2, 1, 3, 300, 95,  '2026-02-18 11:00:00', 'derrota'),
                 (9,  2, 2, 1, 410, 150, '2026-02-19 19:00:00', 'victoria'),
                 (10, 3, 1, 1, 260, 70,  '2026-02-16 18:00:00', 'victoria'),
                 (11, 3, 1, 3, 120, 40,  '2026-02-17 18:30:00', 'derrota'),
                 (12, 3, 2, 2, 600, 200, '2026-02-18 20:00:00', 'victoria'),
                 (13, 3, 3, 1, 220, 80,  '2026-02-19 21:00:00', 'victoria'),
                 (14, 1, 1, 2, 520, 130, '2026-02-17 09:00:00', 'victoria'),
                 (15, 1, 2, 1, 150, 55,  '2026-02-18 09:30:00', 'derrota')");
        }
    }

    // Insereix els badges de BADGES_PER_JOC dels jocs que ja existeixen i encara no
    // els tenen. Idempotent: si tot hi és, només fa 1 consulta lleugera.
    private static function badgesDeJocs(PDO $pdo)
    {
        foreach (self::BADGES_PER_JOC as $nomJoc => $badges) {
            try {
                $codis = array_map(static fn($b) => $b[0], $badges);
                $marcadors = implode(',', array_fill(0, count($codis), '?'));

                $stmt = $pdo->prepare("SELECT COUNT(*) FROM badges WHERE codi IN ($marcadors)");
                $stmt->execute($codis);
                if ((int) $stmt->fetchColumn() >= count($codis)) {
                    continue; // ja estan tots
                }

                $stmt = $pdo->prepare("SELECT id FROM jocs WHERE nom_joc = ? ORDER BY id LIMIT 1");
                $stmt->execute([$nomJoc]);
                $jocId = $stmt->fetchColumn();
                if ($jocId === false) {
                    continue; // el joc encara no s'ha creat: es reintentarà a la propera connexió
                }

                $ins = $pdo->prepare(
                    "INSERT IGNORE INTO badges (codi, nom, descripcio, icona, tipus, llindar, joc_id, ordre)
                     VALUES (:codi, :nom, :descripcio, :icona, :tipus, :llindar, :joc, :ordre)"
                );
                foreach ($badges as $b) {
                    $ins->execute([
                        'codi' => $b[0], 'nom' => $b[1], 'descripcio' => $b[2], 'icona' => $b[3],
                        'tipus' => $b[4], 'llindar' => $b[5], 'joc' => (int) $jocId, 'ordre' => $b[6],
                    ]);
                }
            } catch (PDOException $e) {
                error_log('[Migrator] badges de "' . $nomJoc . '": ' . $e->getMessage());
            }
        }
    }

    // Crea una taula amb claus foranes; si falla (errno 150...) ho reintenta sense elles
    private static function crearTaula(PDO $pdo, $sqlAmbPlaceholder, $claus)
    {
        try {
            $pdo->exec(str_replace('%FK%', $claus, $sqlAmbPlaceholder));
        } catch (PDOException $e) {
            error_log('[Migrator] Claus foranes no disponibles (' . $e->getMessage() . '); es crea sense.');
            $pdo->exec(str_replace('%FK%', '', $sqlAmbPlaceholder));
        }
    }

    // Executa una sentència de dades de prova: un error aquí no ha d'aturar la resta
    private static function executar(PDO $pdo, $sql)
    {
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            error_log('[Migrator] ' . $e->getMessage() . ' · SQL: ' . substr(preg_replace('/\s+/', ' ', $sql), 0, 120));
        }
    }
}
