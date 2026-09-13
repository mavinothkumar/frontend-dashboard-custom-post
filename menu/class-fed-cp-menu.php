<?php
/**
 * Custom Post Menu
 *
 * @package frontend-dashboard-custom-post
 */

if ( ! class_exists( 'Fed_Cp_Menu' ) ) {
	/**
	 * Class Fed_Cp_Menu
	 */
	class Fed_Cp_Menu {
		/**
		 * FEDCP_Menu constructor.
		 */
		public function __construct() {
			add_filter( 'fed_admin_dashboard_settings_menu_header', array(
				$this,
				'fed_cp_admin_dashboard_settings_menu_header',
			) );
			add_filter( 'fed_get_custom_post_settings_by_type', array(
				$this,
				'fed_cp_get_custom_post_settings_by_type',
			), 10, 2 );
			add_action( 'fed_default_admin_scripts_styles', array( $this, 'fed_cp_enqueue_script_style_admin' ) );
			add_action( 'fed_default_frontend_scripts_styles', array( $this, 'fed_cp_enqueue_script_style_admin' ) );
			add_action( 'wp_ajax_fed_cp_admin_settings', array( $this, 'fed_cp_admin_settings' ) );

			add_filter( 'fed_admin_script_loading_pages', array( $this, 'fed_cp_admin_script_loading_pages' ) );

			add_action( 'fed_frontend_main_menu', array( $this, 'fed_cp_frontend_main_menu' ) );
			add_action( 'fed_restrictive_menu_names', array( $this, 'fed_cp_restrictive_menu_names' ) );
			add_action( 'fed_frontend_dashboard_menu_container', array(
				$this,
				'fed_cp_frontend_dashboard_menu_container',
			), 10, 2 );

			add_action( 'wp_ajax_fed_dashboard_add_edit_post', array( $this, 'fed_dashboard_add_edit_post' ) );
			add_action( 'wp_ajax_fed_dashboard_delete_post_by_id', array(
				$this,
				'fed_dashboard_delete_post_by_id_fn',
			) );
		}

		/**
		 * Add Edit Post
		 */
		public function fed_dashboard_add_edit_post() {
			$post = $_REQUEST;
			fed_verify_nonce( $post );

			$fed_admin_options = fed_get_post_settings_by_type( $post['fed_post_type'] );
			$user_role         = fed_get_current_user_role();

			if ( isset( $post['ID'] ) && ! empty( $post['ID'] ) ) {
				$user_post = get_post( (int) $post['ID'] );
				if ( (int) get_current_user_id() === (int) $user_post->post_author || fed_is_admin() ) {
					$default['ID']          = (int) $user_post->ID;
					$default['post_author'] = (int) $user_post->post_author;
				} else {
					$error = new WP_Error( 'fed_dashboard_add_post_invalid_user_access',
						__( 'Invalid User Access', 'frontend-dashboard-custom-post' )
					);
					wp_send_json_error( array( 'message' => $error->get_error_messages() ) );
				}
			}

			if (
				count( array_intersect(
					$user_role,
					array_keys( $fed_admin_options['permissions']['post_permission'] )
				) ) > 0
			) {
				$extras               = fed_fetch_rows_by_table( BC_FED_TABLE_POST );
				$post_status_settings = isset( $fed_admin_options['settings']['fed_post_status'] ) ? sanitize_text_field( $fed_admin_options['settings']['fed_post_status'] ) : 'publish';

				if ( ! fed_is_admin() ) {
					if ( 'publish' === $post_status_settings ) {
						$post_status = isset( $post['post_status'] ) ? sanitize_text_field( $post['post_status'] ) : 'publish';
					}
					if ( 'pending' === $post_status_settings || 'draft' === $post_status_settings ) {
						$post_status = isset( $post['post_status'] ) && ( 'pending' === $post['post_status'] || $post['draft'] ) ?
							sanitize_text_field( $post['post_status'] ) : 'draft';
					}
				}

				if ( fed_is_admin() ) {
					$post_status = fed_get_data( 'post_status', $post, $post_status_settings );
				}

				if ( empty( $post['post_title'] ) ) {
					$error = new WP_Error( 'fed_dashboard_add_post_title_missing',
						__( 'Please fill post title', 'frontend-dashboard-custom-post' )
					);
					wp_send_json_error( array( 'message' => $error->get_error_messages() ) );
				}


				$default['post_title']     = sanitize_text_field( $post['post_title'] );
				$default['post_content']   = isset( $post['post_content'] ) ? wp_kses_post( $post['post_content'] ) : '';
				$default['post_category']  = isset( $post['post_category'] ) ? sanitize_text_field( $post['post_category'] ) : '';
				$default['tags_input']     = isset( $post['tags_input'] ) ? implode( ',', $post['tags_input'] ) : '';
				$default['post_type']      = isset( $post['post_type'] ) ? sanitize_text_field( $post['post_type'] ) : 'post';
				$default['comment_status'] = isset( $post['comment_status'] ) ? sanitize_text_field( $post['comment_status'] ) : 'open';
				$default['post_status']    = fed_sanitize_text_field( $post_status );

				if ( isset( $post['_thumbnail_id'] ) ) {
					$default['_thumbnail_id'] = ( '' == $post['_thumbnail_id'] ) ? - 1 : (int) $post['_thumbnail_id'];
				}

				if ( isset( $post['tax_input'] ) ) {
					$default['tax_input'] = $post['tax_input'];
				}

				foreach ( $extras as $index => $extra ) {
					if ( isset( $extra['input_type'] ) && 'wp_editor' === $extra['input_type'] ) {
						$default['meta_input'][ $extra['input_meta'] ] = isset( $post[ $extra['input_meta'] ] ) ? wp_kses_post( $post[ $extra['input_meta'] ] ) : '';
					} elseif ( isset( $extra['input_type'] ) && 'multi_line' === $extra['input_type'] ) {
						$default['meta_input'][ $extra['input_meta'] ] = isset( $post[ $extra['input_meta'] ] ) ? wp_kses( $post[ $extra['input_meta'] ],
							array()
						) : '';
					} else {
						$default['meta_input'][ $extra['input_meta'] ] = isset( $post[ $extra['input_meta'] ] ) ? fed_sanitize_text_field( $post[ $extra['input_meta'] ] ) : '';
					}
				}

				$post_id = wp_insert_post( $default );

				if ( $post_id instanceof WP_Error ) {
					wp_send_json_error( $post_id->get_error_messages() );
				}

				wp_send_json_success( array(
					'message' => $post['post_title'] . __( ' Successfully Saved', 'frontend-dashboard-custom-post' ),
					'id'      => $post_id,
				) );
			}
			$error = new WP_Error( 'fed_action_not_allowed',
				__( 'Sorry! your are not allowed to do this action', 'frontend-dashboard-custom-post' )
			);

			wp_send_json_error( array( 'message' => $error->get_error_messages() ) );
		}

		/**
		 * Enqueue Script and Style in Admin
		 *
		 * @param  array $scripts  Scripts.
		 *
		 * @return mixed
		 */
		public function fed_cp_enqueue_script_style_admin( $scripts ) {
			$scripts['scripts']['fed_cp_script'] = array(
				'wp_core'      => false,
				'name'         => 'Custom Post',
				'plugin_name'  => 'Frontend Dashboard Custom Post',
				'src'          => plugins_url( '/assets/fed_cp_script.js', FED_CP_PLUGIN ),
				'dependencies' => array(),
				'version'      => false,
				'in_footer'    => true,
			);

			$scripts['styles']['fed_cp_style'] = array(
				'wp_core'      => false,
				'name'         => 'Custom Post',
				'plugin_name'  => 'Frontend Dashboard Custom Post',
				'src'          => plugins_url( '/assets/fed_cp_style.css', FED_CP_PLUGIN ),
				'dependencies' => array(),
				'version'      => false,
				'media'        => false,
			);

			return $scripts;
		}

		/**
		 * Action Hook to Admin Script Loading Pages
		 *
		 * @param  array $array  array.
		 *
		 * @return array
		 */
		public function fed_cp_admin_script_loading_pages( $array ) {
			$array[] = 'fed_custom_post';
			$array[] = 'fed_taxonomies';

			return $array;
		}

		/**
		 * Restrictive Menu Names
		 *
		 * @param  array $slug  Slug.
		 *
		 * @return array
		 */
		public function fed_cp_restrictive_menu_names( $slug ) {
			$post_type = array_keys( fed_get_public_post_types() );

			return array_merge( $slug, $post_type );
		}

		/**
		 * Custom Post Settings By Type
		 *
		 * @param  array  $array  Array.
		 * @param  string $post_type  Post Type.
		 *
		 * @return mixed
		 */
		public function fed_cp_get_custom_post_settings_by_type( $array, $post_type ) {
			$custom_post_settings = get_option( 'fed_cp_admin_settings' );

			return isset( $custom_post_settings[ $post_type ] ) ? $custom_post_settings[ $post_type ] : $array;

		}

		/**
		 * Frontend Main Menu
		 *
		 * @param  array $menus  Menus.
		 *
		 * @return array
		 */
		public function fed_cp_frontend_main_menu( $menus ) {
			$get_default_post_items    = fed_get_public_post_types();
			$admin_custom_post_options = get_option( 'fed_cp_admin_settings' );
			$default                   = array();
			$user                      = get_userdata( get_current_user_id() );
			if ( $admin_custom_post_options && $user ) {
				foreach ( $admin_custom_post_options as $key => $options ) {
					if ( in_array( $key, array_keys( $get_default_post_items ) ) ) {
						$post_type     = get_post_type_object( $key );
						$menu_position = ( isset( $options['menu']['post_position'] ) && '' != $options['menu']['post_position'] ) ? (int) $options['menu']['post_position'] : 99;

						$menu_name = $this->getMenuNameByPostType( $options, $post_type );

						$menu_icon = $this->getMenuIconByPostType( $options, $post_type );

						if (
							isset( $options['permissions']['post_permission'] ) &&
							count( array_intersect( $user->roles,
								array_keys( $options['permissions']['post_permission'] ) ) ) > 0
						) {
							$default[ $key ] = array(
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

			return array_merge( $menus, $default );

		}

		/**
		 * Admin Dashboard Settings Menu Header
		 *
		 * @param  array $menu  Menu.
		 *
		 * @return array
		 */
		public function fed_cp_admin_dashboard_settings_menu_header( $menu ) {
			return array_merge( $menu, array(
				'custom_post' => array(
					'icon_class' => 'fa fa-envelope-open',
					'name'       => 'Post/Custom Post',
					'callable'   => array(
						'object' => $this,
						'method' => 'fed_cp_show_admin_settings',
					),
				),
			) );
		}

		/**
		 * Menu Container
		 *
		 * @param  array $request  Request.
		 * @param  array $menu_items  Menu items.
		 */
		public function fed_cp_frontend_dashboard_menu_container( $request, $menu_items ) {
			if ( 'post' === $menu_items['menu_request']['menu_type'] ) {
				$post_menus = get_option( 'fed_cp_admin_settings' );
				$post_type  = get_post_type_object( $menu_items['menu_request']['menu_slug'] );
				$menu_id    = isset( $menu_items['menu_request']['menu_id'] ) ? $menu_items['menu_request']['menu_id'] : '';
				$menu_conf  = isset( $post_menus[ $menu_id ] ) ? $post_menus[ $menu_id ] : array();
				$menu_name  = $this->getMenuNameByPostType( $menu_conf, $post_type );
				$menu_icon  = $this->getMenuIconByPostType( $menu_conf, $post_type );
				$menu       = array(
					'name'  => $menu_name,
					'icon'  => $menu_icon,
					'query' => $menu_items,
				);
				if ( $post_menus ) {
					?>
					<div class="fed_dashboard_item active">
						<div class="flex items-center justify-between pb-5 mb-6 border-b border-slate-100">
							<div class="flex items-center gap-3">
								<div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
									<span class="<?php echo esc_attr( $menu_icon ); ?>"></span>
								</div>
								<div>
									<h2 class="text-xl font-bold text-slate-900 tracking-tight mb-0">
										<?php echo esc_html( $menu_name ); ?>
									</h2>
									<p class="text-xs text-slate-500 mb-0">
										<?php echo esc_html( sprintf( __( 'Manage your %s items', 'frontend-dashboard-custom-post' ), strtolower( $menu_name ) ) ); ?>
									</p>
								</div>
							</div>
							<?php if ( ! isset( $request['post_status'] ) && ! isset( $request['post_id'] ) && fed_cp_is_user_can_add_post( $menu_items['menu_request']['menu_id'] ) ) { ?>
								<a class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-xs transition-all duration-150 no-underline cursor-pointer" href="<?php echo esc_url( add_query_arg( array( 'post_status' => 'add', 'fed_post_type' => $menu_items['menu_request']['menu_id'] ) ) ); ?>">
									<i class="fa fa-plus text-xs" style="color: #ffffff !important;"></i>
									<span style="color: #ffffff !important;"><?php esc_html_e( 'Add New', 'frontend-dashboard' ); ?></span>
								</a>
							<?php } ?>
						</div>
						<div class="fed_dashboard_panel_body">
							<?php
							do_action( 'fed_dashboard_panel_inside_top' );
							do_action( 'fed_dashboard_panel_inside_top_' . fed_get_data( 'menu_request.menu_slug',
									$menu_items ) );
							/**
							 * Add New post
							 */
							if ( isset( $request['post_status'] ) && 'add' === $request['post_status'] ) {
								$this->fed_cp_frontend_dashboard_add_new_post( $request, $menu );
							}
							/**
							 * Edit Post by ID
							 */
							if ( isset( $request['post_id'] ) && 0 !== (int) $request['post_id'] ) {
								$this->fed_cp_frontend_dashboard_edit_post_by_id( (int) $request['post_id'], $menu );
							}
							/**
							 * List Post
							 */
							if ( ! isset( $request['post_status'] ) && ! isset( $request['post_id'] ) ) {
								$this->fed_display_dashboard_view_post_list( $menu,
									$menu_items['menu_request']['menu_id'] );
							}
							do_action( 'fed_dashboard_panel_inside_bottom' );
							do_action( 'fed_dashboard_panel_inside_bottom_' . fed_get_data( 'menu_request.menu_slug',
									$menu_items ) );
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
		public function fed_cp_show_admin_settings() {
			$cp_admin_settings = get_option( 'fed_cp_admin_settings' );
			$tabs              = $this->fed_cp_admin_settings_menu_options( $cp_admin_settings );
			$no                = mt_rand( 1000, 9999 );

			if ( count( $tabs ) ) {
				?>
				<div class="flex flex-col lg:flex-row gap-6 items-start w-full fed-settings-subtab-container" id="fed_subtabs_wrap_<?php echo esc_attr( $no ); ?>">
					<!-- Left Subtab Sidebar -->
					<div class="w-full lg:w-64 shrink-0">
						<div class="bg-white rounded-3xl p-3 border border-slate-200/80 shadow-xs space-y-1.5" role="tablist">
							<?php
							$menu_count = 0;
							foreach ( $tabs as $index => $tab ) {
								$active = ( 0 === $menu_count );
								$menu_count ++;
								?>
								<a href="#<?php echo esc_attr( $index ); ?>"
								   data-target="#subtab_pane_<?php echo esc_attr( $index . '_' . $no ); ?>"
								   role="tab"
								   data-toggle="tab"
								   class="fed-subtab-link flex items-center gap-3 px-3.5 py-3 rounded-2xl text-xs font-semibold transition-all cursor-pointer no-underline <?php echo $active ? 'fed-subtab-active bg-indigo-50 border border-indigo-200 text-indigo-700 shadow-2xs font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 border border-transparent'; ?>">
									<div class="w-8 h-8 rounded-xl flex items-center justify-center text-sm shrink-0 <?php echo $active ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-500'; ?>">
										<i class="<?php echo esc_attr( $tab['icon'] ); ?>"></i>
									</div>
									<span class="truncate"><?php echo esc_html( $tab['name'] ); ?></span>
								</a>
							<?php } ?>
						</div>
					</div>

					<!-- Right Subtab Content Pane -->
					<div class="flex-1 min-w-0 w-full">
						<div class="tab-content w-full">
							<?php
							$content_count = 0;
							foreach ( $tabs as $index => $tab ) {
								$active = ( 0 === $content_count );
								$content_count ++;
								?>
								<div role="tabpanel"
									 class="tab-pane <?php echo $active ? 'active block' : 'hidden'; ?>"
									 id="subtab_pane_<?php echo esc_attr( $index . '_' . $no ); ?>"
									 data-pane="<?php echo esc_attr( $index ); ?>">
									<?php $this->fed_cp_admin_settings_tabs( $index, $cp_admin_settings ); ?>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
				<?php
			} else {
				?>
				<div class="bg-white rounded-3xl p-12 text-center border border-slate-200/80 shadow-xs">
					<i class="fas fa-cubes text-3xl text-slate-300 mb-3"></i>
					<h3 class="text-sm font-bold text-slate-800 m-0"><?php esc_html_e( 'No Public Custom Post Types Found', 'frontend-dashboard-custom-post' ); ?></h3>
					<p class="text-xs text-slate-500 mt-1"><?php esc_html_e( 'Please create a public custom post type first.', 'frontend-dashboard-custom-post' ); ?></p>
				</div>
				<?php
			}
		}

		/**
		 * Admin settings menu Options
		 *
		 * @param  array $cp_admin_settings  Admin Settings.
		 *
		 * @return array
		 */
		public function fed_cp_admin_settings_menu_options( $cp_admin_settings ) {
			$custom_post_type = fed_get_public_post_types();
			$post_array       = array();
			if ( $custom_post_type ) {
				foreach ( $custom_post_type as $key => $post_type ) {
					$post_object = get_post_type_object( $key );
					$options     = isset( $cp_admin_settings[ $key ] ) ? $cp_admin_settings[ $key ] : array();

					$post_name          = $this->getMenuNameByPostType( $options, $post_object );
					$post_icon          = $this->getMenuIconByPostType( $options, $post_object );
					$post_array[ $key ] = array(
						'icon' => $post_icon,
						'name' => $post_name,
					);

				}
			}

			return $post_array;
		}

		/**
		 * Admin Settings Tab
		 *
		 * @param  array $index  Index.
		 * @param  array $cp_admin_settings  Admin Settings.
		 */
		public function fed_cp_admin_settings_tab( $index, $cp_admin_settings ) {
			$post_status      = fed_get_post_status();
			$custom_post_type = fed_get_public_post_types();
			$all_roles        = fed_get_user_roles();

			$post_permission = isset( $cp_admin_settings[ $index ]['permissions']['post_permission'] ) ? array_keys( $cp_admin_settings[ $index ]['permissions']['post_permission'] ) : array();
			$menu            = isset( $cp_admin_settings[ $index ]['menu']['rename_post'] ) ? $cp_admin_settings[ $index ]['menu']['rename_post'] : $index;

			?>
			<form method="post"
					class="fed_admin_menu fed_ajax"
					action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=fed_cp_admin_settings' ) ); ?>">

				<?php wp_nonce_field( 'fed_nonce', 'fed_nonce' ); ?>

				<?php
				// phpcs:ignore
				echo fed_loader();
				?>

				<input type="hidden"
						name="custom_post_type"
						value="<?php echo esc_attr( $index ); ?>"/>

				<div class="fed_admin_panel_container">
					<p>Note: Custom post "<?php echo esc_attr( $menu ); ?>" settings availability are based on how it designed</p>
					<div class="fed_admin_panel_content_wrapper">
						<div class="custom_post_settings">
							<div class="row">
								<div class="col-md-12">
									<h4><?php __( 'Settings', 'frontend-dashboard-custom-post' ); ?></h4>
								</div>
							</div>
							<div class="row">
								<div class="col-md-3 fed_menu_title">New Post Status</div>
								<div class="col-md-4">
									<div class="col-md-6">
										<?php
										// phpcs:ignore
										echo fed_input_box( 'fed_post_status', array(
											'name'    => 'fed_post_status',
											'value'   => isset( $cp_admin_settings[ $index ]['settings']['fed_post_status'] ) ? $cp_admin_settings[ $index ]['settings']['fed_post_status'] : '',
											'options' => $post_status,
										), 'select' );
										?>
									</div>
								</div>
							</div>
						</div>
						<div class="custom_post_dashboard">
							<div class="row">
								<div class="col-md-12">
									<h4><?php esc_attr_e( 'Dashboard Settings',
											'frontend-dashboard-custom-post' ); ?></h4>
								</div>
							</div>
							<div class="row">
								<div class="col-md-4">
									<?php
									// phpcs:ignore
									echo fed_input_box( 'post_content', array(
										'name'          => 'post_content',
										'value'         => isset( $cp_admin_settings[ $index ]['dashboard']['post_content'] ) ? $cp_admin_settings[ $index ]['dashboard']['post_content'] : '',
										'default_value' => 'Enable',
										'label'         => __( 'Disable Content', 'frontend-dashboard-custom-post' ),
									), 'checkbox' );
									?>
								</div>

								<div class="col-md-4">
									<?php
									// phpcs:ignore
									echo fed_input_box( 'fed_post_dashboard_category', array(
										'name'          => 'fed_post_dashboard_category',
										'value'         => isset( $cp_admin_settings[ $index ]['dashboard']['fed_post_dashboard_category'] ) ? $cp_admin_settings[ $index ]['dashboard']['fed_post_dashboard_category'] : '',
										'default_value' => 'Enable',
										'label'         => __( 'Disable Category', 'frontend-dashboard-custom-post' ),
									), 'checkbox' );
									?>
								</div>

								<div class="col-md-4">
									<?php
									// phpcs:ignore
									echo fed_input_box( 'fed_post_dashboard_tag', array(
										'name'          => 'fed_post_dashboard_tag',
										'value'         => isset( $cp_admin_settings[ $index ]['dashboard']['fed_post_dashboard_tag'] ) ? $cp_admin_settings[ $index ]['dashboard']['fed_post_dashboard_tag'] : '',
										'default_value' => 'Enable',
										'label'         => __( 'Disable Tag', 'frontend-dashboard-custom-post' ),
									), 'checkbox' );
									?>
								</div>

								<div class="col-md-4">
									<?php
									// phpcs:ignore
									echo fed_input_box( 'featured_image', array(
										'name'          => 'featured_image',
										'value'         => isset( $cp_admin_settings[ $index ]['dashboard']['featured_image'] ) ? $cp_admin_settings[ $index ]['dashboard']['featured_image'] : '',
										'default_value' => 'Enable',
										'label'         => __( 'Disable Featured Image',
											'frontend-dashboard-custom-post' ),
									), 'checkbox' );
									?>
								</div>

								<div class="col-md-4">
									<?php
									// phpcs:ignore
									echo fed_input_box( 'allow_comments', array(
										'name'          => 'allow_comments',
										'value'         => isset( $cp_admin_settings[ $index ]['dashboard']['allow_comments'] ) ? $cp_admin_settings[ $index ]['dashboard']['allow_comments'] : '',
										'default_value' => 'Enable',
										'label'         => __( 'Disable Allow Comments',
											'frontend-dashboard-custom-post' ),
									), 'checkbox' );
									?>
								</div>
							</div>
						</div>
						<div class="custom_post_menu">
							<div class="row">
								<div class="col-md-12">
									<h4><?php esc_attr_e( 'Menu', 'frontend-dashboard-custom-post' ); ?></h4>
								</div>
							</div>
							<div class="row">
								<div class="col-md-4">
									<label><?php esc_attr_e( 'Post Menu Name',
											'frontend-dashboard-custom-post' ) ?></label>
									<?php
									// phpcs:ignore
									echo fed_input_box( 'fed_post_menu_name', array(
										'name'        => 'rename_post',
										'placeholder' => __( 'Please enter new name for Post' ),
										'value'       => isset( $cp_admin_settings[ $index ]['menu']['rename_post'] ) ? $cp_admin_settings[ $index ]['menu']['rename_post'] : $custom_post_type[ $index ],
									), 'single_line' );
									?>
								</div>
								<div class="col-md-4">
									<label><?php esc_attr_e( 'Post Menu Position',
											'frontend-dashboard-custom-post' ) ?></label>
									<?php
									// phpcs:ignore
									echo fed_input_box( 'post_menu_position', array(
										'name'        => 'post_position',
										'value'       => isset( $cp_admin_settings[ $index ]['menu']['post_position'] ) ? $cp_admin_settings[ $index ]['menu']['post_position'] : 2,
										'placeholder' => __( 'Post Menu Position' ),
									), 'number' );
									?>

								</div>
								<div class="col-md-4">
									<label>
										<?php esc_attr_e( 'Post Menu Icon', 'frontend-dashboard-custom-post' ); ?>
									</label>
									<?php
									// phpcs:ignore
									echo fed_input_box( 'fed_payment_options[post_menu_icon]', array(
										'name'        => 'post_menu_icon',
										'placeholder' => __( 'Please Select Post Menu Icon' ),
										'value'       => isset( $cp_admin_settings[ $index ]['menu']['post_menu_icon'] ) ? $cp_admin_settings[ $index ]['menu']['post_menu_icon'] : 'fa fa-file-text',
										'class'       => 'post_menu_icon',
										'extra'       => 'data-toggle="modal" data-target=".fed_show_fa_list" placeholder="Menu Icon" data-fed_menu_box_id="post_menu_icon"',
									), 'single_line' );
									?>
								</div>

							</div>
						</div>
						<div class="custom_post_permissions">
							<div class="row">
								<div class="col-md-12">
									<h4>
										<?php
										esc_attr_e( 'Allow User Roles to Add/Edit/Delete Posts',
											'frontend-dashboard-custom-post' );
										?>
									</h4>
								</div>
							</div>
							<div class="row">
								<?php
								foreach ( $all_roles as $key => $role ) {
									$c_value = in_array( $key, $post_permission, false ) ? 'Enable' : 'Disable';
									?>
									<div class="col-md-3">
										<?php
										// phpcs:ignore
										echo fed_input_box( 'post_permission', array(
											'default_value' => 'Enable',
											'name'          => 'post_permission[' . $key . ']',
											'label'         => $role,
											'value'         => $c_value,
										), 'checkbox' );
										?>
									</div>
									<?php
								}
								?>
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
		 * @param  string $index  Index
		 * @param  array  $cp_admin_settings  Admin Settings
		 */
		public function fed_cp_admin_settings_tabs( $index, $cp_admin_settings ) {
			$post_object = get_post_type_object( $index );
			$options     = isset( $cp_admin_settings[ $index ] ) ? $cp_admin_settings[ $index ] : array();
			$menu        = $this->getMenuNameByPostType( $options, $post_object );
			$menu_icons  = $this->getMenuIconByPostType( $options, $post_object );
			$tabs        = $this->fed_cp_admin_settings_tab_content( $index, $cp_admin_settings );
			?>
			<div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-xs space-y-6">
				<div class="flex items-center gap-3.5 pb-5 border-b border-slate-100">
					<div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-base shrink-0">
						<i class="<?php echo esc_attr( $menu_icons ); ?>"></i>
					</div>
					<div>
						<h3 class="text-sm sm:text-base font-bold text-slate-900 m-0"><?php echo esc_html( $menu ); ?></h3>
						<p class="text-xs text-slate-500 m-0 mt-0.5"><?php esc_html_e( 'Configure frontend submission settings, permissions, and layout for this post type.', 'frontend-dashboard-custom-post' ); ?></p>
					</div>
				</div>

				<form method="post"
						class="fed_admin_menu fed_ajax space-y-6"
						action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=fed_cp_admin_settings' ) ); ?>">

					<?php wp_nonce_field( 'fed_nonce', 'fed_nonce' ); ?>
					<?php echo fed_loader(); ?>

					<input type="hidden" name="custom_post_type" value="<?php echo esc_attr( $index ); ?>"/>

					<div class="space-y-6">
						<?php
						foreach ( $tabs as $tab_index => $tab ) {
							if ( 'post_permission' === $tab_index ) {
								$current_post_perms = isset( $options['permissions']['post_permission'] ) ? array_keys( $options['permissions']['post_permission'] ) : array();
								?>
								<div class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-5 sm:p-6 space-y-4">
									<?php
									fed_render_user_roles_selector(
										array(
											'name_prefix'         => 'post_permission',
											'selected'            => $current_post_perms,
											'all_roles'           => fed_get_user_roles(),
											'default_all_checked' => true,
											'title'               => $tab['name'],
											'description'         => __( 'Select user roles permitted to manage, author, edit, and delete posts for this post type.', 'frontend-dashboard-custom-post' ),
										)
									);
									?>
								</div>
								<?php
								continue;
							}

							if ( 'taxonomies' === $tab_index ) {
								$taxonomies = get_object_taxonomies( $index, 'object' );
								$core_tax   = array( 'category', 'post_tag' );
								$all_roles  = fed_get_user_roles();
								?>
								<div class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-5 sm:p-6 space-y-5">
									<div class="flex items-center gap-2.5 pb-3 border-b border-slate-200/80">
										<div class="w-7 h-7 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-xs">
											<i class="fas fa-tags"></i>
										</div>
										<div>
											<h4 class="text-xs font-bold text-slate-900 m-0"><?php echo esc_html( $tab['name'] ); ?></h4>
											<?php if ( isset( $tab['note'] ) ) : ?>
												<p class="text-[11px] text-slate-500 m-0 mt-0.5"><?php echo esc_html( $tab['note'] ); ?></p>
											<?php endif; ?>
										</div>
									</div>

									<?php if ( ! empty( $taxonomies ) ) : ?>
										<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
											<?php
											foreach ( $taxonomies as $tax_key => $taxonomy ) :
												$wp_core_tax    = in_array( $tax_key, $core_tax, true ) ? ' (WP Core)' : '';
												$tax_title      = $taxonomy->label . $wp_core_tax;
												$disabled_roles = isset( $options['taxonomies'][ $tax_key ] ) && is_array( $options['taxonomies'][ $tax_key ] ) ? array_keys( $options['taxonomies'][ $tax_key ] ) : array();
												?>
												<div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-2xs">
													<?php
													fed_render_user_roles_selector(
														array(
															'name_prefix'         => 'taxonomies[' . $tax_key . ']',
															'selected'            => $disabled_roles,
															'all_roles'           => $all_roles,
															'default_all_checked' => false,
															'title'               => $tax_title,
															'description'         => sprintf( __( 'Select user role(s) to DISABLE the visibility of %s', 'frontend-dashboard-custom-post' ), $tax_title ),
														)
													);
													?>
												</div>
											<?php endforeach; ?>
										</div>
									<?php else : ?>
										<div class="p-4 bg-amber-50/70 border border-amber-200/70 rounded-2xl text-xs text-amber-800">
											<?php esc_html_e( 'No taxonomies are currently registered or associated with this post type.', 'frontend-dashboard-custom-post' ); ?>
										</div>
									<?php endif; ?>
								</div>
								<?php
								continue;
							}
							?>
							<div class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-5 sm:p-6 space-y-4">
								<div class="flex items-center gap-2.5 pb-3 border-b border-slate-200/80">
									<h4 class="text-xs font-bold text-slate-900 m-0"><?php echo esc_html( $tab['name'] ); ?></h4>
								</div>

								<?php if ( isset( $tab['note'] ) ) : ?>
									<p class="text-xs text-slate-500 m-0"><?php echo esc_html( $tab['note'] ); ?></p>
								<?php endif; ?>

								<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
									<?php foreach ( $tab['input'] as $post_type ) : ?>
										<div class="space-y-1.5">
											<?php if ( isset( $post_type['heading'] ) ) : ?>
												<div class="text-xs font-bold text-indigo-700 bg-indigo-50/80 p-2 rounded-xl border border-indigo-100 mb-2">
													<?php echo esc_html( $post_type['heading'] ); ?>
												</div>
											<?php endif; ?>

											<?php if ( isset( $post_type['name'] ) && null !== $post_type['name'] ) : ?>
												<label class="block text-xs font-bold text-slate-700">
													<?php echo wp_kses_post( isset( $post_type['required'] ) ? '<span class="text-rose-500">' . $post_type['name'] . '</span>' : $post_type['name'] ); ?>
												</label>
											<?php endif; ?>

											<?php
											if ( isset( $post_type['input'] ) ) {
												echo fed_get_input_details( $post_type['input'] );
											}
											if ( isset( $post_type['extra'] ) ) {
												echo '<div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2">';
												foreach ( $post_type['extra'] as $extra ) {
													?>
													<div class="p-2.5 bg-white border border-slate-200/80 rounded-xl">
														<?php if ( isset( $extra['label_title'] ) ) : ?>
															<span class="block text-[11px] font-semibold text-slate-600 mb-1"><?php echo esc_html( $extra['label_title'] ); ?></span>
														<?php endif; ?>
														<?php echo fed_get_input_details( $extra ); ?>
													</div>
													<?php
												}
												echo '</div>';
											}
											?>

											<?php if ( ! empty( $post_type['help_message'] ) ) : ?>
												<p class="text-[11px] text-slate-400 m-0"><?php echo wp_strip_all_tags( $post_type['help_message'] ); ?></p>
											<?php endif; ?>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php } ?>
					</div>

					<div class="pt-4 border-t border-slate-100 flex items-center justify-end">
						<button type="submit" class="fed-btn-primary h-11 inline-flex items-center justify-center gap-2 px-6 rounded-xl font-semibold text-xs tracking-wide shadow-sm transition-all active:scale-95 cursor-pointer">
							<i class="fas fa-save text-xs" style="color: #ffffff !important;"></i>
							<span style="color: #ffffff !important;"><?php esc_html_e( 'Save Changes', 'frontend-dashboard-custom-post' ); ?></span>
						</button>
					</div>
				</form>
			</div>
			<?php
		}

		/**
		 * Admin Settings Save
		 */
		public function fed_cp_admin_settings() {
			$request   = isset( $_POST ) ? wp_unslash( $_POST ) : array();
			fed_verify_nonce( $request );
			$post_type = isset( $request['custom_post_type'] ) ? sanitize_text_field( $request['custom_post_type'] ) : '';
			if ( fed_check_post_type( $post_type ) ) {

				if ( empty( $request['rename_post'] ) || empty( $request['post_position'] ) || empty( $request['post_menu_icon'] ) ) {
					wp_send_json_error( array(
						'message' => __( 'Please enter all menu fields', 'frontend-dashboard-custom-post' ),
					) );
				}
				$fed_admin_settings_custom_post = get_option( 'fed_cp_admin_settings' );

				$fed_admin_settings_custom_post[ $post_type ] = array(
					'settings'    => array(
						'fed_post_status'      => isset( $request['fed_post_status'] ) ? sanitize_text_field( $request['fed_post_status'] ) : 'publish',
						'disable_post_edit'    => isset( $request['disable_post_edit'] ) ? sanitize_text_field( $request['disable_post_edit'] ) : 'no',
						'disable_post_add_new' => isset( $request['disable_post_add_new'] ) ? sanitize_text_field( $request['disable_post_add_new'] ) : 'no',
						'disable_post_delete'  => isset( $request['disable_post_delete'] ) ? sanitize_text_field( $request['disable_post_delete'] ) : 'no',
						'disable_post_view'    => isset( $request['disable_post_view'] ) ? sanitize_text_field( $request['disable_post_view'] ) : 'no',
					),
					'permissions' => array( 'post_permission' => isset( $request['post_permission'] ) ? $request['post_permission'] : array() ),
					'menu'        => array(
						'rename_post'    => isset( $request['rename_post'] ) ? sanitize_text_field( $request['rename_post'] ) : 'Post',
						'post_position'  => isset( $request['post_position'] ) ? sanitize_text_field( $request['post_position'] ) : 2,
						'post_menu_icon' => isset( $request['post_menu_icon'] ) ? sanitize_text_field( $request['post_menu_icon'] ) : 'fa fa-file-text',
					),
					'dashboard'   => isset( $request['dashboard'] ) ? $request['dashboard'] : array(),
					'taxonomies'  => isset( $request['taxonomies'] ) ? $request['taxonomies'] : array(),
				);

				$filter = apply_filters( 'fed_cp_admin_settings_save', $fed_admin_settings_custom_post, $post_type,
					$request );
				update_option( 'fed_cp_admin_settings', $filter );

				wp_send_json_success( array(
					'message' => __( 'Custom Post Updated Successfully ', 'frontend-dashboard-custom-post' ),
				) );
			}
			wp_send_json_error( array(
				'message' => __( 'Post Type Does not Exist', 'frontend-dashboard-custom-post' ),
			) );
		}

		/**
		 * Admin settings Tab Content
		 *
		 * @param  string $index  Index
		 * @param  array  $request  Request
		 *
		 * @return array
		 */
		public function fed_cp_admin_settings_tab_content( $index, $request ) {
			$post_status      = fed_get_post_status();
			$user_roles       = fed_get_user_roles();
			$post_permissions = isset( $request[ $index ]['permissions']['post_permission'] ) ? array_keys( $request[ $index ]['permissions']['post_permission'] ) : array();
			$options          = isset( $request[ $index ] ) ? $request[ $index ] : array();
			$post_type        = get_post_type_object( $index );
			$menu_title       = $this->getMenuNameByPostType( $options, $post_type );
			$post_permission  = array();

			foreach ( $user_roles as $key => $role ) {
				$c_value                 = in_array( $key, $post_permissions, false ) ? 'Enable' : 'Disable';
				$post_permission[ $key ] = array(
					'name'  => null,
					'input' => array(
						'input_type'    => 'checkbox',
						'user_value'    => $c_value,
						'input_meta'    => 'post_permission[' . $key . ']',
						'label'         => $role,
						'default_value' => 'Enable',
					),
				);
			}

			$content = array(
				'menu'               => array(
					'name'  => 'Menu',
					'input' => array(
						'rename_post'    => array(
							'name'  => __( 'Post Menu Name', 'frontend-dashboard-custom-post' ),
							'input' => array(
								'placeholder' => __( 'Post Menu Name',
									'frontend-dashboard-custom-post' ),
								'input_type'  => 'single_line',
								'user_value'  => isset( $request[ $index ]['menu']['rename_post'] ) ? $request[ $index ]['menu']['rename_post'] : '',
								'input_meta'  => 'rename_post',
							),
						),
						'post_position'  => array(
							'name'  => __( 'Post Menu Position', 'frontend-dashboard-custom-post' ),
							'input' => array(
								'placeholder' => __( 'Post Menu Position',
									'frontend-dashboard-custom-post' ),
								'input_type'  => 'number',
								'user_value'  => isset( $request[ $index ]['menu']['post_position'] ) ? $request[ $index ]['menu']['post_position'] : '',
								'input_meta'  => 'post_position',
							),
						),
						'post_menu_icon' => array(
							'name'  => __( 'Menu Icon', 'frontend-dashboard-custom-post' ),
							'input' => array(
								'placeholder' => __( 'Menu Icon', 'frontend-dashboard-custom-post' ),
								'input_type'  => 'single_line',
								'user_value'  => isset( $request[ $index ]['menu']['post_menu_icon'] ) ? $request[ $index ]['menu']['post_menu_icon'] : '',
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
							'name'  => __( 'New Post Status', 'frontend-dashboard-custom-post' ),
							'input' => array(
								'input_type'  => 'select',
								'user_value'  => isset( $request[ $index ]['settings']['fed_post_status'] ) ? $request[ $index ]['settings']['fed_post_status'] : '',
								'input_meta'  => 'fed_post_status',
								'input_value' => $post_status,
							),
						),
						'disable_post_add_new' => array(
							'name'  => __( 'Disable Post Add New', 'frontend-dashboard-custom-post' ),
							'input' => array(
								'input_type'  => 'select',
								'user_value'  => isset( $request[ $index ]['settings']['disable_post_add_new'] ) ? $request[ $index ]['settings']['disable_post_add_new'] : '',
								'input_meta'  => 'disable_post_add_new',
								'input_value' => fed_yes_no( 'ASC' ),
							),
						),
						'disable_post_edit'    => array(
							'name'  => __( 'Disable Post Edit', 'frontend-dashboard-custom-post' ),
							'input' => array(
								'input_type'  => 'select',
								'user_value'  => isset( $request[ $index ]['settings']['disable_post_edit'] ) ? $request[ $index ]['settings']['disable_post_edit'] : '',
								'input_meta'  => 'disable_post_edit',
								'input_value' => fed_yes_no( 'ASC' ),
							),
						),
						'disable_post_view'    => array(
							'name'  => __( 'Disable Post View', 'frontend-dashboard-custom-post' ),
							'input' => array(
								'input_type'  => 'select',
								'user_value'  => isset( $request[ $index ]['settings']['disable_post_view'] ) ? $request[ $index ]['settings']['disable_post_view'] : '',
								'input_meta'  => 'disable_post_view',
								'input_value' => fed_yes_no( 'ASC' ),
							),
						),
						'disable_post_delete'  => array(
							'name'  => __( 'Disable Post Delete', 'frontend-dashboard-custom-post' ),
							'input' => array(
								'input_type'  => 'select',
								'user_value'  => isset( $request[ $index ]['settings']['disable_post_delete'] ) ? $request[ $index ]['settings']['disable_post_delete'] : '',
								'input_meta'  => 'disable_post_delete',
								'input_value' => fed_yes_no( 'ASC' ),
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
								'user_value'    => isset( $request[ $index ]['dashboard']['post_content'] ) ? 'Enable' : '',
								'input_meta'    => 'dashboard[post_content]',
								'label'         => __( 'Disable Post Content',
									'frontend-dashboard-custom-post' ),
								'default_value' => 'Enable',
							),
						),
						'featured_image' => array(
							'name'  => null,
							'input' => array(
								'input_type'    => 'checkbox',
								'user_value'    => isset( $request[ $index ]['dashboard']['featured_image'] ) ? 'Enable' : '',
								'input_meta'    => 'dashboard[featured_image]',
								'label'         => __( 'Disable Feature Image',
									'frontend-dashboard-custom-post' ),
								'default_value' => 'Enable',
							),
						),
						'allow_comments' => array(
							'name'  => null,
							'input' => array(
								'input_type'    => 'checkbox',
								'user_value'    => isset( $request[ $index ]['dashboard']['allow_comments'] ) ? 'Enable' : '',
								'input_meta'    => 'dashboard[allow_comments]',
								'label'         => __( 'Disable Comments',
									'frontend-dashboard-custom-post' ),
								'default_value' => 'Enable',
							),
						),
					),
				),
				'post_permission'    => array(
					'name'  => 'Allow User Roles to Add/Edit/Delete ' . $menu_title,
					'input' => $post_permission,
				),
				'taxonomies'         => array(
					'name'  => 'Taxonomies [Category/Tag]',
					'input' => fed_cp_checkbox_taxonomies_with_users( $request, $index ),
					'note'  => __( 'Select the respective role(s) to DISABLE the visibility of Taxonomy',
						'frontend-dashboard-custom-post' ),
				),
			);

			return apply_filters( 'fed_cp_admin_fed_settings', $content, $index, $request );

		}

		/**
		 * Get Menu icon by Post Type
		 *
		 * @param  array  $options  Options.
		 * @param  object $post_type  Post Type.
		 *
		 * @return string|void
		 */
		public function getMenuIconByPostType( $options, $post_type ) {
			/**
			 * Check for Default Post Dashicons
			 * else take from the Setting Dashboard
			 */
			$menu_icon = 'fa fa-file-text';

			if ( null !== $post_type && isset( $post_type->menu_icon ) ) {
				$menu_icon = 'dashicons ' . $post_type->menu_icon;
			}
			if ( isset( $options['menu']['post_menu_icon'] ) && '' != $options['menu']['post_menu_icon'] ) {
				$menu_icon = esc_attr( $options['menu']['post_menu_icon'] );
			}

			return $menu_icon;
		}

		/**
		 * Get Menu Name by Post Type
		 *
		 * @param  array  $options  Options.
		 * @param  object $post_type  Post Type.
		 *
		 * @return string|void
		 */
		public function getMenuNameByPostType( $options, $post_type ) {
			if ( isset( $options['menu']['rename_post'] ) && ! empty( $options['menu']['rename_post'] ) ) {
				return esc_attr( $options['menu']['rename_post'] );
			}

			if ( null !== $post_type && isset( $post_type->label ) ) {
				return $post_type->label;
			}

			return 'Post';
		}

		/**
		 * Delete post by ID
		 */
		public function fed_dashboard_delete_post_by_id_fn() {
			$post_payload = isset( $_POST ) ? wp_unslash( $_POST ) : array();

			if ( ! isset( $post_payload['fed_dashboard_delete_post_by_id'] ) || ! wp_verify_nonce( $post_payload['fed_dashboard_delete_post_by_id'],
				'fed_dashboard_delete_post_by_id' )
			) {
				wp_send_json_error( array(
					'message' => array(
						__( 'Invalid Request, Please reload the page and try again',
							'frontend-dashboard-custom-post' ),
					),
				) );

			}

			$post = get_post( (int) $post_payload['post_id'] );

			if ( ( get_current_user_id() == $post->post_author || fed_is_admin() ) && fed_cp_is_user_can_delete_post( $post->post_type ) ) {
				// All post will be soft delete from version 1.5.3.
				$status = wp_update_post(
					array(
						'ID'          => $post_payload['post_id'],
						'post_status' => 'trash',
					)
				);
				if ( ! $status ) {
					wp_send_json_error( array(
						'message' => __( 'Something went wrong, please refresh and try again later',
							'frontend-dashboard-custom-post' ),
					) );

				}
				wp_send_json_success( array(
					'message' => __( 'Successfully Deleted', 'frontend-dashboard-custom-post' ),
				) );
			}

			wp_send_json_error( array(
				'message' => __( 'You are not allowed to do this action', 'frontend-dashboard-custom-post' ),
			) );
		}

		/**
		 * Frontend Dashboard Add New Post
		 *
		 * @param  array $request  Request
		 * @param  array $menu  Menu
		 */
		private function fed_cp_frontend_dashboard_add_new_post( $request, $menu ) {
			$post_type     = isset( $request['fed_post_type'] ) ? $request['fed_post_type'] : 'post';
			$post_table    = fed_fetch_rows_by_table( BC_FED_TABLE_POST );
			$post_settings = fed_get_post_settings_by_type( $post_type );

			$default_post_status = fed_get_post_status();
			$post_status         = fed_get_data( 'settings.fed_post_status', $post_settings, 'pending' );
			if (
				array_key_exists( $post_status, $default_post_status ) &&
				( 'pending' === $post_status || 'draft' === $post_status ) &&
				! fed_is_admin()
			) {
				if ( 'pending' === $post_status ) {
					unset( $default_post_status['publish'] );
				} else {
					$default_post_status = array( 'draft' => 'Draft' );
				}
			}

			usort( $post_table, 'fed_sort_by_order' );
			?>
			<div class="flex items-center justify-between pb-5 mb-6 border-b border-slate-100 flex-wrap gap-3">
				<a class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-all no-underline cursor-pointer shadow-2xs"
				   href="<?php echo esc_url( remove_query_arg( array( 'post_status', 'post_id' ) ) ); ?>">
					<i class="fa fa-arrow-left text-xs"></i>
					<span><?php esc_html_e( 'Back to', 'frontend-dashboard-custom-post' ); ?> <?php echo esc_html( $menu['name'] ); ?></span>
				</a>
			</div>
			<form method="post"
					class="fed_dashboard_add_new_post space-y-6"
					action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=fed_dashboard_add_edit_post' ) ); ?>">

				<?php wp_nonce_field( 'fed_nonce', 'fed_nonce' ); ?>

				<?php
				// phpcs:ignore
				echo fed_get_input_details(
					array(
						'input_meta' => 'ID',
						'user_value' => '',
						'input_type' => 'hidden',
						'id_name'    => 'fed_post_id_hidden',
					) );
				?>

				<input type="hidden"
						name="post_type"
						value="<?php echo esc_attr( $post_type ); ?>">

				<input type="hidden"
						name="fed_post_type"
						value="<?php echo esc_attr( $post_type ); ?>">

				<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
					<!-- Main Canvas (Left 8 Cols) -->
					<div class="lg:col-span-8 space-y-6">
						<!-- Post Title Card -->
						<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs space-y-2">
							<label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
								<?php esc_html_e( 'Title', 'frontend-dashboard-custom-post' ); ?> <span class="text-rose-500">*</span>
							</label>
							<?php
							// phpcs:ignore
							echo fed_get_input_details( array(
								'placeholder' => __( 'Enter post title here...', 'frontend-dashboard-custom-post' ),
								'input_meta'  => 'post_title',
								'input_type'  => 'single_line',
							) );
							?>
						</div>

						<!-- Content Editor Card -->
						<?php
						if ( ! isset( $post_settings['dashboard']['post_content'] ) && post_type_supports( $post_type, 'editor' ) ) {
							?>
							<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs space-y-3">
								<label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
									<?php esc_html_e( 'Content', 'frontend-dashboard-custom-post' ); ?>
								</label>
								<div class="rounded-xl overflow-hidden border border-slate-200">
									<?php wp_editor( '', 'post_content', array( 'quicktags' => true ) ); ?>
								</div>
							</div>
							<?php
						}
						?>

						<!-- Extra Fields Card -->
						<?php
						$custom_fields = array();
						foreach ( $post_table as $item ) {
							if ( $post_type === $item['post_type'] ) {
								$custom_fields[] = $item;
							}
						}
						if ( ! empty( $custom_fields ) ) {
							?>
							<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs space-y-4">
								<h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-3">
									<i class="fas fa-sliders-h text-indigo-500"></i>
									<span><?php esc_html_e( 'Additional Fields', 'frontend-dashboard-custom-post' ); ?></span>
								</h4>
								<div class="space-y-4">
									<?php
									foreach ( $custom_fields as $item ) {
										?>
										<div class="space-y-1.5">
											<label class="block text-xs font-semibold text-slate-700">
												<?php echo esc_html( $item['label_name'] ); ?>
											</label>
											<?php echo fed_get_input_details( $item ); ?>
										</div>
										<?php
									}
									?>
								</div>
							</div>
							<?php
						}
						?>
					</div>

					<!-- Sidebar (Right 4 Cols) -->
					<div class="lg:col-span-4 space-y-5">
						<!-- Publishing & Actions Card -->
						<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs space-y-4">
							<h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-3">
								<i class="fas fa-paper-plane text-indigo-500"></i>
								<span><?php esc_html_e( 'Publish Settings', 'frontend-dashboard-custom-post' ); ?></span>
							</h4>

							<div class="space-y-1.5">
								<label class="block text-xs font-semibold text-slate-700">
									<?php esc_html_e( 'Post Status', 'frontend-dashboard-custom-post' ); ?>
								</label>
								<?php
								echo fed_form_select(
									array(
										'input_value' => $default_post_status,
										'input_meta'  => 'post_status',
										'user_value'  => '',
										'class_name'  => 'form-control',
									)
								);
								?>
							</div>

							<?php
							if ( ! isset( $post_settings['dashboard']['allow_comments'] ) && post_type_supports( $post_type, 'comments' ) ) {
								?>
								<div class="pt-2 border-t border-slate-100">
									<label class="flex items-center gap-2 cursor-pointer text-xs font-medium text-slate-700">
										<?php
										echo fed_get_input_details( array(
											'input_meta'    => 'comment_status',
											'input_type'    => 'checkbox',
											'default_value' => 'open',
											'user_value'    => 'open',
										) );
										?>
										<span><?php esc_html_e( 'Allow Comments', 'frontend-dashboard-custom-post' ); ?></span>
									</label>
								</div>
								<?php
							}
							?>

							<div class="pt-3 border-t border-slate-100">
								<button class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] text-white text-xs font-bold rounded-xl shadow-xs transition-all cursor-pointer"
										type="submit">
									<i class="fa fa-save text-xs" style="color: #ffffff !important;"></i>
									<span style="color: #ffffff !important;"><?php esc_html_e( 'Save & Publish', 'frontend-dashboard-custom-post' ); ?></span>
								</button>
							</div>
						</div>

						<!-- Featured Image Card -->
						<?php
						if ( ! isset( $post_settings['dashboard']['featured_image'] ) && post_type_supports( $post_type, 'thumbnail' ) ) {
							?>
							<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs space-y-3">
								<h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
									<i class="fas fa-image text-indigo-500"></i>
									<span><?php esc_html_e( 'Featured Image', 'frontend-dashboard-custom-post' ); ?></span>
								</h4>
								<?php
								echo fed_get_input_details( array(
									'input_meta' => '_thumbnail_id',
									'input_type' => 'file',
								) );
								?>
							</div>
							<?php
						}
						?>

						<!-- Taxonomies & Tags -->
						<?php $this->fed_show_category_tag_post_format( $post_type, $post_settings ); ?>
					</div>
				</div>
			</form>
			<?php
		}


		/**
		 * Display Dashboard View Post List
		 *
		 * @param  string $post_type  Post Type
		 * @param  array  $menu  Menu
		 */
		private function fed_display_dashboard_view_post_list( $menu, $post_type = 'post' ) {
			$get_payload       = isset( $_GET ) ? array_map( 'sanitize_text_field', wp_unslash( $_GET ) ) : array();
			$post              = fed_process_dashboard_display_post( $post_type );
			$current_page      = isset( $_REQUEST['page_number'] ) ? absint( $_REQUEST['page_number'] ) : 1;
			$pagination_counts = ceil( $post->found_posts / get_option( 'posts_per_page', 10 ) );
			$posts             = $post->get_posts();
			?>
			<div class="overflow-x-auto rounded-xl border border-slate-200/80 bg-white shadow-xs">
				<table class="w-full text-left border-collapse">
					<thead>
						<tr class="bg-slate-50/80 border-b border-slate-200/80 text-xs font-semibold text-slate-500 uppercase tracking-wider">
							<th class="py-3 px-4 w-16">#ID</th>
							<th class="py-3 px-4"><?php esc_html_e( 'Title', 'frontend-dashboard' ); ?></th>
							<th class="py-3 px-4"><?php esc_html_e( 'Author', 'frontend-dashboard' ); ?></th>
							<th class="py-3 px-4 whitespace-nowrap"><?php esc_html_e( 'Date', 'frontend-dashboard' ); ?></th>
							<th class="py-3 px-4 text-right"><?php esc_html_e( 'Actions', 'frontend-dashboard' ); ?></th>
						</tr>
					</thead>
					<tbody class="divide-y divide-slate-100 text-sm text-slate-700">
						<?php if ( empty( $posts ) ) { ?>
							<tr>
								<td colspan="5" class="py-12 text-center text-slate-400">
									<i class="fa fa-folder-open-o text-3xl mb-2 block text-slate-300"></i>
									<p class="text-sm font-medium mb-0"><?php esc_html_e( 'No records found.', 'frontend-dashboard' ); ?></p>
								</td>
							</tr>
						<?php } else {
							foreach ( $posts as $single_post ) {
								$status = $single_post->post_status;
								$status_classes = array(
									'publish' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
									'draft'   => 'bg-amber-50 text-amber-700 border-amber-200/60',
									'pending' => 'bg-sky-50 text-sky-700 border-sky-200/60',
									'trash'   => 'bg-red-50 text-red-700 border-red-200/60',
								);
								$badge_class = isset( $status_classes[ $status ] ) ? $status_classes[ $status ] : 'bg-slate-50 text-slate-700 border-slate-200';
								$author_name = get_the_author_meta( 'display_name', $single_post->post_author );
								$formatted_date = date_i18n( get_option( 'date_format' ), strtotime( $single_post->post_date ) );
								?>
								<tr class="hover:bg-slate-50/60 transition-colors">
									<td class="py-3.5 px-4 font-mono text-xs text-slate-400">
										#<?php echo (int) $single_post->ID; ?>
									</td>
									<td class="py-3.5 px-4 font-medium text-slate-900">
										<div class="flex items-center gap-2 flex-wrap">
											<span><?php echo esc_html( $single_post->post_title ); ?></span>
											<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border <?php echo esc_attr( $badge_class ); ?>">
												<?php echo esc_html( fed_get_display_post_status( $status ) ); ?>
											</span>
										</div>
									</td>
									<td class="py-3.5 px-4 text-slate-600">
										<div class="flex items-center gap-2">
											<span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 text-xs flex items-center justify-center font-bold">
												<?php echo esc_html( strtoupper( substr( $author_name, 0, 1 ) ) ); ?>
											</span>
											<span><?php echo esc_html( $author_name ); ?></span>
										</div>
									</td>
									<td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">
										<?php echo esc_html( $formatted_date ); ?>
									</td>
									<td class="py-3.5 px-4 text-right whitespace-nowrap">
										<div class="inline-flex items-center gap-1.5 justify-end">
											<?php if ( fed_cp_is_user_can_view_post( $post_type ) ) { ?>
												<a class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors inline-flex items-center justify-center"
													target="_blank"
													title="<?php esc_attr_e( 'View', 'frontend-dashboard' ); ?>"
													href="<?php echo esc_url( get_permalink( (int) $single_post->ID ) ); ?>">
													<i class="fa fa-eye"></i>
												</a>
											<?php } ?>

											<?php if ( fed_cp_is_user_can_edit_post( $post_type ) ) { ?>
												<a class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors inline-flex items-center justify-center no-underline cursor-pointer"
													title="<?php esc_attr_e( 'Edit', 'frontend-dashboard' ); ?>"
													href="<?php echo esc_url( add_query_arg( array( 'post_id' => (int) $single_post->ID, 'fed_post_type' => $post_type ) ) ); ?>">
													<i class="fa fa-pencil"></i>
												</a>
											<?php } ?>

											<?php if ( fed_cp_is_user_can_delete_post( $post_type ) ) { ?>
												<form method="post"
													class="inline-block fed_dashboard_delete_post_by_id m-0"
													action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=fed_dashboard_delete_post_by_id' ) ); ?>">
													<?php wp_nonce_field( 'fed_dashboard_delete_post_by_id', 'fed_dashboard_delete_post_by_id' ); ?>
													<input type="hidden" name="post_id" value="<?php echo (int) $single_post->ID; ?>"/>
													<button class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors border-0 bg-transparent cursor-pointer inline-flex items-center justify-center"
															title="<?php esc_attr_e( 'Delete', 'frontend-dashboard' ); ?>"
															type="submit">
														<i class="fa fa-trash"></i>
													</button>
												</form>
											<?php } ?>
										</div>
									</td>
								</tr>
							<?php }
						} ?>
					</tbody>
				</table>
			</div>
			<?php
			if ( $pagination_counts > 1 ) {
				fed_get_pagination( $current_page, $pagination_counts );
			}
		}

		/**
		 * Show Category Tag, Post Format
		 *
		 * @param  object $post  Post
		 * @param  array  $post_settings  Post Settings
		 */
		private function fed_show_category_tag_post_format( $post, $post_settings ) {
			$post_type = is_object( $post ) ? $post->post_type : $post;
			$ctps      = fed_get_category_tag_post_format( $post_type );
			$user_role = fed_get_current_user_role_key();

			foreach ( $ctps as $index => $ctp ) {
				if ( 'category' === $index ) {
					foreach ( $ctp as $cindex => $category ) {
						if ( ! isset( $post_settings['taxonomies'][ $cindex ][ $user_role ] ) ) {
							?>
							<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs space-y-3">
								<h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
									<i class="fas fa-folder text-indigo-500"></i>
									<span><?php echo esc_attr( $category->label ); ?></span>
								</h4>
								<div>
									<?php
									// phpcs:ignore
									echo fed_get_dashboard_display_categories( $post, $category );
									?>
								</div>
							</div>
							<?php
						}
					}
				}
				if ( 'tag' === $index ) {
					foreach ( $ctp as $tindex => $tag ) {
						if ( ! isset( $post_settings['taxonomies'][ $tindex ][ $user_role ] ) ) {
							?>
							<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs space-y-3">
								<div class="flex items-center justify-between">
									<h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 m-0">
										<i class="fas fa-tags text-indigo-500"></i>
										<span><?php echo esc_attr( $tag->label ); ?></span>
									</h4>
									<?php do_action( 'fed_frontend_dashboard_edit_tag_label', $tag, $post ); ?>
								</div>
								<div>
									<?php
									// phpcs:ignore
									echo fed_get_dashboard_display_tags( $post, $tag );
									?>
								</div>
							</div>
							<?php
						}
					}
				}
				if ( 'post_format' === $index ) {
					if ( ! isset( $post_settings['taxonomies']['post_format'][ $user_role ] ) ) {
						$post_format = fed_dashboard_get_post_format();
						if ( is_array( $post_format ) ) {
							$post_value = isset( $post->ID ) ? esc_attr( get_post_format( $post->ID ) ) : 'standard';
							if ( empty( $post_value ) ) {
								$post_value = 'standard';
							}
							$format_options = array(
								'standard' => __( 'Standard', 'frontend-dashboard-custom-post' ),
							);
							foreach ( $post_format as $pf ) {
								$format_options[ $pf ] = ucfirst( $pf );
							}
							?>
							<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs space-y-3">
								<h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
									<i class="fas fa-newspaper text-indigo-500"></i>
									<span><?php esc_attr_e( 'Post Format', 'frontend-dashboard-custom-post' ); ?></span>
								</h4>
								<div>
									<?php
									// phpcs:ignore
									echo fed_form_select(
										array(
											'input_meta'  => 'tax_input[post_format][]',
											'input_value' => $format_options,
											'user_value'  => $post_value,
											'class_name'  => 'form-control',
										)
									);
									?>
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
		 * @param  int   $post_id  Post ID
		 * @param  array $menu  Menu
		 */
		private function fed_cp_frontend_dashboard_edit_post_by_id( $post_id, $menu ) {
			$user         = get_userdata( get_current_user_id() );
			$post         = get_post( (int) $post_id );
			$preview_link = get_preview_post_link( $post->ID );
			$post_status  = fed_get_data( 'post_status', $post, array() );
			if (
				null !== $post &&
				(
					( fed_cp_is_user_can_edit_post( $post->post_type ) && (int) $post->post_author === (int) $user->ID ) ||
					fed_is_admin()
				)
			) {
				$post_table    = fed_fetch_table_rows_by_key_value( BC_FED_TABLE_POST, 'post_type', $post->post_type );
				$post_meta     = get_post_meta( $post->ID );
				$post_settings = fed_get_post_settings_by_type( $post->post_type );
				uasort( $post_table, 'fed_sort_by_order' );
				?>
				<div class="flex items-center justify-between pb-5 mb-6 border-b border-slate-100 flex-wrap gap-3">
					<a class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-all no-underline cursor-pointer shadow-2xs"
					   href="<?php echo esc_url( remove_query_arg( array( 'post_id', 'post_status' ) ) ); ?>">
						<i class="fa fa-arrow-left text-xs"></i>
						<span><?php esc_html_e( 'Back to', 'frontend-dashboard-custom-post' ); ?> <?php echo esc_html( $menu['name'] ); ?></span>
					</a>

					<div class="flex items-center gap-2 flex-wrap">
						<?php
						if ( fed_cp_is_user_can_add_post( $post->post_type ) ) {
							$add_url     = add_query_arg( array(
								'post_status'   => 'add',
								'fed_post_type' => $post->post_type,
							) );
							$new_add_url = remove_query_arg( 'post_id', $add_url );
							?>
							<a class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold transition-all no-underline cursor-pointer" href="<?php echo esc_url( $new_add_url ); ?>">
								<i class="fa fa-plus text-xs"></i>
								<span><?php esc_html_e( 'Add New', 'frontend-dashboard-custom-post' ); ?></span>
							</a>
						<?php } ?>
						<?php if ( $preview_link && ! empty( $preview_link ) ) { ?>
							<a target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-all no-underline cursor-pointer" href="<?php echo esc_url( $preview_link ); ?>">
								<i class="fa fa-eye text-xs"></i>
								<span><?php esc_html_e( 'Preview Post', 'frontend-dashboard-custom-post' ); ?></span>
							</a>
						<?php } ?>
					</div>
				</div>

				<form method="post"
						class="fed_dashboard_process_edit_post_request space-y-6"
						action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=fed_dashboard_add_edit_post' ) ); ?>">

					<?php wp_nonce_field( 'fed_nonce', 'fed_nonce' ); ?>

					<?php
					// phpcs:ignore
					echo fed_get_input_details( array(
						'input_meta' => 'ID',
						'user_value' => (int) $post->ID,
						'input_type' => 'hidden',
						'id_name'    => 'fed_post_id_hidden',
					) );
					?>

					<?php
					// phpcs:ignore
					echo fed_get_input_details( array(
						'input_meta' => 'fed_post_type',
						'user_value' => $post->post_type,
						'input_type' => 'hidden',
					) );
					?>

					<?php
					// phpcs:ignore
					echo fed_get_input_details( array(
						'input_meta' => 'post_type',
						'user_value' => $post->post_type,
						'input_type' => 'hidden',
					) );
					?>

					<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
						<!-- Main Canvas (Left 8 Cols) -->
						<div class="lg:col-span-8 space-y-6">
							<!-- Post Title Card -->
							<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs space-y-2">
								<label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
									<?php esc_html_e( 'Title', 'frontend-dashboard-custom-post' ); ?> <span class="text-rose-500">*</span>
								</label>
								<?php
								// phpcs:ignore
								echo fed_input_box( 'post_title', array(
									'value'       => esc_attr( $post->post_title ),
									'placeholder' => __( 'Post Title', 'frontend-dashboard-custom-post' ),
								), 'single_line' );
								?>
							</div>

							<!-- Post Content Card -->
							<?php
							if ( ! isset( $post_settings['dashboard']['post_content'] ) ) {
								?>
								<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs space-y-3">
									<label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
										<?php esc_html_e( 'Content', 'frontend-dashboard-custom-post' ); ?>
									</label>
									<div class="rounded-xl overflow-hidden border border-slate-200">
										<?php
										wp_editor( $post->post_content, 'post_content', array(
											'quicktags' => true,
										) );
										?>
									</div>
								</div>
								<?php
							}
							?>

							<!-- Extra Fields Card -->
							<?php
							$custom_fields = array();
							foreach ( $post_table as $item ) {
								if ( $post->post_type === $item['post_type'] ) {
									$custom_fields[] = $item;
								}
							}
							if ( ! empty( $custom_fields ) ) {
								?>
								<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs space-y-4">
									<h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-3">
										<i class="fas fa-sliders-h text-indigo-500"></i>
										<span><?php esc_html_e( 'Additional Fields', 'frontend-dashboard-custom-post' ); ?></span>
									</h4>
									<div class="space-y-4">
										<?php
										foreach ( $custom_fields as $item ) {
											$temp               = $item;
											$temp['user_value'] = isset( $post_meta[ $item['input_meta'] ][0] ) ? $post_meta[ $item['input_meta'] ][0] : '';
											?>
											<div class="space-y-1.5">
												<label class="block text-xs font-semibold text-slate-700">
													<?php echo esc_html( $item['label_name'] ); ?>
												</label>
												<?php echo fed_get_input_details( $temp ); ?>
											</div>
											<?php
										}
										?>
									</div>
								</div>
								<?php
							}
							?>
						</div>

						<!-- Sidebar (Right 4 Cols) -->
						<div class="lg:col-span-4 space-y-5">
							<!-- Publishing & Status Card -->
							<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs space-y-4">
								<h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-3">
									<i class="fas fa-paper-plane text-indigo-500"></i>
									<span><?php esc_html_e( 'Publish Settings', 'frontend-dashboard-custom-post' ); ?></span>
								</h4>

								<?php
								if ( fed_is_admin() || ( ( 'draft' === $post_status || 'pending' === $post_status ) && ! fed_is_admin() ) ) {
									$default_post_status = fed_get_post_status();
									$post_status_setting = fed_get_data( 'settings.fed_post_status', $post_settings );
									if ( 'pending' === $post_status_setting && ! fed_is_admin() ) {
										unset( $default_post_status['publish'] );
									}
									if ( 'draft' === $post_status_setting && ! fed_is_admin() ) {
										$default_post_status = array( 'draft' => 'Draft' );
									}
									?>
									<div class="space-y-1.5">
										<label class="block text-xs font-semibold text-slate-700">
											<?php esc_html_e( 'Post Status', 'frontend-dashboard-custom-post' ); ?>
										</label>
										<?php
										echo fed_form_select(
											array(
												'input_value' => $default_post_status,
												'input_meta'  => 'post_status',
												'user_value'  => $post_status,
												'class_name'  => 'form-control',
											)
										);
										?>
									</div>
								<?php } ?>

								<?php
								if ( ! isset( $post_settings['dashboard']['allow_comments'] ) ) {
									?>
									<div class="pt-2 border-t border-slate-100">
										<label class="flex items-center gap-2 cursor-pointer text-xs font-medium text-slate-700">
											<?php
											echo fed_input_box( 'comment_status', array(
												'default_value' => 'open',
												'value'         => esc_attr( $post->comment_status ),
											), 'checkbox' );
											?>
											<span><?php esc_html_e( 'Allow Comments', 'frontend-dashboard-custom-post' ); ?></span>
										</label>
									</div>
									<?php
								}
								?>

								<div class="pt-3 border-t border-slate-100">
									<button class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] text-white text-xs font-bold rounded-xl shadow-xs transition-all cursor-pointer"
											type="submit">
										<i class="fa fa-save text-xs" style="color: #ffffff !important;"></i>
										<span style="color: #ffffff !important;"><?php esc_attr_e( 'Update Post', 'frontend-dashboard-custom-post' ); ?></span>
									</button>
								</div>
							</div>

							<!-- Featured Image Card -->
							<?php
							if ( ! isset( $post_settings['dashboard']['featured_image'] ) ) {
								$thumbnail = isset( $post_meta['_thumbnail_id'] ) ? (int) $post_meta['_thumbnail_id'][0] : '';
								?>
								<div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-2xs space-y-3">
									<h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
										<i class="fas fa-image text-indigo-500"></i>
										<span><?php esc_html_e( 'Featured Image', 'frontend-dashboard-custom-post' ); ?></span>
									</h4>
									<?php
									echo fed_get_input_details( array(
										'input_meta' => '_thumbnail_id',
										'user_value' => $thumbnail,
										'input_type' => 'file',
									) );
									?>
								</div>
								<?php
							}
							?>

							<!-- Taxonomies & Tags -->
							<?php $this->fed_show_category_tag_post_format( $post, $post_settings ); ?>
						</div>
					</div>
				</form>
				<?php
			} else {
				echo wp_kses_post( __( '<h2>Unauthorised Access</h2>', 'frontend-dashboard-custom-post' ) );
			}
		}
	}

	new Fed_Cp_Menu();
}
