# Plan de reforma: controladores JSON manteniendo solo los archivos actuales

## Decisiones confirmadas

- **No se crea ningún archivo nuevo**, excepto los archivos Docker (que no existen hoy y son requisito del despliegue).
- Se mantienen las rutas, los modelos, los controladores, las vistas y la estructura de carpetas actual.
- Los controladores dejan de hacer `include` de vistas y devuelven JSON con el envoltorio `{ok, data, meta}`.
- Las vistas `.php` siguen siendo los mismos archivos, pero convertidos en cáscaras HTML + JS que hacen `fetch` a su propia URL con `Accept: application/json`.
- Los helpers JSON se definen como **funciones globales dentro de `index.php`**. No se añade protección CSRF: se conserva la vulnerabilidad marcada en el laboratorio.
- Los niveles se consultan con PDO directo desde `JocController`; no se crea `NivellModel`.
- `Session.php`, `Roles.php`, `api/api.php` y todos los modelos quedan intactos; las vulnerabilidades del laboratorio se conservan.
- Docker: Apache + mod_php + MariaDB; `database/init.sql` lo aporta el usuario desde phpMyAdmin.

> Se descarta cualquier API paralela, controlador adaptador, modelo nuevo o JS compartido nuevo. Todo el código nuevo vive en archivos que ya existen.

## Resumen

1. `index.php`: define `json_input()`, `json_meta()`, `json_send()`, `json_ok()`, `json_error()` y registra el mapa `ruta → vista` con `$router->view(...)`.
2. `core/Router.php`: negociación por `Accept: application/json` e `include` de la vista cáscara cuando la petición no es JSON.
3. Los 7 controladores existentes sustituyen `include`, `die` y `header('Location: …')` por `json_ok()` / `json_error()`. Las llamadas a modelos se mantienen.
4. `JocController` añade dos métodos privados que consultan `nivells_joc` con PDO y devuelven `nivells[]` y `configuracio` en la respuesta de `/jugar`.
5. Cada vista `.php` existente pasa a HTML + JS inline. `views/layout/header.php` contiene los helpers JS compartidos; `views/layout/footer.php` conserva el tema.
6. `public/js/charts.js` expone `window.renderGrafics()`.
7. `public/js/jocs/1/index.php` y `/2/index.php` fijan los metas `game-*` desde el JSON.
8. Docker es la única excepción: se crean `docker/apache/Dockerfile`, `docker/apache/000-default.conf`, `docker/apache/php.ini`, `docker-compose.yml` y `.env.example`.

## Archivos modificados

```text
Plataforma_Jocs/
├── index.php                          (funciones JSON + mapa de vistas)
├── core/Router.php                    (negociación + include de vista)
├── controllers/
│   ├── AuthController.php
│   ├── JocController.php              (+ consulta PDO de nivells_joc)
│   ├── UsuariController.php
│   ├── GestioJocsController.php
│   ├── AmicsController.php
│   ├── EstadistiquesController.php
│   └── BadgesController.php
├── views/
│   ├── layout/header.php              (helpers JS, sin PHP de datos)
│   ├── layout/footer.php              (tema, sin PHP de datos)
│   ├── auth/{login,registre}.php
│   ├── jocs/{llista,joc,joc_html}.php
│   ├── usuari/{perfil,ranking}.php
│   ├── amics/{index,perfil_public}.php
│   ├── gestio_jocs/{llista,formulari,denegat}.php
│   ├── estadistiques/index.php
│   └── badges/index.php
├── public/js/charts.js
├── public/js/jocs/1/index.php
└── public/js/jocs/2/index.php
```

## Archivos nuevos (única excepción)

```text
Plataforma_Jocs/docker/apache/Dockerfile
Plataforma_Jocs/docker/apache/000-default.conf
Plataforma_Jocs/docker/apache/php.ini
Plataforma_Jocs/docker-compose.yml
Plataforma_Jocs/.env.example
Plataforma_Jocs/database/init.sql   (lo aporta el usuario)
```

## Flujo de una petición

1. El navegador pide `/mvc/?route=/jocs` sin cabeceras especiales → `Router` hace `include` de `views/jocs/llista.php` (HTML).
2. El JS inline de esa vista pide `fetch(location.href, { headers: { Accept: 'application/json' } })` → `Router` llama a `JocController::llistaJocs()`, que devuelve `{ok, data, meta}`.
3. Todo `POST` se trata como JSON. `json_input()` lee el cuerpo JSON y mantiene `$_POST` como alternativa para los juegos ZIP con `application/x-www-form-urlencoded`. No se exige token CSRF: el laboratorio mantiene esa vulnerabilidad.

