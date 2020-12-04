<?php
/**
 * TheWebSolver\Core\Helper\CPT_Factory class
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

use WP_Error;
use TheWebSolver\Core\admin\Notice;

/**
 * Factory for the Registering Post Type and Taxonomy to follow DRY development.
 * 
 * This class should be instantiated from each registering type constructor\
 * by supplying proper parameters to set it's properties' values.
 * 
 * It will be used as structure for setting, validating and saving each registering type values.
 * 
 * @api
 */
final class CPT_Factory implements CPT_Interface {
    /**
     * Key of the registering type.
     * 
     * ###  CAUTION
     * #### - This will be the name of the registering type and should never be changed.
     * #### - If changed, then another type will get registered instantly.
     * #### - All data created within this type won't be automatically migrated to changed the one.
     *
     * @var string
     * 
     * @since 1.0
     * 
     * @access public
     */
    public $key;

    /**
     * The singular name of the registering type.
     * 
     * Used in $args['label']['singular_name']\
     * {@see @method CPT_Factory::_set_names()}
     * 
     * @var string
     * 
     * @since 1.0
     * 
     * @access public
     */
    public $singular_name;

    /**
     * The name of the registering type. Usually plural.
     * 
     * Used in $args['labels']['name']\
     * {@see @method CPT_Factory::_set_names()}
     * 
     * @var string
     * 
     * @since 1.0
     * 
     * @access public
     */
    public $plural_name;

    /**
     * The slug of the registering type.
     * 
     * Used in $args['rewrite']['slug']\
     * {@see @method CPT_Factory::_set_names()}
     * 
     * @var string
     * 
     * @since 1.0
     * 
     * @access public
     */
    public $slug;

    /**
     * An array of labels of the registering type.
     * 
     * Used in $args['labels'].\
     * {@see @method CPT_Factory::set_labels()}
     * 
     * @var array
     * 
     * @since 1.0
     * 
     * @access public
     */
    public $labels;

    /**
     * An array of arguments of the registering type.
     * 
     * Used in registering type function.\
     * {@see @method CPT_Factory::set_args()}
     * 
     * @var array
     * 
     * @since 1.0
     * 
     * @access public
     */
    public $args;

    /**
     * Whether to enable frontend access using URL.
     * 
     * This ensures publicly disabled registering type to be hidden\
     * by redirecting to the desired URL when accessed directly.
     *
     * @var bool
     * 
     * @since 1.0
     * 
     * @access public
     */
    public $redirect_frontend;

    /**
     * Flush rewrite rules handler.
     * 
     * It's value becomes `true` for one time on page reload\
     * after changing the slug of the registering type.\
     * {@see @method CPT_Factory::_validate_names()}
     *
     * @var bool
     * 
     * @since 1.0
     * 
     * @access public
     */
    public $flush_rewrite_rule = false;

    /**
     * Registering Post type list filtering keys.
     * 
     * @var string[]|bool
     * * Taxonomies/Post Meta Keys in array if post type.
     * * Whether can be used for filtering if taxonomy.
     * 
     * @since 1.0
     * 
     * @access public
     */
    public $filter_key;

    /**
     * Registering type assigned objects.
     * 
     * @var string|string[]
     * 
     * @since 1.0
     * 
     * @access public
     */
    public $assigned_objects;

    /**
     * Columns for admin table.
     * 
     * @var array
     * 
     * @since 1.0
     * 
     * @access public
     */
    public $columns;

    /**
     * Sets debugging state.
     *
     * @var bool
     * 
     * @since 1.0
     * 
     * @static
     * 
     * @access public
     */
    private static $_debug;

    /**
     * Option key to get values using `get_option()` API
     *
     * @var string
     * 
     * @since 1.0
     * 
     * @static
     * 
     * @access public
     */
    private static $_option;

    /**
     * Validation request of the registering type.
     * 
     * Values are extracted from the respective registering class names.
     *
     * @var string[]
     * * @type `string` `$id` - Registering type. Possible values are **post_type|taxonomy**.
     * * @type `string` `$register` - The registering type function exists. Possible values are **register_post_type|register_taxonomy**.
     * * @type `string` `$exists` - Whether this registering type key already registered. Possible values are **post_type_exists|taxonomy_exists**.
     *  * @type `string` `$type` - The registering type name. Possible values are **Post Type|Taxonomy**.
     * 
     * @since 1.0
     * 
     * @access public
     */
    private $_request;

    /**
     * Holder for the names from API parameter.
     * 
     * It will be used to assign values to:
     * * @property CPT_Factory::$key
     * * @property CPT_Factory::$singular_name
     * * @property CPT_Factory::$name
     * * @property CPT_Factory::$slug
     * 
     * @var string[]
     * 
     * @since 1.0
     * 
     * @access public
     */
    private $_names;

    /**
     * Key character length of the registering type.
     * 
     * The WordPress recommended maximum key character length\
     * as key { @see @property CPT_Factory::$key } will get saved to database.
     *
     * @var int
     * 
     * @since 1.0
     * 
     * @access public
     */
    private $_key_length;

    /**
     * Taxonomy terms of the registering taxonomy to be used for redirection.
     * 
     * @var string[]
     * 
     * @since 1.0
     * 
     * @access public
     */
    private $_redirect_terms = [];

    /**
     * HTTP Status Response code for redirection of the registering type.
     * 
     * @var int Defaults to `302` - Moved Temporarily.
     *
     * @since 1.0
     * 
     * @access public
     */
    private $_redirect_code = 302;

    /**
     * Query args in redirection URL of the registering type.
     * 
     * @var array
     * 
     * @since 1.0
     * 
     * @access public
     */
    private $_redirect_query = [];

