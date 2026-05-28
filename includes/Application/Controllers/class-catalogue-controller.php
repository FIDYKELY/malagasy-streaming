<?php
/**
 * Catalogue Controller
 * Récupère la liste des films et tantara
 */

if (!defined('ABSPATH')) exit;

class Malagasy_Catalogue_Controller {

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
        register_rest_route('malagasy/v1', '/films', [
            'methods' => 'GET',
            'callback' => [$this, 'get_films'],
            'permission_callback' => '__return_true',
            'args' => [
                'page' => ['default' => 1],
                'per_page' => ['default' => 10],
                'genre' => ['default' => '']
            ]
        ]);

        register_rest_route('malagasy/v1', '/tantara', [
            'methods' => 'GET',
            'callback' => [$this, 'get_tantara'],
            'permission_callback' => '__return_true',
            'args' => [
                'page' => ['default' => 1],
                'per_page' => ['default' => 10],
                'theme' => ['default' => '']
            ]
        ]);
    }

    /**
     * Récupère la liste des films
     */
    public function get_films($request) {
        $criteria = [
            'page' => (int) $request['page'],
            'per_page' => (int) $request['per_page'],
            'genre' => $request['genre']
        ];

        $films = $this->film_repository->find_all($criteria);

        $result = [];
        foreach ($films as $film) {
            $result[] = [
                'id' => $film->id,
                'title' => $film->title,
                'slug' => get_post_field('post_name', $film->id),
                'thumbnail' => $film->thumbnail,
                'access_type' => $film->access_type,
                'duree' => $film->duree
            ];
        }

        return rest_ensure_response($result);
    }

    /**
     * Récupère la liste des tantara
     */
    public function get_tantara($request) {
        $criteria = [
            'page' => (int) $request['page'],
            'per_page' => (int) $request['per_page'],
            'theme' => $request['theme']
        ];

        $tantara_list = $this->tantara_repository->find_all($criteria);

        $result = [];
        foreach ($tantara_list as $tantara) {
            $result[] = [
                'id' => $tantara->id,
                'title' => $tantara->title,
                'slug' => get_post_field('post_name', $tantara->id),
                'thumbnail' => $tantara->thumbnail,
                'access_type' => $tantara->access_type,
                'duree' => $tantara->duree
            ];
        }

        return rest_ensure_response($result);
    }
}
