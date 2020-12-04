<?php
/**
 * TheWebSolver\Core\Helper\CPT_Column Class.
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
 * CPT_Column class
 * 
 * Manage post_type\taxonomy admin table columns.
 * 
 * @api
 */
final class CPT_Column {
    /**
     * Table columns from API parameter.
     *
     * @var array
     * 
     * @since 1.0
     * 
     * @static
     * 
     * @access public
     */
    public static $columns = [];

    /**
     * Modified columns after ordering by priority.
     * 
     * @var string[]
     * 
     * @since 1.0
     * 
     * @static
     * 
     * @access public
     */

    public static $modified = [];

    /**
     * Query value for `orderby` from sorted column.
     *
     * @var string
     * 
     * @since 1.0
     * 
     * @static
     * 
     * @access public
     */
    public static $orderby;

    /**
     * Meta query `query_vars` value from sorted column.
     *
     * @var string[]
     * 
     * @since 1.0
     * 
     * @static
     * 
     * @access public
     */
    public static $query_vars = [];

    /**
     * Modified query object from query vars.
     * 
     * {@see @property CPT_Column::$query_vars }
     *
     * @var \WP_Query|\WP_Term_Query
     * 
     * @since 1.0
     * 
     * @static
     * 
     * @access public
     */
    public static $parsed_query;

    /**
     * Added columns.
     *
     * @var string[] Column slug as key, label as value.
     * 
     * @since 1.0
     * 
     * @access public
     */
    public $add = [];

    /**
     * Removed columns.
     *
     * @var string[] Column slugs in an array.
     * 
     * @since 1.0
     * 
     * @access public
     */
    public $remove = [];

    /**
     * Callback function names for displaying column content.
     *
     * @var string[] Column slug as key, callback function as value.
     * 
     * @since 1.0
     * 
     * @access public
     */
    public $callback = [];

    /**
     * Columns that are sortable.
     *
     * @var string[] Column slugs in an array.
     * 
     * @since 1.0
     * 
     * @access public
     */
    public $sortable = [];

    /**
     * Columns that are unsortable.
     *
     * @var string[] Column slugs in an array.
     * 
     * @since 1.0
     * 
     * @access public
     */
    public $unsortable = [];

    /**
     * An array of columns with their ordering priority.
     *
     * @var array Column slug as key, priority number as value.
     * 
     * @since 1.0
     * 
     * @access public
     */
    public $priority;

    /**
     * The current page where to manage columns.
     * 
     * Must be either **post_type** or **taxonomy**.
     *
     * @var string
     * 
     * @since 1.0
     * 
     * @access public
     */
    public $type;

    /**
     * Post Type/Taxonomy key.
     *
     * @var string
     * 
     * @since 1.0
     * 
     * @access public
     */
    public $key;

    /**
     * Sets and instantiates CPT_Column class.
     *
     * @param string $key **required** Post Type Key/Taxonomy Key of the current page.
     * @param bool $is_post **optional** Whether is post type page. Defaults to `true`.
     * 
     * @return CPT_Column
     * 
     * @since 1.0
     * 
     * @static
     * 
     * @access public
     */
    public static function for( string $key, bool $is_post = true ): CPT_Column {
        static $instance;

        // Get current type and column data.
        if( $is_post ) {
            $type   = 'post_type';
            $data   = tws_cpt()->plugin()->get_post_type_columns();
        } else {
            $type   = 'taxonomy';
            $data   = tws_cpt()->plugin()->get_taxonomy_columns();
        }
    
        // Set property value for the current admin table.
        self::$columns  = array_key_exists( $key, $data ) ? $data[$key] : [];

        if( ! is_a( $instance, get_class() ) ) {
            $instance = new self( $type, $key );
        }

        // If everything is correct, instantiate class.
        return $instance;
    }

    /**
     * Private constructor to prevent direct instantiation.
     * 
     * @param string $type **required** Accepted values are `post_type|taxonomy`.
     * @param string $key **required** Post type key|Taxonomy key.
     * 
     * @since 1.0
     * 
     * @access private
     */
    private function __construct( string $type, string $key ) {
        // Set property values from parameters.
        $this->type = $type;
        $this->key  = $key;
    }

