<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('UtilisateurController');
$routes->setDefaultMethod('inscrire');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

// API utilisteur endpoints
$routes->group('api', function ($routes) {
    $routes->post('utilisateurs', 'UtilisateurController::inscrire');
    $routes->post('utilisateurs/connexion', 'UtilisateurController::connexion');
    $routes->get('utilisateurs/mon-profil', 'UtilisateurController::profil');
    $routes->get('utilisateurs', 'UtilisateurController::lister');
    $routes->get('utilisateurs/(:num)', 'UtilisateurController::afficher/$1');
    $routes->put('utilisateurs/(:num)', 'UtilisateurController::modifier/$1');
    $routes->delete('utilisateurs/(:num)', 'UtilisateurController::supprimer/$1');
    $routes->post('utilisateurs/deconnexion', 'UtilisateurController::deconnexion');

    $routes->get('trajets', 'TrajetController::lister');
    $routes->get('trajets/(:num)', 'TrajetController::afficher/$1');
    $routes->post('trajets', 'TrajetController::creer');
    $routes->put('trajets/(:num)', 'TrajetController::modifier/$1');
    $routes->delete('trajets/(:num)', 'TrajetController::supprimer/$1');

    $routes->post('reservations', 'ReservationController::ajouter');
    $routes->get('reservations/mes-reservations', 'ReservationController::mesReservations');
    $routes->post('reservations/(:num)/annuler', 'ReservationController::annuler/$1');
    $routes->get('reservations', 'ReservationController::lister');

    $routes->get('vehicules', 'VehiculeController::lister');
    $routes->get('vehicules/(:num)', 'VehiculeController::afficher/$1');
    $routes->post('vehicules', 'VehiculeController::creer');
    $routes->put('vehicules/(:num)', 'VehiculeController::modifier/$1');
    $routes->delete('vehicules/(:num)', 'VehiculeController::supprimer/$1');

    $routes->get('chauffeurs', 'ChauffeurController::lister');
    $routes->get('chauffeurs/(:num)', 'ChauffeurController::afficher/$1');
    $routes->post('chauffeurs', 'ChauffeurController::creer');
    $routes->put('chauffeurs/(:num)', 'ChauffeurController::modifier/$1');
    $routes->delete('chauffeurs/(:num)', 'ChauffeurController::supprimer/$1');
});
