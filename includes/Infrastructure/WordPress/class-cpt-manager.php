<?php
/**
 * Base CPT Manager
 * Classe abstraite pour la gestion centralisée des CPT
 */

if (!defined('ABSPATH')) exit;

abstract class Malagasy_CPT_Manager {

    protected $post_type;
    protected $labels;
    protected $taxonomies = [];

    public function __construct() {
        add_action('init', [$this, 'register_cpt']);
        add_action('init', [$this, 'register_taxonomies']);
    }

    /**
     * Enregistre le CPT
     */
    public function register_cpt() {
        $args = [
            'labels' => $this->get_labels(),
            'public' => true,
            'show_in_menu' => true,
            'menu_icon' => $this->get_menu_icon(),
            'has_archive' => true,
            'rewrite' => ['slug' => $this->get_rewrite_slug()],
            'supports' => ['title', 'editor', 'thumbnail'],
            'show_in_rest' => true
        ];

        register_post_type($this->post_type, $args);
        add_filter("rest_prepare_{$this->post_type}", [$this, 'add_custom_fields_to_rest'], 10, 3);
    }

    /**
     * Enregistre les taxonomies associées
     */
    public function register_taxonomies() {
        foreach ($this->taxonomies as $taxonomy => $config) {
            register_taxonomy($taxonomy, $this->post_type, [
                'label' => $config['label'],
                'hierarchical' => $config['hierarchical'] ?? false,
                'show_in_rest' => true,
                'show_admin_column' => true
            ]);
        }
    }

    /**
     * Ajoute les champs personnalisés à la réponse REST
     */
    public function add_custom_fields_to_rest($response, $post, $request) {
        $custom_fields = $this->get_custom_fields();

        foreach ($custom_fields as $field) {
            $meta_key = "_" . $this->post_type . "_" . $field;
            $value = get_post_meta($post->ID, $meta_key, true);

            if ($field === 'url_video_hls' || $field === 'url_audio') {
                $value = $value ? esc_url_raw($value) : '';
            }

            $response->data['meta_' . $field] = $value;
        }

        return $response;
    }

    /**
     * Méthodes à implémenter par les sous-classes
     */
    abstract protected function get_labels();
    abstract protected function get_menu_icon();
    abstract protected function get_rewrite_slug();
    abstract protected function get_custom_fields();
}
