const gameConf = {
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
}

gameConf["fotogramesEnemics"] = gameConf.velocitatEnemics * 10;
gameConf["fotogramesAteroides"] = gameConf.velocitatAsteroides * 10;