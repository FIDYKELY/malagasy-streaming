<?php
/**
 * Films Metabox Manager
 */

if (!defined('ABSPATH')) exit;

class Malagasy_Films_Metabox extends Malagasy_Metabox_Manager {

    public function __construct() {
        $this->post_type = 'film_malagasy';
        $this->metabox_id = 'film_details';
        $this->fields = [
            'realisateur' => [
                'label' => 'Réalisateur',
                'type' => 'text'
            ],
            'annee' => [
                'label' => 'Année',
                'type' => 'number'
            ],
            'url_video_hls' => [
                'label' => 'URL HLS (.m3u8)',
                'type' => 'url'
            ],
            'duree' => [
                'label' => 'Durée (minutes)',
                'type' => 'number'
            ],
            'licence' => [
                'label' => 'Licence',
                'type' => 'text'
            ]
        ];

        parent::__construct();
    }

    protected function get_metabox_title() {
        return 'Détails du film';
    }
}
