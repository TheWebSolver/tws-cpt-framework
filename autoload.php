<?php // phpcs:ignore WordPress.NamingConventions
/**
 * The autoloader API.
 *
 * The autoloading class with namespace mapping.
 * An alternative to the composer autloader.
 * If composer autoload found on root directory, it will be used instead.
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
 * @package TheWebSolver\Core
 * @author  Shesh Ghimire <shesh@thewebsolver.com>
 * @version 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Autoloader class.
 *
 * @example usage
 * ### If class files are present in the same directory:
 * - `root/Includes/Helper/Helper_Class.php`
 * - `root/Includes/Helper/General/Another_Class.php`
 * - `root/Includes/API/General_API.php`
 *
 * ```
 * // From plugin root, init autoloader.
 * $map = array('Includes'=>'TheWebSolver\Core');
 * TWS_Autoloader::load()->root(__DIR__)->path($map)->walk();
 * ```
 *
 * ### If class files are present in different directories:
 * - `root/Includes/Helper/Helper_Class.php`
 * - `root/Includes/Template/General/Another_Class.php`
 * - `root/Source/API/General_API.php`
 *
 * Below is the structure how namesapce and classname should be for different directories.
 * ```
 * // File: Helper_Class.php
 * namespace TheWebSolver\Core\Helper;
 * class Helper_Class {}
 *
 * // File: Another_Class.php
 * namespace TheWebSolver\Core\Template\General;
 * class Another_Class {}
 *
 * // File: General_API.php
 * namespace TheWebSolver\Source\API;
 * class General_API {}
 *
 * // Lets autoload above structure files.
 * // Subdirectory names after which namespace maps.
 * $map = array(
 *  'Includes' =>'TheWebSolver\Core',
 *  'API'      =>'TheWebSolver\Source',
 * );
 *
 * // From plugin root, init autoloader.
 * TWS_Autoloader::load()->root(__DIR__)->path($map)->walk();
 * ```
 */
class TWS_Autoloader {
	/**
	 * Autoloader instance.
	 *
	 * @var TWS_Autoloader
	 * @since 1.0
	 */
	private static $instance;

	/**
	 * The plugin root.
	 *
	 * @var string[]
	 * @since 1.0
	 */
	private $root;

	/**
	 * The mapped namespace with it's directory name.
	 *
	 * @var string[]
	 * @since 1.0
	 */
	public $paths;

	/**
	 * The classes set for inclusion.
	 *
	 * @var string[]
	 * @since 1.0
	 */
	private $classes = array();

	/**
	 * The class to include file for.
	 *
	 * @var string
	 * @since 1.0
	 */
	private $class = '';

	/**
	 * The autoload status.
	 *
	 * @var bool[]
	 * @since 1.0
	 */
	private $autoload = array();

	/**
	 * On debug, files are not included.
	 *
	 * @var bool
	 * @since 1.0
	 */
	private $debug = false;

	/**
	 * Instantiates autoloader.
	 *
	 * When passing path, it should be mappable with the namespace.
	 * If using composer, the vendor autoload will be used.
	 *
	 * @return TWS_Autoloader
	 * @since 1.0
	 */
	public static function load(): TWS_Autoloader {
		return ! is_null( self::$instance ) ? self::$instance : new self();
	}

	/**
	 * Creates full path for the given directory.
	 *
	 * @param string $path The path to be appended to root.
	 *
	 * @return string
	 * @since 1.0
	 */
	private function map( string $path ): string {
		return trailingslashit( $this->root ) . untrailingslashit( $path );
	}

	/**
	 * Sets plugin root.
	 *
	 * @param string $dir The plugin root directory path. Usually `__DIR__`.
	 * @return TWS_Autoloader
	 * @since 1.0
	 */
	public function root( string $dir ): TWS_Autoloader {
		$this->root = $dir;

		return $this;
	}

	/**
	 * Sets namespace mapping directory name(s).
	 *
	 * @param string[] $name Mapping directory name and it's namespace.
	 * @return TWS_Autoloader
	 * @since 1.0
	 */
	public function path( array $name ): TWS_Autoloader {
		$this->paths = $name;

		return $this;
	}

	/**
	 * Sets debug mode.
	 *
	 * @param bool $enable Found file is not included if debug is true.
	 * @return TWS_Autoloader
	 * @since 1.0
	 */
	public function debug( bool $enable ): TWS_Autoloader {
		$this->debug = $enable;

		return $this;
	}

	/**
	 * Gets file from mapped path created using class parts.
	 *
	 * @param string[] $parts The classname parts.
	 * @param string   $path  The path to append parts to.
	 * @return string $path The file with full path that matches namespace.
	 * @since 1.0
	 */
	private function file( array $parts, string $path ): string {
		foreach ( $parts as $part ) {
			$path .= "/$part";
		}

		$path .= '.php';

		return $path;
	}

