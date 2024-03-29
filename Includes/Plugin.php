<?php // phpcs:ignore WordPress.NamingConventions
/**
 * Handles plugin initialization.
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
 */

namespace TheWebSolver\CPT;

use TheWebSolver\CPT\Controller\Singleton_Trait;
use TheWebSolver\CPT\Helper\Column;
use TheWebSolver\CPT\Helper\Factory;
use TheWebSolver\CPT\Helper\Filter;
use WP_Screen;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Plugin class.
 */
final class Plugin {
	use Singleton_Trait;

	/**
	 * Plugin version.
	 *
	 * @since 2.0
	 */
	const VERSION = '2.0';

	/**
	 * Registered post types.
	 *
	 * @var Factory[]
	 *
	 * @since 2.0
	 */
	private $post_types = array();

	/**
	 * Registered taxonomies.
	 *
	 * @var Factory[]
	 *
	 * @since 2.0
	 */
	private $taxonomies = array();

	/**
	 * Registered post type filters.
	 *
	 * @var string[][]
	 *
	 * @since 2.0
	 */
	private $post_type_filters = array();

	/**
	 * Registered post type columns.
	 *
	 * @var array
	 *
	 * @since 2.0
	 */
	private $post_type_columns = array();

	/**
	 * Registered taxonomy columns.
	 *
	 * @var array
	 *
	 * @since 2.0
	 */
	private $taxonomy_columns = array();

	/**
	 * Current admin screen object.
	 *
	 * @var WP_Screen
	 *
	 * @since 2.0
	 */
	private $screen;

	/**
	 * Inits plugin.
	 *
	 * @since 1.0
	 * @since 2.0 Removed core plugin integration.
	 */
	public function init() {
		// Perform task on current admin screen.
		add_action( 'current_screen', array( $this, 'screen' ) );

		return $this;
	}

	/**
	 * Verifies current page is given post type admin page.
	 *
	 * @param string $post_type The post type key/name.
	 *
	 * @return bool
	 *
	 * @since 2.0
	 */
	public function is_page( string $post_type ): bool {
		return $this->screen && $this->screen->post_type === $post_type;
	}

	/**
	 * Performs task on current admin screen.
	 *
	 * If is a post type screen, then:
	 * * Add action hook if post type is generated by `Factory`.
	 * * Add action hook if taxonomy is generated by `Factory`.
	 * * Instantiate class `Filter` if is post type page (not taxonomy page).
	 * * Instantiate class `Column` if is post type or taxonomy page.
	 *
	 * @param WP_Screen $current Passed from `current_screen` action hook.
	 *
	 * @since 1.0
	 */
	public function screen( WP_Screen $current ) {
		$this->screen = $current;
		$post_types   = $this->get_post_types();
		$taxonomies   = $this->get_taxonomies();
		$post         = $current->post_type;
		$taxo         = $current->taxonomy;
		$cpt          = is_array( $post_types ) && ! empty( $post_types )
		? array_keys( $post_types )
		: array();
		$tax          = is_array( $taxonomies ) && ! empty( $taxonomies )
		? array_keys( $taxonomies )
		: array();

		// Only continue if current screen matches the post type key, and
		// If current screen is not taxonomy page of current post type.
		if ( in_array( $post, $cpt, true ) && '' === $taxo ) {
			/**
			 * WPHOOK: Action -> Fires if current screen is of Factory post type screen.
			 *
			 * @param Factory $factory The current post type factory instance.
			 *
			 * @since 1.0
			 * @since 2.0 Changed hook name from `load_hzfex_{$post}_post_type`.
			 * @since 2.0 Passed factory instance instead of post type slug.
			 */
			do_action( "load_tws_{$post}_post_type", $this->post_types[ $post ] );

			if ( isset( $this->post_type_filters[ $post ] ) ) {
				Filter::load()->for( $post, array_unique( $this->post_type_filters[ $post ] ) );
			}

			Column::load()->for( $post )->walk()->apply();
		}

		// Only continue if current screen matches taxonomy key.
		if ( in_array( $taxo, $tax, true ) ) {
			/**
			 * WPHOOK: Action -> Fires if current screen is of Factory taxonomy screen.
			 *
			 * @param Factory $factory The current taxonomy factory instance.
			 *
			 * @since 1.0
			 * @since 2.0 Changed hook name from `load_hzfex_{$taxo}_taxonomy`.
			 * @since 2.0 Passed factory instance instead of taxonomy slug.
			 */
			do_action( "load_tws_{$taxo}_taxonomy", $this->taxonomies[ $taxo ] );

			Column::load()->for( $taxo, false )->walk()->apply();
		}
	}

