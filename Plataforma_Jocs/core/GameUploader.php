<?php
/**
 * Desplegament de jocs pujats en format ZIP.
 *
 * ============================ LABORATORI EDUCATIU ============================
 * VULNERABILITATS INTENCIONADES (V2 - Pujada de ZIP insegura / Zip Slip)
 *
 *  - NO s'inspecciona el contingut del ZIP (ni HTML, ni JS, ni PHP, ni res).
 *  - NO hi ha antivirus ni detecció de contingut maliciós.
 *  - L'extracció es fa a mà, entrada per entrada, concatenant el nom que ve
 *    DINS del ZIP amb el directori de destí, sense normalitzar la ruta ni
 *    comprovar que el resultat continua dins del directori del joc:
 *    una entrada "../../../x.txt" s'escriu fora del directori (Zip Slip /
 *    Path Traversal). (ZipArchive::extractTo() es protegeix d'això, per això
 *    NO s'utilitza.)
 *  - No es restringeixen extensions: un .php dins el ZIP queda accessible
 *    dins public/js/jocs/<id>/ i el servidor l'executa.
 * ============================================================================
 */
class GameUploader
{
    // Directori base on viuen tots els jocs: public/js/jocs/<id>/
    public static function directoriBase()
    {
        return __DIR__ . '/../public/js/jocs';
    }

    public static function directoriJoc($jocId)
    {
        return self::directoriBase() . '/' . (int) $jocId;
    }

    /**
     * Extreu el ZIP a public/js/jocs/<id>/ i retorna la ruta (relativa al
     * directori del joc) de l'index.html principal.
     * Llança una Exception si el ZIP no es pot obrir o no conté cap index.html.
     */
    public static function desplegar($rutaZip, $jocId)
    {
        $desti = self::directoriJoc($jocId);

        $zip = new ZipArchive();
        if ($zip->open($rutaZip) !== true) {
            throw new Exception("No s'ha pogut obrir el fitxer ZIP.");
        }

        if (!is_dir($desti)) {
            mkdir($desti, 0777, true);
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $nom = $zip->getNameIndex($i);

            // VULNERABILITAT (Zip Slip): $nom no es valida ni es normalitza
            $objectiu = $desti . '/' . $nom;

            if (substr($nom, -1) === '/') {
                @mkdir($objectiu, 0777, true);
                continue;
            }

            @mkdir(dirname($objectiu), 0777, true);
            file_put_contents($objectiu, $zip->getFromIndex($i));
        }
        $zip->close();

        $index = self::trobarIndex($desti);
        if ($index === null) {
            throw new Exception("El ZIP no conté cap fitxer index.html.");
        }
        return $index;
    }

    // Cerca l'index.html més proper a l'arrel del joc (p.ex. "index.html" o "joc/index.html")
    private static function trobarIndex($desti)
    {
        if (is_file($desti . '/index.html')) {
            return 'index.html';
        }

        $millor = null;
        $millorProfunditat = PHP_INT_MAX;
        if (!is_dir($desti)) {
            return null;
        }
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($desti, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $fitxer) {
            if (strtolower($fitxer->getFilename()) === 'index.html') {
                $relatiu = ltrim(str_replace('\\', '/', substr($fitxer->getPathname(), strlen($desti))), '/');
                $prof = substr_count($relatiu, '/');
                if ($prof < $millorProfunditat) {
                    $millorProfunditat = $prof;
                    $millor = $relatiu;
                }
            }
        }
        return $millor;
    }

    // Elimina el directori d'un joc pujat (només el seu directori <id>)
    public static function eliminar($jocId)
    {
        $dir = self::directoriJoc($jocId);
        if (!is_dir($dir) || (int) $jocId <= 0) {
            return;
        }
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $f) {
            $f->isDir() && !$f->isLink() ? @rmdir($f->getPathname()) : @unlink($f->getPathname());
        }
        @rmdir($dir);
    }
}
