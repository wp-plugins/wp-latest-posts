<?php
/** WP Latest Posts main class **/
class wpcuWPFnPlugin extends YD_Plugin {
	
	//TODO: separate front-end and back-end methods, only include necessary code
	
	const	CUSTOM_POST_NEWS_WIDGET_NAME	= 'wpcuwpfp-news-widget';
	const 	CUSTOM_POST_NONCE_NAME			= 'wpcufpn_editor_tabs';
	
	const 	POSITIVE_INT_GT1				= 'positive_integer_1+';		//Those fields need to have a positive integer value greater than 1
	const	BOOL							= 'bool';				 		//Booleans
	const	FILE_UPLOAD						= 'file_upload';				//File uploads
	const	LI_TO_ARRAY						= 'li_to_array';				//Convert sortable lists to array
	
	const	DEFAULT_IMG_PREFIX				= 'wpcufpn_default_img_';		//Default uploaded image file prefix
	const	MAIN_FRONT_STYLESHEET			= 'css/wpcufpn_front.css';		//Main front-end stylesheet
	const	MAIN_FRONT_SCRIPT				= 'js/wpcufpn_front.js';		//Main front-end jQuery script
	const 	DEFAULT_IMG						= 'img/default-image-fpnp.png';	//Default thumbnail image
    //const   THEME_LIBRARY                   = 'themes/default/default.php';
	
	const	USE_LOCAL_JS_LIBS				= true;
	
	/** Field default values **/
	private $_field_defaults = array(
		'default_img'			=> '',
		'source_type'			=> 'src_category',
		'cat_post_source_order'	=> 'date',
		'cat_post_source_asc'	=> 'desc',
		'cat_source_order'		=> 'date', 
		'cat_source_asc'		=> 'desc',
		'pg_source_order'		=> 'order',
		'pg_source_asc'			=> 'desc',
		'show_title'			=> 1,	// Wether or not to display the block title
		'amount_pages'			=> 1,
		'amount_cols'			=> 3,
		'pagination'			=> 2,
		'max_elts'				=> 5,
        'off_set'               => 0,   //number posts to skip
		'total_width'			=> 100,
		'total_width_unit'		=> 0,	//%
		'crop_title'			=> 2,
		'crop_title_len'		=> 1,
		'crop_text'				=> 2,
		'crop_text_len'			=> 2,
		'autoanimation'			=> 0,
		'autoanimation_trans'	=> 1,
		'theme'					=> 'default',
		'box_top'				=> array(),
		'box_left'				=> array('Thumbnail'),
		'box_right'				=> array('Title','Date','Text'),
		'box_bottom'			=> array(),
		'thumb_img'				=> 1,	// 0 == use featured image
		'image_size'            => 'mediumSize',
        'thumb_width'			=> 150,	// in px
        'thumb_height'			=> 150,	// in px
		'crop_img'				=> 0,	// 0 == do not crop (== resize to fit)
		'margin_left'			=> 0,
		'margin_top'			=> 0,
		'margin_right'			=> 4,
		'custom_css'			=> '',
		'margin_bottom'			=> 4,
		'date_fmt'				=> '',
		'read_more'				=> '',
		'default_img_previous'	=> '',	// Overridden in constructor
		'default_img'			=> '',	// Overridden in constructor
        'dfThumbnail'           => 'Thumbnail',
        'dfTitle'               => 'Title',
        'dfText'                => 'Text',
        'dfDate'                => 'Date',
	);
	
	/** Specific field value properties to enforce **/
	private $_enforce_fields = array(
		'amount_pages'	=> self::POSITIVE_INT_GT1,
		'amount_cols'	=> self::POSITIVE_INT_GT1,
		'amount_rows'	=> self::POSITIVE_INT_GT1,
		'max_elts'		=> self::POSITIVE_INT_GT1,
		'default_img'	=> self::FILE_UPLOAD,
		'box_top'		=> self::LI_TO_ARRAY,
		'box_left'		=> self::LI_TO_ARRAY,
		'box_right'		=> self::LI_TO_ARRAY,
		'box_bottom'	=> self::LI_TO_ARRAY,
	);
	
	/** Drop-down menu values **/
	private $_pagination_values = array(
		'None',
		'Arrows',
		'Arrows with bullets',
		'Bullets'
	);
	public $_width_unit_values = array(
		'%',
		'em',
		'px'
	);
	private $_thumb_img_values = array(
		'Use featured image',
		//'Use first attachment',
		'Use first image'
	);
	
	/**
	 * Headers for style.css files.
	 *
	 * @static
	 * @access private
	 * @var array
	 */
	private static $file_headers = array(
			'Name'        => 'Theme Name',
			'ThemeURI'    => 'Theme URI',
			'Description' => 'Description',
			'Author'      => 'Author',
			'AuthorURI'   => 'Author URI',
			'Version'     => 'Version',
			'Template'    => 'Template',
			'Status'      => 'Status',
			'Tags'        => 'Tags',
			'TextDomain'  => 'Text Domain',
			'DomainPath'  => 'Domain Path',
	);
	/**
	 * Counts how many widgets are being displayed
	 * @var int
	 */
	public	$widget_count = 0;
	
	/** 
	 * Constructor
	 * 
	 */
	public function __construct( $opts ) {

		parent::YD_Plugin( $opts );
		$this->form_blocks = $opts['form_blocks']; // YD Legacy (was to avoid "backlinkware")
		
		/** Check PHP and WP versions upon install **/
		register_activation_hook( dirname( dirname( __FILE__ ) ), array( $this, 'activate' ) );

		//add_action('init', array($this, 'checkUsed'));

        /** Setup default image **/
		$this->_field_defaults['default_img_previous'] = plugins_url( self::DEFAULT_IMG, dirname( __FILE__ ) );
		$this->_field_defaults['default_img'] = plugins_url( self::DEFAULT_IMG, dirname( __FILE__ ) );
		
		/** Sets up custom post types **/
		add_action( 'init', array( $this, 'setupCustomPostTypes' ) );
		
		/** Register our widget (implemented in separate wp-fpn-widget.inc.php class file) **/
		add_action( 'widgets_init', function(){
			register_widget( 'wpcuFPN_Widget' );
		});
		
		/** Register our shortcode **/
		add_shortcode('frontpage_news', array($this, 'applyShortcode'));
		
		
		
		if( is_admin() ) {

            /** Load tabs ui + drag&drop ui **/
			add_action('admin_enqueue_scripts', array( $this, 'loadAdminScripts' ) );
			
			/** Load admin css for tabs **/
			add_action( 'admin_init',	array( $this, 'addAdminStylesheets' ) );
			
			/** Customize custom post editor screen **/
			//add_action( 'admin_head', array( $this, 'changeIcon' ) );	//Unused
			add_action( 'admin_menu', array( $this, 'setupCustomMetaBoxes' ) );
			add_action( 'admin_menu', array( $this, 'setupCustomMenu' ) );
			add_action( 'save_post', array( $this, 'saveCustomPostdata' ) );
			
			/** Customize Tiny MCE Editor **/
			add_action( 'admin_init', array( $this, 'setupTinyMce' ) );
			add_action( 'in_admin_footer', array( $this, 'editorFooterScript' ) );
			
			/** Tiny MCE 4.0 fix **/
			if( get_bloginfo('version') >= 3.9 ) {
				add_action( 'media_buttons', array( $this, 'editorButton' ), 1000 ); //1000 = put it at the end
			}
			
			if( !class_exists('wpcuWPFnProPlugin') )
				add_filter( 'plugin_row_meta', array( $this, 'addProLink' ), 10, 2 );
			
		} else {
			
			/** Load our theme stylesheet on the front if necessary **/
			add_action( 'wp_print_styles',	array( $this, 'addStylesheet' ) );
			
			/** Load our fonts on the front if necessary **/
			add_action( 'wp_print_styles',	array( $this, 'addFonts' ) );

			/** Load our front-end slide control script **/
			//add_action( 'wp_print_scripts', array( $this, 'addFrontScript' ),0 );
			add_action( 'the_posts' , array($this, 'prefixEnqueue'),100);
			//add_action( 'after_setup_theme', array( $this, 'child_theme_setup' ) );
		}
	}
	
	/**
	 * Plugin Activation hook function to check for Minimum PHP and WordPress versions
	 * @see http://wordpress.stackexchange.com/questions/76007/best-way-to-abort-plugin-in-case-of-insufficient-php-version
	 * 
	 * @param string $wp Minimum version of WordPress required for this plugin
	 * @param string $php Minimum version of PHP required for this plugin
	 */
	public function activate( $wp = '3.2', $php = '5.3.1' ) {
		global $wp_version;
		if ( version_compare( PHP_VERSION, $php, '<' ) )
        {
            $flag = 'PHP';

        } elseif ( version_compare( $wp_version, $wp, '<' ) ) {
            $flag = 'WordPress';
        } else {
            $this->checkUsed();
            return;
        }
        $version = 'PHP' == $flag ? $php : $wp;
        deactivate_plugins( basename( __FILE__ ) );
        wp_die('<p>The <strong>WP Latest Posts</strong> plugin requires '.$flag.'  version '.$version.' or greater.</p>','Plugin Activation Error',  array( 'response'=>200, 'back_link'=>TRUE ) );
	}
    /**
     * check user
     * use new theme default for new users
     */
    public function checkUsed()
    {
        global $wpdb;
        $oldBlock = get_option("_wpcufpn_onceLoad");
        if (empty($oldBlock))
        {
            $meta_key = "_wpcufpn_settings";
            $postsId = $wpdb->get_results($wpdb->prepare(" SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s ", $meta_key));
            if ( ! empty($postsId))
            {
                foreach($postsId as $postId)
                {
                    $postId = $postId->post_id;
                    $postMeta = get_post_meta($postId, "_wpcufpn_settings", true);
                    if (strpos ($postMeta['theme'], "default"))
                    {
                        $postMeta['theme'] = str_replace("default", "oldDefault", $postMeta['theme']);
                        $postMeta['theme'] = addslashes($postMeta['theme']);
                        update_post_meta($postId, "_wpcufpn_settings", $postMeta);
                    }
                }
            }
            $onceLoad = 1;
            add_option("_wpcufpn_onceLoad", $onceLoad, "", "no");
        }

    }

