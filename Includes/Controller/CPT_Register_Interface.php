<?php
/**
 * TheWebSolver\Core\Helper\CPT_Register_Interface class.
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
 * Blueprint|Grouping|Rules|Abstraction method -> to be used by Post Types & Taxonomies.
 * 
 * * @abstract @method `set_filter($key)`
 * ** `bool` on taxonomy: whether to set taxonomy to be used as filter key.
 * ** `array` on post type: filter keys to be used as filter.
 * * @abstract @method `manage_columns($columns)` Admin table columns to manage.
 * * @abstract @method `add_columns($factory)` Method to be added to action hook.
 * 
 * @since 1.0
 */
interface CPT_Register_Interface {
    public function set_filter($key);
    public function manage_columns($columns);
    public function add_columns(CPT_Factory $factory);
}