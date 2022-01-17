<?php // phpcs:ignore WordPress.NamingConventions
/**
 * TheWebSolver\Core\Helper\Factory class
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
 * @package TheWebSolver\Core\CPT_Framework\Class
 * @since   1.0
 * @version 2.0 Developed with WPCS, namespaced autoloader usage.
 */

namespace TheWebSolver\CPT\Helper;

use WP_Error;
use TheWebSolver\CPT\Controller\CPT_Interface;
use WP_Post_Type;
use WP_Taxonomy;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Factory for the Registering Post Type and Taxonomy to follow DRY development.
 *
 * This class should be instantiated from each registering type constructor
 * by supplying proper parameters to set it's properties' values.
 *
 * It will be used as structure for setting, validating and saving each registering type values.
 */
final class Factory implements CPT_Interface {
	/**
	 * Key of the registering type.

	 * - This will be the name of the registering type and should never be changed.
	 * - If changed, then another type will get registered instantly.
	 * - All data created within this type won't be automatically migrated to the changed one.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	public $key;

	/**
	 * The singular name of the registering type.
	 *
	 * Used in $args['label']['singular_name']
	 * {@see @method Factory::set_names()}
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	public $singular_name;

	/**
	 * The name of the registering type. Usually plural.
	 *
	 * Used in $args['labels']['name']
	 * {@see @method Factory::set_names()}.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	public $plural_name;

	/**
	 * The slug of the registering type.
	 *
	 * Used in $args['rewrite']['slug'].
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	public $slug;

	/**
	 * An array of labels of the registering type.
	 *
	 * Used in $args['labels']
	 * {@see @method Factory::labels()}.
	 *
	 * @var array
	 *
	 * @since 1.0
	 */
	public $labels;

	/**
	 * An array of arguments of the registering type.
	 *
	 * Used in registering type function.
	 * {@see @method Factory::args()}
	 *
	 * @var array
	 *
	 * @since 1.0
	 */
	public $args;

	/**
	 * Whether to enable frontend access using URL.
	 *
	 * This ensures publicly disabled registering type to be hidden
	 * by redirecting to the given URL when accessed directly.
	 *
	 * @var bool
	 *
	 * @since 1.0
	 */
	public $redirect_frontend;

	/**
	 * Flush rewrite rules handler.
	 *
	 * It's value becomes `true` for one time on page reload
	 * after changing the slug of the registering type.
	 * {@see @method Factory::validate_names()}.
	 *
	 * @var bool
	 *
	 * @since 1.0
	 */
	public $flush_rewrite_rule = false;

	/**
	 * Registering type filter key for use as filter key.
	 *
	 * @var string[]|bool
	 * * Taxonomies/Post Meta Keys in array if post type.
	 * * Whether can be used for filtering if taxonomy.
	 *
	 * @since 1.0
	 */
	public $filter_key;

	/**
	 * Registering type assigned objects.
	 *
	 * @var string[]
	 *
	 * @since 1.0
	 */
	public $assigned_objects;

	/**
	 * Columns for admin table.
	 *
	 * @var array
	 *
	 * @since 1.0
	 */
	public $columns;

	/**
	 * Sets debugging state.
	 *
	 * @var bool
	 *
	 * @since 1.0
	 */
	private static $debug;

	/**
	 * Option key to get registering types.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	private $option_key;

	/**
	 * Whether option is updated.
	 *
	 * @var bool
	 *
	 * @since 2.0
	 */
	private $did_update = false;

	/**
	 * Validation request of the registering type.
	 *
	 * Values are extracted from the respective registering class name.
	 *
	 * @var string[]
	 * * @type `string` `$id`       - Possible values are post_type|taxonomy.
	 * * @type `string` `$register` - Possible values are register_post_type|register_taxonomy.
	 * * @type `string` `$exists`   - Possible values are post_type_exists|taxonomy_exists.
	 * * @type `string` `$type`     - Possible values are Post Type|Taxonomy.
	 *
	 * @since 1.0
	 */
	public $request;

	/**
	 * Holder for the names from API parameter.
	 *
	 * It will be used to assign values to:
	 * * @property Factory::$key
	 * * @property Factory::$singular_name
	 * * @property Factory::$name
	 * * @property Factory::$slug
	 *
	 * @var string[]
	 *
	 * @since 1.0
	 */
	private $names;

	/**
	 * Key character length of the registering type.
	 *
	 * The WordPress recommended maximum key character length
	 * as key { @see @property Factory::$key } will get saved to database.
	 *
	 * @var int
	 *
	 * @since 1.0
	 */
	private $key_length;

	/**
	 * Taxonomy terms of the registering taxonomy to be used for redirection.
	 *
	 * @var string[]
	 *
	 * @since 1.0
	 */
	private $redirect_terms = array();

	/**
	 * HTTP Status Response code for redirection of the registering type.
	 *
	 * @var int Defaults to `302` - Moved Temporarily.
	 *
	 * @since 1.0
	 */
	private $redirect_code = 302;

	/**
	 * Query args in redirection URL of the registering type.
	 *
	 * @var array
	 *
	 * @since 1.0
	 */
	private $redirect_query = array();

	/**
	 * Whether factory can be initialized.
	 *
	 * @var bool
	 *
	 * @since 2.0
	 */
	private $can_start = true;

	/**
	 * Registering type option values to be saved.
	 *
	 * @var (string|true)[]
	 *
	 * @since 2.0
	 */
	public $option_value = array();