	/** 
	 * Sets up WP custom post types
	 * 
	 */
	public function setupCustomPostTypes() {
		$labels = array(
			'name' 					=> __( 'WP Latest Posts Blocks', 'wpcufpn' ),
			'singular_name' 		=> __( 'WPLP Block', 'wpcufpn' ),
			'add_new' 				=> __( 'Add New', 'wpcufpn' ),
			'add_new_item' 			=> __( 'Add New WPLP Block', 'wpcufpn' ),
			'edit_item' 			=> __( 'Edit WPLP Block', 'wpcufpn' ),
			'new_item' 				=> __( 'New WPLP Block', 'wpcufpn' ),
			'all_items' 			=> __( 'All News Blocks', 'wpcufpn' ),
			'view_item' 			=> __( 'View WPLP Block', 'wpcufpn' ),
			'search_items'			=> __( 'Search WPLP Blocks', 'wpcufpn' ),
			'not_found' 			=> __( 'No WPLP Block found', 'wpcufpn' ),
			'not_found_in_trash'	=> __( 'No WPLP Block found in Trash', 'wpcufpn' ),
			'parent_item_colon' 	=> '',
			'menu_name' 			=> __( 'Latest Posts', 'wpcufpn' )
		);
		register_post_type( self::CUSTOM_POST_NEWS_WIDGET_NAME, array(
			'public'		=> false,
			'show_ui'		=> true,
			'menu_position'	=> 5,
			'labels'		=> $labels,
			'supports'		=> array(
				'title', 'author'
			),
			'menu_icon'				=> 'dashicons-admin-page',
		) );
	}
	
	/**
	 * Append our theme stylesheet if necessary
	 * 
	 */
	function addStylesheet() {
		/*
		TODO: is there a way to load our theme stylesheet only where necessary?
		global $wpcufpn_needs_stylesheet;
		if( !$wpcufpn_needs_stylesheet )
			return;
		*/
		
		$myStyleUrl 	= plugins_url( self::MAIN_FRONT_STYLESHEET, dirname( __FILE__ ) );
		$myStylePath	= plugin_dir_path( dirname( __FILE__ ) ) . self::MAIN_FRONT_STYLESHEET;
		
		if ( file_exists( $myStylePath ) ) {
			wp_register_style( 'myStyleSheets', $myStyleUrl );
			wp_enqueue_style( 'myStyleSheets' );
		}

	}
	
	
	/**
	 * Append our fonts if necessary
	 *
	 */
	function addFonts() {
		/*
		TODO: is there a way to load our fonts only where necessary?
		global $wpcufpn_needs_fonts;
		if( !$wpcufpn_needs_fonts )
			return;
		*/
	
		$myFontsUrl 	= 	'https://fonts.googleapis.com/css?' .
							'family=Raleway:400,500,600,700,800,900|' .
							'Alegreya:400,400italic,700,700italic,900,900italic|' .
							'Varela+Round' .
							'&subset=latin,latin-ext';
	
		wp_register_style( 'myFonts', $myFontsUrl );
		wp_enqueue_style( 'myFonts' );
	}
	
	/**
	 * Append our front-end script if necessary
	 * 
	 */
	function addFrontScript() {
		//TODO: load only if necessary (is this possible ?)
		
		wp_enqueue_script(
			'wpcufpn-front',
			plugins_url( self::MAIN_FRONT_SCRIPT, dirname( __FILE__ ) ),
			array( 'jquery' ),
			'0.1',
			true
		);
	}
	
	/**
	 * Save our custom setting fields in the WP database
	 * 
	 * @param inc $post_id
	 * @return inc $post_id (unchanged)
	 */
	public function saveCustomPostdata( $post_id ) {
        global $post;

        if ( self::CUSTOM_POST_NEWS_WIDGET_NAME != get_post_type( $post_id ) )
			return $post_id;
		
		if ( ! isset( $_POST[self::CUSTOM_POST_NONCE_NAME . '_nonce'] ) )
			return $post_id;
		
		$nonce = $_POST[self::CUSTOM_POST_NONCE_NAME . '_nonce'];
		if ( ! wp_verify_nonce( $nonce, self::CUSTOM_POST_NONCE_NAME ) )
			return $post_id;
		
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;
		
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return $post_id;
		
		$my_settings = get_post_meta( $post->ID, '_wpcufpn_settings', true );
        //var_dump($my_settings); die();
		$my_settings = wp_parse_args( $my_settings, $this->_field_defaults );
		
		/** File uploads **/
		//error_log( 'FILES: ' . serialize( $_FILES ) );	//Debug
		foreach( $_FILES as $field_name => $field_value ) {
			if( preg_match( '/^wpcufpn_/', $field_name ) ) {
				//error_log( 'matched wpcufpn_' );			//Debug
				$new_field_name = preg_replace( '/^wpcufpn_/', '', $field_name );
				if( is_uploaded_file( $_FILES[$field_name]['tmp_name'] ) ) {
					$uploads = wp_upload_dir();
					$upload_dir = ( $uploads['path'] ) . '/';
					$upload_url = ( $uploads['url'] ) . '/';
					if( preg_match( '/(\.[^\.]+)$/', $_FILES[$field_name]['name'], $matches ) )
						$ext = $matches[1];
					$upload_file = self::DEFAULT_IMG_PREFIX . date("YmdHis") . $ext;
					if ( rename( $_FILES[$field_name]['tmp_name'],
							$upload_dir . $upload_file )
					) {
						chmod( $upload_dir . $upload_file, 0664 );
						// $this->update_msg .= __( 'Temporary file ' ) . $_FILES["game_image"]["tmp_name"] .
						//	" was moved to " . $upload_dir . $upload_file;
						//var_dump( $_FILES["game_image"] );
						$my_settings[$new_field_name] = $upload_url . $upload_file;
						//error_log( 'renamed ' . $upload_url . $upload_file );	//Debug
					} else {
						$this->update_msg .= __( 'Processing of temporary uploader file has failed' .
								' please check for file directory ' ) . $upload_dir;
						//error_log( $this->update_msg );	//Debug
					}
				} else {
					//error_log( '!is_uploaded_file(' . $_FILES[$field_name]['tmp_name'] . ')' );	//Debug
					
					/** keep the previous image **/
					if( isset( $_POST[$field_name . '_previous'] ) && $_POST[$field_name . '_previous'] )
						$my_settings[$new_field_name] = $_POST[$field_name . '_previous'];
				}
			}
		}
		//var_dump($_POST);
		/** Normal fields **/
		foreach( $_POST as $field_name => $field_value ) {
			if( preg_match( '/^wpcufpn_/', $field_name ) ) {
				if( preg_match( '/_none$/', $field_name ) )
					continue;
				$field_name = preg_replace( '/^wpcufpn_/', '', $field_name );
				if( is_array( $field_value ) ) {
					$my_settings[$field_name] = $field_value;
				} else {
					if( preg_match( '/^box_/', $field_name ) ) {
						/** No sanitizing for those fields that are supposed to contain html **/
						$my_settings[$field_name] = $field_value;
					} else {
						$my_settings[$field_name] = sanitize_text_field( $field_value );
					}
					
					/** Enforce specific field value properties **/
					if( isset(  $this->_enforce_fields[$field_name] ) ) {
						if( self::POSITIVE_INT_GT1 == $this->_enforce_fields[$field_name] ) {
							$my_settings[$field_name] = intval($my_settings[$field_name]);
							if( $my_settings[$field_name] < 1 )
								$my_settings[$field_name] = 1;
						}
						if( self::BOOL == $this->_enforce_fields[$field_name] ) {
							$my_settings[$field_name] = intval($my_settings[$field_name]);
							if( $my_settings[$field_name] < 1 )
								$my_settings[$field_name] = 0;
							if( $my_settings[$field_name] >= 1 )
								$my_settings[$field_name] = 1;
						}
						if( self::FILE_UPLOAD == $this->_enforce_fields[$field_name] ) {
							//Do nothing I guess.
						}
						if( self::LI_TO_ARRAY == $this->_enforce_fields[$field_name] ) {
							if( $field_value ) {
								$values = preg_split( '/<\/li><li[^>]*>/i', $field_value );
							} else {
								$values = array();
							}
							if($values)
								array_walk($values, function(&$value, $key){
									$value = strip_tags($value);
								});
							$my_settings[$field_name] = $values;
						}
					}
				}
			}
		}
		update_post_meta( $post_id, '_wpcufpn_settings', $my_settings );
		
		return $post_id;
	}
	
