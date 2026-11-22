<?php
require_once __DIR__ . '/../config/database.php';

class EstadistiquesModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    // Totals de l'usuari: partides, victòries, derrotes, puntuació i temps
    public function resum($usuariId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) AS partides,
                    COALESCE(SUM(CASE WHEN resultat = 'victoria' THEN 1 ELSE 0 END), 0) AS victories,
                    COALESCE(SUM(CASE WHEN resultat = 'derrota' THEN 1 ELSE 0 END), 0) AS derrotes,
                    COALESCE(SUM(puntuacio_obtinguda), 0) AS puntuacio_total,
                    COALESCE(SUM(durada_segons), 0) AS temps_total
             FROM partides WHERE usuari_id = :u"
        );
        $stmt->execute(['u' => $usuariId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function jocsMesJugats($usuariId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT j.nom_joc, COUNT(*) AS partides
             FROM partides p JOIN jocs j ON j.id = p.joc_id
             WHERE p.usuari_id = :u GROUP BY j.id, j.nom_joc ORDER BY partides DESC, j.nom_joc LIMIT 8"
        );
        $stmt->execute(['u' => $usuariId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Evolució cronològica de la puntuació (les últimes 30 partides)
    public function evolucio($usuariId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT data_partida, puntuacio_obtinguda FROM (
                SELECT id, data_partida, puntuacio_obtinguda FROM partides
                WHERE usuari_id = :u ORDER BY data_partida DESC, id DESC LIMIT 30
             ) t ORDER BY data_partida ASC, id ASC"
        );
        $stmt->execute(['u' => $usuariId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Comparativa de l'usuari amb una llista d'ids (ell mateix + amics)
    public function comparativa(array $ids)
    {
        $ids = array_map('intval', $ids);
        $llista = implode(',', $ids);
        $sql = "SELECT u.id, u.nom_usuari,
                       COUNT(p.id) AS partides,
                       COALESCE(SUM(CASE WHEN p.resultat = 'victoria' THEN 1 ELSE 0 END), 0) AS victories,
                       COALESCE(SUM(p.puntuacio_obtinguda), 0) AS puntuacio_total,
                       COALESCE(SUM(p.durada_segons), 0) AS temps_total
                FROM usuaris u LEFT JOIN partides p ON p.usuari_id = u.id
                WHERE u.id IN ($llista) GROUP BY u.id, u.nom_usuari ORDER BY puntuacio_total DESC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Historial de partides amb cerca per nom de joc.
     *
     * VULNERABILITAT INTENCIONADA V5 (SQL Injection): $cerca es concatena
     * directament dins la consulta, sense consulta preparada ni escapament.
     */
    public function historial($usuariId, $cerca, $ordre)
    {
        $columnes = [
            'data' => 'p.data_partida DESC, p.id DESC',
            'puntuacio' => 'p.puntuacio_obtinguda DESC',
            'durada' => 'p.durada_segons DESC',
        ];
        $orderBy = $columnes[$ordre] ?? $columnes['data'];
        $usuariId = (int) $usuariId;

        $sql = "SELECT p.id, j.nom_joc, p.nivell_jugat, p.puntuacio_obtinguda, p.durada_segons, p.resultat, p.data_partida
                FROM partides p JOIN jocs j ON j.id = p.joc_id
                WHERE p.usuari_id = $usuariId AND j.nom_joc LIKE '%$cerca%'
                ORDER BY $orderBy LIMIT 100";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
