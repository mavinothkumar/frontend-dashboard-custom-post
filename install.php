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
    if ( ! isset($cp_admin_settings['post'])) {
        $admin_settings = array('post' => get_option('fed_admin_settings_post', array()));
        $merge          = array_merge($cp_admin_settings, $admin_settings);
        update_option('fed_cp_admin_settings', $merge);
    }
}
