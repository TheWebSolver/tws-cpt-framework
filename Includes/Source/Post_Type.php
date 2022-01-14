<?php
/**
 * TheWebSolver\Core\Cpt\Post_Type Class.
 * 
 * Creates new post types.
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

namespace TheWebSolver\Core\Cpt;

use TheWebSolver\Core\Helper\CPT_Factory;
use TheWebSolver\Core\Helper\CPT_Interface;
use TheWebSolver\Core\Helper\CPT_Register_Interface;

/**
 * TheWebSolver\Core\Cpt\Post_Type Class.
 * 
 * @api
 */
final class Post_Type implements CPT_Interface, CPT_Register_Interface {
    /**
     * Initialize factory for Custom Post Type registration.
     *
     * @var CPT_Factory
     * 
     * @since 1.0
     * 
     * @access public
     */
    private $factory;

    /**
     * Constructs post type data.
     * 
     * @param string[] $names **required** Should be in key/value pair of:
     * * @type `string` `$key` **required** The post type key.
     * * @type `string` `$slug` **required** The post type slug.
     * * @type `string` `$singular_name` **optional** Generated from key, if not provided.
     * * @type `string` `$plural_name` **optional** Generated from key, if not provided.
     * 
     * @since 1.0
     */
    public function __construct( array $names ) {
        $this->factory = CPT_Factory::start_engine_with( get_class(), $names, 20 );
    }

    /**
     * Sets the labels for the post type.
     * 
     * @param  array $labels An array of labels for the post type
     * 
     * @return Post_Type
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function set_labels( array $labels ): Post_Type {
        $this->factory->set_labels( $labels );
        return $this;
    }

    /**
     * Sets arguments for the post type.
     * 
     * @param  array $args An array of args for the post type
     * 
     * @return Post_Type
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function set_args( array $args ): Post_Type {
        $this->factory->set_args( $args );
        return $this;
    }

    /**
     * Sets Taxonomy to the post type.
     * 
     * @param  string/string[] $taxonomies single (in `string`) or multiple (in `array`) taxonomies for the post type.
     * 
     * @return Post_Type
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function assign_objects( $taxonomies ): Post_Type {
        $this->factory->assign_objects( $taxonomies );
        return $this;
    }

    /**
     * Sets frontend page redirection of the post type singular or archive page.
     *
     * @param array $args
     * 
     * @return Post_Type
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function set_redirect( array $args ): Post_Type {
        $this->factory->set_redirect( $args );
        return $this;
    }

    /**
     * Sets Post Type Admin Table filtering keys.
     *
     * @param string|string[] $keys
     * * _if taxonomy_: single taxonomy slug, or array of taxonomy keys.
     * * _if post meta_: single meta key, or array of meta keys.
     * 
     * @return Post_Type
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function set_filter( $keys ): Post_Type {
        $this->factory->filter_key = ! is_array($keys) ? [$keys] : (array) $keys;
        return $this;
    }

    /**
     * Set post type admin table columns.
     *
     * @param string|string[] $columns
     * 
     * @return Post_Type
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function manage_columns( $columns ): Post_Type {
        $this->factory->columns = $columns;
        return $this;
    }

    /**
     * Registers the post type.
     * 
     * @return void
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function start_registration() {
        $this->factory->start_registration();

        add_action( 'init', [ $this, 'register' ] );
        add_action( 'init', [ $this, 'finish_registration' ], 15 );

        if( true === $this->factory->redirect_frontend && ! is_admin() ) {
            add_action( 'template_redirect', [ $this, 'frontend_redirect' ] );
        }

        /**
         * Only perform admin post table list filtering if:
         * - Filter keys set (either taxonomy keys or post meta keys),
         * - Filter keys set is either an array of keys or a single key, and
         * - Action hook is available after successful registration.
         */
        $tag    = "registered_hzfex_{$this->factory->key}_post_type";
        $keys   = $this->factory->filter_key;
        $cols   = $this->factory->columns;
        if( ( is_array( $keys ) && $keys[0] !== '' ) ) {
            add_action( $tag, [$this, 'add_filters'] );
        }

        // Initiate hook if columns is set.
        if( is_array( $cols ) && ! empty( $cols ) ) {
            add_action( $tag, [$this, 'add_columns'] );
        }
    }

    /**
     * Redirect single post type and post type archive page.
     *
     * @return bool
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function frontend_redirect() {
        $this->factory->frontend_redirect();
    }

    /**
     * Registration hook.
     *
     * @return void
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function register() {
        $this->factory->register();
    }

    /**
     * Finish factory registration process.
     *
     * @return bool
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function finish_registration() {
        $this->factory->finish_registration();
    }

    /**
     * Sets Post Type List filters.
     * 
     * This actually sets the filters to global vars `$tws_post_type_filters`\
     * The global vars will then be used to set filter accordingly.
     *
     * @param CPT_Factory $factory Passed from action hook.
     * 
     * @return void
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function add_filters( CPT_Factory $factory ) {
        CPT_Factory::set_post_type_filters( $factory->key, $factory->filter_key );
    }

    /**
     * Sets Post Type table columns.
     * 
     * This actually sets the columns to global vars `$tws_post_type_columns`\
     * The global vars will then be used to set columns accordingly.
     *
     * @param CPT_Factory $factory Passed from action hook.
     * 
     * @return void
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function add_columns( CPT_Factory $factory ) {
        CPT_Factory::set_post_type_columns( $factory->key, $factory->columns );
    }
}