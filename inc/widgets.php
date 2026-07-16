<?php
/**
 * --------------------------------------------------------------
 *  Custom Widgets Loader
 * --------------------------------------------------------------
 *  This file is responsible for including all custom widget
 *  components used across the Elonix theme. Each component
 *  handles specific widget functionality such as:
 *  - Recent Posts
 *  - Buttons
 *  - Search Bar
 *  - Sidebar Controls
 * --------------------------------------------------------------
 *
 *  @package   Elonix – Toolkit for Elementor
 *  @version   1.0.0
 *  @author    Creative RakibRoni
 * --------------------------------------------------------------
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Include: Recent Posts Widget
 * Handles display and query of latest blog posts.
 */
require_once ELONIX_ACC_PATH . 'inc/widgets/recent-posts.php';

/**
 * Include: Newsletter Widget
 */
require_once ELONIX_ACC_PATH . 'inc/widgets/newsletter.php';
/**
 * Include: Advanced Categories Widget
 */
require_once ELONIX_ACC_PATH . 'inc/widgets/advanced-categories.php';

/**
 * Include: Elonix Service Help Widget
 */
require_once ELONIX_ACC_PATH . 'inc/widgets/contact-help.php';

/**
 * Elonix: Service Downloads Widget
 */
require_once ELONIX_ACC_PATH . 'inc/widgets/service-download.php';

/**
 * Include: Search Component
 * Manages custom search bar widget for headers or sidebars.
 */
require_once ELONIX_ACC_PATH . 'inc/components/search.php';

/**
 * Include: Sidebar Controls Component
 * Adds advanced sidebar options and widget styling controls.
 */
require_once ELONIX_ACC_PATH . 'inc/components/sidebar-controls.php';

/**
 * Include: Header Sticky Controls Component
 * Adds advanced Header Sticky options and widget styling controls.
 */
require_once ELONIX_ACC_PATH . 'inc/components/header-sticky.php';

/**
 * Include: MobileMenu Controls Component
 * Adds advanced MobileMenu options and widget styling controls.
 */
require_once ELONIX_ACC_PATH . 'inc/components/mobile-menu.php';

/**
 * Include: SocailLinks Controls Component
 * Adds advanced SocailLinks options and widget styling controls.
 */
require_once ELONIX_ACC_PATH . 'inc/components/social-links.php';
