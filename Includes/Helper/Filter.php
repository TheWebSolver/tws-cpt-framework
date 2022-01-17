<?php // phpcs:ignore WordPress.NamingConventions
/**
 * TheWebSolver\Core\Helper\Filter class
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
 *              Made properties private but can be accessed magically.
 *              Made methods non-static.
 */

namespace TheWebSolver\CPT\Helper;

use TheWebSolver\CPT\Controller\Singleton_Trait;
use WP_Query;
use wpdb;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Filter class for filtering admin list table.
 *
 * @method string[] filtered_keys() Keys present in URL for querying when filtering.
 * @method string post_type()       Post type key for which filtering to be added.
 * @method string[] filtered_keys() Keys present in URL for querying when filtering.
 * @method string[] queried_keys()  Gets Meta key/value pair for parsing query.
 * @method WP_Query parsed_query()  Gets modified query with queried keys.
 */
final class Filter {
	use Singleton_Trait;

	/**
	 * The filtering keys.
	 *
	 * Keys can be of taxonomies or custom metas.
	 *
	 * @var string[]
	 *
	 * @since 1.0
	 */
	private $filter_keys;

	/**
	 * The post type key for which filtering to be added.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	private $post_type;

	/**
	 * Keys present in URL for querying when filtering.
	 *
	 * These keys are what will be used for defining query keys.
	 * {@see @property Filter::$queried_keys }
	 * These keys must be from `{$prefix}_postmeta` database table
	 * and not from `{$prefix}_posts` table as query may not
	 * be parsed using keys from main post table.
	 *
	 * @var string[]
	 *
	 * @since 1.0
	 */
	private $filtered_keys = array();

	/**
	 * Meta_key/meta_value pair that will be used for parsing query.
	 *
	 * @var string[]
	 *
	 * @since 1.0
	 */
	private $queried_keys = array();

	/**
	 * Modified query with queried keys.
	 *
	 * The modified query object after passing filtered post meta key/values.
	 *
	 * @var WP_Query
	 *
	 * @since 1.0
	 */
	private $parsed_query;

	/**
	 * Starts filtering process for the given post type.
	 *
	 * @param string   $post_type Post Type key.
	 * @param string[] $filters   The filter keys.
	 *
	 * @return bool Initiate filtering if keys set, else false.
	 *
	 * @since 1.0
	 */
	public function for( string $post_type, array $filters ): bool {
		// Bail early if filtering is not set for the current post type.
		if ( empty( $filters ) ) {
			return false;
		}

		$this->filter_keys = $filters;
		$this->post_type   = $post_type;

		$this->parse_query( $filters );

		// Add filter dropdown on admin post table.
		add_action( 'restrict_manage_posts', array( $this, 'restrict' ) );

		return true;
	}

	/**
	 * Adds filter dropdown.
	 *
	 * Can be used directly.
	 * Simply add filter post type and keys, then parse query with filtered key.
	 * * `admin_init` hook to parse query.
	 * * `restrict_manage_posts` hook to add filter.
	 *
	 * @param string $post_type (required) Post type key.
	 * @param string $key       (required) Taxonomy/Meta key to create filter dropdown.
	 *
	 * @return bool True if filters set, false otherwise.
	 *
	 * @since 1.0
	 * @since 2.0 Renamed method name from `with`, code refactoring.
	 * @example usage
	 * ```
	 * use TheWebSolver\Core\Helper\Filter;
	 *
	 * // Create filter dropdowns.
	 * add_action('restrict_manage_posts',function(){
	 *      Filter::load()->with('post_type_key','post_filter_key');
	 *      Filter::load()->with('post_type_key','another_filter_key');
	 * });
	 *
	 * // Parse query to return only post that matches with filtered key.
	 * add_action( 'admin_init',function(){
	 *  Filter::load()->parse_query(['post_filter_key','another_filter_key']);
	 * });
	 * ```
	 */
	public function with( string $post_type, string $key ): bool {
		// Bail early if can't add to the current post type.
		if ( ! did_action( 'restrict_manage_posts' ) || ! tws_cpt()->is_page( $post_type ) ) {
			return false;
		}

		$filters = tws_cpt()->get_post_type_filters();

		// Get all filter keys set for the current post type, if any.
		$exits = isset( $filters[ $post_type ] ) ? (array) $filters[ $post_type ] : array();

		// Bail if filter keys is already set.
		// This to prevent duplication of same filter being added using different methods.
		if ( in_array( $key, $exits, true ) ) {
			return false;
		}

		$this->by( $post_type, $key );

		tws_cpt()->set_post_type_filters( $post_type, $key );
	}

