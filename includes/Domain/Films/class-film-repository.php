<?php
/**
 * Film Repository
 * Accès aux données des films
 */

if (!defined('ABSPATH')) exit;

class Malagasy_Film_Repository {

    /**
     * Trouve un film par ID
     */
    public function find($id) {
        $post = get_post($id);

        if (!$post || $post->post_type !== 'film_malagasy') {
            return null;
        }

        return Malagasy_Film::from_post($post);
    }

    /**
     * Récupère tous les films selon les critères
     */
    public function find_all($criteria = []) {
        $args = [
            'post_type' => 'film_malagasy',
            'post_status' => 'publish',
            'posts_per_page' => $criteria['per_page'] ?? 10,
            'paged' => $criteria['page'] ?? 1,
            'orderby' => $criteria['orderby'] ?? 'date',
            'order' => $criteria['order'] ?? 'DESC'
        ];

        if (!empty($criteria['genre'])) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'film_genre',
                    'field' => 'slug',
                    'terms' => $criteria['genre']
                ]
            ];
        }

        if (!empty($criteria['search'])) {
            $args['s'] = $criteria['search'];
        }

        $query = new WP_Query($args);
        $films = [];

        foreach ($query->posts as $post) {
            $films[] = Malagasy_Film::from_post($post);
        }

        wp_reset_postdata();

        return $films;
    }

    /**
     * Récupère le nombre total de films
     */
    public function count($criteria = []) {
        $args = [
            'post_type' => 'film_malagasy',
            'post_status' => 'publish'
        ];

        if (!empty($criteria['genre'])) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'film_genre',
                    'field' => 'slug',
                    'terms' => $criteria['genre']
                ]
            ];
        }

        $query = new WP_Query($args);
        return $query->found_posts;
    }
}
