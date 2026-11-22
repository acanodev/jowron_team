<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Sistema de badges / assoliments.
 *
 * Els badges (taula `badges`) tenen un tipus de criteri i un llindar; quan un
 * usuari el compleix es desa a `usuari_badges`. Criteris disponibles:
 *
 *   partides           partides jugades
 *   victories          partides guanyades (nivells superats)
 *   puntuacio_total    suma de puntuacions
 *   puntuacio_partida  millor puntuació en una sola partida
 *   nivell             nivell més alt superat
 *   joc_completat      jocs amb tots els nivells superats
 *   jocs_diferents     jocs diferents jugats
 *   temps_total        segons de joc acumulats
 *   amics              amics acceptats
 *
 * Si el badge té joc_id, els criteris basats en partides només compten aquell joc.
 */
class BadgeModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    // Catàleg complet (amb el nom del joc associat, si n'hi ha)
    public function cataleg()
    {
        $stmt = $this->pdo->query(
            "SELECT b.*, j.nom_joc FROM badges b LEFT JOIN jocs j ON j.id = b.joc_id ORDER BY b.ordre, b.id"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Valor actual de la mètrica d'un criteri per a un usuari (opcionalment limitat a un joc)
    public function valor($usuariId, $tipus, $jocId = null)
    {
        $joc = $jocId !== null ? ' AND p.joc_id = :joc' : '';
        $params = ['u' => $usuariId];
        if ($jocId !== null) {
            $params['joc'] = $jocId;
        }

        switch ($tipus) {
            case 'partides':
                $sql = "SELECT COUNT(*) FROM partides p WHERE p.usuari_id = :u$joc";
                break;
            case 'victories':
                $sql = "SELECT COUNT(*) FROM partides p WHERE p.usuari_id = :u AND p.resultat = 'victoria'$joc";
                break;
            case 'puntuacio_total':
                $sql = "SELECT COALESCE(SUM(p.puntuacio_obtinguda), 0) FROM partides p WHERE p.usuari_id = :u$joc";
                break;
            case 'puntuacio_partida':
                $sql = "SELECT COALESCE(MAX(p.puntuacio_obtinguda), 0) FROM partides p WHERE p.usuari_id = :u$joc";
                break;
            case 'nivell':
                $sql = "SELECT COALESCE(MAX(p.nivell_jugat), 0) FROM partides p
                        WHERE p.usuari_id = :u AND p.resultat = 'victoria'$joc";
                break;
            case 'joc_completat':
                $sql = "SELECT COUNT(DISTINCT p.joc_id) FROM partides p JOIN jocs j ON j.id = p.joc_id
                        WHERE p.usuari_id = :u AND p.resultat = 'victoria' AND p.nivell_jugat >= j.nivells_totals$joc";
                break;
            case 'jocs_diferents':
                $sql = "SELECT COUNT(DISTINCT p.joc_id) FROM partides p WHERE p.usuari_id = :u";
                $params = ['u' => $usuariId];
                break;
            case 'temps_total':
                $sql = "SELECT COALESCE(SUM(p.durada_segons), 0) FROM partides p WHERE p.usuari_id = :u$joc";
                break;
            case 'amics':
                $sql = "SELECT COUNT(*) FROM amistats a
                        WHERE a.estat = 'acceptada' AND (a.solicitant_id = :u OR a.receptor_id = :u2)";
                $params = ['u' => $usuariId, 'u2' => $usuariId];
                break;
            default:
                return 0;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    // Badges ja obtinguts per l'usuari: [badge_id => data_obtencio]
    public function obtinguts($usuariId)
    {
        $stmt = $this->pdo->prepare("SELECT badge_id, data_obtencio FROM usuari_badges WHERE usuari_id = :u");
        $stmt->execute(['u' => $usuariId]);
        $r = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $f) {
            $r[(int) $f['badge_id']] = $f['data_obtencio'];
        }
        return $r;
    }

    /**
     * Comprova tots els badges de l'usuari i concedeix els que compleixi.
     * Retorna la llista dels badges NOUS (per avisar el jugador).
     */
    public function avaluar($usuariId)
    {
        $usuariId = (int) $usuariId;
        $ja = $this->obtinguts($usuariId);
        $nous = [];
        $cache = [];

        foreach ($this->cataleg() as $b) {
            if (isset($ja[(int) $b['id']])) {
                continue;
            }
            $jocId = $b['joc_id'] !== null ? (int) $b['joc_id'] : null;
            $clau = $b['tipus'] . '|' . ($jocId ?? '-');
            if (!isset($cache[$clau])) {
                $cache[$clau] = $this->valor($usuariId, $b['tipus'], $jocId);
            }
            if ($cache[$clau] >= (int) $b['llindar']) {
                $stmt = $this->pdo->prepare(
                    "INSERT INTO usuari_badges (usuari_id, badge_id, data_obtencio) VALUES (:u, :b, :d)"
                );
                try {
                    $stmt->execute(['u' => $usuariId, 'b' => $b['id'], 'd' => date('Y-m-d H:i:s')]);
                    $nous[] = $b;
                } catch (PDOException $e) {
                    // Duplicat per una petició simultània: ja el tenia, no és un error
                }
            }
        }
        return $nous;
    }

    /**
     * Catàleg amb l'estat de l'usuari: cada badge porta `obtingut` (data o null),
     * `progres` (valor actual, limitat al llindar) i `percentatge`.
     */
    public function ambEstat($usuariId)
    {
        $usuariId = (int) $usuariId;
        $ja = $this->obtinguts($usuariId);
        $cache = [];
        $r = [];

        foreach ($this->cataleg() as $b) {
            $jocId = $b['joc_id'] !== null ? (int) $b['joc_id'] : null;
            $clau = $b['tipus'] . '|' . ($jocId ?? '-');
            if (!isset($cache[$clau])) {
                $cache[$clau] = $this->valor($usuariId, $b['tipus'], $jocId);
            }
            $llindar = max(1, (int) $b['llindar']);
            $b['obtingut'] = $ja[(int) $b['id']] ?? null;
            $b['progres'] = $b['obtingut'] ? $llindar : min($cache[$clau], $llindar);
            $b['llindar'] = $llindar;
            $b['percentatge'] = (int) floor(100 * $b['progres'] / $llindar);
            $r[] = $b;
        }
        return $r;
    }

    // Només els badges obtinguts (per al perfil), els més recents primer
    public function obtingutsDetall($usuariId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT b.*, ub.data_obtencio FROM usuari_badges ub JOIN badges b ON b.id = ub.badge_id
             WHERE ub.usuari_id = :u ORDER BY ub.data_obtencio DESC, b.ordre"
        );
        $stmt->execute(['u' => $usuariId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
