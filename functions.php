<?php

/**
 * Single Metadata API
 *
 * Functions for retrieving and manipulating metadata of various WordPress object types.
 * Metadata or an object is a represented by a simple key-value pair. 
 * Other than default WordPress metadata entries, objects may NOT contain multiple
 * metadata entries that share the same key.
 *
 * @author Nico Kaiser <nico.kaiser@boerse-go.de>
 */

/**
 * Add metadata for the specified object.
 *
 * @uses $wpdb WordPress database object for queries.
 *
 * @param string $meta_type Type of object metadata is for (e.g., comment, post, or user)
 * @param int $object_id ID of the object metadata is for
 * @param string $meta_key Metadata key
 * @param string $meta_value Metadata value
 *
 * @return bool true on successful update, false on failure (key already exists)
 */
function bg_add_metadata_single($meta_type, $object_id, $meta_key, $meta_value)
{
  if (!$meta_type || !$meta_key)
    return false;

  if (!$object_id = absint($object_id))
    return false;

  if (! $table = _bg_get_meta_single_table($meta_type))
    return false;

  global $wpdb;

  $column = esc_sql($meta_type . '_id');

  // expected_slashed ($meta_key)
  $meta_key = stripslashes($meta_key);
  $meta_value = stripslashes_deep($meta_value);
  //$meta_value = sanitize_meta($meta_key, $meta_value, $meta_type);

  if ($wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM $table WHERE meta_key = %s AND $column = %d",
    $meta_key, $object_id)))
    return false;

  $_meta_value = $meta_value;
  $meta_value = maybe_serialize($meta_value);

  $result = $wpdb->insert($table, array(
    $column => $object_id,
    'meta_key' => $meta_key,
    'meta_value' => $meta_value
  ));

  if (! $result)
    return false;

  wp_cache_delete($object_id, 'bg_' . $meta_type . '_meta_single');

  return true;
}

/**
 * Update metadata for the specified object. If no value already exists for the specified object
 * ID and metadata key, the metadata will be added.
 *
 * @uses $wpdb WordPress database object for queries.
 *
 * @param string $meta_type Type of object metadata is for (e.g., comment, post, or user)
 * @param int $object_id ID of the object metadata is for
 * @param string $meta_key Metadata key
 * @param string $meta_value Metadata value
 *
 * @return bool True on successful update, false on failure.
 */