	/**
	 * Loads js/ajax scripts
	 * 
	 */
	public function loadAdminScripts( $hook ) {
		
		/** Only load on post edit admin page **/
		if( 'post.php' != $hook && 'post-new.php' != $hook )
			return $hook;
		
		if( wpcuWPFnPlugin::CUSTOM_POST_NEWS_WIDGET_NAME != get_post_type() )
			return $hook;
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('jquery-ui-mouse');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-draggable');
		wp_enqueue_script('jquery-ui-droppable');
		wp_enqueue_script('jquery-ui-button');
		wp_enqueue_script('jquery-ui-slider');
		/*
		wp_enqueue_script(
			'uniform',
			plugins_url( 'js/jquery.uniform.min.js', dirname( __FILE__ ) ),
			array( 'jquery' ),
			'0.1',
			true
		);
		*/
//		wp_enqueue_script(
//			'wpcufpn-colorpicker',
//			plugins_url( 'js/wpcufpn_colorpicker.js', dirname( __FILE__ ) ),
//			array( 'jquery' ),
//			'0.1',
//			true
//		);
		
		wp_enqueue_script(
			'wpcufpn-back',
			plugins_url( 'js/wpcufpn_back.js', dirname( __FILE__ ) ),
			array( 'jquery' ),
			'0.1',
			true
		);
        wp_enqueue_script( 'wp-color-picker');

        wp_enqueue_script('wpcufpn-newColorPicker', plugins_url('js/wpcufpn_newColorPicker.js', dirname(__FILE__)),
            array('jquery'),
            '0.1',
            true
            );

        /** add codemirror js */
        wp_enqueue_script('wpcufpn-codemirror', plugins_url('codemirror/lib/codemirror.js', dirname(__FILE__)),
            array('jquery'),
            '0.1',
            true
        );
        /** mode css */
        wp_enqueue_script('wpcufpn-codemirrorMode', plugins_url('codemirror/mode/css/css.js', dirname(__FILE__)),
            array('jquery'),
            '0.1',
            true
        );

        wp_enqueue_script('wpcufpn-codemirrorAdmin', plugins_url('js/wpcufpn_codemirrorAdmin.js', dirname(__FILE__)),
            array('jquery'),
            '0.1',
            true
        );
	}
	
	/**
	 * Load additional admin stylesheets
	 * of jquery-ui
	 *
	 */
	function addAdminStylesheets() {
		
		/** add color picker css */
        wp_enqueue_style('wp-color-picker');

        wp_register_style( 'uiStyleSheet', plugins_url( 'css/jquery-ui-custom.css', dirname( __FILE__ ) ) );
		
		wp_enqueue_style( 'uiStyleSheet' );
		
		wp_register_style( 'wpcufpnAdmin', plugins_url( 'css/wpcufpn_admin.css', dirname( __FILE__ ) ) );
		wp_enqueue_style( 'wpcufpnAdmin' );
		
		wp_register_style( 'unifStyleSheet', plugins_url( 'css/uniform/css/uniform.default.css', dirname( __FILE__ ) ) );
		wp_enqueue_style( 'unifStyleSheet' );

        /** add codemirror css */
        wp_register_style('wpcufpn_codemirror', plugins_url( 'codemirror/lib/codemirror.css', dirname( __FILE__ ) ) );
        wp_enqueue_style('wpcufpn_codemirror');

        wp_register_style('wpcufpn_codemirrorTheme', plugins_url( 'codemirror/theme/3024-day.css', dirname( __FILE__ ) ) );
        wp_enqueue_style('wpcufpn_codemirrorTheme');
	}
	
	
	/**
	 * Customizes the default custom post type editor screen:
	 * - removes default meta-boxes
	 * - adds our own settings meta-boxes
	 * 
	 */
	 
	public function setupCustomMetaBoxes() {
		remove_meta_box('slugdiv', self::CUSTOM_POST_NEWS_WIDGET_NAME, 'core');
		remove_meta_box('authordiv', self::CUSTOM_POST_NEWS_WIDGET_NAME, 'core');
		
		add_meta_box( 
			'wpcufpnnavtabsbox', 
			__( 'WP Latest Posts Block Settings', 'wpcufpn' ), 
			array( $this, 'editorTabs' ), 
			self::CUSTOM_POST_NEWS_WIDGET_NAME, 
			'normal', 
			'core' 
		);
	}
	
	/**
	 * Adds our admin menu item(s)
	 * 
	 */
	public function setupCustomMenu() {
		add_submenu_page(
			'edit.php?post_type=wpcuwpfp-news-widget',
			'About...',
			'About...',
			'activate_plugins',
			'about-wpfpn',
			array( $this, 'displayAboutTab' )
		);
	}
	
	/**
	 * Create navigation tabs in the main configuration screen
	 * 
	 */	
	public function editorTabs() {
		wp_nonce_field( self::CUSTOM_POST_NONCE_NAME, self::CUSTOM_POST_NONCE_NAME . '_nonce' );
				
		//TODO: externalize js, cleanup obsolete/commented code
		?>

		<div style="background:#fff" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
		
			<script type="text/javascript">
				(function($) {
					$(document).ready(function() {

						$('.wpcufpntabs').tabs();

						$('#tab-1 ul.hidden').hide();
						
						$('.source_type_sel').click(function(e){
							//console.log( 'clicked ' + $(this).val() );
							//$( '.wpcufpntabs' ).tabs( 'load', $(this).val() );
							$( '#tab-' + $(this).val() ).click();
						});

						$( '#tab-' + $('input[name=wpcufpn_source_type]:checked').val() ).click();

						/** You can check the all box or any other boxes, but not both **/
						$('#cat_all').click(function(e){
							if( $(this).is(':checked') ) {
								$('.cat_cb').attr('checked', false);
							}
						});
						$('.cat_cb').click(function(e){
							if( $(this).is(':checked') ) {
								$('#cat_all').attr('checked', false);
							}
						});

						/** UI switches **/
						//$("select, input, button").uniform();
						//$('.radioset').buttonset();

						/** Drag & Drop widget configurator **/
						
						/*
						$('ul.arrow_col li').click(function(e) {
							console_log( 'Clicked on ' + $(this).text() );
							$('.drop_zone_col .top').append('<div class="draggable">' + $(this).text() + '</div>');
							$('.draggable').draggable();
						});
						*/
						
						/*
						$('.drop_zone_col .wpcu-inner-admin-block ul').droppable({
							drop: function( event, ui ) {
								$( this ).addClass( "ui-state-highlight" );
								$(ui.draggable).css('background','#F00');
								console_log( 'dropped! ui:' + $(ui.draggable).text() );
								$(this).append('<div class="locked">' + $(ui.draggable).text() + '&nbsp;<a href="#">x</a></div>');
								$(ui.draggable).remove();
							}
						});
						*/

						/*
						$('ul.arrow_col').droppable({
							drop: function( event, ui ) {
								console_log( 'coming back: ' + $(ui.draggable).text() + ' - ' + ui.helper );
								console_log( ui.helper );
								if($(ui.helper).hasClass('ui-sortable-helper')) {
									$(ui.helper).toggle( "pulsate" ).toggle( "pulsate" );
									$(ui.draggable).remove();
								}
							}
						});
						*/
						
						
						
						/*
						$('ul.arrow_col li').draggable({ 
							connectToSortable: '.sortable',
							helper: 'clone',
							containment: '#wpcufpn_config_zone'
						});
						*/
						
						/*
						$('#trashbin').sortable({
							update: function( event, ui ) {
								console_log( 'trash was updated: ' + $(ui.item).text() );
								$(ui.item).remove();
							},
							receive: function( event, ui ) {
								console_log( 'trash received: ' + $(ui.item).text() );
							}
						});
						$('#trashbin').disableSelection();
						*/

						$('.slider').slider({
							min: 0,
							max: 50,
							slide: function( event, ui ) {
								console.log( event );
								console.log( ui );
								field = event.target.id.substr(7);
								console.log( field );	//Debug
								$( "#" + field ).val( ui.value );
							}
						});
						$('.slider').each(function() {
							//console.log( this.id );
							var field = this.id.substr(7);
							$(this).slider({
								min: 0,
								max: 50,
								value: $( "#" + field ).val(),
								slide: function( event, ui ) {
									//console.log( event );
									//console.log( ui );
									//field = event.target.id.substr(7);
									//console.log( field );	//Debug
									$( "#" + field ).val( ui.value );
								}
							});
						});
						$('#margin_sliders input').change( function() {
							$('#slider_' + this.id).slider( 'value', $(this).val() );
						});

						$('form').attr( 'enctype', 'multipart/form-data' );
						
					});	//document.ready()
				})( jQuery );
				function console_log( msg ) {
					if(window.console) {
						window.console.log( msg );
					}
				}
			</script>
		
			<div id="wpcufpnnavtabs" class="wpcufpntabs">
				<ul>
					<li><a href="#tab-1"><?php _e( 'Content source', 'wpcufpn' ); ?></a></li>
					<li><a href="#tab-2"><?php _e( 'Display and theme', 'wpcufpn' ); ?></a></li>
					<li><a href="#tab-3"><?php _e( 'Images source', 'wpcufpn' ); ?></a></li>
					<li><a href="#tab-4"><?php _e( 'Advanced', 'wpcufpn' ); ?></a></li>
				</ul>

				<div id="tab-1" class="metabox_tabbed_content wpcufpntabs">
					<?php $this->displayContentSourceTab(); ?>
				</div>
				
				<div id="tab-2" class="metabox_tabbed_content">
					<?php $this->displayDisplayThemeTab(); ?>
				</div>
				
				<div id="tab-3" class="metabox_tabbed_content">
					<?php $this->displayImageSourceTab(); ?>
				</div>
				
				<div id="tab-4" class="metabox_tabbed_content">
					<?php $this->displayAdvancedTab(); ?>
				</div>
				
			</div>
			
		</div>
		<?php
	}
	