## Rutas existentes → vista → datos del JSON

| Ruta | Controlador::método | Vista | `data` esperado |
|---|---|---|---|
| `/`, `/jocs` | `JocController::llistaJocs` | `views/jocs/llista.php` | `jocs[]` |
| `/jugar&id=N` | `JocController::jugar` | `views/jocs/joc.php` | `joc`, `gameBaseUrl`, `esJocHtml`, `urlJoc`, `nivellActual`, `totalNivells`, `nomJugador`, `nivells[]`, `configuracio` |
| `/login` GET | `AuthController::mostrarLogin` | `views/auth/login.php` | `error?` |
| `/login` POST | `AuthController::login` | — | `usuari?` + `meta.redirect` |
| `/registre` GET | `AuthController::mostrarRegistre` | `views/auth/registre.php` | `valors[]` |
| `/registre` POST | `AuthController::registre` | — | `meta.redirect` |
| `/logout` | `AuthController::logout` | — | `meta.redirect` |
| `/perfil` | `UsuariController::perfil` | `views/usuari/perfil.php` | `usuari`, `partides[]`, `badges[]` |
| `/ranking` | `UsuariController::ranking` | `views/usuari/ranking.php` | `ranking[]` |
| `/actualitzar-perfil` POST | `UsuariController::actualitzarPerfil` | — | `meta.redirect` |
| `/amics` | `AmicsController::index` | `views/amics/index.php` | `cerca`, `resultats[]`, `amics[]`, `rebudes[]`, `enviades[]` |
| `/amics/solicitar` POST | `AmicsController::solicitar` | — | `meta.redirect` |
| `/amics/respondre` POST | `AmicsController::respondre` | — | `meta.redirect` |
| `/amics/eliminar` POST | `AmicsController::eliminar` | — | `meta.redirect` |
| `/usuari&id=N` | `AmicsController::perfilPublic` | `views/amics/perfil_public.php` | `usuari`, `relacio`, `resum`, `partides[]`, `badges[]` |
| `/estadistiques` | `EstadistiquesController::index` | `views/estadistiques/index.php` | `usuari`, `resum`, 5 datasets, `ranking[]`, `comparativa[]`, `historial[]`, `errorSql`, `numBadges` |
| `/badges` | `BadgesController::index` | `views/badges/index.php` | `badges[]`, `obtinguts`, `total` |
| `/gestio-jocs` | `GestioJocsController::llista` | `views/gestio_jocs/llista.php` | `jocs[]` |
| `/gestio-jocs/nou` | `GestioJocsController::nou` | `views/gestio_jocs/formulari.php` | `joc: null`, `error?` |
| `/gestio-jocs/editar&id=N` | `GestioJocsController::editar` | `views/gestio_jocs/formulari.php` | `joc`, `error?` |
| `/gestio-jocs/crear` POST | `GestioJocsController::crear` | — | `meta.redirect` o `data.error` |
| `/gestio-jocs/actualitzar` POST | `GestioJocsController::actualitzar` | — | `meta.redirect` o `data.error` |
| `/gestio-jocs/eliminar` POST | `GestioJocsController::eliminar` | — | `meta.redirect` |
| `/guardar-puntuacio` POST | `JocController::guardarPuntuacio` | — | Envoltorio `{ok, data, meta}` más las claves legacy `success`, `completed`, `nextNivell`, `badgesNous[]` en la raíz para no cambiar los juegos |

`meta` incluye siempre: `loggedIn`, `nomUsuari`, `rol` (cookie Base64), `potGestionarJocs`, `flash` y, si aplica, `redirect`.

---

# Código propuesto

## `index.php`: funciones JSON y mapa de vistas

