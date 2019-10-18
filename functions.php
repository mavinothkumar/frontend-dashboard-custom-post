<?php

/**
 * Append the Version -- Custom Post
 */
add_filter(/**
 * @param $version
 *
 * @return array
 */
    'fed_plugin_versions', function ($version) {
    return array_merge($version, array('custom_post' => 'Custom Post ('.FED_CP_PLUGIN_VERSION.')'));
});


/**
 * @param  array  $request
 *
 * @return array
 */
function fed_cp_get_custom_post_types(array $request = array())
{

    $labels = array(
        'Basic Settings' => array(
            'slug'          => array(
                'name'         => __('Post Type Slug', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) movies or mov_ies', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['slug']) ? $request['slug'] : '',
                    'input_meta'  => 'slug',
                    'class_name'  => 'fed_convert_space_to_underscore',
                    'is_required' => 'true',
                ),
                'required'     => true,
                'help_message' => fed_show_help_message(array(
                    'content' => 'Post type slug',
                )),
            ),
            'label'         => array(
                'name'         => __('Plural Name', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Movies', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['label']) ? $request['label'] : '',
                    'input_meta'  => 'label',
                    'is_required' => 'true',
                ),
                'required'     => true,
                'help_message' => fed_show_help_message(array('content' => ' A plural descriptive name for the post type marked for translation.')),
            ),
            'singular_name' => array(
                'name'         => __('Singular Name', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'required'    => 'true',
                    'placeholder' => __('(eg) Movie', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['singular_name']) ? $request['singular_name'] : '',
                    'input_meta'  => 'singular_name',
                ),
                'required'     => true,
                'help_message' => fed_show_help_message(array('content' => 'General name for the post type, usually singular.')),
            ),
            'menu_icon'     => array(
                'name'         => __('Menu Icon', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('Click to get Menu Icons', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['menu_icon']) ? $request['menu_icon'] : '',
                    'input_meta'  => 'menu_icon',
                    'class_name'  => 'fed_cp_menu_icon menu_icon',
                    'extra'       => 'data-fed_menu_box_id="menu_icon" data-toggle="modal" data-target=".fed_show_fa_list"',
                ),
                'help_message' => fed_show_help_message(array('content' => 'Custom post type menu icon')),
            ),
        ),
        'Label Settings' => array(
            'name'                  => array(
                'name'         => __('Name', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Horror Movies', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['name']) ? $request['name'] : '',
                    'input_meta'  => 'name',
                ),
                'help_message' => fed_show_help_message(array('content' => 'General name for the post type, usually plural.')),
            ),
            'menu_name'             => array(
                'name'         => __('Menu Name', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Horror Movies', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['menu_name']) ? $request['menu_name'] : '',
                    'input_meta'  => 'menu_name',
                ),
                'help_message' => fed_show_help_message(array('content' => 'Default is the same as `name`')),
            ),
            'name_admin_bar'        => array(
                'name'         => __('Name Admin Bar', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Horror Movies', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['name_admin_bar']) ? $request['name_admin_bar'] : '',
                    'input_meta'  => 'name_admin_bar',
                ),
                'help_message' => fed_show_help_message(array('content' => 'String for use in New in Admin menu bar. Default is the same as `singular_name`.')),
            ),
            'archives'              => array(
                'name'         => __('(eg) Movies Archives', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('Item Archives', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['archives']) ? $request['archives'] : '',
                    'input_meta'  => 'archives',
                ),
                'help_message' => fed_show_help_message(array('content' => 'String for use with archives in nav menus. Default is Post Archives/Page Archives.')),
            ),
            'attributes'            => array(
                'name'         => __('Attributes', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Movies Attributes', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['attributes']) ? $request['attributes'] : '',
                    'input_meta'  => 'attributes',
                ),
                'help_message' => fed_show_help_message(array('content' => 'Label for the attributes meta box. Default is Post Attributes  /  Page Attributes ')),
            ),
            'parent_item_colon'     => array(
                'name'         => __('Parent Item Colon', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Movies:', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['parent_item_colon']) ? $request['parent_item_colon'] : '',
                    'input_meta'  => 'parent_item_colon',
                ),
                'help_message' => fed_show_help_message(array('content' => 'This string isn\'t used on non-hierarchical types. In hierarchical ones the default is Parent Page:')),
            ),
            'all_items'             => array(
                'name'         => __('All Items', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) All Movies', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['all_items']) ? $request['all_items'] : '',
                    'input_meta'  => 'all_items',
                ),
                'help_message' => fed_show_help_message(array('content' => 'String for the submenu. Default is All Posts/All Pages.')),
            ),
            'add_new_item'          => array(
                'name'         => __('Add New Item', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Add New Movie', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['add_new_item']) ? $request['add_new_item'] : '',
                    'input_meta'  => 'add_new_item',
                ),
                'help_message' => fed_show_help_message(array('content' => __('Default is Add New Post/Add New Page.', 'frontend-dashboard-custom-post'))),
            ),
            'add_new'               => array(
                'name'         => __('Add New', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Add New', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['add_new']) ? $request['add_new'] : '',
                    'input_meta'  => 'add_new',
                ),
                'help_message' => fed_show_help_message(array('content' => 'Default is Edit Post/Edit Page.')),
            ),
            'new_item'              => array(
                'name'         => __('New Item', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) New Movie', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['new_item']) ? $request['new_item'] : '',
                    'input_meta'  => 'new_item',
                ),
                'help_message' => fed_show_help_message(array('content' => 'Default is New Post/New Page.')),
            ),
            'edit_item'             => array(
                'name'         => __('Edit Item', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Edit Movie', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['edit_item']) ? $request['edit_item'] : '',
                    'input_meta'  => 'edit_item',
                ),
                'help_message' => fed_show_help_message(array('content' => 'Default is Edit Post/Edit Page.')),
            ),
            'view_item'             => array(
                'name'         => __('View Item', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) View Movie', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['view_item']) ? $request['view_item'] : '',
                    'input_meta'  => 'view_item',
                ),
                'help_message' => fed_show_help_message(array('content' => 'Default is View Post/View Page')),
            ),
            'view_items'            => array(
                'name'         => __('View Items', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) View Movies', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['view_items']) ? $request['view_items'] : '',
                    'input_meta'  => 'view_items',
                ),
                'help_message' => fed_show_help_message(array('content' => ' Label for viewing post type archives. Default is View Posts  /  View Pages .')),
            ),
            'search_items'          => array(
                'name'         => __('Search Items', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Search Movie', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['search_items']) ? $request['search_items'] : '',
                    'input_meta'  => 'search_items',
                ),
                'help_message' => fed_show_help_message(array('content' => 'Default is Search Posts/Search Pages')),
            ),
            'not_found'             => array(
                'name'         => __('Not Found', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Movie Not found', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['not_found']) ? $request['not_found'] : '',
                    'input_meta'  => 'not_found',
                ),
                'help_message' => fed_show_help_message(array('content' => 'Default is No posts found/No pages found.')),
            ),
            'not_found_in_trash'    => array(
                'name'         => __('Not Found in Trash', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Movie Not found in Trash', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['not_found_in_trash']) ? $request['not_found_in_trash'] : '',
                    'input_meta'  => 'not_found_in_trash',
                ),
                'help_message' => fed_show_help_message(array('content' => 'Default is No posts found in Trash/No pages found in Trash.')),
            ),
            'featured_image'        => array(
                'name'         => __('Featured Image', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Featured Image for this Movie', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['featured_image']) ? $request['featured_image'] : '',
                    'input_meta'  => 'featured_image',
                ),
                'help_message' => fed_show_help_message(array('content' => 'Default is Featured Image.')),
            ),
            'set_featured_image'    => array(
                'name'         => __('Set Featured Image', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Set featured image for this Movie', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['set_featured_image']) ? $request['set_featured_image'] : '',
                    'input_meta'  => 'set_featured_image',
                ),
                'help_message' => fed_show_help_message(array('content' => 'Default is Set featured image.')),
            ),
            'remove_featured_image' => array(
                'name'         => __('Remove Featured Image', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Remove featured image for this Movie',
                        'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['remove_featured_image']) ? $request['remove_featured_image'] : '',
                    'input_meta'  => 'remove_featured_image',
                ),
                'help_message' => fed_show_help_message(array('content' => 'Default is Remove featured image.')),
            ),
            'use_featured_image'    => array(
                'name'         => __('Use Featured Image', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Use as featured image for this Movie',
                        'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['use_featured_image']) ? $request['use_featured_image'] : '',
                    'input_meta'  => 'use_featured_image',
                ),
                'help_message' => fed_show_help_message(array('content' => 'Default is Use as featured image.')),
            ),
            'insert_into_item'      => array(
                'name'         => __('Insert into Item', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Insert into Movie', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['insert_into_item']) ? $request['insert_into_item'] : '',
                    'input_meta'  => 'insert_into_item',
                ),
                'help_message' => fed_show_help_message(array('content' => 'String for the media frame button. Default is Insert into post/Insert into page.')),
            ),
            'uploaded_to_this_item' => array(
                'name'         => __('Uploaded to this Movie', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Uploaded to this item', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['uploaded_to_this_item']) ? $request['uploaded_to_this_item'] : '',
                    'input_meta'  => 'uploaded_to_this_item',
                ),
                'help_message' => fed_show_help_message(array('content' => 'String for the media frame filter. Default is Uploaded to this post/Uploaded to this page.')),
            ),
            'items_list'            => array(
                'name'         => __('Items List', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Movies list', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['items_list']) ? $request['items_list'] : '',
                    'input_meta'  => 'items_list',
                ),
                'help_message' => fed_show_help_message(array('content' => 'String for the table hidden heading.')),
            ),
            'items_list_navigation' => array(
                'name'         => __('Items List Navigation', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Movies list navigation', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['items_list_navigation']) ? $request['items_list_navigation'] : '',
                    'input_meta'  => 'items_list_navigation',
                ),
                'help_message' => fed_show_help_message(array('content' => 'String for the table pagination hidden heading.')),
            ),
            'filter_items_list'     => array(
                'name'         => __('Filter items list', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Filter Movies list', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['filter_items_list']) ? $request['filter_items_list'] : '',
                    'input_meta'  => 'filter_items_list',
                ),
                'help_message' => fed_show_help_message(array('content' => 'String for the table views hidden heading.')),
            ),
            'description'           => array(
                'name'         => __('Description', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Description', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['description']) ? $request['description'] : '',
                    'input_meta'  => 'description',
                ),
                'help_message' => fed_show_help_message(array('content' => 'A short descriptive summary of what the post type is.')),
            ),
            'menu_position'         => array(
                'name'         => __('Menu Position', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg)20 or 30 or any integer value', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['menu_position']) ? $request['menu_position'] : '',
                    'input_meta'  => 'menu_position',
                ),
                'help_message' => fed_show_help_message(array('content' => 'The position in the menu order the post type should appear')),
            ),
            'capability_type'       => array(
                'name'         => __('Capability Type', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg)Default is post', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['capability_type']) ? $request['capability_type'] : 'post',
                    'input_meta'  => 'capability_type',
                ),
                'help_message' => fed_show_help_message(array('content' => 'The string to use to build the read, edit, and delete capabilities')),
            ),
        ),
        'Settings'       => array(
            'hierarchical'        => array(
                'name'         => __('Hierarchical', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['hierarchical']) ? $request['hierarchical'] : '',
                    'input_meta'  => 'hierarchical',
                    'input_value' => array('false' => 'False', 'true' => 'True'),
                ),
                'help_message' => fed_show_help_message(array('content' => 'Whether the post type is hierarchical (e.g. page). Allows Parent to be specified. The supports parameter should contain page-attributes to show the parent select box on the editor page..')),
            ),
            'public'              => array(
                'name'         => __('Public', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['public']) ? $request['public'] : '',
                    'input_meta'  => 'public',
                    'input_value' => array('true' => 'True', 'false' => 'False'),
                ),
                'help_message' => fed_show_help_message(array('content' => 'Controls how the type is visible to authors (show_in_nav_menus, show_ui) and readers (exclude_from_search, publicly_queryable).')),
            ),
            'show_ui'             => array(
                'name'         => __('Show UI', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['show_ui']) ? $request['show_ui'] : '',
                    'input_meta'  => 'show_ui',
                    'input_value' => array('true' => 'True', 'false' => 'False'),
                ),
                'help_message' => fed_show_help_message(array('content' => 'Whether to generate a default UI for managing this post type in the admin.')),
            ),
            'show_in_menu'        => array(
                'name'         => __('Show in Menu', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['show_in_menu']) ? $request['show_in_menu'] : '',
                    'input_meta'  => 'show_in_menu',
                    'input_value' => array('true' => 'True', 'false' => 'False'),
                ),
                'help_message' => fed_show_help_message(array('content' => 'Where to show the post type in the admin menu')),
            ),
            'show_in_admin_bar'   => array(
                'name'         => __('Show in Admin Bar', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['show_in_admin_bar']) ? $request['show_in_admin_bar'] : '',
                    'input_meta'  => 'show_in_admin_bar',
                    'input_value' => array('true' => 'True', 'false' => 'False'),
                ),
                'help_message' => fed_show_help_message(array('content' => 'Whether to make this post type available in the WordPress admin bar')),
            ),
            'show_in_nav_menus'   => array(
                'name'         => __('Show in Nav Menus', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['show_in_nav_menus']) ? $request['show_in_nav_menus'] : '',
                    'input_meta'  => 'show_in_nav_menus',
                    'input_value' => array('true' => 'True', 'false' => 'False'),
                ),
                'help_message' => fed_show_help_message(array('content' => 'Whether post_type is available for selection in navigation menus')),
            ),
            'can_export'          => array(
                'name'         => __('Can Export', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['can_export']) ? $request['can_export'] : '',
                    'input_meta'  => 'can_export',
                    'input_value' => array('true' => 'True', 'false' => 'False'),
                ),
                'help_message' => fed_show_help_message(array('content' => 'Can this post_type be exported.')),
            ),
            'has_archive'         => array(
                'name'         => __('Has Archive', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['has_archive']) ? $request['has_archive'] : '',
                    'input_meta'  => 'has_archive',
                    'input_value' => array('true' => 'True', 'false' => 'False'),
                ),
                'help_message' => fed_show_help_message(array('content' => 'Enables post type archives. Will use $post_type as archive slug by default.')),
            ),
            'exclude_from_search' => array(
                'name'         => __('Exclude from Search', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['exclude_from_search']) ? $request['exclude_from_search'] : '',
                    'input_meta'  => 'exclude_from_search',
                    'input_value' => array('false' => 'False', 'true' => 'True'),
                ),
                'help_message' => fed_show_help_message(array('content' => 'Enables post type archives. Will use $post_type as archive slug by default.')),
            ),
            'publicly_queryable'  => array(
                'name'         => __('Publicly Queryable', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['publicly_queryable']) ? $request['publicly_queryable'] : '',
                    'input_meta'  => 'publicly_queryable',
                    'input_value' => array('true' => 'True', 'false' => 'False'),
                ),
                'help_message' => fed_show_help_message(array('content' => 'Whether queries can be performed on the front end as part of parse_request()')),
            ),
            'query_var'           => array(
                'name'         => __('Query Var', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['query_var']) ? $request['query_var'] : '',
                    'input_meta'  => 'query_var',
                    'input_value' => array('true' => 'True', 'false' => 'False'),
                ),
                'help_message' => fed_show_help_message(array('content' => 'Sets the query_var key for this post type.')),
            ),
            'delete_with_user'    => array(
                'name'         => __('Delete with user', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['delete_with_user']) ? $request['delete_with_user'] : '',
                    'input_meta'  => 'delete_with_user',
                    'input_value' => array('false' => 'False', 'true' => 'True'),
                ),
                'help_message' => fed_show_help_message(array('content' => 'Whether to delete posts of this type when deleting a user. If true, posts of this type belonging to the user will be moved to trash when then user is deleted. If false, posts of this type belonging to the user will not be trashed or deleted')),
            ),
            'show_in_rest'        => array(
                'name'         => __('Show in REST', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['show_in_rest']) ? $request['show_in_rest'] : '',
                    'input_meta'  => 'show_in_rest',
                    'input_value' => array('true' => 'True', 'false' => 'False'),
                ),
                'help_message' => fed_show_help_message(array('content' => 'Whether to expose this post type in the REST API.')),
            ),
            'rest_base'           => array(
                'name'         => __('REST base', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) REST base', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['rest_base']) ? $request['rest_base'] : '',
                    'input_meta'  => 'rest_base',
                ),
                'help_message' => fed_show_help_message(array('content' => 'The base slug that this post type will use when accessed using the REST API.')),
            ),
            'rewrite'             => array(
                'name'         => __('Rewrite URL', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['rewrite']) ? $request['rewrite'] : '',
                    'input_meta'  => 'rewrite',
                    'input_value' => array('true' => 'True', 'false' => 'False'),
                ),
                'help_message' => fed_show_help_message(array('content' => 'Triggers the handling of rewrites for this post type. To prevent rewrites, set to false.')),
            ),
            'rewrite_slug'        => array(
                'name'         => __('Rewrite URL Slug', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Rewrite URL Slug', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['rewrite_slug']) ? $request['rewrite_slug'] : '',
                    'input_meta'  => 'rewrite_slug',
                ),
                'help_message' => fed_show_help_message(array('content' => 'Slug to rewrite the default url base')),
            ),

        ),
        'Taxonomies'     => fed_cp_checkbox_taxonomies($request),
        'Supports'       => array(
            'title'           => array(
                'name'  => null,
                'input' => array(
                    'input_type'    => 'checkbox',
                    'user_value'    => isset($request['supports']['title']) ? $request['supports']['title'] : 'Enabled',
                    'input_meta'    => 'supports[title]',
                    'label'         => __('Title', 'frontend-dashboard-custom-post'),
                    'default_value' => 'Enabled',
                ),
            ),
            'editor'          => array(
                'name'  => null,
                'input' => array(
                    'input_type'    => 'checkbox',
                    'user_value'    => isset($request['supports']['editor']) ? $request['supports']['editor'] : 'Enable',
                    'input_meta'    => 'supports[editor]',
                    'label'         => __('Editor', 'frontend-dashboard-custom-post'),
                    'default_value' => 'Enable',
                ),
            ),
            'author'          => array(
                'name'  => null,
                'input' => array(
                    'input_type'    => 'checkbox',
                    'user_value'    => isset($request['supports']['author']) ? $request['supports']['author'] : '',
                    'input_meta'    => 'supports[author]',
                    'label'         => __('Author', 'frontend-dashboard-custom-post'),
                    'default_value' => 'Enable',
                ),
            ),
            'thumbnail'       => array(
                'name'  => null,
                'input' => array(
                    'input_type'    => 'checkbox',
                    'user_value'    => isset($request['supports']['thumbnail']) ? $request['supports']['thumbnail'] : '',
                    'input_meta'    => 'supports[thumbnail]',
                    'label'         => __('Thumbnail', 'frontend-dashboard-custom-post'),
                    'default_value' => 'Enable',
                ),
            ),
            'excerpt'         => array(
                'name'  => null,
                'input' => array(
                    'input_type'    => 'checkbox',
                    'user_value'    => isset($request['supports']['excerpt']) ? $request['supports']['excerpt'] : '',
                    'input_meta'    => 'supports[excerpt]',
                    'label'         => __('Excerpt', 'frontend-dashboard-custom-post'),
                    'default_value' => 'Enable',
                ),
            ),
            'trackbacks'      => array(
                'name'  => null,
                'input' => array(
                    'input_type'    => 'checkbox',
                    'user_value'    => isset($request['supports']['trackbacks']) ? $request['supports']['trackbacks'] : '',
                    'input_meta'    => 'supports[trackbacks]',
                    'label'         => __('Trackbacks', 'frontend-dashboard-custom-post'),
                    'default_value' => 'Enable',
                ),
            ),
            'custom-fields'   => array(
                'name'  => null,
                'input' => array(
                    'input_type'    => 'checkbox',
                    'user_value'    => isset($request['supports']['custom-fields']) ? $request['supports']['custom-fields'] : '',
                    'input_meta'    => 'supports[custom-fields]',
                    'label'         => __('Custom Fields', 'frontend-dashboard-custom-post'),
                    'default_value' => 'Enable',
                ),
            ),
            'comments'        => array(
                'name'  => null,
                'input' => array(
                    'input_type'    => 'checkbox',
                    'user_value'    => isset($request['supports']['comments']) ? $request['supports']['comments'] : '',
                    'input_meta'    => 'supports[comments]',
                    'label'         => __('Comments', 'frontend-dashboard-custom-post'),
                    'default_value' => 'Enable',
                ),
            ),
            'revisions'       => array(
                'name'  => null,
                'input' => array(
                    'input_type'    => 'checkbox',
                    'user_value'    => isset($request['supports']['revisions']) ? $request['supports']['revisions'] : '',
                    'input_meta'    => 'supports[revisions]',
                    'label'         => __('Revisions', 'frontend-dashboard-custom-post'),
                    'default_value' => 'Enable',
                ),
            ),
            'page-attributes' => array(
                'name'  => null,
                'input' => array(
                    'input_type'    => 'checkbox',
                    'user_value'    => isset($request['supports']['page-attributes']) ? $request['supports']['page-attributes'] : '',
                    'input_meta'    => 'supports[page-attributes]',
                    'label'         => __('Page Attributes', 'frontend-dashboard-custom-post'),
                    'default_value' => 'Enable',
                ),
            ),
            'post-formats'    => array(
                'name'  => null,
                'input' => array(
                    'input_type'    => 'checkbox',
                    'user_value'    => isset($request['supports']['post-formats']) ? $request['supports']['post-formats'] : '',
                    'input_meta'    => 'supports[post-formats]',
                    'label'         => __('Post Formats', 'frontend-dashboard-custom-post'),
                    'default_value' => 'Enable',
                ),
            ),
        ),
    );

    return $labels;
}

