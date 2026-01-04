<?php
/**
 * Whatever functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Whatever
 */

if ( ! defined( 'WTVR_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( 'WTVR_VERSION', '0.3.5' );
}
$micro_time = microtime(true);

if ( ! function_exists( 'wtvr_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function wtvr_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on Whatever, use a find and replace
		 * to change 'wtvr' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'wtvr', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus( [
			'main-menu' => esc_html__( 'Primary', 'wtvr' ),
		] );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			[
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			]
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );
	}
endif;
add_action( 'after_setup_theme', 'wtvr_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function wtvr_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'wtvr_content_width', 688 );
}
add_action( 'after_setup_theme', 'wtvr_content_width', 0 );


/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function wtvr_widgets_init() {
	register_sidebar(
		[
			'name'          => esc_html__( 'Sidebar', 'wtvr' ),
			'id'            => 'sidebar',
			'description'   => esc_html__( 'Add widgets here.', 'wtvr' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		]
	);
}
add_action( 'widgets_init', 'wtvr_widgets_init' );

/**
 * Enqueue backend scripts and styles
 */
function oege_enqueue_back(){

	//wp_enqueue_script('oege-backscript', get_template_directory_uri() . '/js/back.js', [] , null, true );
	wp_enqueue_style( 'wtvr-backstyle', get_template_directory_uri() . '/back.css');
	//wp_enqueue_style( 'wtvr-bothstyle', get_template_directory_uri() . '/both.css');

}
add_action( 'admin_enqueue_scripts', 'oege_enqueue_back' );
/**
 * Enqueue frontend scripts and styles
 */
function wtvr_scripts() {

	// Google fonts using webfontloader https://github.com/typekit/webfontloader
	wp_enqueue_script( 'wtvr-google-webfonts', 'https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js' );

	// Slick slider https://kenwheeler.github.io/slick/
	wp_enqueue_script( 'wtvr-slick-script', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', [] , null, true);
	// TODO: create own styles
	wp_enqueue_style( 'wtvr-slick-style', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css');
	wp_enqueue_style( 'wtvr-slick-theme', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css');

	// Infinite scroll https://infinite-scroll.com/
	//wp_enqueue_script( 'wtvr-infinite-scroll', 'https://unpkg.com/infinite-scroll@4/dist/infinite-scroll.pkgd.js', [] , null, true);

	// Scroll events http://scrollmagic.io/
	wp_enqueue_script( 'wtvr-scrollmagic', 'https://cdnjs.cloudflare.com/ajax/libs/ScrollMagic/2.0.7/ScrollMagic.min.js' );
	//TODO: remove scrollmagic debug
	//wp_enqueue_script( 'wtvr-scrollmagic-debug', 'https://cdnjs.cloudflare.com/ajax/libs/ScrollMagic/2.0.7/plugins/debug.addIndicators.min.js' );


	// Lazy load responsive images https://github.com/aFarkas/lazysizes
	wp_enqueue_script( 'wtvr-ls-unveilhooks', get_template_directory_uri() . '/js/ls/ls.unveilhooks.min.js', [] , null, true );
	wp_enqueue_script( 'wtvr-ls-parent-fit', get_template_directory_uri() . '/js/ls/ls.parent-fit.min.js', [] , null, true );
	wp_enqueue_script( 'wtvr-ls-object-fit', get_template_directory_uri() . '/js/ls/ls.object-fit.min.js', [] , null, true );
	wp_enqueue_script( 'wtvr-ls-respimg', get_template_directory_uri() . '/js/ls/ls.respimg.min.js', [] , null, true );
	wp_enqueue_script( 'wtvr-ls-bgset', get_template_directory_uri() . '/js/ls/ls.bgset.min.js', [] , null, true );
	wp_enqueue_script( 'wtvr-ls', get_template_directory_uri() . '/js/ls/lazysizes.min.js', [] , null, true );


	// http://photoswipe.com
//	wp_enqueue_style( 'wtvr-photoswipe', get_template_directory_uri() . '/lib/photoswipe/photoswipe.css' );
//	wp_enqueue_style( 'wtvr-photoswipe-skin', get_template_directory_uri() . '/lib/photoswipe/default-skin/default-skin.css' );
//	wp_enqueue_script( 'wtvr-photoswipe-script', get_template_directory_uri() . '/lib/photoswipe/photoswipe.min.js', [], null, true );
//	wp_enqueue_script( 'wtvr-photoswipe-ui', get_template_directory_uri() . '/lib/photoswipe/photoswipe-ui-default.min.js', [], null, true );



	// Icons
	wp_enqueue_style( 'wtvr-icons', get_template_directory_uri() . '/lib/icons/styles.css' , [], WTVR_VERSION );

	// Main style
	wp_enqueue_style( 'wtvr-style', get_stylesheet_uri(), [], WTVR_VERSION );

	// Correction styles
	wp_enqueue_style( 'wtvr-corr', get_template_directory_uri() . '/correction-styles.css' , [], WTVR_VERSION );


	// Global variables and functions
	wp_enqueue_script( 'wtvr-global', get_template_directory_uri() . '/js/global.js', ['jquery'], WTVR_VERSION, true );
	// Typography
	wp_enqueue_script( 'wtvr-typography', get_template_directory_uri() . '/js/typography.js', [], WTVR_VERSION, true );
	// Navigation
	wp_enqueue_script( 'wtvr-navigation', get_template_directory_uri() . '/js/navigation.js', ['jquery'], WTVR_VERSION, true );
	// Media
	wp_enqueue_script( 'wtvr-media', get_template_directory_uri() . '/js/media.js', ['jquery'], WTVR_VERSION, true );
	// Ajax
	//wp_enqueue_script( 'wtvr-ajax', get_template_directory_uri() . '/js/ajax.js', ['jquery'] , WTVR_VERSION, true );

	// Main script
	wp_enqueue_script( 'wtvr-main', get_template_directory_uri() . '/js/main.js', ['jquery'] , WTVR_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'wtvr_scripts' );


/*
 * Remove unused scripts
 *
 */
if (!is_user_logged_in()) {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );

	add_action( 'wp_print_styles', 'wtvr_deregister_guest_styles', 100 );

	function wtvr_deregister_guest_styles() {
		wp_deregister_style( 'dashicons' );
	}
}

function itcoop_bsearch_posts_orderby( $orderby, $query ) {
	global $wpdb;
	if ( $query->is_search() ) {
	  $query->set('orderby', false);
	  return false;
	}
	return $orderby;
  }
  add_filter( 'posts_orderby', 'itcoop_bsearch_posts_orderby', 9, 2 );


/**
 * TGM plugin activation
 *
 * @link http://tgmpluginactivation.com
 */
require 'inc/plugins.php';

/**
 * Custom post types
 */
require 'inc/post-types.php';

/**
 * Custom taxonomies
 */
require 'inc/taxonomies.php';

/**
 * Class autoloader
 */
require 'inc/autoloader.php';

/**
 * Classes functions
 */
require 'inc/classes-functions.php';

/**
 * Functions which enhance the theme by hooking into WordPress
 */
require 'inc/template-functions.php';

/**
 * Term functions
 */
require 'inc/terms-functions.php';

/**
 * Custom queries
 */
require 'inc/queries.php';

/**
 * Custom template tags
 */
require 'inc/template-tags.php';

/**
 * ACF Field related functions
 */
require 'inc/acf-functions.php';


/**
 * Theme template actions
 */
require 'inc/template-actions.php';

/**
 * Customizer additions
 */
require 'inc/customizer.php';

/**
 * ACF Option Pages
 */
require 'inc/acf-option-pages.php';

/**
 * Custom inline styles
 */
require 'inc/custom-styles.php';

/**
 * Custom menus
 */
require 'inc/menus.php';

/**
 * WP tweaks
 */
require 'inc/wp-tweaks.php';

/**
 * Custom fields
 */
require 'inc/custom-fields/_custom-fields.php';
/**
 * ALT texts featured image
 */
require_once get_template_directory() . '/inc/media.php';

/**
 * SVG
 */
require 'inc/svg.php';

//ADMIN INTERFACE
if ( is_admin() ) {
	/**
    * Custom columns
	 */
	require 'inc/admin-interface/custom-columns.php';
}