```php
<?php
require_once 'core/Router.php';
require_once 'core/Session.php';
require_once 'core/Roles.php';
Session::start();

function json_input(): array
{
    static $dades = null;
    if ($dades === null) {
        $cos = file_get_contents('php://input');
        $json = json_decode($cos, true);
        $dades = is_array($json) ? array_merge($_POST, $json) : $_POST;
    }
    return $dades;
}

function json_meta(array $extra = []): array
{
    return array_merge([
        'loggedIn' => Session::isLoggedIn(),
        'nomUsuari' => Session::get('nom_usuari'),
        'rol' => $_COOKIE['rol'] ?? Roles::codificar(Roles::USUARI),
        'potGestionarJocs' => Roles::potGestionarJocs(),
        'flash' => Session::flash(),
    ], $extra);
}

function json_send(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function json_ok(array $data = [], array $meta = [], int $status = 200): void
{
    json_send(['ok' => true, 'data' => $data, 'meta' => json_meta($meta)], $status);
}

function json_error(string $missatge, int $status = 400, array $data = [], array $meta = []): void
{
    json_send(['ok' => false, 'data' => ['error' => $missatge] + $data, 'meta' => json_meta($meta)], $status);
}

require_once 'controllers/AuthController.php';
require_once 'controllers/JocController.php';
require_once 'controllers/UsuariController.php';
require_once 'controllers/GestioJocsController.php';
require_once 'controllers/AmicsController.php';
require_once 'controllers/EstadistiquesController.php';
require_once 'controllers/BadgesController.php';

$router = new Router();

// Todas las líneas $router->get(...) y $router->post(...) existentes se dejan igual.

$router->view('/', 'views/jocs/llista.php');
$router->view('/jocs', 'views/jocs/llista.php');
$router->view('/jugar', 'views/jocs/joc.php');
$router->view('/login', 'views/auth/login.php');
$router->view('/registre', 'views/auth/registre.php');
$router->view('/perfil', 'views/usuari/perfil.php');
$router->view('/ranking', 'views/usuari/ranking.php');
$router->view('/amics', 'views/amics/index.php');
$router->view('/usuari', 'views/amics/perfil_public.php');
$router->view('/estadistiques', 'views/estadistiques/index.php');
$router->view('/badges', 'views/badges/index.php');
$router->view('/gestio-jocs', 'views/gestio_jocs/llista.php');
$router->view('/gestio-jocs/nou', 'views/gestio_jocs/formulari.php');
$router->view('/gestio-jocs/editar', 'views/gestio_jocs/formulari.php');

$router->dispatch();
```

`Session.php` y `Roles.php` no se editan: `Roles::actual()` sigue leyendo la cookie Base64 y la sesión mantiene su configuración actual.

## `core/Router.php` (mismo archivo, modificado)

```php
<?php
class Router
{
    private $routes = [];
    private $views = [];

    public function get($path, $controller, $method)
    {
        $this->routes['GET'][$path] = ['controller' => $controller, 'method' => $method];
    }

    public function post($path, $controller, $method)
    {
        $this->routes['POST'][$path] = ['controller' => $controller, 'method' => $method];
    }

    public function view($path, $file)
    {
        $this->views[$path] = $file;
    }

    private function volJson(): bool
    {
        if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE'], true)) {
            return true;
        }
        return str_contains(strtolower($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')
            || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $_GET['route'] ?? '/';

        if (!isset($this->routes[$method][$path])) {
            if ($this->volJson()) {
                json_error('Ruta no trobada', 404);
            }
            die('Ruta no trobada: ' . htmlspecialchars($path));
        }

        if (!$this->volJson()) {
            if (!isset($this->views[$path])) {
                http_response_code(404);
                die('Vista no trobada');
            }
            include __DIR__ . '/../' . $this->views[$path];
            exit;
        }

        $route = $this->routes[$method][$path];
        $controller = new $route['controller']();
        $controller->{$route['method']}();
    }
}
```

Se usa `include` (no `readfile`) para que las vistas puedan seguir haciendo `include 'views/layout/header.php'` y `include 'views/layout/footer.php'`.

## Cambios en los controladores

