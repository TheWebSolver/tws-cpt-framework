<?php // phpcs:ignore WordPress.NamingConventions
/**
 * Admin list table column API.
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
 * @package TheWebSolver\Core\CPT_Framework\Class\API
 * @since   1.0
 * @version 2.0 Developed with WPCS, namespaced autoloader usage.
 *              Made methods privated but can be accessed using magic method.
 */

namespace TheWebSolver\CPT\Helper;

use TheWebSolver\CPT\Controller\Singleton_Trait;
use WP_Query;
use WP_Term_Query;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Column class
 *
 * Manage post_type\taxonomy admin table columns.
 *
 * @method ((string|string[]|int)[]|bool[]|bool)[]|string[] columns() All columns set.
 * @method string[] modified() Modified columns after ordering by priority.
 * @method string orderby() Query value for `orderby` from sorted column.
 * @method string[] query_vars() Gets WP_Query `query_vars` value from sorted column.
 * @method WP_Query|WP_Term_Query parsed_query() Modified query object from query vars.
 * @method string[] add() Added columns.
 * @method string[] remove() Removed columns.
 * @method string[] callback() Callback function names for displaying column content.
 * @method (string|string[])[] sortable() Sortable columns.
 * @method string[] unsortable() Unsortable columns.
 * @method int[] priority() Columns with their ordering priority.
 * @method string type() The current page where to manage columns.
 * @method string key() Post Type/Taxonomy slug/key.
 *
 * @since 2.0 Exposed private properties with magic method.
 */
final class Column {
	use Singleton_Trait;

	/**
	 * Table columns from API parameter.
	 *
	 * @var ((string|string[]|int)[]|bool[]|bool)[]|string[]
	 *
	 * @since 1.0
	 */
	private $columns = array();

	/**
	 * Modified columns after ordering by priority.
	 *
	 * @var string[]
	 *
	 * @since 1.0
	 */
	private $modified = array();

	/**
	 * Query value for `orderby` from sorted column.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	private $orderby;

	/**
	 * Meta query `query_vars` value from sorted column.
	 *
	 * @var string[]
	 *
	 * @since 1.0
	 */
	private $query_vars = array();

	/**
	 * Modified query object from query vars.
	 *
	 * {@see @property Column::$query_vars }
	 *
	 * @var WP_Query|WP_Term_Query Based on where column is instantiated.
	 *
	 * @since 1.0
	 */
	private $parsed_query;

	/**
	 * Added columns.
	 *
	 * @var string[] Column slug as key, label as value.
	 *
	 * @since 1.0
	 */
	private $add = array();

	/**
	 * Removed columns.
	 *
	 * @var string[] Column slugs in an array.
	 *
	 * @since 1.0
	 */
	private $remove = array();

	/**
	 * Callback function names for displaying column content.
	 *
	 * @var string[] Column slug as key, callback function as value.
	 *
	 * @since 1.0
	 */
	private $callback = array();

	/**
	 * Columns that are sortable.
	 *
	 * @var (string|string[])[] Column slugs in an array.
	 *
	 * @since 1.0
	 */
	private $sortable = array();

	/**
	 * Columns that are unsortable.
	 *
	 * @var string[] Column slugs in an array.
	 *
	 * @since 1.0
	 */
	private $unsortable = array();

	/**
	 * Columns with their ordering priority.
	 *
	 * @var int[] Column slug as key, priority number as value.
	 *
	 * @since 1.0
	 */
	private $priority;

	/**
	 * The current page where to manage columns.
	 *
	 * Must be either `post_type` or `taxonomy`.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	private $type;

	/**
	 * Post Type/Taxonomy slug/key.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	private $key;

	/**
	 * Sets and instantiates Column class.
	 *
	 * @param string $key     Post Type Key/Taxonomy Key of the current page.
	 * @param bool   $is_post Whether is post type page. Defaults to `true`.
	 *
	 * @return Column
	 *
	 * @since 1.0
	 */
	public function for( string $key, bool $is_post = true ): Column {
		$this->type = 'post_type';
		$columns    = tws_cpt()->get_post_type_columns();

		if ( ! $is_post ) {
			$this->type = 'taxonomy';
			$columns    = tws_cpt()->get_taxonomy_columns();
		}

		// Set property value for the current admin table.
		$this->columns = array_key_exists( $key, $columns ) ? $columns[ $key ] : array();
		$this->key     = $key;

		return $this;
	}

