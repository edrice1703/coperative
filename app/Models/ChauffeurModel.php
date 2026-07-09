<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modèle pour la table des chauffeurs.
 *
 * Gère les chauffeurs affectés aux trajets.
 */
class ChauffeurModel extends Model
{
    protected $table = 'chauffeurs';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'nom',
        'prenom',
        'telephone',
        'numero_permis',
        'etat',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
