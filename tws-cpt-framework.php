<?php
/**
 * Plugin Name: The Web Solver Custom Post Type Framework
 * Plugin URI: https://github.com/TheWebSolver/tws-custom-post-type-framework
 * Description: <b>Custom Post Type framework</b> to register WordPress Custom Post Types and Taxonomies
 * Version: 1.0
 * Author: Shesh Ghimire
 * Author URI: https://www.linkedin.com/in/sheshgh/
 * Requires at least: 5.3
 * Requires PHP: 5.6
 * Text Domain: tws-core
 * License: GNU General Public License v3.0 (or later)
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package TheWebSolver\Core\CPT_Framework
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

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The Web Solver Custom Post Type Framework class
 * 
 * @since 1.0
 */
final class HZFEX_Cpt_Framework {
    /**
	 * Creates an instance of this class.
	 *
	 * @since 1.0
	 * 
	 * @static
	 * 
	 * @access public
	 * 
	 * @return HZFEX_Cpt_Framework
	 */
	public static function activate(): HZFEX_Cpt_Framework {
        static $tws_cpt;
		if( ! is_a( $tws_cpt, get_class() ) ) {
            $tws_cpt = new self();
            $tws_cpt->define_constants()->require_main_file();
        }
        return $tws_cpt;
    }

    /**
     * Define plugin constants.
     *
     * @return HZFEX_Cpt_Framework
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function define_constants() {
        // define plugin textdomain.
        // TWS Core plugin already defines it.
        if( ! defined( 'HZFEX_TEXTDOMAIN' ) ) define( 'HZFEX_TEXTDOMAIN', 'tws-core' );

        // define plugin debug mode. DEBUG: set to true when needed.
        // TWS Core plugin already defines it.
        if( ! defined( 'HZFEX_DEBUG_MODE' ) ) define( 'HZFEX_DEBUG_MODE', true );

        define( 'HZFEX_CPT' , __( 'The Web Solver Custom Post Type' , HZFEX_TEXTDOMAIN ) );
        define( 'HZFEX_CPT_FILE' , __FILE__ );
        define( 'HZFEX_CPT_URL', plugin_dir_url( __FILE__ ) );
        define( 'HZFEX_CPT_BASENAME', plugin_basename( __FILE__ ) );
        define( 'HZFEX_CPT_PATH', plugin_dir_path( __FILE__ ) );
        define( 'HZFEX_CPT_VERSION', '1.0' );
        return $this;
    }

    /**
     * Require main plugin file.
     *
     * @return HZFEX_Cpt_Framework
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function require_main_file() {
        require_once __DIR__ . '/Includes/CPT.php';
    }

    /**
     * Initialize Plugin class.
     *
     * @return TheWebSolver\Core\Cpt\Plugin
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function plugin(): TheWebSolver\Core\Cpt\Plugin {
        return TheWebSolver\Core\Cpt\Plugin::boot();
    }

    /**
     * Prevent direct instantiation.
     * 
     * @since 1.0
     */
    private function __construct() {}
}

/**
 * Main function to instantiate HZFEX_Cpt_Framework class.
 *
 * @return HZFEX_Cpt_Framework
 * 
 * @since 1.0
 */
function tws_cpt(): HZFEX_Cpt_Framework {
    return HZFEX_Cpt_Framework::activate();
}

// Initializes the plugin.
tws_cpt()->plugin();