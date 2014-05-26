<?php // -- /archives-for-menus.php

/**
 * Plugin Name:  Archives for Menus
 * Version:      0.1.0
 *
 * Description:  Add links to post archives (custom post types included) to your nav menus. Based on https://gist.github.com/davidmh/8050982.
 *
 * Author:       The A-TEAM
 * Author URI:   https://github.com/asmbs
 *
 * License:      MIT License
 * License URI:  http://opensource.org/licenses/MIT
 *
 */

// No direct access ----------------------------------------------------------------------------

if ( !defined( 'ABSPATH' ) )
  exit;



// ---------------------------------------------------------------------------------------------
// Define class
// ---------------------------------------------------------------------------------------------

class Archives_For_Menus
{
  /**
   * @var  string  A slug that will be used throughout the rendering process to build
   *               class/ID attributes and reference menu objects.
   *
   */
  private $slug = 'archive';


  // -------------------------------------------------------------------------------------------
  // Singleton class
  // -------------------------------------------------------------------------------------------
  
  private static $instance = NULL;
  
  /**
   * instance()
   *
   * Retrieve the class instance.
   *
   * @return  Archives_For_Menus  The class instance.
   *
   */
  public static function instance()
  {
    if ( self::$instance === NULL )
      self::$instance = new self();
    return self::$instance;
  }

  // -------------------------------------------------------------------------------------------


  // -------------------------------------------------------------------------------------------
  // Setup
  // -------------------------------------------------------------------------------------------

  /**
   * __construct()
   * 
   * Build the instance and register setup hooks.
   *
   */
  private function __construct()
  {
    // Register hook to add meta box
    add_action( 'admin_head-nav-menus.php', [ $this, 'add_archives_meta_box' ] );

    // Register item setup filters
    add_filter( 'wp_get_nav_menu_items', [ $this, 'archive_menu_filter' ], 10, 3 );
    add_filter( 'wp_setup_nav_menu_item', [ $this, 'setup_archive_item' ] );

    // Register enqueue hook for our admin JS
    add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
  }


  /**
   * add_archives_meta_box()
   *
   * Register the menu editor meta box.
   *
   */
  public function add_archives_meta_box()
  {
    add_meta_box( 'add-archive', __( 'Archives' ), [ $this, 'render_archives_meta_box' ], 'nav-menus', 'side', 'default' );
  }


  /**
   * enqueue_admin_scripts()
   *
   * Loads admin scripts for the nav menu editor.
   *
   * @param  string  $hook  The page hook (for conditionally loading scripts).
   *
   */
  public function enqueue_admin_scripts( $hook )
  {
    if ( $hook == 'nav-menus.php' )
    {
      wp_enqueue_script( 'edit-menu-archive', plugins_url( 'assets/js/archive.js', __FILE__ ), [ 'jquery' ], false, false );
    }
  }

  // -------------------------------------------------------------------------------------------


  // -------------------------------------------------------------------------------------------
  // Rendering
  // -------------------------------------------------------------------------------------------

  /**
   * render_archives_meta_box()
   *
   * Renders the contents of the archive link meta box.
   *
   */
  public function render_archives_meta_box()
  {
    // Get nav menu ID
    global $nav_menu_selected_id;

    // Get all post types that support archives
    $post_types = get_post_types( [
      'show_in_nav_menus' => true,
      'has_archive'       => true
    ], 'object' );

    // Initialize the walker
    $walker = new Walker_Nav_Menu_Checklist( [] );

    // Set item properties for the walker
    foreach ( $post_types as &$item )
    {
      $item->classes          = [
        'type-archive',
        'post-type-'. $item->name
      ];
      $item->type             = 'custom';
      $item->object_id        = $this->slug;
      $item->title            = $item->labels->name;
      $item->object           = $item->name;
      $item->menu_item_parent = 0;
      $item->url              = get_post_type_archive_link( $item->name );
      $item->target           = 0;
      $item->attr_title       = 0;
      $item->xfn              = 0;
      $item->db_id            = 0;

      wp_setup_nav_menu_item( $item );
    }

    printf(
      '<div id="%1$s" class="posttypediv">'
        .'<div id="tabs-panel-%1$s" class="tabs-panel tabs-panel-active">'
          .'<ul id="%1$s-checklist" class="categorychecklist form-no-clear">'
            .'%2$s'
          .'</ul>'
        .'</div>'
      .'</div>'
      .'<p class="button-controls">'
        .'<span class="add-to-menu">'
          .'<input id="submit-add-%1$s" class="button-secondary right submit-add-to-menu" type="submit" name="add-%1$s-menu-item" value="%3$s"%5$s />'
          .'<span class="spinner"></span>'
        .'</span>'
      .'</p>',
      $this->slug,
      walk_nav_menu_tree( $post_types, 0, (object) [ 'walker' => $walker ] ),
      __( 'Add to Menu' ),
      esc_url( admin_url( 'images/wpspin_light.gif' ) ),
      disabled( $nav_menu_selected_id, 0 )
    );
  }

  // -------------------------------------------------------------------------------------------


  // -------------------------------------------------------------------------------------------
  // Filters
  // -------------------------------------------------------------------------------------------

  /**
   * archive_menu_filter()
   *
   * Properly generate archive menu item fields. Uses wp_setup_nav_menu_item(), which includes
   * the setup_item() filter.
   *
   * @param   array  $items  The current list of items.
   * @param   int    $menu   The current menu ID.
   * @param   array  $args   Additional arguments.
   * @return  array          The processed list of items.
   *
   * @see     setup_item()
   *
   */
  public function archive_menu_filter( $items, $menu, $args )
  {
    foreach ( $items as &$item )
    {
      // Only make the wp_setup_nav_menu_item() call for archive items.
      if ( $item->type == 'custom' && in_array( 'type-archive', $item->classes ) )
        $item = $this->setup_archive_item( $item );
    }
    return $items;
  }


  /**
   * setup_archive_item()
   *
   * Processes item fields for archive menu items. Relies heavily on the array of classes
   * set during meta box generation, so make sure those are correct!
   *
   * @param   WP_Post  $item  A modified WP_Post object representing the menu item.
   * @return  WP_Post         The more modified WP_Post object, with proper fields set.
   *
   * @see     render_archives_meta_box()
   *
   */
  public function setup_archive_item( $item )
  {
    if ( $item->type == 'custom' && in_array( 'type-archive', $item->classes ) )
    {
      // Override the item type label (will default to 'Custom')
      $item->type_label = __( 'Archive' );

      // Yank the post type from the `post-type-*` class
      foreach ( $item->classes as $class )
      {
        if ( preg_match( '/post-type/i', $class ) !== FALSE )
          $post_type = substr( $class, 10 );
      }

      // Just to be safe, only correct these fields if the post type could be determined
      if ( isset( $post_type ) )
      {
        $item->url = get_post_type_archive_link( $post_type );
        $item->object = $post_type;
      }
    }

    // All done
    //predump( $item );
    return $item;
  }

  // -------------------------------------------------------------------------------------------
}

// ---------------------------------------------------------------------------------------------


// ---------------------------------------------------------------------------------------------
// Initialize
// ---------------------------------------------------------------------------------------------

Archives_For_Menus::instance();

// ---------------------------------------------------------------------------------------------