/**
 * @param  array  $request
 *
 * @return array
 */
function fed_cp_get_taxonomies_label(array $request = array())
{

    $labels = array(
        'Basic Settings' => array(
            'slug'          => array(
                'name'         => __('Taxonomy Slug', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) actors (or) act_ors', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['slug']) ? $request['slug'] : '',
                    'input_meta'  => 'slug',
                    'class_name'  => 'fed_convert_space_to_underscore',
                    'is_required' => 'true',
                ),
                'required'     => true,
                'help_message' => fed_show_help_message(array(
                    'content' => __('Taxonomy slug, No Capital letter, space and special characters','frontend-dashboard-custom-post')
                )),
            ),
            'label'         => array(
                'name'         => __('Plural Name', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Actors', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['label']) ? $request['label'] : '',
                    'input_meta'  => 'label',
                    'is_required' => 'true',
                ),
                'required'     => true,
                'help_message' => fed_show_help_message(array('content' => __(' A plural descriptive name for the taxonomy marked for translation.','frontend-dashboard-custom-post'))),
            ),
            'singular_name' => array(
                'name'         => __('Singular Name', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'required'    => 'true',
                    'placeholder' => __('(eg) Actor', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['singular_name']) ? $request['singular_name'] : '',
                    'input_meta'  => 'singular_name',
                ),
                'required'     => true,
                'help_message' => fed_show_help_message(array('content' => __('General name for the taxonomy, usually singular.','frontend-dashboard-custom-post'))),
            ),
            'fed_extra'     => array(
                'input'        => fed_cp_checkbox_post_type($request),
                'label'        => 'Attach to Post Type',
                'required'     => true,
                'help_message' => fed_show_help_message(array(
                    'content' => __('Select any one post type','frontend-dashboard-custom-post')
                )),
            ),
        ),
        'Label Settings' => array(
            'name'                       => array(
                'name'         => __('Name', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Actors', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['name']) ? $request['name'] : '',
                    'input_meta'  => 'name',
                ),
                'help_message' => fed_show_help_message(array('content' => __('General name for the taxonomy, usually plural.','frontend-dashboard-custom-post'))),
            ),
            'menu_name'                  => array(
                'name'         => __('Menu Name', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Actors', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['menu_name']) ? $request['menu_name'] : '',
                    'input_meta'  => 'menu_name',
                ),
                'help_message' => fed_show_help_message(array('content' => __('Default is the same as `name`','frontend-dashboard-custom-post'))),
            ),
            'parent_item_colon'          => array(
                'name'         => __('Parent Item Colon', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Actors:', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['parent_item_colon']) ? $request['parent_item_colon'] : '',
                    'input_meta'  => 'parent_item_colon',
                ),
                'help_message' => fed_show_help_message(array('content' => __('This string isn\'t used on non-hierarchical types. In hierarchical ones the default is Parent Page:','frontend-dashboard-custom-post'))),
            ),
            'all_items'                  => array(
                'name'         => __('All Items', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) All Actors', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['all_items']) ? $request['all_items'] : '',
                    'input_meta'  => 'all_items',
                ),
                'help_message' => fed_show_help_message(array('content' => __('String for the submenu. Default is All Posts/All Pages.','frontend-dashboard-custom-post'))),
            ),
            'add_new_item'               => array(
                'name'         => __('Add New Item', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Add New Actor', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['add_new_item']) ? $request['add_new_item'] : '',
                    'input_meta'  => 'add_new_item',
                ),
                'help_message' => fed_show_help_message(array('content' => __('Default is Add New Post/Add New Page.','frontend-dashboard-custom-post'))),
            ),
            'new_item_name'              => array(
                'name'         => __('New Item Name', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) New Actor Name', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['new_item_name']) ? $request['new_item_name'] : '',
                    'input_meta'  => 'new_item_name',
                ),
                'help_message' => fed_show_help_message(array('content' => __('Default is New Item Name','frontend-dashboard-custom-post'))),
            ),
            'edit_item'                  => array(
                'name'         => __('Edit Item', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Edit Actor', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['edit_item']) ? $request['edit_item'] : '',
                    'input_meta'  => 'edit_item',
                ),
                'help_message' => fed_show_help_message(array('content' => __('Default is Edit Post/Edit Page.','frontend-dashboard-custom-post'))),
            ),
            'view_item'                  => array(
                'name'         => __('View Item', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) View Actor', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['view_item']) ? $request['view_item'] : '',
                    'input_meta'  => 'view_item',
                ),
                'help_message' => fed_show_help_message(array('content' => __('Default is View Post/View Page','frontend-dashboard-custom-post'))),
            ),
            'update_item'                => array(
                'name'         => __('Update Item', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Update Actor', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['update_item']) ? $request['update_item'] : '',
                    'input_meta'  => 'update_item',
                ),
                'help_message' => fed_show_help_message(array('content' => __('Default is Update Item','frontend-dashboard-custom-post'))),
            ),
            'parent_item'                => array(
                'name'         => __('Parent Item', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Parent Actor', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['parent_item']) ? $request['parent_item'] : '',
                    'input_meta'  => 'parent_item',
                ),
                'help_message' => fed_show_help_message(array('content' => __('Default is Update Item','frontend-dashboard-custom-post'))),
            ),
            'search_items'               => array(
                'name'         => __('Search Items', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Search Actor', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['search_items']) ? $request['search_items'] : '',
                    'input_meta'  => 'search_items',
                ),
                'help_message' => fed_show_help_message(array('content' => __('Default is Search Posts/Search Pages','frontend-dashboard-custom-post'))),
            ),
            'not_found'                  => array(
                'name'         => __('Not Found', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Actor Not found', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['not_found']) ? $request['not_found'] : '',
                    'input_meta'  => 'not_found',
                ),
                'help_message' => fed_show_help_message(array('content' => __('Default is No posts found/No pages found.','frontend-dashboard-custom-post'))),
            ),
            'description'                => array(
                'name'         => __('Description', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Description', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['description']) ? $request['description'] : '',
                    'input_meta'  => 'description',
                ),
                'help_message' => fed_show_help_message(array('content' => __('A short descriptive summary of what the taxonomy is.','frontend-dashboard-custom-post'))),
            ),
            'popular_items'              => array(
                'name'         => __('Popular Items', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Popular Actor(s)', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['popular_items']) ? $request['popular_items'] : '',
                    'input_meta'  => 'popular_items',
                ),
                'help_message' => fed_show_help_message(array('content' => __('The popular items text. This string is not used on hierarchical taxonomies. Default is Popular Tags or null','frontend-dashboard-custom-post'))),
            ),
            'separate_items_with_commas' => array(
                'name'         => __('Separate Item with commas', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Separate Actors with commas', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['separate_items_with_commas']) ? $request['separate_items_with_commas'] : '',
                    'input_meta'  => 'separate_items_with_commas',
                ),
                'help_message' => fed_show_help_message(array('content' => __('The separate item with commas text used in the taxonomy meta box. This string is not used on hierarchical taxonomies. Default is __( \'Separate tags with commas\' ), or null','frontend-dashboard-custom-post'))),
            ),
            'add_or_remove_items'        => array(
                'name'         => __('Add or remove tags', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Add or remove Actor', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['add_or_remove_items']) ? $request['add_or_remove_items'] : '',
                    'input_meta'  => 'add_or_remove_items',
                ),
                'help_message' => fed_show_help_message(array('content' => __('the add or remove items text and used in the meta box when JavaScript is disabled. This string is not used on hierarchical taxonomies. Default is __( \'Add or remove tags\' ) or null','frontend-dashboard-custom-post'))),
            ),
            'choose_from_most_used'      => array(
                'name'         => __('Choose from the most used tags', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Choose from the most used Actor', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['choose_from_most_used']) ? $request['choose_from_most_used'] : '',
                    'input_meta'  => 'choose_from_most_used',
                ),
                'help_message' => fed_show_help_message(array('content' => __('the choose from most used text used in the taxonomy meta box. This string is not used on hierarchical taxonomies. Default is __( \'Choose from the most used tags\' ) or null','frontend-dashboard-custom-post'))),
            ),
        ),
        'Settings'       => array(
            'hierarchical'       => array(
                'name'         => __('Hierarchical', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['hierarchical']) ? $request['hierarchical'] : '',
                    'input_meta'  => 'hierarchical',
                    'input_value' => array('false' => 'False', 'true' => 'True'),
                ),
                'help_message' => fed_show_help_message(array('content' => __('Whether the taxonomy is hierarchical (e.g. page). Allows Parent to be specified. The supports parameter should contain page-attributes to show the parent select box on the editor page..','frontend-dashboard-custom-post'))),
            ),
            'show_tagcloud'      => array(
                'name'         => __('Show Tag Cloud', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['show_tagcloud']) ? $request['show_tagcloud'] : '',
                    'input_meta'  => 'show_tagcloud',
                    'input_value' => array('true' => 'True', 'false' => 'False'),
                ),
                'help_message' => fed_show_help_message(array('content' => __('Whether to allow the Tag Cloud widget to use this taxonomy.','frontend-dashboard-custom-post'))),
            ),
            'show_in_quick_edit' => array(
                'name'         => __('Show in Quick Edit', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['show_in_quick_edit']) ? $request['show_in_quick_edit'] : '',
                    'input_meta'  => 'show_in_quick_edit',
                    'input_value' => array('true' => 'True', 'false' => 'False'),
                ),
                'help_message' => fed_show_help_message(array('content' => __('Whether to show the taxonomy in the quick/bulk edit panel','frontend-dashboard-custom-post'))),
            ),
            'show_admin_column'  => array(
                'name'         => __('Show admin column', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['show_admin_column']) ? $request['show_admin_column'] : '',
                    'input_meta'  => 'show_admin_column',
                    'input_value' => array('true' => 'True', 'false' => 'False'),
                ),
                'help_message' => fed_show_help_message(array('content' => __('Whether to allow automatic creation of taxonomy columns on associated post-types table','frontend-dashboard-custom-post'))),
            ),
            'sort'               => array(
                'name'         => __('Sort', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['sort']) ? $request['sort'] : '',
                    'input_meta'  => 'sort',
                    'input_value' => array('true' => 'True', 'false' => 'False'),
                ),
                'help_message' => fed_show_help_message(array('content' => __('Whether this taxonomy should remember the order in which terms are added to objects','frontend-dashboard-custom-post'))),
            ),
            'public'             => array(
                'name'         => __('Public', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['public']) ? $request['public'] : '',
                    'input_meta'  => 'public',
                    'input_value' => array('true' => 'True', 'false' => 'False'),
                ),
                'help_message' => fed_show_help_message(array('content' => __('Controls how the type is visible to authors (show_in_nav_menus, show_ui) and readers (exclude_from_search, publicly_queryable).','frontend-dashboard-custom-post'))),
            ),
            'show_ui'            => array(
                'name'         => __('Show UI', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['show_ui']) ? $request['show_ui'] : '',
                    'input_meta'  => 'show_ui',
                    'input_value' => array('true' => 'True', 'false' => 'False'),
                ),
                'help_message' => fed_show_help_message(array('content' => __('Whether to generate a default UI for managing this taxonomy in the admin.','frontend-dashboard-custom-post'))),
            ),
            'show_in_menu'       => array(
                'name'         => __('Show in Menu', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['show_in_menu']) ? $request['show_in_menu'] : '',
                    'input_meta'  => 'show_in_menu',
                    'input_value' => array('true' => 'True', 'false' => 'False'),
                ),
                'help_message' => fed_show_help_message(array('content' => __('Where to show the taxonomy in the admin menu','frontend-dashboard-custom-post'))),
            ),
            'show_in_nav_menus'  => array(
                'name'         => __('Show in Nav Menus', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['show_in_nav_menus']) ? $request['show_in_nav_menus'] : '',
                    'input_meta'  => 'show_in_nav_menus',
                    'input_value' => array('true' => 'True', 'false' => 'False'),
                ),
                'help_message' => fed_show_help_message(array('content' => __('Whether post_type is available for selection in navigation menus','frontend-dashboard-custom-post'))),
            ),
            'publicly_queryable' => array(
                'name'         => __('Publicly Queryable', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['publicly_queryable']) ? $request['publicly_queryable'] : '',
                    'input_meta'  => 'publicly_queryable',
                    'input_value' => array('true' => 'True', 'false' => 'False'),
                ),
                'help_message' => fed_show_help_message(array('content' => __('Whether queries can be performed on the front end as part of parse_request()','frontend-dashboard-custom-post'))),
            ),
            'query_var'          => array(
                'name'         => __('Query Var', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['query_var']) ? $request['query_var'] : '',
                    'input_meta'  => 'query_var',
                    'input_value' => array('true' => 'True', 'false' => 'False'),
                ),
                'help_message' => fed_show_help_message(array('content' => __('Sets the query_var key for this taxonomy.','frontend-dashboard-custom-post'))),
            ),
            'show_in_rest'       => array(
                'name'         => __('Show in REST', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['show_in_rest']) ? $request['show_in_rest'] : '',
                    'input_meta'  => 'show_in_rest',
                    'input_value' => array('true' => 'True', 'false' => 'False'),
                ),
                'help_message' => fed_show_help_message(array('content' => __('Whether to expose this taxonomy in the REST API.','frontend-dashboard-custom-post'))),
            ),
            'rest_base'          => array(
                'name'         => __('REST base', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) REST base', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['rest_base']) ? $request['rest_base'] : '',
                    'input_meta'  => 'rest_base',
                ),
                'help_message' => fed_show_help_message(array('content' => __('The base slug that this taxonomy will use when accessed using the REST API.','frontend-dashboard-custom-post'))),
            ),
            'rewrite'            => array(
                'name'         => __('Rewrite URL', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'input_type'  => 'select',
                    'user_value'  => isset($request['rewrite']) ? $request['rewrite'] : '',
                    'input_meta'  => 'rewrite',
                    'input_value' => array('true' => 'True', 'false' => 'False'),
                ),
                'help_message' => fed_show_help_message(array('content' => __('Triggers the handling of rewrites for this taxonomy. To prevent rewrites, set to false.','frontend-dashboard-custom-post'))),
            ),
            'rewrite_slug'       => array(
                'name'         => __('Rewrite URL Slug', 'frontend-dashboard-custom-post'),
                'input'        => array(
                    'placeholder' => __('(eg) Rewrite URL Slug', 'frontend-dashboard-custom-post'),
                    'input_type'  => 'single_line',
                    'user_value'  => isset($request['rewrite_slug']) ? $request['rewrite_slug'] : '',
                    'input_meta'  => 'rewrite_slug',
                ),
                'help_message' => fed_show_help_message(array('content' => __('Slug to rewrite the default url base','frontend-dashboard-custom-post'))),
            ),

        ),
    );

    return $labels;
}

