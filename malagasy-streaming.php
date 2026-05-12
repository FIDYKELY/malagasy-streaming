<?php
/**
 * Plugin Name: Streaming Malagasy
 * Description: Plugin streaming Gasy.
 * Version: 1.0
 * Author: Fidy
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/class-films-cpt.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-tantara-cpt.php';

new Films_CPT();
new Films_Metabox();
new Tantara_CPT();
new Tantara_Metabox();