	/**
	 * Wp Latest Posts Widget Content source Settings tab
	 * 
	 */
	private function displayContentSourceTab() {
		global $post;
		$checked = array();
		$settings = get_post_meta( $post->ID, '_wpcufpn_settings', true );
		if( empty( $settings ) )
			$settings = $this->_field_defaults;
		
		if( !isset($settings['source_type']) || !$settings['source_type'] )
			$settings['source_type'] = 'src_category';
		
		$source_type_checked[$settings['source_type']] = ' checked="checked"';
		
		
		$tabs = array(
			'tab-1-1' => array(
				'id'		=> 'tab-src_category',
				'name'		=> __( 'Post categories', 'wpcufpn' ),
				'value'		=> 'src_category',
				'method'	=> array( $this, 'displayContentSourceCategoryTab' )
			),
			'tab-1-2' => array(
				'id'	=> 'tab-src_page',
				'name'	=> __( 'Pages', 'wpcufpn' ),
				'value'		=> 'src_page',
				'method'	=> array( $this, 'displayContentSourcePageTab' )
			)
		);
		$tabs = apply_filters( 'wpcufpn_src_type', $tabs );
		
		
		?>

		<ul class="hidden">
			<?php foreach( $tabs as $tabhref => $tab ) : ?>
			<li><a href="#<?php echo $tabhref; ?>" id="<?php echo $tab['id']; ?>"><?php echo $tab['name']; ?></a></li>
			<?php endforeach; ?>
		</ul>
		
		<ul class="horizontal">
			<?php $idx=0; ?>
			<?php foreach( $tabs as $tabhref => $tab ) : ?>
			<li><input type="radio" name="wpcufpn_source_type" id="sct<?php echo ++$idx; ?>" value="<?php echo $tab['value']; ?>" class="source_type_sel" <?php echo (isset($source_type_checked[$tab['value']])?$source_type_checked[$tab['value']]:""); ?> />
				<label for="sct<?php echo ++$idx; ?>" class="post_radio"><?php echo $tab['name']; ?></label></li>
			<?php endforeach; ?>
		</ul>
		
		<?php foreach( $tabs as $tabhref => $tab ) : ?>
			<div id="<?php echo $tabhref; ?>">
				<?php call_user_func( $tab['method'] ); ?>
			</div>
		<?php endforeach; ?>
		
		<?php
	}
	
	/**
	 * Wp Latest Posts Widget Display and theme Settings tab
	 *
	 */
	private function displayDisplayThemeTab() {
		global $post;
		$settings = get_post_meta( $post->ID, '_wpcufpn_settings', true );
		if( empty( $settings ) )
			$settings = $this->_field_defaults;
		
		if( isset($settings['show_title']) )
			$show_title_checked[$settings['show_title']] = ' checked="checked"';
		if( isset($settings['pagination']) )
			$pagination_selected[$settings['pagination']] = ' selected="selected"';
		if( isset($settings['total_width_unit']) )
			$units_selected[$settings['total_width_unit']] = ' selected="selected"';


		/*
		 * 
		 * Specific parameters with Mansonry
		 * 
		 */ 
		$classdisabled="";
		if (strpos($settings["theme"],'masonry') || strpos($settings["theme"],'portfolio')){
			$classdisabled=" disabled";
		}
		
		$classdisabledsmooth="";
		if (strpos($settings["theme"],'timeline') ){
			$classdisabledsmooth=" disabled";
		}

		echo '<div class="wpcu-inner-admin-col">';
		
		// -block---------------------------------- //
		echo '<div class="wpcu-inner-admin-block">';
		echo '<ul class="fields">';
		
		/** Show title radio button set **/
		echo '<li class="field"><label class="coltab">' . __( 'Show title', 'wpcufpn' ) . '</label>' .
				'<span class="radioset">' .
				'<input id="show_title1" type="radio" name="wpcufpn_show_title" value="0" ' . (isset($show_title_checked[0])?$show_title_checked[0]:'') . '/>' .
				'<label for="show_title1">' . __('Off', 'wpcufpn') . '</label>' .
				'<input id="show_title2" type="radio" name="wpcufpn_show_title" value="1" ' . (isset($show_title_checked[1])?$show_title_checked[1]:'') . '/>' .
				'<label for="show_title2">' . __('On', 'wpcufpn') . '</label>' .
				'</span>';
		echo '</li>';
		/*
		echo '<li class="field '.$classdisabled.$classdisabledsmooth.'"><label for="amount_pages" class="coltab">' . __( 'Number of pages with posts', 'wpcufpn' ) . '</label>' .
			'<input id="amount_pages" type="text" name="wpcufpn_amount_pages" value="' . htmlspecialchars( isset($settings['amount_pages'])?$settings['amount_pages']:'' ) . '" class="short-text" '.$classdisabled.$classdisabledsmooth.'/></li>';
		*/
        /*
         * display number of columns
         */
		echo '<li class="field '.$classdisabledsmooth.'"><label for="   amount_cols" class="coltab">' . __( 'Number of columns', 'wpcufpn' ) . '</label>' .
			'<input id="amount_cols" type="text" name="wpcufpn_amount_cols" value="' . htmlspecialchars( isset($settings['amount_cols'])?$settings['amount_cols']:'3' ) . '" class="short-text" '.$classdisabledsmooth.'/></li>';
		/*
		 * display number of rows
		 */
		echo '<li class="field '.$classdisabled.$classdisabledsmooth.'"><label for="amount_rows" class="coltab">' . __( 'Number of rows', 'wpcufpn' ) . '</label>' .
			'<input id="amount_rows" type="text" name="wpcufpn_amount_rows" value="' . htmlspecialchars( isset($settings['amount_rows'])?$settings['amount_rows']:'' ) . '" class="short-text" '.$classdisabled.$classdisabledsmooth.'/></li>';
				
			
		/* Deactivated for now (vertical sliders) , TODO: reactivate
		echo '<li class="field"><label for="amount_rows" class="coltab">' . __( 'Number of rows', 'wpcufpn' ) . '</label>' .
			'<input id="amount_rows" type="text" name="wpcufpn_amount_rows" value="' . htmlspecialchars( $settings['amount_rows'] ) . '" class="short-text" /></li>';
		*/
		
		/** Pagination drop-down **/
		echo '<li class="field '.$classdisabled.$classdisabledsmooth.'"><label for="pagination" class="coltab">' . __( 'Pagination', 'wpcufpn' ) . '</label>' .
				'<select id="pagination" name="wpcufpn_pagination" '.$classdisabled.$classdisabledsmooth.'>';
		foreach( $this->_pagination_values as $value=>$text ) {
			echo '<option value="' . $value . '" ' . (isset($pagination_selected[$value])?$pagination_selected[$value]:'') . '>';
			echo htmlspecialchars( __( $text, 'wpcufpn' ) );
			echo '</option>';
		}
		echo '</select></li>';
		/*
		 * display max elements
		 */
		echo '<li class="field"><label for="max_elts" class="coltab">' . __( 'Max number of elements', 'wpcufpn' ) . '</label>' .
				'<input id="max_elts" type="text" name="wpcufpn_max_elts" value="' . htmlspecialchars( isset($settings['max_elts'])?$settings['max_elts']:'' ) . '" class="short-text" /></li>';
		/*
		 * display total width
		 */
        echo '<li class="field"><label for="total_width" class="coltab">' . __( 'Total width', 'wpcufpn' ) . '</label>' .
				'<input id="total_width" type="text" name="wpcufpn_total_width" value="' . htmlspecialchars( isset($settings['total_width'])?$settings['total_width']:'' ) . '" class="short-text" />';
		
		/** Width units drop-down **/
		echo '<select id="total_width_unit" name="wpcufpn_total_width_unit">';
		foreach( $this->_width_unit_values as $value=>$text ) {
			echo '<option value="' . (isset($value)?$value:'') . '" ' . (isset($units_selected[$value])?$units_selected[$value]:'') . '>' .
				$text . '</option>';
		}
		echo '</select></li>';
        /** offset number posts to skip */
        echo '<li class="field"><label for="off_set" class="coltab">' . __( 'Number of posts to skip:', 'wpcufpn' ) . '</label>' .
            '<input id="off_set" type="text" name="wpcufpn_off_set" value="' . htmlspecialchars( isset($settings['off_set'])?$settings['off_set']:'' ) . '" class="short-text" />';

		do_action( 'wpcufpn_displayandtheme_add_fields', $settings );
		echo '</ul>';	//fields
		echo '</div>';	//wpcu-inner-admin-block
		// ---------------------------------------- //
		
		if( !class_exists('wpcuWPFnProPlugin') ) {
			echo '<div class="wpcufpn_pro_reminder_rows wpcu-inner-admin-block notice notice-success is-dismissible below-h2"><p>' .
				__(
					'Additional advanced customization features<br/> and various beautiful ' .
					'pre-configured templates and themes<br/> are available with the optional ' .
					'<a href="http://www.joomunited.com/wordpress-products/wp-latest-posts" target="_blank">pro add-on</a>.'
				) .
			'</p></div>';
		} else {
			do_action( 'wpcufpn_displaytheme_col1_add_fields', $settings );
		}
		
		echo '</div>';	//wpcu-inner-admin-col
		echo '<div class="wpcu-inner-admin-col">';
		
		if( isset($settings['theme']) )
			$theme_selected[$settings['theme']] = ' selected="selected"';
		
		// -block---------------------------------- //
		echo '<div class="wpcu-inner-admin-block with-title with-border">';
		echo '<h4>Theme choice and preview</h4>';
		echo '<ul class="fields">';
		
		/** Theme drop-down **/
		echo '<li class="field"><label for="theme" class="coltab">' . __( 'Theme', 'wpcufpn' ) . '</label>' .
				'<select id="theme" name="wpcufpn_theme">';
		$all_themes = (array)$this->themeLister();
		wp_localize_script( 'wpcufpn-back', 'themes', $all_themes );
		//var_dump( $all_themes );	//Debug
		foreach( $all_themes as $dir=>$theme ) {
            $idOldDefault = "";
            $disabled = "";
            if ($theme['name'] == "Old Default theme")
            {
                $idOldDefault = 'id="oldDefaultThemeOption"';
            }
            echo '<option '.$idOldDefault.' value="' . $dir . '" ' . (isset($theme_selected[$dir])?$theme_selected[$dir]:'') . '>';
			echo $theme['name'];
			echo '</option>';	
		}
		echo '</select></li>';
		
		echo '</ul>';	//fields
		echo '<div class="wpcufpn-theme-preview">';
		
		/** enforce default (first found theme) **/
		if( !isset($settings['theme']) || 'default' == $settings['theme'] ) {
			reset($all_themes);
			$settings['theme'] = key($all_themes);
		}
		
		if( isset($all_themes[$settings['theme']]['theme_url']) ) {
			$screenshot_file_url = $all_themes[$settings['theme']]['theme_url'] . '/screenshot.png';
			$screenshot_file_path = $all_themes[$settings['theme']]['theme_root'] . '/screenshot.png';
			
		} else {
			$screenshot_file = false;
		}
		//echo 'screenshot file: ' . $screenshot_file . '<br/>';	//Debug
		if( isset($screenshot_file_path) && file_exists( $screenshot_file_path ) ) {
			echo '<img alt="preview" src="' . $screenshot_file_url . 
				'" style="width:100%;height:100%;" />';
		}
		echo '</div>';
		echo '</div>';	//wpcu-inner-admin-block
		// ---------------------------------------- //
		
		$box_top = $box_left = $box_right = $box_bottom = '';
		
		
		/*
		 * 
		 * Remove configuration
		 * 
		 */
		
		if( !class_exists('wpcuWPFnProPlugin') ) {
			$classdisabled=" disabled";
		} else {
			$classdisabled="";
		}
        /**
         * check WPLP Block
         */

        if (strpos($settings['theme'], "oldDefault"))
        {
            $classdisabled=" disabled";
            include_once(dirname(plugin_dir_path(__FILE__)) . '/themes/oldDefault/oldDefault.php');

        } else {
            include_once(dirname(plugin_dir_path(__FILE__)) . '/themes/default/default.php');
        }
	}
	
