<?php
$jocId = $joc['id'];
$indexFile = __DIR__ . "/../../public/js/jocs/$jocId/index.php";

if (!file_exists($indexFile)) {
    die("Joc no trobat");
}

// Calculem la URL base dels recursos del joc perquè es carreguin correctament
// tant si la plataforma s'executa a l'arrel com en un subdirectori (/mvc/).
$baseDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
$gameBaseUrl = $baseDir . '/public/js/jocs/' . $jocId . '/';

// Incloem directament l'index del joc
include $indexFile; // VULNERABILITAT: Inclusió de fitxers sense validació (LFI)
