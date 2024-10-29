<?php
/*
 * Plugin Name: Automatic Post Categories
 * Version: 1.0
 * Plugin URI: http://woorocks.com
 * Description: This plugin will automatically connect your posts to categories found in title and content texts.
 * Author: Andreas Kviby
 * Author URI: http://woorocks.com
 * Requires at least: 4.0
 * Tested up to: 4.7
 *
 * Text Domain: automatic-post-categories
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Andreas Kviby
 * @since 1.0.0
 */

 // Create a helper function for easy SDK access.
function apc_fs() {
    global $apc_fs;

    if ( ! isset( $apc_fs ) ) {
        // Include Freemius SDK.
        require_once dirname(__FILE__) . '/freemius/start.php';

        $apc_fs = fs_dynamic_init( array(
            'id'                  => '727',
            'slug'                => 'automatic-post-categories',
            'type'                => 'plugin',
            'public_key'          => 'pk_577d0e0b1ec98f9678c81e8403cea',
            'is_premium'          => false,
            'has_premium_version' => false,
            'has_addons'          => false,
            'has_paid_plans'      => false,
            'menu'                => array(
                'slug'       => 'automatic_post_categories_settings',
                'account'    => false,
                'parent'     => array(
                    'slug' => 'options-general.php',
                ),
            ),
        ) );
    }

    return $apc_fs;
}

// Init Freemius.
apc_fs();

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-automatic-post-categories.php' );
require_once( 'includes/class-automatic-post-categories-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-automatic-post-categories-admin-api.php' );
require_once( 'includes/lib/class-automatic-post-categories-post-type.php' );
require_once( 'includes/lib/class-automatic-post-categories-taxonomy.php' );

/**
 * Returns the main instance of Automatic_Post_Categories to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Automatic_Post_Categories
 */
function Automatic_Post_Categories () {
	$instance = Automatic_Post_Categories::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = Automatic_Post_Categories_Settings::instance( $instance );
	}

	return $instance;
}

Automatic_Post_Categories();
