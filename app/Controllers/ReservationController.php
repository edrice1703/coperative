<?php

namespace App\Controllers;

use App\Models\ReservationModel;
use App\Models\TrajetModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Contrôleur des réservations.
 *
 * Gère la création, l'annulation et l'affichage des réservations.
 */
class ReservationController extends BaseController
{
    protected ReservationModel $modeleReservation;
    protected TrajetModel $modeleTrajet;

    public function __construct()
    {
        $this->modeleReservation = new ReservationModel();
        $this->modeleTrajet = new TrajetModel();
    }

    /**
     * Crée une nouvelle réservation pour l'utilisateur connecté.
     * Vérifie la disponibilité des places et met à jour le nombre de places restantes.
     */
    public function ajouter(): ResponseInterface
    {
        if ($erreur = $this->verifierAuthentifie()) {
            return $erreur;
        }

        $donnees = $this->obtenirDonneesRequete();
        if ($this->erreurJson !== null) {
            return $this->reponseErreurJson();
        }

        if (! $this->validate('reservation', $donnees)) {
            return $this->response->setStatusCode(400)->setJSON(['errors' => $this->validator->getErrors()]);
        }

        $trajet = $this->modeleTrajet->find($donnees['trajet_id']);
        if (empty($trajet)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Trajet introuvable.']);
        }

        $placesDemandees = (int) $donnees['nombre_places'];
        $placesDisponibles = (int) $trajet['places_restantes'];
        if ($placesDemandees > $placesDisponibles) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'Pas assez de places disponibles pour ce trajet.',
                'places_disponibles' => $placesDisponibles,
            ]);
        }

        $session = session();
        $utilisateur = $session->get('utilisateur');

        $reservation = [
            'utilisateur_id' => $utilisateur['id'],
            'trajet_id' => $donnees['trajet_id'],
            'nombre_places' => $placesDemandees,
            'statut' => 'CONFIRMEE',
        ];

        // Utilisation d'une transaction pour s'assurer que la réservation
        // et la mise à jour du nombre de places restent cohérentes.
        $db = \Config\Database::connect();
        $db->transStart();

        $inserted = $this->modeleReservation->insert($reservation);
        $updated = $this->modeleTrajet->save([
            'id' => $trajet['id'],
            'places_restantes' => $placesDisponibles - $placesDemandees,
        ]);

        $db->transComplete();

        if (! $db->transStatus() || ! $inserted || ! $updated) {
            return $this->response->setStatusCode(400)->setJSON([
                'errors' => $this->modeleReservation->errors() ?: ['reservation' => 'Impossible de créer la réservation.'],
            ]);
        }

        $reservationCree = $this->modeleReservation->find($this->modeleReservation->getInsertID());
        return $this->response->setStatusCode(201)->setJSON([
            'message' => 'Réservation créée avec succès.',
            'reservation' => $reservationCree,
        ]);
    }

    /**
     * Retourne les réservations du client connecté.
     */
    public function mesReservations(): ResponseInterface
    {
        if ($erreur = $this->verifierAuthentifie()) {
            return $erreur;
        }

        $session = session();
        $utilisateur = $session->get('utilisateur');

        $reservations = $this->modeleReservation
            ->select('reservations.*, trajets.depart, trajets.arrivee, trajets.date_depart, trajets.heure_depart, trajets.prix')
            ->join('trajets', 'trajets.id = reservations.trajet_id')
            ->where('reservations.utilisateur_id', $utilisateur['id'])
            ->findAll();

        return $this->response->setJSON(['reservations' => $reservations]);
    }

    /**
     * Annule une réservation et restitue les places au trajet.
     * Seul le propriétaire ou un administrateur peut effectuer cette action.
     */
    public function annuler(?int $id = null): ResponseInterface
    {
        if ($erreur = $this->verifierAuthentifie()) {
            return $erreur;
        }

        if (! $id) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Identifiant manquant.']);
        }

        $reservation = $this->modeleReservation->find($id);
        if (empty($reservation)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Réservation introuvable.']);
        }

        $session = session();
        $utilisateur = $session->get('utilisateur');
        if ($reservation['utilisateur_id'] !== $utilisateur['id'] && ($utilisateur['role'] ?? '') !== 'ADMIN') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Accès refusé à cette réservation.']);
        }

        if ($reservation['statut'] === 'ANNULEE') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Cette réservation est déjà annulée.']);
        }

        $trajet = $this->modeleTrajet->find($reservation['trajet_id']);

        // Annule la réservation et remet les places dans le trajet.
        $db = \Config\Database::connect();
        $db->transStart();

        if (! empty($trajet)) {
            $this->modeleTrajet->save([
                'id' => $trajet['id'],
                'places_restantes' => (int) $trajet['places_restantes'] + (int) $reservation['nombre_places'],
            ]);
        }

        $this->modeleReservation->save([
            'id' => $reservation['id'],
            'statut' => 'ANNULEE',
        ]);

        $db->transComplete();
        if (! $db->transStatus()) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Impossible d’annuler la réservation pour le moment.']);
        }

        $reservation['statut'] = 'ANNULEE';
        return $this->response->setJSON([
            'message' => 'Réservation annulée.',
            'reservation' => $reservation,
        ]);
    }

    /**
     * Liste toutes les réservations pour l'administrateur.
     */
    public function lister(): ResponseInterface
    {
        if ($erreur = $this->verifierAdmin()) {
            return $erreur;
        }

        $reservations = $this->modeleReservation
            ->select('reservations.*, utilisateurs.nom as utilisateur_nom, utilisateurs.email as utilisateur_email, trajets.depart, trajets.arrivee, trajets.date_depart')
            ->join('utilisateurs', 'utilisateurs.id = reservations.utilisateur_id')
            ->join('trajets', 'trajets.id = reservations.trajet_id')
            ->findAll();

        return $this->response->setJSON(['reservations' => $reservations]);
    }
}