/**
 * @return array
 */
function fed_cp_get_all_taxonomies()
{
    return get_taxonomies();
}

/**
 * @param  array  $array
 *
 * @return array
 */
function fed_cp_get_taxonomies(array $array = array('public' => true, 'show_ui' => true))
{
    return get_taxonomies($array, 'objects');
}

/**
 * @return array
 */
function fed_cp_get_taxonomies_key_key()
{
    $tax  = fed_cp_get_taxonomies();
    $keys = array_keys($tax);

    return array_combine($keys, $keys);
}

/**
 * @return array
 */
function fed_cp_get_post_types_key_key()
{
    $post_types = fed_get_public_post_types();
    $keys       = array_keys($post_types);

    return array_combine($keys, $keys);
}

/**
 * @param  array  $request
 *
 * @return array
 */
function fed_cp_checkbox_taxonomies(array $request = array())
{
    $taxonomies = fed_cp_get_taxonomies();
    $core_tax   = array('category', 'post_tag');
    $new_tax    = array();
    foreach ($taxonomies as $index => $taxonomy) {
        $wp_core_tax     = in_array($index, $core_tax, true) ? ' (WP Core)' : '';
        $new_tax[$index] = array(
            'name'  => null,
            'input' => array(
                'input_type'    => 'checkbox',
                'user_value'    => isset($request['taxonomies'][$index]) ? $request['taxonomies'][$index] : '',
                'input_meta'    => 'taxonomies['.$index.']',
                'label'         => sprintf(__('%s', 'frontend-dashboard-custom-post'),$taxonomy->label.$wp_core_tax),
                'default_value' => 'Enable',
            ),
        );
    }

    return $new_tax;
}

