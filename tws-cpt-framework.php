<?php // phpcs:ignore WordPress.NamingConventions
/**
 * Plugin Name: The Web Solver Custom Post Type Framework
 * Plugin URI: https://github.com/TheWebSolver/tws-custom-post-type-framework
 * Description: <b>Custom Post Type framework</b> to register WordPress Custom Post Types and Taxonomies
 * Version: 2.0
 * Author: Shesh Ghimire
 * Author URI: https://www.linkedin.com/in/sheshgh/
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * License: GNU General Public License v3.0 (or later)
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package TheWebSolver\Core\CPT_Framework
 */

use TheWebSolver\CPT\Plugin;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * TheWebSolver CPT Framework.
 *
 * @return Plugin
 *
 * @since 1.0
 * @since 2.0 Autoloader and plugin instantiated.
 */
function tws_cpt(): Plugin {
	static $plugin = null;

	if ( null === $plugin ) {
		// Autoloader from composer or custom.
		require_once __DIR__ . '/autoload.php';

		$map    = array( 'Includes' => 'TheWebSolver\CPT' );
		$loader = TWS_Autoloader::load();

		$loader->root( __DIR__ )->path( $map )->walk();

		$plugin = Plugin::load()->init();

		/**
		 * WPHOOK: Action -> fires after CPT Framework loaded.
		 *
		 * @param Plugin         $plugin The plugin instance.
		 * @param TWS_Autoloader $loader The autoloader instance.
		 * @since 2.0
		 */
		do_action( 'tws_cpt_framework_loaded', $plugin, $loader );
	}

	return $plugin;
}

tws_cpt();
