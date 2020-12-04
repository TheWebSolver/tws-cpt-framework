<?php
/**
 * TheWebSolver\Core\Cpt\Plugin class.
 * 
 * Handles plugin initialization.
 * 
 * @package TheWebSolver\Core\CPT_Framework\Class
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

namespace TheWebSolver\Core\Cpt;

use TheWebSolver\Core\Helper\CPT_Column;
use TheWebSolver\Core\Helper\CPT_Filter_List;

/**
 * Plugin class.
 */
final class Plugin {
    /**
     * Plugin args.
     *
     * @var array
     * 
     * @since 1.0
     * 
     * @access public
     */
    public $args;

    /**
     * Boot framework.
     *
     * @return Plugin
     * 
     * @since 1.0
     * 
     * @static
     * 
     * @access public
     */
    public static function boot(): Plugin {
        static $instance;
        if( ! is_a( $instance, get_class() ) ) {
            $instance = new self();
            $instance->init();
        }
        return $instance;
    }

    /**
     * Include files, Initialize WordPress actions and hooks.
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function init() {
        // Set global vars.
        $this->get_post_types();
        $this->get_taxonomies();
        $this->get_post_type_filters();
        $this->get_post_type_columns();
        $this->get_taxonomy_columns();
    
        // Set args.
        $this->args = [
            'id'        => basename( HZFEX_CPT_BASENAME, '.php' ),
            'name'      => HZFEX_CPT,
            'version'   => HZFEX_CPT_VERSION,
            'activated' => in_array( HZFEX_CPT_BASENAME, get_option( 'active_plugins' ), true ),
            'loaded'    => in_array( HZFEX_CPT_BASENAME, get_option( 'active_plugins' ), true ),
            'scope'     => 'framework'
        ];

        // Require necessary framework files.
        require_once __DIR__ . '/template/Interface-CPT.php';
        require_once __DIR__ . '/template/Interface-Register.php';
        require_once __DIR__ . '/Helper/Factory.php';
        require_once __DIR__ . '/Helper/Filter.php';
        require_once __DIR__ . '/Helper/Column.php';
        require_once __DIR__ . '/Source/Post-Type.php';
        require_once __DIR__ . '/Source/Taxonomy.php';
        require_once __DIR__ . '/API/API.php';

        // Register this plugin as extension to TWS Core.
        // Using inside hook so it always fire after core plugin is loaded.
        add_action( 'hzfex_core_loaded', [ $this, 'register' ] );

        /**
         * This works only when inclued within core plugin.
         * Using this framework outside the core plugin
         * should implement their own way of flushing
         * rewrite rules on plugin activation.
         * 
         * Also, run hook only once when flushing is true.
         */
        $option     = (array) get_option( 'on_hzfex_core_activation', [] );
        $has_flush  = isset( $option['flush'] ) ? $option['flush'] : false;

        if( $has_flush ) {
            add_action( 'admin_init', [ $this, 'flush_rewrite_rule' ] );
        }

