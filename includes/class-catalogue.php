<?php

if(!defined('ABSPATH')) exit;

class Malagasy_Catalogue{
    public function __construct(){
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(){
        register_rest_route('/malagasy/v1', '/films', [
            'methods' => 'GET',
            'callback' => [$this, 'handle_films_list'],
            'permission_callback' => '__return_true',
            'args' => [
                'page' => [
                    'default' => 1,
                    'validate_callback' => function($param) { return is_numeric($param);}
                ],
                'per_page' => [
                    'default' => 10,
                    'validate_callback' => function($param) { return is_numeric($param);}
                ],
                'genre' => [
                    'default' => '',
                    'sanitize_callback' => 'sanitize_text_field'          
                ]
            ]
        ]);
    }

    public function handle_films_list($request){
        $page = (int) $request['page'];
        $per_page = (int) $request['per_page'];
        $genre = $request['genre'];

        $args = [
            'post_type' => 'film_malagasy',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'orderby' => 'date',
            'order' => 'DESC',
        ];
        if(!empty($genre)){
                $args['tax_query'] = [
                    [
                        'taxonomy' => 'film_genre',
                        'field' => 'slug',
                        'terms' => $genre
                    ]
                ];
            }
        $query = new WP_Query($args);

        $result = [];
        if($query->have_posts()){
            foreach($query->posts as $post){
                $result[]= [
                    'id' => $post->ID,
                    'title' => $post->post_title,
                    'slug' => $post->post_name,
                    'thumbnail' => get_the_post_thumbnail_url( $post->ID, 'medium' ),
                    'access_type' => get_post_meta( $post->ID, '_content_access_type', true ) ?: 'freemium'
                ];
            }
        }
        wp_reset_postdata(  );
        return rest_ensure_response($result);
    }
}