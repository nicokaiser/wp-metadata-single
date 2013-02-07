<?php
/*
Plugin Name: BG Single Metadata API
Plugin URI: https://github.com/godmodelabs/wp-metadata-single
Description: Implement a way to retrieve and manipulate metadata of WordPress objects which are unique by key (unlike the default WordPress metadata behavior)
Version: 1.0.0
Author: Nico Kaiser
Author URI: http://www.boerse-go.ag
*/

$plugin_path = plugin_dir_path(__FILE__); 

require_once($plugin_path . 'install.php');
require_once($plugin_path . 'functions.php');

register_activation_hook(__FILE__, '_bg_metadata_single_install');
