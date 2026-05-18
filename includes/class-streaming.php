<?php

if(!defined('ABSPATH')) exit;


class Malagasy_Streaming{
    public function __construct(){
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(){
        register_rest_route('malagasy/v1', '/film/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'malagasy_get_film_secure'],
            'permission_callback' => function () {
                return is_user_logged_in();
            }
        ]);

        register_rest_route('malagasy/v1', '/tantara/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'malagasy_get_tantara_secure'],
            'permission_callback' => function () {
                return is_user_logged_in();
            }
        ]);
        register_rest_route('malagasy/v1', '/films/(?P<id>\d+)/stream', [
            'methods' => 'GET',
            'callback' => [$this, 'get_film_stream'],
            'permission_callback' => function(){
                return is_user_logged_in();
            } 
        ]);
    }
    function malagasy_get_film_secure($request){
        $post_id = (int) $request['id'];
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'film_malagasy') {
            return new WP_Error('invalid_film', 'Film introuvable', ['status' => 404]);
        }

        $user_id = get_current_user_id();
        if (!malagasy_user_has_access($user_id, $post_id, $post->post_type)) {
            return new WP_Error('no_access', 'Accès refusé. Abonnez-vous ou achetez ce contenu.', ['status' => 403]);
        }
        $subscription = get_user_meta($user_id, 'subscription_type', true) ?: 'freemium';
        $data = [
            'id' => $post->ID,
            'title' => $post->post_title,
            'realisateur' => get_post_meta($post->ID, '_film_realisateur', true),
            'annee' => get_post_meta($post->ID, '_film_annee', true),
            'duree' => get_post_meta($post->ID, '_film_duree', true),
            'licence' => get_post_meta($post->ID, '_film_licence', true),
            'user_subscription' => $subscription,
        ];

        return rest_ensure_response($data);
    }

    function malagasy_get_tantara_secure($request){
        $post_id = (int) $request['id'];
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'tantara_malagasy') {
            return new WP_Error('invalid_tantara', 'Tantara introuvable', ['status' => 404]);
        }

        $user_id = get_current_user_id();
        if (!$user_id) {
            return new WP_Error('unauthorized', 'Authentification requise', ['status' => 401]);
        }

        $data = [
            'id' => $post->ID,
            'title' => $post->post_title,
            'conteur' => get_post_meta($post->ID, '_tantara_conteur', true),
            'duree' => get_post_meta($post->ID, '_tantara_duree', true),
            'langue' => get_post_meta($post->ID, '_tantara_langue', true)
        ];

        return rest_ensure_response($data);
    }

    public function get_film_stream($request){
        $film_id = (int) $request['id'];

        $user_id = get_current_user_id();
        if(!$user_id){
            return new WP_Error('unauthorized', 'Authentification requise', ['status' => 401]);
        }

        if(!malagasy_user_has_access($user_id, $film_id, 'film_malagasy')){
            return new WP_Error('forbidden', 'Accès refusé à ce film', ['status' => 403]);
        }

        $stream_url = get_post_meta($film_id, '_film_url_video_hls', true);

        if(!$stream_url){
            return new WP_Error('not_found', 'URL de streaming introuvable', ['status' => 404]);
        }

        return rest_ensure_response([
            'id' => $film_id,
            'stream_url' => $stream_url
        ]);
    }

}