    /**
     * Starts factory.
     * 
     * @param string $class **required** Name of the class where factory is instantiated.
     * @param string[] $names **required** Should be in key/value pair of:
     * * @type `string` `$key` **required** The registering type key.
     * * @type `string` `$slug` **required** The registering type slug.
     * * @type `string` `$singular_name` _optional_ Generated from key, if not provided.
     * * @type `string` `$plural_name` _optional_ Generated from key, if not provided.
     * 
     * @param int $key_length **required** Length of key character limit set by WordPress
     * 
     * @return CPT_Factory|void Factory instance if called from valid class, die message if not.
     * 
     * @since 1.0
     * 
     * @static Start factory engine from `Post_Type` or `Taxonomy` class privately.
     * 
     * @access public
     */
    public static function start_engine_with( string $class, array $names, int $key_length ) {
        // Only proceed if factory started from `Post_Type` or `Taxonomy` class.
        // Offset with 22 namespace characters for more strict validation.
        if( 22 === strpos( $class, "Post_Type", 22 ) || 22 === strpos( $class, "Taxonomy", 22 ) ) {
            return new self( $class, $names, $key_length );
        }

        // Die with message that factory can't be started from unrecognized class.
        return wp_die( 'Can\'t start factory engine because "'.get_class().'" is initialized from unrecognized class "'.$class.'".', "Cannot start factory" );
    }

    /**
     * Private constructor to prevent direct instantiation.
     *
     * @param string $class
     * @param string[] $name
     * @param int $key_length
     * 
     * @since 1.0
     * 
     * @access private
     */
    private function __construct( string $class, array $names, int $key_length ) {
        // Set raw names from API parameter.
        $this->_names = $names;

        // Sets debugging mode.
        self::$_debug = defined( "HZFEX_DEBUG_MODE" ) && HZFEX_DEBUG_MODE;

        // WordPress recommended key length to register the type.
        $this->_key_length = $key_length;

        // Sets registering type data from classname.
        $this->_set_request_prop( $class );

        // Shutdown factory if can't continue further.
        if( ! $this->_can_start() ) {
            $this->_shutdown_factory(); // Die with error message.
        }

        /*****************************************************
          ╔══════════════════════════════════════════════════╗
         ║ If we arrive here, then factory is still running.║
        ╚══════════════════════════════════════════════════╝
        **************************************************/

        // Validate proper names.
        $this->_validate_names();
    }

    /**
     * Validation check if factory should continue further.
     *
     * @return bool True if `key` and `slug` index is set in names { @see @property CPT_Factory::$_names }, false if not.
     * 
     * @since 1.0
     * 
     * @access private
     */
    private function _can_start() {
        // If is WP_Error, bail.
        if( is_wp_error( $this->_maybe_valid_names() ) ) {
            return false;
        }
        return true;
    }

    /**
     * Sets request property values from classname.
     * 
     * @param string $class
     *
     * @return void
     * 
     * @since 1.0
     * 
     * @access public
     */
    private function _set_request_prop( string $class ) {
        $class_array    = explode( '\\', $class ); // convert class with namespace to array.
        $request        = strtolower( array_pop( $class_array ) ); // get classname from array.

        $this->_request['id']        = $request; // registering type
        $this->_request['register']  = "register_{$request}"; // registering type function 
        $this->_request['exists']    = "{$request}_exists"; // type existence check function
        $this->_request['type']      = ucwords( str_replace( "_", " ", $request ) ); // registering type name

        self::$_option = "tws_registered_{$request}";
    }

    /**
     * Sets the labels for the registering type.
     * 
     * @param  array $labels An array of labels for the registering type
     * 
     * @return void
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function set_labels( array $labels ) {
        $this->labels = $labels;
    }

     /**
     * Sets arguments for the registering type.
     * 
     * @param  array $args An array of args for the registering type
     * 
     * @return void
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function set_args( array $args ) {
        $this->args = $args;
    }

    /**
     * Assign object for the registering type.
     * 
     * @param string|string[] $objects
     * 
     * @return void
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function assign_objects( $objects ) {
        // Convert to array if single post type provided as string.
        $this->assigned_objects = ! is_array( $objects ) ? [$objects] : (array) $objects;
    }

    /**
     * Sets redirection properties.
     * 
     * Following key/value of args { @see @property CPT_Factory::$args }\
     * will be forced set if `$enabled` is `true` for redirection to work.
     * * `public` => `false`
     * * `publicly_queryable` => `true`
     * * `has_archive` => `true` _(only for Post Type args)_
     * * `query_var` => `false`
     *
     * @param array $args
     * * @type `bool` `$enabled` Frontend redirection enabled or not.
     * * @type `string|string[]` `$terms` Taxonomy terms if taxonomy has redirection enabled.
     * * @type `int` `response_code` HTTP Status Response Code for redirection.
     * 
     * @return void
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function set_redirect( array $args ) {
        $this->redirect_frontend = isset( $args['enabled'] ) && is_bool( $args['enabled'] ) ? $args['enabled'] : false;

        if( isset( $args['terms'] ) && ! empty( $args['terms'] ) ) {
            $this->_redirect_terms = ! is_array( $args['terms'] ) ? [$args['terms']] : (array) $args['terms'];
        }

        if( isset( $args['response_code'] ) && ! empty( $args['response_code'] ) && is_int( $args['response_code'] ) ) {
            $this->_redirect_code = (int) $args['response_code'];
        }
    }

    /**
     * Validates and sets the registering type names.
     * 
     * This should only be executed if factory can start.\
     * {@see @method CPT_Factory::_can_start()}
     *
     * @return void
     * 
     * @since 1.0
     * 
     * @access public
     */
    private function _validate_names() {
        // From option.
        $types      = get_option( self::$_option );
        $type       = isset( $types[$this->key] ) ? $types[$this->key] : false;

        // Prepare option values.
        // Save current registering type key and names as key/value pair.
        // Only perform if first time registering new type.
        if( ! $type ) {
            $types[$this->key] = [];
            update_option( self::$_option, $types );
        }

        // Get option values.
        $saved_type = get_option(self::$_option)[$this->key];
        $saved_slug = isset( $saved_type['slug'] ) ? $saved_type['slug'] : '';

        // From API parameters.
        $slug       = $this->_sanitize_name( $this->_names['slug'] );

        // Prepare slug.
        $this->slug = strtolower( $slug );

        // Checks if slug value has been changed from the option previously saved.
        if( $this->slug !== $saved_slug ) {
            // Sets flush value to true.
            $this->flush_rewrite_rule = true;

            // Saves rewrite rule as value to option of registering type.
            // It can then be used to handle rewrite rule.
            $types[$this->key]['slug_changed'] = true;
            update_option( self::$_option, $types );
        }

        // Update slug value to option.
        $this->_update_option('slug');

        // Prepare names for $args['labels']
        $this->_set_names();
    }

