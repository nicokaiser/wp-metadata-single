<?php

/**
 * Create database tables
 *
 * @uses $wpdb WordPress database object for queries.
 */
function _bg_metadata_single_install()
{
  global $wpdb;

  $types = array('post', 'comment', 'user');
  foreach ($types as $type) {
    $table_name = $wpdb->prefix . $type . 'meta_single';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      require_once(ABSPATH . 'wp-admin/upgrade-functions.php');

      $sql = "CREATE TABLE $table_name (
        {$type}_id bigint(20) unsigned NOT NULL default '0',
        meta_key varchar(255) default NULL,
        meta_value longtext,
        PRIMARY KEY ({$type}_id, meta_key),
        KEY ({$type}_id),
        KEY (meta_key)
      );";

      dbDelta($sql);
    }    
  }
}
