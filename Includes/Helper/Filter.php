<?php
/**
 * TheWebSolver\Core\Helper\CPT_Filter_List class
 * 
 * @package TheWebSolver\Core\CPT_Framework\Class\API
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
 */

namespace TheWebSolver\Core\Helper;

/**
 * CPT_Filter_List class for filtering admin list table.
 * 
 * @api
 */
final class CPT_Filter_List {
    /**
     * Class instance.
     *
     * @var CPT_Filter_List
     * 
     * @since 1.0
     * 
     * @static
     * 
     * @access private
     */
    private static $_instance;

    /**
     * The filtering keys.
     * 
     * Keys can be of taxonomies or custom metas.
     *
     * @var string[]
     * 
     * @since 1.0
     * 
     * @access public
     */
    private static $_filter_keys;

    /**
     * The post type key for which filtering to be added.
     * 
     * @var string
     * 
     * @since 1.0
     * 
     * @static
     * 
     * @access public
     */
    public static $post_type;

    /**
     * Keys present in URL for querying when filtering.
     * 
     * These keys are what will be used for defining query keys.\
     * {@see @property CPT_Filter_List::$queried_keys }\
     * These keys must be from `{$prefix}_postmeta` database table\
     * and not from `{$prefix}_posts` table as query may not\
     * be parsed using keys from main post table.
     *
     * @var string[]
     * 
     * @since 1.0
     * 
     * @access public
     */
    public static $filtered_keys = [];

    /**
     * Meta_key/meta_value pair that will be used for parsing query.
     *
     * @var string[]
     * 
     * @since 1.0
     * 
     * @static
     * 
     * @access public
     */
    public static $queried_keys = [];

    /**
     * Modified query with queried keys.
     * 
     * The modified query object after passing filtered post meta key/values.
     *
     * @var \WP_Query
     * 
     * @since 1.0
     * 
     * @static
     * 
     * @access public
     */
    public static $parsed_query;