	/**
	 * Wp Latest Posts Widget Image source Settings tab
	 *
	 */
	private function displayImageSourceTab() {
		global $post;
		$settings = get_post_meta( $post->ID, '_wpcufpn_settings', true );
		if( empty( $settings ) )
			$settings = $this->_field_defaults;

		if( isset($settings['thumb_img']) )
			$thumb_selected[$settings['thumb_img']] = ' selected="selected"';

		echo '<ul class="fields">';
		
		/** Thumbnail image src drop-down **/
		echo '<li class="field"><label for="thumb_img" class="coltab">' . __( 'Select Image', 'wpcufpn' ) . '</label>' .
			'<select id="thumb_img" name="wpcufpn_thumb_img">';
		foreach( $this->_thumb_img_values as $value=>$text ) {
			echo '<option value="' . $value . '" ' . (isset($thumb_selected[$value])?$thumb_selected[$value]:'') . '>';
			echo htmlspecialchars( __( $text, 'wpcufpn' ) );
			echo '</option>';
		}
		echo '</select></li>';

        /**
         * selected
         */
        $imageThumbSizeSelected     = '';
        $imageMediumSizeSelected    = '';
        $imageLargeSizeSelected     = '';

        /**
         * fix notice when update from old version
         */
        if ( ! isset($settings['image_size'])) {
            $settings['image_size'] = "";
        }

        if ($settings['image_size'] == "thumbnailSize")
        {
            $imageThumbSizeSelected = 'selected="selected"';

        } elseif ($settings['image_size'] == "mediumSize") {

            $imageMediumSizeSelected = 'selected="selected"';

        } elseif ($settings['image_size'] == "largeSize") {

            $imageLargeSizeSelected = 'selected="selected"';
        }
		/** image Size field **/
		echo '<li class="field"><label for="thumb_width" class="coltab">' . __( 'Image size', 'wpcufpn' ) . '</label>' .
				'<select id="wpcufpn_imageThumbSize" name="wpcufpn_image_size">
				<option  '.$imageThumbSizeSelected.' value="thumbnailSize" >' .__( 'Thumbnail', 'wpcufpn' ).'</option>

				<option  '.$imageMediumSizeSelected.' value="mediumSize" >' .__( 'Medium', 'wpcufpn' ).'</option>

                <option  '.$imageLargeSizeSelected.' value="largeSize" >' .__( 'Large', 'wpcufpn' ).'</option>

			</select></li>';
		
		do_action( 'wpcufpn_displayimagesource_crop_add_fields', $settings );
		
		/** Sliders **/
		// -block---------------------------------- //
		echo '<div id="margin_sliders" class="wpcu-inner-admin-block with-title with-border">';
		echo '<h4>Image margin</h4>';
		echo '<ul class="fields">';
		echo '<li class="field"><label for="margin_left" class="coltab">' . __( 'Margin left', 'wpcufpn' ) . '</label>' .
				'<span id="slider_margin_left" class="slider"></span>' .
				'<input id="margin_left" type="text" name="wpcufpn_margin_left" value="' . htmlspecialchars( isset($settings['margin_left'])?$settings['margin_left']:'' ) . '" class="short-text" /></li>';
		echo '<li class="field"><label for="margin_top" class="coltab">' . __( 'Margin top', 'wpcufpn' ) . '</label>' .
				'<span id="slider_margin_top" class="slider"></span>' .
				'<input id="margin_top" type="text" name="wpcufpn_margin_top" value="' . htmlspecialchars( isset($settings['margin_top'])?$settings['margin_top']:'' ) . '" class="short-text" /></li>';
		echo '<li class="field"><label for="margin_right" class="coltab">' . __( 'Margin right', 'wpcufpn' ) . '</label>' .
				'<span id="slider_margin_right" class="slider"></span>' .
				'<input id="margin_right" type="text" name="wpcufpn_margin_right" value="' . htmlspecialchars( isset($settings['margin_right'])?$settings['margin_right']:'' ) . '" class="short-text" /></li>';
		echo '<li class="field"><label for="margin_bottom" class="coltab">' . __( 'Margin bottom', 'wpcufpn' ) . '</label>' .
				'<span id="slider_margin_bottom" class="slider"></span>' .
				'<input id="margin_bottom" type="text" name="wpcufpn_margin_bottom" value="' . htmlspecialchars( isset($settings['margin_bottom'])?$settings['margin_bottom']:'' ) . '" class="short-text" /></li>';
		echo '</ul>';	//fields
		echo '</div>';	//wpcu-inner-admin-block
		// ---------------------------------------- //
		
		if( !class_exists('wpcuWPFnProPlugin') ) {
			echo '<p class="wpcu pro_reminder"><div class="wpcufpn_pro_reminder_row notice notice-success is-dismissible below-h2">' .
				__(
					'Additional advanced customization features are available with the optional ' .
					'<a href="http://www.joomunited.com/wordpress-products/wp-latest-posts" target="_blank" >pro add-on</a>.'
				) . 
			'</div></p>';
		} else {
			do_action( 'wpcufpn_imagesource_add_fields', $settings );
		}
	}
	
	/**
	 * Wp Latest Posts Widget Advanced Settings tab
	 *
	 */
	private function displayAdvancedTab() {
		global $post;
		$settings = get_post_meta( $post->ID, '_wpcufpn_settings', true );
		if( empty( $settings ) )
			$settings = $this->_field_defaults;
		
		echo '<ul class="fields">';
		
		echo '<li class="field"><label for="date_fmt" class="coltab">' . __( 'Date format', 'wpcufpn' ) . '</label>' .
			'<input id="date_fmt" type="text" name="wpcufpn_date_fmt" value="' . htmlspecialchars( isset($settings['date_fmt'])?$settings['date_fmt']:'' ) . '" class="short-text" />
			<a id="wpcufpn_dateFormat" target="_blank" href="http://php.net/manual/en/function.date.php"> ' . __( 'Date format', 'wpcufpn' ) . ' </a>
			</li>';
		
		echo '<li class="field"><label for="text_content" class="coltab">' . __( 'Text Content', 'wpcufpn' ) . '</label>' .
		    '<select name="wpcufpn_text_content" id="text_content">' .
			'<option value="0" ' . ((isset($settings['text_content']) && $settings['text_content']=="0")?"selected":'')  . ' class="short-text">Full content</option>' .
			'<option value="1" ' . ((isset($settings['text_content']) && $settings['text_content']=="1")?"selected":'')  . ' class="short-text">Excerpt content</option>' .
			'</select> </li>'; 
			 
		
		
		echo '</ul>';	//fields
		
		
		if( !class_exists('wpcuWPFnProPlugin') ) {
			echo '<div class="wpcufpn_pro_reminder_rows halfed notice notice-success is-dismissible below-h2">';
			echo '<p>' . __('Looking out for more <em>advanced</em> features?') . '</p>';
			echo '<p>' . __('&rarr;&nbsp;Check out our optional <a href="http://www.joomunited.com/wordpress-products/wp-latest-posts" target="_blank" >"Pro" add-on</a>.') . '</p>';
			echo '</div>';
		} else {
			do_action( 'wpcufpn_displayadvanced_add_fields', $settings );
		}
			
		echo '<hr/><div><label for="custom_css" class="coltab" style="vertical-align:top">' . __( 'Custom CSS', 'wpcufpn' ) . '</label>' .
			'<textarea id="custom_css" cols="100" rows="5" name="wpcufpn_custom_css">' . ( isset($settings['custom_css'])?$settings['custom_css']:'' ) . '</textarea></div>';

        if (isset($post->ID) && isset($post->post_title) && (!empty($post->post_title)))
        {
            echo '<hr/><div><label for="phpCodeInsert" class="coltab" style="margin:10px 0 5px">' . __( 'Copy & paste this code into a template file to display this WPLP block', 'wpcufpn' ) . '</label>' .
                '<br><textarea readonly id="phpCodeInsert" cols="100" rows="2" name="wpcufpn_phpCodeInsert">'.__( 'echo do_shortcode(\'[frontpage_news widget="'.$post->ID.'" name="'.$post->post_title.'"]\');' , "wpcufpn" ) . '</textarea></div>';
        }
	}
	