| Controlador::método | Nueva salida |
|---|---|
| `AuthController::mostrarLogin` | `json_ok(['error' => $error ?? null])` |
| `AuthController::login` | `json_ok(['usuari' => ...], ['redirect' => '/mvc/'])` o `json_error('Credencials incorrectes', 401)` |
| `AuthController::mostrarRegistre` | `json_ok(['valors' => json_input()])` |
| `AuthController::registre` | `json_ok([], ['redirect' => '/mvc/?route=/login'])` |
| `AuthController::logout` | `json_ok([], ['redirect' => '/mvc/'])` |
| `JocController::llistaJocs` | `json_ok(['jocs' => $jocs])` |
| `JocController::jugar` | `json_ok([...joc, gameBaseUrl, esJocHtml, urlJoc, nivellActual, totalNivells, nomJugador, nivells, configuracio...])` |
| `JocController::guardarPuntuacio` | Misma lógica, pero `json_ok([...])` |
| `UsuariController::perfil` | `json_ok(['usuari'=>…, 'partides'=>…, 'badges'=>…])` |
| `UsuariController::ranking` | `json_ok(['ranking'=>…])` |
| `UsuariController::actualitzarPerfil` | `json_ok([], ['redirect'=>…])` (upload y SQL intactos) |
| `GestioJocsController::llista` | `json_ok(['jocs'=>…])` |
| `GestioJocsController::nou/editar` | `json_ok(['joc'=>…, 'error'=>…])` |
| `GestioJocsController::crear/actualitzar/eliminar` | `json_ok([], ['redirect'=>'/mvc/?route=/gestio-jocs'])`; errores: `json_error($e, 422, ['joc'=>…])` |
| `GestioJocsController::requerirGestor/carregarJocPropi` | `json_error(..., 401/403)` en vez de `include denegat.php` |
| `AmicsController::index` | `json_ok(['cerca'=>…, 'resultats'=>…, 'amics'=>…, 'rebudes'=>…, 'enviades'=>…])` |
| `AmicsController::perfilPublic` | `json_ok(['usuari'=>…, 'relacio'=>…, 'resum'=>…, 'partides'=>…, 'badges'=>…])` |
| `AmicsController::solicitar/respondre/eliminar` | `json_ok([], ['redirect'=>…])` |
| `EstadistiquesController::index` | `json_ok([...resum, datasets, ranking, comparativa, historial, errorSql, numBadges...])` |
| `BadgesController::index` | `json_ok(['badges'=>…, 'obtinguts'=>…, 'total'=>…])` |

### Ejemplo: `controllers/AuthController.php` (archivo existente)

```php
<?php
require_once 'models/UsuariModel.php';
require_once 'core/Session.php';
require_once 'core/Roles.php';

class AuthController
{
    private $usuariModel;

    public function __construct()
    {
        $this->usuariModel = new UsuariModel();
    }

    public function mostrarLogin()
    {
        json_ok(['error' => null]);
    }

    public function login()
    {
        $dades = json_input();
        $email = $dades['email'] ?? '';
        $password = $dades['password'] ?? '';
        $usuari = $this->usuariModel->login($email, $password);

        if (!$usuari) {
            json_error('Credencials incorrectes', 401);
        }

        Session::set('usuari_id', $usuari['id']);
        Session::set('nom_usuari', $usuari['nom_usuari']);
        Roles::establirCookie($usuari['rol'] ?? Roles::codificar(Roles::USUARI));
        json_ok(['usuari' => $usuari], ['redirect' => '/mvc/']);
    }

    public function mostrarRegistre()
    {
        json_ok(['valors' => json_input()]);
    }

    public function registre()
    {
        $dades = json_input();
        $this->usuariModel->crear(
            $dades['nom_usuari'] ?? '',
            $dades['email'] ?? '',
            $dades['password'] ?? '',
            $dades['nom_complet'] ?? ''
        );
        json_ok([], ['redirect' => '/mvc/?route=/login']);
    }

    public function logout()
    {
        Session::logout();
        Roles::esborrarCookie();
        json_ok([], ['redirect' => '/mvc/']);
    }
}
```

### Ejemplo: `controllers/JocController.php` (archivo existente)