	/**
	 * Adds and displays filter dropdown on Post List Table.
	 *
	 * @param string $post_type The Post Type Key passed from action hook.
	 *
	 * @return bool
	 *
	 * @since 1.0
	 * @since 2.0 Renamed method name from `with_keys`.
	 */
	public function restrict( string $post_type ) {
		// Not necessary to check post type as this won't run on incorrect post type screen.
		// But as we have $post_type param, we are checking here too.
		// So, bail early in case post type didn't match...
		// OMG: THIS IS GETTING SO RIDICULOUS NOW!!!
		if ( $this->post_type !== $post_type ) {
			return;
		}

		foreach ( $this->filter_keys as $key ) {
			$this->by( $post_type, $key );
		}
	}

	/**
	 * Starts filteration process by current key type.
	 *
	 * @param string $post_type Post type key.
	 * @param string $key       Taxonomy/Meta key to use for adding filteration dropdown.
	 *
	 * @return string
	 *
	 * @since 1.0
	 * @since 2.0 Renamed method from `_filter_by_key_type`.
	 */
	private function by( string $post_type, string $key ): string {
		return taxonomy_exists( $key )
			? $this->by_taxonomy( $post_type, $key )
			: $this->by_meta( $post_type, $key );
	}

	/**
	 * Adds taxonomy filter dropdown.
	 *
	 * @param string $post_type     Post type key.
	 * @param string $name Taxonomy key to use for adding filteration dropdown.
	 *
	 * @return string Empty string if something went wrong.
	 *
	 * @since 1.0
	 * @since 2.0 Renamed method name from `_by_taxonomy` and added return value.
	 */
	private function by_taxonomy( string $post_type, string $name ): string {
		// Get the terms of the taxonomy.
		$terms = get_terms(
			array(
				'taxonomy'   => $name,
				'hide_empty' => false,
			)
		);

		// Bail if taxonomy has no terms.
		if ( empty( $terms ) ) {
			return '';
		}

		$tax = get_taxonomy( $name );

		// Category can be added by WordPress by default.
		// So, bail if it is the key that is set for filtering.
		if ( ! $tax || 'category' === $tax->name ) {
			return '';
		}

		// Bail if taxonomy isn't registered to post type.
		// This is to prevent showing of filter dropdown where
		// taxonomy has nothing to do with the current post type.
		if ( ! in_array( $post_type, $tax->object_type, true ) ) {
			return '';
		}

		// Bail if taxonomy can't be used for querying.
		// This is to prevent showing of filter dropdown
		// because filtering fails without query_var enabled.
		if ( false === $tax->query_var ) {
			return '';
		}

		/**
		 *   ╔═══════════════════════════════════════════════════╗
		 *  ║ If we arrive here, then set the taxonomy dropdown.║
		 * ╚═══════════════════════════════════════════════════╝
		 */

		$args = array(
			'option_none_value' => '',
			'hide_empty'        => 0,
			'hide_if_empty'     => false,
			'show_count'        => false, // Coz counts all post types where tax assigned.
			'taxonomy'          => $tax->name,
			'name'              => $name,
			'orderby'           => 'name',
			'hierarchical'      => true,
			'value_field'       => 'slug',
			'selected'          => Factory::is_get( $name ),
			'show_option_none'  => sprintf(
				/* translators: %s The tax label */
				_x( 'All %s', 'taxonomy filter option none', 'tws-cpt-framework' ),
				$tax->label
			),
		);

		/**
		 * WPHOOK: Filter -> Args for taxonomy dropdown.
		 *
		 * @param array $args         The default arguments.
		 * @param string $post_type   The post type name/key.
		 * @param object $tax         The taxonomy object.
		 * @var   (string|int|bool)[] Modified args for dropdown filtering.
		 * @since 1.0
		 * @since 2.0 Renamed tag name from `hzfex_{$name}_taxonomy_filter_args`.
		 */
		$all_args = apply_filters( "tws_{$name}_taxonomy_filter_args", $args, $post_type, $tax );

		return wp_dropdown_categories( $all_args );
	}

