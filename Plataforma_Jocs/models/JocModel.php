<?php
require_once 'config/database.php';

class JocModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function obtenirJocsActius()
    {
        $sql = "SELECT * FROM jocs WHERE actiu = 1";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // VULNERABILITAT: IDOR
    public function obtenirJoc($id)
    {
        $sql = "SELECT * FROM jocs WHERE id = $id";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ---------------------------------------------------------------------
    // CRUD de jocs (gestió per a admin i developer)
    // ---------------------------------------------------------------------

    // Admin: tots els jocs. Developer: només els que ha publicat ell.
    public function obtenirJocsGestio($usuariId, $esAdmin)
    {
        if ($esAdmin) {
            $stmt = $this->pdo->query(
                "SELECT j.*, u.nom_usuari AS nom_developer
                 FROM jocs j LEFT JOIN usuaris u ON u.id = j.developer_id
                 ORDER BY j.id"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmt = $this->pdo->prepare(
            "SELECT j.*, u.nom_usuari AS nom_developer
             FROM jocs j LEFT JOIN usuaris u ON u.id = j.developer_id
             WHERE j.developer_id = :dev ORDER BY j.id"
        );
        $stmt->execute(['dev' => $usuariId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Retorna l'id autogenerat del nou joc
    public function crearJoc($nom, $descripcio, $puntuacioMaxima, $nivells, $actiu, $developerId)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO jocs (nom_joc, descripcio, puntuacio_maxima, nivells_totals, actiu, developer_id)
             VALUES (:nom, :descripcio, :punts, :nivells, :actiu, :dev)"
        );
        $stmt->execute([
            'nom' => $nom,
            'descripcio' => $descripcio,
            'punts' => $puntuacioMaxima,
            'nivells' => $nivells,
            'actiu' => $actiu,
            'dev' => $developerId,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function establirRutaIndex($id, $rutaIndex)
    {
        $stmt = $this->pdo->prepare("UPDATE jocs SET ruta_index = :ruta WHERE id = :id");
        $stmt->execute(['ruta' => $rutaIndex, 'id' => $id]);
    }

    public function actualitzarJoc($id, $nom, $descripcio, $puntuacioMaxima, $nivells, $actiu)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE jocs SET nom_joc = :nom, descripcio = :descripcio, puntuacio_maxima = :punts,
                    nivells_totals = :nivells, actiu = :actiu
             WHERE id = :id"
        );
        $stmt->execute([
            'nom' => $nom,
            'descripcio' => $descripcio,
            'punts' => $puntuacioMaxima,
            'nivells' => $nivells,
            'actiu' => $actiu,
            'id' => $id,
        ]);
    }

    public function eliminarJoc($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM jocs WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}
