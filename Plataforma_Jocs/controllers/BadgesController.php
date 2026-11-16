<?php
require_once 'models/BadgeModel.php';
require_once 'core/Session.php';

/**
 * Pàgina d'assoliments: tots els badges, els que l'usuari ja té i el progrés
 * dels que li falten. En entrar es tornen a avaluar (p. ex. per recollir els
 * assoliments de partides anteriors a l'existència dels badges).
 */
class BadgesController
{
    public function index()
    {
        Session::requireLogin();
        $usuariId = (int) Session::get('usuari_id');

        $model = new BadgeModel();
        $model->avaluar($usuariId);
        $badges = $model->ambEstat($usuariId);

        $obtinguts = count(array_filter($badges, fn($b) => $b['obtingut']));
        $total = count($badges);

        include 'views/badges/index.php';
    }
}