	/**
	 * Wp Latest Posts Widget About tab
	 *
	 */
	public function displayAboutTab() {
		
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui' );
		wp_enqueue_script( 'javascript', plugins_url( '/js/wpcufpn_about.js', dirname( __FILE__ ) ) , array('jquery'), '1.0.0', true );
		
		echo '<div class="about_content">';
		
		echo '<p> </p>';
		
		/** Support information **/
		
		if( !class_exists('wpcuWPFnProPlugin') ) {
			echo '<div id="promote">';
			echo '<h1>Get Pro version</h1>';
			//echo '<p>Compatible with optional "<a href="http://www.joomunited.com/wordpress-products/wp-latest-posts">add-on</a>" version 2.0.6</p>';
			echo '<p><em>Optional add-on is currently not installed or not enabled ' .
				'&rarr; <a href="http://www.joomunited.com/wordpress-products/wp-latest-posts">get it here !</a></em></p>';
			/** Marketing **/ 
			
			echo '<iframe src="//player.vimeo.com/video/77775570" width="485" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe> <p><a href="http://vimeo.com/77775570">';				
			
			
			echo '<table class="feature-listing">
				<tbody>
				<tr class="header-feature"><th class="feature col1"><strong></strong></th><th class="feature col2"><strong>FREE </strong></th><th class="feature col2"><strong>PRO </strong></th></tr>
				
				<tr class="ligne2">
				<td>
				<p>Private ticket support</p>
				</td>
				<td class="feature-text">
				<p style="text-align: center;"><img style="margin: 0px;" src="' . plugins_url( 'img/no.png', dirname( __FILE__ ) ) . '" alt="no" width="16" height="16"></p>
				</td>
				<td class="feature-text">
				<p style="text-align: center;"><img style="margin: 0px;" src="' . plugins_url( 'img/yes.png', dirname( __FILE__ ) ) . '" alt="yes" width="16" height="15"></p>
				</td>
				</tr>
				<tr class="ligne1">
				<td>
				<p>4 responsive premium themes</p>
				</td>
				<td class="feature-text">
				<p style="text-align: center;"><img style="margin: 0px;" src="' . plugins_url( 'img/no.png', dirname( __FILE__ ) ) . '" alt="no" width="16" height="16"></p>
				</td>
				<td class="feature-text">
				<p style="text-align: center;"><img style="margin: 0px;" src="' . plugins_url( 'img/yes.png', dirname( __FILE__ ) ) . '" alt="yes" width="16" height="15"></p>
				</td>
				</tr>
				<tr class="ligne2">
				<td>
				<p>Color chooser to fit your WordPress theme</p>
				</td>
				<td class="feature-text">
				<p style="text-align: center;"><img style="margin: 0px;" src="' . plugins_url( 'img/no.png', dirname( __FILE__ ) ) . '" alt="no" width="16" height="16"></p>
				</td>
				<td class="feature-text">
				<p style="text-align: center;"><img style="margin: 0px;" src="' . plugins_url( 'img/yes.png', dirname( __FILE__ ) ) . '" alt="yes" width="16" height="15"></p>
				</td>
				</tr>
				<tr class="ligne1">
				<td>
				<p>Load content from WordPress tags</p>
				</td>
				<td class="feature-text">
				<p style="text-align: center;"><img style="margin: 0px;" src="' . plugins_url( 'img/no.png', dirname( __FILE__ ) ) . '" alt="no" width="16" height="16"></p>
				</td>
				<td class="feature-text">
				<p style="text-align: center;"><img style="margin: 0px;" src="' . plugins_url( 'img/yes.png', dirname( __FILE__ ) ) . '" alt="yes" width="16" height="15"></p>
				</td>
				</tr>
				<tr>
				<td colspan="3"><br/>
				<i>And more...</i>
				<td>
				</tbody>
				</table><br/><br/>';
			
			
			echo '<div class="flexslider"><ul class="slides">';
				echo '<li><img src="' . plugins_url( 'img/gridtheme.png', dirname( __FILE__ ) ) . '" alt="JoomUnited Logo" /></li>';
				echo '<li><img src="' . plugins_url( 'img/categorygrid.png', dirname( __FILE__ ) ) . '" alt="JoomUnited Logo" /></li>';
				echo '<li><img src="' . plugins_url( 'img/smoothhover.png', dirname( __FILE__ ) ) . '" alt="JoomUnited Logo" /></li>';
				echo '<li><img src="' . plugins_url( 'img/timeline.png', dirname( __FILE__ ) ) . '" alt="JoomUnited Logo" /></li>';
			echo '</ul></div>';
			
			echo '<a href="http://www.joomunited.com/wordpress-products/wp-latest-posts" target="_blank" class="getthepro">
			Get the Pro version now !
			</a>';
			
			echo '</div>';	
		} else {
			do_action( 'wpcufpn_display_about', $this->version );
		}
		echo '<hr/><p>' . __('Initially released in october 2013 by <a href="http://www.joomunited.com/">JoomUnited</a>') . '</p>';
		echo '<p>WP Latest Posts WordPress plugin version ' . $this->version . '</p>';
		echo '<p>' . __('Author: ') . ' JoomUnited</p>';
		echo '<p>' . __('Your current version of WordPress is: ') . get_bloginfo('version') . '</p>';
		echo '<p>' . __('Your current version of PHP is: ') . phpversion() . '</p>';
		echo '<p>' . __('Your hosting provider\'s web server currently runs: ') . $_SERVER['SERVER_SOFTWARE'] . '</p>';
		echo '<p><em>' . __('Please specify all of the above information when contacting us for support.') . '</em></p>';
		
		echo '<p><a href="http://www.joomunited.com/wordpress-products/wp-latest-posts">WP Latest Posts official support site</a></p>';
		echo '<a href="http://www.joomunited.com/wordpress-products/wp-latest-posts">';
		echo '<img src="' . plugins_url( 'img/wpcu-logo.png', dirname( __FILE__ ) ) . '" alt="JoomUnited Logo" /></a>';
		echo '</div>';
	}
	
	/**
	 * Content source tab for post categories
	 * 
	 */
	private function displayContentSourceCategoryTab() {
		
		global $post;
        $source_cat_checked = array();
		$checked = array();
		$settings = get_post_meta( $post->ID, '_wpcufpn_settings', true );
		if( empty( $settings ) )
			$settings = $this->_field_defaults;
		
		if( !isset($settings['source_category']) || empty($settings['source_category']) || !$settings['source_category'] )
			$settings['source_category'] = array( '_all' );
		
		foreach( $settings['source_category'] as $cat ) {
			$source_cat_checked[$cat] = ' checked="checked"';
		};
		
		if( isset($settings['cat_post_source_order']) )
			$source_order_selected[$settings['cat_post_source_order']] = ' selected="selected"';
		if( isset($settings['cat_post_source_asc']) )
			$source_asc_selected[$settings['cat_post_source_asc']] = ' selected="selected"';
		
		echo '<ul class="fields">';
		
		echo '<li class="field">';
		echo '<ul>';
		echo '<li><input id="cat_all" type="checkbox" name="source_category[]" value="_all" ' . (isset($source_cat_checked['_all'])?$source_cat_checked['_all'] : '') . ' />' .
			'<label for="cat_all" class="post_cb">All</li>';
		$cats = get_categories();
		foreach( $cats as $cat ) {
			echo '<li><input id="ccb_' . $cat->term_id . '" type="checkbox" name="wpcufpn_source_category[]" value="' .
				$cat->term_id . '" ' . (isset($source_cat_checked[$cat->term_id])?$source_cat_checked[$cat->term_id]:"") . ' class="cat_cb" />';
			echo '<label for="ccb_' . $cat->term_id . '" class="post_cb">' . $cat->name . '</label></li>';
		}
		echo '</ul>';
		echo '</li>';	//field
		
		echo '<li class="field">';
		echo '<label for="cat_post_source_order" class="coltab">' . __( 'Order by', 'wpcufpn' ) . '</label>';
		echo '<select name="wpcufpn_cat_post_source_order" id="cat_post_source_order" >';
		echo '<option value="date" ' . (isset($source_order_selected['date'])?$source_order_selected['date']:"") . '>' . __( 'By date', 'wpcufpn' ) . '</option>';
		echo '<option value="title" ' . (isset($source_order_selected['title'])?$source_order_selected['title']:"") . '>' . __( 'By title', 'wpcufpn' ) . '</option>';
        echo '<option value="random" ' . (isset($source_order_selected['random'])?$source_order_selected['random']:"") . '>' . __( 'By random', 'wpcufpn' ) . '</option>';
		//echo '<option value="order" ' . $source_order_selected['order'] . '>' . __( 'By order', 'wpcufpn' ) . '</option>';
		echo '</select>';
		echo '</li>';	//field
		
		echo '<li class="field">';
		echo '<label for="cat_post_source_asc" class="coltab">' . __( 'Posts sort order', 'wpcufpn' ) . '</label>';
		echo '<select name="wpcufpn_cat_post_source_asc" id="cat_post_source_asc">';
		echo '<option value="asc" ' . (isset($source_asc_selected['asc'])?$source_asc_selected['asc']:"") . '>' . __( 'Ascending', 'wpcufpn' ) . '</option>';
		echo '<option value="desc" ' . (isset($source_asc_selected['desc'])?$source_asc_selected['desc']:"") . '>' . __( 'Descending', 'wpcufpn' ) . '</option>';
		echo '</select>';
		echo '</li>';	//field
		
		if( !class_exists('wpcuWPFnProPlugin') ) {
			echo '</ul><p class="wpcu pro_reminder"><div class="wpcufpn_pro_reminder_row notice notice-success is-dismissible below-h2" >' .
				__(
					'Additional content source options are available with the optional ' .
					'<a href="http://www.joomunited.com/wordpress-products/wp-latest-posts" target="_blank" >pro add-on</a>.'
				) .
			'</div></p><ul>';
		} else {
			do_action( 'wpcufpn_source_category_add_fields', $settings );
		}
		
		echo '</ul>';	//fields
	}
	