    /**
     * Shutdown with die message if `WP_Error` returns true.
     * 
     * For die message: {@see wp_die()}.
     * For wp error check: {@see is_wp_error()}.
     * 
     * @return string die message.
     * 
     * @since 1.0
     * 
     * @access public
     */
    private function _shutdown_factory() {
        $type = $this->_request['type'];
        $this->_error_message( "$type Registration Failed", "names", "_maybe_valid_names" );
    }

    /**
     * Checks if names parameter supplied for registering type has any errors.
     * 
     * * names should be supplied as an array. { @see @property CPT_Factory::$names }
     * * names array should have atleast `key` and `slug` key/value pair set.
     * * `key` character length should not exceed the number specified. { @see @property CPT_Factory::$key_length }
     * 
     * @return WP_Error|true `WP_Error` object if $names not supplied properly, `true` if it is.
     * 
     * @since 1.0
     * 
     * @access public
     */
    private function _maybe_valid_names() {

        $type       = $this->_request['type'];
        $names      = $this->_names;
        $key_length = $this->_key_length;

        $error      = new WP_Error();
        $names_val  = [];
        $message    = "<p><b>$type Key and Slug</b> must be set in <code><em>key=>value</em></code> pair in an array to get registered. <b><em>_ (underscore)</em></b> and <b><em>- (dash)</em></b> can be used interchangeably for the array value.</p><p>Following <code><em>key=>value</em></code> missing in the array for <code><em>\$names</em></code> parameter.</p>";
        
        // If not an array.
        if( ! is_array( $names ) ) {
            $error->add( 'names', sprintf( "<b>%s</b> must must be an array to register $type.", $names ) );
            return $error;
        }

        // If key not set or has invalid value.
        if( ! isset( $names['key'] ) || empty( $names['key'] ) || ! is_string( $names['key'] ) ) {
            $names_val[] .= "'key' => 'custom-name'";
        }

        // If slug not set or has invalid value.
        if( ! isset( $names['slug'] ) || empty( $names['slug'] ) || ! is_string( $names['slug'] ) ) {
            $names_val[] .= "'slug' => 'custom-slug'";
        }

        // If any of the below case matches, add error message.
        if(
            ! isset( $names['key'] ) ||
            empty( $names['key'] ) ||
            ! is_string( $names['key'] ) ||
            ! isset( $names['slug'] ) ||
            empty( $names['slug'] ) ||
            ! is_string( $names['slug'] )
        ) {
            $names_val_in_array = implode( ', ', $names_val );
            $message .= '<pre>' . print_r( "[{$names_val_in_array}]", true ) . '</pre>';
            $error->add( 'names', $message );
            return $error;
        }

        // Sanitized key.
        $sanitized_key = sanitize_key( $names['key'] );

        // WordPress recommended key length check, add error message if failed.
        if( strlen( $sanitized_key ) > $key_length ) {
            /* Translators - 
            %1$s: key exceed length text
            %2$s: registering type name/key
            %3$s: exceeds word
            %4$s: registering type name/key length
            %5$s: after sanitaztion text
            %6$s: sanitization function
            */
            $message = sprintf( '<p>%1$s</p><p><code><b><em>%2$s</em></b></code>%3$s<b>%4$s</b> %5$s <code><b>%6$s</b></code>.</p>',
                "$sanitized_key $type Key must not exceed $key_length characters in length.",
                $sanitized_key,
                ' exceeds "',
                $key_length,
                '" characters limit even after sanitization using WordPress function',
                'sanitize_key()'
            );
            $error->add( 'names', $message );
            return $error;
        }

        // Sets registering type key if everything is valid.
        $this->key = $sanitized_key;

        // If names are valid, return true.
        return true;
    }

    /**
     * Sets names for the $names parameter.
     *
     * @return void
     * 
     * @since 1.0
     * 
     * @access public
     */
    private function _set_names() {
        // required properties whose values to be set.
        $props  = ['singular_name', 'plural_name'];

        // Iterate over props to set values to.
        foreach( $props as $prop ) {
            // Triggers if API parameters have necessary property values supplied.
            if ( isset( $this->_names[$prop] ) ) {
                // Sets each property value.
                $this->$prop = $this->_names[$prop];

                // Updates option's value of each property.
                $this->_update_option( $prop );

                // Only continue if $prop isn't already passed as parameter.
                // We'll omit setting value for `key` and `slug` props
                // as it's mandatory to register the type.
                // They are required to be passed as parameter from the API.
                continue;
            }

            // If property is not set and property is singular or name (usually plural).
            if ( in_array( $prop, ['singular_name', 'plural_name'] ) ) {
                // Sets a human friendly name.
                $value = ucwords( strtolower( str_replace( ['-', '_'], ' ', $this->key ) ) );
            }

            // If property is not set and property is name (usually plural, so), append an 's'.
            if ( $prop === 'plural_name' ) {
                // Adds "s" at the end of human friendly name to make it plural.
                $value .= 's';
            }

            // Sets each property value.
            $this->$prop = $value;

            // Updates option's value of each property.
            $this->_update_option( $prop );
        }
    }

