<?php
/**
 * Custom Post Type and Taxonomy Registration API.
 * 
 * API functions easier for namespacing.
 * 
 * @package TheWebSolver\Core\CPT_Framework\API
 */

namespace TheWebSolver\Register;

use TheWebSolver\Core\Cpt\Post_Type;
use TheWebSolver\Core\Cpt\Taxonomy;
use TheWebSolver\Core\Helper\CPT_Factory;

/**
 * Registers new post type.
 * 
 * Alias for:
 *  ```
 * (new  TheWebSolver\Core\Cpt\Post_Type($names))
 *      ->set_labels($labels)
 *      ->set_args($args)
 *      ->assign_objects($taxonomies)
 *      ->set_redirect($redirect_frontend)
 *      ->set_filter($filter_keys)
 *      ->manage_columns($manage_columns)
 *      ->start_registration();
 * ```
 * 
 * TODO: Integrate migrate codes: {@link https://plugins.trac.wordpress.org/browser/post-type-switcher/trunk/post-type-switcher.php}
 *
 * @param array $names **required** An array of names.
 * * @type string `key` - Post Type Name/Key
 * #### - Highly recommended to use `-` (dash) or `_` (underscore) between words and not ` ` (space).
 * #### - _POST TYPE NAME/KEY SHOULD NOT BE CHANGED ONCE SET_. If changed, another CPT will get registered. Set {@see @param `$migrate`} value to `true` if you changed post type name/key and want to transfer all posts created from this post type to newly changed post type name/key.
 * * @type string `singular_name` - default value for `$args['labels']['singular_name']`
 * * @type string `name` - default value for `$args['labels']['name']`
 * * @type string `slug` - default value for `$args['rewrite']['slug']`
 * 
 * @param array $labels {@see @method `CPT_Factory::get_labels()`}.\
 * {@link https://developer.wordpress.org/reference/functions/get_post_type_labels/}:
 * 
 * @param array $args {@see @method `CPT_Factory::get_args()`}.\
 * {@link https://developer.wordpress.org/reference/functions/register_post_type/}.
 * #### - `show_ui`, `show_in_menu` needs to be set to `true` for admin use. Defaults to `public`.
 * #### - `show_in_rest => true`, `supports => ['editor']` to be set for using "Gutenberg Blocks".
 * 
 * @param string|string[] $taxonomies Taxonomy key in string or array of keys to assign to post type.
 * #### Keep in mind, when supplying taxonomy, it should already be registered.
 * 
 * @param array $redirect_frontend   Whether to redirect posts url in frontend.
 *                                   Redirection happens if set to `true`.\
 *                                   `$args` value will also be overridden as below:
 *                                   > `public              => false`\
 *                                   > `publicly_queryable  => true`\
 *                                   > `has_archive         => true`\
 *                                   > `query_var           => false`
 * * @type `bool` `$enabled` Frontend redirection enabled or not. Defaults to `false`.
 * * @type `int` `response_code` HTTP Status Response Code for redirection. Defaults to `302 (moved temporarily)`.
 * 
 * @param string|string[] $filter_keys Key in string or array of keys for filtering admin table. {@see @method `CPT_Filter_List::with_keys()`}
 * 
 * @param array $manage_columns An array of column data to add/remove/sort admin table. {@see @method `CPT_Column::apply()`}
 * 
 * @return WP_Post_Type|WP_Error|false
 * 
 * @link https://developer.wordpress.org/reference/functions/register_post_type/
 * 
 * @since 1.0
 */
function post_type(
    array $names,
    $labels = [],
    $args = [],
    $taxonomies = '',
    $redirect_frontend = ['enabled' => false, 'response_code' => 302],
    $filter_keys = '',
    $manage_columns = [],
    $migrate = false
) {

    // TWS Post Type API.
    $post_type = new Post_Type($names);
    $post_type
        ->set_labels($labels)
        ->set_args($args)
        ->assign_objects($taxonomies)
        ->set_redirect($redirect_frontend)
        ->set_filter($filter_keys)
        ->manage_columns($manage_columns)
        ->start_registration();
}

