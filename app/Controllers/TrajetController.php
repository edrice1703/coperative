<?php

namespace App\Controllers;

use App\Models\ChauffeurModel;
use App\Models\TrajetModel;
use App\Models\VehiculeModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Contrôleur des trajets.
 *
 * Gère la création, la modification, la suppression et la lecture des trajets.
 * Vérifie que le véhicule et le chauffeur sont disponibles lors de l'affectation.
 */
class TrajetController extends BaseController
{
    protected TrajetModel $modeleTrajet;
    protected VehiculeModel $modeleVehicule;
    protected ChauffeurModel $modeleChauffeur;

    public function __construct()
    {
        $this->modeleTrajet = new TrajetModel();
        $this->modeleVehicule = new VehiculeModel();
        $this->modeleChauffeur = new ChauffeurModel();
    }

    /**
     * Retourne la liste de tous les trajets.
     */
    public function lister(): ResponseInterface
    {
        $trajets = $this->modeleTrajet->findAll();
        return $this->response->setJSON(['trajets' => $trajets]);
    }

    /**
     * Retourne les détails d'un trajet par ID.
     */
    public function afficher(?int $id = null): ResponseInterface
    {
        if (! $id) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Identifiant manquant.']);
        }

        $trajet = $this->modeleTrajet->find($id);
        if (empty($trajet)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Trajet introuvable.']);
        }