    /**
     * Sanitizes names to machine readable format.
     *
     * @param string $name Name to be sanitized.
     * 
     * @return string The sanitized name.
     * 
     * @since 1.0
     * 
     * @access public
     */
    private function _sanitize_name( $name ): string {
        // Replaces all spaces with hyphens.
        $out   = str_replace( ['_', ' '], '-', $name );

        // Removes special characters.
        $out   = preg_replace( '/[^A-Za-z0-9\-]/', '', $out );
        return $out;
    }

    /**
     * Updates option value to database.
     * 
     * This will update values either from property values set from API parameter or auto generated.
     *
     * @param array $prop Property name.
     * 
     * @return bool true on success, false on failure.
     * 
     * @see {@method CPT_Factory::_set_names()}
     * 
     * @since 1.0
     * 
     * @access public
     */
    private function _update_option( $prop ): bool {

        $key = $this->key;

        $option = get_option( self::$_option );

        // Bail early if this registering type hasn't been saved as option.
        if( ! isset( $option[$key] ) ) return false;

        // Set option values.
        if( $prop === 'singular_name' ) $option[$key]['singular_name'] = $this->singular_name;
        if( $prop === 'plural_name' ) $option[$key]['plural_name'] = $this->plural_name;
        if( $prop === 'slug' ) $option[$key]['slug'] = $this->slug;

        // Update option values.
        update_option( self::$_option, $option );

        // Return true after successfully saving option.
        return true;
    }

    /**
     * Starts the registration process.
     *
     * @return bool
     * set registering type args { @see @property CPT_Factory::$args }, else\
     * false if not valid request {@see @method CPT_Factory::_is_valid_request()}.
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function start_registration(): bool {
        if( ! $this->_is_valid_request() ) {
            return false;
        }
        return $this->get_args();
    }

    /**
     * Main method to handle registering type.
     * 
     * This method should be called from respective registering type
     * `register()` method that is added to `init` hook to perform actual
     * registration process.
     * 
     * @return void
     * 
     * @see {@method `CPT_Factory::_register_type()`}
     * @see {@method `CPT_Factory::_after_registering_type()`}
     * @see {@method `CPT_Factory::_rewrite_handle()`}
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function register() {
        $this->_register_type();
        $this->_after_registering_type();

        $key    = "tws_registered_{$this->_request['id']}";
        $type   = get_option( $key )[$this->key];

        // Flush rewrite rule if slug gets changed.
        if( isset( $type['slug_changed'] ) ) {
            $this->_register_type();
            $this->_rewrite_handle();
        }
    }

    /**
     * Performs tasks after type registration.
     * 
     * * Sets `CPT_Factory` as global var of respective registering type.
     * * Creates `do_action` hook passing `CPT_Factory` as parameter.
     *
     * @return void
     * 
     * @since 1.0
     * 
     * @access public
     */
    private function _after_registering_type() {
        $GLOBALS["tws_registered_{$this->_request['id']}"];

        // Set CPT_Factory to global vars.
        $GLOBALS["tws_registered_{$this->_request['id']}"][$this->key] = $this;

        /**
         * WPHOOK: Action -> Fires immediately after registering type.
         * 
         * @param CPT_Factory $this - The current instance of CPT_Factory.
         * 
         * @since 1.0
         */
        do_action( "registered_hzfex_{$this->key}_{$this->_request['id']}", $this );
    }

    /**
     * Finalize registering process with additional execution.
     * 
     * * Assign taxonomy to post type.
     *
     * @return mixed function result, false on failure.
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function finish_registration() {
        $type       = $this->_request['id'];
        $method     = "__assign_{$type}";
        $objects    = $this->assigned_objects;

        // Bail early if method doesn't exist.
        if( ! method_exists( $this, $method ) ) {
            return false;
        }

        // Bail if no objects assigned.
        if( empty( $objects ) ) {
            return false;
        }

        // Assign object by registering type.
        foreach ( $objects as $type ) {
            call_user_func( [$this, $method], $type );
        }
    }

    /**
     * Method that actually registers the type.
     *
     * @return \WP_Post_Type|\WP_Taxonomy|\WP_Error|false
     * 
     * @since 1.0
     * 
     * @access public
     */
    private function _register_type() {
        $register = $this->_request['register'];

        // Bail if registering method doesn't exist.
        if( ! method_exists( $this, "__{$register}" ) ) return false;

        call_user_func( [ $this, "__{$register}" ] );
    }
    
    /**
     * Registers the post type.
     * 
     * @return \WP_Post_Type|\WP_Error
     * 
     * @since 1.0
     * 
     * @access private
     */
    private function __register_post_type() {
        register_post_type( $this->key, $this->args );
    }

    /**
     * Registers the taxonomy.
     *
     * @return \WP_Taxonomy|\WP_Error
     * 
     * @since 1.0
     * 
     * @access private
     */
    private function __register_taxonomy() {
        register_taxonomy( $this->key, null, $this->args );
    }

    /**
     * Post Type where registering taxonomy is to be assigned.
     * 
     * @return bool True on success, false on failure.
     * 
     * @since 1.0
     * 
     * @access private
     */
    private function __assign_post_type( $taxonomy ) {
        // Register taxonomy to the post type, if taxonomy exists.
        if( taxonomy_exists( $taxonomy ) ) {
            register_taxonomy_for_object_type( $taxonomy, $this->key );
        }
        return false;
    }

