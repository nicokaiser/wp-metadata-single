<?php

// we need admin functions wp_delete_user)
require_once(ABSPATH . '/wp-admin/includes/user.php');

class MetadataSingleTest extends WordPressTestCase
{
  public function setUp()
  {
    global $wpdb;

    parent::setUp();

    // Truncate tables
    foreach (array('post', 'comment', 'user') as $type) {
        $table_name = $wpdb->prefix . $type . 'meta_single';
        $wpdb->query("TRUNCATE TABLE $table_name");
    }
  }

  public function testMetaTypes()
  {
    // valid types
    $this->assertTrue(bg_add_metadata_single('post', 1, 'foo', 'bar'));
    $this->assertTrue(bg_add_metadata_single('comment', 1, 'foo', 'bar'));
    $this->assertTrue(bg_add_metadata_single('user', 1, 'foo', 'bar'));

    // invalid type
    $this->assertFalse(bg_add_metadata_single('invalid_type', 1, 'foo', 'bar'));
  }

  public function testAddInvalidParameters()
  {
    // invalid parameters
    $this->assertFalse(bg_add_metadata_single(null, 1, 'foo', 'bar'));
    $this->assertFalse(bg_add_metadata_single(null, 'NaN', 'foo', 'bar'));
    $this->assertFalse(bg_add_metadata_single('post', null, 'foo', 'bar'));
    $this->assertFalse(bg_add_metadata_single('invalid_type', 1, 'foo', 'bar'));
  }

  public function testAdd()
  {
    // metadata does not exist initially
    $this->assertFalse(bg_metadata_single_exists('post', 1, 'foo'));

    // adds metadata
    $this->assertTrue(bg_add_metadata_single('post', 1, 'foo', 'bar'));
    $this->assertTrue(bg_metadata_single_exists('post', 1, 'foo'));
    $this->assertEquals('bar', bg_get_metadata_single('post', 1, 'foo'));

    // false if already exists
    $this->assertFalse(bg_add_metadata_single('post', 1, 'foo', 'bar'));
  }

  public function testGetInvalid()
  {
    // invalid parameters
    $this->assertFalse(bg_get_metadata_single(null, 1, 'foo'));
    $this->assertFalse(bg_get_metadata_single(null, 'NaN', 'foo'));
  }

  public function testGetOne()
  {
    $this->assertTrue(bg_add_metadata_single('post', 1, 'foo', 'bar'));
    $this->assertTrue(bg_add_metadata_single('post', 1, 'foo1', 'baz'));

    // get a specific value
    $this->assertEquals('bar', bg_get_metadata_single('post', 1, 'foo'));
    $this->assertEquals('baz', bg_get_metadata_single('post', 1, 'foo1'));
  }

  public function testGetAll()
  {
    $this->assertTrue(bg_add_metadata_single('post', 1, 'foo', 'bar'));
    $this->assertTrue(bg_add_metadata_single('post', 1, 'foo1', 'baz'));

    // get all metadata values for a post
    $this->assertEmpty(array_diff(
      array('foo' => 'bar', 'foo1' => 'baz'), 
      bg_get_metadata_single('post', 1)
    ));
  }

  public function testUpdateInvalidParameters()
  {
    // invalid parameters
    $this->assertFalse(bg_update_metadata_single(null, 1, 'foo', 'bar'));
    $this->assertFalse(bg_update_metadata_single(null, 'NaN', 'foo', 'bar'));
    $this->assertFalse(bg_update_metadata_single('post', null, 'foo', 'bar'));
    $this->assertFalse(bg_update_metadata_single('invalid_type', 1, 'foo', 'bar'));
  }

  public function testUpdateAdd()
  {
    // metadata does not exist initially
    $this->assertFalse(bg_metadata_single_exists('post', 1, 'foo'));

    // update non-existent metadata => add
    $this->assertTrue(bg_update_metadata_single('post', 1, 'foo', 'bar'));
    $this->assertTrue(bg_metadata_single_exists('post', 1, 'foo'));
    $this->assertEquals('bar', bg_get_metadata_single('post', 1, 'foo'));
  }

