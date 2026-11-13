const defaultConf = {
  "nivell": 1,
  "maxPunts": 1000,
  "velocitatEnemics": 5,
  "intervalAparicioMs": 5000,
  "puntsPerEnemic": 100,
  "maxEnemics": 100,
  "ampleEnemics": 75,
  "altEnemics": 75,
  "nomJugador": "Jowron",
  "puntsJugador": 0,
  "killsJugador": 0,
  "velocitatJugador": 10,
  "ampleJugador": 150,
  "altJugador": 100,
  "maxVides": 3,
  "maxAsteroides": 100,
  "velocitatAsteroides": 10,
};

const loadedConf = (await getConfFromDB()) ?? {};

export const gameConf = {
  ...defaultConf,
  "nivell": loadedConf.nivell ?? defaultConf.nivell,
  "maxPunts": loadedConf.puntsNivell ?? defaultConf.maxPunts,
  "velocitatEnemics": loadedConf.velocitatEnemics ?? defaultConf.velocitatEnemics,
  "maxEnemics": loadedConf.maxEnemics ?? defaultConf.maxEnemics,
  "maxVides": loadedConf.vides ?? defaultConf.maxVides,
};

gameConf["fotogramesEnemics"] = gameConf.velocitatEnemics * 10;
gameConf["fotogramesAsteroides"] = gameConf.velocitatAsteroides * 10;

async function getConfFromDB() {
  try {
    const response = await fetch('../../../../api/api.php/jocs/1/nivells/1');

    if (!response.ok) {
      throw new Error(`Error HTTP: ${response.status}`);
    }

    return await response.json();

  } catch (e) {
    console.error("No s'ha pogut carregar la configuració del nivell:", e);
    return null;
  }
}
