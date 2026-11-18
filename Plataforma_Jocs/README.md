# Consideracions de desplegament

## La carpeta arrel

Actualment l'arrel es considera /mvc/, per tant teniu 3 opcions:

1. Copiar el projecte a /var/www/html/mvc
2. Modificar l'arrel de les rutes /mvc/?route a /?route
3. Crear un virtualhost amb Apache
4. Crear un fitxer .htaccess amb mod_rewrite

## Funcionalitats pendents

1. Perfil d'usuari (amb modificació de dades personals i les seves estadístiques)
2. Ranking d'usuaris (classificació per cada joc i un ranking global)
3. API funcional separada del MVC
4. Aspecte visual (CSS)
5. Integració del joc principal
6. Pujada d'imatges de perfil sense validació (per tractar la vulnerabilitat file upload)

## Els jocs (opcional)

Opcional: Podeu crear un CRUD per afegir nous jocs a la plataforma. Només els administradors o desenvolupadors indie podrien accedir al CRUD de jocs per integrar un nou joc. Podríeu fins i tot crear 3 tipus d'usuari (admin, developer i usuari).

Funcionalitats avançades (opcionals):

- Sistema de badges/assoliments
- Xat en temps real entre jugadors
- Torneos i competicions
- Sistema d'amics i reptes
- Estadístiques avançades amb gràfics

---

# Ampliació: CRUD de jocs amb rols, estadístiques, amics i badges

Vegeu `docs/LABORATORI_VULNERABILITATS.md` (vulnerabilitats V1, V2, V3, V5 i V6 i usuaris de prova).

## Posada en marxa de la nova funcionalitat
1. **Migració de la BD: automàtica.** `core/Migrator.php` crea sol les taules/columnes que falten (`amistats`, `badges`, `usuari_badges`, `partides.resultat`, `usuaris.rol`...) la primera vegada que la plataforma es connecta a la BD (MariaDB i MySQL). `database/novetats.sql` continua disponible per fer-ho a mà: `mariadb -u plataforma_user -p plataforma_videojocs < database/novetats.sql`
   Els badges de **Snake Classic** (`database/snake_classic.sql`) també s'apliquen sols: el migrador els insereix en la primera connexió posterior a crear el joc a *Gestió jocs* amb el nom exacte "Snake Classic" (el SQL només cal executar-lo a mà si no vols esperar-ho).
2. Assegurar que `public/js/jocs/` i `public/uploads/` són escrivibles per l'usuari del servidor web (p. ex. `chmod -R 777 public/js/jocs public/uploads`).
3. `upload_max_filesize`/`post_max_size` de `php.ini` limiten la mida dels ZIP.

## Noves rutes
`/gestio-jocs` (+ `/nou`, `/crear`, `/editar`, `/actualitzar`, `/eliminar`), `/amics` (+ `/solicitar`, `/respondre`, `/eliminar`), `/usuari?id=`, `/estadistiques`, `/badges`.

## CRUD de jocs i rols
Hi ha 3 tipus d'usuari: **admin**, **developer** i **usuari** (el que rep tothom en registrar-se). Només admin i developer poden entrar a *Gestió jocs*: l'admin gestiona tots els jocs i el developer només els que ha publicat. Un usuari normal rep un 403.

## Jocs en ZIP
Un ZIP ha de tenir un `index.html`. S'extreu a `public/js/jocs/<id>/` i es mostra a la plataforma en un iframe. Per desar partides, el joc pot fer `POST /mvc/?route=/guardar-puntuacio` amb `joc_id`, `nivell`, `puntuacio`, `durada` i, opcionalment, `resultat=derrota`. La resposta JSON inclou `badgesNous` amb els assoliments que s'acaben de desbloquejar.

## Estadístiques
`/estadistiques`: targetes resum, gràfics SVG (victòries/derrotes, jocs més jugats, evolució de puntuacions, ranking i comparativa amb amics, `public/js/charts.js`, sense llibreries externes) i historial filtrable.

## Amics
Cerca d'usuaris, sol·licituds enviades/rebudes, llista d'amics i perfil públic (`/usuari?id=`).

## Badges / assoliments
`/badges` mostra tots els assoliments, els obtinguts i el progrés dels que falten; també apareixen al perfil. Es concedeixen automàticament en desar una partida, en acceptar una amistat i en entrar a `/badges` o al propi perfil. El catàleg viu a la taula `badges` (tipus de criteri + llindar, opcionalment lligat a un joc; ara n'hi ha de generals i de *Nau vs Ovnis*). Criteris disponibles: `partides`, `victories`, `puntuacio_total`, `puntuacio_partida`, `nivell`, `joc_completat`, `jocs_diferents`, `temps_total` i `amics`. Per afegir-ne un de nou basta fer un `INSERT` a `badges`.
