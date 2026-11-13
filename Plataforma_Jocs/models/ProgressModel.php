<?php
require_once 'config/database.php';

class ProgressModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function obtenirNivellActual($usuariId, $jocId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT nivell_actual FROM progres_usuari
             WHERE usuari_id = :usuari_id AND joc_id = :joc_id
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([
            'usuari_id' => $usuariId,
            'joc_id' => $jocId,
        ]);

        return (int) ($stmt->fetchColumn() ?: 1);
    }

    public function registrarNivellSuperat($usuariId, $jocId, $nivell, $puntuacio, $durada, $totalNivells)
    {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO partides (usuari_id, joc_id, nivell_jugat, puntuacio_obtinguda, durada_segons)
                 VALUES (:usuari_id, :joc_id, :nivell, :puntuacio, :durada)"
            );
            $stmt->execute([
                'usuari_id' => $usuariId,
                'joc_id' => $jocId,
                'nivell' => $nivell,
                'puntuacio' => $puntuacio,
                'durada' => $durada,
            ]);

            $completat = $nivell >= $totalNivells;
            $nouNivell = $completat ? $totalNivells : $nivell + 1;

            $stmt = $this->pdo->prepare(
                "SELECT id FROM progres_usuari
                 WHERE usuari_id = :usuari_id AND joc_id = :joc_id
                 ORDER BY id DESC LIMIT 1"
            );
            $stmt->execute([
                'usuari_id' => $usuariId,
                'joc_id' => $jocId,
            ]);
            $progresId = $stmt->fetchColumn();

            if ($progresId) {
                $stmt = $this->pdo->prepare(
                    "UPDATE progres_usuari
                     SET nivell_actual = :nivell_actual,
                         puntuacio_maxima = GREATEST(COALESCE(puntuacio_maxima, 0), :puntuacio),
                         partides_jugades = COALESCE(partides_jugades, 0) + 1,
                         ultima_partida = NOW()
                     WHERE id = :id"
                );
                $stmt->execute([
                    'nivell_actual' => $nouNivell,
                    'puntuacio' => $puntuacio,
                    'id' => $progresId,
                ]);
            } else {
                $stmt = $this->pdo->prepare(
                    "INSERT INTO progres_usuari
                     (usuari_id, joc_id, nivell_actual, puntuacio_maxima, partides_jugades, ultima_partida)
                     VALUES (:usuari_id, :joc_id, :nivell_actual, :puntuacio, 1, NOW())"
                );
                $stmt->execute([
                    'usuari_id' => $usuariId,
                    'joc_id' => $jocId,
                    'nivell_actual' => $nouNivell,
                    'puntuacio' => $puntuacio,
                ]);
            }

            $this->pdo->commit();

            return [
                'completed' => $completat,
                'nextNivell' => $completat ? null : $nouNivell,
                'totalNivells' => $totalNivells,
            ];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
