<?php
require_once __DIR__ . '/../config/database.php';

class AmistatModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function cercarUsuaris($text, $usuariId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, nom_usuari, nom_complet, avatar FROM usuaris
             WHERE (nom_usuari LIKE :q OR nom_complet LIKE :q) AND id <> :id
             ORDER BY nom_usuari LIMIT 20"
        );
        $stmt->execute(['q' => '%' . $text . '%', 'id' => $usuariId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fila d'amistat entre dos usuaris (en qualsevol sentit) o false
    public function obtenirRelacio($a, $b)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM amistats
             WHERE (solicitant_id = :a1 AND receptor_id = :b1) OR (solicitant_id = :b2 AND receptor_id = :a2)
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute(['a1' => $a, 'b1' => $b, 'a2' => $a, 'b2' => $b]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenirAmistat($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM amistats WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Retorna un codi: ok | jo_mateix | ja_amics | ja_pendent | no_existeix
    public function enviarSolicitud($de, $a)
    {
        if ($de == $a) {
            return 'jo_mateix';
        }
        $stmt = $this->pdo->prepare("SELECT id FROM usuaris WHERE id = :id");
        $stmt->execute(['id' => $a]);
        if (!$stmt->fetchColumn()) {
            return 'no_existeix';
        }

        $rel = $this->obtenirRelacio($de, $a);
        if ($rel) {
            if ($rel['estat'] === 'acceptada') {
                return 'ja_amics';
            }
            if ($rel['estat'] === 'pendent') {
                return 'ja_pendent';
            }
            // Estava rebutjada: es reobre com a nova sol·licitud
            $stmt = $this->pdo->prepare(
                "UPDATE amistats SET solicitant_id = :de, receptor_id = :a, estat = 'pendent',
                        data_solicitud = NOW(), data_resposta = NULL WHERE id = :id"
            );
            $stmt->execute(['de' => $de, 'a' => $a, 'id' => $rel['id']]);
            return 'ok';
        }

        $stmt = $this->pdo->prepare("INSERT INTO amistats (solicitant_id, receptor_id) VALUES (:de, :a)");
        $stmt->execute(['de' => $de, 'a' => $a]);
        return 'ok';
    }

    // VULNERABILITAT V3 (IDOR): només es filtra per l'id de l'amistat, mai pel
    // receptor. Qualsevol usuari pot acceptar/rebutjar sol·licituds d'altres.
    public function respondre($amistatId, $accio)
    {
        $estat = $accio === 'acceptar' ? 'acceptada' : 'rebutjada';
        $stmt = $this->pdo->prepare(
            "UPDATE amistats SET estat = :estat, data_resposta = NOW() WHERE id = :id AND estat = 'pendent'"
        );
        $stmt->execute(['estat' => $estat, 'id' => $amistatId]);
        return $stmt->rowCount();
    }

    // VULNERABILITAT V3 (IDOR): no es comprova que l'usuari sigui part de l'amistat.
    public function eliminar($amistatId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM amistats WHERE id = :id");
        $stmt->execute(['id' => $amistatId]);
        return $stmt->rowCount();
    }

    // Llista d'amics acceptats: cada fila té amistat_id i les dades de l'altre usuari
    public function obtenirAmics($usuariId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT a.id AS amistat_id, u.id, u.nom_usuari, u.nom_complet, u.avatar, a.data_resposta
             FROM amistats a
             JOIN usuaris u ON u.id = CASE WHEN a.solicitant_id = :u1 THEN a.receptor_id ELSE a.solicitant_id END
             WHERE a.estat = 'acceptada' AND (a.solicitant_id = :u2 OR a.receptor_id = :u3)
             ORDER BY u.nom_usuari"
        );
        $stmt->execute(['u1' => $usuariId, 'u2' => $usuariId, 'u3' => $usuariId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenirPendentsRebudes($usuariId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT a.id AS amistat_id, a.data_solicitud, u.id, u.nom_usuari, u.nom_complet, u.avatar
             FROM amistats a JOIN usuaris u ON u.id = a.solicitant_id
             WHERE a.receptor_id = :u AND a.estat = 'pendent' ORDER BY a.data_solicitud DESC"
        );
        $stmt->execute(['u' => $usuariId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Sol·licituds enviades per l'usuari i el seu estat (pendent / rebutjada)
    public function obtenirEnviades($usuariId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT a.id AS amistat_id, a.estat, a.data_solicitud, u.id, u.nom_usuari, u.nom_complet, u.avatar
             FROM amistats a JOIN usuaris u ON u.id = a.receptor_id
             WHERE a.solicitant_id = :u AND a.estat IN ('pendent', 'rebutjada') ORDER BY a.data_solicitud DESC"
        );
        $stmt->execute(['u' => $usuariId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
