<?php
/**
 * Tantara Metabox Manager
 */

if (!defined('ABSPATH')) exit;

class Malagasy_Tantara_Metabox extends Malagasy_Metabox_Manager {

    public function __construct() {
        $this->post_type = 'tantara_malagasy';
        $this->metabox_id = 'tantara_details';
        $this->fields = [
            'conteur' => [
                'label' => 'Conteur',
                'type' => 'text'
            ],
            'url_audio' => [
                'label' => 'URL HLS (.m3u8)',
                'type' => 'url'
            ],
            'duree' => [
                'label' => 'Durée (minutes)',
                'type' => 'number'
            ],
            'langue' => [
                'label' => 'Langue',
                'type' => 'text'
            ]
        ];

        parent::__construct();
    }

    protected function get_metabox_title() {
        return 'Détails du tantara';
    }
}
