<?php
// Incloure els fitxers per gestionar les rutes i controlar les sessions d'usuari
require_once 'core/Router.php';
require_once 'core/Session.php';
require_once 'core/Roles.php';
Session::start();
require_once 'controllers/AuthController.php';
require_once 'controllers/JocController.php';
require_once 'controllers/UsuariController.php';
require_once 'controllers/GestioJocsController.php';
require_once 'controllers/AmicsController.php';
require_once 'controllers/EstadistiquesController.php';
require_once 'controllers/BadgesController.php';

// VULNERABILITAT: Headers de seguretat absents

$router = new Router(); // Emmagatzema les rutes i redirigeix peticions GET i POST al controlador corresponent

// DEFINICIÓ DE RUTES (Cada ruta disposa de la URL, el Controlador i el Mètode que s'executa)
// Rutes d'autenticació
$router->get('/login', 'AuthController', 'mostrarLogin');
$router->post('/login', 'AuthController', 'login');
$router->get('/registre', 'AuthController', 'mostrarRegistre');
$router->post('/registre', 'AuthController', 'registre');
$router->get('/logout', 'AuthController', 'logout');

// Rutes de jocs
$router->get('/', 'JocController', 'llistaJocs');
$router->get('/jocs', 'JocController', 'llistaJocs');
$router->get('/jugar', 'JocController', 'jugar');
$router->post('/guardar-puntuacio', 'JocController', 'guardarPuntuacio');

// Rutes d'usuari
$router->get('/perfil', 'UsuariController', 'perfil');
$router->get('/ranking', 'UsuariController', 'ranking');
$router->post('/actualitzar-perfil', 'UsuariController', 'actualitzarPerfil');

// Rutes de gestió de jocs (CRUD: només admin i developer segons la cookie "rol")
$router->get('/gestio-jocs', 'GestioJocsController', 'llista');
$router->get('/gestio-jocs/nou', 'GestioJocsController', 'nou');
$router->post('/gestio-jocs/crear', 'GestioJocsController', 'crear');
$router->get('/gestio-jocs/editar', 'GestioJocsController', 'editar');
$router->post('/gestio-jocs/actualitzar', 'GestioJocsController', 'actualitzar');
$router->post('/gestio-jocs/eliminar', 'GestioJocsController', 'eliminar');

// Rutes del sistema d'amics
$router->get('/amics', 'AmicsController', 'index');
$router->post('/amics/solicitar', 'AmicsController', 'solicitar');
$router->post('/amics/respondre', 'AmicsController', 'respondre');
$router->post('/amics/eliminar', 'AmicsController', 'eliminar');
$router->get('/usuari', 'AmicsController', 'perfilPublic');

// Estadístiques avançades amb gràfics
$router->get('/estadistiques', 'EstadistiquesController', 'index');

// Badges / assoliments
$router->get('/badges', 'BadgesController', 'index');

// REP PETICIONS DE L'USUARI: Detecta el mètode HTTP i la ruta i redirigeix al controlador corresponent, sinó mostra un error.
$router->dispatch();
