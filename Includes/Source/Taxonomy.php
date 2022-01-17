<?php // phpcs:ignore WordPress.NamingConventions
/**
 * Creates new taxonomies.
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
 * @package TheWebSolver\Core\CPT_Framework\Class
 * @since   1.0
 * @version 2.0 Developed with WPCS, namespaced autoloader usage.
 *              Methods are now handled by CPT_Register_Trait.
 */

namespace TheWebSolver\CPT\Source;

use TheWebSolver\CPT\Controller\CPT_Register_Trait;
use TheWebSolver\CPT\Controller\CPT_Interface;
use TheWebSolver\CPT\Controller\CPT_Register_Interface;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Register taxonomy class.
 */
final class Taxonomy implements CPT_Interface, CPT_Register_Interface {
	use CPT_Register_Trait;

	/**
	 * Constructs Taxonomy data.
	 *
	 * @param string[] $names  Should be in key/value pair for following indexes:
	 * * `key`           (required) The taxonomy key. NEVER CHANGE IT.
	 * * `slug`          (required) The taxonomy slug. Can be same as key.
	 * * `singular_name` (optional) Generated from key, if not provided.
	 * * `plural_name`   (optional) Generated from key, if not provided.
	 *
	 * @since 1.0
	 * @since 2.0 Using `CPT_Register_Trait` to handle `taxonomy` registration.
	 */
	public function __construct( array $names ) {
		$this->start_factory_with( 32, $names );
	}
}
