<?php
/**
 * Installation
 *
 * @package frontend-dashboard-custom-post
 */

/**
 * Installation and Migration
 */
function fed_custom_post_install()
{
    $cp_admin_settings = get_option('fed_cp_admin_settings', array());
    if ( ! is_array($cp_admin_settings) ) {
        $cp_admin_settings = array();
    }
    $all_roles = function_exists('fed_get_user_roles') ? fed_get_user_roles() : array();

    // Ensure core 'post' exists in fed_cp_admin_settings
    if ( ! isset($cp_admin_settings['post']) ) {
        $admin_settings = get_option('fed_admin_settings_post', array());
        if ( empty($admin_settings) && ! empty($all_roles) ) {
            $admin_settings = fed_get_default_post_options($all_roles);
        }
        $cp_admin_settings['post'] = $admin_settings;
    }

    // Ensure core 'page' exists in fed_cp_admin_settings
    if ( ! isset($cp_admin_settings['page']) && ! empty($all_roles) ) {
        $page_settings = fed_get_default_post_options($all_roles);
        $page_settings['menu']['rename_post']    = __('Pages', 'frontend-dashboard-custom-post');
        $page_settings['menu']['post_menu_icon'] = 'fas fa-file-alt';
        $page_settings['menu']['post_position']  = 20;
        $cp_admin_settings['page'] = $page_settings;
    }

    // Normalize any existing custom post slugs (e.g. KPost -> kpost) in fed_cp_custom_posts
    $custom_posts = get_option('fed_cp_custom_posts', array());
    if ( is_array($custom_posts) && ! empty($custom_posts) ) {
        $normalized_cpt = array();
        $updated_cpt    = false;
        foreach ( $custom_posts as $cpt_key => $cpt_val ) {
            $normalized_key   = sanitize_key( ! empty($cpt_val['slug']) ? $cpt_val['slug'] : $cpt_key );
            $cpt_val['slug']  = $normalized_key;
            $normalized_cpt[ $normalized_key ] = $cpt_val;
            if ( $normalized_key !== $cpt_key ) {
                $updated_cpt = true;
            }

            if ( ! isset($cp_admin_settings[ $normalized_key ]) && ! empty($all_roles) ) {
                $cpt_settings = fed_get_default_post_options($all_roles);
                $cpt_settings['menu']['rename_post']    = ! empty($cpt_val['label']) ? $cpt_val['label'] : $normalized_key;
                $cpt_settings['menu']['post_menu_icon'] = ! empty($cpt_val['menu_icon']) ? $cpt_val['menu_icon'] : 'dashicons-admin-post';
                $cp_admin_settings[ $normalized_key ]   = $cpt_settings;
            }
        }
        if ( $updated_cpt ) {
            update_option('fed_cp_custom_posts', $normalized_cpt);
        }
    }

    update_option('fed_cp_admin_settings', $cp_admin_settings);
}