    /**
     * Taxonomy to be assigned to registering post type.
     * 
     * @return bool True on success, false on failure.
     * 
     * @since 1.0
     * 
     * @access private
     */
    private function __assign_taxonomy( $post_type ) {
        // Register taxonomy to the post type, if post type exists.
        if( post_type_exists( $post_type ) ) {
            register_taxonomy_for_object_type( $this->key, $post_type );
        }
        return false;
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
     * 
     * @access public
     */
    private function _is_valid_request(): bool {
        if( is_callable( $this->_request['register'], true ) && is_callable( $this->_request['exists'], true ) ) {
            return
            function_exists( $this->_request['register'] ) && 
            false === call_user_func( $this->_request['exists'], $this->key );

            // TODO: add classname has been changed error.
        }
        return false;
    }

    /**
     * Gets the final args for the registering type.
     * 
     * @return true successfully set args { @see @property CPT_Factory::$args }.
     * 
     * @see CPT_Factory::set_post_type_default_args() Args for Post Type
     * @see CPT_Factory::set_taxonomy_default_args() Args for Taxonomy
     * 
     * @since 1.0
     * 
     * @access private
     */
    public function get_args() {
        // Default args from method name according to registering type.
        $type_func  = "__set_{$this->_request['id']}_default_args";
        $args       = method_exists( $this, $type_func ) ? call_user_func( [ $this, $type_func ] ) : [];

        $this->get_labels();

        // Set args labels. Use $labels parameter from API instead of adding inside $args,
        // and this is exactly what we are validating and setting here.
        if ( ! isset( $args['labels'] ) ) {
            $args['labels'] = $this->labels;
        }

        // Set args value accordingly if frontend is disabled.
        // Additional value is set on respective method, if any.
        if( true === $this->redirect_frontend ) {
            $args['public'] = false;
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
     * @return true successfully set labels { @see @property CPT_Factory::$labels }.
     * 
     * @see CPT_Factory::set_post_type_default_labels() Labels for Post Type
     * @see CPT_Factory::set_taxonomy_default_labels() Labels for Taxonomy
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function get_labels() {
        // Default args from method name according to registering type.
        $type_func  = "__set_{$this->_request['id']}_default_labels";
        $defaults   = method_exists( $this, $type_func ) ? call_user_func( [ $this, $type_func ] ) : [];

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
     * 
     * @access public
     */
    private function _set_default_args() {
        // Set "public, publicly_queryable, query_var, has_archive to "false"
        // to disable frontend with 404 code "page not found".
        $defaults = [
            'public'             => false, // disable frontend
            'publicly_queryable' => false, // if true, then "query" and "publicly accessible URL" works.
            'query_var'          => $this->key, // can be string, empty, null, or FALSE. no effect if "publicly_queryable" is false. Won't filter Admin Post List table if set to false.
            'show_ui'            => true, // display and workable at admin

            // set to false for non-public and non-queriable registering types as no need to change slugs
            'rewrite'     => [
                'slug'           => $this->slug, // also used for flushing rewrite rules, if changed
                'with_front'     => false,
            ],
        ];
        return $defaults;
    }

    /**
     * Creates the labels for the registering type
     * 
     * @return array labels to pass to $args['labels'] of the registering type.
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function _set_default_labels() {
        $defaults = [
            'name'                       => _x( $this->plural_name, $this->_request['type'] .' plural name', HZFEX_TEXTDOMAIN ),
            'singular_name'              => _x( $this->singular_name, $this->_request['type'] .' singular name', HZFEX_TEXTDOMAIN ),
            'menu_name'                  => _x( $this->plural_name, $this->_request['type'] .' menu name', HZFEX_TEXTDOMAIN ),
            'add_new_item'               => __( "Add New ".$this->singular_name, HZFEX_TEXTDOMAIN ),
            'edit_item'                  => __( "Edit ".$this->singular_name, HZFEX_TEXTDOMAIN ),
            'all_items'                  => __( "All ".$this->plural_name, HZFEX_TEXTDOMAIN ),
            'view_item'                  => __( "View ".$this->singular_name, HZFEX_TEXTDOMAIN ),
            'update_item'                => __( "Update ".$this->singular_name, HZFEX_TEXTDOMAIN ),
            'search_items'               => __( "Search ".$this->plural_name, HZFEX_TEXTDOMAIN ),
            'not_found'                  => __( "No ".$this->plural_name." found", HZFEX_TEXTDOMAIN ),
            'parent_item_colon'          => __( "Parent ".$this->singular_name.":", HZFEX_TEXTDOMAIN ),
            'items_list_navigation'      => __( $this->singular_name." list navigation", HZFEX_TEXTDOMAIN ),
            'items_list'                 => __( $this->plural_name." list", HZFEX_TEXTDOMAIN ),
        ];
        return $defaults;
    }

    /**
     * Sets additional default args for Post Type.
     * 
     * Additional args will then be merged to default args.
     * {@see @method CPT_Factory::_set_default_args()}
     *
     * @return array
     * 
     * @since 1.0
     * 
     * @access public
     */
    private function __set_post_type_default_args() {
        $defaults = [
            'has_archive'             => false, // bool/string to have archive page with slug.
            'exclude_from_search'     => true, // disable from search on frontend. direct impact on the custom taxonomy archives. i.e. won't show post types in taxonomy archive pages.
            'supports'                => ['title', 'author', 'excerpt']
        ];

        // Merge post defaults and common registering type defaults.
        $defaults = array_merge( $this->_set_default_args(), $defaults );

        // Merge with args from API.
        $args = $this->_finalize_args( $defaults );

        // Set args value accordingly if frontend is disabled.
        if( true === $this->redirect_frontend ) {
            $args['has_archive'] = true;
        }
        return $args;
    }

    /**
     * Sets additional default labels for Post Type.
     * 
     * Additional labels will then be merged to default labels.
     * {@see @method CPT_Factory::_set_default_labels()}
     * 
     * @return array
     * 
     * @since 1.0
     * 
     * @access public
     */
    private function __set_post_type_default_labels() {
        $defaults = [
            'name_admin_bar'             => _x( $this->singular_name, 'post type name admin bar', HZFEX_TEXTDOMAIN ),
            'add_new'                    => __( "Add New", $this->singular_name, HZFEX_TEXTDOMAIN ),
            'new_item'                   => __( 'New '.$this->singular_name, HZFEX_TEXTDOMAIN ),
            'view_items'                 => __( 'View '.$this->plural_name, HZFEX_TEXTDOMAIN ),
            'not_found_in_trash'         => __( 'No '.$this->plural_name.' found in trash', HZFEX_TEXTDOMAIN ),
            'archives'                   => __( $this->singular_name. 'Archives', HZFEX_TEXTDOMAIN ),
            'attributes'                 => __( $this->singular_name. 'Attributes', HZFEX_TEXTDOMAIN ),
            'filter_items_list'          => __( 'Filter '.$this->singular_name.' list', HZFEX_TEXTDOMAIN ),
            'item_published'             => __( $this->singular_name.' published', HZFEX_TEXTDOMAIN ),
            'item_published_privately'   => __( $this->singular_name.' published privately', HZFEX_TEXTDOMAIN ),
            'item_reverted_to_draft'     => __( $this->singular_name.' reverted to draft', HZFEX_TEXTDOMAIN ),
            'item_scheduled'             => __( $this->singular_name.' scheduled', HZFEX_TEXTDOMAIN ),
            'item_updated'               => __( $this->singular_name.' updated', HZFEX_TEXTDOMAIN )
        ];   
        // Merge post defaults and common registering type defaults.
        return array_merge( $this->_set_default_labels(), $defaults );
    }

    /**
     * Sets additional default args for Taxonomy.
     * 
     * Additional args will then be merged to default args.
     * {@see @method CPT_Factory::_set_default_args()}
     *
     * @return array
     * 
     * @since 1.0
     * 
     * @access public
     */
    private function __set_taxonomy_default_args() {
        $defaults = [
            'hierarchical'         => true, // like category (not tags)
            'show_admin_column'    => true, // display column on post type list
        ];

        // Merge taxonomy defaults and common registering type defaults.
        $defaults = array_merge( $this->_set_default_args(), $defaults );

        // Merge with args from API.
        $args = $this->_finalize_args( $defaults );
        return $args;
    }

    /**
     * Sets additional default labels for Taxonomy.
     * 
     * Additional labels will then be merged to default labels.
     * {@see @method CPT_Factory::_set_default_labels()}
     * 
     * @return array
     * 
     * @since 1.0
     * 
     * @access public
     */
    private function __set_taxonomy_default_labels() {
        $defaults = [
            'new_item_name'              => __( "New {$this->singular_name} Name", HZFEX_TEXTDOMAIN ),
            'parent_item'                => __( "Parent {$this->singular_name}", HZFEX_TEXTDOMAIN ),
            'popular_items'              => __( "Popular {$this->plural_name}", HZFEX_TEXTDOMAIN ),
            'separate_items_with_commas' => __( "Separate {$this->plural_name} with commas", HZFEX_TEXTDOMAIN ),
            'add_or_remove_items'        => __( "Add or remove {$this->plural_name}", HZFEX_TEXTDOMAIN ),
            'choose_from_most_used'      => __( "Choose from most used {$this->plural_name}", HZFEX_TEXTDOMAIN )
        ];

        // Merge taxonomy defaults and common registering type defaults.
        return array_merge( $this->_set_default_labels(), $defaults );
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
     * 
     * @access private
     */
    private function _finalize_args( array $defaults ) {
        return array_replace_recursive( $defaults, $this->args );
    }

    /**
     * Redirect frontend pages of the registering type.
     *
     * @return void
     * 
     * @since 1.0
     * 
     * @access public
     */
    public function frontend_redirect() {
        if( $this->_is_valid_page() ) {
            $this->_frontend_redirect();
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
     * 
     * @access public
     */
    private function _is_valid_page() {
        // Get the method by registering type.
        $method = "__is_{$this->_request['id']}_page";

        if( ! method_exists( $this, $method ) ) {
            return false;
        }
        return $this->$method();
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
     * 
     * @access private
     */
    private function _frontend_redirect() {
        global $wp_query;
        if ( ! isset( $wp_query ) ) {
            _doing_it_wrong( __METHOD__ , __( 'Redirection failed because it ran before global var "$wp_query".', HZFEX_TEXTDOMAIN ), '1.0' );
            return false;
        }

        // Registering type redirection args.
        $add_args   = "__redirect_{$this->_request['id']}_args";

        /**
         * WPHOOK: Filter -> set redirection url
         * 
         * @var bool
         * 
         * @since 1.0
         * 
         * @example usage:
         * add_filter( "redirect_hzfex_{$post_type_name}_to", "http://mycustom.url" );
         */
        $to = apply_filters( "redirect_hzfex_{$this->key}_to", admin_url() );

        /**
         * WPHOOK: Filter -> Add/remove query args on destination (`$to`) URL.
         * 
         * @var bool
         * 
         * @since 1.0
         * 
         * @example usage:
         * add_filter( "hzfex_has_{$post_type_key}_redirect_query_args", "__return_false" );
         */
        $has_arg        = apply_filters( "hzfex_has_{$this->key}_redirect_query_args", true );
        $this->_redirect_query  = [
            'http_referrer'        => rawurlencode_deep( $_SERVER['REQUEST_URI'] ),
            'public'               => 0, // is false
            'redirect'             => 1, // is true
        ];
        $url            = esc_url_raw( $to );

        if( $has_arg ) {
            // Set additional query parameters.
            if( method_exists( $this, $add_args ) ) {
                $this->$add_args();
            }

            // Set redirection URL with query args.
            $url = htmlspecialchars_decode( add_query_arg( $this->_redirect_query, $url ) );
        }

        // Redirect page with 301 moved permanently HTTP response code for SEO compatibility.
        wp_redirect( $url, $this->_redirect_code ); exit;
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
     * 
     * @access public
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
     * 
     * @access public
     */
    private function __is_taxonomy_page() {
        $terms = $this->_redirect_terms;

        // Set terms as empty string if not set so all terms get redirected.
        if( sizeof( $terms ) === 0 || empty( $terms[0] ) ) {
            $terms = ''; 
        }
        return is_tax( $this->key, $terms );
    }

    /**
     * Sets post type page redirection args.
     * 
     * @return void
     * 
     * @since 1.0
     * 
     * @access public
     */
    private function __redirect_post_type_args() {
        $this->_redirect_query['post_type'] = $this->key;
        if( is_singular( $this->key ) ) {
            $this->_redirect_query['referrer_type'] = 'singular'; // is singular page.
        }
        if( is_post_type_archive( $this->key ) ) {
            $this->_redirect_query['referrer_type'] = 'archive'; // is post type archive page.
        }
    }

    /**
     * Sets taxonomy page redirection args.
     * 
     * @return void
     * 
     * @since 1.0
     * 
     * @access public
     */
    private function __redirect_taxonomy_args() {
        $this->_redirect_query['taxonomy'] = $this->key;

        $term = get_queried_object()->slug;

        if( in_array( $term, $this->_redirect_terms ) ) {
            $this->_redirect_query['term'] = $term; // is taxonomy term defined for redirection.
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
     * To overcome this, `CPT_Factory` has a handy property.
     * { @see @property CPT_Factory::$flush_rewrite_rule }.
     * The property value will be set to true once the slug
     * gets changed and triggers rewrite rules.
     * { @see @method CPT_Factory::_rewrite_handle()}.
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
     * @return WP_Error|void
     * 
     * @since 1.0
     * 
     * @access public
     */
    private function _rewrite_handle() {
        // The registering type.
        $type = $this->_request['type'];

        // Flush the rewrite rules.
        flush_rewrite_rules( true );

        // Handles slug changed behaviour on backend.
        if( is_admin() ) {
            // Displays admin notice.
            $this->_admin_rewrite_handle();

            // Resets `flush_rewrite_rule` property and unset `slug_changed` option key.
            $this->_additional_error_handling( 'slug_change' );
        }

        // Handles slug changed behaviour on frontend.
        else {
            if( is_wp_error( $this->_frontend_rewrite_handle() ) ) {
                // Displays die message and resets `flush_rewrite_rule` property.
                $this->_error_message( $this->key ." $type Slug changed", "slug_change", "_frontend_rewrite_handle" );
            }
        }
    }

    /**
     * Displays admin notice when slug gets changed.
     * 
     * Notice can only be displayed if this plugin
     * is included within core plugin or core plugin
     * is installed and activated separately.
     *
     * @return string
     * 
     * @link TODO: add core plugin link here.
     * 
     * @since 1.0
     * 
     * @access public
     */
    private function _admin_rewrite_handle() {
        // Get message to display on admin notice.
        $msg = $this->_rewrite_handle_message();
        $msg .= ' ' .$this->_redirection_plugin_link();

        // Check if Core plugin exists.
        if( function_exists( 'tws_core' ) ) {
            require_once HZFEX_CORE_ADMIN_PATH . '/notices.php';
            Notice::instance();
            Notice::create( "{$this->key}_slug_change", $msg, true, 'success' );
        }
    }

    /**
     * Frontend rewrite handle.
     *
     * @return WP_Error|true Shows die message on frontend if slug changed and debug is on.
     * 
     * @since 1.0
     * 
     * @access public
     */
    private function _frontend_rewrite_handle() {
        $error  = new WP_Error(); // Create new error object.
        $msg    = $this->_rewrite_handle_message(); // Get common message.

        /* Translators -
        %1$s: message text
        %2$s: frontend limitation text
        %3$s: dashboard link
        %4$s: link text
        %5$s: keep showing text
        %6$s: response code text
        %7$s: URL change die text
        */
        $message = sprintf(
            '<p style="text-decoration:line-through;opacity:0.3">%1$s</p><hr><div style="color:#b71c1c"><h3>%2$s</h3><p style="text-align:center;padding:10px 20px; border:2px solid #b71c1c;border-radius:5px;"><b><a href="%3$s">%4$s</a></b></p><b><em>%5$s</em></b></div><hr><h4>%6$s</h4><p><b>%7$s</b></p>',
            $msg,
            __( "Frontend may not flush rewrite rules properly after slug changed. To save new slug, visit admin dashboard.", HZFEX_TEXTDOMAIN ),
            esc_url( admin_url() ),
            __( 'Visit Admin Dashboard', HZFEX_TEXTDOMAIN ),
            __( "This error may get displayed again until dashboard is visited.", HZFEX_TEXTDOMAIN ),
            __( 'HTTP Response Code: 500', HZFEX_TEXTDOMAIN ),
            __( 'This page has died with HTTP Response code of 500 due to changes made in URL (specifically the slug).', HZFEX_TEXTDOMAIN )
        );

        $error->add( 'slug_change', $message );

        // Only die if debug is on and user logged in as admin.
        $show = self::$_debug && is_user_logged_in() && current_user_can( 'manage_options' );

        // Otherwise return true (no dying happens).
        return $show ? $error : true;
    }

    /**
     * HTML message to display when handling flushing of rewrite rule.
     *
     * @return string
     * 
     * @since 1.0
     * 
     * @access public
     */
    private function _rewrite_handle_message() {
        /* Translators -
        %1$s: registering type
        %2$s: slug change text
        %3$s: rewrite rule api function
        %4$s: function triggered text
        */
        $text = sprintf( '%1$s %2$s <b><code><em>%3$s</em></code></b> %4$s',
            $this->_request['type'],
            __( 'slug has been changed.', HZFEX_TEXTDOMAIN ),
            "flush_rewrite_rules()",
            __( 'function has been triggered to restructure the permalink. Although not required, but as a double measure, permalink rules can be flushed by clicking "Save Changes" from', HZFEX_TEXTDOMAIN )
        );
        
        /* Translators -
        %1$s: Registering type key/name
        %2$s: notice text
        %3$s: admin permalink page link
        %4$s: link text
        */
        $msg = sprintf(
            '<b>%1$s</b> %2$s <a href="%3$s"><b><em>%4$s</em></b></a>.',
            $this->key,
            $text,
            admin_url( 'options-permalink.php' ),
            __( 'Settings->Permalinks', HZFEX_TEXTDOMAIN )
        );

        return $msg;
    }

    /**
     * Redirection WordPress plugin page link.
     * 
     * @return string HTML text with redirection page link.
     * 
     * @since 1.0
     * 
     * @access private
     */
    private function _redirection_plugin_link() {
        /* Translators -
        %1$s: furthermore text
        %2$s: plugin page URL
        %3$s: link text
        */
        $msg = sprintf('<span>%1$s <a href="%2$s" target="_blank"><em>%3$s</em></a>.</span>',
            __( 'Furthermore, 404 error on old slug URL can be handled using', HZFEX_TEXTDOMAIN ),
            esc_url( "https://wordpress.org/plugins/redirection/" ),
            __( 'Redirection Plugin', HZFEX_TEXTDOMAIN )
        );

        return $msg;
    }

    /**
     * WordPress dies error message.
     *
     * @param string $title Title that appears on browser tab and as heading.
     * @param string $code WP_Error Code.
     * @param string $method CPT_Factory method name which triggers WP_Error.
     * @param int $response_code HTTP response code.
     * 
     * @return void Die with unordered list of WP_Error message.
     * 
     * @since 1.0
     * 
     * @access public
     */
    private function _error_message( string $title, string $code, string $method, $response_code = 500 ) {

        // Die early if undefined method passed.
        if( ! method_exists( $this, $method ) ) {
            /* Translators -
            %1$s: Factory classname
            %2$s: Factory method name
            %3$s: message
            */
            $msg = sprintf( '<><code><b>%1$s</b>::<em>%2$s</em></code> %3$s</span>',
                get_class(),
                $method,
                __( "method doesn't exist for showing error message.", HZFEX_TEXTDOMAIN )
            );
            return wp_die( new WP_Error( 'factory_failed', $msg ) );
        }

        $title      = ucwords( $title );
        $message    = $this->$method()->get_error_messages($code);

        return wp_die(
            '<h1 style="text-align:center;color:#b71c1c;">'.$title.'</h1>
            <ul style="list-style:none;">
                <li>' . implode( '</li><li>', $message ) . '</li>
            </ul>',
            $title,
            $response_code
        );
    }

    /**
     * Additional handling of errors.
     *
     * @param string $code can be a WP_Error code or any other code for execution.
     * 
     * @return void
     * 
     * @since 1.0
     * 
     * @access public
     */
    private function _additional_error_handling( string $code ) {
        if( $code === "slug_change" ) {
            // Set property value to false.
            $this->flush_rewrite_rule = false;
            
            $key    = "tws_registered_{$this->_request['id']}";
            $option = get_option( $key );

            // Unset `rewrite_rule` key from saved option
            // so that execution can happen only once.
            unset( $option[$this->key]['slug_changed'] );
            update_option( $key, $option );
        }
    }

    /**
     * Sets post type and it's filtration keys to global vars.
     * 
     * @param string $post_type **required** Post type key where filtering dropdown to add.
     * @param string $keys **required** Taxonomy/Post Meta keys to use for adding filteration dropdown.
     * 
     * @return void
     * 
     * @global array $tws_post_type_filters
     * 
     * @since 1.0
     * 
     * @static
     * 
     * @access public
     */
    public static function set_post_type_filters( string $post_type, $keys = '' ) {
        global $tws_post_type_filters;

        // Convert keys to array if string given.
        $keys = ! is_array( $keys ) ? [$keys] : (array) $keys;

        if( ! isset( $tws_post_type_filters[$post_type] ) ) {
            $tws_post_type_filters[$post_type] = $keys;
            
        } else {
            $set_keys = $tws_post_type_filters[$post_type];
            $set_keys = ! is_array( $set_keys ) ? [$set_keys] : (array) $set_keys;
            $tws_post_type_filters[$post_type] = array_merge( $set_keys, $keys );
        }

        // Set array values as keys and save to global vars.
        $tws_post_type_filters[$post_type] = array_combine( $tws_post_type_filters[$post_type], $tws_post_type_filters[$post_type] );
    }

    /**
     * Set columns for post type.
     *
     * @param string $post_type **required** Post type key.
     * @param array $args **required** Column data.
     * 
     * @return void
     * 
     * @global array $tws_post_type_columns
     * 
     * @since 1.0
     * 
     * @example usage:
     * #### For setting args.
     * ```
     * $args = [
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
     * ];
     * ```
     * 
     * @static
     * 
     * @access public
     */
    public static function set_post_type_columns( string $post_type, array $args ) {
        global $tws_post_type_columns;

        if( ! isset( $tws_post_type_columns[$post_type] ) ) {
            $tws_post_type_columns[$post_type] = $args;
        } else {
            $set_args = $tws_post_type_columns[$post_type];
            $tws_post_type_columns[$post_type] = array_merge_recursive( $set_args, $args );
        }
    }

    /**
     * Set columns for taxonomy.
     *
     * @param string $taxonomy **required** Taxonomy key.
     * @param array $args **required** Column data.
     * 
     * @return void
     * 
     * @global array $tws_taxonomy_columns
     * 
     * @since 1.0
     * 
     * @example usage:
     * #### For setting args.
     * ```
     * $args = [
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
     *          // Passed parameters: `$content`, `$column` and `$term_id`.
     *          'callback'   => 'function_name_that_display_content',
     *          'priority'   => 3, // "0" will be first, "1" will be second, and so on. If want to order all columns properly, all columns must be set with this key/value.
     *      ],
     * ];
     * ```
     * 
     * @static
     * 
     * @access public
     */
    public static function set_taxonomy_columns( string $taxonomy, array $args ) {
        global $tws_taxonomy_columns;

        if( ! isset( $tws_taxonomy_columns[$taxonomy] ) ) {
            $tws_taxonomy_columns[$taxonomy] = $args;
        } else {
            $set_args = $tws_taxonomy_columns[$taxonomy];
            $tws_taxonomy_columns[$taxonomy] = array_merge_recursive( $set_args, $args );
        }
    }
}