        return $this->response->setJSON(['trajet' => $trajet]);
    }

    /**
     * Crée un trajet. Réservé aux administrateurs.
     * Vérifie l'existence et la disponibilité du véhicule et du chauffeur.
     */
    public function creer(): ResponseInterface
    {
        if ($erreur = $this->verifierAdmin()) {
            return $erreur;
        }

        $donnees = $this->obtenirDonneesRequete();
        if ($this->erreurJson !== null) {
            return $this->reponseErreurJson();
        }

        if (! $this->validate('trajet', $donnees)) {
            return $this->response->setStatusCode(400)->setJSON(['errors' => $this->validator->getErrors()]);
        }

        $vehicule = $this->modeleVehicule->find($donnees['vehicule_id']);
        if (empty($vehicule)) {
            return $this->response->setStatusCode(400)->setJSON(['errors' => ['vehicule_id' => 'Véhicule introuvable.']]);
        }

        $chauffeur = $this->modeleChauffeur->find($donnees['chauffeur_id']);
        if (empty($chauffeur)) {
            return $this->response->setStatusCode(400)->setJSON(['errors' => ['chauffeur_id' => 'Chauffeur introuvable.']]);
        }

        // Vérifie que le véhicule est en état DISPONIBLE avant affectation.
        if (($vehicule['etat'] ?? '') !== 'DISPONIBLE') {
            return $this->response->setStatusCode(400)->setJSON([
                'errors' => ['vehicule_id' => 'Le véhicule doit être disponible pour être affecté au trajet.'],
            ]);
        }

        if (($chauffeur['etat'] ?? '') !== 'DISPONIBLE') {
            return $this->response->setStatusCode(400)->setJSON([
                'errors' => ['chauffeur_id' => 'Le chauffeur doit être disponible pour être affecté au trajet.'],
            ]);
        }

        $trajet = [
            'depart' => trim($donnees['depart'] ?? ''),
            'arrivee' => trim($donnees['arrivee'] ?? ''),
            'date_depart' => trim($donnees['date_depart'] ?? ''),
            'heure_depart' => trim($donnees['heure_depart'] ?? ''),
            'prix' => $donnees['prix'] ?? 0,
            'places_restantes' => $donnees['places_restantes'] ?? 0,
            'vehicule_id' => $donnees['vehicule_id'],
            'chauffeur_id' => $donnees['chauffeur_id'],
        ];

        if (! $this->modeleTrajet->insert($trajet)) {
            return $this->response->setStatusCode(400)->setJSON([
                'errors' => $this->modeleTrajet->errors() ?: ['trajet' => 'Impossible de créer le trajet.'],
            ]);
        }

        $trajetCreer = $this->modeleTrajet->find($this->modeleTrajet->getInsertID());
        return $this->response->setStatusCode(201)->setJSON([
            'message' => 'Trajet créé avec succès.',
            'trajet' => $trajetCreer,
        ]);
    }

    /**
     * Met à jour un trajet existant. Réservé aux administrateurs.
     */
    public function modifier(?int $id = null): ResponseInterface
    {
        if ($erreur = $this->verifierAdmin()) {
            return $erreur;
        }

        if (! $id) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Identifiant manquant.']);
        }

        $trajetExistant = $this->modeleTrajet->find($id);
        if (empty($trajetExistant)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Trajet introuvable.']);
        }

        $donnees = $this->obtenirDonneesRequete();
        if ($this->erreurJson !== null) {
            return $this->reponseErreurJson();
        }

        if (! $this->validate('trajet', $donnees)) {
            return $this->response->setStatusCode(400)->setJSON(['errors' => $this->validator->getErrors()]);
        }

        $vehicule = $this->modeleVehicule->find($donnees['vehicule_id']);
        if (empty($vehicule)) {
            return $this->response->setStatusCode(400)->setJSON(['errors' => ['vehicule_id' => 'Véhicule introuvable.']]);
        }

        $chauffeur = $this->modeleChauffeur->find($donnees['chauffeur_id']);
        if (empty($chauffeur)) {
            return $this->response->setStatusCode(400)->setJSON(['errors' => ['chauffeur_id' => 'Chauffeur introuvable.']]);
        }

        // Vérifie que le véhicule est en état DISPONIBLE avant affectation.
        if (($vehicule['etat'] ?? '') !== 'DISPONIBLE') {
            return $this->response->setStatusCode(400)->setJSON([
                'errors' => ['vehicule_id' => 'Le véhicule doit être disponible pour être affecté au trajet.'],
            ]);
        }

        if (($chauffeur['etat'] ?? '') !== 'DISPONIBLE') {
            return $this->response->setStatusCode(400)->setJSON([
                'errors' => ['chauffeur_id' => 'Le chauffeur doit être disponible pour être affecté au trajet.'],
            ]);
        }

        $trajet = [
            'id' => $id,
            'depart' => trim($donnees['depart'] ?? ''),
            'arrivee' => trim($donnees['arrivee'] ?? ''),
            'date_depart' => trim($donnees['date_depart'] ?? ''),
            'heure_depart' => trim($donnees['heure_depart'] ?? ''),
            'prix' => $donnees['prix'] ?? 0,
            'places_restantes' => $donnees['places_restantes'] ?? 0,
            'vehicule_id' => $donnees['vehicule_id'],
            'chauffeur_id' => $donnees['chauffeur_id'],
        ];

        if (! $this->modeleTrajet->save($trajet)) {
            return $this->response->setStatusCode(400)->setJSON([
                'errors' => $this->modeleTrajet->errors() ?: ['trajet' => 'Impossible de mettre à jour le trajet.'],
            ]);
        }

        $trajetMisAJour = $this->modeleTrajet->find($id);
        return $this->response->setJSON([
            'message' => 'Trajet mis à jour.',
            'trajet' => $trajetMisAJour,
        ]);
    }

    /**
     * Supprime un trajet par ID. Réservé aux administrateurs.
     */
    public function supprimer(?int $id = null): ResponseInterface
    {
        if ($erreur = $this->verifierAdmin()) {
            return $erreur;
        }

        if (! $id) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Identifiant manquant.']);
        }

        $trajet = $this->modeleTrajet->find($id);
        if (empty($trajet)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Trajet introuvable.']);
        }

        $this->modeleTrajet->delete($id);
        return $this->response->setJSON(['message' => 'Trajet supprimé.']);
    }
}