    /**
     * Prepare the registering type admin table column.
     * 
     * @param string[]  $column   **required** An array of columns data. {@see @example _below_}
     * 
     * @return CPT_Column
     * 
     * @since 1.0
     * 
     * @example usage:
     * 
     * ```
     * $columns = [
     *      'column_key' => [
     *          // Set as human friendly table header name.
     *          'label'      => __( 'Column label', HZFEX_TEXTDOMAIN ),
     * 
     *          // Set `sortable` value to `true` if `meta_key_used_in_callback` is same as `column_key` and other as default.
     *          // Set `sortable` value to `false` to disable sorting of the column.
     *          // Set `sortable` value to `meta_key_used_in_callback` (as `string`) if other as default.
     *          // Set `sortable` value to `array` as shown below if value is different than default:
     *          // `key`     - meta key used in callback function. Defaults to `column_key`.
     *          // `type`    - meta type. Defaults to "CHAR".
     *          // `compare` - compare operator. Defaults to "=".
     *          'sortable'  => [
     *              'key'       => 'column_key',
     *              'type'      => 'CHAR',
     *              'compare'   => '=',
     *          ],
     * 
     *          // Function to display content in the `column_key` column.
     *          // Passed parameters: `$column` and `$post_id`.
     *          'callback'   => 'function_name_that_display_content',
     *          'priority'   => 3, // "0" will be first, "1" will be second, and so on. If want to order all columns properly, all columns must be set with this key/value.
     *      ],
     *      // Add another column data.
     * ];
     * 
     * // Alternatively, if don't need to set any array values,
     * // use as shown below: (usually for default columns).
     * $columns = ['custom_slug_one', 'custom_slug_two'];
     * ```
     * 
     * @access public
     */
    public function prepare(): CPT_Column {
        // Bail early if no columns set.
        if( empty( self::$columns ) ) {
            return $this;
        }

        // Iterate over columns and set property value.
        foreach ( self::$columns as $column => $data ) :
            // If data is set as string, it must be the column key.
            if( is_string( $data ) ) :
                // Set label value.
                $label = $this->_create_label_from( $data );

                // Set column key and label as value of the property.
                $this->add[$data] = (string) $label;

                // Only continue if $data is set as array
                // which it is almost in every case.
                continue;
            endif;

            /*********************************************************
              ╔══════════════════════════════════════════════════════╗
             ║ If we arrive here, then array data given, work on it.║
            ╚══════════════════════════════════════════════════════╝
            ******************************************************/

            // If $data has sortable key, add sort/unsort feature to the column.
            if( isset( $data['sortable'] ) ) :
                // If array given, prepare it for query vars.
                if( is_array( $data['sortable'] ) ) {
                    $key        = isset( $data['sortable']['key'] ) ? (string) $data['sortable']['key'] : (string) $column;
                    $type       = isset( $data['sortable']['type'] ) ? (string) $data['sortable']['type'] : 'CHAR';
                    $compare    = isset( $data['sortable']['compare'] ) ? (string) $data['sortable']['compare'] : '=';

                    // Set property value.
                    $this->sortable[$column] = [
                        'key'      => $key,
                        'type'     => $type,
                        'compare'  => $compare,
                    ];
                }

                // Set column key as key and sortable value as value of the property.
                if( is_string( $data['sortable'] ) ) {
                    $this->sortable[$column] = $data['sortable'];
                }

                // If bool given, sorting is either enabled or disabled.
                if( is_bool( $data['sortable'] ) ) {
                    // If set to true, sorting must be done using `column_slug`.
                    // Which means `column_slug` must be same as `meta_key`
                    // that is used in callback function for displaying the content.
                    if( true === $data['sortable'] ) {
                        $this->sortable[$column] = $column;
                    }
                    
                    // If set to false, sorting must be disabled for the column.
                    else {
                        $this->unsortable[] = $column;
                    }
                }
            endif;

            // Columns to add.
            if( isset( $data['label'] ) && ! empty( $data['label'] ) ) {
                $this->add[$column] = (string) $data['label'];
            } else {
                $this->add[$column] = $this->_create_label_from( $column );
            }

            // This is for custom columns with contents
            // fetched from callback function.
            if( isset( $data['callback'] ) && is_callable( $data['callback'] ) ) {
                $this->callback[$column] = (string) $data['callback'];
            }

            // Columns position.
            if( isset( $data['priority'] ) && ! empty( $data['priority'] ) ) {
                $this->priority[$column] = (int) $data['priority'];
            }
        endforeach;

        $sorted_key     = isset( $_GET['orderby'] ) ? (string) $_GET['orderby'] : '';

        // Trim if sorted column key is taxonomy.
        // coz it can't be treated as query vars.
        $is_tax         = 0 === strpos( $sorted_key, 'taxonomy-', 0 ) && 0 === strrpos( $sorted_key, 'taxonomy-', 0 ) ? true : false;
        self::$orderby  = $is_tax ? '' : $sorted_key;

        return $this;
    }

