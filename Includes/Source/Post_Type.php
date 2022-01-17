<?php // phpcs:ignore WordPress.NamingConventions
/**
 * Creates new post types.
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
use TheWebSolver\CPT\Helper\Factory;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Register Post Type class.
 */
final class Post_Type implements CPT_Interface, CPT_Register_Interface {
	use CPT_Register_Trait;

	/**
	 * Constructs post type data.
	 *
	 * @param string[] $names  Should be in key/value pair for following indexes:
	 * * `key`           (required) The post type key. NEVER CHANGE IT.
	 * * `slug`          (required) The post type slug. Can be same as key.
	 * * `singular_name` (optional) Generated from key, if not provided.
	 * * `plural_name`   (optional) Generated from key, if not provided.
	 *
	 * @since 1.0
	 * @since 2.0 Using `CPT_Register_Trait` to handle `post_type` registration.
	 */
	public function __construct( array $names ) {
		$this->start_factory_with( 20, $names );

		add_action( $this->hook, array( $this, 'add_filters' ), 1 );
	}

	/**
	 * Sets Post Type List filters.
	 *
	 * @param Factory $factory Passed from action hook.
	 *
	 * @since 1.0
	 * @since 2.0 Post type filters are now set from `Plugin`.
	 */
	public function add_filters( Factory $factory ) {
		tws_cpt()->set_post_type_filters( $factory->key, $factory->filter_key );
	}
}
