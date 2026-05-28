<?php
/**
 * Streaming Controller
 * Gère l'accès aux contenus (films et tantara)
 */

if (!defined('ABSPATH')) exit;

class Malagasy_Streaming_Controller {

    private $film_repository;
    private $tantara_repository;

    public function __construct() {
        $this->film_repository = new Malagasy_Film_Repository();
        $this->tantara_repository = new Malagasy_Tantara_Repository();

        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Enregistre les routes API
     */
    public function register_routes() {
        // Route pour un film spécifique
        register_rest_route('malagasy/v1', '/film/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_film'],
            'permission_callback' => [Malagasy_Auth_Middleware::class, 'is_authenticated']
        ]);

        // Route pour un tantara spécifique
        register_rest_route('malagasy/v1', '/tantara/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_tantara'],
            'permission_callback' => [Malagasy_Auth_Middleware::class, 'is_authenticated']
        ]);

        // Route pour le streaming d'un film
        register_rest_route('malagasy/v1', '/film/(?P<id>\d+)/stream', [
            'methods' => 'GET',
            'callback' => [$this, 'get_film_stream'],
            'permission_callback' => [Malagasy_Auth_Middleware::class, 'is_authenticated']
        ]);

        // Route pour le streaming d'un tantara
        register_rest_route('malagasy/v1', '/tantara/(?P<id>\d+)/stream', [
            'methods' => 'GET',
            'callback' => [$this, 'get_tantara_stream'],
            'permission_callback' => [Malagasy_Auth_Middleware::class, 'is_authenticated']
        ]);
    }

    /**
     * Récupère les détails d'un film
     */
    public function get_film($request) {
        $film_id = (int) $request['id'];
        $user_id = get_current_user_id();

        $film = $this->film_repository->find($film_id);

        if (!$film) {
            return new WP_Error(
                'film_not_found',
                'Film introuvable.',
                ['status' => 404]
            );
        }

        // Vérifier l'accès
        $access_check = Malagasy_Auth_Middleware::verify_content_access($user_id, $film_id);
        if (is_wp_error($access_check)) {
            return $access_check;
        }

        $subscription = Malagasy_User_Service::get_subscription($user_id);

        return rest_ensure_response([
            'id' => $film->id,
            'title' => $film->title,
            'content' => $film->content,
            'realisateur' => $film->realisateur,
            'annee' => $film->annee,
            'duree' => $film->duree,
            'licence' => $film->licence,
            'access_type' => $film->access_type,
            'user_subscription' => $subscription
        ]);
    }

    /**
     * Récupère les détails d'un tantara
     */
    public function get_tantara($request) {
        $tantara_id = (int) $request['id'];
        $user_id = get_current_user_id();

        $tantara = $this->tantara_repository->find($tantara_id);

        if (!$tantara) {
            return new WP_Error(
                'tantara_not_found',
                'Tantara introuvable.',
                ['status' => 404]
            );
        }

        // Vérifier l'accès
        $access_check = Malagasy_Auth_Middleware::verify_content_access($user_id, $tantara_id);
        if (is_wp_error($access_check)) {
            return $access_check;
        }

        return rest_ensure_response([
            'id' => $tantara->id,
            'title' => $tantara->title,
            'content' => $tantara->content,
            'conteur' => $tantara->conteur,
            'duree' => $tantara->duree,
            'langue' => $tantara->langue,
            'access_type' => $tantara->access_type
        ]);
    }

    /**
     * Récupère l'URL de streaming d'un film
     */
    public function get_film_stream($request) {
        $film_id = (int) $request['id'];
        $user_id = get_current_user_id();

        // Vérifier l'accès
        $access_check = Malagasy_Auth_Middleware::verify_content_access($user_id, $film_id);
        if (is_wp_error($access_check)) {
            return $access_check;
        }

        $film = $this->film_repository->find($film_id);

        if (!$film || !$film->url_video_hls) {
            return new WP_Error(
                'stream_not_found',
                'URL de streaming introuvable.',
                ['status' => 404]
            );
        }

        return rest_ensure_response([
            'id' => $film_id,
            'stream_url' => $film->url_video_hls
        ]);
    }

    /**
     * Récupère l'URL de streaming d'un tantara
     */
    public function get_tantara_stream($request) {
        $tantara_id = (int) $request['id'];
        $user_id = get_current_user_id();

        // Vérifier l'accès
        $access_check = Malagasy_Auth_Middleware::verify_content_access($user_id, $tantara_id);
        if (is_wp_error($access_check)) {
            return $access_check;
        }

        $tantara = $this->tantara_repository->find($tantara_id);

        if (!$tantara || !$tantara->url_audio) {
            return new WP_Error(
                'stream_not_found',
                'URL de streaming introuvable.',
                ['status' => 404]
            );
        }

        return rest_ensure_response([
            'id' => $tantara_id,
            'stream_url' => $tantara->url_audio
        ]);
    }
}