```php
<?php
require_once 'models/JocModel.php';
require_once 'models/ProgressModel.php';
require_once 'models/PartidaModel.php';
require_once 'models/BadgeModel.php';
require_once 'core/Session.php';

class JocController
{
    private $jocModel;
    private $progressModel;

    public function __construct()
    {
        $this->jocModel = new JocModel();
        $this->progressModel = new ProgressModel();
    }

    private function nivellsDe($jocId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT nivell, configuracio_json FROM nivells_joc WHERE joc_id = :id ORDER BY nivell'
        );
        $stmt->execute(['id' => $jocId]);
        return array_map(static fn($f) => [
            'nivell' => $f['nivell'],
            'configuracio' => json_decode($f['configuracio_json'], true),
        ], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function configuracioDe($jocId, $nivell)
    {
        $pdo = Database::getConnection();
        $sql = "SELECT configuracio_json FROM nivells_joc WHERE joc_id = $jocId AND nivell = $nivell";
        $fila = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
        return $fila ? json_decode($fila['configuracio_json'], true) : null;
    }

    public function llistaJocs()
    {
        json_ok(['jocs' => $this->jocModel->obtenirJocsActius()]);
    }

    public function jugar()
    {
        $jocId = $_GET['id'] ?? 0;
        $joc = $this->jocModel->obtenirJoc($jocId);
        if (!$joc) {
            json_error('Joc no trobat', 404);
        }

        $totalNivells = max(1, (int) $joc['nivells_totals']);
        $nivellActual = 1;
        if (Session::isLoggedIn()) {
            $nivellActual = $this->progressModel->obtenirNivellActual(Session::get('usuari_id'), $jocId);
            $nivellActual = max(1, min($nivellActual, $totalNivells));
        }

        $indexFile = __DIR__ . '/../public/js/jocs/' . $joc['id'] . '/index.php';
        $indexHtmlFile = __DIR__ . '/../public/js/jocs/' . $joc['id'] . '/index.html';
        if (!file_exists($indexFile) && empty($joc['ruta_index']) && file_exists($indexHtmlFile)) {
            $joc['ruta_index'] = 'index.html';
        }
        $esJocHtml = !file_exists($indexFile) && !empty($joc['ruta_index']);
        $baseDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
        $gameBaseUrl = $baseDir . '/public/js/jocs/' . $joc['id'] . '/';
        $urlJoc = $gameBaseUrl . implode('/', array_map('rawurlencode', explode('/', $joc['ruta_index'])));

        json_ok([
            'joc' => $joc,
            'esJocHtml' => $esJocHtml,
            'urlJoc' => $esJocHtml ? $urlJoc : $gameBaseUrl . 'index.php',
            'gameBaseUrl' => $gameBaseUrl,
            'nivellActual' => $nivellActual,
            'totalNivells' => $totalNivells,
            'nomJugador' => Session::get('nom_usuari') ?? 'Jowron',
            'nivells' => $this->nivellsDe($jocId),
            'configuracio' => $this->configuracioDe($jocId, $nivellActual),
        ]);
    }

    public function guardarPuntuacio()
    {
        $dades = json_input();
        if (!$dades || !Session::isLoggedIn()) {
            json_error('Petició no vàlida', 401);
        }

        $usuariId = Session::get('usuari_id');
        $jocId = (int) ($dades['joc_id'] ?? 0);
        $nivell = (int) ($dades['nivell'] ?? 1);
        $puntuacio = (int) ($dades['puntuacio'] ?? 0);
        $durada = (int) ($dades['durada'] ?? 0);

        $joc = $this->jocModel->obtenirJoc($jocId);
        if (!$joc) {
            json_error('Joc no trobat', 404);
        }

        $totalNivells = max(1, (int) $joc['nivells_totals']);
        $nivell = max(1, min($nivell, $totalNivells));

        if (($dades['resultat'] ?? 'victoria') === 'derrota') {
            (new PartidaModel())->registrarDerrota($usuariId, $jocId, $nivell, max(0, $puntuacio), max(0, $durada));
            json_ok(['success' => true, 'resultat' => 'derrota'] + $this->badgesNous($usuariId));
        }

        $resultat = $this->progressModel->registrarNivellSuperat(
            $usuariId, $jocId, $nivell, max(0, $puntuacio), max(0, $durada), $totalNivells
        );
        json_ok(['success' => true] + $resultat + $this->badgesNous($usuariId));
    }

    private function badgesNous($usuariId)
    {
        try {
            $nous = (new BadgeModel())->avaluar($usuariId);
        } catch (Throwable $e) {
            error_log('[Badges] ' . $e->getMessage());
            $nous = [];
        }
        return ['badgesNous' => array_map(fn($b) => [
            'codi' => $b['codi'], 'nom' => $b['nom'], 'descripcio' => $b['descripcio'], 'icona' => $b['icona'],
        ], $nous)];
    }
}
```

La consulta de niveles conserva la concatenación SQL de `api/api.php` para no corregir la vulnerabilidad del laboratorio.

### Ejemplo: `controllers/GestioJocsController.php` (métodos clave)

