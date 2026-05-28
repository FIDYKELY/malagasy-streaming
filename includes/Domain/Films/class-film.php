<?php
/**
 * Film Entity
 * Représentation d'un film
 */

if (!defined('ABSPATH')) exit;

class Malagasy_Film {

    public $id;
    public $title;
    public $content;
    public $thumbnail;
    public $realisateur;
    public $annee;
    public $url_video_hls;
    public $duree;
    public $licence;
    public $access_type;
    public $prix;

    /**
     * Crée une instance Film depuis un post WordPress
     */
    public static function from_post($post) {
        $film = new self();
        $film->id = $post->ID;
        $film->title = $post->post_title;
        $film->content = $post->post_content;
        $film->thumbnail = get_the_post_thumbnail_url($post->ID, 'medium');
        $film->realisateur = get_post_meta($post->ID, '_film_malagasy_realisateur', true);
        $film->annee = (int) get_post_meta($post->ID, '_film_malagasy_annee', true);
        $film->url_video_hls = get_post_meta($post->ID, '_film_malagasy_url_video_hls', true);
        $film->duree = (int) get_post_meta($post->ID, '_film_malagasy_duree', true);
        $film->licence = get_post_meta($post->ID, '_film_malagasy_licence', true);
        $film->access_type = get_post_meta($post->ID, '_content_access_type', true) ?: 'freemium';
        $film->prix = (int) get_post_meta($post->ID, '_film_malagasy_prix', true);

        return $film;
    }

    /**
     * Convertit en tableau pour l'API
     */
    public function to_array() {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'thumbnail' => $this->thumbnail,
            'realisateur' => $this->realisateur,
            'annee' => $this->annee,
            'url_video_hls' => $this->url_video_hls,
            'duree' => $this->duree,
            'licence' => $this->licence,
            'access_type' => $this->access_type,
            'prix' => $this->prix
        ];
    }

    /**
     * Vérifie si le film est accessible gratuitement
     */
    public function is_free() {
        return $this->access_type === 'freemium';
    }

    /**
     * Vérifie si le film est premium
     */
    public function is_premium() {
        return $this->access_type === 'premium';
    }

    /**
     * Vérifie si le film est payant à l'acte
     */
    public function is_payperview() {
        return $this->access_type === 'payperview';
    }
}