	/**
	 * WP_Error instance if something fails.
	 *
	 * @var WP_Error
	 *
	 * @since 2.0
	 */
	private $error = false;

	/**
	 * Current registered page hook.
	 *
	 * @var string
	 *
	 * @since 2.0
	 */
	public $hook = '';

	/**
	 * Factory constructor.
	 *
	 * @param string   $class      The registering type class.
	 * @param string[] $names      The registering type names.
	 * @param int      $key_length The WP supported max length to insert data to database.
	 *
	 * @since 1.0
	 * @since 2.0 Method is made public and can be instantiated directly.
	 * @since 2.0 Name validation is done from the respective registering type class.
	 */
	public function __construct( string $class, array $names, int $key_length ) {
		$this->names      = $names;
		self::$debug      = defined( 'HZFEX_DEBUG_MODE' ) && HZFEX_DEBUG_MODE;
		$this->key_length = $key_length;
		$this->error      = new WP_Error();

		$this->parse( $class );
	}

	/**
	 * Determines whether factory has failed.
	 *
	 * { @property Factory::$can_start } is set when validating name
	 * and this method returns it {@see Factory::has_valid_names()}
	 *
	 * @return bool
	 *
	 * @since 2.0
	 */
	public function failed(): bool {
		return ! $this->can_start;
	}

	/**
	 * Gets errors.
	 *
	 * @return WP_Error
	 *
	 * @since 2.0
	 */
	public function error() {
		return $this->error;
	}

	/**
	 * Gets option value.
	 *
	 * @param string $key The registering type key.
	 *
	 * @return ((string|true)[])[]|(string|true)[]
	 *         All registered type values or registered type value if key given.
	 *
	 * @since 2.0
	 */
	public function option( string $key = '' ): array {
		$types = (array) get_option( $this->option_key, array() );

		if ( $key ) {
			return isset( $types[ $key ] ) ? $types[ $key ] : $types;
		}

		return $types;
	}

	/**
	 * Checks if update triggered during runtime.
	 *
	 * @return bool True if registering type value updated, false otherwise.
	 *
	 * @since 2.0
	 */
	public function did_update(): bool {
		return $this->did_update;
	}

	/**
	 * Dies.
	 *
	 * @param string $title The wp_die title.
	 * @param string $code  The WP_Error code to get error message.
	 *
	 * @since 1.0
	 * @since 2.0 Changed method name from `_shutdown_factory` and made public.
	 * @since 2.0 Accepts two args to show message on shutdown.
	 */
	public function shutdown( string $title, string $code ) {
		$title      = ucwords( $title );
		$error      = $this->error->get_error_message( $code );
		$info_title = "<h1 style='text-align:center;color:#b71c1c;'>$title</h1>";
		$message    = $info_title . $error;

		wp_die( wp_kses_post( $message ), esc_html( $title ) );
	}

	/**
	 * Sets request property values from classname.
	 *
	 * @param string $class The registering type class.
	 *
	 * @since 1.0
	 * @since 2.0 Changed method name from `_set_request_prop`.
	 */
	private function parse( string $class ) {
		$request                   = $class;
		$this->request['id']       = $request;
		$this->request['register'] = "register_{$request}";
		$this->request['exists']   = "{$request}_exists";
		$this->request['type']     = self::make_label( $request );
		$this->option_key          = "tws_registered_{$request}";
	}

	/**
	 * Sets the labels for the registering type.
	 *
	 * @param array $labels An array of labels for the registering type.
	 *
	 * @since 1.0
	 */
	public function labels( array $labels ) {
		$this->labels = $labels;
	}

	/**
	 * Sets arguments for the registering type.
	 *
	 * @param array $args An array of args for the registering type.
	 *
	 * @since 1.0
	 */
	public function args( array $args ) {
		$this->args = $args;
	}

	/**
	 * Converts given thing to an array.
	 *
	 * @param mixed $thing Thing to convert to an array.
	 *
	 * @return array
	 *
	 * @since 2.0
	 */
	public static function to_array( $thing ): array {
		return ! is_array( $thing ) ? array( $thing ) : (array) $thing;
	}

	/**
	 * Makes given key as human readable label.
	 *
	 * @param string $key The key to convert to label.
	 *
	 * @return string
	 *
	 * @since 2.0
	 */
	public static function make_label( string $key ): string {
		return ucwords( strtolower( str_replace( array( '-', '_' ), ' ', $key ) ) );
	}

	/**
	 * Gets superglobal value.
	 *
	 * @param string $key     The $_GET request key.
	 * @param string $default The default value if not set.
	 * @param bool   $clean   Whether to sanitize value of the $key or get as is.
	 *
	 * @return string
	 *
	 * @since 2.0
	 */
	public static function is_get( string $key, string $default = '', bool $clean = true ): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$value = isset( $_GET[ $key ] ) ? wp_unslash( $_GET[ $key ] ) : $default;

