<?php
require_once __DIR__ . '/../models/BadgeModel.php';
require_once __DIR__ . '/../core/Session.php';

/**
 * Pàgina d'assoliments: tots els badges, els que l'usuari ja té i el progrés
 * dels que li falten. En entrar es tornen a avaluar (p. ex. per recollir els
 * assoliments de partides anteriors a l'existència dels badges).
 */
class BadgesController
{
    public function index()
    {
        if (!Session::isLoggedIn()) {
            json_error('Cal iniciar sessió', 401, [], ['redirect' => '/mvc/?route=/login']);
        }
        $usuariId = (int) Session::get('usuari_id');

        $model = new BadgeModel();
        $model->avaluar($usuariId);
        $badges = $model->ambEstat($usuariId);

        $obtinguts = count(array_filter($badges, fn($b) => $b['obtingut']));
        $total = count($badges);

        json_ok([
            'badges' => $badges,
            'obtinguts' => $obtinguts,
            'total' => $total,
        ]);
    }
}
