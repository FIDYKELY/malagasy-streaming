<?php
/**
 * Tantara Repository
 */

if (!defined('ABSPATH')) exit;

class Malagasy_Tantara_Repository {

    /**
     * Trouve un tantara par ID
     */
    public function find($id) {
        $post = get_post($id);

        if (!$post || $post->post_type !== 'tantara_malagasy') {
            return null;
        }

        return Malagasy_Tantara::from_post($post);
    }

    /**
     * Récupère tous les tantara selon les critères
     */
    public function find_all($criteria = []) {
        $args = [
            'post_type' => 'tantara_malagasy',
            'post_status' => 'publish',
            'posts_per_page' => $criteria['per_page'] ?? 10,
            'paged' => $criteria['page'] ?? 1,
            'orderby' => $criteria['orderby'] ?? 'date',
            'order' => $criteria['order'] ?? 'DESC'
        ];

        if (!empty($criteria['theme'])) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'tantara_theme',
                    'field' => 'slug',
                    'terms' => $criteria['theme']
                ]
            ];
        }

        $query = new WP_Query($args);
        $tantara_list = [];

        foreach ($query->posts as $post) {
            $tantara_list[] = Malagasy_Tantara::from_post($post);
        }

        wp_reset_postdata();

        return $tantara_list;
    }
}
