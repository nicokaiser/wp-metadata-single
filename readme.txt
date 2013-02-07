=== BG Single Metadata API ===
Contributors: Nico Kaiser
Tags: meta, custom, field, custom field, post, comment, user
Requires at least: 3.4
Tested up to: 3.4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Implement a way to retrieve and manipulate metadata of WordPress objects which are unique by key (unlike the default WordPress metadata behavior)


== Description ==

The default WordPress behavior for metadata (custom fields) for posts, comments and users is to allow multiple values per key. This can be changed with the $single parameter, but having a data model with a unique metadata key per object (post, comment or user) can be handy in some situations.


== Functions ==

= bg_add_metadata_single( $meta_type, $object_id, $meta_key, $meta_value ) =

Add metadata for the specified object.

* $meta_type (string) Type of object metadata is for (e.g., comment, post, or user)
* $object_id (int) ID of the object metadata is for
* $meta_key (string) Metadata key
* $meta_value (string) Metadata value

Returns true on successful update, false on failure (key already exists)


= bg_update_metadata_single( $meta_type, $object_id, $meta_key, $meta_value ) =

Update metadata for the specified object. If no value already exists for the specified object ID and metadata key, the metadata will be added.

* $meta_type (string) Type of object metadata is for (e.g., comment, post, or user)
* $object_id (int) ID of the object metadata is for
* $meta_key (string) Metadata key
* $meta_value (string) Metadata value

Returns true on successful update, false on failure.


= bg_delete_metadata_single( $meta_type, $object_id, $meta_key, $delete_all = false ) =

Delete metadata for the specified object. 

* $meta_type (string) Type of object metadata is for (e.g., comment, post, or user)
* $object_id (int) ID of the object metadata is for
* $meta_key (string) Metadata key
* $delete_all (bool) Optional, default is false. If true, delete matching metadata entries for all objects, ignoring the specified object_id. Otherwise, only delete matching metadata entries for the specified object_id.

Returns true on successful delete, false on failure.


= bg_get_metadata_single( $meta_type, $object_id, $meta_key = '' ) =

Retrieve metadata for the specified object.

* $meta_type (string) Type of object metadata is for (e.g., comment, post, or user)
* $object_id (int) ID of the object metadata is for
* $meta_key (string) Optional. Metadata key. If not specified, retrieve all metadata for the specified object.

Returns the metadata value or ''


= bg_metadata_single_exists($meta_type, $object_id, $meta_key) =

Determine if a meta key is set for a given object

* $meta_type (string) Type of object metadata is for (e.g., comment, post, or user)
* $object_id (int) ID of the object metadata is for
* $meta_key (string) Metadata key

Returns true of the key is set, false if not.


== Installation ==

1. Upload 'bg-metadata-single' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The tables `wp_postmeta_single`, `wp_commentmeta_single`, `wp_usermeta_single` will be automatically created


== Changelog ==

= 1.0.0 =
* Initial release
