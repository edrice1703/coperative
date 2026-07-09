<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modèle pour la table des véhicules.
 *
 * Gère les véhicules utilisés pour les trajets.
 */
class VehiculeModel extends Model
{
    protected $table = 'vehicules';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'immatriculation',
        'marque',
        'capacite',
        'etat',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
