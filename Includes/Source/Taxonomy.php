<?php
/**
 * TheWebSolver\Core\Cpt\Taxonomy Class.
 * 
 * Creates new taxonomies.
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
 * TheWebSolver\Core\Cpt\Taxonomy Class.
 * 
 * @api
 */
final class Taxonomy implements CPT_Interface, CPT_Register_Interface {
    /**
     * Initialize factory for Custom Taxonomy registration.
     *
     * @var CPT_Factory
     * 
     * @since 1.0
     * 
     * @access private
     */
    private $factory;

    /**
     * Constructs Taxonomy data.
     * 
     * @param string[] $names **required** Should be in key/value pair of:
     * * @type `string` `$key` **required** The taxonomy key.
     * * @type `string` `$slug` **required** The taxonomy slug.
     * * @type `string` `$singular_name` **optional** Generated from key, if not provided.
     * * @type `string` `$plural_name` **optional** Generated from key, if not provided.
     * 
     * @since 1.0
     */
    public function __construct( array $names ) {
        $this->factory = CPT_Factory::start_engine_with( get_class(), $names, 32 );
    }

    /**
     * Sets the labels for the taxonomy type.
     * 
     * @param  array $labels An array of labels for the taxonomy type
     * 
     * @return Taxonomy
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function set_labels( array $labels ): Taxonomy {
        $this->factory->set_labels( $labels );
        return $this;
    }

    /**
     * Sets arguments for the taxonomy type.
     * 
     * @param  array $args An array of args for the taxonomy type
     * 
     * @return Taxonomy
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function set_args( array $args ): Taxonomy {
        $this->factory->set_args( $args );
        return $this;
    }

    /**
     * Assigns post types to register the taxonomy to.
     * 
     * @param  string|string[] $post_types single (in `string`) or multiple (in `array`) post types this taxonomy to be assigned to.
     * 
     * @return Taxonomy
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function assign_objects( $post_types ): Taxonomy {
        $this->factory->assign_objects( $post_types );
        return $this;
    }

    /**
     * Sets frontend archive page redirection for the taxonomy term(s).
     *
     * @param array $args
     * 
     * @return Taxonomy
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function set_redirect( array $args ): Taxonomy {
        $this->factory->set_redirect( $args );
        return $this;
    }

    /**
     * Sets taxonomy as filter in Admin Post List Table.
     *
     * @param bool $enable True to set key as query vars, false if not.
     * 
     * @return Taxonomy
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function set_filter( $enable ): Taxonomy {
        $this->factory->filter_key = $enable;
        return $this;
    }

    /**
     * Set taxonomy admin table columns.
     *
     * @param string|string[] $columns
     * 
     * @return Taxonomy
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function manage_columns( $columns ): Taxonomy {
        $this->factory->columns= $columns;
        return $this;
    }

    /**
     * Registers the taxonomy.
     * 
     * @return void
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function start_registration() {
        $this->factory->start_registration();

        add_action( 'init', [ $this, 'register' ], 9 );
        add_action( 'init', [ $this, 'finish_registration' ], 14 );

        if( true === $this->factory->redirect_frontend && ! is_admin() ) {
            add_action( 'template_redirect', [ $this, 'frontend_redirect' ] );
        }

        /**
         * Only manage Admin table list column if:
         * - Columns data is set and not empty.
         * - action hook is available after successful registration.
         */
        $tag    = "registered_hzfex_{$this->factory->key}_taxonomy";
        $cols   = $this->factory->columns;
        if( is_array( $cols ) && ! empty( $cols ) ) {
            add_action( $tag, [$this, 'add_columns'] );
        }
    }

    /**
     * Redirect taxonomy archive page with matching terms.
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
     * Sets Taxonomy table columns.
     * 
     * This actually sets the columns to global vars `$tws_taxonomy_columns`\
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
        CPT_Factory::set_taxonomy_columns( $factory->key, $factory->columns );
    }
}