<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modèle pour la table des utilisateurs.
 *
 * Hash le mot de passe avant insertion ou mise à jour.
 */
class UtilisateurModel extends Model
{
    protected $table = 'utilisateurs';

    protected $primaryKey = 'id';

    protected $allowedFields = [
        'nom',
        'prenom',
        'telephone',
        'email',
        'mot_de_passe',
        'role'
    ];

    protected $useTimestamps = true;

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    /**
     * Hash le mot de passe avant d'enregistrer l'utilisateur en base.
     *
     * Si le mot de passe est déjà hashé, il est conservé tel quel.
     */
    protected function hashPassword(array $data): array
    {
        if (empty($data['data']['mot_de_passe'])) {
            return $data;
        }

        $passwordInfo = password_get_info($data['data']['mot_de_passe']);
        if (! isset($passwordInfo['algo']) || $passwordInfo['algo'] === 0) {
            $data['data']['mot_de_passe'] = password_hash($data['data']['mot_de_passe'], PASSWORD_DEFAULT);
        }

        return $data;
    }
}