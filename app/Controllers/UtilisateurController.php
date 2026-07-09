<?php

namespace App\Controllers;

use App\Models\UtilisateurModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * Contrôleur des utilisateurs.
 *
 * Gère l'inscription, la connexion, la déconnexion, la lecture, la modification
 * et la suppression des comptes utilisateur.
 */
class UtilisateurController extends BaseController
{
    protected UtilisateurModel $modeleUtilisateur;

    public function __construct()
    {
        $this->modeleUtilisateur = new UtilisateurModel();
        helper(['form']);
    }

    /**
     * Crée un nouveau compte utilisateur.
     * Attend un payload JSON avec nom, prenom, telephone, email, mot_de_passe et role.
     */
    public function inscrire(): ResponseInterface
    {
        $donnees = $this->obtenirDonneesRequete();
        if ($this->erreurJson !== null) {
            return $this->reponseErreurJson();
        }

        if (! $this->validate('utilisateur', $donnees)) {
            return $this->response->setStatusCode(400)->setJSON([
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $donneesUtilisateur = [
            'nom' => trim($donnees['nom'] ?? ''),
            'prenom' => trim($donnees['prenom'] ?? ''),
            'telephone' => trim($donnees['telephone'] ?? ''),
            'email' => trim($donnees['email'] ?? ''),
            'mot_de_passe' => $donnees['mot_de_passe'] ?? '',
            'role' => $donnees['role'] ?? 'CLIENT',
        ];

        if (! $this->modeleUtilisateur->insert($donneesUtilisateur)) {
            return $this->response->setStatusCode(400)->setJSON([
                'errors' => $this->modeleUtilisateur->errors() ?: ['utilisateur' => 'Impossible de créer l’utilisateur.'],
            ]);
        }

        $utilisateur = $this->modeleUtilisateur->find($this->modeleUtilisateur->getInsertID());
        unset($utilisateur['mot_de_passe']);

        return $this->response->setStatusCode(201)->setJSON([
            'message' => 'Utilisateur créé avec succès.',
            'utilisateur' => $utilisateur,
        ]);
    }

    /**
     * Authentifie un utilisateur et crée une session.
     */
    public function connexion(): ResponseInterface
    {
        $donnees = $this->obtenirDonneesRequete();
        if ($this->erreurJson !== null) {
            return $this->reponseErreurJson();
        }

        if (! $this->validate('utilisateurLogin', $donnees)) {
            return $this->response->setStatusCode(400)->setJSON([
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $email = trim($donnees['email'] ?? '');
        $motDePasse = $donnees['mot_de_passe'] ?? '';

        $utilisateur = $this->modeleUtilisateur->where('email', $email)->first();
        if (empty($utilisateur) || ! password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
            return $this->response->setStatusCode(401)->setJSON([
                'error' => 'Identifiants invalides.',
            ]);
        }

        $session = session();
        $session->set('utilisateur', [
            'id' => $utilisateur['id'],
            'email' => $utilisateur['email'],
            'role' => $utilisateur['role'],
        ]);

        unset($utilisateur['mot_de_passe']);
        return $this->response->setJSON([
            'message' => 'Connexion réussie.',
            'utilisateur' => $utilisateur,
        ]);
    }

    /**
     * Supprime la session de l'utilisateur connecté.
     */
    public function deconnexion(): ResponseInterface
    {
        $session = session();
        $session->remove('utilisateur');

        return $this->response->setJSON([
            'message' => 'Déconnexion réussie.',
        ]);
    }

    /**
     * Retourne la liste de tous les utilisateurs.
     * Réservé aux administrateurs.
     */
    public function lister(): ResponseInterface
    {
        if ($erreur = $this->verifierAdmin()) {
            return $erreur;
        }

        $utilisateurs = $this->modeleUtilisateur->findAll();
        foreach ($utilisateurs as &$utilisateur) {
            unset($utilisateur['mot_de_passe']);
        }
        unset($utilisateur);

        return $this->response->setJSON(['utilisateurs' => $utilisateurs]);
    }

    /**
     * Retourne le profil de l'utilisateur connecté.
     */
    public function profil(): ResponseInterface
    {
        $utilisateur = $this->utilisateurConnecte();
        if (empty($utilisateur)) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Authentification requise.']);
        }

        $user = $this->modeleUtilisateur->find($utilisateur['id']);
        if (empty($user)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Utilisateur introuvable.']);
        }

        unset($user['mot_de_passe']);
        return $this->response->setJSON(['utilisateur' => $user]);
    }

    /**
     * Affiche le détail d'un utilisateur par ID.
     */
    public function afficher(?int $id = null): ResponseInterface
    {
        if (! $id) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Identifiant manquant.']);
        }

        $utilisateur = $this->modeleUtilisateur->find($id);
        if (empty($utilisateur)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Utilisateur introuvable.']);
        }

        unset($utilisateur['mot_de_passe']);

        return $this->response->setJSON(['utilisateur' => $utilisateur]);
    }

    /**
     * Met à jour un utilisateur existant.
     * Le mot de passe est modifié uniquement si le champ mot_de_passe est présent.
     */
    public function modifier(?int $id = null): ResponseInterface
    {
        if (! $id) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Identifiant manquant.']);
        }

        $utilisateurExistant = $this->modeleUtilisateur->find($id);
        if (empty($utilisateurExistant)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Utilisateur introuvable.']);
        }

        $donneesRecues = $this->obtenirDonneesRequete();
        if ($this->erreurJson !== null) {
            return $this->reponseErreurJson();
        }

        $rules = [
            'nom' => 'required|max_length[100]',
            'prenom' => 'required|max_length[100]',
            'telephone' => 'required|max_length[20]|regex_match[/^[0-9 +()\-]+$/]|is_unique[utilisateurs.telephone,id,' . $id . ']',
            'email' => 'required|valid_email|max_length[150]|is_unique[utilisateurs.email,id,' . $id . ']',
            'mot_de_passe' => 'permit_empty|min_length[8]',
            'role' => 'required|in_list[ADMIN,CLIENT]',
        ];

        if (! $this->validate($rules, $donneesRecues)) {
            return $this->response->setStatusCode(400)->setJSON([
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $donneesUtilisateur = [
            'id' => $id,
            'nom' => trim($donneesRecues['nom'] ?? ''),
            'prenom' => trim($donneesRecues['prenom'] ?? ''),
            'telephone' => trim($donneesRecues['telephone'] ?? ''),
            'email' => trim($donneesRecues['email'] ?? ''),
            'role' => $donneesRecues['role'] ?? 'CLIENT',
        ];

        if (! empty($donneesRecues['mot_de_passe'])) {
            $donneesUtilisateur['mot_de_passe'] = $donneesRecues['mot_de_passe'];
        }

        if (! $this->modeleUtilisateur->save($donneesUtilisateur)) {
            return $this->response->setStatusCode(400)->setJSON([
                'errors' => $this->modeleUtilisateur->errors() ?: ['utilisateur' => 'Impossible de mettre à jour l’utilisateur.'],
            ]);
        }

        $utilisateur = $this->modeleUtilisateur->find($id);
        unset($utilisateur['mot_de_passe']);

        return $this->response->setJSON([
            'message' => 'Utilisateur mis à jour.',
            'utilisateur' => $utilisateur,
        ]);
    }

    /**
     * Supprime un utilisateur par ID.
     */
    public function supprimer(?int $id = null): ResponseInterface
    {
        if (! $id) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Identifiant manquant.']);
        }

        $utilisateur = $this->modeleUtilisateur->find($id);
        if (empty($utilisateur)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Utilisateur introuvable.']);
        }

        $this->modeleUtilisateur->delete($id);

        return $this->response->setJSON(['message' => 'Utilisateur supprimé.']);
    }
}
