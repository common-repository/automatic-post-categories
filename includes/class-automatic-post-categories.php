<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Automatic_Post_Categories {

	/**
	 * The single instance of Automatic_Post_Categories.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'automatic_post_categories';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		add_filter( 'cron_schedules', array( $this, 'woorocks_custom_cron_schedule'));

		register_deactivation_hook($this->file, array( $this, 'deactivate_cronjob' ) );

		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );


		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new Automatic_Post_Categories_Admin_API();
		}

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );
		if (get_option('woorocks_apc_match-active') == 'on') {
			add_action('init', array( $this , 'execute_plugin_code' ) );
		} else {
			$timestamp = wp_next_scheduled( 'woorocks_apc_cron_hook' );
			wp_unschedule_event( $timestamp, 'woorocks_apc_cron_hook' );
		}

	} // End __construct ()

function woorocks_custom_cron_schedule( $schedules ) {
		$schedules[ 'every-1-minutes' ] = array( 'interval' => 1 * MINUTE_IN_SECONDS, 'display' => __( 'Every 1 minutes', 'automatic-post-categories' ) );
		$schedules[ 'every-5-minutes' ] = array( 'interval' => 5 * MINUTE_IN_SECONDS, 'display' => __( 'Every 5 minutes', 'automatic-post-categories' ) );
		$schedules[ 'every-15-minutes' ] = array( 'interval' => 15 * MINUTE_IN_SECONDS, 'display' => __( 'Every 15 minutes', 'automatic-post-categories' ) );
		$schedules[ 'every-30-minutes' ] = array( 'interval' => 30 * MINUTE_IN_SECONDS, 'display' => __( 'Every 30 minutes', 'automatic-post-categories' ) );
		return $schedules;
}

public function deactivate_cronjob() {
		wp_clear_scheduled_hook('woorocks_apc_cron_hook');
		$timestamp = wp_next_scheduled( 'woorocks_apc_cron_hook' );
		wp_unschedule_event( $timestamp, 'woorocks_apc_cron_hook' );
	}

public function execute_plugin_code() {

	if (get_option('woorocks_apc_match-active') == 'on') {
		add_action ('woorocks_apc_cron_hook', array( $this, 'woorocks_apc_cron_execute') );
		if ( ! wp_next_scheduled( 'woorocks_apc_cron_hook' ) ) {

    	wp_schedule_event( time(), get_option('woorocks_apc_wpcron-schedule'), 'woorocks_apc_cron_hook' );
		} else {

		}

	} else {
		$timestamp = wp_next_scheduled( 'woorocks_apc_cron_hook' );
		wp_unschedule_event( $timestamp, 'woorocks_apc_cron_hook' );
		//remove_action ('woorocks_apc_cron_hook', 'woorocks_apc_cron_execute');
	}
}
public function woorocks_apc_cron_execute() {
	global $wpdb;
	$categories = get_terms( 'category', 'orderby=count&hide_empty=0' );
	$result = $wpdb->get_results("SELECT ID, post_title, post_date, post_content from $wpdb->posts where post_type = 'post' AND post_status = 'publish' ORDER BY ID desc");

	foreach ( $result as $contentItem )
	{
	  $contentToSearch = '';
	  $contentToSearch = strtolower($contentItem->post_title) . ' ';
		$contentToSearch .= strtolower($contentItem->post_content) . ' ';
	  foreach( $categories as $category ) {
			$categoryToSearch = '';
			$categoryToSearch = strtolower($category->name);
			if (strpos(strtolower(get_option('woorocks_apc_do-no-match')), $categoryToSearch) == false) {
		    if (preg_match("/\b$categoryToSearch\b/i", $contentToSearch))  {
		      wp_set_post_categories( $contentItem->ID, $category->term_id , true );
		    } else {
					if (get_option('woorocks_apc_delete-none-matching') == 'on') {
						wp_remove_object_terms( $contentItem->ID, $categoryToSearch, 'category' );//wp_set_post_categories( $contentItem->ID, array( 1 ), false );
						}

				}
			} else {
				// Here we have a term that should not be matched at all according to settings
			}
	  }
	}
	wp_reset_query();
}
	/**
	 * Wrapper function to register a new post type
	 * @param  string $post_type   Post type name
	 * @param  string $plural      Post type item plural name
	 * @param  string $single      Post type item single name
	 * @param  string $description Description of post type
	 * @return object              Post type class object
	 */
	public function register_post_type ( $post_type = '', $plural = '', $single = '', $description = '', $options = array() ) {

		if ( ! $post_type || ! $plural || ! $single ) return;

		$post_type = new Automatic_Post_Categories_Post_Type( $post_type, $plural, $single, $description, $options );

		return $post_type;
	}

	/**
	 * Wrapper function to register a new taxonomy
	 * @param  string $taxonomy   Taxonomy name
	 * @param  string $plural     Taxonomy single name
	 * @param  string $single     Taxonomy plural name
	 * @param  array  $post_types Post types to which this taxonomy applies
	 * @return object             Taxonomy class object
	 */
	public function register_taxonomy ( $taxonomy = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array() ) {

		if ( ! $taxonomy || ! $plural || ! $single ) return;

		$taxonomy = new Automatic_Post_Categories_Taxonomy( $taxonomy, $plural, $single, $post_types, $taxonomy_args );

		return $taxonomy;
	}

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-frontend' );
	} // End enqueue_scripts ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'automatic-post-categories', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'automatic-post-categories';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main Automatic_Post_Categories Instance
	 *
	 * Ensures only one instance of Automatic_Post_Categories is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Automatic_Post_Categories()
	 * @return Main Automatic_Post_Categories instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();

	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}