/**
 * @param  array  $request
 * @param  null  $tax_name
 *
 * @param  string  $taxo
 *
 * @return array
 */
function fed_cp_checkbox_taxonomies_with_users(array $request = array(), $tax_name = null, $taxo = 'taxonomies')
{
    $taxonomies = get_object_taxonomies($tax_name, 'object');
    $users      = fed_get_user_roles();
    $core_tax   = array('category', 'post_tag');
    $new_tax    = array();
    foreach ($taxonomies as $index => $taxonomy) {
        $wp_core_tax     = in_array($index, $core_tax, true) ? ' (WP Core)' : '';
        $new_tax[$index] = array(
            'heading' => sprintf(__('%s', 'frontend-dashboard-custom-post'),$taxonomy->label.$wp_core_tax),
        );
        foreach ($users as $key => $user) {
            $new_tax[$index]['extra'][$key] = array(
                'input_type'    => 'checkbox',
                'user_value'    => isset($request[$tax_name][$taxo][$index][$key]) ? $request[$tax_name][$taxo][$index][$key] : '',
                'input_meta'    => $taxo.'['.$index.']['.$key.']',
                'label'         => sprintf(__('%s', 'frontend-dashboard-custom-post'),$user),
                'default_value' => 'Enable',
            );
        }
    }

    return $new_tax;
}