	/**
	 * Adds post meta filter dropdown.
	 *
	 * @param string $post_type   Post type key.
	 * @param string $meta_key    Meta key to use for filtering.
	 * @param array  $post_status Post status.
	 *
	 * @return string Empty string if something goes wrong.
	 *
	 * @global wpdb $wpdb The WordPress Database API.
	 * @since 1.0
	 * @since 2.0 Renamed method name from `_by_meta`.
	 */
	private function by_meta( string $post_type, string $meta_key, array $post_status = array() ): string {
		global $wpdb;

		$post_status = empty( $post_status ) ? array( 'publish', 'inherit' ) : $post_status;
		$status      = implode( "', '", $post_status ); // SQL syntax supported imploding.

		/**
		 * WPHOOK: Filter -> Modify SQL query for post meta dropdown.
		 *
		 * @param array $query      The default SQL query. This should be modified using filter.
		 * @param string $post_type The post type name/key.
		 * @var   array             Modify default arguments of post meta for dropdown filtering.
		 * @since 1.0
		 * @since 2.0 Renamed tag name from `hzfex_{$meta_key}_postmeta_query`.
		 * @since 2.0 Shortcircuit query result before making SQL query.
		 */
		$query  = apply_filters( "tws_{$meta_key}_postmeta_query_filter", array(), $post_type );
		$result = $query;

		if ( empty( $query ) ) {
			$result = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare(
					"SELECT DISTINCT {$wpdb->postmeta}.meta_value FROM {$wpdb->postmeta}, {$wpdb->posts} WHERE {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id AND {$wpdb->postmeta}.meta_key = %s AND {$wpdb->posts}.post_status IN ('{$status}') AND {$wpdb->posts}.post_type = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$meta_key,
					$post_type
				)
			);
		}

		/**
		 * Uncomment below $result if want to search in `wp_posts` table too
		 * but highly reecommended not to do so because it can't parse query result
		 * when filtered. Use it just for testing purpose. Possible $meta_key are
		 * `post_title`, `post_name`, etc. that is oviously different for each post.
		 *
		 * $result = !empty($result) ? $result : $this->maybe_by_post($post_type,$meta_key,$status);
		 */

		// Bail if result is empty.
		if ( empty( $result ) ) {
			return '';
		}

		$selected = Factory::is_get( $meta_key, '', false );
		$title    = Factory::make_label( $meta_key );

		$select   = sprintf( '<select class="%1$s" id="%1$s" name="%1$s">', esc_attr( $meta_key ) );
		$options  = '<option value="-1">';
		$options .= sprintf(
			/* translators: %s: The meta or taxonomy title */
			_x( 'All %s', 'meta filter option none', 'tws-cpt-framework' ),
			$title
		);
		$options .= '</option>';

		foreach ( $result as $option ) {
			$is       = selected( $option, $selected, false );
			$options .= sprintf( '<option value="%1$s"%2$s>%1$s</option>', $option, $is );
		}

		/**
		 * WPHOOK: Filter-> Modify option values.
		 *
		 * @param string $options
		 * @param string $selected
		 *
		 * @var string
		 *
		 * @since 1.0
		 * @since 2.0 Renamed tag name from `hzfex_{$meta_key}_query_dropdown_result`.
		 */
		$all_options = apply_filters( "tws_{$meta_key}_query_dropdown_result", $options, $selected );

		$html  = $select . $all_options;
		$html .= '</select>';

		echo trim( $html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		return $html;
	}

