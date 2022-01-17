<?php // phpcs:ignore WordPress.NamingConventions
/**
 * The registering type trait.
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
 * @package TheWebSolver\Core\CPT_Framework\Trait
 * @since   2.0
 * @author  Shesh Ghimire <shesh@thewebsolver.com>
 */

namespace TheWebSolver\CPT\Controller;

use TheWebSolver\CPT\Helper\Factory;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Exposes the registering type helper methods.
 *
 * @since 2.0
 */
trait CPT_Register_Trait {
	/**
	 * The factory instance.
	 *
	 * @var Factory
	 *
	 * @since 2.0
	 */
	private $factory;

	/**
	 * The registering type.
	 *
	 * Possible values are `post_type|taxonomy`.
	 *
	 * @var string
	 *
	 * @since 2.0
	 */
	private $type;

	/**
	 * Rergister type hook priority which registers the type.
	 *
	 * @var int
	 *
	 * @since 2.0
	 */
	private $start = 10;

	/**
	 * Finish registering type hook priority which assigns objects to each other.
	 *
	 * @var int
	 *
	 * @since 2.0
	 */
	private $end = 14;

	/**
	 * The currently loaded page hook.
	 *
	 * @var string
	 *
	 * @since 2.0
	 */
	public $hook;

	/**
	 * Gets classname sans namespace.
	 *
	 * @return string
	 *
	 * @since 2.0
	 */
	private function get_class(): string {
		$classname   = explode( '\\', get_class() );
		$class       = $classname ? array_pop( $classname ) : '';
		$class       = strtolower( trim( $class ) );
		$this->type  = $class;
		$this->start = 'post_type' === $class ? 11 : 10;
		$this->end   = 'post_type' === $class ? 15 : 14;

		return $class;
	}

	/**
	 * Starts factory for registration process.
	 *
	 * @param int      $length The WP supported max length to insert data to database.
	 * @param string[] $names  The registering type names.
	 *
	 * @since 2.0
	 */
	private function start_factory_with( int $length, array $names ) {
		// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure, WordPress.CodeAnalysis.AssignmentInCondition
		if ( $class = $this->get_class() ) {
			$this->factory = new Factory( $class, $names, $length );

			$this->process();

			$this->hook = "load_tws_{$this->factory->key}_{$this->type}";
		}
	}

	/**
	 * Validates if all API parameters are okay.
	 *
	 * WordPress dies if proper names are not set.
	 *
	 * @since 2.0
	 */
	private function process() {
		if ( ! $this->factory->has_valid_names() ) {
			$this->factory->shutdown( "{$this->type} Registration Failed", 'names' );
		}

		$this->factory->validate_names();
	}