/**
 *
 * @param  array  $request
 * @param  null  $tax_name
 * @param       $user_roles
 *
 * @return mixed
 * @todo: working in custom post edit/delete/view
 *
 */
function fed_cp_customize_post_for_user_role(array $request = array(), $tax_name = null, $user_roles)
{
    unset($user_roles['administrator']);
    foreach ($user_roles as $key => $role) {
        $new_tax[$key]                                 = array(
            'heading' => __($role, 'frontend-dashboard-custom-post'),
        );
        $new_tax[$key]['extra']['disable_post_edit']   = array(
            'input_type'  => 'select',
            'user_value'  => isset($request[$tax_name]['post_permission'][$key]['disable_post_edit']) ? $request[$tax_name]['post_permission'][$key]['disable_post_edit'] : '',
            'input_meta'  => 'post_permission['.$key.'][disable_post_edit]',
            'input_value' => fed_yes_no('ASC'),
            'label_title' => 'Disable Post Edit',
        );
        $new_tax[$key]['extra']['disable_post_view']   = array(
            'input_type'  => 'select',
            'user_value'  => isset($request[$tax_name]['post_permission'][$key]['disable_post_view']) ? $request[$tax_name]['post_permission'][$key]['disable_post_view'] : '',
            'input_meta'  => 'post_permission['.$key.'][disable_post_view]',
            'input_value' => fed_yes_no('ASC'),
            'label_title' => 'Disable Post View',
        );
        $new_tax[$key]['extra']['disable_post_delete'] = array(
            'input_type'  => 'select',
            'user_value'  => isset($request[$tax_name]['post_permission'][$key]['disable_post_delete']) ? $request[$tax_name]['post_permission'][$key]['disable_post_delete'] : '',
            'input_meta'  => 'post_permission['.$key.'][disable_post_delete]',
            'input_value' => fed_yes_no('ASC'),
            'label_title' => 'Disable Post Delete',
        );
    }

    return $new_tax;
}

