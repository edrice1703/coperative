<?php

namespace App\Controllers;

use App\Models\ChauffeurModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Contrôleur des chauffeurs.
 *
 * Gère la création, la modification, la suppression et la lecture des chauffeurs.
 */
class ChauffeurController extends BaseController
{
    protected ChauffeurModel $modeleChauffeur;

    public function __construct()
    {
        $this->modeleChauffeur = new ChauffeurModel();
    }

    /**
     * Retourne la liste de tous les chauffeurs.
     */
    public function lister(): ResponseInterface
    {
        $chauffeurs = $this->modeleChauffeur->findAll();
        return $this->response->setJSON(['chauffeurs' => $chauffeurs]);
    }

    /**
     * Retourne un chauffeur par son ID.
     */
    public function afficher(?int $id = null): ResponseInterface
    {
        if (! $id) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Identifiant manquant.']);
        }

        $chauffeur = $this->modeleChauffeur->find($id);
        if (empty($chauffeur)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Chauffeur introuvable.']);
        }

        return $this->response->setJSON(['chauffeur' => $chauffeur]);
    }

    /**
     * Crée un chauffeur. Réservé aux administrateurs.
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

        if (! $this->validate('chauffeur', $donnees)) {
            return $this->response->setStatusCode(400)->setJSON(['errors' => $this->validator->getErrors()]);
        }

        $chauffeur = [
            'nom' => trim($donnees['nom'] ?? ''),
            'prenom' => trim($donnees['prenom'] ?? ''),
            'telephone' => trim($donnees['telephone'] ?? ''),
            'numero_permis' => trim($donnees['numero_permis'] ?? ''),
            'etat' => $donnees['etat'] ?? 'DISPONIBLE',
        ];

        if (! $this->modeleChauffeur->insert($chauffeur)) {
            return $this->response->setStatusCode(400)->setJSON([
                'errors' => $this->modeleChauffeur->errors() ?: ['chauffeur' => 'Impossible de créer le chauffeur.'],
            ]);
        }

        $chauffeurCree = $this->modeleChauffeur->find($this->modeleChauffeur->getInsertID());
        return $this->response->setStatusCode(201)->setJSON([
            'message' => 'Chauffeur créé avec succès.',
            'chauffeur' => $chauffeurCree,
        ]);
    }

    /**
     * Met à jour un chauffeur existant. Réservé aux administrateurs.
     */
    public function modifier(?int $id = null): ResponseInterface
    {
        if ($erreur = $this->verifierAdmin()) {
            return $erreur;
        }

        if (! $id) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Identifiant manquant.']);
        }

        $chauffeurExistant = $this->modeleChauffeur->find($id);
        if (empty($chauffeurExistant)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Chauffeur introuvable.']);
        }

        $donnees = $this->obtenirDonneesRequete();
        if ($this->erreurJson !== null) {
            return $this->reponseErreurJson();
        }

        $rules = [
            'nom' => 'required|max_length[100]',
            'prenom' => 'required|max_length[100]',
            'telephone' => 'required|max_length[20]|regex_match[/^[0-9 +()\-]+$/]|is_unique[chauffeurs.telephone,id,' . $id . ']',
            'numero_permis' => 'required|max_length[50]|is_unique[chauffeurs.numero_permis,id,' . $id . ']',
            'etat' => 'required|in_list[DISPONIBLE,EN_MISSION,ABSENT]',
        ];

        if (! $this->validate($rules, $donnees)) {
            return $this->response->setStatusCode(400)->setJSON(['errors' => $this->validator->getErrors()]);
        }

        $chauffeur = [
            'id' => $id,
            'nom' => trim($donnees['nom'] ?? ''),
            'prenom' => trim($donnees['prenom'] ?? ''),
            'telephone' => trim($donnees['telephone'] ?? ''),
            'numero_permis' => trim($donnees['numero_permis'] ?? ''),
            'etat' => $donnees['etat'] ?? 'DISPONIBLE',
        ];

        if (! $this->modeleChauffeur->save($chauffeur)) {
            return $this->response->setStatusCode(400)->setJSON([
                'errors' => $this->modeleChauffeur->errors() ?: ['chauffeur' => 'Impossible de mettre à jour le chauffeur.'],
            ]);
        }

        $chauffeurMisAJour = $this->modeleChauffeur->find($id);
        return $this->response->setJSON([
            'message' => 'Chauffeur mis à jour.',
            'chauffeur' => $chauffeurMisAJour,
        ]);
    }

    /**
     * Supprime un chauffeur par ID. Réservé aux administrateurs.
     */
    public function supprimer(?int $id = null): ResponseInterface
    {
        if ($erreur = $this->verifierAdmin()) {
            return $erreur;
        }

        if (! $id) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Identifiant manquant.']);
        }

        $chauffeur = $this->modeleChauffeur->find($id);
        if (empty($chauffeur)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Chauffeur introuvable.']);
        }

        $this->modeleChauffeur->delete($id);
        return $this->response->setJSON(['message' => 'Chauffeur supprimé.']);
    }
}