    /**
     * Apply WordPress action and filter hooks to manage admin list column.
     *
     * @return void
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function apply() {
        if( method_exists( $this, "__manage_{$this->type}_columns" ) ) {
            // Call respective registering type method to manage admin table columns.
            call_user_func( [$this, "__manage_{$this->type}_columns"] );

            // Set/unset columns for sorting of admin table list.
            //  Common callback method is used for both types.
            add_filter( "manage_edit-{$this->key}_sortable_columns", [$this, 'maybe_sort'] );
        }
    }

    /**
     * Manage post type admin table columns.
     * 
     * @return void
     * 
     * @since 1.0
     * 
     * @access private
     */
    private function __manage_post_type_columns() {
        add_filter( "manage_{$this->key}_posts_columns", [$this, 'modify'], 10, 1 );
        add_filter( "manage_{$this->key}_posts_custom_column", [$this, 'display_post_content'], 10, 2 );

        // We set priority according to the query vars set by filtering
        // because filter must have higher piority than sort... of course!!!
        $priority = empty( CPT_Filter_List::$queried_keys ) ? 100 : 98;
        add_filter( 'parse_query', [$this, 'parse_query'], $priority );
    }

    /**
     * Manage taxonomy admin table columns.
     * 
     * @return void
     * 
     * @since 1.0
     * 
     * @access private
     */
    private function __manage_taxonomy_columns() {
        add_filter( "manage_edit-{$this->key}_columns", [$this, 'modify'], 10, 1 );
        add_filter( "manage_{$this->key}_custom_column", [$this, 'display_term_content'], 10, 3 );
        add_action( 'parse_term_query', [$this, 'parse_term_query'] );
    }

