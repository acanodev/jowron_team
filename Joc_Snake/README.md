# Snake Classic

Joc retro classic de la serp. Menja les pomes per créixer sense xocar contra les parets.

Joc per a **Jowron Team / ArJol.cat**. Es publica com qualsevol joc ZIP de la plataforma (*Gestió jocs*): s'extreu a `Plataforma_Jocs/public/js/jocs/<id>/` i es mostra dins la plataforma amb un `iframe` (capçalera, menú i tema dia/nit inclosos). **No s'ha modificat cap fitxer de `Plataforma_Jocs/`.**

## Joc
- Moviment continu amb interpolació, fletxes + WASD, creueta tàctil i lliscament del dit.
- 1 punt per poma. Cada 3 pomes puja el nivell i la serp va més ràpida (150 ms → mínim 60 ms).
- Xoc amb paret o amb el propi cos; no es pot girar 180º (ni amb dues pulsacions dins d'un tick); la poma mai surt dins la serp.
- Iniciar / Pausa-Reprendre / Reiniciar (botons, Espai, P/Esc, R). Pausa automàtica en perdre el focus.
- HUD: punts, rècord, temps (només temps de joc), nivell, jugador i estat. Pantalla de Game Over amb resum.

## Fitxers
| Fitxer | Paper |
|---|---|
| `index.html`, `index.css` | Estructura i estil (tema segons la plataforma) |
| `conf.js` | Configuració (tauler, velocitat, mode API, enllaços) |
| `motor.js` | Lògica pura del joc (sense DOM ni xarxa) |
| `snake-ui.js` | Dibuix, HUD, pantalles i entrades |
| `api-jowron.js` | **Única** capa d'integració amb la plataforma |
| `main.js` | Orquestració |
| `tests/motor.test.mjs` | Proves del motor (`node --test tests/motor.test.mjs`) |
| `database/snake_classic.sql` | Badges de Snake (`INSERT` al catàleg existent) |
| `empaquetar.sh` → `dist/snake_classic.zip` | ZIP per pujar a *Gestió jocs* |
| `docs/LABORATORI_VULNERABILITATS_SNAKE.md` | Vulnerabilitats (no es publica) |

## Posada en marxa
1. `./empaquetar.sh`
2. Entrar com a admin o developer → **Gestió jocs → Nou joc**. Nom **exactament** `Snake Classic`; descripció del joc; puntuació màxima 400; nivells 1; pujar `dist/snake_classic.zip`.
3. Badges: `mariadb -u plataforma_user -p plataforma_videojocs < database/snake_classic.sql` (després del pas 2: els enllaça pel nom; l'id el decideix la BD).
4. Jugar des de **Jocs**.

## Integració amb l'API (`api-jowron.js`)
| Operació | Estat | Com |
|---|---|---|
| Desar partida (usuari de la sessió, puntuació, durada) | **Funciona** | `POST ?route=/guardar-puntuacio` (existent). Es desa com a `derrota` (Snake no té nivells) |
| Badges desbloquejats | **Funciona** | Es mostren els `badgesNous` de la resposta; el catàleg global no es duplica |
| Rècord personal | **Pendent de l'API** | Fallback: rècord local del navegador (HUD "Rècord (local)") |
| Estadístiques / partides anteriors | **Pendent de l'API** | El panell enllaça a `/estadistiques` i `/badges` de la plataforma |
| Nom de l'usuari | **Pendent de l'API** | L'àlies és local |

Els mètodes pendents ja existeixen i llancen `ApiNoDisponible`; en tenir endpoints només cal implementar-los a `ApiReal`. `conf.api.transport = "rest"` apunta a la ruta `api/api.php/partides` del document de canvis del repo, que **no és present en aquest ZIP i no s'ha pogut verificar**.

**Mode proves** (`conf.api.mode = "proves"` o `?mode=proves`): simula tot amb `localStorage`, no fa cap petició i mostra un avís groc. S'activa sol si el joc s'obre fora de la plataforma. Per provar-lo sol: `python3 -m http.server` a `Joc_Snake/` i obrir `http://localhost:8000/?mode=proves`.

## Assoliments
Existents: es reutilitzen els generals (Primer contacte, Pilot constant…). Nous (`database/snake_classic.sql`): Primera mossegada, Gana de pomes (10), Mig centenar (50), Centenari (100), Serp habitual (10 partides).
**No implementables encara** amb els criteris de `BadgeModel`: *superar el rècord personal* (només s'avisa a la pantalla, no es persisteix) i *sobreviure X segons* (caldria un criteri `durada_partida`).