	/**
	 * Sets type and it's factory instance.
	 *
	 * @param string  $key     The registering type key.
	 * @param Factory $factory The factory instance.
	 * @param string  $type    The registering type. Possible values are `post_type|taxonomy`.
	 *
	 * @since 2.0
	 */
	public function set_type( string $key, Factory $factory, string $type ) {
		$prop = 'post_type' === $type ? 'post_types' : 'taxonomies';

		$this->$prop[ $key ] = $factory;
	}

	/**
	 * Registered post types.
	 *
	 * @param string $key The post type key.
	 *
	 * @return Factory|Factory[] Factory for given key, else all factories.
	 *
	 * @since 1.0
	 * @since 2.0 Removed global var and use property value instead.
	 */
	public function get_post_types( string $key = '' ) {
		return isset( $this->post_types[ $key ] )
			? $this->post_types[ $key ]
			: $this->post_types;
	}

	/**
	 * Registered taxonomies global var.
	 *
	 * @param string $key The taxonomy key.
	 *
	 * @return Factory|Factory[] Factory for given key, else all factories.
	 *
	 * @since 1.0
	 * @since 2.0 Removed global var and use property value instead.
	 */
	public function get_taxonomies( string $key = '' ) {
		return isset( $this->taxonomies[ $key ] )
			? $this->taxonomies[ $key ]
			: $this->taxonomies;
	}

	/**
	 * Sets property value.
	 *
	 * @param string          $prop      The property name.
	 * @param string|string[] $value     To be saved key (as string) or keys (in an array).
	 * @param string          $post_type The post type key/name.
	 *
	 * @since 2.0
	 */
	private function set( string $prop, $value, string $post_type ) {
		$value = Factory::to_array( $value );
		$set   = isset( $this->$prop[ $post_type ] )
		? array_merge( $this->$prop[ $post_type ], $value )
		: $value;

		$this->$prop[ $post_type ] = $set;
	}

	/**
	 * Sets post type filters.
	 *
	 * @param string          $post_type The post type for which filter to be added.
	 * @param string|string[] $value     The filter key (as string) or keys (in an array).
	 *
	 * @since 2.0
	 */
	public function set_post_type_filters( string $post_type, $value ) {
		$this->set( 'post_type_filters', $value, $post_type );
	}

	/**
	 * Sets post type columns.
	 *
	 * @param string          $post_type The post type for which column to be added.
	 * @param string|string[] $value     The column key (as string) or keys (in an array).
	 *
	 * @since 2.0
	 */
	public function set_post_type_columns( string $post_type, $value ) {
		$this->set( 'post_type_columns', $value, $post_type );
	}

	/**
	 * Sets taxonomy columns.
	 *
	 * @param string          $taxonomy The taxonomy for which column to be added.
	 * @param string|string[] $value    The column key (as string) or keys (in an array).
	 *
	 * @since 2.0
	 */
	public function set_taxonomy_columns( string $taxonomy, $value ) {
		$this->set( 'taxonomy_columns', $value, $taxonomy );
	}

	/**
	 * Gets filter keys set for post types.
	 *
	 * @return string[][] Post type key as index and filter key in string|string[] as value.
	 *
	 * @since 1.0
	 * @since 2.0 Removed global var and use property value instead.
	 */
	public function get_post_type_filters(): array {
		return $this->post_type_filters;
	}

	/**
	 * Gets columns data set for post types.
	 *
	 * @return array Saved Post Type Key as key and value.
	 *
	 * @since 1.0
	 * @since 2.0 Removed global var and use property value instead.
	 */
	public function get_post_type_columns(): array {
		return $this->post_type_columns;
	}

	/**
	 * Gets columns data set for taxonomy.
	 *
	 * @return array Saved Taxonomy Key as key and value.
	 *
	 * @since 1.0
	 * @since 2.0 Removed global var and use property value instead.
	 */
	public function get_taxonomy_columns(): array {
		return $this->taxonomy_columns;
	}

	/**
	 * Private constructor to prevent direct instantiation.
	 *
	 * @since 1.0
	 */
	private function __construct() {}
}
