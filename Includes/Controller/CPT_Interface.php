<?php // phpcs:ignore WordPress.NamingConventions
/**
 * TheWebSolver\Core\Helper\CPT_Interface class.
 *
 * -----------------------------------
 * DEVELOPED-MAINTAINED-SUPPPORTED BY
 * -----------------------------------
 * ███║     ███╗   ████████████████
 * ███║     ███║   ═════════██████╗
 * ███║     ███║        ╔══█████═╝
 *  ████████████║      ╚═█████
 * ███║═════███║      █████╗
 * ███║     ███║    █████═╝
 * ███║     ███║   ████████████████╗
 * ╚═╝      ╚═╝    ═══════════════╝
 *
 * @package TheWebSolver\Core\CPT_Framework\Interface
 * @since   1.0
 * @version 2.0 Developed with WPCS, namespaced autoloader usage.
 */

namespace TheWebSolver\CPT\Controller;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CPT Abstraction methods for consistent structure.
 *
 * Blueprint|Grouping|Rules|Abstraction method to be used by Factory, Post Types & Taxonomies.
 *
 * @since 1.0
 */
interface CPT_Interface {
	/**
	 * Sets labels for the registering type.
	 *
	 * @param array $labels Labels in an array.
	 */
	public function labels( array $labels );

	/**
	 * Sets arguments for the registering type.
	 *
	 * @param array $args Args in an array.
	 */
	public function args( array $args );

	/**
	 * Sets post-type or taxonomy for the registering type.
	 *
	 * @param string|string[] $objects Single object in string, multiple in an array.
	 */
	public function assign( $objects );

	/**
	 * Sets frontend page redirection of the registering type singular or archive page.
	 *
	 * @param array $args The redirection args.
	 */
	public function redirect( array $args );

	/**
	 * Registers the type.
	 */
	public function start();

	/**
	 * Redirects registering type and it's archive page.
	 */
	public function frontend_redirect();

	/**
	 * Starts registration hook.
	 */
	public function register();

	/**
	 * Finishes factory registration process.
	 */
	public function finish();
}
