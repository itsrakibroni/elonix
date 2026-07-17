<?php
/**
 * Elonix – Toolkit for Elementor Elementor Widget Registry
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Widget_Registry {

	/**
	 * Instance holder.
	 *
	 * @var Elonix_Toolkit_Widget_Registry|null
	 */
	private static $_instance = null;

	/**
	 * Registered widgets metadata array.
	 *
	 * @var array
	 */
	private $widgets = array();

	/**
	 * Get class instance.
	 *
	 * @return Elonix_Toolkit_Widget_Registry Instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Load default sample entries for demonstration.
		// These can be safely overridden, disabled, or removed dynamically later.
		$this->register_sample_widgets();
	}

	/**
	 * Register sample placeholder widgets for demonstration.
	 */
	private function register_sample_widgets() {
		$samples = array(
			'es-heading'               => array(
				'title'       => 'Heading',
				'description' => 'A highly customizable heading widget with title, subtitle, and description controls.',
				'icon'        => 'eicon-heading',
				'category'    => 'elonix-widgets',
				'path'        => 'widgets/advanced-heading/class-widget.php',
				'class'       => 'Elonix_Toolkit_Heading_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'heading', 'title', 'subtitle', 'description' ),
			),
			'es-button'                => array(
				'title'       => 'Button',
				'description' => 'A highly customizable standalone action button widget with icons, styling and hover configurations.',
				'icon'        => 'eicon-button',
				'category'    => 'elonix-widgets',
				'path'        => 'widgets/advanced-button/class-widget.php',
				'class'       => 'Elonix_Toolkit_Button_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'button', 'link', 'cta' ),
			),
			'es-accordion'             => array(
				'title'       => 'Accordion',
				'description' => 'A highly customizable, responsive, and accessible WAI-ARIA accordion widget with FAQ schema support.',
				'icon'        => 'eicon-accordion',
				'category'    => 'elonix-widgets',
				'path'        => 'widgets/accordion/class-widget.php',
				'class'       => 'Elonix_Toolkit_Accordion_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'accordion', 'toggle', 'faq', 'schema' ),
			),
			'es-image'                 => array(
				'title'       => 'Image',
				'description' => 'A highly customizable image widget with object fit options, hover transition effects, CSS filters, and responsive sizes.',
				'icon'        => 'eicon-image',
				'category'    => 'elonix-widgets',
				'path'        => 'widgets/advanced-image/class-widget.php',
				'class'       => 'Elonix_Toolkit_Image_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'image', 'photo', 'media' ),
			),
			'es-icon-box'              => array(
				'title'       => 'Icon Box',
				'description' => 'A highly customizable icon box layout widget combining icons, titles, descriptions, and buttons inside a modular style card.',
				'icon'        => 'eicon-icon-box',
				'category'    => 'elonix-widgets',
				'path'        => 'widgets/advanced-icon-box/class-widget.php',
				'class'       => 'Elonix_Toolkit_Icon_Box_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'icon', 'box', 'card', 'feature' ),
			),
			'es-feature-cards'         => array(
				'title'       => 'Feature Cards',
				'description' => 'A highly customizable premium feature cards widget.',
				'icon'        => 'eicon-gallery-grid',
				'category'    => 'elonix-widgets',
				'path'        => 'widgets/feature-cards/class-widget.php',
				'class'       => 'Elonix_Toolkit_Feature_Cards_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'card', 'feature', 'grid', 'carousel' ),
			),
			'es-breadcrumb'            => array(
				'title'       => 'Breadcrumb',
				'description' => 'A highly customizable breadcrumb widget with schema support, WooCommerce compatibility, and SEO plugins integration.',
				'icon'        => 'eicon-navigation-horizontal',
				'category'    => 'elonix-theme-builder',
				'path'        => 'widgets/advanced-breadcrumb/class-widget.php',
				'class'       => 'Elonix_Toolkit_Breadcrumb_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'breadcrumb', 'navigation', 'seo', 'yoast', 'rankmath', 'woocommerce' ),
			),
			'es-post-title'            => array(
				'title'       => 'Post Title',
				'description' => 'A highly customizable post title widget with dynamic tags, SEO heading hierarchy warnings, and title decoration/effects.',
				'icon'        => 'eicon-post-title',
				'category'    => 'elonix-theme-builder',
				'path'        => 'widgets/advanced-post-title/class-widget.php',
				'class'       => 'Elonix_Toolkit_Post_Title_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'post', 'title', 'heading', 'seo', 'dynamic' ),
			),
			'es-nav-menu'              => array(
				'title'       => 'Nav Menu',
				'description' => 'A premium, highly customizable navigation menu widget with responsive layout systems and WAI-ARIA compliance.',
				'icon'        => 'eicon-nav-menu',
				'category'    => 'elonix-theme-builder',
				'path'        => 'widgets/nav-menu/class-widget.php',
				'class'       => 'Elonix_Toolkit_Nav_Menu_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'navigation', 'menu', 'navbar', 'header', 'eskit' ),
			),
			'es-social-icons'          => array(
				'title'       => 'Social Icons',
				'description' => 'A highly customizable, responsive, and accessible social icons and links widget for Elementor.',
				'icon'        => 'eicon-social-icons',
				'category'    => 'elonix-widgets',
				'path'        => 'widgets/social-icons/class-widget.php',
				'class'       => 'Elonix_Toolkit_Social_Icons_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'social', 'icons', 'links', 'eskit' ),
			),
			'es-error-code'            => array(
				'title'       => 'Error Code',
				'description' => 'Dynamic error code widget displaying current status code (404, 410, etc.) with styling and effects.',
				'icon'        => 'eicon-number-field',
				'category'    => 'elonix-widgets',
				'path'        => 'widgets/error-code/class-widget.php',
				'class'       => 'Elonix_Toolkit_Error_Code_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'error', 'code', 'number', '404', '410' ),
			),
			'es-search'                => array(
				'title'       => 'Search',
				'description' => 'A highly customizable, responsive, and accessible live AJAX search widget for Elementor.',
				'icon'        => 'eicon-search',
				'category'    => 'elonix-widgets',
				'path'        => 'widgets/es-search/class-widget.php',
				'class'       => 'Elonix_Toolkit_Search_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'search', 'find', 'ajax', 'live', 'filter', 'eskit' ),
			),
			'es-search-results'        => array(
				'title'       => 'Search Results',
				'description' => 'A premium contextual WordPress search results widget for Search Builder templates and Elementor pages.',
				'icon'        => 'eicon-archive-posts',
				'category'    => 'elonix-archive',
				'path'        => 'widgets/es-search-results/class-widget.php',
				'class'       => 'Elonix_Toolkit_Search_Results_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'search', 'results', 'query', 'posts', 'archive', 'ajax', 'eskit' ),
			),
			'es-categories'            => array(
				'title'       => 'Categories',
				'description' => 'A premium categories and taxonomy grid list widget for Elementor.',
				'icon'        => 'eicon-folder-o',
				'category'    => 'elonix-archive',
				'path'        => 'widgets/es-categories/class-widget.php',
				'class'       => 'Elonix_Toolkit_Categories_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'categories', 'terms', 'taxonomies', 'grid', 'carousel', 'eskit' ),
			),
			'es-post-list'             => array(
				'title'       => 'Post List',
				'description' => 'A premium highly customizable posts CPT and WooCommerce products list/grid widget for Elementor.',
				'icon'        => 'eicon-bullet-list',
				'category'    => 'elonix-archive',
				'path'        => 'widgets/es-post-list/class-widget.php',
				'class'       => 'Elonix_Toolkit_Post_List_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'posts', 'list', 'grid', 'woo', 'cpt', 'blog', 'eskit' ),
			),
			'es-tag-cloud'             => array(
				'title'       => 'Smart Taxonomy Filter',
				'description' => 'A premium highly customizable terms and taxonomy list filter widget for Elementor.',
				'icon'        => 'eicon-tags',
				'category'    => 'elonix-archive',
				'path'        => 'widgets/es-tag-cloud/class-widget.php',
				'class'       => 'Elonix_Toolkit_Tag_Cloud_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'tag', 'cloud', 'category', 'taxonomy', 'filter', 'ajax', 'eskit' ),
			),
			'es-post-block'            => array(
				'title'       => 'Post Block Grid',
				'description' => 'A premium ThemeForest-grade magazine, blog, and news block grid widget for Elementor.',
				'icon'        => 'eicon-post-list',
				'category'    => 'elonix-archive',
				'path'        => 'widgets/es-post-block/class-widget.php',
				'class'       => 'Elonix_Toolkit_Post_Block_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'posts', 'block', 'magazine', 'news', 'grid', 'ajax', 'eskit' ),
			),
			'es-back-to-top'           => array(
				'title'       => 'Back To Top',
				'description' => 'A highly advanced and accessible back to top widget with progress indicators and reading trackers.',
				'icon'        => 'eicon-arrow-up',
				'category'    => 'elonix-widgets',
				'path'        => 'widgets/es-back-to-top/class-widget.php',
				'class'       => 'Elonix_Toolkit_Back_To_Top_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'back', 'top', 'scroll', 'progress', 'reading', 'time', 'eskit' ),
			),
			'es-smart-contact-actions' => array(
				'title'       => 'Smart Contact Actions',
				'description' => 'A highly advanced and accessible floating contact & actions widget with business hours and analytics.',
				'icon'        => 'eicon-comments',
				'category'    => 'elonix-widgets',
				'path'        => 'widgets/es-smart-contact-actions/class-widget.php',
				'class'       => 'Elonix_Toolkit_Smart_Contact_Actions_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'contact', 'actions', 'whatsapp', 'messenger', 'email', 'call', 'telegram', 'eskit' ),
			),
			'es-featured-image'        => array(
				'title'       => 'Featured Image',
				'description' => 'A premium dynamic featured image widget with aspect ratios, fallback images, hover animations, and full accessibility support.',
				'icon'        => 'eicon-featured-image',
				'category'    => 'elonix-theme-builder',
				'path'        => 'widgets/es-featured-image/class-widget.php',
				'class'       => 'Elonix_Toolkit_Featured_Image_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'featured', 'image', 'thumbnail', 'post', 'page', 'archive', 'dynamic', 'eskit' ),
			),
			'es-post-content'          => array(
				'title'       => 'Post Content',
				'description' => 'A dynamic read-only widget that renders the current post\'s native WordPress content exactly as intended.',
				'icon'        => 'eicon-post-content',
				'category'    => 'elonix-theme-builder',
				'path'        => 'widgets/es-post-content/class-widget.php',
				'class'       => 'Elonix_Toolkit_Post_Content_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'post', 'content', 'the_content', 'dynamic', 'eskit', 'single' ),
			),
			'es-post-info'             => array(
				'title'       => 'Post Info',
				'description' => 'A premium dynamic post meta info widget with drag-and-drop element order builder, icons, reading time calculator, and advanced styling.',
				'icon'        => 'eicon-post-info',
				'category'    => 'elonix-theme-builder',
				'path'        => 'widgets/es-post-info/class-widget.php',
				'class'       => 'Elonix_Toolkit_Post_Info_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'post', 'info', 'meta', 'author', 'date', 'category', 'tag', 'comments', 'reading', 'views', 'custom', 'eskit' ),
			),
			'es-post-terms'            => array(
				'title'       => 'Post Terms',
				'description' => 'A premium dynamic post terms taxonomy widget with auto-detection, badge/pill styles, custom separators, and remaining count indicators.',
				'icon'        => 'eicon-tags',
				'category'    => 'elonix-theme-builder',
				'path'        => 'widgets/es-post-terms/class-widget.php',
				'class'       => 'Elonix_Toolkit_Post_Terms_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'post', 'terms', 'taxonomy', 'category', 'tag', 'custom', 'badge', 'pill', 'eskit' ),
			),
			'es-post-share'            => array(
				'title'       => 'Post Share',
				'description' => 'A premium dynamic post sharing widget supporting 12+ networks, centered popup window management, copy link to clipboard with toast notification, and advanced styling.',
				'icon'        => 'eicon-share',
				'category'    => 'elonix-theme-builder',
				'path'        => 'widgets/es-post-share/class-widget.php',
				'class'       => 'Elonix_Toolkit_Post_Share_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'post', 'share', 'social', 'network', 'dynamic', 'eskit' ),
			),
			'es-post-comments'         => array(
				'title'       => 'Post Comments',
				'description' => 'A premium dynamic post comments widget with advanced styling, custom css, and form controls.',
				'icon'        => 'eicon-comments',
				'category'    => 'elonix-theme-builder',
				'path'        => 'widgets/es-post-comments/class-widget.php',
				'class'       => 'Elonix_Toolkit_Post_Comments_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'post', 'comments', 'form', 'reply', 'review', 'eskit' ),
			),
			'es-post-excerpt'          => array(
				'title'       => 'Post Excerpt',
				'description' => 'A premium dynamic post excerpt widget with fallback support, trimming options, and read more links.',
				'icon'        => 'eicon-post-excerpt',
				'category'    => 'elonix-theme-builder',
				'path'        => 'widgets/es-post-excerpt/class-widget.php',
				'class'       => 'Elonix_Toolkit_Post_Excerpt_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'post', 'excerpt', 'summary', 'read more', 'dynamic', 'eskit' ),
			),
			'es-testimonial'           => array(
				'title'       => 'Testimonial',
				'description' => 'A premium highly customizable testimonial card widget with grid and carousel layouts.',
				'icon'        => 'eicon-testimonial',
				'category'    => 'elonix-widgets',
				'path'        => 'widgets/es-testimonial/class-widget.php',
				'class'       => 'Elonix_Toolkit_Testimonial_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'testimonial', 'review', 'quote', 'carousel', 'grid', 'eskit' ),
			),
			'es-brand'                 => array(
				'title'       => 'Brand',
				'description' => 'A premium highly customizable brand and logo carousel/grid widget.',
				'icon'        => 'eicon-slider-push',
				'category'    => 'elonix-widgets',
				'path'        => 'widgets/es-brand/class-widget.php',
				'class'       => 'Elonix_Toolkit_Brand_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'brand', 'logo', 'carousel', 'grid', 'client', 'partner', 'eskit' ),
			),
			'es-marquee'               => array(
				'title'       => 'Marquee',
				'description' => 'A premium highly customizable marquee and ticker widget.',
				'icon'        => 'eicon-exchange',
				'category'    => 'elonix-widgets',
				'path'        => 'widgets/marquee/class-widget.php',
				'class'       => 'Elonix_Toolkit_Marquee_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'marquee', 'ticker', 'scroll', 'news', 'eskit' ),
			),
			'es-gallery'               => array(
				'title'       => 'Gallery',
				'description' => 'A flagship premium gallery widget with grid, masonry, justified, metro, and carousel layouts.',
				'icon'        => 'eicon-gallery-grid',
				'category'    => 'elonix-widgets',
				'path'        => 'widgets/es-gallery/class-widget.php',
				'class'       => 'Elonix_Toolkit_Gallery_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'gallery', 'image', 'photo', 'portfolio', 'grid', 'masonry', 'carousel', 'eskit' ),
			),
			'es-progress'              => array(
				'title'       => 'Progress Bar',
				'description' => 'A highly advanced progress bar widget with circle, linear, multi-segment, and step progress styles.',
				'icon'        => 'eicon-skill-bar',
				'category'    => 'elonix-widgets',
				'path'        => 'widgets/es-progress/class-widget.php',
				'class'       => 'Elonix_Toolkit_Progress_Bar_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'progress', 'bar', 'skill', 'circle', 'step', 'eskit' ),
			),
			'es-toggle'               => array(
				'title'       => 'Toggle',
				'description' => 'A generic Elementor target toggle widget.',
				'icon'        => 'eicon-exchange',
				'category'    => 'elonix-widgets',
				'path'        => 'widgets/es-toggle/class-widget.php',
				'class'       => 'Elonix_Toolkit_Toggle_Widget',
				'version'     => '1.0.0',
				'keywords'    => array( 'toggle', 'switcher', 'visibility', 'tabs', 'eskit' ),
			),
		);

		foreach ( $samples as $slug => $args ) {
			$this->register( $slug, $args );
		}
	}

	/**
	 * Register a widget dynamically.
	 *
	 * @param string $slug Widget identifier slug.
	 * @param array  $args Widget metadata configurations.
	 * @return bool True if successfully registered, false otherwise.
	 */
	public function register( $slug, $args ) {
		// Security check: validate widget slug ID format
		if ( ! preg_match( '/^[a-z0-9\-_]+$/i', $slug ) ) {
			return false;
		}

		// Ensure mandatory args are present and sanitized
		$defaults = array(
			'id'          => $slug,
			'title'       => '',
			'description' => '',
			'icon'        => 'eicon-code',
			'category'    => 'elonix-widgets',
			'keywords'    => array( 'elonix', 'toolkit' ),
			'path'        => '',
			'class'       => '',
			'version'     => '1.0.0',
			'type'        => 'free', // Extensibility: 'free', 'pro', 'woocommerce', 'dynamic', 'builder'
		);

		$parsed_args = wp_parse_args( $args, $defaults );

		// Sanitize title, description and all parameters
		$parsed_args['title']       = sanitize_text_field( $parsed_args['title'] );
		$parsed_args['description'] = sanitize_text_field( $parsed_args['description'] );
		$parsed_args['icon']        = sanitize_text_field( $parsed_args['icon'] );
		$parsed_args['category']    = sanitize_text_field( $parsed_args['category'] );
		$parsed_args['path']        = sanitize_text_field( $parsed_args['path'] );
		$parsed_args['class']       = sanitize_text_field( $parsed_args['class'] );
		$parsed_args['version']     = sanitize_text_field( $parsed_args['version'] );
		$parsed_args['type']        = sanitize_text_field( $parsed_args['type'] );

		if ( is_array( $parsed_args['keywords'] ) ) {
			$parsed_args['keywords'] = array_map( 'sanitize_text_field', $parsed_args['keywords'] );
		}

		$this->widgets[ $slug ] = $parsed_args;
		return true;
	}

	/**
	 * Unregister a widget dynamically.
	 *
	 * @param string $slug Widget slug.
	 * @return bool True if successfully removed, false if not found.
	 */
	public function unregister( $slug ) {
		if ( isset( $this->widgets[ $slug ] ) ) {
			unset( $this->widgets[ $slug ] );
			return true;
		}
		return false;
	}

	/**
	 * Retrieve a registered widget by slug.
	 *
	 * @param string $slug Widget slug.
	 * @return array|null Metadata array if found, null otherwise.
	 */
	public function get_widget( $slug ) {
		return isset( $this->widgets[ $slug ] ) ? $this->widgets[ $slug ] : null;
	}

	/**
	 * Get all registered widgets metadata.
	 *
	 * @return array Dictionary of registered widgets.
	 */
	public function get_widgets() {
		return $this->widgets;
	}

	/**
	 * Check if a widget is in the registry.
	 *
	 * @param string $slug Widget slug.
	 * @return bool True if registered.
	 */
	public function widget_exists( $slug ) {
		return isset( $this->widgets[ $slug ] );
	}

	/**
	 * Get total count of registered widgets.
	 *
	 * @return int Total count.
	 */
	public function get_widget_count() {
		return count( $this->widgets );
	}
}

/**
 * Global static helper wrapper class to support simple registration format:
 * Widget_Registry::register(...)
 */
class Widget_Registry {

	public static function register( $slug, $args ) {
		return Elonix_Toolkit_Widget_Registry::instance()->register( $slug, $args );
	}

	public static function unregister( $slug ) {
		return Elonix_Toolkit_Widget_Registry::instance()->unregister( $slug );
	}

	public static function get_widget( $slug ) {
		return Elonix_Toolkit_Widget_Registry::instance()->get_widget( $slug );
	}

	public static function get_widgets() {
		return Elonix_Toolkit_Widget_Registry::instance()->get_widgets();
	}

	public static function widget_exists( $slug ) {
		return Elonix_Toolkit_Widget_Registry::instance()->widget_exists( $slug );
	}

	public static function get_widget_count() {
		return Elonix_Toolkit_Widget_Registry::instance()->get_widget_count();
	}
}