/**
 * @param  array  $request
 *
 * @return array
 */
function fed_cp_checkbox_post_type(array $request = array())
{
    $taxonomies = fed_get_public_post_types();
    $core_tax   = array('post', 'page', 'attachment');
    $new_tax    = array();
    foreach ($taxonomies as $index => $taxonomy) {
        $wp_core_tax     = in_array($index, $core_tax, true) ? __('(WP Core)','frontend-dashboard-custom-post') : '';
        $new_tax[$index] = array(
            'name'     => null,
            'input'    => array(
                'input_type'    => 'checkbox',
                'user_value'    => isset($request['object_type'][$index]) ? $request['object_type'][$index] : '',
                'input_meta'    => 'object_type['.$index.']',
                'label'         => __($taxonomy.$wp_core_tax, 'frontend-dashboard-custom-post'),
                'default_value' => 'Enable',
            ),
            'required' => true,
        );
    }

    return $new_tax;
}

/**
 * @return array
 */
function fed_cp_default_custom_post_types_key()
{
    $default    = apply_filters('fed_cp_default_post_types', array(
        'slug'                  => 'slug',
        'name'                  => 'name',
        'singular_name'         => 'singular_name',
        'menu_name'             => 'menu_name',
        'name_admin_bar'        => 'name_admin_bar',
        'archives'              => 'archives',
        'attributes'            => 'attributes',
        'parent_item_colon'     => 'parent_item_colon',
        'all_items'             => 'all_items',
        'add_new_item'          => 'add_new_item',
        'add_new'               => 'add_new',
        'new_item'              => 'new_item',
        'edit_item'             => 'edit_item',
        'view_item'             => 'view_item',
        'view_items'            => 'view_items',
        'search_items'          => 'search_items',
        'not_found'             => 'not_found',
        'not_found_in_trash'    => 'not_found_in_trash',
        'featured_image'        => 'featured_image',
        'set_featured_image'    => 'set_featured_image',
        'remove_featured_image' => 'remove_featured_image',
        'use_featured_image'    => 'use_featured_image',
        'insert_into_item'      => 'insert_into_item',
        'uploaded_to_this_item' => 'uploaded_to_this_item',
        'items_list'            => 'items_list',
        'items_list_navigation' => 'items_list_navigation',
        'filter_items_list'     => 'filter_items_list',
        'label'                 => 'label',
        'description'           => 'description',
        'supports'              => 'supports',
        'taxonomies'            => 'taxonomies',
        'hierarchical'          => 'hierarchical',
        'public'                => 'public',
        'show_ui'               => 'show_ui',
        'show_in_menu'          => 'show_in_menu',
        'menu_position'         => 'menu_position',
        'show_in_admin_bar'     => 'show_in_admin_bar',
        'show_in_nav_menus'     => 'show_in_nav_menus',
        'can_export'            => 'can_export',
        'has_archive'           => 'has_archive',
        'exclude_from_search'   => 'exclude_from_search',
        'publicly_queryable'    => 'publicly_queryable',

        'menu_icon'        => 'menu_icon',
        'capability_type'  => 'capability_type',
        'rewrite'          => 'rewrite',
        'rewrite_slug'     => 'rewrite_slug',
        'query_var'        => 'query_var',
        'delete_with_user' => 'delete_with_user',
        'show_in_rest'     => 'show_in_rest',
        'rest_base'        => 'rest_base',

    ));
    $taxonomies = array('taxonomies' => fed_cp_get_taxonomies_key_key());

    $supports = array(
        'supports' => array(
            'title'           => 'title',
            'post-formats'    => 'post-formats',
            'page-attributes' => 'page-attributes',
            'revisions'       => 'revisions',
            'comments'        => 'comments',
            'custom-fields'   => 'custom-fields',
            'trackbacks'      => 'trackbacks',
            'excerpt'         => 'excerpt',
            'thumbnail'       => 'thumbnail',
            'author'          => 'author',
            'editor'          => 'editor',
        ),
    );

    return array_merge($default, $taxonomies, $supports);
}

/**
 * @return array
 */
function fed_cp_default_taxonomies_key()
{
    $default     = apply_filters('fed_cp_default_taxonomies', array(
        'slug'                       => 'slug',
        'name'                       => 'name',
        'singular_name'              => 'singular_name',
        'menu_name'                  => 'menu_name',
        'update_item'                => 'update_item',
        'new_item_name'              => 'new_item_name',
        'popular_items'              => 'popular_items',
        'separate_items_with_commas' => 'separate_items_with_commas',
        'add_or_remove_items'        => 'add_or_remove_items',
        'choose_from_most_used'      => 'choose_from_most_used',
        'show_tagcloud'              => 'show_tagcloud',
        'show_in_quick_edit'         => 'show_in_quick_edit',
        'show_admin_column'          => 'show_admin_column',
        'sort'                       => 'sort',
        'parent_item_colon'          => 'parent_item_colon',
        'parent_item'                => 'parent_item',
        'all_items'                  => 'all_items',
        'add_new_item'               => 'add_new_item',
        'edit_item'                  => 'edit_item',
        'view_item'                  => 'view_item',
        'search_items'               => 'search_items',
        'not_found'                  => 'not_found',
        'label'                      => 'label',
        'description'                => 'description',
        'supports'                   => 'supports',
        'taxonomies'                 => 'taxonomies',
        'hierarchical'               => 'hierarchical',
        'public'                     => 'public',
        'show_ui'                    => 'show_ui',
        'show_in_menu'               => 'show_in_menu',
        'show_in_nav_menus'          => 'show_in_nav_menus',
        'publicly_queryable'         => 'publicly_queryable',
        'rewrite'                    => 'rewrite',
        'rewrite_slug'               => 'rewrite_slug',
        'query_var'                  => 'query_var',
        'show_in_rest'               => 'show_in_rest',
        'rest_base'                  => 'rest_base',
    ));
    $object_type = array('object_type' => fed_cp_get_post_types_key_key());

    return array_merge($default, $object_type);
}

/**
 * @return array
 */
function fed_cp_default_taxonomies()
{
    return array(
        'action',
        'attachment',
        'attachment_id',
        'author',
        'author_name',
        'calendar',
        'cat',
        'category',
        'category__and',
        'category__in',
        'category__not_in',
        'category_name',
        'comments_per_page',
        'comments_popup',
        'customize_messenger_channel',
        'customized',
        'cpage',
        'day',
        'debug',
        'error',
        'exact',
        'feed',
        'fields',
        'hour',
        'include',
        'link_category',
        'm',
        'minute',
        'monthnum',
        'more',
        'name',
        'nav_menu',
        'nonce',
        'nopaging',
        'offset',
        'order',
        'orderby',
        'p',
        'page',
        'page_id',
        'paged',
        'pagename',
        'pb',
        'perm',
        'post',
        'post__in',
        'post__not_in',
        'post_format',
        'post_mime_type',
        'post_status',
        'post_tag',
        'post_type',
        'posts',
        'posts_per_archive_page',
        'posts_per_page',
        'preview',
        'robots',
        's',
        'search',
        'second',
        'sentence',
        'showposts',
        'static',
        'subpost',
        'subpost_id',
        'tag',
        'tag__and',
        'tag__in',
        'tag__not_in',
        'tag_id',
        'tag_slug__and',
        'tag_slug__in',
        'taxonomy',
        'tb',
        'term',
        'theme',
        'type',
        'w',
        'withcomments',
        'withoutcomments',
        'year',
    );
}

