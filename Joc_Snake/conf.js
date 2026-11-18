// Configuració de Snake Classic (mateix paper que conf.js a Joc_Nau).
// Cap valor d'aquí conté credencials ni dades de la BD.
export const snakeConf = {
  nomJoc: "Snake Classic",

  // --- Tauler ---
  graella: { columnes: 20, files: 20, mida: 24 }, // mida = píxels interns per cel·la
  inicial: { longitud: 3, direccio: "dreta" },

  // --- Dificultat: cada `pomesPerNivell` pomes la serp va més ràpida ---
  velocitat: { inicialMs: 150, minimMs: 60, reduccioMs: 8, pomesPerNivell: 3 },
  puntsPerPoma: 1,

  // --- Integració amb Jowron Team (només la fa servir api-jowron.js) ---
  api: {
    // "real"   -> parla amb la plataforma (sessió PHP del navegador)
    // "proves" -> simulació local, SENSE xarxa (desenvolupament sense API)
    mode: "real",

    // "legacy" -> endpoint que JA existeix en aquest repo: POST ?route=/guardar-puntuacio
    // "rest"   -> API REST descrita a "Documentación de cambios.md" (NO és al codi
    //             d'aquest ZIP; sense verificar). Canvieu-ho quan l'API estigui llesta.
    transport: "legacy",

    // Permet forçar el mode proves amb ?mode=proves a la URL (útil per provar el joc sol)
    permetreModeProvesPerUrl: true,

    // Rutes relatives a l'arrel de la plataforma (p. ex. /mvc/)
    endpoints: {
      legacy: { desarPartida: "?route=/guardar-puntuacio" },
      rest: { desarPartida: "api/api.php/partides" },
    },
    timeoutMs: 8000,
  },

  // Enllaços a pàgines JA existents de la plataforma
  enllacos: {
    jocs: "?route=/jocs",
    estadistiques: "?route=/estadistiques",
    assoliments: "?route=/badges",
  },
};