		return $clean ? sanitize_key( $value ) : $value;
	}

	/**
	 * Validates if given string can be used as key or slug.
	 *
	 * @param string $string The string to check for.
	 *
	 * @return bool
	 *
	 * @since 2.0
	 */
	public static function valid_key( string $string ): bool {
		return ! preg_match( '/[^A-Za-z\-\_]/', $string );
	}

	/**
	 * Assign object for the registering type.
	 *
	 * @param string|string[] $objects The object to register to this type.
	 *
	 * @since 1.0
	 * @since 2.0 Use of `to_array` helper method.
	 */
	public function assign( $objects ) {
		$this->assigned_objects = self::to_array( $objects );
	}

	/**
	 * Sets redirection properties.
	 *
	 * Following key/value of args { @see @property Factory::$args }
	 * will be forced set if `$enabled` is `true` for redirection to work.
	 * * `public`             => `false`
	 * * `publicly_queryable` => `true`
	 * * `has_archive`        => `true`  - only for Post Type args
	 * * `query_var`          => `false`
	 *
	 * @param array $args The redirection args.
	 * * @type `bool`            `$enabled`       Frontend redirection enabled or not.
	 * * @type `string|string[]` `$terms`         Taxonomy terms if taxonomy has redirection enabled.
	 * * @type `int`             `$response_code` HTTP Status Response Code for redirection.
	 *
	 * @since 1.0
	 * @since 2.0 Use of `to_array` helper method.
	 */
	public function redirect( array $args ) {
		$this->redirect_frontend = isset( $args['enabled'] ) && $args['enabled'] ? (bool) $args['enabled'] : false;

		if ( isset( $args['terms'] ) && ! empty( $args['terms'] ) ) {
			$this->redirect_terms = self::to_array( $args['terms'] );
		}

		if ( isset( $args['response_code'] ) && ! empty( $args['response_code'] ) && is_int( $args['response_code'] ) ) {
			$this->redirect_code = (int) $args['response_code'];
		}
	}

	/**
	 * Validates and sets the registering type names.
	 *
	 * @since 1.0
	 * @since 2.0 Made method public to be called from registering type.
	 * @since 2.0 Check if key is set before saving registering type as option value.
	 * @since 2.0 WordPress dies if key is still not set.
	 * @since 2.0 Set property instead of saving option immediately if not saved already.
	 * @since 2.0 Values are saved at once from { @property Factory::$option_value }
	 *            and only when runtime have any difference than saved value.
	 */
	public function validate_names() {
		// Key not set yet for some reason, factory shouldn't start.
		$this->can_start = '' !== $this->key;
		$types           = $this->option();
		$type            = isset( $types[ $this->key ] ) ? (array) $types[ $this->key ] : array();
		$which           = 'Key';

		// Check if registering type has already been saved.
		if ( ! empty( $type ) ) {
			$saved_slug      = isset( $type['slug'] ) ? (string) $type['slug'] : '';
			$this->can_start = '' !== $saved_slug;
			$which           = 'Slug';
		}

		// Don't proceed further if key or slug is not set.
		if ( $this->failed() ) {
			$this->error->add( 'validate_names', "$which not found for validating names." );
			$this->shutdown( "{$this->request['type']} Failed Processing", 'validate_names' );
		}

		$this->slug                 = $this->names['slug'];
		$this->option_value['slug'] = $this->slug;

		// Check if slug value has been changed from the option previously saved.
		if ( $this->slug !== $saved_slug ) {
			$this->flush_rewrite_rule = true;

			// Saves rewrite rule as value to option of registering type.
			// It can then be used to handle rewrite rule.
			$this->option_value['slug_changed'] = true;
		}

		$this->set_names();

		$this->update_option( $types, $type );
	}

	/**
	 * Checks if required name key has valid value set.
	 *
	 * @param string $key The key that is required.
	 *
	 * @return bool True if valid, false otherwise.
	 *
	 * @since 2.0
	 */
	private function name_has( string $key ): bool {
		$valid = isset( $this->names[ $key ] )
		&& is_string( $this->names[ $key ] )
		&& '' !== trim( $this->names[ $key ] );

		$this->can_start = $valid;

		return $valid;
	}

	/**
	 * Checks if names parameter supplied for registering type has any errors.
	 *
	 * * names should be supplied as an array. { @see @property Factory::$names }
	 * * names array should have atleast `key` and `slug` key/value pair set.
	 * * `key` character length should not exceed the number specified. { @see @property Factory::$key_length }
	 *
	 * @return bool True if names are valid, false otherwise.
	 *
	 * @since 1.0
	 * @since 2.0 Made method public to be called from registering type.
	 * @since 2.0 Changed method name. Set error property value. No more return value.
	 * @since 2.0 This method should be called by registering type.
	 */
	public function has_valid_names(): bool {
		$type       = $this->request['type'];
		$names      = $this->names;
		$key_length = $this->key_length;
		$error_data = array();
		$message    = "<p><b>$type Key and Slug</b> must be set in <code><em>key=>value</em></code> pair in an array to get registered. <b><em>_ (underscore)</em></b> and <b><em>- (dash)</em></b> can be used interchangeably for the array value.</p><p>Following <code><em>key=>value</em></code> missing in the array for <code><em>\$names</em></code> parameter.</p>";

		if ( ! $this->name_has( 'key' ) ) {
			$error_data['key'] = "new-{$this->request['id']}-key-or-name";
		} else {
			if ( ! self::valid_key( $names['key'] ) ) {
				$this->error->add(
					'invalid_key',
					sprintf(
						'Given key <b>%s</b> is not a valid format. Only <b>"a-z, -, or _"</b> are allowed.',
						$names['key']
					)
				);

				$this->shutdown( "Invalid $type key.", 'invalid_key' );
			}
		}

		if ( ! $this->name_has( 'slug' ) ) {
			$error_data['slug'] = "new-{$this->request['id']}-slug";
		} else {
			if ( ! self::valid_key( $names['slug'] ) ) {
				$this->error->add(
					'invalid_slug',
					sprintf(
						'Given slug <b>%s</b> is not a valid format. Only <b>"a-z, -, or _"</b> are allowed.',
						$names['slug']
					)
				);

				$this->shutdown( "Invalid $type slug.", 'invalid_slug' );
			}
		}

		if ( $this->failed() ) {
			$css      = 'style="font-style:italic;padding:2em;background:#eee;border-radius:5px;"';
			$message .= '<pre ' . $css . '>' . print_r( $error_data, true ) . '</pre>'; // phpcs:ignore -- Print OK.

			$this->error->add( 'names', $message, $error_data );
		}

		$sanitized_key = $names['key'];

		// WordPress recommended key length check, add error message if failed.
		if ( strlen( $sanitized_key ) > $key_length ) {
			$this->can_start = false;
			$message         = sprintf(
				'<p>%1$s</p><p><code><b><em>%2$s</em></b></code>%3$s<b>%4$s</b> %5$s <code><b>%6$s</b></code>.</p>',
				"$sanitized_key $type Key must not exceed $key_length characters in length.",
				$sanitized_key,
				' exceeds "',
				$key_length,
				'" characters limit even after using WordPress sanitize function',
				'sanitize_key()'
			);

			$this->error->add( 'names', $message );
		}

		// Sets registering type key if everything is valid.
		$this->key = $sanitized_key;

		return $this->can_start;
	}

	/**
	 * Sets names for the $names parameter.
	 *
	 * Prepare names for registering type labels.
	 *
	 * @since 1.0
	 * @since 2.0 Removed unnecessary checks and other enhancements.
	 */
	private function set_names() {
		// Required properties whose values to be set.
		$props = array( 'singular_name', 'plural_name' );

		// Iterate over props to set values to.
		foreach ( $props as $prop ) {
			// Triggers if API parameters have necessary property values supplied.
			if ( isset( $this->names[ $prop ] ) ) {
				$this->$prop                 = $this->names[ $prop ];
				$this->option_value[ $prop ] = $this->$prop;

				// Only continue if $prop isn't already passed as parameter.
				continue;
			}

			// If property is not set and property is singular.
			$value = self::make_label( $this->key );

			// If property is name (usually plural), append a 's' to the value.
			if ( 'plural_name' === $prop ) {
				$value .= 's';
			}

			// Sets each property value.
			$this->$prop = $value;

			// Updates option's value of each property.
			$this->option_value[ $prop ] = $value;
		}
	}

	/**
	 * Updates option value to database.
	 *
	 * {@see @method CPT_Factory::_set_names()}.
	 *
	 * @param ((string|true)[])[] $all     All registering types saved values.
	 * @param (string|true)[]     $current Current type saved values.
	 *
	 * @return bool True if value is updated, false otherwise.
	 *
	 * @since 1.0
	 * @since 2.0 Remamed method from `_update_option`.
	 * @since 2.0 Updates are made at once after checking if there is any difference.
	 */
	private function update_option( array $all, array $current ): bool {
		// Only update if saved and runtime values have any difference.
		if ( ! empty( array_diff_assoc( $current, $this->option_value ) ) ) {
			$all[ $this->key ] = $this->option_value;

			update_option( $this->option_key, $all );

			$this->did_update = true;
		}

		return $this->did_update;
	}

	/**
	 * Starts the registration process.
	 *
	 * @return bool
	 * set registering type args { @see @property Factory::$args }, else
	 * false if not valid request {@see @method Factory::is_valid_request()}.
	 *
	 * @since 1.0
	 * @since 2.0 Renamed method name from `start_registration`.
	 */
	public function start(): bool {
		return ! $this->is_valid_request() ? false : $this->get_args();
	}

	/**
	 * Main method to handle registering type.
	 *
	 * This method should be called from respective registering type
	 * `register()` method that is added to `init` hook to perform actual
	 * registration process.
	 *
	 * {@see @method `Factory::register_type()`}
	 * {@see @method `Factory::after_registering_type()`}
	 * {@see @method `Factory::rewrite_handle()`}
	 *
	 * @since 1.0
	 * @since 2.0 Remove slug changed key from current type option value.
	 */
	public function register() {
		$this->register_type();
		$this->after_registering_type();

		$option = $this->option();

		// Flush rewrite rule if slug gets changed.
		if ( isset( $option[ $this->key ]['slug_changed'] ) ) {
			// No recurrence please.
			unset( $option[ $this->key ]['slug_changed'] );

			update_option( $this->option_key, $option );

			$this->register_type();
			$this->rewrite_handle();
		}
	}

	/**
	 * Performs tasks after type registration.
	 *
	 * * Sets `Factory` as global var of respective registering type.
	 * * Creates `do_action` hook passing `Factory` as parameter.
	 *
	 * @since 1.0
	 * @since 2.0 Set plugin property instead of global var.
	 * @since 2.0 Update action hook tag.
	 */
	private function after_registering_type() {
		tws_cpt()->set_type( $this->key, $this, $this->request['id'] );

		$key  = $this->key;
		$type = $this->request['id'];

		/**
		 * WPHOOK: Action -> Fires immediately after registering type.
		 *
		 * @param Factory $this The current factory instance.
		 * @since 1.0
		 * @since 2.0 Renamed action hook prefix part from `registered_hzfex_`.
		 * @since 2.0 Removed registering type key from action hook tag
		 */
		do_action( "tws_registered_{$type}", $this );

		/**
		 * WPHOOK: Action -> Fires on the currently loaded registering type page.
		 *
		 * @param Factory $this The current factory instance.
		 * @since 2.0
		 */
		do_action( "tws_registered_{$type}_{$key}", $this );
	}

	/**
	 * Finalizes registering process with additional execution.
	 *
	 * * Assign taxonomy to post type.
	 *
	 * @return mixed function result, false on failure.
	 *
	 * @since 1.0
	 * @since 2.0 Renamed method name from `finish_registration`.
	 * @since 2.0 Return WP_Error if assign method not found.
	 */
	public function finish() {
		$type    = $this->request['id'];
		$method  = "__assign_{$type}";
		$objects = $this->assigned_objects;

		// Bail early if method doesn't exist.
		if ( ! method_exists( $this, $method ) ) {
			$this->error->add(
				'object_assign_method_not_found',
				"Can't assign objects for {$this->singular_name}."
			);

			return $this->error;
		}

		// Bail if no objects assigned.
		if ( empty( $objects ) ) {
			return false;
		}

		// Assign object by registering type.
		foreach ( $objects as $type ) {
			call_user_func( array( $this, $method ), $type );
		}
	}

	/**
	 * Method that actually registers the type.
	 *
	 * @return WP_Post_Type|WP_Taxonomy|WP_Error
	 *
	 * @since 1.0
	 * @since 2.0 Return WP_Error if factory registration method not found.
	 */
	public function register_type() {
		$register = $this->request['register'];

		// Bail if registering method doesn't exist.
		if ( ! method_exists( $this, "__{$register}" ) ) {
			$this->error->add(
				'register_method_not_found',
				"Can't register {$this->singular_name} {$this->request['type']} using {$register}."
			);

			return $this->error;
		}

		return call_user_func( array( $this, "__{$register}" ) );
	}

	/**
	 * Registers the post type.
	 *
	 * @return WP_Post_Type|WP_Error
	 *
	 * @since 1.0
	 */
	private function __register_post_type() {
		return register_post_type( $this->key, $this->args );
	}

	/**
	 * Registers the taxonomy.
	 *
	 * @return WP_Taxonomy|WP_Error
	 *
	 * @since 1.0
	 */
	private function __register_taxonomy() {
		return register_taxonomy( $this->key, null, $this->args );
	}

	/**
	 * Post Type where registering taxonomy is to be assigned.
	 *
	 * @param string $taxonomy The taxonomy to assing to the registering post type.
	 *
	 * @return bool True on success, false on failure.
	 *
	 * @since 1.0
	 */
	private function __assign_post_type( string $taxonomy ): bool {
		return taxonomy_exists( $taxonomy )
		? register_taxonomy_for_object_type( $taxonomy, $this->key )
		: false;
	}

	/**
	 * Taxonomy to be assigned to registering post type.
	 *
	 * @param string $post_type The post type for which taxonomy to be registered.
	 *
	 * @return bool True on success, false on failure.
	 *
	 * @since 1.0
	 */
	private function __assign_taxonomy( string $post_type ): bool {
		return post_type_exists( $post_type )
		? register_taxonomy_for_object_type( $this->key, $post_type )
		: false;
	}

	/**
	 * Checks for validity before registering.
	 *
	 * Validation checks are made using function:
	 * * registering type function exists
	 * * registering type name/key already exists in database
	 *
	 * @return bool
	 *
	 * @since 1.0
	 */
	private function is_valid_request(): bool {
		if ( is_callable( $this->request['register'], true ) && is_callable( $this->request['exists'], true ) ) {
			return function_exists( $this->request['register'] ) && false === call_user_func( $this->request['exists'], $this->key );
		}

		return false;
	}

	/**
	 * Gets the final args for the registering type.
	 *
	 * @return true successfully set args { @see @property Factory::$args }.
	 *
	 * {@see Factory::set_post_type_default_args() Args for Post Type}
	 * {@see Factory::set_taxonomy_default_args() Args for Taxonomy}
	 *
	 * @since 1.0
	 */
	public function get_args() {
		// Default args from method name according to registering type.
		$type_func = "__set_{$this->request['id']}_default_args";
		$args      = method_exists( $this, $type_func ) ? call_user_func( array( $this, $type_func ) ) : array();

		$this->get_labels();

		// Set args labels. Use $labels parameter from API instead of adding inside $args.
		// and this is exactly what we are validating and setting here.
		if ( ! isset( $args['labels'] ) ) {
			$args['labels'] = $this->labels;
		}

		// Set args value accordingly if frontend is disabled.
		// Additional value is set on respective method, if any.
		if ( $this->redirect_frontend ) {
			$args['public']             = false;
			$args['publicly_queryable'] = true;

			// if not using for filtering, remove querying capability.
			$args['query_var'] = is_bool( $this->filter_key ) && $this->filter_key ? $this->key : false;
		}

		// Finally, set property value.
		$this->args = $args;

		return true;
	}

	/**
	 * Gets the final labels for the registering type.
	 *
	 * @return true successfully set labels { @see @property Factory::$labels }.
	 *
	 * {@see Factory::set_post_type_default_labels() Labels for Post Type}
	 * {@see Factory::set_taxonomy_default_labels() Labels for Taxonomy}
	 *
	 * @since 1.0
	 */
	public function get_labels() {
		// Default args from method name according to registering type.
		$type_func = "__set_{$this->request['id']}_default_labels";
		$defaults  = method_exists( $this, $type_func ) ? call_user_func( array( $this, $type_func ) ) : array();

		// Replace defaults with that provided.
		// Using PHP function instead of `wp_parse_args` for
		// replacing elements in multi-dimensional array, if any.
		$labels = array_replace_recursive( $defaults, $this->labels );

		// Finally, set property value.
		$this->labels = $labels;

		return true;
	}

	/**
	 * Creates arguments for the registering type.
	 *
	 * @return array args to pass when registering type.
	 *
	 * @since 1.0
	 */
	private function set_default_args() {
		// Set "public, publicly_queryable, query_var, has_archive to "false".
		// This is to disable frontend with 404 code "page not found".
		$defaults = array(
			'public'             => false, // Disable frontend. Other options will override this.
			'publicly_queryable' => false, // If true, then "query" and "publicly accessible URL" works.
			'query_var'          => $this->key, // Can be string, empty, null, or FALSE. no effect if "publicly_queryable" is false. Won't filter Admin Post List table if set to false.
			'show_ui'            => true, // Display and workable at admin.

			// Set to false for non-public and non-queriable registering types as no need to change slugs.
			'rewrite'            => array(
				'slug'       => $this->slug, // Also used for flushing rewrite rules, if changed.
				'with_front' => false,
			),
		);

		return $defaults;
	}

	/**
	 * Creates the labels for the registering type.
	 *
	 * @return string[] labels to pass to $args['labels'] of the registering type.
	 *
	 * @since 1.0
	 */
	public function set_default_labels(): array {
		return array(
			'name'                  => $this->plural_name,
			'singular_name'         => $this->singular_name,
			'menu_name'             => $this->plural_name,
			'add_new_item'          => "Add New {$this->singular_name}",
			'edit_item'             => "Edit {$this->singular_name}",
			'all_items'             => "All {$this->plural_name}",
			'view_item'             => "View {$this->singular_name}",
			'update_item'           => "Update {$this->singular_name}",
			'search_items'          => "Search {$this->plural_name}",
			'not_found'             => "No {$this->plural_name} found",
			'parent_item_colon'     => "Parent {$this->singular_name}",
			'items_list_navigation' => "{$this->singular_name} list navigation",
			'items_list'            => "{$this->plural_name} list",
		);
	}

	/**
	 * Sets additional default args for Post Type.
	 *
	 * Additional args will then be merged to default args.
	 * {@see @method Factory::set_default_args()}
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	private function __set_post_type_default_args() {
		$defaults = array(
			'has_archive'         => false, // Bool/string to have archive page with slug.
			'exclude_from_search' => true, // Disable from search on frontend. direct impact on the custom taxonomy archives. i.e. won't show post types in taxonomy archive pages.
			'supports'            => array( 'title', 'author', 'excerpt' ),
		);

		// Merge post defaults and common registering type defaults.
		$defaults = array_merge( $this->set_default_args(), $defaults );

		// Merge with args from API.
		$args = $this->finalize_args( $defaults );

		// Set args value accordingly if frontend is disabled.
		if ( $this->redirect_frontend ) {
			$args['has_archive'] = true;
		}

		return $args;
	}

	/**
	 * Sets additional default labels for Post Type.
	 *
	 * Additional labels will then be merged to default labels.
	 * {@see @method Factory::set_default_labels()}
	 *
	 * @return string[]
	 *
	 * @since 1.0
	 */
	private function __set_post_type_default_labels(): array {
		$defaults = array(
			'name_admin_bar'           => $this->singular_name,
			'add_new'                  => "Add New {$this->singular_name}",
			'new_item'                 => "New $this->singular_name}",
			'view_items'               => "View {$this->plural_name}",
			'not_found_in_trash'       => "No {$this->plural_name}",
			'archives'                 => "{$this->singular_name} 'Archives",
			'attributes'               => "{$this->singular_name} 'Attributes",
			'filter_items_list'        => "Filter {$this->singular_name}",
			'item_published'           => "{$this->singular_name} published",
			'item_published_privately' => "{$this->singular_name} published privately",
			'item_reverted_to_draft'   => "{$this->singular_name} reverted to draft",
			'item_scheduled'           => "{$this->singular_name} scheduled",
			'item_updated'             => "{$this->singular_name} updated",
		);

		// Merge post defaults and common registering type defaults.
		return array_merge( $this->set_default_labels(), $defaults );
	}

	/**
	 * Sets additional default args for Taxonomy.
	 *
	 * Additional args will then be merged to default args.
	 * {@see @method Factory::set_default_args()}
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	private function __set_taxonomy_default_args() {
		$defaults = array(
			'hierarchical'      => true, // Like category (not tags).
			'show_admin_column' => true, // Display column on post type list.
		);

		// Merge taxonomy defaults and common registering type defaults.
		$defaults = array_merge( $this->set_default_args(), $defaults );

		// Merge with args from API.
		$args = $this->finalize_args( $defaults );

		return $args;
	}

	/**
	 * Sets additional default labels for Taxonomy.
	 *
	 * Additional labels will then be merged to default labels.
	 * {@see @method Factory::set_default_labels()}
	 *
	 * @return string[]
	 *
	 * @since 1.0
	 */
	private function __set_taxonomy_default_labels(): array {
		$defaults = array(
			'new_item_name'              => "New {$this->singular_name} Name",
			'parent_item'                => "Parent {$this->singular_name}",
			'popular_items'              => "Popular {$this->plural_name}",
			'separate_items_with_commas' => "Separate {$this->plural_name} with commas",
			'add_or_remove_items'        => "Add or remove {$this->plural_name}",
			'choose_from_most_used'      => "Choose from most used {$this->plural_name}",
		);

		// Merge taxonomy defaults and common registering type defaults.
		return array_merge( $this->set_default_labels(), $defaults );
	}

	/**
	 * Merge default args of registering type with that from API parameter.
	 *
	 * Replace defaults with that supplied from API parameter.
	 * Using PHP function `array_replace_recursive` here
	 * instead of `wp_parse_args` for replacing elements
	 * in multi-dimensional array like `rewrite` and `supports`.
	 *
	 * @param array $defaults Registering Type default args.
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	private function finalize_args( array $defaults ): array {
		return array_replace_recursive( $defaults, $this->args );
	}

	/**
	 * Redirect frontend pages of the registering type.
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	public function frontend_redirect() {
		if ( $this->is_valid_page() ) {
			$this->__frontend_redirect();
		}
	}

	/**
	 * Registering type page where the factory is being called.
	 *
	 * This method validates on:
	 * * which page is being called, and
	 * * which registering type is calling it.
	 *
	 * @return bool True if valid page, false if not.
	 *
	 * @since 1.0
	 */
	private function is_valid_page() {
		// Get the method by registering type.
		$method = "__is_{$this->request['id']}_page";

		return ! method_exists( $this, $method ) ? false : $this->$method();
	}

	/**
	 * Redirects registering type when frontend accessed via URL.
	 *
	 * Redirects direct accessible URL to home with `301` _**Moved Permanently**_ Status Response Code.\
	 * `publicly_queryable` args value must be set to `true` _(which it is if redirection is true)_ \
	 * for `is_archive()`, `is_post_type_archive()`, `is_singular()` and `wp_redirect()` to work.
	 *
	 * @return bool True if redirection successful, false if not.
	 *
	 * @since 1.0
	 */
	private function __frontend_redirect() {
		global $wp_query;

		if ( ! isset( $wp_query ) ) {
			_doing_it_wrong( __METHOD__, 'Redirection failed because it ran before global var "$wp_query".', '1.0' );

			return false;
		}

		// Registering type redirection args.
		$add_args = "__redirect_{$this->request['id']}_args";

		/**
		 * WPHOOK: Filter -> set redirection url
		 *
		 * @var bool
		 * @since 1.0
		 * @example usage:
		 * ```
		 * add_filter("tws_redirect_{$post_type_name}_to","http://mycustom.url");
		 * ```
		 */
		$to = apply_filters( "tws_redirect_{$this->key}_to", admin_url() );

		/**
		 * WPHOOK: Filter -> Add/remove query args on destination (`$to`) URL.
		 *
		 * @var bool
		 * @since 1.0
		 * @example usage:
		 * ```
		 * add_filter("tws_has_{$post_type_key}_redirect_query_args","__return_false");
		 * ```
		 */
		$has_arg = apply_filters( "tws_has_{$this->key}_redirect_query_args", true );

		$url                  = esc_url_raw( $to );
		$this->redirect_query = array(
			'http_referrer' => rawurlencode_deep( $_SERVER['REQUEST_URI'] ), // phpcs:ignore -- Supoerglobal set.
			'public'        => 0, // Is false.
			'redirect'      => 1, // Is true.
		);

		if ( $has_arg ) {
			// Set additional query parameters.
			if ( method_exists( $this, $add_args ) ) {
				$this->$add_args();
			}

			// Set redirection URL with query args.
			$url = htmlspecialchars_decode( add_query_arg( $this->redirect_query, $url ) );
		}

		// Redirect page with 301 moved permanently HTTP response code for SEO compatibility.
		wp_safe_redirect( $url, $this->redirect_code );
		exit;
	}

	/**
	 * Custom Post type single post page or archive page.
	 *
	 * This method returns true if:
	 * * Current page is Custom Post Type single post page, or
	 * * Current page is Custom Post Type archive page. i.e. `siteurl/cpt-slug` page.
	 *
	 * @return bool True is valid page, false if not.
	 *
	 * @since 1.0
	 */
	private function __is_post_type_page() {
		return is_singular( $this->key ) || is_post_type_archive( $this->key );
	}

	/**
	 * Custom taxonomy archive page.
	 *
	 * @return bool True if is valid page, false if not.
	 *
	 * @since 1.0
	 */
	private function __is_taxonomy_page() {
		$terms = $this->redirect_terms;

		// Set terms as empty string if not set so all terms get redirected.
		if ( count( $terms ) === 0 || empty( $terms[0] ) ) {
			$terms = '';
		}

		return is_tax( $this->key, $terms );
	}

	/**
	 * Sets post type page redirection args.
	 *
	 * @since 1.0
	 */
	private function __redirect_post_type_args() {
		$this->redirect_query['post_type'] = $this->key;

		if ( is_singular( $this->key ) ) {
			$this->redirect_query['referrer_type'] = 'singular'; // Is singular page.
		}

		if ( is_post_type_archive( $this->key ) ) {
			$this->redirect_query['referrer_type'] = 'archive'; // Is post type archive page.
		}
	}

	/**
	 * Sets taxonomy page redirection args.
	 *
	 * @since 1.0
	 */
	private function __redirect_taxonomy_args() {
		$this->redirect_query['taxonomy'] = $this->key;

		$term = get_queried_object()->slug;

		if ( in_array( $term, $this->redirect_terms, true ) ) {
			$this->redirect_query['term'] = $term; // Is taxonomy term defined for redirection.
		}
	}

	/**
	 * Handles rewrite rules.
	 *
	 * Flush rewrite rule if registering type slug gets changed.
	 *
	 * This will execute `flush_rewrite_rules()` function
	 * which will restructure the permalinks from newly changed slug.
	 *
	 * Here are the options provided by WordPress:
	 *
	 * - Recommended way for plugins to use `flush_rewrite_rules()`
	 *   is to hook into `register_activation_hook` and use there.
	 *   Since, it has limitation of running only once during plugin
	 *   activation, it won't restructure permalinks until plugin is
	 *   deactivated and reactivated agian.
	 *
	 * - Another option is to go to `settings->permalinks` and
	 *   click `Save Changes` to restructure permalink everytime
	 *   slug has been changed, which isn't ideal situation either.
	 *
	 * ---------
	 * To overcome this, `Factory` has a handy property.
	 * { @see @property Factory::$flush_rewrite_rule }.
	 * The property value will be set to true once the slug
	 * gets changed and triggers rewrite rules.
	 * { @see @method Factory::rewrite_handle()}.
	 *
	 * The value should be reset from registering type class
	 * so it won't run more than once.
	 * ---------
	 *
	 * This way it overcomes both limitations WordPress has and provides:
	 * - no any _**Expensive Operation**_ of running it on each page load.
	 * - no _**Overhead Cost**_ to WordPress installation.
	 *
	 * Furthermore,
	 * * Handling occurs on both backend and frontend.
	 * * Backend will automatically flush rules if slug is changed.
	 * * Frontend will show die message for one time as rewrite may not be handled from frontend.
	 * * Frontend shows 404 not found if visits page with new slug.
	 *
	 * ### Switching browser's frontend and backend tab after changing the registering type slug seems to not work properly. For eg:
	 * - **Both homepage and dashboard page are opened.**
	 *
	 * #### CASE 1:
	 * - **Being on homepage tab, slug is changed in code.**
	 * - **User switched to dashboard page and reloads.**
	 * - **Flushing is triggered but no admin notice shown.**
	 *
	 * #### CASE 2:
	 * - **Being on dashboard, slug is changed in code.**
	 * - **User switched to homepage and reloads.**
	 * - **Page won't die to notify slug has been changed.**
	 *
	 * @since 1.0
	 * @since 2.0 Show notice when on admin dashboard.
	 * @since 2.0 WP die when debug on and is on frontend.
	 */
	private function rewrite_handle() {
		$type = $this->request['type'];

		// Flush the rewrite rules.
		flush_rewrite_rules( true );

		if ( is_admin() ) {
			$this->admin_rewrite_handle();

			$this->flush_rewrite_rule = false;

			return;
		}

		if ( self::$debug && is_user_logged_in() && current_user_can( 'manage_options' ) ) {
			$title   = "{$this->key} $type Slug Changed";
			$message = sprintf(
				'<p style="text-decoration:line-through;opacity:0.3">%1$s</p><hr><div style="color:#b71c1c"><h3>Frontend may not flush rewrite rules properly after slug changed. To save new slug, visit admin dashboard.</h3><p style="text-align:center;padding:10px 20px; border:2px solid #b71c1c;border-radius:5px;"><b><a href="%2$s">Visit Admin Dashboard</a></b></p><b><em>This error may get displayed again until dashboard is visited.</em></b></div><hr><h4>HTTP Response Code: 500</h4><p><b>This page has died with HTTP Response code of 500 due to changes made in URL (specifically the slug).</b></p>',
				$this->rewrite_handle_message(),
				esc_url( admin_url() )
			);

			$this->error->add( 'registering_type_slug_changed', $message );

			$this->shutdown( $title, 'registering_type_slug_changed' );
		}
	}

	/**
	 * Shows rewrite notice.
	 */
	public function add_notice() {
		$msg = $this->rewrite_handle_message() . ' <span>Furthermore, 404 error on old slug URL can be handled using <a href="https://wordpress.org/plugins/redirection/" target="_blank"><em>Redirection Plugin</em></a>.</span>';

		echo '<div class="notice notice-warning is-dismissible"><p>' . wp_kses_post( $msg ) . '</p></div>';
	}

	/**
	 * Displays admin notice when slug gets changed.
	 *
	 * Notice can only be displayed if this plugin
	 * is included within core plugin or core plugin
	 * is installed and activated separately.
	 *
	 * @since 1.0
	 * @since 2.0 Made method public and notice added directly.
	 * @since 2.0 Removed core plugin requirement.
	 */
	public function admin_rewrite_handle() {
		add_action( 'admin_notices', array( $this, 'add_notice' ) );
	}

	/**
	 * HTML message to display when handling flushing of rewrite rule.
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	private function rewrite_handle_message(): string {
		return sprintf(
			'%1$s slug of <b>%2$s</b> key has been changed. <b><code><em>flush_rewrite_rules()</em></code></b> function has been triggered to restructure the permalink. Although not required, but as a double measure, permalink rules can be flushed by clicking "Save Changes" from <a href="%3$s"><b><em>Settings->Permalinks</em></b></a>.',
			$this->request['type'],
			$this->key,
			admin_url( 'options-permalink.php' )
		);
	}
}