/**
 *
 */
function fed_cp_custom_menu_icons_popup()
{
    ?>
    <div class="bc_fed">
        <div class="modal fade fed_show_fa_list"
             tabindex="-1"
             role="dialog"
        >
            <div class="modal-dialog modal-lg"
                 role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button"
                                class="close"
                                data-dismiss="modal"
                                aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title"><?php _e('Please Select one Image',
                                'frontend-dashboard-custom-post') ?></h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden"
                               id="fed_menu_box_id"
                               name="fed_menu_box_id"
                               value=""/>
                        <div class="row fed_fa_container">
                            <?php foreach (fed_dashicon_list() as $key => $list) {
                                echo '<div class="col-md-1 fed_single_fa" 
							data-dismiss="modal"
							data-id="'.$key.'"
							data-toggle="popover"
							title="'.$list.'"
							data-trigger="hover"
							data-viewport=""
							data-content="'.$list.'"
							>
							<span class="dashicons '.$key.'"  data-id="'.$key.'" id="'.$key.'"></span>
							</div>';
                            } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * @return array
 */
function fed_dashicon_list()
{
    return array(
        'dashicons-menu'                    => 'menu',
        'dashicons-admin-site'              => 'admin site',
        'dashicons-dashboard'               => 'dashboard',
        'dashicons-admin-post'              => 'admin post',
        'dashicons-admin-media'             => 'admin media',
        'dashicons-admin-links'             => 'admin links',
        'dashicons-admin-page'              => 'admin page',
        'dashicons-admin-comments'          => 'admin comments',
        'dashicons-admin-appearance'        => 'admin appearance',
        'dashicons-admin-plugins'           => 'admin plugins',
        'dashicons-admin-users'             => 'admin users',
        'dashicons-admin-tools'             => 'admin tools',
        'dashicons-admin-settings'          => 'admin settings',
        'dashicons-admin-network'           => 'admin network',
        'dashicons-admin-home'              => 'admin home',
        'dashicons-admin-generic'           => 'admin generic',
        'dashicons-admin-collapse'          => 'admin collapse',
        'dashicons-filter'                  => 'filter',
        'dashicons-admin-customizer'        => 'admin customizer',
        'dashicons-admin-multisite'         => 'admin multisite',
        'dashicons-welcome-write-blog'      => 'welcome write blog',
        'dashicons-welcome-add-page'        => 'welcome add page',
        'dashicons-welcome-view-site'       => 'welcome view site',
        'dashicons-welcome-widgets-menus'   => 'welcome widgets menus',
        'dashicons-welcome-comments'        => 'welcome comments',
        'dashicons-welcome-learn-more'      => 'welcome learn more',
        'dashicons-format-standard'         => 'format standard',
        'dashicons-format-aside'            => 'format aside',
        'dashicons-format-image'            => 'format image',
        'dashicons-format-gallery'          => 'format gallery',
        'dashicons-format-video'            => 'format video',
        'dashicons-format-status'           => 'format status',
        'dashicons-format-quote'            => 'format quote',
        'dashicons-format-links'            => 'format links',
        'dashicons-format-chat'             => 'format chat',
        'dashicons-format-audio'            => 'format audio',
        'dashicons-camera'                  => 'camera',
        'dashicons-images-alt'              => 'images alt',
        'dashicons-images-alt2'             => 'images alt2',
        'dashicons-video-alt'               => 'video alt',
        'dashicons-video-alt2'              => 'video alt2',
        'dashicons-video-alt3'              => 'video alt3',
        'dashicons-media-archive'           => 'media archive',
        'dashicons-media-audio'             => 'media audio',
        'dashicons-media-code'              => 'media code',
        'dashicons-media-default'           => 'media default',
        'dashicons-media-document'          => 'media document',
        'dashicons-media-interactive'       => 'media interactive',
        'dashicons-media-spreadsheet'       => 'media spreadsheet',
        'dashicons-media-text'              => 'media text',
        'dashicons-media-video'             => 'media video',
        'dashicons-playlist-audio'          => 'playlist audio',
        'dashicons-playlist-video'          => 'playlist video',
        'dashicons-controls-play'           => 'controls play',
        'dashicons-controls-pause'          => 'controls pause',
        'dashicons-controls-forward'        => 'controls forward',
        'dashicons-controls-skipforward'    => 'controls skipforward',
        'dashicons-controls-back'           => 'controls back',
        'dashicons-controls-skipback'       => 'controls skipback',
        'dashicons-controls-repeat'         => 'controls repeat',
        'dashicons-controls-volumeon'       => 'controls volumeon',
        'dashicons-controls-volumeoff'      => 'controls volumeoff',
        'dashicons-image-crop'              => 'image crop',
        'dashicons-image-rotate'            => 'image rotate',
        'dashicons-image-flip-vertical'     => 'image flip vertical',
        'dashicons-image-flip-horizontal'   => 'image flip horizontal',
        'dashicons-image-filter'            => 'image filter',
        'dashicons-undo'                    => 'undo',
        'dashicons-redo'                    => 'redo',
        'dashicons-editor-bold'             => 'editor bold',
        'dashicons-editor-italic'           => 'editor italic',
        'dashicons-editor-ul'               => 'editor ul',
        'dashicons-editor-ol'               => 'editor ol',
        'dashicons-editor-quote'            => 'editor quote',
        'dashicons-editor-alignleft'        => 'editor alignleft',
        'dashicons-editor-aligncenter'      => 'editor aligncenter',
        'dashicons-editor-alignright'       => 'editor alignright',
        'dashicons-editor-insertmore'       => 'editor insertmore',
        'dashicons-editor-spellcheck'       => 'editor spellcheck',
        'dashicons-editor-distractionfree'  => 'editor distractionfree',
        'dashicons-editor-expand'           => 'editor expand',
        'dashicons-editor-contract'         => 'editor contract',
        'dashicons-editor-kitchensink'      => 'editor kitchensink',
        'dashicons-editor-underline'        => 'editor underline',
        'dashicons-editor-justify'          => 'editor justify',
        'dashicons-editor-textcolor'        => 'editor textcolor',
        'dashicons-editor-paste-word'       => 'editor paste word',
        'dashicons-editor-removeformatting' => 'editor removeformatting',
        'dashicons-editor-video'            => 'editor video',
        'dashicons-editor-customchar'       => 'editor customchar',
        'dashicons-editor-outdent'          => 'editor outdent',
        'dashicons-editor-indent'           => 'editor indent',
        'dashicons-editor-help'             => 'editor help',
        'dashicons-editor-strikethrough'    => 'editor strikethrough',
        'dashicons-editor-unlink'           => 'editor unlink',
        'dashicons-editor-rtl'              => 'editor rtl',
        'dashicons-editor-break'            => 'editor break',
        'dashicons-editor-code'             => 'editor code',
        'dashicons-editor-paragraph'        => 'editor paragraph',
        'dashicons-editor-table'            => 'editor table',
        'dashicons-align-left'              => 'align left',
        'dashicons-align-right'             => 'align right',
        'dashicons-align-center'            => 'align center',
        'dashicons-align-none'              => 'align none',
        'dashicons-lock'                    => 'lock',
        'dashicons-unlock'                  => 'unlock',
        'dashicons-calendar'                => 'calendar',
        'dashicons-calendar-alt'            => 'calendar alt',
        'dashicons-visibility'              => 'visibility',
        'dashicons-hidden'                  => 'hidden',
        'dashicons-post-status'             => 'post status',
        'dashicons-edit'                    => 'edit',
        'dashicons-trash'                   => 'trash',
        'dashicons-sticky'                  => 'sticky',
        'dashicons-external'                => 'external',
        'dashicons-arrow-up'                => 'arrow up',
        'dashicons-arrow-down'              => 'arrow down',
        'dashicons-arrow-right'             => 'arrow right',
        'dashicons-arrow-left'              => 'arrow left',
        'dashicons-arrow-up-alt'            => 'arrow up',
        'dashicons-arrow-down-alt'          => 'arrow down',
        'dashicons-arrow-right-alt'         => 'arrow right',
        'dashicons-arrow-left-alt'          => 'arrow left',
        'dashicons-arrow-up-alt2'           => 'arrow up',
        'dashicons-arrow-down-alt2'         => 'arrow down',
        'dashicons-arrow-right-alt2'        => 'arrow right',
        'dashicons-arrow-left-alt2'         => 'arrow left',
        'dashicons-sort'                    => 'sort',
        'dashicons-leftright'               => 'leftright',
        'dashicons-randomize'               => 'randomize',
        'dashicons-list-view'               => 'list view',
        'dashicons-exerpt-view'             => 'exerpt view',
        'dashicons-grid-view'               => 'grid view',
        'dashicons-move'                    => 'move',
        'dashicons-share'                   => 'share',
        'dashicons-share-alt'               => 'share alt',
        'dashicons-share-alt2'              => 'share alt2',
        'dashicons-twitter'                 => 'twitter',
        'dashicons-rss'                     => 'rss',
        'dashicons-email'                   => 'email',
        'dashicons-email-alt'               => 'email alt',
        'dashicons-facebook'                => 'facebook',
        'dashicons-facebook-alt'            => 'facebook alt',
        'dashicons-googleplus'              => 'googleplus',
        'dashicons-networking'              => 'networking',
        'dashicons-hammer'                  => 'hammer',
        'dashicons-art'                     => 'art',
        'dashicons-migrate'                 => 'migrate',
        'dashicons-performance'             => 'performance',
        'dashicons-universal-access'        => 'universal access',
        'dashicons-tickets'                 => 'tickets',
        'dashicons-nametag'                 => 'nametag',
        'dashicons-clipboard'               => 'clipboard',
        'dashicons-heart'                   => 'heart',
        'dashicons-megaphone'               => 'megaphone',
        'dashicons-schedule'                => 'schedule',
        'dashicons-wordpress'               => 'wordpress',
        'dashicons-wordpress-alt'           => 'wordpress alt',
        'dashicons-pressthis'               => 'pressthis',
        'dashicons-update'                  => 'update',
        'dashicons-screenoptions'           => 'screenoptions',
        'dashicons-info'                    => 'info',
        'dashicons-cart'                    => 'cart',
        'dashicons-feedback'                => 'feedback',
        'dashicons-cloud'                   => 'cloud',
        'dashicons-translation'             => 'translation',
        'dashicons-tag'                     => 'tag',
        'dashicons-category'                => 'category',
        'dashicons-archive'                 => 'archive',
        'dashicons-tagcloud'                => 'tagcloud',
        'dashicons-text'                    => 'text',
        'dashicons-yes'                     => 'yes',
        'dashicons-no'                      => 'no',
        'dashicons-no-alt'                  => 'no alt',
        'dashicons-plus'                    => 'plus',
        'dashicons-plus-alt'                => 'plus alt',
        'dashicons-minus'                   => 'minus',
        'dashicons-dismiss'                 => 'dismiss',
        'dashicons-marker'                  => 'marker',
        'dashicons-star-filled'             => 'star filled',
        'dashicons-star-half'               => 'star half',
        'dashicons-star-empty'              => 'star empty',
        'dashicons-flag'                    => 'flag',
        'dashicons-warning'                 => 'warning',
        'dashicons-location'                => 'location',
        'dashicons-location-alt'            => 'location alt',
        'dashicons-vault'                   => 'vault',
        'dashicons-shield'                  => 'shield',
        'dashicons-shield-alt'              => 'shield alt',
        'dashicons-sos'                     => 'sos',
        'dashicons-search'                  => 'search',
        'dashicons-slides'                  => 'slides',
        'dashicons-analytics'               => 'analytics',
        'dashicons-chart-pie'               => 'chart pie',
        'dashicons-chart-bar'               => 'chart bar',
        'dashicons-chart-line'              => 'chart line',
        'dashicons-chart-area'              => 'chart area',
        'dashicons-groups'                  => 'groups',
        'dashicons-businessman'             => 'businessman',
        'dashicons-id'                      => 'id',
        'dashicons-id-alt'                  => 'id alt',
        'dashicons-products'                => 'products',
        'dashicons-awards'                  => 'awards',
        'dashicons-forms'                   => 'forms',
        'dashicons-testimonial'             => 'testimonial',
        'dashicons-portfolio'               => 'portfolio',
        'dashicons-book'                    => 'book',
        'dashicons-book-alt'                => 'book alt',
        'dashicons-download'                => 'download',
        'dashicons-upload'                  => 'upload',
        'dashicons-backup'                  => 'backup',
        'dashicons-clock'                   => 'clock',
        'dashicons-lightbulb'               => 'lightbulb',
        'dashicons-microphone'              => 'microphone',
        'dashicons-desktop'                 => 'desktop',
        'dashicons-laptop'                  => 'laptop',
        'dashicons-tablet'                  => 'tablet',
        'dashicons-smartphone'              => 'smartphone',
        'dashicons-phone'                   => 'phone',
        'dashicons-index-card'              => 'index card',
        'dashicons-carrot'                  => 'carrot',
        'dashicons-building'                => 'building',
        'dashicons-store'                   => 'store',
        'dashicons-album'                   => 'album',
        'dashicons-palmtree'                => 'palmtree',
        'dashicons-tickets-alt'             => 'tickets alt',
        'dashicons-money'                   => 'money',
        'dashicons-smiley'                  => 'smiley',
        'dashicons-thumbs-up'               => 'thumbs up',
        'dashicons-thumbs-down'             => 'thumbs down',
        'dashicons-layout'                  => 'layout',
        'dashicons-paperclip'               => 'paperclip',
    );
}