	/**
	 * Prepares the registering type admin table column.
	 *
	 * @return Column
	 *
	 * @since 1.0
	 * @since 2.0 Renamed method from `ready`.
	 */
	public function walk(): Column {
		// Bail early if no columns set.
		if ( empty( $this->columns ) ) {
			return $this;
		}

		// Iterate over columns and set property value.
		foreach ( $this->columns as $column => $data ) {
			// If data is set as string, it must be the column key.
			if ( is_string( $data ) ) {
				$this->add[ $data ] = Factory::make_label( $data );

				continue;
			}

			// If data is set as bool, it must be whether to add or remove the column.
			if ( is_bool( $data ) ) {
				if ( $data ) {
					$this->add[ $column ] = Factory::make_label( (string) $column );
				} else {
					$this->remove[ $column ] = (string) $column;
				}

				continue;
			}

			// Friendly debug msg that data can only be array if none of the above.
			if ( ! is_array( $data ) ) {
				_doing_it_wrong( __METHOD__, 'The column data can only be a string, bool or an array.', '2.0' );

				continue;
			}

			/**
			*   ╔══════════════════════════════════════════════════════╗
			*  ║ If we arrive here, then array data given, work on it.║
			* ╚══════════════════════════════════════════════════════╝
			*/

			// If $data has sortable key, add sort/unsort feature to the column.
			if ( isset( $data['sortable'] ) ) {
				$sort = $data['sortable'];

				// If array given, prepare it for query vars.
				if ( is_array( $sort ) ) {
					// Set property value.
					$this->sortable[ $column ] = array(
						'key'     => isset( $sort['key'] ) ? (string) $sort['key'] : (string) $column,
						'type'    => isset( $sort['type'] ) ? (string) $sort['type'] : 'CHAR',
						'compare' => isset( $sort['compare'] ) ? (string) $sort['compare'] : '=',
					);
				} elseif ( is_string( $sort ) ) {
					// Set column key as key and sortable value as value of the property.
					$this->sortable[ $column ] = $sort; // This is a callback func.
				} elseif ( is_bool( $sort ) ) {
					// If set to true, sorting must be done using `column_slug`.
					// Which means `column_slug` must be same as `meta_key`
					// that is used in callback function for displaying the content.
					if ( $sort ) {
						$this->sortable[ $column ] = (string) $column;
					} else {
						// If set to false, sorting must be disabled for the column.
						$this->unsortable[ $column ] = (string) $column;
					}
				}
			}

			// Columns to add.
			if ( isset( $data['label'] ) && ! empty( $data['label'] ) ) {
				$this->add[ $column ] = (string) $data['label'];
			} else {
				$this->add[ $column ] = Factory::make_label( $column );
			}

			// This is for custom columns with contents fetched from callback function.
			if ( isset( $data['callback'] ) && is_callable( $data['callback'] ) ) {
				$this->callback[ $column ] = (string) $data['callback'];
			}

			// Columns position.
			if ( isset( $data['priority'] ) && ! empty( $data['priority'] ) ) {
				$this->priority[ $column ] = (int) $data['priority'];
			}
		}

		$sorted_key = Factory::is_get( 'orderby' );

		// Trim if sorted column key is taxonomy coz it can't be treated as query vars.
		$is_tax        = 0 === strpos( $sorted_key, 'taxonomy-', 0 ) && 0 === strrpos( $sorted_key, 'taxonomy-' );
		$this->orderby = $is_tax ? '' : $sorted_key;

		return $this;
	}

	/**
	 * Applies WordPress action and filter hooks to manage admin list column.
	 *
	 * @since 1.0
	 */
	public function apply() {
		if ( method_exists( $this, "manage_{$this->type}_columns" ) ) {
			// Call respective registering type method to manage admin table columns.
			call_user_func( array( $this, "manage_{$this->type}_columns" ) );

			// Set/unset columns for sorting of admin table list.
			// Common callback method is used for both types.
			add_filter( "manage_edit-{$this->key}_sortable_columns", array( $this, 'maybe_sort' ) );
		}
	}

	/**
	 * Manage post type admin table columns.
	 *
	 * @since 1.0
	 * @since 2.0 Renamed method name from `__manage_post_type_columns`.
	 * @since 2.0 Changed callback name & higher priority for hooks.
	 */
	private function manage_post_type_columns() {
		add_filter( "manage_{$this->key}_posts_columns", array( $this, 'modify' ), 999, 1 );

		// Add custom column content. Callback passed will be used.
		add_filter( "manage_{$this->key}_posts_custom_column", array( $this, 'display_post_content' ), 999, 2 );

		// We set priority according to the query vars set by filtering.
		// because filter must have higher piority than sort... of course!!!.
		$priority = empty( Filter::load()->queried_keys() ) ? 100 : 98;

		add_action( 'parse_query', array( $this, 'parse_post_query' ), $priority );
	}