```php
private function requerirGestor()
{
    if (!Session::isLoggedIn()) {
        json_error('Cal iniciar sessió', 401);
    }
    if (!Roles::potGestionarJocs()) {
        json_error('Accés denegat: aquesta secció és només per a desenvolupadors i administradors.', 403);
    }
}

private function carregarJocPropi($id)
{
    $joc = $this->jocModel->obtenirJoc((int) $id);
    if (!$joc) {
        json_error('Joc no trobat', 404);
    }
    if (!$this->potModificar($joc)) {
        json_error('No tens permís per modificar aquest joc.', 403);
    }
    return $joc;
}

public function llista()
{
    $this->requerirGestor();
    $jocs = $this->jocModel->obtenirJocsGestio(Session::get('usuari_id'), Roles::esAdmin());
    json_ok(['jocs' => $jocs]);
}

public function crear()
{
    $this->requerirGestor();
    $camps = $this->llegirCamps();
    $zip = $this->zipPujat();
    if ($camps['nom'] === '' || $zip === null || $zip['error'] !== UPLOAD_ERR_OK) {
        json_error('Cal nom i un fitxer .zip vàlid.', 422, ['joc' => $this->campsDeVolta($camps)]);
    }

    $id = $this->jocModel->crearJoc(
        $camps['nom'], $camps['descripcio'], $camps['puntuacio'], $camps['nivells'], $camps['actiu'],
        Session::get('usuari_id')
    );
    try {
        $this->jocModel->establirRutaIndex($id, GameUploader::desplegar($zip['tmp_name'], $id));
    } catch (Exception $e) {
        GameUploader::eliminar($id);
        $this->jocModel->eliminarJoc($id);
        json_error($e->getMessage(), 422, ['joc' => $this->campsDeVolta($camps)]);
    }
    json_ok([], ['redirect' => '/mvc/?route=/gestio-jocs']);
}
```

El mismo patrón se aplica al resto de controladores: se conserva el cuerpo con los modelos y solo cambia la salida final.

## `views/layout/header.php` (helpers JS compartidos)

El archivo deja de ejecutar `session_start()`, `Session::isLoggedIn()`, `Roles::potGestionarJocs()` y `Session::get('nom_usuari')`. Pasa a ser markup estático + estos helpers:

```php
<script>
window.appFetch = async (url, options = {}) => {
  const res = await fetch(url, {
    credentials: 'same-origin',
    ...options,
    headers: { Accept: 'application/json', ...(options.headers || {}) },
  });
  return res.json();
};

window.appPost = async (url, body, method = 'POST') => window.appFetch(url, {
  method,
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify(body),
});

window.renderLayout = (meta) => {
  const nav = document.querySelector('.site-nav');
  nav.innerHTML = `
    <a href="/mvc/?route=/jocs" class="logo">ArJol.cat</a>
    <a href="/mvc/?route=/jocs">Jocs</a>
    ${meta.loggedIn ? `
      <a href="/mvc/?route=/ranking">Ranking</a>
      <a href="/mvc/?route=/amics">Amics</a>
      <a href="/mvc/?route=/estadistiques">Estadístiques</a>
      <a href="/mvc/?route=/badges">Assoliments</a>
      ${meta.potGestionarJocs ? '<a href="/mvc/?route=/gestio-jocs">Gestió jocs</a>' : ''}
      <span>Hola, ${meta.nomUsuari}</span>
      <a href="/mvc/?route=/logout">Sortir</a>` : `
      <a href="/mvc/?route=/login">Accedir</a>
      <a href="/mvc/?route=/registre">Registrar-se</a>`}`;
  document.documentElement.dataset.theme = localStorage.getItem('tema') || 'fosc';
};

window.renderFlash = (meta) => {
  const node = document.getElementById('app-flash');
  if (node && meta.flash) {
    node.innerHTML = `<div class="${meta.flash.tipus === 'error' ? 'error' : 'missatge-ok'}">${meta.flash.text}</div>`;
  }
};
</script>
```

`views/layout/footer.php` mantiene el JS del tema (`?tema=clar|fosc`) y el cierre de `</main></body></html>`. Si tiene lógica que necesita `meta`, puede leerla desde una variable global que cada vista fija antes del `include`.

## Vista existente: `views/jocs/llista.php`

```php
<?php include 'views/layout/header.php'; ?>
<div id="app-flash"></div>
<div class="jocs-container">
  <h2>Jocs Disponibles</h2>
  <div class="jocs-grid" id="jocs-grid"></div>
</div>
<script type="module">
const { data, meta } = await window.appFetch(location.href);
window.renderLayout(meta);
window.renderFlash(meta);

document.getElementById('jocs-grid').innerHTML = data.jocs.map(j => `
  <div class="joc-card">
    <h3>${j.nom_joc}</h3>
    <p>${j.descripcio}</p>
    <p>Puntuació màxima: ${j.puntuacio_maxima}</p>
    ${meta.loggedIn
      ? `<a href="/mvc/?route=/jugar&id=${j.id}" class="btn-jugar">Jugar</a>`
      : '<p><a href="/mvc/?route=/login">Inicia sessió per jugar</a></p>'}
  </div>`).join('');

if (meta.redirect) location.href = meta.redirect;
</script>
<?php include 'views/layout/footer.php'; ?>
```