/**
 * @param  string  $post_type
 *
 * @return bool
 */
function fed_cp_is_user_can_edit_post($post_type = 'post')
{
    $settings = get_option('fed_cp_admin_settings');
    $options  = $settings[$post_type];

    $user = new WP_User(get_current_user_id());
    $user->add_cap('edit_posts');

    if ( ! isset($options['settings']['disable_post_edit'])) {
        return true;
    }

    if (isset($options['settings']['disable_post_edit']) && $options['settings']['disable_post_edit'] == 'yes') {
        $user->remove_cap('edit_posts');

        return false;
    }

    return true;
}

/**
 * @param  string  $post_type
 *
 * @return bool
 */
function fed_cp_is_user_can_delete_post($post_type = 'post')
{
    $settings = get_option('fed_cp_admin_settings');
    $options  = $settings[$post_type];

    $user = new WP_User(get_current_user_id());
    $user->add_cap('delete_posts');
    if ( ! isset($options['settings']['disable_post_delete'])) {
        return true;
    }

    if (isset($options['settings']['disable_post_delete']) && $options['settings']['disable_post_delete'] == 'yes') {
        $user->remove_cap('delete_posts');

        return false;
    }

    return true;
}

/**
 * @param  string  $post_type
 *
 * @return bool
 */
function fed_cp_is_user_can_view_post($post_type = 'post')
{
    $settings = get_option('fed_cp_admin_settings');
    $options  = $settings[$post_type];

    if ( ! isset($options['settings']['disable_post_view'])) {
        return true;
    }

    if (isset($options['settings']['disable_post_view']) && $options['settings']['disable_post_view'] == 'yes') {
        return false;
    }

    return true;
}


/**
 * @param  string  $post_type
 *
 * @return bool
 */
function fed_cp_is_user_can_add_post($post_type = 'post')
{
    $settings = get_option('fed_cp_admin_settings');
    $options  = $settings[$post_type];

    if ( ! isset($options['settings']['disable_post_add_new'])) {
        return true;
    }

    if (isset($options['settings']['disable_post_add_new']) && $options['settings']['disable_post_add_new'] == 'yes') {
        return false;
    }

    return true;
}




