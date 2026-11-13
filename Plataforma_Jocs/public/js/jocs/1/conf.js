const getMeta = (name, fallback) =>
  document.querySelector(`meta[name="${name}"]`)?.content ?? fallback;

const jocId = getMeta("game-joc-id", "1");
const totalNivells = Number(getMeta("game-total-nivells", "1")) || 1;
const nivellSolicitat = Math.max(
  1,
  Math.min(Number(getMeta("game-nivell", "1")) || 1, totalNivells),
);
const nomJugador = getMeta("game-player-name", "Jowron");

const defaultConf = {
  "nivell": 1,
  "maxPunts": 1000,
  "velocitatEnemics": 5,
  "intervalAparicioMs": 5000,
  "puntsPerEnemic": 100,
  "maxEnemics": 100,
  "ampleEnemics": 75,
  "altEnemics": 75,
  "nomJugador": nomJugador,
  "puntsJugador": 0,
  "killsJugador": 0,
  "velocitatJugador": 10,
  "ampleJugador": 150,
  "altJugador": 100,
  "maxVides": 3,
  "maxAsteroides": 100,
  "velocitatAsteroides": 2,
};

const loadedConf = (await getConfFromDB()) ?? {};

export const gameConf = {
  ...defaultConf,
  "jocId": jocId,
  "totalNivells": totalNivells,
  "nivell": loadedConf.nivell ?? nivellSolicitat,
  "maxPunts": loadedConf.puntsNivell ?? defaultConf.maxPunts,
  "velocitatEnemics": loadedConf.velocitatEnemics ?? defaultConf.velocitatEnemics,
  "maxEnemics": loadedConf.maxEnemics ?? defaultConf.maxEnemics,
  "maxVides": loadedConf.vides ?? defaultConf.maxVides,
  "velocitatAsteroides": loadedConf.velocitatAsteroides ?? defaultConf.velocitatAsteroides,
};

const FPS_OBJECTES = 60;

gameConf["fotogramesEnemics"] = 1000 / FPS_OBJECTES;
gameConf["fotogramesAsteroides"] = 1000 / FPS_OBJECTES;

async function getConfFromDB() {
  try {
    const response = await fetch(
      `../../../../api/api.php/jocs/${jocId}/nivells/${nivellSolicitat}`,
    );

    if (!response.ok) {
      throw new Error(`Error HTTP: ${response.status}`);
    }

    return await response.json();

  } catch (e) {
    console.error("No s'ha pogut carregar la configuració del nivell:", e);
    return null;
  }
}
