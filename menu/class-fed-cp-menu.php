<?php
/**
 * Custom Post Menu
 *
 * @package frontend-dashboard-custom-post
 */

if ( ! class_exists('Fed_Cp_Menu')) {
    /**
     * Class Fed_Cp_Menu
     */
    class Fed_Cp_Menu
    {
        /**
         * FEDCP_Menu constructor.
         */
        public function __construct()
        {
            add_filter('fed_admin_dashboard_settings_menu_header', array(
                $this,
                'fed_cp_admin_dashboard_settings_menu_header',
            ));
            add_filter('fed_get_custom_post_settings_by_type', array(
                $this,
                'fed_cp_get_custom_post_settings_by_type',
            ), 10, 2);
            add_action('fed_default_admin_scripts_styles', array($this, 'fed_cp_enqueue_script_style_admin'));
            add_action('fed_default_frontend_scripts_styles', array($this, 'fed_cp_enqueue_script_style_admin'));
            add_action('wp_ajax_fed_cp_admin_settings', array($this, 'fed_cp_admin_settings'));

            add_filter('fed_admin_script_loading_pages', array($this, 'fed_cp_admin_script_loading_pages'));

            add_action('fed_frontend_main_menu', array($this, 'fed_cp_frontend_main_menu'));
            add_action('fed_restrictive_menu_names', array($this, 'fed_cp_restrictive_menu_names'));
            add_action('fed_frontend_dashboard_menu_container', array(
                $this,
                'fed_cp_frontend_dashboard_menu_container',
            ), 10, 2);

            add_action('wp_ajax_fed_dashboard_add_edit_post', array($this, 'fed_dashboard_add_edit_post'));
            add_action('wp_ajax_fed_dashboard_delete_post_by_id', array(
                $this,
                'fed_dashboard_delete_post_by_id_fn',
            ));
        }

        /**
         * Add Edit Post
         */
        public function fed_dashboard_add_edit_post()
        {
            $post = $_REQUEST;

            fed_nonce_check($post);

            $fed_admin_options = fed_get_post_settings_by_type($post['fed_post_type']);

            $user_role = fed_get_current_user_role();
            if (count(array_intersect($user_role,
                    array_keys($fed_admin_options['permissions']['post_permission']))) > 0) {
                $extras      = fed_fetch_table_rows_with_key(BC_FED_TABLE_POST, 'input_meta');
                $post_status = isset($fed_admin_options['settings']['fed_post_status']) ? sanitize_text_field($fed_admin_options['settings']['fed_post_status']) : 'publish';

                if (empty($post['post_title'])) {
                    $error = new WP_Error('fed_dashboard_add_post_title_missing',
                        __('Please fill post title', 'frontend-dashboard-custom-post'));
                    wp_send_json_error(array('message' => $error->get_error_messages()));
                }

                $default = array(
                    'post_title'     => sanitize_text_field($post['post_title']),
                    'post_content'   => isset($post['post_content']) ? wp_kses_post($post['post_content']) : '',
                    'post_category'  => isset($post['post_category']) ? sanitize_text_field($post['post_category']) : '',
                    'tags_input'     => isset($post['tags_input']) ? implode(',', $post['tags_input']) : '',
                    'post_type'      => isset($post['post_type']) ? sanitize_text_field($post['post_type']) : 'post',
                    'comment_status' => isset($post['comment_status']) ? sanitize_text_field($post['comment_status']) : 'open',
                    'post_status'    => $post_status,
                );

                if (isset($post['ID'])) {
                    $default['ID'] = (int) $post['ID'];
                }

                if (isset($post['_thumbnail_id'])) {
                    $default['_thumbnail_id'] = $post['_thumbnail_id'] == '' ? -1 : (int) $post['_thumbnail_id'];
                }

                if (isset($post['tax_input'])) {
                    $default['tax_input'] = $post['tax_input'];
                }

                foreach ($extras as $index => $extra) {
                    $default['meta_input'][$index] = isset($post[$index]) ? sanitize_text_field($post[$index]) : '';
                }

                $success = wp_insert_post($default);

                if ($success instanceof WP_Error) {
                    wp_send_json_error($success->get_error_messages());
                }

                wp_send_json_success(array(
                    'message' => $post['post_title'].__(' Successfully saved', 'frontend-dashboard-custom-post'),
                ));
            }
            $error = new WP_Error('fed_action_not_allowed',
                __('Sorry! your are not allowed to do this action', 'frontend-dashboard-custom-post'));

            wp_send_json_error(array('message' => $error->get_error_messages()));
        }

        /**
         * Enqueue Script and Style in Admin
         *
         * @param $scripts
         *
         * @return mixed
         */
        public function fed_cp_enqueue_script_style_admin($scripts)
        {
            $scripts['scripts']['fed_cp_script'] = array(
                'wp_core'      => false,
                'name'         => 'Custom Post',
                'plugin_name'  => 'Frontend Dashboard Custom Post',
                'src'          => plugins_url('/assets/fed_cp_script.js', FED_CP_PLUGIN),
                'dependencies' => array(),
                'version'      => false,
                'in_footer'    => true,
            );

            $scripts['styles']['fed_cp_style'] = array(
                'wp_core'      => false,
                'name'         => 'Custom Post',
                'plugin_name'  => 'Frontend Dashboard Custom Post',
                'src'          => plugins_url('/assets/fed_cp_style.css', FED_CP_PLUGIN),
                'dependencies' => array(),
                'version'      => false,
                'media'        => false,
            );

            return $scripts;
        }

        /**
         * Action Hook to Admin Script Loading Pages
         *
         * @param  array  $array  array.
         *
         * @return array
         */
        public function fed_cp_admin_script_loading_pages($array)
        {
            $array[] = 'fed_custom_post';
            $array[] = 'fed_taxonomies';

            return $array;
        }

        /**
         * Restrictive Menu Names
         *
         * @param  array  $slug  Slug.
         *
         * @return array
         */
        public function fed_cp_restrictive_menu_names($slug)
        {
            $post_type = array_keys(fed_get_public_post_types());

            return array_merge($slug, $post_type);
        }

        /**
         * Custom Post Settings By Type
         *
         * @param  array  $array  Array.
         * @param  string  $post_type  Post Type.
         *
         * @return mixed
         */
        public function fed_cp_get_custom_post_settings_by_type($array, $post_type)
        {
            $custom_post_settings = get_option('fed_cp_admin_settings');

            return isset($custom_post_settings[$post_type]) ? $custom_post_settings[$post_type] : $array;

        }

        /**
         * Frontend Main Menu
         *
         * @param  array  $menus  Menus.
         *
         * @return array
         */
        public function fed_cp_frontend_main_menu($menus)
        {
            $get_default_post_items    = fed_get_public_post_types();
            $admin_custom_post_options = get_option('fed_cp_admin_settings');
            $default                   = array();
            if ($admin_custom_post_options) {
                $user = get_userdata(get_current_user_id());
                foreach ($admin_custom_post_options as $key => $options) {
                    if (in_array($key, array_keys($get_default_post_items))) {
                        $post_type     = get_post_type_object($key);
                        $menu_position = (isset($options['menu']['post_position']) && $options['menu']['post_position'] != '') ? (int) $options['menu']['post_position'] : 99;

                        $menu_name = $this->getMenuNameByPostType($options, $post_type);

                        $menu_icon = $this->getMenuIconByPostType($options, $post_type);


                        if (isset($options['permissions']['post_permission']) && count(array_intersect($user->roles,
                                array_keys($options['permissions']['post_permission']))) > 0) {
                            $default[$key] = array(
                                'id'                => $key,
                                'menu_slug'         => 'post',
                                'menu'              => $menu_name,
                                'menu_order'        => $menu_position,
                                'menu_image_id'     => $menu_icon,
                                'show_user_profile' => 'disable',
                                'menu_type'         => 'post',
                            );
                        }
                    }
                }
            }

            return array_merge($menus, $default);

        }

        /**
         * Admin Dashboard Settings Menu Header
         *
         * @param  array  $menu  Menu.
         *
         * @return array
         */
        public function fed_cp_admin_dashboard_settings_menu_header($menu)
        {
            return array_merge($menu, array(
                'custom_post' => array(
                    'icon_class' => 'fa fa-envelope-open',
                    'name'       => 'Post/Custom Post',
                    'callable'   => array('object' => $this, 'method' => 'fed_cp_show_admin_settings'),
                ),
            ));
        }

        /**
         * Menu Container
         *
         * @param  array  $request  Request.
         * @param  array  $menu_items  Menu items.
         */
        public function fed_cp_frontend_dashboard_menu_container($request, $menu_items)
        {

            if ($menu_items['menu_request']['menu_type'] === 'post') {
                $post_menus = get_option('fed_cp_admin_settings');
                $post_type  = get_post_type_object($menu_items['menu_request']['menu_slug']);
                $menu_name  = $this->getMenuNameByPostType($post_menus[$menu_items['menu_request']['menu_id']],
                    $post_type);
                $menu_icon  = $this->getMenuIconByPostType($post_menus[$menu_items['menu_request']['menu_id']],
                    $post_type);
                $menu       = array('name' => $menu_name, 'icon' => $menu_icon, 'query' => $menu_items);

                if ($post_menus) {
                    ?>
                    <div class="panel panel-primary fed_dashboard_item active">
                        <div class="panel-heading">
                            <h3 class="panel-title">
                                <span class="<?php echo $menu_icon; ?>"></span>
                                <?php echo $menu_name ?>
                            </h3>
                        </div>
                        <div class="panel-body fed_dashboard_panel_body">
                            <?php
                            /**
                             * Add New post
                             */
                            if (isset($request['post_status']) && $request['post_status'] === 'add') {
                                $this->fed_cp_frontend_dashboard_add_new_post($request, $menu);
                            }
                            /**
                             * Edit Post by ID
                             */
                            if (isset($request['post_id']) && (int) $request['post_id'] !== 0) {
                                $this->fed_cp_frontend_dashboard_edit_post_by_id((int) $request['post_id'], $menu);
                            }
                            /**
                             * List Post
                             */
                            if ( ! isset($request['post_status']) && ! isset($request['post_id'])) {
                                $this->fed_display_dashboard_view_post_list($menu,
                                    $menu_items['menu_request']['menu_id']);
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                }
            }
        }

        /**
         * Show admin settings
         */
        public function fed_cp_show_admin_settings()
        {
            $cp_admin_settings = get_option('fed_cp_admin_settings');
            $tabs              = $this->fed_cp_admin_settings_menu_options($cp_admin_settings);

            if (count($tabs)) {
                ?>
                <div class="row">
                    <div class="col-md-3 padd_top_20">
                        <ul class="nav nav-pills nav-stacked"
                            id="fed_cp_admin_setting_tabs"
                            role="tablist">
                            <?php
                            $menu_count = 0;
                            foreach ($tabs as $index => $tab) {
                                $active = $menu_count === 0 ? 'active' : '';
                                $menu_count++;
                                ?>
                                <li role="presentation"
                                    class="<?php echo $active; ?>">
                                    <a href="#<?php echo $index; ?>"
                                       aria-controls="<?php echo $index; ?>"
                                       role="tab"
                                       data-toggle="tab">
                                        <i class="<?php echo $tab['icon']; ?>"></i>
                                        <?php echo $tab['name']; ?>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <div class="col-md-9">
                        <!-- Tab panes -->
                        <div class="tab-content">
                            <?php
                            $content_count = 0;
                            foreach ($tabs as $index => $tab) {
                                $active = $content_count === 0 ? 'active' : '';
                                $content_count++;
                                ?>
                                <div role="tabpanel"
                                     class="tab-pane <?php echo $active; ?>"
                                     id="<?php echo $index; ?>">
                                    <?php
                                    $this->fed_cp_admin_settings_tabs($index, $cp_admin_settings);
                                    ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php
            } else {
                ?>
                <div class="row">
                    <div class="col-md-12 padd_top_20">
                        <h5>Sorry! you don't have any public custom post type</h5>
                    </div>
                </div>
                <?php
            }
        }

        /**
         * Admin settings menu Options
         *
         * @param  array  $cp_admin_settings  Admin Settings.
         *
         * @return array
         */
        public function fed_cp_admin_settings_menu_options($cp_admin_settings)
        {
            $custom_post_type = fed_get_public_post_types();
            $post_array       = array();
            if ($custom_post_type) {
                foreach ($custom_post_type as $key => $post_type) {
                    $post_object = get_post_type_object($key);
                    $options     = isset($cp_admin_settings[$key]) ? $cp_admin_settings[$key] : array();

                    $post_name        = $this->getMenuNameByPostType($options, $post_object);
                    $post_icon        = $this->getMenuIconByPostType($options, $post_object);
                    $post_array[$key] = array(
                        'icon' => $post_icon,
                        'name' => $post_name,
//                            'callable'  => 'fed_cp_admin_settings_tab',
//                            'arguments' => $cp_admin_settings,
                    );

                }
            }

            return $post_array;
        }

        /**
         * Admin Settings Tab
         *
         * @param  array  $index  Index.
         * @param  array  $cp_admin_settings  Admin Settings.
         */
        public function fed_cp_admin_settings_tab($index, $cp_admin_settings)
        {
            //var_dump( $this->fed_cp_admin_settings_tab_content( $index, $cp_admin_settings ) );
            $post_status      = fed_get_post_status();
            $custom_post_type = fed_get_public_post_types();
            $all_roles        = fed_get_user_roles();

            $post_permission = isset($cp_admin_settings[$index]['permissions']['post_permission']) ? array_keys($cp_admin_settings[$index]['permissions']['post_permission']) : array();
            $menu            = isset($cp_admin_settings[$index]['menu']['rename_post']) ? $cp_admin_settings[$index]['menu']['rename_post'] : $index;

            ?>
            <form method="post"
                  class="fed_admin_menu fed_ajax"
                  action="<?php echo admin_url('admin-ajax.php?action=fed_cp_admin_settings') ?>">

                <?php wp_nonce_field('fed_nonce', 'fed_nonce') ?>

                <?php echo fed_loader(); ?>

                <input type="hidden"
                       name="custom_post_type"
                       value="<?php echo $index; ?>"/>

                <div class="fed_admin_panel_container">
                    <p>Note: Custom post "<?php echo $menu; ?>" settings availability are based on how it designed</p>
                    <div class="fed_admin_panel_content_wrapper">
                        <div class="custom_post_settings">
                            <div class="row">
                                <div class="col-md-12">
                                    <h4><?php __('Settings', 'frontend-dashboard-custom-post'); ?></h4>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 fed_menu_title">New Post Status</div>
                                <div class="col-md-4">
                                    <div class="col-md-6">
                                        <?php echo fed_input_box('fed_post_status', array(
                                            'name'    => 'fed_post_status',
                                            'value'   => isset($cp_admin_settings[$index]['settings']['fed_post_status']) ? $cp_admin_settings[$index]['settings']['fed_post_status'] : '',
                                            'options' => $post_status,
                                        ), 'select'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="custom_post_dashboard">
                            <div class="row">
                                <div class="col-md-12">
                                    <h4>Dashboard Settings</h4>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <?php echo fed_input_box('post_content', array(
                                        'name'          => 'post_content',
                                        'value'         => isset($cp_admin_settings[$index]['dashboard']['post_content']) ? $cp_admin_settings[$index]['dashboard']['post_content'] : '',
                                        'default_value' => 'Enable',
                                        'label'         => __('Disable Content', 'frontend-dashboard-custom-post'),
                                    ), 'checkbox'); ?>
                                </div>

                                <div class="col-md-4">
                                    <?php echo fed_input_box('fed_post_dashboard_category', array(
                                        'name'          => 'fed_post_dashboard_category',
                                        'value'         => isset($cp_admin_settings[$index]['dashboard']['fed_post_dashboard_category']) ? $cp_admin_settings[$index]['dashboard']['fed_post_dashboard_category'] : '',
                                        'default_value' => 'Enable',
                                        'label'         => __('Disable Category', 'frontend-dashboard-custom-post'),
                                    ), 'checkbox'); ?>
                                </div>

                                <div class="col-md-4">
                                    <?php echo fed_input_box('fed_post_dashboard_tag', array(
                                        'name'          => 'fed_post_dashboard_tag',
                                        'value'         => isset($cp_admin_settings[$index]['dashboard']['fed_post_dashboard_tag']) ? $cp_admin_settings[$index]['dashboard']['fed_post_dashboard_tag'] : '',
                                        'default_value' => 'Enable',
                                        'label'         => __('Disable Tag', 'frontend-dashboard-custom-post'),
                                    ), 'checkbox'); ?>
                                </div>


                                <div class="col-md-4">

                                    <?php echo fed_input_box('featured_image', array(
                                        'name'          => 'featured_image',
                                        'value'         => isset($cp_admin_settings[$index]['dashboard']['featured_image']) ? $cp_admin_settings[$index]['dashboard']['featured_image'] : '',
                                        'default_value' => 'Enable',
                                        'label'         => __('Disable Featured Image',
                                            'frontend-dashboard-custom-post'),
                                    ), 'checkbox'); ?>
                                </div>

                                <!--								<div class="col-md-4">-->
                                <!--									--><?php //echo fed_input_box( 'post_format', array(
                                //										'name'          => 'post_format',
                                //										'value'         => isset( $cp_admin_settings[ $index ]['dashboard']['post_format'] ) ? $cp_admin_settings[ $index ]['dashboard']['post_format'] : '',
                                //										'default_value' => 'Enable',
                                //										'label'         => __( 'Disable Post Format', 'frontend-dashboard-custom-post' )
                                //									), 'checkbox' );
                                ?>
                                <!--								</div>-->

                                <div class="col-md-4">

                                    <?php echo fed_input_box('allow_comments', array(
                                        'name'          => 'allow_comments',
                                        'value'         => isset($cp_admin_settings[$index]['dashboard']['allow_comments']) ? $cp_admin_settings[$index]['dashboard']['allow_comments'] : '',
                                        'default_value' => 'Enable',
                                        'label'         => __('Disable Allow Comments',
                                            'frontend-dashboard-custom-post'),
                                    ), 'checkbox'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="custom_post_menu">
                            <div class="row">
                                <div class="col-md-12">
                                    <h4>Menu</h4>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Post Menu Name</label>
                                    <?php echo fed_input_box('fed_post_menu_name', array(
                                        'name'        => 'rename_post',
                                        'placeholder' => __('Please enter new name for Post'),
                                        'value'       => isset($cp_admin_settings[$index]['menu']['rename_post']) ? $cp_admin_settings[$index]['menu']['rename_post'] : $custom_post_type[$index],
                                    ), 'single_line') ?>
                                </div>
                                <div class="col-md-4">
                                    <label>Post Menu Position</label>
                                    <?php echo fed_input_box('post_menu_position', array(
                                        'name'        => 'post_position',
                                        'value'       => isset($cp_admin_settings[$index]['menu']['post_position']) ? $cp_admin_settings[$index]['menu']['post_position'] : 2,
                                        'placeholder' => __('Post Menu Position'),
                                    ), 'number'); ?>

                                </div>
                                <div class="col-md-4">
                                    <label>Post Menu Icon</label>
                                    <?php echo fed_input_box('fed_payment_options[post_menu_icon]', array(
                                        'name'        => 'post_menu_icon',
                                        'placeholder' => __('Please Select Post Menu Icon'),
                                        'value'       => isset($cp_admin_settings[$index]['menu']['post_menu_icon']) ? $cp_admin_settings[$index]['menu']['post_menu_icon'] : 'fa fa-file-text',
                                        'class'       => 'post_menu_icon',
                                        'extra'       => 'data-toggle="modal" data-target=".fed_show_fa_list" placeholder="Menu Icon" data-fed_menu_box_id="post_menu_icon"',
                                    ), 'single_line') ?>
                                </div>

                            </div>
                        </div>
                        <div class="custom_post_permissions">
                            <div class="row">
                                <div class="col-md-12">
                                    <h4>Allow User Roles to Add/Edit/Delete Posts</h4>
                                </div>
                            </div>
                            <div class="row">
                                <?php
                                foreach ($all_roles as $key => $role) {
                                    $c_value = in_array($key, $post_permission, false) ? 'Enable' : 'Disable';
                                    ?>
                                    <div class="col-md-3">
                                        <?php echo fed_input_box('post_permission', array(
                                            'default_value' => 'Enable',
                                            'name'          => 'post_permission['.$key.']',
                                            'label'         => $role,
                                            'value'         => $c_value,
                                        ), 'checkbox'); ?>
                                    </div>
                                    <?php
                                } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <input type="submit" class="btn btn-primary" value="Submit"/>
                    </div>
                </div>
            </form>
            <?php
        }

        /**
         * Admin Settings Tabs
         *
         * @param  array  $index  Index
         * @param  array  $cp_admin_settings  Admin Settings
         */
        public function fed_cp_admin_settings_tabs($index, $cp_admin_settings)
        {
            $post_object = get_post_type_object($index);
            $options     = isset($cp_admin_settings[$index]) ? $cp_admin_settings[$index] : array();
            $menu        = $this->getMenuNameByPostType($options, $post_object);
            $menu_icons  = $this->getMenuIconByPostType($options, $post_object);
            $tabs        = $this->fed_cp_admin_settings_tab_content($index, $cp_admin_settings);
            $no          = mt_rand(1, 9999);

            ?>
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <span class="<?php echo $menu_icons; ?>"></span> <?php echo $menu; ?></h3>
                </div>
                <div class="panel-body">
                    <form method="post"
                          class="fed_admin_menu fed_ajax"
                          action="<?php echo admin_url('admin-ajax.php?action=fed_cp_admin_settings') ?>">

                        <?php wp_nonce_field('fed_nonce', 'fed_nonce') ?>

                        <?php echo fed_loader(); ?>

                        <input type="hidden"
                               name="custom_post_type"
                               value="<?php echo $index; ?>"/>

                        <div class="panel-group" id="accordion<?php echo $no; ?>" role="tablist"
                             aria-multiselectable="false">
                            <?php
                            $first = 0;
                            foreach ($tabs as $tab_index => $tab) {
                                $in        = '';
                                $collapsed = 'collapsed';
                                if ($first === 0) {
                                    $in        = 'in';
                                    $collapsed = '';
                                }
                                $first++;
                                ?>
                                <div class="panel panel-secondary-heading">
                                    <div class="panel-heading <?php echo $collapsed; ?>" role="tab"
                                         id="heading<?php echo $tab_index.$no; ?>" data-toggle="collapse"
                                         data-parent="#accordion<?php echo $no; ?>"
                                         href="#collapse<?php echo $tab_index.$no; ?>" aria-expanded="true"
                                         aria-controls="collapse<?php echo $tab_index.$no; ?>">
                                        <h4 class="panel-title">
                                            <a>
                                                <?php echo $tab['name']; ?>
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="collapse<?php echo $tab_index.$no; ?>"
                                         class="panel-collapse collapse <?php echo $in; ?>"
                                         role="tabpanel"
                                         aria-labelledby="heading<?php echo $tab_index.$no; ?>">
                                        <div class="panel-body">
                                            <?php if (isset($tab['note'])) { ?>
                                                <div class="row p-b-20">
                                                    <div class="col-md-12">
                                                        <strong><?php echo isset($tab['note']) ? $tab['note'] : ''; ?>
                                                        </strong>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                            <?php foreach ($tab['input'] as $post_type) { ?>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <?php if (isset($post_type['heading'])) {
                                                            ?>
                                                            <div class="bg-primary p-10">
                                                                <?php echo isset($post_type['heading']) ? $post_type['heading'] : '' ?>
                                                            </div>
                                                            <?php
                                                        } ?>
                                                        <?php if (isset($post_type['name']) && $post_type['name'] !== null) { ?>
                                                            <label>
                                                                <?php echo isset($post_type['required']) ? '<span class="bg-red-font">'.$post_type['name'].'</span>' : $post_type['name']; ?>
                                                                <?php echo isset($post_type['help_message']) ? $post_type['help_message'] : '' ?>
                                                            </label>
                                                        <?php } ?>
                                                        <?php
                                                        if (isset($post_type['input'])) {
                                                            echo fed_get_input_details($post_type['input']);
                                                        }
                                                        if (isset($post_type['extra'])) {
                                                            echo '<br>';
                                                            foreach ($post_type['extra'] as $extra) {
                                                                ?>
                                                                <div class="col-md-6">
                                                                    <?php
                                                                    if (isset($extra['label_title'])) {
                                                                        echo $extra['label_title'];
                                                                    }
                                                                    echo fed_get_input_details($extra);
                                                                    ?>
                                                                </div>
                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <input type="submit" class="btn btn-primary" value="Submit"/>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php
        }

        /**
         * Admin Settings Save
         */
        public function fed_cp_admin_settings()
        {
            $request   = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $post_type = $request['custom_post_type'];
            if (fed_check_post_type($post_type)) {

                if (empty($request['rename_post']) || empty($request['post_position']) || empty($request['post_menu_icon'])) {
                    wp_send_json_error(array(
                        'message' => __('Please enter all menu fields', 'frontend-dashboard-custom-post'),
                    ));
                }
                $fed_admin_settings_custom_post = get_option('fed_cp_admin_settings');

                $fed_admin_settings_custom_post[$post_type] = array(
                    'settings'    => array(
                        'fed_post_status'      => isset($request['fed_post_status']) ? sanitize_text_field($request['fed_post_status']) : 'publish',
                        'disable_post_edit'    => isset($request['disable_post_edit']) ? sanitize_text_field($request['disable_post_edit']) : 'no',
                        'disable_post_add_new' => isset($request['disable_post_add_new']) ? sanitize_text_field($request['disable_post_add_new']) : 'no',
                        'disable_post_delete'  => isset($request['disable_post_delete']) ? sanitize_text_field($request['disable_post_delete']) : 'no',
                        'disable_post_view'    => isset($request['disable_post_view']) ? sanitize_text_field($request['disable_post_view']) : 'no',
                    ),
                    'permissions' => array('post_permission' => isset($request['post_permission']) ? $request['post_permission'] : array()),
                    'menu'        => array(
                        'rename_post'    => isset($request['rename_post']) ? sanitize_text_field($request['rename_post']) : 'Post',
                        'post_position'  => isset($request['post_position']) ? sanitize_text_field($request['post_position']) : 2,
                        'post_menu_icon' => isset($request['post_menu_icon']) ? sanitize_text_field($request['post_menu_icon']) : 'fa fa-file-text',
                    ),
                    'dashboard'   => isset($request['dashboard']) ? $request['dashboard'] : array(),
                    'taxonomies'  => isset($request['taxonomies']) ? $request['taxonomies'] : array(),
                );

                update_option('fed_cp_admin_settings',
                    apply_filters('fed_cp_admin_settings_save', $fed_admin_settings_custom_post, $post_type));

                wp_send_json_success(array(
                    'message' => __('Custom Post Updated Successfully ', 'frontend-dashboard-custom-post'),
                ));
            }
            wp_send_json_error(array(
                'message' => __('Post Type Does not Exist', 'frontend-dashboard-custom-post'),
            ));
        }

        /**
         * Admin settings Tab Content
         *
         * @param  array  $index  Index
         * @param  array  $request  Request
         *
         * @return array
         */
        public function fed_cp_admin_settings_tab_content($index, $request)
        {
            $post_status      = fed_get_post_status();
            $user_roles       = fed_get_user_roles();
            $post_permissions = isset($request[$index]['permissions']['post_permission']) ? array_keys($request[$index]['permissions']['post_permission']) : array();
            $options          = isset($request[$index]) ? $request[$index] : array();
            $post_type        = get_post_type_object($index);
            $menu_title       = $this->getMenuNameByPostType($options, $post_type);
            $post_permission  = array();

            foreach ($user_roles as $key => $role) {
                $c_value               = in_array($key, $post_permissions, false) ? 'Enable' : 'Disable';
                $post_permission[$key] = array(
                    'name'  => null,
                    'input' => array(
                        'input_type'    => 'checkbox',
                        'user_value'    => $c_value,
                        'input_meta'    => 'post_permission['.$key.']',
                        'label'         => __($role, 'frontend-dashboard-custom-post'),
                        'default_value' => 'Enable',
                    ),
                );
            }
            $content = array(
                'menu'               => array(
                    'name'  => 'Menu',
                    'input' => array(
                        'rename_post'    => array(
                            'name'  => __('Post Menu Name', 'frontend-dashboard-custom-post'),
                            'input' => array(
                                'placeholder' => __('Post Menu Name',
                                    'frontend-dashboard-custom-post'),
                                'input_type'  => 'single_line',
                                'user_value'  => isset($request[$index]['menu']['rename_post']) ? $request[$index]['menu']['rename_post'] : '',
                                'input_meta'  => 'rename_post',
                            ),
                        ),
                        'post_position'  => array(
                            'name'  => __('Post Menu Position', 'frontend-dashboard-custom-post'),
                            'input' => array(
                                'placeholder' => __('Post Menu Position',
                                    'frontend-dashboard-custom-post'),
                                'input_type'  => 'number',
                                'user_value'  => isset($request[$index]['menu']['post_position']) ? $request[$index]['menu']['post_position'] : '',
                                'input_meta'  => 'post_position',
                            ),
                        ),
                        'post_menu_icon' => array(
                            'name'  => __('Menu Icon', 'frontend-dashboard-custom-post'),
                            'input' => array(
                                'placeholder' => __('Menu Icon', 'frontend-dashboard-custom-post'),
                                'input_type'  => 'single_line',
                                'user_value'  => isset($request[$index]['menu']['post_menu_icon']) ? $request[$index]['menu']['post_menu_icon'] : '',
                                'input_meta'  => 'post_menu_icon',
                                'class_name'  => 'fed_cp_menu_icon post_menu_icon',
                                'extra'       => 'data-fed_menu_box_id="post_menu_icon" data-toggle="modal" data-target=".fed_show_fa_list"',
                            ),
                        ),
                    ),
                ),
                'settings'           => array(
                    'name'  => 'Settings',
                    'input' => array(
                        'fed_post_status'      => array(
                            'name'  => __('New Post Status', 'frontend-dashboard-custom-post'),
                            'input' => array(
                                'input_type'  => 'select',
                                'user_value'  => isset($request[$index]['settings']['fed_post_status']) ? $request[$index]['settings']['fed_post_status'] : '',
                                'input_meta'  => 'fed_post_status',
                                'input_value' => $post_status,
                            ),
                        ),
                        'disable_post_add_new' => array(
                            'name'  => __('Disable Post Add New', 'frontend-dashboard-custom-post'),
                            'input' => array(
                                'input_type'  => 'select',
                                'user_value'  => isset($request[$index]['settings']['disable_post_add_new']) ? $request[$index]['settings']['disable_post_add_new'] : '',
                                'input_meta'  => 'disable_post_add_new',
                                'input_value' => fed_yes_no('ASC'),
                            ),
                        ),
                        'disable_post_edit'    => array(
                            'name'  => __('Disable Post Edit', 'frontend-dashboard-custom-post'),
                            'input' => array(
                                'input_type'  => 'select',
                                'user_value'  => isset($request[$index]['settings']['disable_post_edit']) ? $request[$index]['settings']['disable_post_edit'] : '',
                                'input_meta'  => 'disable_post_edit',
                                'input_value' => fed_yes_no('ASC'),
                            ),
                        ),
                        'disable_post_view'    => array(
                            'name'  => __('Disable Post View', 'frontend-dashboard-custom-post'),
                            'input' => array(
                                'input_type'  => 'select',
                                'user_value'  => isset($request[$index]['settings']['disable_post_view']) ? $request[$index]['settings']['disable_post_view'] : '',
                                'input_meta'  => 'disable_post_view',
                                'input_value' => fed_yes_no('ASC'),
                            ),
                        ),
                        'disable_post_delete'  => array(
                            'name'  => __('Disable Post Delete', 'frontend-dashboard-custom-post'),
                            'input' => array(
                                'input_type'  => 'select',
                                'user_value'  => isset($request[$index]['settings']['disable_post_delete']) ? $request[$index]['settings']['disable_post_delete'] : '',
                                'input_meta'  => 'disable_post_delete',
                                'input_value' => fed_yes_no('ASC'),
                            ),
                        ),
                    ),
                ),
                'dashboard_settings' => array(
                    'name'  => 'Dashboard Settings',
                    'input' => array(
                        'post_content'   => array(
                            'name'  => null,
                            'input' => array(
                                'input_type'    => 'checkbox',
                                'user_value'    => isset($request[$index]['dashboard']['post_content']) ? 'Enable' : '',
                                'input_meta'    => 'dashboard[post_content]',
                                'label'         => __('Disable Post Content',
                                    'frontend-dashboard-custom-post'),
                                'default_value' => 'Enable',
                            ),
                        ),
                        'featured_image' => array(
                            'name'  => null,
                            'input' => array(
                                'input_type'    => 'checkbox',
                                'user_value'    => isset($request[$index]['dashboard']['featured_image']) ? 'Enable' : '',
                                'input_meta'    => 'dashboard[featured_image]',
                                'label'         => __('Disable Feature Image',
                                    'frontend-dashboard-custom-post'),
                                'default_value' => 'Enable',
                            ),
                        ),
//						'post_format'    => array(
//							'name'  => null,
//							'input' => array(
//								'input_type'    => 'checkbox',
//								'user_value'    => isset( $request[ $index ]['dashboard']['post_format'] ) ? 'Enable' : '',
//								'input_meta'    => 'dashboard[post_format]',
//								'label'         => __( 'Disable Post Format', 'frontend-dashboard-custom-post' ),
//								'default_value' => 'Enable'
//							)
//						),
                        'allow_comments' => array(
                            'name'  => null,
                            'input' => array(
                                'input_type'    => 'checkbox',
                                'user_value'    => isset($request[$index]['dashboard']['allow_comments']) ? 'Enable' : '',
                                'input_meta'    => 'dashboard[allow_comments]',
                                'label'         => __('Disable Comments',
                                    'frontend-dashboard-custom-post'),
                                'default_value' => 'Enable',
                            ),
                        ),
                    ),
                ),
                'post_permission'    => array(
                    'name'  => 'Allow User Roles to Add/Edit/Delete '.$menu_title,
                    'input' => $post_permission
//					'input' => fed_cp_customize_post_for_user_role( $request, $index, $user_roles )
                ),
                'taxonomies'         => array(
                    'name'  => 'Taxonomies [Category/Tag]',
                    'input' => fed_cp_checkbox_taxonomies_with_users($request, $index),
                    'note'  => 'Select the respective role(s) to DISABLE the visibility of Taxonomy',
                ),
            );

            return apply_filters('fed_cp_admin_fed_settings', $content);

        }

        /**
         * Get Menu icon by Post Type
         *
         * @param  array  $options  Options
         * @param  object  $post_type  Post Type
         *
         * @return string|void
         */
        public function getMenuIconByPostType($options, $post_type)
        {
            /**
             * Check for Default Post Dashicons
             * else take from the Setting Dashboard
             */
            $menu_icon = 'fa fa-file-text';

            if (null !== $post_type && isset($post_type->menu_icon)) {
                $menu_icon = 'dashicons '.$post_type->menu_icon;
            }
            if (isset($options['menu']['post_menu_icon']) && $options['menu']['post_menu_icon'] != '') {
                $menu_icon = esc_attr($options['menu']['post_menu_icon']);
            }

            return $menu_icon;
        }

        /**
         * Get Menu Name by Post Type
         *
         * @param  array  $options  Options
         * @param  object  $post_type  Post Type
         *
         * @return string|void
         */
        public function getMenuNameByPostType($options, $post_type)
        {
            if (isset($options['menu']['rename_post']) && ! empty($options['menu']['rename_post'])) {
                return esc_attr($options['menu']['rename_post']);
            }

            if (null !== $post_type && isset($post_type->label)) {
                return $post_type->label;
            }

            return 'Post';
        }

        /**
         * Delete post by ID
         */
        public function fed_dashboard_delete_post_by_id_fn()
        {
            $post = $_REQUEST;

            if ( ! wp_verify_nonce($post['fed_dashboard_delete_post_by_id'], 'fed_dashboard_delete_post_by_id')) {
                wp_send_json_error(array(
                    'message' => array(
                        __('Invalid Request, Please reload the page and try again',
                            'frontend-dashboard-custom-post'),
                    ),
                ));

            }

            $post_type = get_post_type($post['post_id']);

            if ($post_type && fed_cp_is_user_can_delete_post($post_type)) {
                $status = wp_delete_post($post['post_id']);
                if ( ! $status) {
                    wp_send_json_error(array(
                        'message' => __('Something went wrong, please refresh and try again later',
                            'frontend-dashboard-custom-post'),
                    ));

                }
                wp_send_json_success(array(
                    'message' => __('Successfully Deleted', 'frontend-dashboard-custom-post'),
                ));
            }

            wp_send_json_error(array(
                'message' => __('You are not allowed to do this action', 'frontend-dashboard-custom-post'),
            ));
        }

        /**
         * Frontend Dashboard Add New Post
         *
         * @param  array  $request  Request
         * @param  array  $menu  Menu
         */
        private function fed_cp_frontend_dashboard_add_new_post($request, $menu)
        {
            $post_type     = isset($request['fed_post_type']) ? $request['fed_post_type'] : 'post';
            $post_table    = fed_fetch_rows_by_table(BC_FED_TABLE_POST);
            $post_settings = fed_get_post_settings_by_type($post_type);
            ?>
            <div class="row">
                <div class="col-md-5">
                    <a class="btn btn-primary" href="<?php echo esc_url(remove_query_arg('post_status')); ?>">
                        <i class="fa fa-mail-reply"></i>
                        <?php _e('Back to', 'frontend-dashboard-custom-post'); ?>
                        <?php echo $menu['name']; ?>
                    </a>
                </div>
            </div>
            <form method="post"
                  class="fed_dashboard_add_new_post"
                  action="<?php echo admin_url('admin-ajax.php?action=fed_dashboard_add_edit_post'); ?>">

                <?php wp_nonce_field('fed_nonce', 'fed_nonce') ?>

                <input type="hidden"
                       name="post_type"
                       value="<?php echo $post_type; ?>">

                <input type="hidden"
                       name="fed_post_type"
                       value="<?php echo $post_type; ?>">

                <div class="row fed_dashboard_item_field">
                    <div class="col-md-12">
                        <div class="fed_header_font_color"><?php _e('Title') ?></div>
                        <?php echo fed_get_input_details(array(
                            'placeholder' => 'Title',
                            'input_meta'  => 'post_title',
                            'input_type'  => 'single_line',
                        )); ?>
                    </div>

                </div>
                <?php
                if ( ! isset($post_settings['dashboard']['post_content']) && post_type_supports($post_type,
                        'editor')) {
                    ?>
                    <div class="row fed_dashboard_item_field">
                        <div class="col-md-12">
                            <div class="fed_header_font_color"><?php _e('Content') ?></div>
                            <?php wp_editor('', 'post_content', array('quicktags' => true)); ?>
                        </div>

                    </div>
                    <?php
                }

                $this->fed_show_category_tag_post_format($post_type, $post_settings);

                /**
                 * Featured Image
                 * _thumbnail_id
                 */

                if ( ! isset($post_settings['dashboard']['featured_image']) && post_type_supports($post_type,
                        'thumbnail')) {
                    ?>
                    <div class="row fed_dashboard_item_field">
                        <div class="col-md-12">
                            <div class="fed_header_font_color">
                                <?php _e('Featured Image', 'frontend-dashboard-custom-post') ?>
                            </div>
                            <?php echo fed_get_input_details(array(
                                'input_meta' => '_thumbnail_id',
                                'input_type' => 'file',
                            )) ?>
                        </div>
                    </div>
                    <?php
                }

                /**
                 * Comment Status
                 */
                if ( ! isset($post_settings['dashboard']['allow_comments']) && post_type_supports($post_type,
                        'comments')) {
                    ?>
                    <div class="row fed_dashboard_item_field">
                        <div class="col-md-12">
                            <div class="fed_header_font_color"><?php _e('Allow Comments',
                                    'frontend-dashboard-custom-post') ?></div>
                            <?php echo fed_get_input_details(array(
                                'input_meta'    => 'comment_status',
                                'input_type'    => 'checkbox',
                                'default_value' => 'open',
                                'user_value'    => 'open',
                            )); ?>
                        </div>
                    </div>
                    <?php
                }
                /**
                 * Extra Fields
                 */
                foreach ($post_table as $item) {
                    if ($post_type === $item['post_type']) {
                        ?>
                        <div class="row fed_dashboard_item_field">
                            <div class="col-md-12">
                                <div class="fed_header_font_color"><?php _e($item['label_name'],
                                        'frontend-dashboard-custom-post'); ?></div>
                                <?php echo fed_get_input_details($item); ?>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
                <div class="row fed_dashboard_item_field">
                    <div class="col-md-3 col-md-offset-4">
                        <button class="btn btn-primary"
                                type="submit">
                            <i class="fa fa-floppy-o"></i>
                            <?php _e('Save', 'frontend-dashboard-custom-post') ?>
                        </button>
                    </div>
                </div>
            </form>
            <?php
        }


        /**
         * Display Dashboard View Post List
         *
         * @param  string  $post_type  Post Type
         * @param  array  $menu  Menu
         */
        private function fed_display_dashboard_view_post_list($menu, $post_type = 'post')
        {
            $post  = fed_process_dashboard_display_post($post_type);
            $item  = isset($_GET, $_GET['menu_type'], $_GET['menu_id']) ? $_GET['menu_type'].'_'.$_GET['menu_id'] : '';
            $menus = fed_search_index_from_array_recursively($menu['query']['menu_items'], $item);

            ?>
            <?php if (fed_cp_is_user_can_add_post($post_type)) { ?>
            <div class="fed_dashboard_post_menu_container">
                <div class="fed_dashboard_post_menu_add_post">
                    <a class="btn btn-primary" href="<?php echo esc_url(add_query_arg(array(
                        'post_status'   => 'add',
                        'fed_post_type' => $post_type,
                    )), site_url()); ?>">
                        <i class="fa fa-plus"></i>
                        <?php _e('Add New',
                            'frontend-dashboard') ?>
                        <?php echo $menus['menu']; ?>
                    </a>
                </div>
            </div>
        <?php } ?>
            <div class="fed_dashboard_item_field_container">
                <?php foreach ($post->get_posts() as $single_post) { ?>
                    <div class="fed_dashboard_item_field_wrapper">
                        <div class="row fed_dashboard_item_field">
                            <div class="col-md-1 col-xs-3 col-sm-2"><?php echo (int) $single_post->ID; ?></div>
                            <div class="col-md-8 col-xs-9 col-sm-10">
                                <?php echo fed_get_post_status_symbol($single_post->post_status).' '.esc_attr($single_post->post_title); ?>
                            </div>
                            <div class="col-md-3 col-xs-12">
                                <div class="row pull-right">
                                    <?php if (fed_cp_is_user_can_view_post($post_type)) { ?>
                                        <div class="col-xs-4 col-sm-4">
                                            <a class="btn btn-warning" target="_blank"
                                               href="<?php echo get_permalink((int) $single_post->ID) ?>">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </div>
                                    <?php } ?>

                                    <?php if (fed_cp_is_user_can_edit_post($post_type)) { ?>
                                        <div class="col-xs-4 col-sm-4">
                                            <a class="btn btn-primary" href="<?php echo esc_url(add_query_arg(array(
                                                'post_id'       => (int) $single_post->ID,
                                                'fed_post_type' => $post_type,
                                            )), site_url()) ?>">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                        </div>
                                    <?php } ?>
                                    <?php if (fed_cp_is_user_can_delete_post($post_type)) { ?>
                                        <div class="col-xs-4 col-sm-4">
                                            <form method="post"
                                                  class="fed_dashboard_delete_post_by_id"
                                                  action="<?php echo admin_url('admin-ajax.php?action=fed_dashboard_delete_post_by_id'); ?>">
                                                <?php wp_nonce_field('fed_dashboard_delete_post_by_id',
                                                    'fed_dashboard_delete_post_by_id'); ?>
                                                <input type="hidden"
                                                       name="post_id"
                                                       value="<?php echo (int) $single_post->ID; ?>"/>
                                                <button class="btn btn-danger"
                                                        type="submit">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                fed_get_post_pagination($post, $menu);
                ?>
            </div>
            <?php
        }

        /**
         * Show Category Tag, Post Format
         *
         * @param  object  $post  Post
         * @param  array  $post_settings  Post Settings
         */
        private function fed_show_category_tag_post_format($post, $post_settings)
        {
            $post_type = is_object($post) ? $post->post_type : $post;
            $ctps      = fed_get_category_tag_post_format($post_type);
            $user_role = fed_get_current_user_role_key();

            foreach ($ctps as $index => $ctp) {
                if ($index === 'category') {
                    foreach ($ctp as $cindex => $category) {
                        if ( ! isset($post_settings['taxonomies'][$cindex][$user_role])) {
                            ?>
                            <div class="row fed_dashboard_item_field">
                                <div class="col-md-12">
                                    <div class="fed_header_font_color"><?php echo $category->label ?></div>
                                    <?php echo fed_get_dashboard_display_categories($post, $category); ?>
                                </div>
                            </div>
                            <?php
                        }
                    }
                }
                if ($index === 'tag') {
                    foreach ($ctp as $tindex => $tag) {
                        if ( ! isset($post_settings['taxonomies'][$tindex][$user_role])) {
                            ?>
                            <div class="row fed_dashboard_item_field">
                                <div class="col-md-12">
                                    <div class="fed_header_font_color">
                                        <?php echo $tag->label; ?>
                                        <?php do_action('fed_frontend_dashboard_edit_tag_label', $tag) ?>
                                    </div>
                                    <?php echo fed_get_dashboard_display_tags($post, $tag); ?>
                                </div>
                            </div>
                            <?php
                        }
                    }
                }
                if ($index === 'post_format') {
                    if ( ! isset($post_settings['taxonomies']['post_format'][$user_role])) {
                        $post_format = fed_dashboard_get_post_format();
                        if (is_array($post_format)) {
                            $post_format = array_combine($post_format, $post_format);
                            $post_value  = isset($post->ID) ? esc_attr(get_post_format($post->ID)) : 'standard';
                            ?>
                            <div class="row fed_dashboard_item_field">
                                <div class="col-md-12">
                                    <div class="fed_header_font_color"><?php _e('Post Format'); ?></div>
                                    <?php echo fed_input_box('tax_input[post_format][]', array(
                                        'options' => $post_format,
                                        'value'   => $post_value,
                                    ), 'radio'); ?>
                                </div>
                            </div>
                            <?php
                        }
                    }
                }
            }
        }

        /**
         * Frontend Dashboard Edit Post by ID
         *
         * @param  int  $post_id  Post ID
         * @param  array  $menu  Menu
         */
        private function fed_cp_frontend_dashboard_edit_post_by_id($post_id, $menu)
        {
            $user         = get_userdata(get_current_user_id());
            $post         = get_post((int) $post_id);
            $preview_link = get_preview_post_link($post->ID);

            if (fed_cp_is_user_can_edit_post($post->post_type) && $post !== null && $post->post_author == $user->ID) {
                $post_table    = fed_fetch_table_rows_by_key_value(BC_FED_TABLE_POST, 'post_type', $post->post_type);
                $post_meta     = get_post_meta($post->ID);
                $post_settings = fed_get_post_settings_by_type($post->post_type);
                uasort($post_table, 'fed_sort_by_order');
                ?>
                <div class="row">
                    <div class="col-md-6">
                        <a class="btn btn-primary"
                           href="<?php echo esc_url(remove_query_arg('post_id')); ?>">
                            <i class="fa fa-mail-reply"></i>
                            <?php _e('Back to', 'frontend-dashboard-custom-post'); ?>
                            <?php echo $menu['name']; ?>
                        </a>
                    </div>

                    <div class="col-md-6 text-right">
                        <?php if (fed_cp_is_user_can_add_post($post->post_type)) {
                            $add_url     = add_query_arg(array(
                                'post_status'   => 'add',
                                'fed_post_type' => $post->post_type,
                            ));
                            $new_add_url = remove_query_arg('post_id', $add_url);
                            ?>
                            <a class="btn btn-primary" href="<?php echo $new_add_url; ?>">
                                <i class="fa fa-plus" aria-hidden="true"></i>
                                <?php _e('Add New ', 'frontend-dashboard-custom-post') ?> <?php echo $menu['name'] ?>
                            </a>
                        <?php } ?>
                        <?php if ($preview_link && ! empty($preview_link)) { ?>
                            <span class="fed_p_l_20">
							<a target="_blank" class="btn btn-danger" href="<?php printf('%s',
                                $preview_link) ?>">
							<i class="fa fa-eye" aria-hidden="true"></i>
							<?php _e('View this ', 'frontend-dashboard-custom-post') ?>
                                <?php echo $menu['name']; ?>
						</a>
						</span>
                        <?php } ?>
                    </div>
                </div>

                <form method="post"
                      class="fed_dashboard_process_edit_post_request"
                      action="<?php echo admin_url('admin-ajax.php?action=fed_dashboard_add_edit_post'); ?>">

                    <?php wp_nonce_field('fed_nonce', 'fed_nonce') ?>

                    <?php echo fed_get_input_details(array(
                        'input_meta' => 'ID',
                        'user_value' => (int) $post->ID,
                        'input_type' => 'hidden',
                    )) ?>

                    <?php echo fed_get_input_details(array(
                        'input_meta' => 'fed_post_type',
                        'user_value' => $post->post_type,
                        'input_type' => 'hidden',
                    )) ?>

                    <?php echo fed_get_input_details(array(
                        'input_meta' => 'post_type',
                        'user_value' => $post->post_type,
                        'input_type' => 'hidden',
                    ))
                    /**
                     * Post Title
                     */
                    ?>
                    <div class="row fed_dashboard_item_field">
                        <div class="col-md-12">
                            <div class="fed_header_font_color"><?php _e('Title',
                                    'frontend-dashboard-custom-post'); ?></div>
                            <?php echo fed_input_box('post_title', array(
                                'value'       => esc_attr($post->post_title),
                                'placeholder' => 'Post Title',
                            ), 'single_line'); ?>
                        </div>
                    </div>
                    <?php
                    /**
                     * Post Content
                     */
                    if ( ! isset($post_settings['dashboard']['post_content'])) {
                        ?>
                        <div class="row fed_dashboard_item_field">
                            <div class="col-md-12">
                                <div class="fed_header_font_color"><?php _e('Content',
                                        'frontend-dashboard-custom-post'); ?></div>
                                <?php wp_editor($post->post_content, 'post_content', array(
                                    'quicktags' => true,
                                )) ?>
                            </div>

                        </div>
                        <?php

                    }
                    fed_show_category_tag_post_format($post, $post_settings);

                    /**
                     * Featured Image
                     * _thumbnail_id
                     */
                    if ( ! isset($post_settings['dashboard']['featured_image'])) {
                        $thumbnail = isset($post_meta['_thumbnail_id']) ? (int) $post_meta['_thumbnail_id'][0] : '';
                        ?>
                        <div class="row fed_dashboard_item_field">
                            <div class="col-md-12">
                                <div class="fed_header_font_color"><?php _e('Featured Image',
                                        'frontend-dashboard-custom-post') ?></div>
                                <?php echo fed_get_input_details(array(
                                    'input_meta' => '_thumbnail_id',
                                    'user_value' => $thumbnail,
                                    'input_type' => 'file',
                                )); ?>
                            </div>
                        </div>
                        <?php
                    }

                    /**
                     * Comment Status
                     */
                    if ( ! isset($post_settings['dashboard']['allow_comments'])) {
                        ?>
                        <div class="row fed_dashboard_item_field">
                            <div class="col-md-12">
                                <div class="fed_header_font_color"><?php _e('Allow Comments') ?></div>
                                <?php echo fed_input_box('comment_status', array(
                                    'default_value' => 'open',
                                    'value'         => esc_attr($post->comment_status),
                                ), 'checkbox'); ?>
                            </div>
                        </div>
                        <?php
                    }
                    /**
                     * Extra Fields
                     */
                    foreach ($post_table as $item) {
                        $temp               = $item;
                        $temp['user_value'] = isset($post_meta[$item['input_meta']][0]) ? $post_meta[$item['input_meta']][0] : '';
                        ?>
                        <div class="row fed_dashboard_item_field">
                            <div class="col-md-9">
                                <div class="fed_header_font_color"><?php _e($item['label_name']) ?></div>
                                <?php echo fed_get_input_details($temp); ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="row fed_dashboard_item_field">
                        <div class="col-md-3 col-md-offset-4">
                            <button class="btn btn-primary"
                                    type="submit">
                                <i class="fa fa-floppy-o"></i>
                                <?php _e('Save', 'frontend-dashboard') ?>
                            </button>
                        </div>
                    </div>
                </form>
                <?php
            } else {
                echo '<h2>Unauthorised Access</h2>';
            }

        }
    }

    new Fed_Cp_Menu();
}