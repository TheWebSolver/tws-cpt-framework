<?php // phpcs:ignore WordPress.NamingConventions
/**
 * TheWebSolver\Core\Helper\CPT_Register_Interface class.
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

use TheWebSolver\CPT\Helper\Factory;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CPT Abstraction methods for consistent structure.
 *
 * Blueprint|Grouping|Rules|Abstraction method -> to be used by Post Types & Taxonomies.
 *
 * @since 1.0
 */
interface CPT_Register_Interface {
	/**
	 * Sets taxonomy to be used as filter key.
	 *
	 * @param string|string[] $keys The filter keys.
	 * * For Taxonomy:  single taxonomy slug, or array of taxonomy keys.
	 * * For Post Meta: single meta key, or array of meta keys.
	 */
	public function filter( $keys );

	/**
	 * Sets registering type admin table column.
	 *
	 * @param string|string[] $columns Admin table columns.
	 */
	public function manage( $columns );

	/**
	 * Manages registering type table columns.
	 *
	 * @param Factory $factory Passed from action hook.
	 */
	public function manage_columns( Factory $factory );
}
