<?php

if (!defined('ABSPATH')) exit;

class Films_CPT {

    public function __construct() {

        add_action('init', [$this, 'register_film_cpt']);
    }

    public function register_film_cpt() {

        $labels = [
            'name' => 'Films',
            'singular_name' => 'Film',
            'menu_name' => 'Films',
            'add_new' => 'Ajouter',
            'add_new_item' => 'Ajouter un nouveau film',
            'edit_item' => 'Modifier un film',
            'new_item' => 'Nouveau film',
            'view_item' => 'Voir le film',
            'all_items' => 'Tous les films'
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-video-alt3',
            'has_archive' => true,
            'rewrite' => ['slug' => 'films'],
            'supports' => ['title', 'editor', 'thumbnail'],
            'show_in_rest' => true
        ];

        register_post_type('film_malagasy', $args);
        add_filter('rest_prepare_film_malagasy', [$this, 'add_custom_fields_to_rest'], 10,3);

        }
        public function add_custom_fields_to_rest($response, $post, $request){
            $fields = [
                'realisateur',
                'annee',
                'url_video_hls',
                'duree',
                'licence'
            ];
        foreach($fields as $field){
            $meta_key = '_film_' . $field;
            $value = get_post_meta($post->ID, $meta_key, true);
            if ($field === 'url_video_hls') {
                $value = $value ? esc_url_raw($value) : '';
            }
            $response->data['meta_' . $field] = $value;
            }
            return $response;
        }
}

class Films_Metabox{
    public function __construct(){
        add_action('add_meta_boxes', [$this, 'register_metabox']);
        add_action('save_post', [$this, 'save_metabox']);
    }

    public function register_metabox(){
        add_meta_box(
            'film_details',
            'Détails du film',
            [$this, 'render_metabox'],
            'film_malagasy',
            'normal',
            'default'
        );
    }

    public function render_metabox($post){
        wp_nonce_field('save_film_meta', 'film_meta_nonce');

        $realisateur = get_post_meta($post->ID, '_film_realisateur', true);
        $annee = get_post_meta($post->ID, '_film_annee', true);
        $url_video  = get_post_meta($post->ID, '_film_url_video_hls', true);
        $duree = get_post_meta($post->ID, '_film_duree', true);
        $licence  = get_post_meta($post->ID, '_film_licence', true);
        ?>

         <p>
            <label>Réalisateur</label><br>
            <input type="text" name="film_realisateur"
                   value="<?php echo esc_attr($realisateur); ?>"
                   style="width:100%;">
        </p>

        <p>
            <label>Année</label><br>
            <input type="number" name="film_annee"
                   value="<?php echo esc_attr($annee); ?>">
        </p>

        <p>
            <label>URL HLS (.m3u8)</label><br>
            <input type="url" name="film_url_video_hls"
                   value="<?php echo esc_url($url_video); ?>"
                   style="width:100%;">
        </p>

        <p>
            <label>Durée (minutes)</label><br>
            <input type="number" name="film_duree"
                   value="<?php echo esc_attr($duree); ?>">
        </p>

        <p>
            <label>Licence</label><br>
            <input type="text" name="film_licence"
                   value="<?php echo esc_attr($licence); ?>"
                   style="width:100%;">
        </p>

        <?php
    }

    public function save_metabox($post_id) {

        if (
            !isset($_POST['film_meta_nonce']) ||
            !wp_verify_nonce($_POST['film_meta_nonce'], 'save_film_meta')
        ) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $fields = [
            'film_realisateur',
            'film_annee',
            'film_url_video_hls',
            'film_duree',
            'film_licence'
        ];

        foreach ($fields as $field) {

            if (isset($_POST[$field])) {

                $value = $_POST[$field];
                if ($field === 'film_url_video_hls') {
                    $value = sanitize_url($value);
                } elseif (in_array($field, ['film_duree'])) {
                    $value = absint($value);
                } else {
                    $value = sanitize_text_field($value);
                }
                update_post_meta(
                    $post_id,
                    '_' . $field,
                    $value
                );
            }
        }
    }
}
