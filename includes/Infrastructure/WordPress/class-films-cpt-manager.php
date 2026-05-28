<?php
/**
 * Films CPT Manager
 */

if (!defined('ABSPATH')) exit;

class Malagasy_Films_CPT_Manager extends Malagasy_CPT_Manager {

    public function __construct() {
        $this->post_type = 'film_malagasy';
        $this->taxonomies = [
            'film_genre' => [
                'label' => 'Genres',
                'hierarchical' => true
            ],
            'film_realisateur' => [
                'label' => 'Réalisateurs',
                'hierarchical' => false
            ]
        ];

        parent::__construct();
    }

    protected function get_labels() {
        return [
            'name' => 'Films',
            'singular_name' => 'Film',
            'menu_name' => 'Films',
            'add_new' => 'Ajouter',
            'add_new_item' => 'Ajouter un nouveau film',
            'edit_item' => 'Modifier un film',
            'new_item' => 'Nouveau film',
            'view_item' => 'Voir le film',
            'all_items' => 'Tous les films'
        ];
    }

    protected function get_menu_icon() {
        return 'dashicons-video-alt3';
    }

    protected function get_rewrite_slug() {
        return 'films';
    }

    protected function get_custom_fields() {
        return ['realisateur', 'annee', 'url_video_hls', 'duree', 'licence'];
    }
}
