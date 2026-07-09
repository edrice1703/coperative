<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];

    /**
     * Stores parsed request data for the current request.
     *
     * @var array|null
     */
    protected ?array $donneesRequete = null;

    /**
     * Stores the last JSON parse error message, if the request body was invalid JSON.
     *
     * @var string|null
     */
    protected ?string $erreurJson = null;

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = service('session');
    }

    /**
     * Returns the request data as an array.
     *
     * This handles JSON body payloads as well as standard POST/PUT form input.
     */
    /**
     * Lit le corps de la requête et retourne les données sous forme de tableau.
     *
     * Cette méthode accepte à la fois les requêtes JSON et les formulaires classiques.
     * Elle met en cache les données pour qu'elles ne soient parsées qu'une seule fois.
     */
    protected function obtenirDonneesRequete(): array
    {
        if ($this->donneesRequete !== null) {
            return $this->donneesRequete;
        }

        $contenu = trim($this->request->getBody() ?? '');
        $enteteType = strtolower($this->request->getServer('CONTENT_TYPE') ?? '');

        if ($contenu !== '' && str_contains($enteteType, 'application/json')) {
            $decode = json_decode($contenu, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->erreurJson = json_last_error_msg();
                return $this->donneesRequete = [];
            }

            return $this->donneesRequete = is_array($decode) ? $decode : [];
        }

        $post = $this->request->getPost();
        if (! empty($post)) {
            return $this->donneesRequete = $post;
        }

        $rawInput = $this->request->getRawInput();
        if (is_array($rawInput) && ! empty($rawInput)) {
            return $this->donneesRequete = $rawInput;
        }

        return $this->donneesRequete = [];
    }

    /**
     * Retourne une réponse JSON lorsque le corps de la requête JSON est invalide.
     */
    protected function reponseErreurJson(): ResponseInterface
    {
        return $this->response->setStatusCode(400)->setJSON([
            'error' => 'JSON invalide.',
            'message' => $this->erreurJson ?? 'Le corps de la requête JSON est invalide.',
        ]);
    }

    /**
     * Retourne le tableau de données de l'utilisateur connecté depuis la session.
     */
    protected function utilisateurConnecte(): ?array
    {
        $session = session();
        return $session->get('utilisateur') ?? null;
    }

    /**
     * Vérifie qu'un utilisateur est connecté et renvoie une erreur 401 sinon.
     */
    protected function verifierAuthentifie(): ?ResponseInterface
    {
        if (empty($this->utilisateurConnecte())) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Authentification requise.']);
        }

        return null;
    }

    /**
     * Vérifie que l'utilisateur connecté est un administrateur.
     * Renvoie 401 si non connecté, 403 si l'utilisateur n'est pas ADMIN.
     */
    protected function verifierAdmin(): ?ResponseInterface
    {
        if ($erreur = $this->verifierAuthentifie()) {
            return $erreur;
        }

        $utilisateur = $this->utilisateurConnecte();
        if (($utilisateur['role'] ?? '') !== 'ADMIN') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Accès refusé.']);
        }

        return null;
    }
}