El uso de `innerHTML` es deliberado para conservar el XSS del laboratorio.

### Patrón para formularios (ejemplo en `views/amics/index.php`)

```js
document.querySelectorAll('form[data-json]').forEach(form => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const body = Object.fromEntries(new FormData(form));
    const resposta = await window.appPost(form.action, body);
    window.renderFlash(resposta.meta);
    if (resposta.meta.redirect) location.href = resposta.meta.redirect;
  });
});
```

## Vista existente: `views/jocs/joc.php`

```php
<?php include 'views/layout/header.php'; ?>
<div id="app-flash"></div>
<div id="contenidor-joc"></div>
<script type="module">
const { data, meta } = await window.appFetch(location.href);
window.renderLayout(meta);
window.renderFlash(meta);

const cont = document.getElementById('contenidor-joc');
if (data.esJocHtml) {
  cont.innerHTML = `
    <div class="jocs-container">
      <h2>${data.joc.nom_joc}</h2>
      <iframe class="joc-frame" src="${data.urlJoc}" allowfullscreen></iframe>
    </div>`;
} else {
  const id = new URLSearchParams(location.search).get('id');
  cont.innerHTML = `<iframe class="joc-frame" src="${data.gameBaseUrl}index.php?joc_id=${id}" allowfullscreen></iframe>`;
}
</script>
<?php include 'views/layout/footer.php'; ?>
```

## `public/js/jocs/1/index.php` y `/2/index.php` (archivos existentes)

Sustituir los cuatro `<?= ... ?>` de los metas por valores fijados en JS:

```php
<meta name="game-joc-id" content="">
<meta name="game-nivell" content="">
<meta name="game-total-nivells" content="">
<meta name="game-player-name" content="">
<script type="module">
const id = new URLSearchParams(location.search).get('joc_id') || '1';
const res = await fetch('/mvc/?route=/jugar&id=' + encodeURIComponent(id), {
  headers: { Accept: 'application/json' },
  credentials: 'same-origin',
});
const { data } = await res.json();

document.querySelector('meta[name="game-joc-id"]').content = data.joc.id;
document.querySelector('meta[name="game-nivell"]').content = data.nivellActual;
document.querySelector('meta[name="game-total-nivells"]').content = data.totalNivells;
document.querySelector('meta[name="game-player-name"]').content = data.nomJugador;

const base = document.createElement('base');
base.href = data.gameBaseUrl;
document.head.prepend(base);

await import('./main.js');
</script>
```

En el juego 1 se mantiene `<script src="./classes.js">`. Se elimina el `<script src="./main.js" type="module">` original para no cargarlo dos veces. `conf.js` y `main.js` no cambian.

## `public/js/charts.js` (archivo existente)

Envolver el bloque final que recorre `.grafic`:

```js
window.renderGrafics = function () {
  document.querySelectorAll('.grafic').forEach(function (cont) {
    var dades = [];
    try { dades = JSON.parse(cont.getAttribute('data-dades') || '[]'); } catch (e) { }
    cont.textContent = '';
    if (!dades.length) { return buit(cont); }
    var t = cont.getAttribute('data-tipus');
    (t === 'linia' ? linia : t === 'donut' ? donut : barres)(cont, dades);
  });
};

window.renderGrafics();
```

La vista `views/estadistiques/index.php` construye los cinco `.grafic` a partir del JSON y llama a `window.renderGrafics()`.

## Puntos de atención

- `api/api.php` sigue intacto para los juegos ZIP que todavía lo usan.
- `POST /?route=/guardar-puntuacio` acepta el cuerpo form-urlencoded de los juegos ZIP y JSON de las cáscaras; las claves legacy se mantienen en la raíz de la respuesta para que los juegos no cambien.
- Los modelos vulnerables, `Session.php`, `Roles.php` y las vulnerabilidades V1-V6 se mantienen.
- La inclusión directa del juego (LFI) desaparece como `include` PHP: ahora se carga en un iframe desde la URL devuelta en el JSON.
- `meta.rol` expone el mismo valor Base64 de la cookie, conservando V1.

