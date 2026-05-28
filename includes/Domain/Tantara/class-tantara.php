<?php
/**
 * Tantara Entity
 */

if (!defined('ABSPATH')) exit;

class Malagasy_Tantara {

    public $id;
    public $title;
    public $content;
    public $thumbnail;
    public $conteur;
    public $url_audio;
    public $duree;
    public $langue;
    public $access_type;
    public $prix;

    /**
     * Crée une instance depuis un post WordPress
     */
    public static function from_post($post) {
        $tantara = new self();
        $tantara->id = $post->ID;
        $tantara->title = $post->post_title;
        $tantara->content = $post->post_content;
        $tantara->thumbnail = get_the_post_thumbnail_url($post->ID, 'medium');
        $tantara->conteur = get_post_meta($post->ID, '_tantara_malagasy_conteur', true);
        $tantara->url_audio = get_post_meta($post->ID, '_tantara_malagasy_url_audio', true);
        $tantara->duree = (int) get_post_meta($post->ID, '_tantara_malagasy_duree', true);
        $tantara->langue = get_post_meta($post->ID, '_tantara_malagasy_langue', true);
        $tantara->access_type = get_post_meta($post->ID, '_content_access_type', true) ?: 'freemium';
        $tantara->prix = (int) get_post_meta($post->ID, '_tantara_malagasy_prix', true);

        return $tantara;
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
            'conteur' => $this->conteur,
            'url_audio' => $this->url_audio,
            'duree' => $this->duree,
            'langue' => $this->langue,
            'access_type' => $this->access_type,
            'prix' => $this->prix
        ];
    }

    /**
     * Vérifie si accessible gratuitement
     */
    public function is_free() {
        return $this->access_type === 'freemium';
    }
}