        // Perform task on current admin screen.
        add_action( 'current_screen', [ $this, 'screen' ] );
    }

    /**
     * Registers this plugin as an extension.
     * 
     * Makes this plugin an extension of **The Web Solver Extended** plugin.
     * 
     * @link https://github.com/TheWebSolver/thewebsolver-extended
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function register() {
        // Check if core eixists before registering.
        if( function_exists( 'tws_core' ) ) {
            tws_core()->extensions()->register( $this->args );
        }
    }

    /**
     * Flush rewrite rules.
     * 
     * This function will be fired if option have been
     * registered during the core plugin activation.
     * So, flushing of rewrite rule will only be executed
     * if this framework is included within core plugin.
     *
     * @return void
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function flush_rewrite_rule() {
        $option     = (array) get_option( 'on_hzfex_core_activation', [] );
        $option['flush'] = false;
        update_option( 'on_hzfex_core_activation', $option );

        // Perfom flushing of rewrite rules.
        flush_rewrite_rules();
    }

    /**
     * Check if is a post type screen.
     * 
     * If is a post type screen, then:
     * * Add action hook if is post type from class `CPT_Factory`.
     * * Add action hook if is taxonomy from class `CPT_Factory`.
     * * Instantiate class `CPT_Filter_List` if is post type page (not taxonomy page).
     *
     * @param \WP_Screen $current Passed from `current_screen` hook.
     * 
     * @return void
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function screen( $current ) {
        $post_types = $this->get_post_types();
        $taxonomies = $this->get_taxonomies();
        $post       = $current->post_type;
        $taxo       = $current->taxonomy;

        $cpt    = is_array( $post_types ) && ! empty( $post_types ) ? array_keys( $post_types ) : [];
        $tax    = is_array( $taxonomies ) && ! empty( $taxonomies ) ? array_keys( $taxonomies ) : [];

        // Only continue if current screen matches the post type key, and
        // If current screen is not taxonomy page of current post type.
        if( in_array( $post, $cpt, true ) && $taxo === '' ) {
            /**
             * WPHOOK: Action -> Fires if current screen is of CPT_Factory post type screen.
             * 
             * @param string $post - The current post type name.
             * 
             * @since 1.0
             */
            do_action( "load_hzfex_{$post}_post_type", $post );
        }

        // Only continue if current screen matches taxonomy key.
        if( in_array( $taxo, $tax, true ) ) {
            /**
             * WPHOOK: Action -> Fires if current screen is of CPT_Factory taxonomy screen.
             * 
             * @param string $name - The current taxonomy name.
             * 
             * @since 1.0
             */
            do_action( "load_hzfex_{$taxo}_taxonomy", $taxo );
        }

        // Only run on post type page and not on taxonomy page.
        if( $post !== '' && $taxo === '' ) {
            CPT_Filter_List::for( $post );
            CPT_Column::for( $post )->prepare()->apply();
        }

        // Only run on taxonomy page.
        if( $taxo !== '' ) {
            CPT_Column::for( $taxo, false )->prepare()->apply();
        }
    }

    /**
     * Registered post types global var.
     * 
     * Alias for:
     * ```
     * global $tws_registered_post_type;
     * ```
     *
     * @return array Saved in key/value pair as:
     * * @type `string` `Post Type Key`
     * * @type `object` `CPT_Factory`
     * 
     * @global array $tws_registered_post_type
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function get_post_types(): array {
        global $tws_registered_post_type;

        if( ! is_array( $tws_registered_post_type ) ) {
            $tws_registered_post_type = [];
        }

        return $tws_registered_post_type;
    }

    /**
     * Registered taxonomies global var.
     * 
     * Alias for:
     * ```
     * global $tws_registered_taxonomy;
     * ```
     *
     * @return array Saved in key/value pair as:
     * * @type `string` `Taxonomy Key`
     * * @type `object` `CPT_Factory`
     * 
     * @global array $tws_registered_taxonomy
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function get_taxonomies(): array {
        global $tws_registered_taxonomy;

        if( ! is_array( $tws_registered_taxonomy ) ) {
            $tws_registered_taxonomy = [];
        }

        return $tws_registered_taxonomy;
    }

    /**
     * Gets filter keys set for post types.
     * 
     * Alias for:
     * ```
     * global $tws_post_type_filters;
     * ```
     *
     * @return array Saved in key/value pair as:
     * * $type `string` `Post Type Key`
     * * $type `string|string[]` `Filter keys` Can be taxonomy or post meta keys.
     * 
     * @global string[] $tws_post_type_filters
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function get_post_type_filters(): array {
        global $tws_post_type_filters;

        if( ! is_array( $tws_post_type_filters ) ) {
            $tws_post_type_filters = [];
        }

        return $tws_post_type_filters;
    }

    /**
     * Gets columns data set for post types.
     * 
     * Alias for:
     * ```
     * global $tws_post_type_columns;
     * ```
     * 
     * @return array Saved *Post Type Key** as key and value as:
     * * @type `string` - Column slug as key.
     * * @type `string` `$label` - Column label to display on table head.
     * * @type `string|bool|array` `$sortable` -
     * ** if is _string_, the key to sort column for `orderby` query.
     * ** If is _bool_, enable or disable sorting of column.
     * ** if is _array_, set args as -
     *  > * @type `string` - the key to sort column for `orderby` query.
     *  > * @type `bool` - true if numeric, false if not.
     * * @type `string` `callback` - Function that displays column content. Passed parameters: `$column` & `$post_id`.
     * * @type `int` `priority` - The position in which column is to be inserted. **_0_** is first, and so on.
     *
     * @return array
     * 
     * @global array $tws_post_type_columns
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function get_post_type_columns(): array {
        global $tws_post_type_columns;

        if( ! is_array( $tws_post_type_columns ) ) {
            $tws_post_type_columns = [];
        }

        return $tws_post_type_columns;
    }

    /**
     * Gets columns data set for taxonomy.
     * 
     * Alias for:
     * ```
     * global $tws_taxonomy_columns;
     * ```
     * 
     * @return array Saved **Taxonomy Key** as key and value as:
     * * @type `string` - Column slug as key.
     * * @type `string` `$label` - Column label to display on table head.
     * * @type `string|bool|array` `$sortable` -
     * ** if is _string_, the key to sort column for `orderby` query.
     * ** If is _bool_, enable or disable sorting of column.
     * ** if is _array_, set args as -
     *  > * @type `string` - the key to sort column for `orderby` query.
     *  > * @type `bool` - true if numeric, false if not.
     * * @type `string` `callback` - Function that displays column content. Passed parameters: `$content`, `$column` and `$term_id`.
     * * @type `int` `priority` - The position in which column is to be inserted. **_0_** is first, and so on.
     *
     * @return array
     * 
     * @global array $tws_taxonomy_columns
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function get_taxonomy_columns(): array {
        global $tws_taxonomy_columns;

        if( ! is_array( $tws_taxonomy_columns ) ) {
            $tws_taxonomy_columns = [];
        }

        return $tws_taxonomy_columns;
    }

    /**
     * Private constructor to prevent direct instantiation.
     * 
     * @since 1.0
     * 
     * @access private
     */
    private function __construct() {}
}