/**
 * Registers new taxonomy.
 * 
 * Alias for:
 * ```
 * (new TheWebSolver\Core\Cpt\Taxonomy($names))
 *     ->set_labels($labels)
 *     ->set_args($args)
 *     ->assign_objects($post_types)
 *     ->set_redirect($redirect_frontend)
 *     ->set_filter($as_filter)
 *     ->manage_columns($manage_columns)
 *     ->start_registration();
 * ```
 * 
 * @param array $names **required** An array of names.
 * * @type string `key` - Taxonomy Name/Key
 * #### - Highly recommended to use `-` (dash) or `_` (underscore) between words and not ` ` (space).
 * #### - _TAXONOMY NAME/KEY SHOULD NOT BE CHANGED ONCE SET_.
 * * @type string `singular_name` - default value for `$args['labels']['singular_name']`
 * * @type string `name` - default value for `$args['labels']['name']`
 * * @type string `slug` - default value for `$args['rewrite']['slug']`
 * 
 * @param array $labels {@see @method `CPT_Factory::get_labels()`}.\
 * {@link https://developer.wordpress.org/reference/functions/get_taxonomy_labels/}:
 * 
 * @param array $args {@see @method `CPT_Factory::get_args()`}.\
 * {@link https://developer.wordpress.org/reference/functions/register_taxonomy/}.
 * 
 * @param string|string[] $post_types Post type key in string or array of keys to assign this taxonomy to.
 * 
 * @param array $redirect_frontend   Whether to redirect taxonomy archives in frontend.
 *                                   Redirection happens if set to `true`.\
 *                                   `$args` value will also be overridden as below:
 *                                   > `public              => false`\
 *                                   > `publicly_queryable  => true`\
 *                                   > `query_var           => false`
 * * @type `bool` `$enabled` Frontend redirection enabled or not. Defaults to `false`.
 * * @type `string/string[]` `$terms` Taxonomy terms where redirect to happen. Defaults to empty `string` (redirects on all terms page).
 * * @type `int` `response_code` HTTP Status Response Code for redirection. Defaults to `302 (moved temporarily)`.
 * 
 * @param bool $as_filter Whether to use this taxonomy as filter for admin table.\
 * {@see @method `CPT_Factory::get_args()`}.
 * 
 * @param array $manage_columns An array of column data to add/remove/sort admin table. {@see @method CPT_Column::apply()}
 * 
 * @return WP_Taxonomy|WP_Error|false
 * 
 * @since 1.0
 */
function taxonomy(
    array $names,
    $labels = [],
    $args = [],
    $post_types = '',
    $redirect_frontend = ['enabled' => false, 'terms' => '', 'response_code' => 302],
    $as_filter = true,
    $manage_columns = []
    ) {

    // TWS Taxonomy API.
    $taxonomy = new Taxonomy($names);
    $taxonomy
        ->set_labels($labels)
        ->set_args($args)
        ->assign_objects($post_types)
        ->set_redirect($redirect_frontend)
        ->set_filter($as_filter)
        ->manage_columns($manage_columns)
        ->start_registration();
}

/**
 * Sets post type and it's filtration keys to global vars.
 * 
 * Alias for:
 * ```
 * CPT_Factory::set_post_type_filters( string $post_type, $keys = '');
 * ```
 * 
 * @param string $post_type **required** The current post type admin page.
 * @param string|string[] $keys **required** The filtration taxonomy/post_meta keys.
 * 
 * @return void
 * 
 * @global array $tws_post_type_filters
 * 
 * @since 1.0
 */
function filter_post_list_by( string $post_type, $keys ) {
    // TWS Post Type Filter API.
    CPT_Factory::set_post_type_filters( $post_type, $keys );
}

/**
 * Manage columns for post_type/taxonomy admin table.
 * 
 * Alias for:
 * ```
 * // If `$is_post` is `true`.
 * CPT_Factory::set_post_type_columns( $key, $args );
 * // If `$is_post` is `false`.
 * CPT_Factory::set_taxonomy_columns( $key, $args );
 * ```
 *
 * @param string $key **required** Post type key|Taxonomy key.
 * @param array $args **required** Column data in an array {@see @example _below_}.
 * @param bool $is_post _optional_ True for post type, false for taxonomy. Defaults to `true`.
 * 
 * @return void
 * 
 * @since 1.0
 * 
 * @example usage:
 * #### For setting args:
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
 *          // Passed parameters ($is_post = true): $column` and `$post_id`.
 *          // Passed parameters ($is_post = false): `$content`, `$column` and `$term_id`.
 *          'callback'   => 'function_name_that_display_content',
 *          'priority'   => 3, // "0" will be first, "1" will be second, and so on. If want to order all columns properly, all columns must be set with this key/value.
 *      ],
 * ];
 * ```
 */
function manage_column_by( string $key, array $args, bool $is_post = true ) {
    if( $is_post ) {
        CPT_Factory::set_post_type_columns( $key, $args );
    } else {
        CPT_Factory::set_taxonomy_columns( $key, $args );
    }
}