	/**
	 * Manage taxonomy admin table columns.
	 *
	 * @since 1.0
	 * @since 2.0 Renamed method name from `__manage_taxonomy_columns`
	 * @since 2.0 Changed callback name & higher priority for hooks.
	 */
	private function manage_taxonomy_columns() {
		add_filter( "manage_edit-{$this->key}_columns", array( $this, 'modify' ), 999, 1 );
		add_filter( "manage_{$this->key}_custom_column", array( $this, 'display_term_content' ), 999, 3 );
		add_action( 'parse_term_query', array( $this, 'parse_term_query' ) );
	}

	/**
	 * Displays custom content for post type custom column from callback.
	 *
	 * @param string $column  The column key passed from filter.
	 * @param int    $post_id The post ID passed from filter.
	 *
	 * @return mixed The content from callback, else empty string.
	 *
	 * @since 1.0
	 */
	public function display_post_content( string $column, int $post_id ) {
		// Set empty string as value if no callback set.
		if ( ! isset( $this->callback[ $column ] ) ) {
			return '';
		}

		// Callback function that displays the column data.
		return call_user_func_array( $this->callback[ $column ], array( $column, $post_id ) );
	}

	/**
	 * Displays custom content for taxonomy custom column from callback.
	 *
	 * @param string $content The column blank content passed from filter.
	 * @param string $column  The column key passed from filter.
	 * @param int    $term_id The term ID passed from filter.
	 *
	 * @return mixed The content from callback, else empty string.
	 *
	 * @since 1.0
	 */
	public function display_term_content( string $content, string $column, int $term_id ) {
		// Bail early if no callback set.
		if ( ! isset( $this->callback[ $column ] ) ) {
			return $content;
		}

		// Callback function that displays the column data.
		return call_user_func_array( $this->callback[ $column ], array( $content, $column, $term_id ) );
	}

	/**
	 * Make columns sortable/unsortable.
	 *
	 * @param string[] $columns Default columns set for sorting.
	 *
	 * @return string[] Final column for sorting.
	 *
	 * @since 1.0
	 */
	public function maybe_sort( array $columns ): array {
		if ( ! empty( $this->unsortable ) ) {
			// Iterate over all unsortable columns and unset'em from defaults.
			foreach ( $this->unsortable as $unset ) {
				unset( $columns[ $unset ] );
			}
		}
		if ( ! empty( $this->sortable ) ) {
			// Iterate over all sortable columns and append new columns to defaults.
			foreach ( $this->sortable as $column => $set ) {
				$columns[ $column ] = is_array( $set ) ? (string) $set['key'] : (string) $set;
			}
		}

		return $columns;
	}

	/**
	 * Change post type admin table rows order by parsing query data.
	 *
	 * @param WP_Query $query The WP_Query API class.
	 *
	 * @since 1.0
	 * @since 2.0 Changed method name from `parse_query`.
	 */
	public function parse_post_query( WP_Query &$query ): WP_Query {
		// Get current post type page.
		$post_type = Factory::is_get( 'post_type' );

		// Bail early if not in post type admin screen or not the main query.
		if ( ! is_admin() || ! $query->is_main_query() || $query->get( 'post_type' ) !== $post_type || $post_type !== $this->key ) {
			return $query;
		}

		// Get the meta key that is set for sorting from query orderby parameter.
		$meta_key = $query->get( 'orderby' );
		$sortable = $this->is_sortable_by( $meta_key );

		// Bail if column is not sortable with the query set.
		// All taxonomy keys used for filtering is also omitted
		// so query vars are not set by using taxonomy key.
		// Doing so may result to not show any posts in the table.
		// This means only post metas are treated as `query_vars`.
		if ( ! $sortable ) {
			return $query;
		}

		// Return the parsed query vars.
		return $this->get_parsed_query_with( $query, $sortable );
	}

	/**
	 * Change taxonomy admin table row orders by parsing query data.
	 *
	 * @param WP_Term_Query $query The WP_Term_Query API class.
	 *
	 * @since 1.0
	 */
	public function parse_term_query( WP_Term_Query $query ): WP_Term_Query {
		// Bail early if not in taxonomy type admin screen.
		if ( ! is_admin() || ! in_array( $this->key, $query->query_vars['taxonomy'], true ) ) {
			return $query;
		}

		// Get the meta key that is set for sorting from query orderby parameter.
		$meta_key = Factory::is_get( 'orderby' );
		$sortable = $this->is_sortable_by( $meta_key );

		// Bail if column is not sortable with the query set.
		if ( ! $sortable ) {
			return $query;
		}

		// Return the parsed query vars.
		return $this->get_parsed_query_with( $query, $sortable );
	}

