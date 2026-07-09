<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modèle pour la table des réservations.
 *
 * Utilisé pour enregistrer, annuler et lister les réservations.
 */
class ReservationModel extends Model
{
    protected $table = 'reservations';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'utilisateur_id',
        'trajet_id',
        'nombre_places',
        'statut',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
