<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Automatic_Post_Categories_Settings {

	/**
	 * The single instance of Automatic_Post_Categories_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct ( $parent ) {
		$this->parent = $parent;

		$this->base = 'woorocks_apc_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item () {
		$page = add_options_page( __( 'Plugin Settings', 'automatic-post-categories' ) , __( 'WOOROCKS APC Settings', 'automatic-post-categories' ) , 'manage_options' , $this->parent->_token . '_settings' ,  array( $this, 'settings_page' ) );
		add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );
	}

	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets () {

		// We're including the farbtastic script & styles here because they're needed for the colour picker
		// If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below
		wp_enqueue_style( 'farbtastic' );
    	wp_enqueue_script( 'farbtastic' );

    	// We're including the WP media scripts here because they're needed for the image upload field
    	// If you're not including an image upload then you can leave this function call out
    	wp_enqueue_media();

    	wp_register_script( $this->parent->_token . '-settings-js', $this->parent->assets_url . 'js/settings' . $this->parent->script_suffix . '.js', array( 'farbtastic', 'jquery' ), '1.0.0' );
    	wp_enqueue_script( $this->parent->_token . '-settings-js' );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'automatic-post-categories' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

		$settings['standard'] = array(
			'title'					=> __( 'Basic', 'automatic-post-categories' ),
			'description'			=> __( 'Use the below settings before you start using the plugin.', 'automatic-post-categories' ),
			'fields'				=> array(
				array(
					'id' 			=> 'do-no-match',
					'label'			=> __( 'Do not match' , 'automatic-post-categories' ),
					'description'	=> __( 'Do not match categories with the following names (separate with comma)', 'automatic-post-categories' ),
					'type'			=> 'textarea',
					'default'		=> '',
					'placeholder'	=> __( 'headline, fotball, soccer', 'automatic-post-categories' )
				),
				array(
					'id' 			=> 'match-active',
					'label'			=> __( 'Use plugin', 'automatic-post-categories' ),
					'description'	=> __( 'If this is checked the plugin will be active and work according to the WP Cron settings. If unchecked no matching will be done.', 'automatic-post-categories' ),
					'type'			=> 'checkbox',
					'default'		=> 'off'
				),
				array(
					'id' 			=> 'delete-none-matching',
					'label'			=> __( 'Delete non matching', 'automatic-post-categories' ),
					'description'	=> __( 'If this is checked the plugin will take away terms from posts that can not be found anymore in title or content.', 'automatic-post-categories' ),
					'type'			=> 'checkbox',
					'default'		=> 'on'
				),
				array(
					'id' 			=> 'wpcron-schedule',
					'label'			=> __( 'Schedule', 'automatic-post-categories' ),
					'description'	=> __( 'Choose how often the plugin will execute and match your posts.<br><b>If you wanna change schedule you must first deactive the above checkbox, save settings and then re-activate again to make the new schedule take effect.</b>', 'automatic-post-categories' ),
					'type'			=> 'select',
					'options'		=> array( 'hourly' => 'Hourly', 'twicedaily' => 'Twice a day', 'daily' => 'Daily', 'every-30-minutes' => 'Every 30 minute','every-15-minutes' => 'Every 15 minute','every-5-minutes' => 'Every 5 minute','every-1-minutes' => 'Every 1 minute' ),
					'default'		=> 'hourly'
				),
			)
		);

		$settings['extra'] = array(
			'title'					=> __( 'PRO', 'automatic-post-categories' ),
			'description'			=> __( 'These settings will be available in the first PRO version due to release in february 2017<b>NOTE: Using and saving these PRO settings will have no effect in this version!</b>', 'automatic-post-categories' ),
			'fields'				=> array(
				array(
					'id' 			=> 'schedule-in-minutes',
					'label'			=> __( 'Scheduler PRO' , 'automatic-post-categories' ),
					'description'	=> __( 'In the PRO version you can setup how often you want the plugin to run in minutes and do not have to use the built in values.', 'automatic-post-categories' ),
					'type'			=> 'number',
					'default'		=> '',
					'placeholder'	=> __( '10', 'automatic-post-categories' )
				),
				array(
					'id' 			=> 'color-the-text',
					'label'			=> __( 'Color markup matcher', 'automatic-post-categories' ),
					'description'	=> __( 'When the plugin matches a category with content in the_content field this setting will add a surounding class that highlights the text in this selected color.', 'automatic-post-categories' ),
					'type'			=> 'color',
					'default'		=> '#21759B'
				),
				array(
					'id' 			=> 'match-color-active',
					'label'			=> __( 'Use above color marker', 'automatic-post-categories' ),
					'description'	=> __( 'If this is checked the plugin will use the color marker above.', 'automatic-post-categories' ),
					'type'			=> 'checkbox',
					'default'		=> ''
				),
				array(
					'id' 			=> 'match-color-active-linker',
					'label'			=> __( 'Link marked text to category', 'automatic-post-categories' ),
					'description'	=> __( 'If this is checked the plugin will also insert a link on the marked text matched to the category archive page.', 'automatic-post-categories' ),
					'type'			=> 'checkbox',
					'default'		=> ''
				),
				array(
					'id' 			=> 'multi_category',
					'label'			=> __( 'Match selected categories only', 'automatic-post-categories' ),
					'description'	=> __( 'The selected categories will be used when matching, others will be skipped.', 'automatic-post-categories' ),
					'type'			=> 'select_multi',
					'options'		=> array( 'news' => 'News', 'Press' => 'Press', 'Topnews' => 'Top News' ),
					'default'		=> array( 'news' )
				)
			)
		);

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

				if ( ! $current_section ) break;
			}
		}
	}

	public function settings_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {

		// Build page HTML
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
			$html .= '<h2>' . __( 'WooRocks - Automatic Post Category Settings' , 'automatic-post-categories' ) . '</h2>' . "\n";

			$tab = '';
			if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
				$tab .= $_GET['tab'];
			}

			// Show page tabs
			if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

				$html .= '<h2 class="nav-tab-wrapper">' . "\n";

				$c = 0;
				foreach ( $this->settings as $section => $data ) {

					// Set tab class
					$class = 'nav-tab';
					if ( ! isset( $_GET['tab'] ) ) {
						if ( 0 == $c ) {
							$class .= ' nav-tab-active';
						}
					} else {
						if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
							$class .= ' nav-tab-active';
						}
					}

					// Set tab link
					$tab_link = add_query_arg( array( 'tab' => $section ) );
					if ( isset( $_GET['settings-updated'] ) ) {
						$tab_link = remove_query_arg( 'settings-updated', $tab_link );
					}

					// Output tab
					$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

					++$c;
				}

				$html .= '</h2>' . "\n";
			}

			$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

				// Get settings fields
				ob_start();
				settings_fields( $this->parent->_token . '_settings' );
				do_settings_sections( $this->parent->_token . '_settings' );
				$html .= ob_get_clean();

				$html .= '<p class="submit">' . "\n";
					$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
					$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'automatic-post-categories' ) ) . '" />' . "\n";
				$html .= '</p>' . "\n";
			$html .= '</form>' . "\n";
		$html .= '</div>' . "\n";
		$html .= '<hr><h3>From WooRocks:</h3>' . "\n";
		$html .= '<p>Also make sure you look at our other plugin <a href="http://woorocks.com" target="_blank">our webpage</a> and small free snippets for you!</p>';
		echo $html;
	}

	/**
	 * Main Automatic_Post_Categories_Settings Instance
	 *
	 * Ensures only one instance of Automatic_Post_Categories_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Automatic_Post_Categories()
	 * @return Main Automatic_Post_Categories_Settings instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}