	/**
	 * Set the query vars.
	 *
	 * @param WP_Query|WP_Term_Query $query The query object passed as reference.
	 * @param string|string[]        $meta  The meta key.
	 *
	 * @return WP_Query|WP_Term_Query The modified query.
	 *
	 * @since 1.0
	 * @since 2.0 Renamed method name from `_get_parsed_query_with`.
	 */
	private function get_parsed_query_with( object $query, $meta ) {
		// Define query vars data.
		$vars               = &$query->query_vars;
		$type               = 'CHAR';
		$compare            = '=';
		$vars['meta_query'] = array(); // phpcs:ignore -- slow query ok.

		// Set the meta key and meta value query type.
		if ( is_string( $meta ) ) {
			$key = $meta;
		} else {
			$key     = $meta['key'];
			$type    = $meta['type'];
			$compare = $meta['compare'];
		}

		// Set query vars.
		$vars['meta_query'][] = array(
			'key'     => $key,
			'orderby' => 'value',
			'type'    => $type,
			'compare' => $compare,
		);

		// Set query vars as property.
		$this->query_vars = $vars['meta_query'];

		// Set parsed query object as property.
		$this->parsed_query = $query;

		// Return the modifed query.
		return $query;
	}

	/**
	 * Modify admin table columns.
	 *
	 * Modification is done as:
	 * * Added columns will set columns.
	 * * Removed columns will unset columns.
	 * * Priority will change the column order/position.
	 *
	 * @param string[] $columns Default columns set for the current admin table.
	 *
	 * @return array Modified columns.
	 *
	 * @since 1.0
	 */
	public function modify( array $columns ): array {
		// Add columns.
		if ( ! empty( $this->add ) ) {
			foreach ( $this->add as $key => $label ) {
				$columns[ $key ] = $label;
			}
		}

		// Remove columns.
		if ( ! empty( $this->remove ) ) {
			foreach ( $this->remove as $key ) {
				unset( $columns[ $key ] );
			}
		}

		// Get columns order set from API parameter.
		$get_priority = $this->priority;

		/**
		 * Uncomment line after this comment block for `$get_priority`
		 * if you want to ignore the priority number greater than
		 * the total number of columns currently set in the table.
		 * As always, the ordering starts from `0` as it is an array.
		 *
		 * BRIEF EXPLANATION WITH EXAMPLE:
		 * Let's say, 7 columns are currently set in the table
		 * including checkbox (YES...YOU ALMOST FORGOT THAT RIGHT?).
		 * Then, highest priority you can set will be 6
		 * because counting starts from 0 (thus 7=6 DUH!!!).
		 * This means, 7 and above numbers will be ignored and
		 * order from `$default_priority` below will be intact.
		 *
		 * $get_priority = array_intersect( $this->priority, $default_priority );
		 */

		// If no priority is set, then return columns in old order.
		if ( ! is_array( $get_priority ) || empty( $get_priority ) ) {
			return $columns;
		}

		// Generate columns order by their default position.
		$default_priority = array_flip( array_keys( $columns ) );

		// Replace default order with those from API parameter.
		$new_priority = array_replace( $default_priority, $get_priority );

		// Finally sort columns by new order number.
		asort( $new_priority );

		foreach ( $new_priority as $key => $value ) {
			// Set new columns.
			$new_columns[ $key ] = $columns[ $key ];

			// Remove old columns.
			unset( $columns[ $key ] );
		}

		// Set the modified columns as property value.
		$this->modified = $new_columns;

		// Return the modified column and use these to set columns.
		return $new_columns;
	}

	/**
	 * Check if column has sorting feature set with the meta key.
	 *
	 * @param string $meta_key The meta key used for querying.
	 *
	 * @return string|string[] String or array of data if sorting key exists.
	 *
	 * @since 1.0
	 * @since 2.0 Changed method name from `_is_sortable_by` and scrapped unnecessary codes.
	 */
	private function is_sortable_by( string $meta_key ) {
		// Bail early if no sorting set for the type.
		if ( empty( $this->sortable ) ) {
			return '';
		}

		// True if meta key and column key is same.
		if ( array_key_exists( $meta_key, $this->sortable ) ) {
			return $this->orderby ? $this->sortable[ $meta_key ] : '';
		}

		return '';
	}
}