	/**
	 * Content source tab for pages
	 *
	 */
	private function displayContentSourcePageTab() {
		global $post;
		$checked = array();
		$settings = get_post_meta( $post->ID, '_wpcufpn_settings', true );
		if( empty( $settings ) )
			$settings = $this->_field_defaults;

		if(isset($settings['pg_source_order']))
			$source_order_selected[$settings['pg_source_order']] = ' selected="selected"';
		if(isset($settings['pg_source_asc']))
			$source_asc_selected[$settings['pg_source_asc']] = ' selected="selected"';
		
		echo '<ul class="fields">';
		
		echo '<li class="field">';
		echo '<ul>';
		
		
		
		if( !class_exists('wpcuWPFnProPlugin') ) {
			echo '<li><input id="pages_all" type="checkbox" name="wpcufpn_source_pages[]" value="_all" checked="checked"  disabled="disabled" />' .
				'<label for="pages_all" class="post_cb">All</li>';
			echo '<li><p class="wpcu pro_reminder"><div class="wpcufpn_pro_reminder_row notice notice-success is-dismissible below-h2">' .
				__(
						'Additional content source options are available with the optional ' .
						'<a href="http://www.joomunited.com/wordpress-products/wp-latest-posts" target="_blank" >pro add-on</a>.'
				) .
			'</div></p></li>';
		} else {
			do_action( 'wpcufpn_source_page_add_fields', $settings );
		}

		echo '</ul>';
		echo '</li>';	//field		
		
		echo '<li class="field">';
		echo '<label for="pg_source_order" class="coltab">' . __( 'Order by', 'wpcufpn' ) . '</label>';
		echo '<select name="wpcufpn_pg_source_order" id="pg_source_order" >';
		echo '<option value="order" ' . (isset($source_order_selected['order'])?$source_order_selected['order']:"") . '>' . __( 'By order', 'wpcufpn' ) . '</option>';
		echo '<option value="title" ' . (isset($source_order_selected['title'])?$source_order_selected['title']:"") . '>' . __( 'By title', 'wpcufpn' ) . '</option>';
		echo '<option value="date" ' . (isset($source_order_selected['date'])?$source_order_selected['date']:"") . '>' . __( 'By date', 'wpcufpn' ) . '</option>';
        echo '<option value="random" ' . (isset($source_order_selected['random'])?$source_order_selected['random']:"") . '>' . __( 'By random', 'wpcufpn' ) . '</option>';
		echo '</select>';
		echo '</li>';	//field
		
		echo '<li class="field">';
		echo '<label for="pg_source_asc" class="coltab">' . __( 'Pages sort order', 'wpcufpn' ) . '</label>';
		echo '<select name="wpcufpn_pg_source_asc" id="pg_source_asc">';
		echo '<option value="asc" ' . (isset($source_asc_selected['asc'])?$source_asc_selected['asc']:"") . '>' . __( 'Ascending', 'wpcufpn' ) . '</option>';
		echo '<option value="desc" ' . (isset($source_asc_selected['desc'])?$source_asc_selected['desc']:"") . '>' . __( 'Descending', 'wpcufpn' ) . '</option>';
		echo '</select>';
		echo '</li>';	//field
		
		echo '</ul>';	//fields
	}
	
	/**
	 * Builds the drop-down list of available themes
	 * for this plugin
	 * 
	 */
	function themeLister() {
        $found_themes = array();
		$theme_root = dirname( dirname( __FILE__ ) ) . '/themes';
		//echo 'theme dir: ' . $theme_root . '<br/>';	//Debug
		$dirs = @ scandir( $theme_root );
		foreach ( $dirs as $k=>$v ) {
			if( ! is_dir( $theme_root . '/' . $v ) || $v[0] == '.' || $v == 'CVS' ) {
				unset( $dirs[$k] );
			} else {
				$dirs[$k] = array(
					'path' => $theme_root . '/' . $v,
					'url' => plugins_url( 'themes/' . $v, dirname( __FILE__ ) )
				);
			}
		}
		
		/** Load Pro add-on themes **/
		$dirs = apply_filters( 'wpcufpn_themedirs', $dirs );
		
		if ( ! $dirs )
			return false;
		//var_dump( $dirs );	//Debug
		foreach ( $dirs as $dir ) {
			//echo 'dir: ' . $dir . '<br/>';	//debug
			if ( file_exists( $dir['path'] . '/style.css' ) ) {
				$headers = get_file_data( $dir['path'] . '/style.css', self::$file_headers, 'theme' );
				//var_dump( $headers );	//Debug
				$name = $headers['Name'];
				if( 'Default theme' == $name )
					$name = ' ' . $name;	// <- this makes it sort always first
				$found_themes[ $dir['path'] ] = array(
					'name'			=> $name,
					'dir'			=> basename( $dir['path'] ),
					'theme_file'	=> $dir['path'] . '/style.css',
					'theme_root'	=> $dir['path'],
					'theme_url'		=> $dir['url']
				);
			}
		}
		asort( $found_themes );
		return $found_themes;
	}
	
