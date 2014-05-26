<?php // -- /archives-for-menus.php

/**
 * Plugin Name:  Archives for Menus
 * Version:      0.1.0
 *
 * Description:  Add links to post archives (custom post types included) to your nav menus.
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
    echo '<p>Hello</p>';
  }

  // -------------------------------------------------------------------------------------------
}

// ---------------------------------------------------------------------------------------------


// ---------------------------------------------------------------------------------------------
// Initialize
// ---------------------------------------------------------------------------------------------

Archives_For_Menus::instance();

// ---------------------------------------------------------------------------------------------
