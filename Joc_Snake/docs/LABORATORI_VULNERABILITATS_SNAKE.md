# Laboratori de vulnerabilitats · Snake Classic

> **Ús exclusiu en un entorn de laboratori aïllat.** Aquest fitxer és a `Joc_Snake/docs/`, fora de `Plataforma_Jocs/` i fora del ZIP que es publica, de manera que **no es serveix als usuaris del joc**.

Les vulnerabilitats SNK-V1, V3 i V4 les ha introduït Snake Classic de forma intencionada i estan marcades al codi amb `VULNERABILITAT (SNK-Vn)`. Les "E" són vulnerabilitats **ja existents** a la plataforma que Snake exposa o aprofita: **no s'han modificat ni corregit**.

Les proves s'han fet a una còpia de la plataforma (PHP 8.3 `php -S` + MariaDB 10.11, joc pujat amb id 4 per *Gestió jocs*). Substituïu `4` pel vostre id.

---

## Introduïdes per Snake Classic

### SNK-V1 · DOM XSS pel nom del jugador
| | |
|---|---|
| 1. Fitxer | `snake-ui.js` (`mostrarJugador`), origen de la dada a `main.js` (`aliesActual`) |
| 2. Funcionalitat | Camp "Jugador" del HUD; àlies editable a la pantalla d'inici |
| 3. Tipus | DOM-based XSS (reflectit via URL i emmagatzemat via `localStorage`) — CWE-79 |
| 4. Per què | El valor de `?jugador=` (o l'àlies desat) s'insereix amb `innerHTML` sense escapar. Té prioritat el paràmetre de la URL |
| 5. Què permet | Executar JavaScript al mateix origen que la plataforma. Com que `PHPSESSID` i `rol` no són `HttpOnly` (vulnerabilitat existent a `Session.php`/`Roles.php`), permet llegir-los |
| 6. Comprovació | Obrir `…/mvc/public/js/jocs/4/index.html?jugador=%3Cimg%20src%3Dx%20onerror%3Dalert(document.cookie)%3E`. Alternativa: escriure `<img src=x onerror=alert(1)>` a l'àlies, prémer Tab i recarregar. Verificat: l'`<img>` amb `onerror` queda dins de `#hudJugador` |
| 7. Mitigació | `textContent` en lloc d'`innerHTML`; validar longitud/caràcters de l'àlies; `HttpOnly`+`SameSite` a les cookies; CSP |

### SNK-V3 · Manipulació de l'identificador del joc
| | |
|---|---|
| 1. Fitxer | `api-jowron.js` (`detectarEntorn`) |
| 2. Funcionalitat | Enviament de la partida (`joc_id`) |
| 3. Tipus | Manipulació de paràmetres / IDOR sobre identificadors de joc — CWE-639 |
| 4. Per què | El `joc_id` s'agafa de la ruta, però `?joc_id=N` el sobreescriu sense validar que sigui el d'aquest joc; el servidor accepta qualsevol joc existent (E1) |
| 5. Què permet | Registrar puntuacions de Snake a un altre joc (rànquing, estadístiques i badges d'aquell joc) |
| 6. Comprovació | Jugar amb sessió oberta a `…/jocs/4/index.html?joc_id=1` i acabar la partida. `SELECT joc_id,puntuacio_obtinguda FROM partides ORDER BY id DESC LIMIT 1` mostra `joc_id=1` (verificat) |
| 7. Mitigació | No acceptar l'id del client: que el servidor el derivi de la sessió/token del joc; validar que el joc existeix, és actiu i correspon a l'origen de la petició |

### SNK-V4 · Estat de client de confiança (severitat baixa / informatiu)
| | |
|---|---|
| 1. Fitxer | `main.js` (`CLAU_RECORD`), `api-jowron.js` (mode proves, `?mode=proves`) |
| 2. Funcionalitat | Rècord personal local (mentre l'API no el serveixi) i mode proves |
| 3. Tipus | Confiança en dades del client — CWE-602 |
| 4. Per què | El rècord es llegeix de `localStorage` sense integritat; el mode proves es pot activar des de la URL |
| 5. Què permet | Mostrar un rècord/"Nou rècord" falsos a la pròpia pantalla. **No afecta el servidor**: el mode proves no fa cap petició |
| 6. Comprovació | A la consola: `localStorage.setItem('snake_classic.record.4','99999')` i recarregar → HUD "Rècord (local) 99999" |
| 7. Mitigació | Rècord servit per l'API; eliminar `permetreModeProvesPerUrl` en producció (`conf.js`) |

---

## Existents a la plataforma (detectades, NO modificades)

### E1 · Puntuació, durada i joc sense validar a `/guardar-puntuacio`
| | |
|---|---|
| 1. Fitxer | `Plataforma_Jocs/controllers/JocController.php` (`guardarPuntuacio`, comentari `VULNERABILITAT: Sense validació de dades`) |
| 2. Funcionalitat | Desat de partides i avaluació de badges |
| 3. Tipus | Falta de validació al servidor / integritat de dades |
| 4. Per què | `puntuacio`, `durada`, `joc_id` i `resultat` es confien tal com arriben; només es retalla el mínim a 0 |
| 5. Què permet | Falsejar puntuacions i desbloquejar badges (`puntuacio_partida`, `partides`…) sense jugar |
| 6. Comprovació | Amb sessió: `curl -b cookies -d "joc_id=4&nivell=1&puntuacio=999999&durada=0&resultat=derrota" "…/mvc/?route=/guardar-puntuacio"` → `success:true`, fila amb 999999 a `partides` (verificat) |
| 7. Mitigació | Límits per joc (`puntuacio_maxima`), coherència puntuació/durada, token de partida emès pel servidor, rate-limit |

### Altres observacions (resum)
- **E2** `/guardar-puntuacio` no té token CSRF (només depèn de la sessió).
- **E3** `GameUploader.php`: Zip Slip i execució de `.php` pujats (V2 de la plataforma). El `snake_classic.zip` generat és net (7 fitxers, sense rutes `../`).
- **E4** `Roles.php`: el rol surt de la cookie `rol` en Base64 (V1 de la plataforma); per això qualsevol usuari pot arribar a *Gestió jocs* i pujar el ZIP.
- **E5** `views/jocs/llista.php`: `nom_joc` i `descripcio` s'imprimeixen sense escapar → XSS emmagatzemat si es creen jocs amb HTML als camps.
- **E6** `config/database.php`: credencials en text pla al codi (ja marcat al codi).
