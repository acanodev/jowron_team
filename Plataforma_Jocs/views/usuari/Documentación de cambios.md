# Documentación de cambios: jowron\_team.zip → jowron\_team\_1.zip

Proyecto: **ArJol.cat · Plataforma de Videojocs** (PHP MVC, Institut Carles Vallbona, módulo 5023) Comparación realizada el 16/11/2026 sobre el contenido de ambos ZIP, incluyendo su historial Git.

## 1. Resumen ejecutivo

El paso de `jowron_team.zip` a `jowron_team_1.zip` es una **ampliación grande de la plataforma** que se organiza en cuatro bloques:

1. **Nuevas funcionalidades:** gestión de juegos (CRUD con subida de ZIP), roles (`admin`, `developer`, `usuari`), sistema de amigos, sistema de retos y estadísticas avanzadas con gráficos.
2. **Refactor de arquitectura:** toda la lógica y el acceso a datos pasan a una **API REST** (`api/api.php` + `api/routes.php` + `core/Api.php`). Los controladores MVC ya no tocan los modelos: llaman a la API mediante `core/ApiClient.php`.
3. **Migración automática de base de datos:** `core/Migrator.php` crea las tablas y columnas que faltan; `database/novetats.sql` es el equivalente manual.
4. **Laboratorio de vulnerabilidades ampliado:** seis vulnerabilidades intencionadas nuevas (V1–V6), documentadas en `docs/LABORATORI_VULNERABILITATS.md`. Las vulnerabilidades previas del proyecto se mantienen.