---

# Docker (únicos archivos nuevos)

## `docker/apache/Dockerfile`

```dockerfile
FROM php:8.2-apache-bookworm

RUN apt-get update \
  && apt-get install -y --no-install-recommends libzip-dev \
  && docker-php-ext-install pdo_mysql zip \
  && a2enmod rewrite \
  && rm -rf /var/lib/apt/lists/*

COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache/php.ini /usr/local/etc/php/conf.d/99-app.ini

WORKDIR /var/www/html
COPY index.php api core config controllers database models views public /var/www/html/
RUN mkdir -p public/js/jocs public/uploads \
  && ln -s /var/www/html /var/www/html/mvc \
  && chown -R www-data:www-data public/js/jocs public/uploads

EXPOSE 80
```

## `docker/apache/000-default.conf`

```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /var/www/html

    <Directory /var/www/html>
        Options FollowSymLinks
        AllowOverride None
        Require all granted

        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^ /index.php [QSA,L]
    </Directory>

    <DirectoryMatch "/(config|core|controllers|models|database|docker)/">
        Require all denied
    </DirectoryMatch>

    <Directory /var/www/html/views>
        Require all denied
    </Directory>

    <FilesMatch "^\.">
        Require all denied
    </FilesMatch>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

## `docker/apache/php.ini`

```ini
upload_max_filesize = 64M
post_max_size = 64M
cgi.fix_pathinfo = 0
session.cookie_httponly = 1
session.cookie_samesite = Lax
display_errors = Off
log_errors = On
```


> `Session.php` sobrescribe `session.cookie_httponly` a `0`; se deja así para no tocar esa vulnerabilidad.

## `docker-compose.yml`

```yaml
services:
  db:
    image: mariadb:11.4
    restart: unless-stopped
    environment:
      MARIADB_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MARIADB_DATABASE: ${DB_NAME}
      MARIADB_USER: ${DB_USER}
      MARIADB_PASSWORD: ${DB_PASSWORD}
    volumes:
      - db_data:/var/lib/mysql
      - ./database/init.sql:/docker-entrypoint-initdb.d/01-init.sql:ro
    healthcheck:
      test: ["CMD", "healthcheck.sh", "--connect", "--innodb_initialized"]
      interval: 10s
      timeout: 5s
      retries: 10
    networks: [backend]

  web:
    build:
      context: .
      dockerfile: docker/apache/Dockerfile
    restart: unless-stopped
    depends_on:
      db:
        condition: service_healthy
    environment:
      DB_HOST: db
      DB_NAME: ${DB_NAME}
      DB_USER: ${DB_USER}
      DB_PASSWORD: ${DB_PASSWORD}
    ports:
      - "${APP_PORT:-8080}:80"
    volumes:
      - games_data:/var/www/html/public/js/jocs
      - uploads_data:/var/www/html/public/uploads
    networks: [backend, frontend]

  phpmyadmin:
    image: phpmyadmin:5.2
    restart: unless-stopped
    depends_on: [db]
    environment:
      PMA_HOST: db
    ports:
      - "${PMA_PORT:-8081}:80"
    networks: [backend, frontend]

volumes:
  db_data:
  games_data:
  uploads_data:

networks:
  backend:
  frontend:
```

## `.env.example`

```dotenv
APP_PORT=8080
PMA_PORT=8081
DB_NAME=plataforma_videojocs
DB_USER=plataforma_user
DB_PASSWORD=123456789a
DB_ROOT_PASSWORD=canvia-this
```

`DB_USER`/`DB_PASSWORD` deben coincidir con los hardcodeados en `config/database.php:5-8` mientras no se modifique ese archivo.

---

# Verificación

```bash
cp /ruta/al/dump.sql Plataforma_Jocs/database/init.sql
docker compose config
docker compose up -d --build
docker compose exec web sh -c 'for f in index.php core/*.php controllers/*.php; do php -l "$f"; done'
docker compose exec web apachectl configtest
curl -i 'localhost:8080/mvc/?route=/jocs'
curl -i -H 'Accept: application/json' 'localhost:8080/mvc/?route=/jocs'
curl -i -H 'Accept: application/json' 'localhost:8080/mvc/?route=/jugar&id=1'
node --test Joc_Snake/tests/
```

Primera llamada: HTML de la cáscara. Segunda: `{ok, data, meta}` con el listado de juegos.

opencode -s ses_e0cae5f1cffe32eJcEb28mcqQ6
