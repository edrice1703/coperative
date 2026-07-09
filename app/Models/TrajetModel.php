<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modèle pour la table des trajets.
 *
 * Permet de lire et d'écrire les trajets en base.
 */
class TrajetModel extends Model
{
    protected $table = 'trajets';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'depart',
        'arrivee',
        'date_depart',
        'heure_depart',
        'prix',
        'places_restantes',
        'vehicule_id',
        'chauffeur_id',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