| Métrica | jowron\_team.zip | jowron\_team\_1.zip |
| --- | --- | --- |
| Commit de partida (HEAD) | `4ca8d0f` (merge PR #15, 13/11/2026) | `929304d` (15/11/2026) + cambios sin commitear |
| Rama | `ThisIsFine_Jowi` | `ThisIsFine_Jowi` |
| Estado del working tree | limpio | modificado (ver sección 2) |
| Archivos nuevos | — | **25** |
| Archivos modificados | — | **15** |
| Archivos sin cambios | resto del proyecto (`Joc_Cursa_Cavalls`, `Joc_Nau`, vistas de auth, perfil, ranking, etc.) | igual |

Los cambios fuera de `Plataforma_Jocs/` son nulos: `Joc_Cursa_Cavalls`, `Joc_Nau` y el `README.md` raíz son idénticos en ambos ZIP.

## 2. Historial Git y origen de los cambios

El ZIP 1 está exactamente en el commit `4ca8d0f`. El ZIP 2 contiene el mismo historial más **dos commits nuevos** y un conjunto de cambios **sin commitear**:

| Capa | Commit / estado | Fecha | Contenido |
| --- | --- | --- | --- |
| 1 | `829a22d` — WIP: roles, CRUD de jocs amb ZIP, models/controladors d'amics i reptes | 15/11/2026 16:29 | 20 archivos, +1316 / −3. Roles, CRUD de juegos con ZIP, modelos y controladores de amigos y retos, `GameUploader`, `Roles`, migración SQL. Nota del propio commit: index.php referenciaba `EstadistiquesController`, aún inexistente. |
| 2 | `929304d` — Estadístiques, vistes d'amics/reptes/CSS, docs del laboratori i migració SQL | 15/11/2026 17:27 | 14 archivos, +817 / −1. Estadísticas, vistas de amigos y retos, CSS, `charts.js`, documentación del laboratorio, README, cabecera con nuevos enlaces. |
| 3 | **Sin commitear** (working tree) | 15/11/2026 19:33–19:38 | Refactor a API REST: `api/routes.php`, `core/Api.php`, `core/ApiClient.php`, `core/Migrator.php`; reescritura de `api/api.php`, controladores, `database.php`, modelos, `main.js` del juego 1; actualización de README y de la documentación del laboratorio. |

Importante: los cambios de la capa 3 **no están en ningún commit**. Si se hace un `git checkout` o `git stash` sin cuidado se perderían.

## 3. Inventario completo de archivos

Todas las rutas son relativas a `Plataforma_Jocs/`.

### 3.1 Archivos nuevos (25)

| Área | Archivo | Función |
| --- | --- | --- |
| API | `api/routes.php` | Definición de todas las rutas REST (≈22 KB) |
| Core | `core/Api.php` | Núcleo de la API: registro de rutas, despacho, errores |
| Core | `core/ApiClient.php` | Cliente de la API para los controladores MVC |
| Core | `core/Roles.php` | Gestión de roles por cookie Base64 (V1) |
| Core | `core/GameUploader.php` | Despliegue de juegos subidos en ZIP (V2) |
| Core | `core/Migrator.php` | Migración automática de esquema |
| Controladores | `AmicsController.php` | Amigos y perfil público |
| Controladores | `ReptesController.php` | Retos entre usuarios |
| Controladores | `EstadistiquesController.php` | Estadísticas con gráficos |
| Controladores | `GestioJocsController.php` | CRUD de juegos |
| Modelos | `AmistatModel.php` | Tabla `amistats` |
| Modelos | `ReptesModel.php` | Tabla `reptes` |
| Modelos | `EstadistiquesModel.php` | Consultas agregadas de estadísticas |
| Vistas | `views/amics/index.php`, `views/amics/perfil_public.php` | Gestión de amigos y perfil público |
| Vistas | `views/reptes/index.php`, `views/reptes/detall.php` | Lista y detalle de retos |
| Vistas | `views/estadistiques/index.php` | Panel de estadísticas |
| Vistas | `views/gestio_jocs/llista.php`, `formulari.php`, `denegat.php` | CRUD de juegos y pantalla de acceso denegado |
| Vistas | `views/jocs/joc_html.php` | Reproductor en iframe de juegos ZIP |
| JS | `public/js/charts.js` | Gráficos SVG sin dependencias |
| BD | `database/novetats.sql` | Migración SQL manual idempotente |
| Docs | `docs/LABORATORI_VULNERABILITATS.md` | Guía del laboratorio V1–V6 |

### 3.2 Archivos modificados (15)

| Archivo | Resumen del cambio |
| --- | --- |
| `README.md` | Nueva sección de ampliación, puesta en marcha, rutas, juegos ZIP y tabla de la API |
| `api/api.php` | De endpoint único GET de niveles a punto de entrada REST completo |
| `config/database.php` | Ejecuta `Migrator::assegurar()` al conectar |
| `controllers/AuthController.php` | Login, registro y logout vía API |
| `controllers/JocController.php` | Listado, detalle y guardado de partidas vía API |
| `controllers/UsuariController.php` | Perfil, ranking y actualización de perfil vía API |
| `core/Session.php` | Nuevos `requireLogin()` y `flash()` |
| `index.php` | Carga de nuevos controladores y 4 grupos de rutas |
| `models/JocModel.php` | Configuración de nivel y métodos CRUD |
| `models/PartidaModel.php` | `registrarDerrota()` |
| `models/UsuariModel.php` | Rol al registrarse y `actualitzarPerfil()` |
| `public/css/style.css` | +60 líneas de estilos nuevos (≈16,9 KB → 21,4 KB) |
| `public/js/jocs/1/main.js` | La URL de guardado de partidas apunta a la API |
| `views/jocs/joc.php` | Soporte de juegos ZIP con iframe |
| `views/layout/header.php` | Enlaces nuevos en el menú |

## 4. Arquitectura nueva: API REST

### 4.1 Antes

Los controladores creaban instancias de los modelos y accedían directamente a la base de datos. `api/api.php` solo atendía `GET /jocs/{id}/nivells/{n}` para devolver la configuración de un nivel (consulta SQL concatenada, vulnerable a inyección).

### 4.2 Ahora

- **`api/api.php`** es el punto de entrada HTTP. Hace `chdir` a la raíz del proyecto, arranca la sesión, envía cabeceras CORS (`GET, POST, DELETE, OPTIONS`, `Content-Type`), responde 204 a `OPTIONS`, lee el cuerpo como JSON (`Content-Type: application/json`) o como formulario y delega en `Api::handle()`. Mantiene la compatibilidad `api.php?joc_id=1&nivell=2`.
- **`core/Api.php`** registra rutas con `Api::ruta(metodo, patron, handler)`, donde `{id}` equivale a cualquier segmento. Cada handler recibe `($p, $q, $b, $f)` = parámetros de ruta, query string, cuerpo y ficheros, y devuelve `Api::ok()` o `Api::error()`. Incluye la clase `ApiError` (excepción con código HTTP), `Api::requereixLogin()` (401 sin sesión) y gestión de errores 404, 405 y 500.
- **`core/ApiClient.php`** permite a los controladores llamar a la API **dentro del mismo proceso**, sin pasar por la red. Motivo documentado: `php -S` es de un solo hilo y una petición HTTP a sí mismo quedaría bloqueada; el resultado es idéntico al de una llamada HTTP real. Métodos: `get`, `post`, `delete`, `ok`, `flash`, `dades`.
- **JavaScript de los juegos** consume la API por HTTP: configuración de nivel (`GET /api/api.php/jocs/1/nivells/2`) y guardado de partidas (`POST /api/api.php/partides`).
- **Sesión:** la misma sesión PHP (cookie `PHPSESSID`) para web y API.
- **Errores:** formato `{"error": "..."}` con el código HTTP correspondiente (401, 403, 404, 405, 409, 422, 500).

### 4.3 Tabla de rutas de la API (prefijo `/mvc/api/api.php`)

| Área | Rutas |
| --- | --- |
| Auth | `POST /auth/login` · `POST /auth/registre` · `POST /auth/logout` · `GET /auth/me` |
| Juegos | `GET /jocs` · `GET /jocs/{id}` · `GET /jocs/{id}/progres` · `GET /jocs/{id}/nivells/{n}` |
| Partidas | `POST /partides` (`joc_id`, `nivell`, `puntuacio`, `durada`, `resultat`) |
| Usuarios | `GET /usuaris/{id}` · `GET /usuaris/{id}/public` · `POST /usuaris/me` · `GET /ranking` |
| Amigos | `GET /amics?q=` · `POST /amics/solicitar` · `POST /amics/{id}/respondre` · `DELETE /amics/{id}` |
| Retos | `GET /reptes` · `POST /reptes` · `GET /reptes/{id}` · `POST /reptes/{id}/respondre` · `POST /reptes/{id}/resultat` |
| Estadísticas | `GET /estadistiques?usuari_id=&q=&ordre=` |
| Gestión de juegos | `GET /gestio/jocs` · `GET /gestio/jocs/{id}` · `POST /gestio/jocs` · `POST /gestio/jocs/{id}` · `DELETE /gestio/jocs/{id}` |

Con Apache y `mod_rewrite` (`api/.htaccess`, sin cambios) también funciona sin `api.php`: `/mvc/api/jocs`.

### 4.4 Detalle de rutas relevantes

- **`POST /auth/login`:** valida credenciales con `UsuariModel::login`, guarda `usuari_id` y `nom_usuari` en sesión y envía la cookie `rol` con el valor Base64 leído de la BD.
- **`POST /auth/registre`:** crea el usuario (rol `usuari` por defecto); si la BD rechaza el insert devuelve 409 con el mensaje de PDO.
- **`POST /auth/logout`:** cierra sesión y borra la cookie `rol`.
- **`GET /jocs/{id}/progres`:** devuelve `nivell_actual` acotado entre 1 y `nivells_totals`.
- **`POST /partides`:** exige sesión. Si `resultat=derrota` registra una derrota sin avanzar progreso; en caso contrario llama a `ProgressModel::registrarNivellSuperat()`.
- **`GET /usuaris/{id}/public`:** perfil público con resumen estadístico, últimas 5 partidas y relación de amistad con el usuario autenticado.
- **`POST /usuaris/me`:** actualiza nombre, email y avatar (subida del avatar sin validar, vulnerabilidad previa mantenida).
- **`GET /estadistiques`:** devuelve resumen, juegos más jugados, evolución (últimas 30 partidas), top 10 del ranking, comparativa con amigos, historial filtrable y, si lo hay, el error SQL.
- **Gestión de juegos:** requiere rol `admin` o `developer` (403 en caso contrario). El developer solo gestiona sus propios juegos; el admin gestiona todos. La actualización se hace por `POST` porque PHP no lee `multipart` en `PUT`.

## 5. Roles y permisos

`core/Roles.php` define tres roles: `admin`, `developer` y `usuari`.

- El rol se guarda en `usuaris.rol` **codificado en Base64**: `usuari` = `dXN1YXJp`, `admin` = `YWRtaW4=`, `developer` = `ZGV2ZWxvcGVy`.
- Al iniciar sesión se envía la cookie `rol` (sin `HttpOnly`, sin `Secure`, sin firma, `SameSite=Lax`).
- Los permisos se calculan **solo a partir de la cookie**: `Roles::actual()`, `esAdmin()`, `esDeveloper()` y `potGestionarJocs()`.
- Todo usuario nuevo recibe `usuari` (`UsuariModel::crear`).
- El menú muestra «Gestió jocs» solo si `Roles::potGestionarJocs()` es verdadero.

## 6. Gestión de juegos (CRUD con ZIP)

**Rutas web:** `/gestio-jocs`, `/gestio-jocs/nou`, `/gestio-jocs/crear`, `/gestio-jocs/editar`, `/gestio-jocs/actualitzar`, `/gestio-jocs/eliminar`.

**Flujo de creación:**

1. Se crea el registro en `jocs` (con `developer_id` del usuario) y se obtiene su id autogenerado.
2. `GameUploader::desplegar()` extrae el ZIP a `public/js/jocs/<id>/`.
3. Se localiza el `index.html` más cercano a la raíz (función `trobarIndex`) y se guarda su ruta en `jocs.ruta_index`.
4. Si el ZIP falla o no contiene `index.html`, se elimina el directorio y el registro (rollback manual).

**Edición:** se pueden cambiar nombre, descripción, puntuación máxima, nivels y visibilidad; el ZIP es opcional. Si el juego ya era ZIP se reemplaza su directorio; los juegos de la plataforma (`ruta_index` NULL, p. ej. el juego 1) no se borran, se extrae por encima.

**Eliminación:** borra el directorio del juego (solo si es ZIP) y el registro.

**Visualización:** `views/jocs/joc.php` detecta si el juego es ZIP (`ruta_index` informado y sin `index.php` propio) y delega en `views/jocs/joc_html.php`, que lo muestra en un `iframe` con botón de abrir en pestaña nueva.

**Guardado de partidas desde juegos ZIP:** `POST /mvc/api/api.php/partides` con `joc_id`, `nivell`, `puntuacio`, `durada` y opcionalmente `resultat=derrota`. La ruta antigua `POST /mvc/?route=/guardar-puntuacio` sigue funcionando y reenvía a la API.

**Modelo (`JocModel`):** nuevos `obtenirConfiguracioNivell`, `obtenirJocsGestio`, `crearJoc`, `establirRutaIndex`, `actualitzarJoc` y `eliminarJoc` (estos usan consultas preparadas).

## 7. Sistema de amigos

**Rutas web:** `/amics`, `/amics/solicitar`, `/amics/respondre`, `/amics/eliminar`, `/usuari?id=` (perfil público).

- Búsqueda de usuarios por nombre de usuario o nombre completo (máx. 20 resultados).
- Estados de amistad: `pendent`, `acceptada`, `rebutjada`.
- `enviarSolicitud` devuelve un código: `ok`, `jo_mateix`, `ja_amics`, `ja_pendent` o `no_existeix`. Una solicitud rechazada se reabre como nueva sobre la misma fila.
- La vista muestra solicitudes recibidas, enviadas y la lista de amigos, con los ids visibles (necesarios para el laboratorio).
- El perfil público muestra resumen estadístico y últimas partidas, sin email ni datos privados.
- Redirecciones tras solicitar: solo se permiten rutas internas (`tornarA()` rechaza URLs externas).

## 8. Sistema de retos

**Rutas web:** `/reptes`, `/reptes/crear`, `/reptes/veure`, `/reptes/respondre`, `/reptes/resultat`.

- Estados: `pendent` → `acceptat` → `completat` (o `rebutjat`).
- Se crea un reto indicando retado, juego y título opcional (por defecto «Repte a \<juego>»).
- Cuando ambos jugadores envían puntuación, el reto pasa a `completat` y se decide el ganador (`guanyador_id`); si hay empate queda en `NULL`.
- La vista de lista agrupa: recibidos, enviados, activos e historial.
- El detalle muestra el enfrentamiento (versus), la mejor puntuación del usuario en ese juego y los formularios de respuesta o resultado.

## 9. Estadísticas avanzadas

**Ruta web:** `/estadistiques` (parámetros opcionales `usuari_id`, `q`, `ordre`).

Contenido del panel:

- Tarjetas de resumen: partidas, victorias, derrotas, puntuación total, tiempo total, retos ganados y perdidos.
- Gráfico donut de victorias y derrotas.
- Gráfico de barras de los juegos más jugados (máx. 8).
- Gráfico de línea con la evolución de puntuaciones (últimas 30 partidas).
- Gráfico de barras con el top 10 del ranking.
- Comparativa con amigos (barras).
- Historial de partidas (hasta 100) con búsqueda por nombre de juego y orden por fecha, puntuación o duración.

**`public/js/charts.js`:** librería propia en JavaScript puro que genera SVG (barras, línea y donut) usando las variables CSS del tema (compatible con tema día/noche). Se activa con `<div class="grafic" data-tipus="barres|linia|donut" data-dades='[...]'>`. Muestra «Encara no hi ha dades per mostrar.» cuando no hay datos.

## 10. Base de datos

### 10.1 Cambios de esquema

| Tabla | Cambio |
| --- | --- |
| `usuaris` | Nueva columna `avatar` VARCHAR(255) NULL (el código ya la usaba) y `rol` VARCHAR(100) NOT NULL DEFAULT `dXN1YXJp` |
| `jocs` | Nuevas columnas `developer_id` INT NULL, `ruta_index` VARCHAR(255) NULL, `data_creacio` DATETIME |
| `partides` | Nueva columna `resultat` VARCHAR(10) NOT NULL DEFAULT `victoria` |
| `amistats` (nueva) | `id`, `solicitant_id`, `receptor_id`, `estat`, `data_solicitud`, `data_resposta`; clave única (solicitante, receptor); FK a `usuaris` con `ON DELETE CASCADE` |
| `reptes` (nueva) | `id`, `titol`, `creador_id`, `retat_id`, `joc_id`, `estat`, `puntuacio_creador`, `puntuacio_retat`, `guanyador_id`, fechas de creación, respuesta y completado; FK a `usuaris` y `jocs` |

### 10.2 Dos vías de migración

- **Automática (`core/Migrator.php`):** se ejecuta la primera vez que la plataforma se conecta (`config/database.php` la llama tras crear la conexión PDO). Es idempotente (solo 2 consultas ligeras a `information_schema` si todo existe), funciona con MariaDB y MySQL (no usa `ADD COLUMN IF NOT EXISTS`), reintenta sin claves foráneas si falla con error 150 y nunca rompe la web (los errores van a `error_log`).
- **Manual (`database/novetats.sql`):** equivalente, idempotente, ejecutable con `mariadb -u plataforma_user -p plataforma_videojocs < database/novetats.sql`.

### 10.3 Datos de prueba incluidos

- Roles: `admin` (id 1) = admin, `gamer1` (id 2) = usuari, `retro_player` (id 3) = developer. Contraseña de todos: `123456789a`.
- 10 partidas de prueba (ids 6–15, victorias y derrotas), 3 amistades y 2 retos (uno completado y uno pendiente).

## 11. Laboratorio de vulnerabilidades (V1–V6)

Todas están marcadas en el código con el comentario `VULNERABILITAT` y su código. **Uso exclusivo en un entorno de laboratorio aislado.**

| Código | Vulnerabilidad | Dónde | Cómo funciona |
| --- | --- | --- | --- |
| V1 | Escalada de privilegios por cookie Base64 | `core/Roles.php` | El rol sale de la cookie `rol` sin firma ni comparación con la BD. Cambiar `dXN1YXJp` por `YWRtaW4=` da acceso de administrador. |
| V2 | Subida de ZIP insegura y Zip Slip | `core/GameUploader.php` | Sin inspección del contenido; extracción manual sin normalizar rutas (`../` escribe fuera del directorio); sin restricción de extensiones, por lo que un `.php` dentro del ZIP se ejecuta. `extractTo()` no se usa a propósito porque sí se protege. |
| V3 | IDOR en amigos | `AmistatModel::respondre()` y `eliminar()` | Actúan sobre el id de amistad sin comprobar que pertenezca al usuario autenticado. |
| V4 | IDOR, falta de autorización y resultados manipulables en retos | `ReptesModel::obtener/respondre/registrarResultat` | Cualquiera ve o responde cualquier reto; `jugador_id` y `puntuacio` se toman de la petición; se pueden sobrescribir resultados de retos completados. |
| V5 | SQL Injection en estadísticas | `EstadistiquesModel::historial()` | El parámetro `q` se concatena en un `LIKE '%…%'` y los errores SQL se muestran en la página. `ordre` usa lista blanca y `usuari_id` se convierte a entero. |
| V6 | Exposición de información en estadísticas | `EstadistiquesController::index()` | `?usuari_id=N` muestra las estadísticas de cualquier usuario. |

Vulnerabilidades previas que se mantienen: SQLi en login y registro, XSS en la cabecera, IDOR en `/perfil?id=`, subida de avatar sin validar, Open Redirect, sesiones inseguras, LFI en `joc.php`, hash MD5 de contraseñas y exposición de datos en el ranking.

Efecto lateral documentado: como el registro sigue siendo inyectable, un registro manipulado podría fijar `rol` directamente en la BD. Con el refactor, **todas las vulnerabilidades también son explotables directamente contra la API** (p. ej. `POST /api/api.php/amics/2/respondre` o `GET /api/api.php/estadistiques?usuari_id=1&q='`).

Otros puntos de seguridad introducidos por la API que conviene tener presentes: `Access-Control-Allow-Origin: *` en todas las respuestas y mensajes de error internos devueltos tal cual (`Error intern: …`).

## 12. Detalle de cambios en archivos modificados

### 12.1 `index.php`

Carga `core/Roles.php` y los cuatro controladores nuevos, y registra los grupos de rutas `/gestio-jocs/*` (6), `/amics/*` y `/usuari` (5), `/reptes/*` (5) y `/estadistiques` (1).

### 12.2 `core/Session.php`

- `requireLogin()`: redirige a `/mvc/?route=/login` si no hay sesión (evita repetir el bloque en cada controlador).
- `flash($missatge, $tipus)`: mensajes de una sola lectura que sobreviven a una redirección. Con argumento los guarda; sin argumento los devuelve y los borra.

### 12.3 `controllers/AuthController.php`

Ya no instancia `UsuariModel`. Login, registro y logout llaman a `/auth/login`, `/auth/registre` y `/auth/logout`. El registro ahora muestra el error devuelto por la API (antes no validaba nada). Se mantiene la vulnerabilidad de Open Redirect en el login.

### 12.4 `controllers/JocController.php`

Listado, detalle y progreso se obtienen de la API. `guardarPuntuacio()` pasa a ser un simple reenvío a `POST /partides` (compatibilidad con juegos ZIP ya publicados). Ya no requiere sesión en el controlador: la comprobación la hace la API.

### 12.5 `controllers/UsuariController.php`

Perfil, ranking y actualización de perfil pasan por la API. La subida del avatar y la consulta SQL vulnerable se han movido a `POST /usuaris/me`, con el mismo comportamiento.

### 12.6 `models/UsuariModel.php`

- `crear()` incluye ahora la columna `rol` con el valor `Roles::codificar(Roles::USUARI)`.
- Nuevo `actualitzarPerfil()`, con la consulta vulnerable a SQLi (si `avatar` es `null` no se toca esa columna).

### 12.7 `models/PartidaModel.php`

Nuevo `registrarDerrota()`, que inserta una partida con `resultat='derrota'` sin avanzar el progreso de niveles.

### 12.8 `models/JocModel.php`

Añade la consulta de configuración de nivel (conserva la SQLi original, antes en `api.php`) y los métodos del CRUD (ver sección 6).

### 12.9 `config/database.php`

Tras crear la conexión PDO exitosa se carga `core/Migrator.php` y se llama a `Migrator::assegurar($pdo)`.

### 12.10 `public/js/jocs/1/main.js`

La constante `progressUrl` pasa de `?route=/guardar-puntuacio` a `api/api.php/partides`. El resto del juego no cambia.

### 12.11 `views/layout/header.php`

Nuevos enlaces para usuarios con sesión: Amics, Reptes, Estadístiques y, solo si el rol lo permite, Gestió jocs.

### 12.12 `views/jocs/joc.php`

Detecta juegos ZIP (`$esJocHtml`) y los muestra con `joc_html.php`. Mantiene la inclusión del `index.php` de los juegos de la plataforma (LFI intencionada).

### 12.13 `public/css/style.css`

Bloque nuevo «Ampliació» (+60 líneas): botones secundarios, pequeños y de peligro, insignias de estado (`badge-ok`, `badge-pend`, `badge-off`…), mensaje de éxito, iframe del juego, filas de usuario, formulario de búsqueda, tarjetas numéricas, rejillas, estilos de los gráficos SVG (ejes, valores, donut, leyenda) y la vista versus de los retos con el ganador resaltado. Reutiliza las variables CSS del tema día/noche.

### 12.14 `README.md`

Marca la API como hecha, añade la sección «Ampliació», la puesta en marcha (migración automática, permisos de `public/js/jocs/` y `public/uploads/`, límites de `php.ini` de 10M/12M), las nuevas rutas, el formato de los juegos en ZIP y la tabla de la API.

## 13. Puesta en marcha de las novedades

1. **Base de datos:** automática al primer acceso (Migrator), o manual con `database/novetats.sql`.
2. **Permisos:** `public/js/jocs/` y `public/uploads/` deben ser escribibles por el usuario del servidor web (p. ej. `chmod -R 777 public/js/jocs public/uploads`).
3. **Límites de subida:** `upload_max_filesize` y `post_max_size` de `php.ini` limitan el tamaño del ZIP.
4. **Formato del ZIP de un juego:** debe contener un `index.html`; se extrae a `public/js/jocs/<id>/`.
5. **Servidor:** compatible con `php -S` (de ahí el `ApiClient` en proceso) y con Apache + `mod_rewrite`.

## 14. Observaciones y puntos a revisar

- **Cambios sin commitear:** el refactor a la API (capa 3) no está en el historial Git. Conviene hacer commit antes de cualquier operación destructiva.
- **ZIP de ejemplo ausentes:** los commits incluyen `docs/exemples/joc_exemple.zip` y `docs/exemples/joc_zipslip_prova.zip`, y la documentación y el README los citan, pero en el working tree de `jowron_team_1.zip` esos archivos están **borrados** (Git los marca como eliminados) y el directorio `docs/exemples/` no existe. Hay que restaurarlos con `git checkout -- Plataforma_Jocs/docs/exemples` o regenerarlos para poder reproducir V2.
- **Archivo sin cambios con dependencias nuevas:** `views/usuari/perfil.php` y `ranking.php` siguen igual, pero ahora reciben sus datos desde la API.
- **Mensajes del commit:** los dos commits nuevos figuran con autor «Claude» y coautoría de Claude Sonnet 5.5 (sesión de Claude Code), no con el autor habitual del equipo.
- **Comportamiento distinto en el endpoint antiguo:** `api/api.php` ya no rechaza métodos distintos de GET con 405 global; ahora cada ruta decide y devuelve 405 solo si la ruta existe con otro método.

## 15. Resumen visual de lo añadido

| Bloque | Elementos principales |
| --- | --- |
| Funcionalidad | 4 módulos nuevos (juegos, amigos, retos, estadísticas), roles y reproductor de juegos ZIP |
| Arquitectura | API REST con \~30 rutas, cliente en proceso, migrador automático |
| Base de datos | 2 tablas nuevas, 6 columnas nuevas, datos de prueba |
| Interfaz | 8 vistas nuevas, 3 tipos de gráfico SVG, \~60 líneas de CSS, menú ampliado |
| Seguridad (laboratorio) | 6 vulnerabilidades nuevas (V1–V6) y todas las previas conservadas |
| Documentación | README ampliado y guía `LABORATORI_VULNERABILITATS.md` |
