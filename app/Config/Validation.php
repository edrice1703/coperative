<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Validation\StrictRules\CreditCardRules;
use CodeIgniter\Validation\StrictRules\FileRules;
use CodeIgniter\Validation\StrictRules\FormatRules;
use CodeIgniter\Validation\StrictRules\Rules;

class Validation extends BaseConfig
{
    // --------------------------------------------------------------------
    // Setup
    // --------------------------------------------------------------------

    /**
     * Stores the classes that contain the
     * rules that are available.
     *
     * @var list<string>
     */
    public array $ruleSets = [
        Rules::class,
        FormatRules::class,
        FileRules::class,
        CreditCardRules::class,
    ];

    /**
     * Specifies the views that are used to display the
     * errors.
     *
     * @var array<string, string>
     */
    public array $templates = [
        'list'   => 'CodeIgniter\Validation\Views\list',
        'single' => 'CodeIgniter\Validation\Views\single',
    ];

    // --------------------------------------------------------------------
    // Rules
    // --------------------------------------------------------------------

    public array $utilisateur = [
        'nom' => 'required|max_length[100]',
        'prenom' => 'required|max_length[100]',
        'telephone' => 'required|max_length[20]|regex_match[/^[0-9 +()\-]+$/]|is_unique[utilisateurs.telephone]',
        'email' => 'required|valid_email|max_length[150]|is_unique[utilisateurs.email]',
        'mot_de_passe' => 'required|min_length[8]',
        'role' => 'permit_empty|in_list[ADMIN,CLIENT]',
    ];

    public array $utilisateurLogin = [
        'email' => 'required|valid_email|max_length[150]',
        'mot_de_passe' => 'required|min_length[8]',
    ];

    public array $trajet = [
        'depart' => 'required|max_length[150]',
        'arrivee' => 'required|max_length[150]',
        'date_depart' => 'required|valid_date[Y-m-d]',
        'heure_depart' => 'required|regex_match[/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/]',
        'prix' => 'required|decimal|greater_than_equal_to[0]',
        'places_restantes' => 'required|integer|greater_than_equal_to[0]',
        'vehicule_id' => 'required|integer',
        'chauffeur_id' => 'required|integer',
    ];

    public array $vehicule = [
        'immatriculation' => 'required|max_length[30]|is_unique[vehicules.immatriculation]',
        'marque' => 'required|max_length[100]',
        'capacite' => 'required|integer|greater_than[0]',
        'etat' => 'required|in_list[DISPONIBLE,EN_SERVICE,EN_PANNE]',
    ];

    public array $chauffeur = [
        'nom' => 'required|max_length[100]',
        'prenom' => 'required|max_length[100]',
        'telephone' => 'required|max_length[20]|regex_match[/^[0-9 +()\-]+$/]|is_unique[chauffeurs.telephone]',
        'numero_permis' => 'required|max_length[50]|is_unique[chauffeurs.numero_permis]',
        'etat' => 'required|in_list[DISPONIBLE,EN_MISSION,ABSENT]',
    ];

    public array $reservation = [
        'trajet_id' => 'required|integer',
        'nombre_places' => 'required|integer|greater_than[0]',
    ];
}