	/**
	 * Sets the labels for the registering type.
	 *
	 * @param array $labels Lables in an array.
	 *
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function labels( array $labels ) {
		$this->factory->labels( $labels );
		return $this;
	}

	/**
	 * Sets arguments for the registering type.
	 *
	 * @param array $args Args in an array.
	 *
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function args( array $args ) {
		$this->factory->args( $args );
		return $this;
	}

	/**
	 * Sets objects to the registering type.
	 *
	 * If registering type is post type, set taxonomies and vice versa.
	 *
	 * @param string/string[] $name single (in `string`) or multiple (in `array`) object name for the registering type.
	 *
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function assign( $name ) {
		$this->factory->assign( $name );
		return $this;
	}

	/**
	 * Sets frontend page redirection of the registering type singular or archive page.
	 *
	 * @param array $args The redirection args.
	 *
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function redirect( array $args ) {
		$this->factory->redirect( $args );
		return $this;
	}

	/**
	 * Sets registering type admin table filtering keys.
	 *
	 * @param bool|string|string[] $keys The filter keys.
	 * * If used in Post Type:-
	 *  ** If taxonomy: Single taxonomy slug, or array of taxonomy keys.
	 *  ** If postmeta: Single meta key, or array of meta keys.
	 * * If used in taxonomy:-
	 *  ** True to set key as query vars, false if not.
	 *
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function filter( $keys ) {
		if ( 'post_type' === $this->type ) {
			$value = ! is_array( $keys ) ? array( $keys ) : (array) $keys;
		} else {
			$value = (bool) $keys;
		}
		$this->factory->filter_key = $value;

		return $this;
	}

	/**
	 * Set post type admin table columns.
	 *
	 * @param ((string|string[]|int)[]|bool[]|bool)[]|string[] $columns The admin columns.
	 *
	 * @return $this
	 *
	 * @since 2.0
	 * @example usage
	 *
	 * Examples shown below are for post type. But same logic applies for taxonomy.
	 * ```
	 * $columns = [
	 *  // To remove default column like for Title, use column key:
	 *  'title' => false,
	 *
	 *  // Using `sortable` for taxonomy column is PROHIBITED.
	 *  // Using `['sortable'=>true`]` for default columns is also PROHIBITED.
	 *  // Because query vars meta key/value set with column key does not exist.
	 *  // Use this feature to only disable sorting feature of default columns.
	 *  // To disable sorting for default column like for Author, use column key:
	 *  'author' => ['sortable'=>false],
	 *
	 *   // Recommended to use taxonomy slug or meta key as column key/index.
	 *   // As an another full example for the custom column, use:
	 *  'column_key' => [
	 *
	 *    // Table column header name.
	 *    'label' => __( 'Column label', 'tws-cpt-framework' ),
	 *
	 *    // Function to display content in the current column.
	 *    // Function accepts two or three args depending on `$this->type`.
	 *    // For post type: `$column_key` and `$post_id`.
	 *    // For taxonomy: `$content`, `$column_key` and `$term_id`.
	 *    // Mostly `get_post_meta()` or similar is echoed in callback.
	 *    'callback' => 'function_that_display_column_content',
	 *
	 *    // `true`  if meta key in callback is same as column key.
	 *    // `false` to disable sorting of the column (for default columns).
	 *    // `array` for custom sorting query.
	 *    'sortable' => ['key'=>'column_key','type'=>'CHAR','compare'=>'='],
	 *
	 *
	 *    // `1` is first, `2` is second, and so on. To order all columns properly,
	 *    // set with this key/value for all columns.
	 *    'priority' => 3,
	 *   ],
	 *
	 *   // ...add another column data.
	 * ];
	 *
	 * // Alternatively, if don't need to set any array values,
	 * // use as shown below: (usually for default columns).
	 * $columns = ['custom_slug_one','custom_slug_two'];
	 * ```
	 */
	public function manage( $columns ) {
		$this->factory->columns = $columns;
		return $this;
	}

	/**
	 * Registration hook.
	 *
	 * @since 2.0
	 */
	public function register() {
		$this->factory->register();
	}

	/**
	 * Finishes factory registration process.
	 *
	 * @since 2.0
	 */
	public function finish() {
		$this->factory->finish();
	}

	/**
	 * Redirects registering type single or archive page.
	 *
	 * @since 2.0
	 */
	public function frontend_redirect() {
		$this->factory->frontend_redirect();
	}

	/**
	 * Manages registering type table columns.
	 *
	 * @param Factory $factory Passed from action hook.
	 *
	 * @since 2.0
	 */
	public function manage_columns( Factory $factory ) {
		$method = "set_{$this->type}_columns";

		if ( ! is_callable( array( tws_cpt(), $method ), true ) ) {
			_doing_it_wrong( __METHOD__, 'Columns data could not be added.', '2.0' );

			return;
		}

		tws_cpt()->$method( $factory->key, $factory->columns );
	}

	/**
	 * Registers the type.
	 *
	 * @since 2.0
	 */
	public function start() {
		if ( ! $this->factory->start() ) {
			return;
		}

		add_action( 'init', array( $this, 'register' ), $this->start );
		add_action( 'init', array( $this, 'finish' ), $this->end );

		$cols = $this->factory->columns;

		if ( $this->factory->redirect_frontend ) {
			add_action( 'template_redirect', array( $this, 'frontend_redirect' ) );
		}

		// Bail if columns not set.
		if ( ! is_array( $cols ) || empty( $cols ) ) {
			return;
		}

		add_action( $this->hook, array( $this, 'manage_columns' ), 1 );
	}
}