	/**
	 * Prepare meta key/value for parsing query from the filter.
	 *
	 * ### Must be used with {@see @method Filter::by()}.
	 *
	 * @param string|string[] $keys Single key as string or an array of filtered keys to use for modifying query.
	 *
	 * @return void|false Modify the query, false if no queried keys found.
	 *
	 * @since 1.0
	 */
	public function parse_query( $keys ): bool {
		// Get all queried keys present in the URL.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$get_all = array_keys( wp_unslash( $_GET ) );

		// Set property value of those URL keys that matches given filter keys.
		$filtered_keys = array_intersect( Factory::to_array( $keys ), $get_all );

		if ( empty( $filtered_keys ) ) {
			return false;
		}

		// Iterate over queried keys and set key/value pair to modify query.
		foreach ( $filtered_keys as $filter ) {
			// All taxonomy keys used for filtering is omitted
			// so query vars are not set by using taxonomy key.
			// Doing so may result to not so any posts in the table.
			// This means only post metas are treated as `query_vars`.
			if ( taxonomy_exists( $filter ) ) {
				continue;
			}

			// Get query key/value pair and set as property value.
			$this->queried_keys[ $filter ] = Factory::is_get( $filter, '-1', false );
		}

		$this->filtered_keys = $filtered_keys;

		// Bail if nothing to parse query for.
		if ( empty( $this->queried_keys ) ) {
			return false;
		}

		// Parse query and show filtered posts on admin post table.
		// Using higher priority to overcome possible overriding.
		add_filter( 'parse_query', array( $this, 'modify' ), 99 );

		return true;
	}

	/**
	 * Modifies the query to work with filtering.
	 *
	 * @param WP_Query $query WordPress query API passed from filter.
	 *
	 * @return WP_Query The modified query with filters set.
	 *
	 * @since 1.0
	 * @since 2.0 Renamed method name from `modify_query_with_filter_keys`.
	 */
	public function modify( WP_Query &$query ): WP_Query {
		// Get current post type page.
		$post_type = Factory::is_get( 'post_type' );

		// Bail early if nothing is being queried or not in current post type page.
		if ( ! is_admin() || ! $query->is_main_query() || $query->get( 'post_type' ) !== $post_type ) {
			$this->parsed_query = $query;

			return $query;
		}

		// Define query vars data.
		$vars               = &$query->query_vars;
		$vars['meta_query'] = array( 'relation' => 'AND' ); // phpcs:ignore

		// Filtering will override query vars of sortable column.
		// So, include sorting order query vars here.
		$order_key = Column::load()->query_vars();
		$orderby   = array_shift( $order_key );

		if ( ! empty( $orderby ) ) {
			$vars['meta_query'][] = $orderby;
		}

		// Iterate over all queries set and define as `query_vars`.
		foreach ( $this->queried_keys as $key => $value ) {
			// Only continue if queried filtering key exists and has valid value.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( empty( $_GET[ $key ] ) || '-1' === $value ) {
				continue;
			}

			// Set query vars.
			$vars['meta_query'][] = array(
				'key'   => $key,
				'value' => $value,
			);
		}

		// Set parsed query object as property.
		$this->parsed_query = $query;

		// Return the modified query.
		return $query;
	}

	/**
	 * Prepare filter dropdown from `{$prefix}_posts` table.
	 *
	 * @param string $post_type   (required) Post type key.
	 * @param string $key         The database table column name (to treat as `meta_key`).
	 * @param string $post_status The post status.
	 *
	 * @return array Array of found data returned from database query.
	 *
	 * @global wpdb $wpdb The WordPress Database API.
	 * @since 1.0
	 * @since 2.0 Renamed method name from `maybe_by_post`.
	 * @since 2.0 Removed filter applied to query result.
	 * @internal This is only for testing purpose and should not be used.
	 * This successfully generates dropdown but can't filter table
	 * because `WP_Query->request` only get result from `wp_postmeta` database table.
	 * So, obviously it can't modify query vars that is the result of `wp_post` table.
	 */
	private function maybe_by_post( string $post_type, string $key, string $post_status ): array {
		global $wpdb;

		// phpcs:disable
		return $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT {$key} FROM {$wpdb->posts} WHERE {$wpdb->posts}.post_status IN ({$post_status}) AND {$wpdb->posts}.post_type = %s",
				$post_type
			)
		);
		// phpcs:enable
	}
}