    /**
     * Start filtering process for given post type.
     * 
     * This will ensure class is instantiated only once, and\
     * only on the post type screen where filtering is to be done.
     *
     * @param string $post_type **required** Post Type key.
     * 
     * @return CPT_Filter_List|false Initiate filtering if keys set, else false.
     * 
     * @since 1.0
     * 
     * @static
     * 
     * @access public
     */
    public static function for( string $post_type ) {
        // Get filter keys.
        $filters = tws_cpt()->plugin()->get_post_type_filters();

        // Bail early if fitering is not set for the current post type.
        if ( ! array_key_exists( $post_type, $filters ) ) {
            return false;
        }

        // Get filtering keys of the current post type and set property values.
        self::$_filter_keys = $filters[$post_type];
        self::$post_type    = $post_type;

        if( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        // If everything is correct, instantiate.
        return self::$_instance;
    }

    /**
     * Adds filter dropdown.
     * 
     * #### It's better to use API {@see filter_post_list_by()} as below instead of using this method:
     * ```
     * use function TheWebSolver\Register\filter_post_list_by;
     * filter_post_list_by( string $post_type, $keys = '' );
     * ```
     * 
     * #### To know more about how to use: {@see @example - _below_}
     * 
     * **Why Use This?**
     * * Can be useful, in some cases, to run this method if not using API.
     * 
     * **How to use this?**
     * * Hook with `admin_init` to parse query.
     * * Hook with `restrict_manage_posts` to add filter.
     *
     * @param string $post_type **required** Post type key.
     * @param string $key **required** Taxonomy/Post Meta key to use for adding filteration dropdown.
     * 
     * @return string|void The HTML dropdown list.
     * 
     * @since 1.0
     * 
     * @example usage:
     * ```
     * use TheWebSolver\Core\Helper\CPT_Filter_List;
     * 
     * // Add filter dropdown.
     * add_action('restrict_manage_posts', function () {
     *      CPT_Filter_List::by('post_type_key', 'post_filter_key');
     *      CPT_Filter_List::by('post_type_key', 'another_filter_key');
     * });
     * // Then parse query to return only post that match filter key.
     * // Use it with admin hooks like `admin_init` hook.
     * add_action( 'admin_init', function(){
     *      CPT_Filter_List::parse_query(['post_filter_key','another_filter_key']);
     * });
     * ```
     * 
     * @static
     * 
     * @access public
     */
    public static function by( string $post_type, string $key ) {
        // Bail early if can't add to the current post type.
        if( ! did_action( 'restrict_manage_posts' ) || ! self::_can_add_to( $post_type ) ) {
            return;
        }

        // Filters already been set to global vars.
        $filters = tws_cpt()->plugin()->get_post_type_filters();

        // Get all filter keys set for the current post type, if any.
        $_is_set = isset( $filters[$post_type] ) ? (array) $filters[$post_type] : [];

        // Bail if filter keys is already set to global vars with API.
        // This to prevent duplication of same filter being added using different methods.
        if( in_array( $key, $_is_set, true ) ) {
            return;
        }

        // Add new filters for the current post type.
        self::_filter_by_key_type( $post_type, $key );

        // Save the unused filter key to global vars.
        CPT_Factory::set_post_type_filters( $post_type, $key );
    }

    /**
     * Private constructor to prevent direct instantiation.
     * 
     * @param string $post_type **required** Post type key.
     * @param string|string[] $keys **required** Taxonomy/Post Meta keys to use for adding filteration dropdown.
     * 
     * @since 1.0
     * 
     * @access private
     */
    private function __construct() {
        self::parse_query( self::$_filter_keys );

        // Add filter dropdown on admin post table.
        add_action( 'restrict_manage_posts', [ $this ,'with_keys' ] );
    }

    /**
     * Add and display filter dropdown on Post List Table.
     *
     * @param string $post_type The Post Type Key passed from action hook.
     * 
     * @return bool
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function with_keys( string $post_type ) {
        // Not necessary to check post type as this won't run on incorrect post type screen.
        // But as we have $post_type param, we are checking here too.
        // So, bail early in case post type didn't match...
        // OMG: THIS IS GETTING SO RIDICULOUS NOW!!!
        if( self::$post_type !== $post_type ) {
            return;
        }

        // Bail if filter key is set empty.
        if( empty( self::$_filter_keys) ) {
            return;
        }

        // Remove any duplication on filter keys.
        $filter_keys = array_unique( self::$_filter_keys );

        foreach( $filter_keys as $key ) {
            self::_filter_by_key_type( $post_type, $key );
        }
    }

    /**
     * Start filteration process by current key type.
     *
     * @param string $post_type **required** Post type key.
     * @param string $key Taxonomy/Post Meta key to use for adding filteration dropdown.
     * 
     * @return string|void|null
     * 
     * @since 1.0
     * 
     * @static
     * 
     * @access public
     */
    private static function _filter_by_key_type( string $post_type, string $key ) {
        if( taxonomy_exists( $key ) ) {
            self::_by_taxonomy( $post_type, $key );
        } else {
            self::_by_meta( $post_type, $key );
        }
    }

    /**
     * Adds taxonomy filter dropdown.
     *
     * @param string $post_type **required** Post type key.
     * @param string $name Taxonomy key to use for adding filteration dropdown.
     * 
     * @return string|void The HTML dropdown list.
     * 
     * @since 1.0
     * 
     * @static
     * 
     * @access public
     */
    private static function _by_taxonomy( string $post_type, string $name ) {
        // Get the terms of the taxonomy.
        $terms = get_terms( [
            'taxonomy'     => $name,
            'hide_empty'   => false,
        ] );

        // Bail if taxonomy has no terms.
        if( empty( $terms ) ) {
            return;
        }

        $tax = get_taxonomy( $name );

        // Category can be added by WordPress by default.
        // So, bail if it is the key that is set for filtering.
        if( $tax->name === 'category' ) {
            return;
        }

        // Bail if taxonomy isn't registered to post type.
        // This is to prevent showing of filter dropdown where
        // taxonomy has nothing to do with the current post type.
        if( ! in_array( $post_type, $tax->object_type, true ) ) {
            return;
        }

        // Bail if taxonomy can't be used for querying.
        // This is to prevent showing of filter dropdown
        // because filtering fails without query_var enabled.
        if( false === $tax->query_var ) {
            return;
        }

        /******************************************************
          ╔═══════════════════════════════════════════════════╗
         ║ If we arrive here, then set the taxonomy dropdown.║
        ╚═══════════════════════════════════════════════════╝
        ***************************************************/
    
        // Saves HTML select dropdown value from taxonomy term already been selected.
        $selected = isset( $_GET[$name] ) && $_GET[$name] !== '' ? $_GET[$name] : '';

        $args = [
            'option_none_value' => '',
            'hide_empty'        => 0,
            'hide_if_empty'     => false,
            'show_count'        => false, // coz counts all post types where tax assigned
            'taxonomy'          => $tax->name,
            'name'              => $name,
            'orderby'           => 'name',
            'hierarchical'      => true,
            'show_option_none'  => _x( "All {$tax->label}", "taxonomy filter option none", HZFEX_TEXTDOMAIN ),
            'value_field'       => 'slug',
            'selected'          => $selected
        ];

        /**
         * WPHOOK: Filter -> Modify args for taxonomy dropdown.
         * 
         * @param array $args The default arguments. This should be modified using filter.
         * @param string $post_type The post type name/key.
         * @param object $tax The taxonomy object.
         * 
         * @var array Modify default arguments of taxonomy for dropdown filtering.
         * 
         * @since 1.0
         */
        $args = apply_filters( "hzfex_{$name}_taxonomy_filter_args", $args, $post_type, $tax );

        wp_dropdown_categories( $args );
    }

    /**
     * Adds post meta filter dropdown.
     *
     * @param string $post_type **required** Post type key.
     * @param string $meta_key Meta key to use for filtering.
     * @param array $post_status Post status.
     * 
     * @return string|null The HTML dropdown list, null if empty query.
     * 
     * @since 1.0
     * 
     * @static
     * 
     * @access public
     */
    private static function _by_meta( string $post_type, string $meta_key, array $post_status = ['publish'] ) {
        global $wpdb;

        $status = implode('", "', $post_status); // SQL syntax supported imploding.
        
        /* Translators -
        %1$s: Post Meta
        %2$s: Posts
        %3$s: Meta Key
        %4$s: Post Status
        %5$s: Current Post Type
        %6$s: Meta Key to orderby
        */
        $query = $wpdb->prepare('
            SELECT DISTINCT pm.meta_value FROM %1$s pm
            LEFT JOIN %2$s p ON p.ID = pm.post_id
            WHERE pm.meta_key = "%3$s"
            AND p.post_status IN ("%4$s")
            AND p.post_type = "%5$s"
            ORDER BY "%6$s"',
            $wpdb->postmeta,
            $wpdb->posts,
            $meta_key,
            $status,
            $post_type,
            $meta_key
        );

        /**
         * WPHOOK: Filter -> Modify SQL query for post meta dropdown.
         * 
         * @param array $query The default SQL query. This should be modified using filter.
         * @param string $post_type The post type name/key.
         * 
         * @var string Modify default arguments of post meta for dropdown filtering.
         * 
         * @since 1.0
         */
        $query = apply_filters( "hzfex_{$meta_key}_postmeta_query", $query, $post_type );

        $result = $wpdb->get_col( $query );

        /**
         * NOTE: -
         * @internal Uncomment below $result if want to search in `wp_posts` table too
         * but highly reecommended not to do so because it can't parse query result
         * when filtered. Use it just for testing purpose. Possible $meta_key are
         * `post_title`, `post_name`, etc. that is oviously different for each post.
         */
        // $result = ! empty( $result ) ? $result : self::_maybe_by_post( $post_type, $meta_key, $status );

        // Bail if result is empty.
        if( empty( $result ) ) {
            return;
        }

        // Saves HTML select dropdown value from taxonomy already been selected.
        $selected = isset( $_GET[$meta_key] ) && $_GET[$meta_key] !== '' ? $_GET[$meta_key] : '';

        // Convert slug to human friendly name to show as all option.
        $title = ucwords( str_replace( ['-', '_'], ' ', $meta_key ) );

        $options[] = sprintf(
            '<option value="-1">%1$s</option>',
            _x( "All $title", "meta filter option none", HZFEX_TEXTDOMAIN )
        );

        foreach( $result as $option ) {
            $is_selected = $option == $selected ? ' selected="selected"' : '';
            /* Translators -
            %1$s: option as value
            %2$s: if is a selected option
            %3$s: opton as name
            */
            $options[] = sprintf(
                '<option value="%1$s"%2$s>%3$s</option>',
                esc_attr( $option ),
                $is_selected,
                $option
            );
        }

        /**
         * WPHOOK: Filter-> Modify how the options is outputted.
         * 
         * @param string $options
         * @param string $selected
         * 
         * @var string[]
         * 
         * @since 1.0
         */
        $options = apply_filters( "hzfex_{$meta_key}_query_dropdown_result", $options, $selected );

        // Outputs the select dropdown filtering.
        echo '<select class="" id="'.$meta_key.'" name="'.$meta_key.'">';
        echo join( "\n", $options );
        echo '</select>';
    }

    /**
     * Prepare meta key/value for parsing query from the filter.
     * 
     * ### Must be used with {@see @method CPT_Filter_List::by()}.
     * 
     * @param string|string[] $keys Single key as string or an array of filtered keys to use for modifying query.
     *
     * @return void|false Modify the query, false if no queried keys found.
     * 
     * @since 1.0
     * 
     * @static
     * 
     * @access public
     */
    public static function parse_query( $keys ) {
        // Get all queried keys present in the URL.
        $get_all            = array_keys( $_GET );
        $keys               = ! is_array( $keys ) ? [$keys] : (array) $keys;

        // Set property value of those URL keys that matches given filter keys.
        self::$filtered_keys = array_intersect( $keys, $get_all );
        
        if( ! empty( self::$filtered_keys ) ) {
            // Iterate over queried keys and set key/value pair to modify query.
            foreach( self::$filtered_keys as $filter ) {
                // All taxonomy keys used for filtering is omitted
                // so query vars are not set by using taxonomy key.
                // Doing so may result to not so any posts in the table.
                // This means only post metas are treated as `query_vars`.
                if( taxonomy_exists( $filter ) ) {
                    continue;
                }

                // Get query key/value pair and set as property value.
                self::$queried_keys[$filter] = isset( $_GET[$filter] ) ? $_GET[$filter] : '-1';
            }
        }

        // Bail if nothing to parse query for.
        if( empty( self::$queried_keys ) ) {
            return false;
        }

        // Parse query and show filtered posts on admin post table.
        // Using higher priority to overcome possible overriding.
        add_filter( 'parse_query', [__CLASS__, 'modify_query_with_filter_keys'], 99 );
    }
    
    /**
     * Modify the query to work with filtering.
     *
     * @param \WP_Query $query WordPress query API passed from filter.
     * 
     * @return \WP_Query The modified query with filters set.
     * 
     * @since 1.0
     * 
     * @static
     * 
     * @access public
     */
    public static function modify_query_with_filter_keys( \WP_Query &$query ): \WP_Query {
        // Get current post type page.
        $post_type  = isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';

        // Bail early if nothing is being queried or not in current post type page.
        if( ! is_admin() || ! $query->is_main_query() || $query->get( 'post_type' ) !== $post_type ) {
            self::$parsed_query = $query;
            return $query;
        }

        // Define query vars data.
        $vars               = &$query->query_vars;
        $vars['meta_query'] = ['relation' => 'AND'];

        // Filtering will override query vars of sortable column.
        // So, include sorting order query vars here.
        $orderby = array_shift( CPT_Column::$query_vars );
        if( ! empty( $orderby ) ) {
            $vars['meta_query'][] = $orderby;
        }

        // Iterate over all queries set and define as `query_vars`.
        foreach( self::$queried_keys as $key => $value ) {
            // Only continue if queried filtering key exists and has valid value.
            if( empty( $_GET[$key] ) || $value === '-1' ) {
                continue;
            }

            // Set query vars.
            $vars['meta_query'][] = [
                'key'   => $key,
                'value' => $value,
            ];
        }

        // Set parsed query object as property.
        self::$parsed_query = $query;

        // Return the modified query.
        return $query;
    }

    /**
     * Add filter check.
     * 
     * For validating post type when using static method for adding filteration.
     *
     * @param string $post_type Post type key where filtering dropdown to add.
     * 
     * @return bool True if screen and post type matches, false if not.
     * 
     * @since 1.0
     * 
     * @static
     * 
     * @access public
     */
    private static function _can_add_to( string $post_type ) : bool {
        global $current_screen;

        // Bail if not on same screen as post type.
        if( $current_screen->post_type === $post_type ) {
            return true;
        }
        return false;
    }

    /**
     * Prepare filter dropdown from `{$prefix}_posts` table.
     *
     * @param string $post_type **required** Post type key.
     * @param string $key The database table column name (to treat as `meta_key`)
     * @param string $post_status The post status
     * 
     * @return array Array of found data returned from database query.
     * 
     * @since 1.0
     * 
     * @internal This is only for testing purpose and should not be used.
     * This successfully generates dropdown but can't filter table
     * because `WP_Query->request` only get result from `wp_postmeta` database table.
     * So, obviously it can't modify query vars that is the result of `wp_post` table.
     * 
     * @static
     * 
     * @access private
     */
    private static function _maybe_by_post( string $post_type, string $key, string $post_status ): array {
        global $wpdb;

        /* Translators -
        %1$: Post Key
        %2$s: Posts
        %3$s: Post Status
        %4$s: Post Type Key
        %5$s: Post Key
        */
        $query = $wpdb->prepare('
            SELECT DISTINCT p.%1$s FROM %2$s p 
            WHERE p.post_status IN ("%3$s") 
            AND p.post_type = "%4$s" 
            ORDER BY p.%5$s',
            $key,
            $wpdb->posts,
            $post_status,
            $post_type,
            $key
        );

        /**
         * WPHOOK: Filter -> Modify SQL query for post key dropdown.
         * 
         * @param array $query The default SQL query. This should be modified using filter.
         * @param string $post_type The post type name/key.
         * 
         * @var string Modify default arguments of post key for dropdown filtering.
         * 
         * @since 1.0
         */
        $query = apply_filters( "hzfex_{$key}_query_dropdown_result", $query, $post_type );

        return $wpdb->get_col( $query );
    }
}