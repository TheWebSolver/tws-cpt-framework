<?php // phpcs:ignore WordPress.NamingConventions
/**
 * The Singleton Trait.
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
 * @package TheWebSolver\Core\CPT_Framework\Trait
 * @since   2.0
 * @author  Shesh Ghimire <shesh@thewebsolver.com>
 */

namespace TheWebSolver\CPT\Controller;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Singleton trait.
 */
trait Singleton_Trait {
	/**
	 * Singleton instance.
	 *
	 * @var ($this)[]
	 *
	 * @since 2.0
	 */
	protected static $instance = array();

	/**
	 * Loads called class instance.
	 *
	 * @return $this The called class instance.
	 *
	 * @since 2.0
	 */
	final public static function load() {
		$class = get_called_class();

		if ( ! isset( static::$instance[ $class ] ) ) {
			static::$instance[ $class ] = new $class();
		}

		return static::$instance[ $class ];
	}

	/**
	 * Gets private properties.
	 *
	 * @param string $name      The property name.
	 * @param mixed  $arguments method arguments (never used here).
	 *
	 * @return mixed The property value.
	 *
	 * @since 2.0
	 */
	public function __call( string $name, $arguments ) {
		$properties = get_class_vars( get_class() );

		return isset( $properties[ $name ] ) ? $this->$name : false;
	}

	// phpcs:disable -- Prevent these events.
	protected function __construct() {}
	final protected function __clone() {}
	final protected function __sleep() {}
	final protected function __wakeup() {}
	// phpcs:disable
}