	/**
	 * Creates directory paths part from the class.
	 *
	 * @param string $namespace The namespace.
	 * @return string[]
	 * @since 1.0
	 */
	private function parts( string $namespace ): array {
		$parts = explode( '\\', substr( $this->class, strlen( $namespace . '\\' ) ) );

		return $parts ? $parts : array();
	}

	/**
	 * Prevent loading if autoload is set to false.
	 *
	 * @throws LogicException Can't load current class using autoloader.
	 * @since 1.0
	 */
	protected function block() {
		if ( class_exists( $this->class, false ) ) {
			throw new LogicException(
				'Unable to load class:"' . $this->class . '" because autoload is set to "false".'
			);
		}
	}

	/**
	 * Includes mapped directories.
	 *
	 * @param string $file The file to include.
	 * @return bool
	 * @since 1.0
	 */
	private function include( string $file ): bool {
		$this->autoload[ $file ] = false;

		// Bail if file is not readable.
		if ( ! is_readable( $file ) ) {
			return false;
		}

		if ( ! $this->debug ) {
			include $file;
		}

		$this->autoload[ $file ]       = true;
		$this->classes[ $this->class ] = $file;

		return true;
	}

	/**
	 * Includes mapped directories for autoloading.
	 *
	 * @return bool True if file found and included, false otherwise.
	 * @since 1.0
	 */
	private function locate(): bool {
		if ( ! is_array( $this->paths ) || empty( $this->paths ) ) {
			return false;
		}

		$files = array();

		foreach ( $this->paths as $dir => $namespace ) {
			// Ignore classes not in the given namespace.
			if ( strpos( $this->class, $namespace . '\\' ) !== 0 ) {
				continue;
			}

			$parts = $this->parts( $namespace );

			// Ignore non-classmapped.
			if ( empty( $parts ) ) {
				continue;
			}

			$file    = $this->file( $parts, $this->map( $dir ) );
			$include = $this->include( $file );
			$files[] = $include ? $file : '';
		}

		return ! empty( $files );
	}

	/**
	 * Includes file if mapping successful.
	 *
	 * @param string $class The full class to instantiate.
	 * @return bool True if autoloaded, false otherwise. Catch error, if any.
	 * @since 1.0
	 */
	public function autoload( string $class ): bool {
		$this->class = $class;

		return $this->locate();
	}

	/**
	 * Registers classes for autoloading.
	 *
	 * Internally, composer autoload file is checked. If it exists:
	 * * Composer autoload file will be included without custom registration.
	 * * Composer autoload file must be on same root.
	 * * This method will always return true.
	 *
	 * Param passed will only be used if composer autoload file does not exist.
	 * {@see `spl_autoload_register()`}.
	 *
	 * @param bool $throw   Specifies whether spl_autoload_register() should throw
	 *                      exceptions when the autoload_function cannot be
	 *                      registered. Ignored since 8.0.
	 * @param bool $prepend If true, spl_autoload_register() will prepend
	 *                      the autoloader on the autoload stack instead of
	 *                      appending it.
	 * @return bool
	 * @since 1.0
	 */
	public function walk( bool $throw = true, bool $prepend = false ): bool {
		// Composer autoload exists, include that.
		if ( file_exists( $autoloader = $this->root . '/vendor/autoload.php' ) ) { // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure, WordPress.CodeAnalysis.AssignmentInCondition
			include $autoloader;

			return true;
		}

		return spl_autoload_register( array( $this, 'autoload' ), $throw, $prepend );
	}

	/**
	 * Validates if path is autoloaded.
	 *
	 * @param bool $path Whether path is autoloaded or not.
	 * @return bool
	 * @since 1.0
	 */
	public function valid( bool $path ): bool {
		return true === $path;
	}

	/**
	 * Gets mapped paths.
	 *
	 * @return bool[]
	 * @since 1.0
	 */
	public function get(): array {
		return array_filter( $this->get_all(), array( $this, 'valid' ) );
	}

	/**
	 * Gets all mapped paths.
	 *
	 * It includes those files that do not get mapped.
	 *
	 * @return bool[]
	 * @since 1.0
	 */
	public function get_all(): array {
		return $this->autoload;
	}

	/**
	 * Gets mapped classes.
	 *
	 * @return string[]
	 * @since 1.0
	 */
	public function classes(): array {
		return $this->classes;
	}

	/**
	 * Static only class.
	 *
	 * @since 1.0
	 */
	private function __construct() {}
}
