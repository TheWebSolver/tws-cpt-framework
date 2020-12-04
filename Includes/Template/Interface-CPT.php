<?php
/**
 * TheWebSolver\Core\Helper\CPT_Interface class.
 * 
 * @package TheWebSolver\Core\CPT_Framework\Interface
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
 * CPT Abstraction methods for consistent structure.
 * 
 * Blueprint|Grouping|Rules|Abstraction method -> to be used by Factory, Post Types & Taxonomies.
 * * @abstract @method `set_labels(array $labels)` - Set labels from api parameter.
 * * @abstract @method `set_args(array $args)` - Set args from api parameter.
 * * @abstract @method `assign_objects($objects)` - Assign objects to registering type.
 * * @abstract @method `set_redirect(array $args)` - Set redirection args from api parameter.
 * * @abstract @method `start_registration()` - Validate request and get args.
 * * @abstract @method `frontend_redirect()` - Redirect frontend URLs to prevent direct access.
 * * @abstract @method `register()` - 
 * ** Register type,
 * ** add factory to global var of respective registering type, and
 * ** add `do_action` hook with respective registering type key passing `CPT_Factory` as an arg.
 * * @abstract @method `finish_registration()` - additional execution after registration.
 * 
 * @since 1.0
 */
interface CPT_Interface {
    public function set_labels(array $labels);
    public function set_args(array $args);
    public function assign_objects( $objects );
    public function set_redirect(array $args);
    public function start_registration();
    public function frontend_redirect();
    public function register();
    public function finish_registration();
}