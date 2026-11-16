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
    "nomJugador": nomJugador,
    "puntsNivell": 500
};

const loadedConf = (await getConfFromDB()) ?? {};

export const gameConf = {
    ...defaultConf,
    "nivell": defaultConf.nivell,
    "puntsNivell": loadedConf.puntsNivell ?? defaultConf.puntsNivell
};

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