function bg_update_metadata_single($meta_type, $object_id, $meta_key, $meta_value)
{
  if (!$meta_type || !$meta_key)
    return false;

  if (!$object_id = absint($object_id))
    return false;

  if (! $table = _bg_get_meta_single_table($meta_type))
    return false;

  global $wpdb;

  $column = esc_sql($meta_type . '_id');

  // expected_slashed ($meta_key)
  $meta_key = stripslashes($meta_key);
  $passed_value = $meta_value;
  $meta_value = stripslashes_deep($meta_value);
  //$meta_value = sanitize_meta($meta_key, $meta_value, $meta_type);

  if (! $meta_id = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $table WHERE meta_key = %s AND $column = %d", $meta_key, $object_id)))
    return bg_add_metadata_single($meta_type, $object_id, $meta_key, $passed_value);

  $_meta_value = $meta_value;
  $meta_value = maybe_serialize($meta_value);

  $data  = compact('meta_value');
  $where = array($column => $object_id, 'meta_key' => $meta_key);

  $wpdb->update($table, $data, $where);

  wp_cache_delete($object_id, 'bg_' . $meta_type . '_meta_single');

  return true;
}

/**
 * Delete metadata for the specified object.
 *
 * @uses $wpdb WordPress database object for queries.
 *
 * @param string $meta_type Type of object metadata is for (e.g., comment, post, or user)
 * @param int $object_id ID of the object metadata is for
 * @param string $meta_key Metadata key
 * @param string $meta_value Optional. Metadata value. If specified, only delete metadata entries
 *    with this value. Otherwise, delete all entries with the specified meta_key.
 * @param bool $delete_all Optional, default is false. If true, delete matching metadata entries
 *    for all objects, ignoring the specified object_id. Otherwise, only delete matching
 *    metadata entries for the specified object_id.
 * @return bool True on successful delete, false on failure.
 */
function bg_delete_metadata_single($meta_type, $object_id, $meta_key, $delete_all = false)
{
  if (!$meta_type || !$meta_key)
    return false;

  if ((!$object_id = absint($object_id)) && !$delete_all)
    return false;

  if (! $table = _bg_get_meta_single_table($meta_type))
    return false;

  global $wpdb;

  $type_column = esc_sql($meta_type . '_id');
  #$id_column = 'user' == $meta_type ? 'umeta_id' : 'meta_id';
  // expected_slashed ($meta_key)
  $meta_key = stripslashes($meta_key);

  $query = $wpdb->prepare("DELETE FROM $table WHERE meta_key = %s", $meta_key);

  if (!$delete_all)
    $query .= $wpdb->prepare(" AND $type_column = %d", $object_id);

  $count = $wpdb->query($query);

  if ( !$count )
    return false;

  if ($delete_all) {
    foreach ((array) $object_ids as $o_id) {
      wp_cache_delete($o_id, 'bg_' .  $meta_type . '_meta_single');
    }
  } else {
    wp_cache_delete($object_id, 'bg_' . $meta_type . '_meta_single');
  }

  return true;
}

/**
 * Retrieve metadata for the specified object.
 *
 * @param string $meta_type Type of object metadata is for (e.g., comment, post, or user)
 * @param int $object_id ID of the object metadata is for
 * @param string $meta_key Optional. Metadata key. If not specified, retrieve all metadata for
 *    the specified object.
 *
 * @return string|array metadata value
 */
function bg_get_metadata_single($meta_type, $object_id, $meta_key = '')
{
  if (!$meta_type)
    return false;

  if (!$object_id = absint($object_id))
    return false;

  $meta_cache = wp_cache_get($object_id, 'bg_' . $meta_type . '_meta_single');

  if (!$meta_cache) {
    $meta_cache = _bg_update_meta_single_cache($meta_type, array($object_id));
    $meta_cache = $meta_cache[$object_id];
  }

  if (!$meta_key)
    return $meta_cache;

  if (isset($meta_cache[$meta_key])) {
    return maybe_unserialize($meta_cache[$meta_key]);
  }

  return '';
}

/**
 * Determine if a meta key is set for a given object
 *
 * @param string $meta_type Type of object metadata is for (e.g., comment, post, or user)
 * @param int $object_id ID of the object metadata is for
 * @param string $meta_key Metadata key.
 * @return boolean true of the key is set, false if not.
 */
function bg_metadata_single_exists($meta_type, $object_id, $meta_key)
{
  if (! $meta_type)
    return false;

  if (! $object_id = absint($object_id))
    return false;

  $meta_cache = wp_cache_get($object_id, 'bg_' . $meta_type . '_meta_single');

  if (!$meta_cache) {
    $meta_cache = _bg_update_meta_single_cache($meta_type, array($object_id));
    $meta_cache = $meta_cache[$object_id];
  }

  if (isset($meta_cache[$meta_key]))
    return true;

  return false;
}

/**
 * Update the metadata cache for the specified objects.
 *
 * @uses $wpdb WordPress database object for queries.
 *
 * @param string $meta_type Type of object metadata is for (e.g., comment, post, or user)
 * @param int|array $object_ids array or comma delimited list of object IDs to update cache for
 * @return mixed Metadata cache for the specified objects, or false on failure.
 */
function _bg_update_meta_single_cache($meta_type, $object_ids)
{
  if (empty($meta_type) || empty($object_ids))
    return false;

  if (! $table = _bg_get_meta_single_table($meta_type))
    return false;

  $column = esc_sql($meta_type . '_id');

  global $wpdb;

  if (!is_array($object_ids)) {
    $object_ids = preg_replace('|[^0-9,]|', '', $object_ids);
    $object_ids = explode(',', $object_ids);
  }

  $object_ids = array_map('intval', $object_ids);

  $cache_key = 'bg_' . $meta_type . '_meta_single';
  $ids = array();
  $cache = array();
  foreach ($object_ids as $id) {
    $cached_object = wp_cache_get($id, $cache_key);
    if (false === $cached_object)
      $ids[] = $id;
    else
      $cache[$id] = $cached_object;
  }

  if (empty($ids))
    return $cache;

  // Get meta info
  $id_list = join(',', $ids);
  $meta_list = $wpdb->get_results($wpdb->prepare("SELECT $column, meta_key, meta_value FROM $table WHERE $column IN ($id_list)",
    $meta_type), ARRAY_A);

  if (!empty($meta_list)) {
    foreach ($meta_list as $metarow) {
      $mpid = intval($metarow[$column]);
      $mkey = $metarow['meta_key'];
      $mval = $metarow['meta_value'];

      // Force subkeys to be array type:
      if (!isset($cache[$mpid]) || !is_array($cache[$mpid]))
        $cache[$mpid] = array();

      // Add a value to the current pid/key:
      $cache[$mpid][$mkey] = $mval;
    }
  }

  foreach ($ids as $id) {
    if (! isset($cache[$id]))
      $cache[$id] = array();
    wp_cache_add($id, $cache[$id], $cache_key);
  }

  return $cache;
}

/**
 * Retrieve the name of the single metadata table for the specified object type.
 *
 * @uses $wpdb WordPress database object for queries.
 *
 * @param string $type Type of object to get metadata table for (e.g., comment, post, or user)
 * @return mixed Metadata table name, or false if no metadata table exists
 */
function _bg_get_meta_single_table($type)
{
  global $wpdb;

  if (! in_array($type, array('post', 'comment', 'user')))
    return false;

  $table_name = $wpdb->prefix . $type . 'meta_single';

  return $table_name;
}