	/** 
	 * Customize Tiny MCE Editor 
	 * 
	 */
	public function setupTinyMce() {
		if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) ) {
			add_filter( 'mce_buttons', array( $this, 'filter_mce_button' ) );
			add_filter( 'mce_external_plugins', array( $this, 'filter_mce_plugin' ) );
			add_filter( 'mce_css', array( $this, 'plugin_mce_css' ) );
		}
	}
	public function filter_mce_button( $buttons ) {
		array_push( $buttons, '|', 'wpcufpn_button' );
		return $buttons;
	}
	public function filter_mce_plugin( $plugins ) {
		if( get_bloginfo('version') < 3.9 ) {
			$plugins['wpcufpn'] = plugins_url( 'js/wpcufpn_tmce_plugin.js', dirname( __FILE__ ) );
		} else {
			$plugins['wpcufpn'] = plugins_url( 'js/wpcufpn_tmce_plugin-3.9.js', dirname( __FILE__ ) );
		}
		return $plugins;
	}
	public function plugin_mce_css( $mce_css ) {
		if ( ! empty( $mce_css ) )
			$mce_css .= ',';
	
		$mce_css .= plugins_url( 'css/wpcufpn_tmce_plugin.css', dirname( __FILE__ ) );
	
		return $mce_css;
	}
	
	/**
	 * Add insert button above tinyMCE 4.0 (WP 3.9+)
	 * 
	 */
	public function editorButton() {
		$args = "";

		$args = wp_parse_args( $args, array(
			'text'      => __( 'Add Latest Posts', 'wpcufpn' ),
			'class'     => 'button',
			'echo'      => true
		) );

		/** Print button **/
		//$button = '<a href="javascript:void(0);" class="wpcufpn-button ' . $args['class'] . '" title="' . $args['text'] . '" data-target="' . $args['target'] . '" data-mfp-src="#su-generator" data-shortcode="' . (string) $args['shortcode'] . '">' . $args['icon'] . $args['text'] . '</a>';
		$button = '<a href="#TB_inline?height=150&width=150&inlineId=wpcufpn-popup-wrap&modal=true" ' .
			'class="wpcufpn-button thickbox ' . $args['class'] . '" ' .
			'title="' . $args['text'] . '">' .
			'<span style = "vertical-align: text-top" class="dashicons dashicons-admin-page"></span>' . $args['text'] .
		'</a>'
        ;
		
		/** Prepare insertion popup **/
		add_action( 'admin_footer', array( $this, 'insertPopup' ) );
		
		if ( $args['echo'] ) echo $button;
		return $button;
	}
	
	/**
	 * Prepare block insertion popup for admin editor with tinyMCE 4.0 (WP 3.9+)
	 * 
	 */
	public function insertPopup() {
		?>
		
		<div id="wpcufpn-popup-wrap" class="media-modal wp-core-ui" style="display:none">
			<a class="media-modal-close" href="#" onClick="javascript:tb_remove();" title="Close"><span class="media-modal-icon"></span></a>
			<div id="wpcufpn-select-content" class="media-modal-content">

				<div class="wpcufpn-frame-title" style="margin-left: 30px;"><h1><?php echo __( 'WP Latest Posts', 'wpcufpn' ); ?></h1></div>
			
				<div id="wpcufpn_widgetlist" style="margin:50px auto;">
				<?php if( $widgets = get_posts( array( 'post_type'=>self::CUSTOM_POST_NEWS_WIDGET_NAME, 'posts_per_page'=>-1 ) ) ) : ?>
					<select id="wpcufpn_widget_select">
					<option><?php echo __('Select which block to insert:', 'wpcufpn' ); ?></option>
					<?php foreach( $widgets as $widget ) : ?>
						<option value="<?php echo $widget->ID; ?>"><?php echo $widget->post_title; ?></option>
					<?php endforeach; ?>
					</select>
				<?php else : ?>
					<p><?php echo __( 'No Latest Posts Widget has been created.', 'wpcufpn' ); ?></p>
					<p><?php echo __( 'Please create one to use this button.', 'wpcufpn' ); ?></p>
				<?php endif; ?>
				</div>
				
				<script>
				(function($){
		        	$('#wpcufpn_widgetlist').on( 'change', function(e){
		            	//console.log( 'selected e: ' + $('option:selected', this).val() );	//Debug
		            	//console.log( e );													//Debug
		            	insertShortcode( $('option:selected', this).val(), $('option:selected', this).text() );
		            	$('#wpcufpn_widgetlist').find('option:first').attr('selected', 'selected');
		            	tb_remove();
		            });
				    function insertShortcode( widget_id, widget_title ) {
				    	var shortcode = '[frontpage_news';
				    	if( null != widget_id )
				    		shortcode += ' widget="' + widget_id + '"';
				    	if( null != widget_title )
				    		shortcode += ' name="' + widget_title + '"';
				    	shortcode += ']';
				    	
				    	/** Inserts the shortcode into the active editor and reloads display **/
//				    	var ed = tinyMCE.activeEditor;
//
//                            console.log("visual");
//                            ed.execCommand('mceInsertContent', 0, shortcode);
//                            setTimeout(function() { ed.hide(); }, 1);
//                            setTimeout(function() { ed.show(); }, 10);
//
                        wpcufpn_send_to_editor(shortcode);
				    }
                    
                    var wpActiveEditor, wpcufpn_send_to_editor;

                    wpcufpn_send_to_editor = function( html ) {
                        var editor,
                            hasTinymce = typeof tinymce !== 'undefined',
                            hasQuicktags = typeof QTags !== 'undefined';

                        if ( ! wpActiveEditor ) {
                            if ( hasTinymce && tinymce.activeEditor ) {
                                editor = tinymce.activeEditor;
                                wpActiveEditor = editor.id;
                            } else if ( ! hasQuicktags ) {
                                return false;
                            }
                        } else if ( hasTinymce ) {
                            editor = tinymce.get( wpActiveEditor );
                        }

                        if ( editor && ! editor.isHidden() ) {
                            editor.execCommand( 'mceInsertContent', 0, html );
                            setTimeout(function() { editor.hide(); }, 1);
                            setTimeout(function() { editor.show(); }, 10);

                        } else if ( hasQuicktags ) {
                            QTags.insertContent( html );
                        } else {
                            document.getElementById( wpActiveEditor ).value += html;
                        }

                        // If the old thickbox remove function exists, call it
                        if ( window.tb_remove ) {
                            try { window.tb_remove(); } catch( e ) {}
                        }
                    };
				})( jQuery );
				</script>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Adds a js script to the post and page editor screen footer
	 * to configure our tinyMCE extension
	 * with the list of available widgets
	 * 
	 */
	public function editorFooterScript() {
		//TODO: return false if not page/post edit screen
		
		echo '<script>';
		echo "var wpcufpn_widgets = new Array();\n";
		$widgets = get_posts( array( 'post_type'=>self::CUSTOM_POST_NEWS_WIDGET_NAME, 'posts_per_page'=>-1 ) );
		foreach( $widgets as $widget )
			echo "wpcufpn_widgets['$widget->ID']='$widget->post_title';\n";
		echo '</script>';
	}
	
	
	/**
	 * Add Style and script in head and footer
	 * 
	 */
	public function prefixEnqueue ($posts) {
		if ( empty($posts) || is_admin() )
			return $posts;
		$pattern = get_shortcode_regex();

	
		foreach ($posts as $post) {
			preg_match_all('/'.$pattern.'/s', $post->post_content, $matches);
			$widgetIDArray=array();
			$trig=false;
			foreach ($matches as $matchtest){
				if (is_array($matchtest)){
					foreach ($matchtest as $matchtestsub){
						preg_match_all('/widget="(.*?)"/s', $matchtestsub, $widgetIDarray);
                        //print_r($widgetIDarray); die();
						foreach ($widgetIDarray as $widgetID) {
								if (!empty($widgetID)){
									if (is_array($widgetID)){
										foreach ($widgetID as $widgetIDunique) {
											if(is_numeric($widgetIDunique) && !in_array($widgetIDunique, $widgetIDArray, true)){
												$widgetIDArray[]=$widgetIDunique;
											}											
										}
									}else {
										if(is_numeric($widgetIDunique) && !in_array($widgetIDunique, $widgetIDArray, true)){
											$widgetIDArray[]=$widgetIDunique;
										}	
									}
								}
						}						
					}
				}
				else {
					preg_match_all('/widget="(.*?)"/s', $matchtestsub, $widgetIDarray);
					foreach ($widgetIDarray as $widgetID) {
								if (!empty($widgetID)){
									if (is_array($widgetID)){
										foreach ($widgetID as $widgetIDunique) {
											if(is_numeric($widgetIDunique) && !in_array($widgetIDunique, $widgetIDArray, true)){
												$widgetIDArray[]=$widgetIDunique;
											}											
										}
									}else {
										if(is_numeric($widgetIDunique) && !in_array($widgetIDunique, $widgetIDArray, true)){
											$widgetIDArray[]=$widgetIDunique;
										}	
									}
								}
						}	
				}
				
			}
		
			/*
			foreach ($matches[2] as $matche => $matchkey) {
				if ($matchkey == 'frontpage_news') {
					$widgetIDArray[]=$matche;
				}					
			}
			*/
			foreach ($widgetIDArray as $widgetIDitem) {
					$widget = get_post( $widgetIDitem );
                    if ( isset($widget) && !empty($widget) ) {
                        $widget->settings = get_post_meta( $widget->ID, '_wpcufpn_settings', true );
                        $front = new wpcuFPN_Front( $widget );
                        add_action( 'wp_print_styles',array($front,"loadThemeStyle"));
                        add_action('wp_head',array( $front, 'customCSS' ));
                        add_action( 'wp_print_scripts',array($front,"loadThemeScript"));
                    }
			}
			
				/*
				if (is_array($matche) && $matche[2] == 'frontpage_news') {
					echo "<pre>";
					print_r($matche);
					echo "</pre>";				
					preg_match('/widget="(.*?)"/s', $matche[3], $widgetID);
					$widget = get_post( $widgetID[1] );
					$widget->settings = get_post_meta( $widget->ID, '_wpcufpn_settings', true );
					$front = new wpcuFPN_Front( $widget );
					//$front->loadThemeStyle();
					add_action( 'wp_print_styles',array($front,"loadThemeStyle"));
					add_action('wp_head',array( $front, 'customCSS' ));
					add_action( 'wp_print_scripts',array($front,"loadThemeScript")); 					
				}	*/
			 
			
		}
		return $posts;
	}
	
	/**
	 * Returns content of our shortcode
	 * 
	 */
	public function applyShortcode( $args = array() ) {

		$html = '';
		
            $widget_id = $args['widget'];
			$widget = get_post( $widget_id );
			if( isset($widget) &&  ! empty( $widget ) ) {
                $widget->settings = get_post_meta( $widget->ID, '_wpcufpn_settings', true );
				$front = new wpcuFPN_Front( $widget );
				$front->loadThemeStyle();
                $front->loadThemeScript();
				$html .= $front->display( false );
			} else {
				$html .= "\n<!-- WPFN: this News Widget is not initialized -->\n";
			}

		return $html;
	}
	
	/**
	 * Sets up the settings page in the WP back-office
	 *
	 */
	private function display_page() {
	
		include( 'back-office-display.inc.php' );
	
	}
	
	public function addProLink( $links, $file ) {
		$base = plugin_basename( $this->plugin_file );
		if ( $file == $base ) {
			$links[] = '<a href="http://www.joomunited.com/wordpress-products/wp-latest-posts">'
				. __('Get "pro" add-on') . '</a>';
			$links[] = '<a href="http://www.joomunited.com/wordpress-products/wp-latest-posts">'
				. __('Support') . '</a>';
		}
		return $links;
	}
	
	// \/------------------------------------------ STANDARD ------------------------------------------\/
		
	/**
	 * overloaded
	 * Displays a standard plugin settings page in the Settings menu of the WordPress administration interface
	 *
	 * @see trunk/inc/YD_Plugin#plugin_options()
	 */
	public function plugin_options() {
		
		/** reserved to contributors **/
		if ( !current_user_can( 'edit_posts' ) )  {	
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		if( class_exists( 'ydfgOP' ) ) {
			$op = new ydfgOP( $this );
		} else {
			$op = new YD_OptionPage( $this );
		}
		if ( $this->option_page_title ) {
			$op->title = $this->option_page_title;
		} else {
			$op->title = __( $this->plugin_name, $this->tdomain );
		}
		$op->sanitized_name = $this->sanitized_name;
		$op->yd_logo = '';
		$op->support_url = $this->support_url;
		$op->initial_funding = $this->initial_funding; 			// array( name, url )
		$op->additional_funding = $this->additional_funding;	// array of arrays
		$op->version = $this->version;
		$op->translations = $this->translations;
		$op->plugin_dir = $this->plugin_dir;
		$op->has_cache = $this->has_cache;
		$op->option_page_text = $this->option_page_text;
		$op->plg_tdomain = $this->tdomain;
		$op->donate_block = $this->op_donate_block;
		$op->credit_block = $this->op_credit_block;
		$op->support_block = $this->op_support_block;
		$this->option_field_labels['disable_backlink'] = 'Disable backlink in the blog footer:';
		$op->option_field_labels = $this->option_field_labels;
		$op->form_add_actions = $this->form_add_actions;
		$op->form_method =  $this->form_method;
		if( $_GET['do'] || $_POST['do'] ) $op->do_action( $this );
		$op->header();
		if( class_exists( 'ydfgOP' ) ) {
			$op->styles();
		}
		$op->option_values = get_option( $this->option_key );

		$this->display_page();
		
		if( $this->has_cron ) $op->cron_status( $this->crontab );
	}
}
?>