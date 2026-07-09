<?php

namespace App\Controllers;

use App\Models\VehiculeModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Contrôleur des véhicules.
 *
 * Gère la création, la modification, la suppression et la lecture des véhicules.
 */
class VehiculeController extends BaseController
{
    protected VehiculeModel $modeleVehicule;

    public function __construct()
    {
        $this->modeleVehicule = new VehiculeModel();
    }

    /**
     * Retourne la liste de tous les véhicules.
     */
    public function lister(): ResponseInterface
    {
        $vehicules = $this->modeleVehicule->findAll();
        return $this->response->setJSON(['vehicules' => $vehicules]);
    }

    /**
     * Retourne un véhicule par son ID.
     */
    public function afficher(?int $id = null): ResponseInterface
    {
        if (! $id) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Identifiant manquant.']);
        }

        $vehicule = $this->modeleVehicule->find($id);
        if (empty($vehicule)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Véhicule introuvable.']);
        }

        return $this->response->setJSON(['vehicule' => $vehicule]);
    }

    /**
     * Crée un véhicule. Réservé aux administrateurs.
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

        if (! $this->validate('vehicule', $donnees)) {
            return $this->response->setStatusCode(400)->setJSON(['errors' => $this->validator->getErrors()]);
        }

        $vehicule = [
            'immatriculation' => trim($donnees['immatriculation'] ?? ''),
            'marque' => trim($donnees['marque'] ?? ''),
            'capacite' => (int) ($donnees['capacite'] ?? 0),
            'etat' => $donnees['etat'] ?? 'DISPONIBLE',
        ];

        if (! $this->modeleVehicule->insert($vehicule)) {
            return $this->response->setStatusCode(400)->setJSON([
                'errors' => $this->modeleVehicule->errors() ?: ['vehicule' => 'Impossible de créer le véhicule.'],
            ]);
        }

        $vehiculeCree = $this->modeleVehicule->find($this->modeleVehicule->getInsertID());
        return $this->response->setStatusCode(201)->setJSON([
            'message' => 'Véhicule créé avec succès.',
            'vehicule' => $vehiculeCree,
        ]);
    }

    /**
     * Met à jour un véhicule existant. Réservé aux administrateurs.
     */
    public function modifier(?int $id = null): ResponseInterface
    {
        if ($erreur = $this->verifierAdmin()) {
            return $erreur;
        }

        if (! $id) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Identifiant manquant.']);
        }

        $vehiculeExistant = $this->modeleVehicule->find($id);
        if (empty($vehiculeExistant)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Véhicule introuvable.']);
        }

        $donnees = $this->obtenirDonneesRequete();
        if ($this->erreurJson !== null) {
            return $this->reponseErreurJson();
        }

        $rules = [
            'immatriculation' => 'required|max_length[30]|is_unique[vehicules.immatriculation,id,' . $id . ']',
            'marque' => 'required|max_length[100]',
            'capacite' => 'required|integer|greater_than[0]',
            'etat' => 'required|in_list[DISPONIBLE,EN_SERVICE,EN_PANNE]',
        ];

        if (! $this->validate($rules, $donnees)) {
            return $this->response->setStatusCode(400)->setJSON(['errors' => $this->validator->getErrors()]);
        }

        $vehicule = [
            'id' => $id,
            'immatriculation' => trim($donnees['immatriculation'] ?? ''),
            'marque' => trim($donnees['marque'] ?? ''),
            'capacite' => (int) ($donnees['capacite'] ?? 0),
            'etat' => $donnees['etat'] ?? 'DISPONIBLE',
        ];

        if (! $this->modeleVehicule->save($vehicule)) {
            return $this->response->setStatusCode(400)->setJSON([
                'errors' => $this->modeleVehicule->errors() ?: ['vehicule' => 'Impossible de mettre à jour le véhicule.'],
            ]);
        }

        $vehiculeMisAJour = $this->modeleVehicule->find($id);
        return $this->response->setJSON([
            'message' => 'Véhicule mis à jour.',
            'vehicule' => $vehiculeMisAJour,
        ]);
    }

    /**
     * Supprime un véhicule par ID. Réservé aux administrateurs.
     */
    public function supprimer(?int $id = null): ResponseInterface
    {
        if ($erreur = $this->verifierAdmin()) {
            return $erreur;
        }

        if (! $id) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Identifiant manquant.']);
        }

        $vehicule = $this->modeleVehicule->find($id);
        if (empty($vehicule)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Véhicule introuvable.']);
        }

        $this->modeleVehicule->delete($id);
        return $this->response->setJSON(['message' => 'Véhicule supprimé.']);
    }
}