  public function testUpdateExisting()
  {
    // metadata does not exist initially
    $this->assertFalse(bg_metadata_single_exists('post', 1, 'foo'));

    // add metadata
    $this->assertTrue(bg_add_metadata_single('post', 1, 'foo', 'bar'));

    // update existing metadata
    $this->assertTrue(bg_update_metadata_single('post', 1, 'foo', 'baz'));
    $this->assertEquals('baz', bg_get_metadata_single('post', 1, 'foo'));
  }

  public function testDeleteInvalidParameters()
  {
    // invalid parameters
    $this->assertFalse(bg_delete_metadata_single(null, 1, 'foo'));
    $this->assertFalse(bg_delete_metadata_single(null, 'NaN', 'foo'));
    $this->assertFalse(bg_delete_metadata_single('post', null, 'foo'));
    $this->assertFalse(bg_delete_metadata_single('invalid_type', 1, 'foo'));
  }

  public function testDelete()
  {
    // metadata does not exist initially
    $this->assertFalse(bg_metadata_single_exists('post', 1, 'foo'));

    // add metadata
    $this->assertTrue(bg_add_metadata_single('post', 1, 'foo', 'bar'));
    $this->assertTrue(bg_add_metadata_single('post', 1, 'foo1', 'baz'));

    // delete metadata
    $this->assertTrue(bg_delete_metadata_single('post', 1, 'foo'));
    $this->assertFalse(bg_metadata_single_exists('post', 1, 'foo'));
    $this->assertTrue(bg_metadata_single_exists('post', 1, 'foo1'));
  }

  public function testDeleteUnknown()
  {
    $this->assertFalse(bg_metadata_single_exists('post', 1, 'non-existent'));

    // deleting a non-existent metadata returns false
    $this->assertFalse(bg_delete_metadata_single('post', 1, 'non-existent'));
  }

  public function testDeleteAll()
  {
    // add metadata
    $this->assertTrue(bg_add_metadata_single('post', 1, 'foo', 'bar'));
    $this->assertTrue(bg_add_metadata_single('post', 1, 'foo1', 'baz'));
    $this->assertTrue(bg_add_metadata_single('post', 2, 'foo', 'baz'));

    // delete all 'foo' metadata
    $this->assertTrue(bg_delete_metadata_single('post', 1, 'foo', true));

    $this->assertFalse(bg_metadata_single_exists('post', 1, 'foo'));
    $this->assertFalse(bg_metadata_single_exists('post', 2, 'foo'));
    $this->assertTrue(bg_metadata_single_exists('post', 1, 'foo1'));
  }

  public function testDeletedPostAction()
  {
    // create post
    $ids = self::insertQuickPosts(1);
    $post_id = $ids[0];

    $this->assertTrue(bg_add_metadata_single('post', $post_id, 'foo', 'bar'));
    $this->assertTrue(bg_metadata_single_exists('post', $post_id, 'foo'));

    // delete post
    wp_delete_post($post_id, true); 
    $this->assertFalse(bg_metadata_single_exists('post', $post_id, 'foo'));
  }

  public function testDeletedCommentAction()
  {
    // create post
    $ids = self::insertQuickPosts(1);
    $post_id = $ids[0];

    // create comment
    $ids = self::insertQuickComments($post_id, 1);
    $comment_id = $ids[0];

    $this->assertTrue(bg_add_metadata_single('comment', $comment_id, 'foo', 'bar'));
    $this->assertTrue(bg_metadata_single_exists('comment', $comment_id, 'foo'));

    // delete comment
    wp_delete_comment($comment_id, true);
    $this->assertFalse(bg_metadata_single_exists('comment', $comment_id, 'foo'));
  }

  public function testDeletedUserAction()
  {
    // create user
    $user_id = self::createUser();

    $this->assertTrue(bg_add_metadata_single('user', $user_id, 'foo', 'bar'));
    $this->assertTrue(bg_metadata_single_exists('user', $user_id, 'foo'));

    // delete user
    wp_delete_user($user_id);
    $this->assertFalse(bg_metadata_single_exists('user', $user_id, 'foo'));
  }
}
