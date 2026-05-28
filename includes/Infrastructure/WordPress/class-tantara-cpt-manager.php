<?php
/**
 * Tantara CPT Manager
 */

if (!defined('ABSPATH')) exit;

class Malagasy_Tantara_CPT_Manager extends Malagasy_CPT_Manager {

    public function __construct() {
        $this->post_type = 'tantara_malagasy';
        $this->taxonomies = [
            'tantara_conteur' => [
                'label' => 'Conteurs',
                'hierarchical' => false
            ],
            'tantara_theme' => [
                'label' => 'Thèmes',
                'hierarchical' => true
            ]
        ];

        parent::__construct();
    }

    protected function get_labels() {
        return [
            'name' => 'Tantara',
            'singular_name' => 'Tantara',
            'menu_name' => 'Tantara',
            'add_new' => 'Ajouter',
            'add_new_item' => 'Ajouter un nouveau tantara',
            'edit_item' => 'Modifier un tantara',
            'new_item' => 'Nouveau tantara',
            'view_item' => 'Voir le tantara',
            'all_items' => 'Tous les tantara'
        ];
    }

    protected function get_menu_icon() {
        return 'dashicons-format-audio';
    }

    protected function get_rewrite_slug() {
        return 'tantara';
    }

    protected function get_custom_fields() {
        return ['conteur', 'url_audio', 'duree', 'langue'];
    }
}
