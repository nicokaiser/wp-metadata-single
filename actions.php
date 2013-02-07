<?php

add_action('deleted_post', function ($object_id) {
  $meta = bg_get_metadata_single('post', $object_id);
  if (! $meta) return;
  foreach ($meta as $meta_key => $value) {
    bg_delete_metadata_single('post', $object_id, $meta_key);
  }
}, 10, 1);

add_action('deleted_comment', function ($object_id) {
  $meta = bg_get_metadata_single('comment', $object_id);
  if (! $meta) return;
  foreach ($meta as $meta_key => $value) {
    bg_delete_metadata_single('comment', $object_id, $meta_key);
  }
}, 10, 1);

add_action('deleted_user', function ($object_id) {
  $meta = bg_get_metadata_single('user', $object_id);
  if (! $meta) return;
  foreach ($meta as $meta_key => $value) {
    bg_delete_metadata_single('user', $object_id, $meta_key);
  }
}, 10, 1);