    /**
     * Display custom content for post type custom column from callback.
     * 
     * @param string $column The column key passed from filter.
     * @param int $post_id The post ID passed from filter.
     * 
     * @return mixed The content from callback, else empty string.
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function display_post_content( string $column, int $post_id ) {
        // Set empty string as value if no callback set.
        if( ! isset( $this->callback[$column] ) ) {
            return '';
        }

        // Callback function that displays the column data.
        return call_user_func_array( $this->callback[$column], [$column, $post_id] );
    }

    /**
     * Display custom content for taxonomy custom column from callback.
     * 
     * @param string $content The column blank content passed from filter.
     * @param string $column The column key passed from filter.
     * @param int $term_id The term ID passed from filter.
     * 
     * @return mixed The content from callback, else empty string.
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function display_term_content( string $content, string $column, int $term_id ) {
        // Bail early if no callback set.
        if( ! isset( $this->callback[$column] ) ) {
            return $content;
        }

        // Callback function that displays the column data.
        return call_user_func_array( $this->callback[$column], [$content, $column, $term_id] );
    }

    /**
     * Make columns sortable/unsortable.
     * 
     * @param string[] $columns Default columns set for sorting.
     * 
     * @return string[] Final column for sorting.
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function maybe_sort( array $columns ): array {
        if( ! empty( $this->unsortable ) ) {
            // Iterate over all unsortable columns and unset'em from defaults.
            foreach( $this->unsortable as $unset ) {
                unset( $columns[$unset] );
            }
        }
        if( ! empty( $this->sortable ) ) {
            // Iterate over all sortable columns and append new columns to defaults.
            foreach( $this->sortable as $column => $set ) {
                $columns[$column] = is_array( $set ) ? (string) $set['key'] : (string) $set;
            }
        }
        return $columns;
    }

    /**
     * Change post type admin table rows order by parsing query data.
     * 
     * @param \WP_Query $query The WP_Query API class
     * 
     * @return void
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function parse_query( \WP_Query &$query ): \WP_Query {
        // Get current post type page.
        $post_type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';

        // Bail early if not in post type admin screen or not the main query.
        if( ! is_admin() || ! $query->is_main_query() || $query->get( 'post_type' ) !== $post_type || $post_type !== $this->key ) {
            return $query;
        }

        // Get the meta key that is set for sorting from query orderby parameter.
        $meta_key       = $query->get( 'orderby' );

        // Bail if column is not sortable with the query set.
        // All taxonomy keys used for filtering is also omitted
        // so query vars are not set by using taxonomy key.
        // Doing so may result to not so any posts in the table.
        // This means only post metas are treated as `query_vars`.
        if ( false === $this->_is_sortable_by( $meta_key ) ) {
            return $query;
        }

        // Return the parsed query vars.
        return $this->_get_parsed_query_with( $query, $meta_key );
    }

    /**
     * Change taxonomy admin table row orders by parsing query data.
     * 
     * @param \WP_Term_Query $query The WP_Term_Query API class
     * 
     * @return void
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function parse_term_query( \WP_Term_Query $query ): \WP_Term_Query {
        // Get current taxonomy page.
        $taxonomy = isset( $_GET['taxonomy'] ) ? $_GET['taxonomy'] : '';

        // Bail early if not in taxonomy type admin screen.
        if( ! is_admin() || ! in_array( $taxonomy, $query->query_vars['taxonomy'] ) || $taxonomy !== $this->key ) {
            return $query;
        }

        // Get the meta key that is set for sorting from query orderby parameter.
        $meta_key = isset( $_GET['orderby'] ) ? $_GET['orderby']: '';

        // Bail if column is not sortable with the query set.
        if ( false === $this->_is_sortable_by( $meta_key ) ) {
            return $query;
        }

        // Return the parsed query vars.
        return $this->_get_parsed_query_with( $query, $meta_key );
    }

    /**
     * Set the query vars.
     *
     * @param \WP_Query|\WP_Term_Query $query The query object passed as reference
     * @param string $meta The meta key
     * 
     * @return \WP_Query|\WP_Term_Query The modified query.
     * 
     * @since 1.0
     * 
     * @access private
     */
    private function _get_parsed_query_with( object &$query, string $meta ): object {
        // Define query vars data.
        $vars               = &$query->query_vars;
        $vars['meta_query'] = [];

        // Set the meta key and meta value query type.
        if ( is_string( $meta ) ) {
            $key        = $meta;
            $type       = 'CHAR';
            $compare    = '=';
        } else {
            $key        = $meta['key'];
            $type       = $meta['type'];
            $compare    = $meta['compare'];
        }

        // Set query vars.
        $vars['meta_query'][] = [
            'key'       => $key,
            'orderby'   => 'value',
            'type'      => $type,
            'compare'   => $compare,
        ];

        // Set query vars as property.
        self::$query_vars = $vars['meta_query'];

        // Set parsed query object as property.
        self::$parsed_query = $query;

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
     * 
     * @access public
     */
    public function modify( array $columns ): array {
        // Add columns.
        if ( ! empty( $this->add ) ) {
            foreach( $this->add as $key => $label ) {
                $columns[$key] = $label;
            }
        }

        // Remove columns.
        if ( ! empty( $this->remove ) ) {
            foreach( $this->remove as $key ) {
                unset( $columns[$key] );
            }
        }

        // Get columns order set from API parameter.
        $get_priority       = $this->priority;

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
         */
        // $get_priority       = array_intersect( $this->priority, $default_priority );

        // If no priority is set, then return columns in old order.
        if( is_null( $get_priority ) || empty( $get_priority ) ) {
            return $columns;
        }

        // Generate columns order by their default position.
        $default_priority   = array_flip( array_keys( $columns ) );

        // Replace default order with those from API parameter.
        $new_priority       = array_replace( $default_priority, $get_priority );

        // Finally sort columns by new order number.
        asort( $new_priority );
        
        foreach ( $new_priority as $key => $value ) {
            // Set new columns.
            $new_columns[$key] = $columns[$key];

            // Remove old columns.
            unset( $columns[$key] );
        }

        // Set the modified columns as property value.
        self::$modified = $new_columns;

        // Return the modified column and use these to set columns.
        return $new_columns;
    }

    /**
     * Check if column has sorting feature set with the meta key.
     *
     * @param string $meta_key The meta key used for querying.
     * 
     * @return string|array|false String or array of data if sorting key exists, else false.
     * 
     * @since 1.0
     * 
     * @access public
     */
    private function _is_sortable_by( string $meta_key ) {
        // Bail early if no sorting set for the type.
        if( empty( $this->sortable ) ) {
            return false;
        }

        // True if meta key and column key is same.
        if( array_key_exists( $meta_key, $this->sortable ) ) {
            return self::$orderby === '' ? false : $this->sortable[$meta_key];
        }

        $sortable = false;
        foreach( $this->sortable as $column => $data ) {
            // True if meta key is set as sortable value.
            if( is_string( $data ) && $data === $meta_key ) {
                $sortable = self::$orderby === '' ? false : (string) $data;

                // Only continue if not string.
                continue;
            }

            // True if in array, meta key is set as sortable value of the `key` index.
            if( is_array( $data ) && isset( $data['key'] ) && $data['key'] === $meta_key ) {
                $sortable = self::$orderby === '' ? false : (array) $data;
            }
        }

        // False if meta key is not set for sorting.
        return $sortable;
    }

    /**
     * Create human readable label from given string.
     *
     * @param string $string
     * 
     * @return string
     * 
     * @since 1.0
     * 
     * @access private
     */
    private function _create_label_from( string $string ): string {
        return str_replace(['_', '-'], ' ', ucwords( $string ) );
    }
}