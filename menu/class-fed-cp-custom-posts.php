<?php
/**
 * Custom Post
 *
 * @package frontend-dashboard-custom-post
 */

if ( ! class_exists( 'Fed_Cp_Custom_Posts' ) ) {
	/**
	 * Class Fed_Cp_Custom_Posts
	 */
	class Fed_Cp_Custom_Posts {
		/**
		 * FEDCP_Menu constructor.
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'fed_cp_register_custom_post' ) );
			add_filter( 'fed_add_main_sub_menu', array( $this, 'fed_cp_add_main_sub_menu' ) );
			add_action( 'wp_ajax_fed_cp_add_custom_post_type', array( $this, 'fed_cp_add_custom_post_type_store' ) );
			add_action( 'wp_ajax_fed_cp_delete_custom_post_type', array(
				$this,
				'fed_cp_delete_custom_post_type_delete',
			) );
		}

		/**
		 * Add Main Sub Menu
		 *
		 * @param  array $menu  Menu.
		 *
		 * @return mixed
		 */
		public function fed_cp_add_main_sub_menu( $menu ) {
			$menu['fed_custom_post'] = array(
				'page_title' => __( 'Custom Post', 'frontend-dashboard-custom-post' ),
				'menu_title' => __( 'Custom Post', 'frontend-dashboard-custom-post' ),
				'capability' => 'manage_options',
				'callback'   => array( $this, 'fed_admin_custom_post_layout' ),
				'position'   => 45,
			);

			return $menu;
		}

		/**
		 * Custom Post Layout.
		 */
		public function fed_admin_custom_post_layout() {
			if ( isset( $_GET['page'] ) && 'fed_custom_post' === $_GET['page'] ) {
				if ( isset( $_REQUEST['fed_type_id'] ) && ! empty( $_REQUEST['fed_type_id'] ) ) {
					/**
					 * Edit Custom Post Type by ID
					 */
					$this->fed_cp_edit_custom_post_type( $_REQUEST );
					fed_cp_custom_menu_icons_popup();
				} else {
					/**
					 * List Custom Post Type
					 */
					$this->fed_cp_add_custom_post_type();

					fed_cp_custom_menu_icons_popup();
				}
			}
		}

		/**
		 * Delete Custom Post Type
		 */
		public function fed_cp_delete_custom_post_type_delete() {
			$request = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			fed_verify_nonce( $_GET );
			$pt = get_option( 'fed_cp_custom_posts' );
			if ( ! isset( $pt[ $request['id'] ] ) ) {
				wp_send_json_error( array(
					'message' => __( 'Invalid Custom Post ID', 'frontend-dashboard-custom-post' ),
				) );
			}
			$url = admin_url() . 'admin.php?page=fed_custom_post';
			unset( $pt[ $request['id'] ] );
			update_option( 'fed_cp_custom_posts', $pt );
			wp_send_json_success( array(
				'message' => __( 'Custom Post Type Successfully Deleted', 'frontend-dashboard-custom-post' ),
				'reload'  => $url,
			) );
		}

		/**
		 * Add Custom Post Type
		 */
		public function fed_cp_add_custom_post_type_store() {
			$request      = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$redirect_url = admin_url() . 'admin.php?page=fed_custom_post';
			$status       = __( 'added', 'frontend-dashboard-custom-post' );
			/**
			 * Check for Nonce
			 */
			fed_verify_nonce( $request );
			/**
			 * Check for mandatory fields
			 */
			if ( ! isset( $request['slug'], $request['label'], $request['singular_name'] ) || fed_request_empty( $request['slug'] ) || fed_request_empty( $request['label'] ) || fed_request_empty( $request['singular_name'] ) ) {
				wp_send_json_error( array(
					'message' => __( 'Please fill mandatory fields', 'frontend-dashboard-custom-post' ),
				) );
			}
			/**
			 * Validate for same post key
			 */
			$old_cpt    = get_option( 'fed_cp_custom_posts', array() );
			$public_cpt = get_post_types();
			$merge_cpt  = array_merge( $old_cpt, $public_cpt );
			if ( ! isset( $request['fed_cpt_edit'] ) && isset( $merge_cpt[ $request['slug'] ] ) ) {
				wp_send_json_error( array(
					'message' => __( 'Custom Post Type ',
							'frontend-dashboard-custom-post' ) . $merge_cpt[ $request['slug'] ] . __( ' slug already exist',
							'frontend-dashboard-custom-post' ),
				) );
			}
			if ( isset( $request['fed_cpt_edit'] ) ) {
				$redirect_url = admin_url() . 'admin.php?page=fed_custom_post&fed_type_id=' . $request['slug'];
				$status       = __( 'updated', 'frontend-dashboard-custom-post' );
			}

			/**
			 * Validate with default fields
			 */
			$default                     = fed_cp_default_custom_post_types_key();
			$output                      = fed_compare_two_arrays_get_second_value( $default, $request );
			$old_cpt[ $request['slug'] ] = $output;


			update_option( 'fed_cp_custom_posts', $old_cpt );

			wp_send_json_success( array(
				/* translators: 1:  Custom Post Name, 2: Status */
				'message' => sprintf( __( 'Custom post type %1$s successfully %2$s', 'frontend-dashboard-custom-post' ),
					$request['label'], $status ),
				'reload'  => $redirect_url,
			) );
		}

		/**
		 * Register Custom Post
		 */
		public function fed_cp_register_custom_post() {
			$menus = get_option( 'fed_cp_custom_posts' );
			if ( $menus ) {
				foreach ( $menus as $index => $menu ) {
					$supports       = false;
					$taxonomies     = array();
					$name           = fed_request_empty( $menu['name'] ) ? $menu['singular_name'] : $menu['name'];
					$menu_name      = fed_request_empty( $menu['menu_name'] ) ? $menu['label'] : $menu['menu_name'];
					$name_admin_bar = fed_request_empty( $menu['name_admin_bar'] ) ? $menu['singular_name'] : $menu['name_admin_bar'];
					/* translators: 1:  Singular Name */
					$archives          = fed_request_empty( $menu['archives'] ) ? sprintf( __( '%1$s Archives',
						'frontend-dashboard-custom-post' ), $menu['singular_name'] ) : $menu['archives'];
					$attributes        = fed_request_empty( $menu['attributes'] ) ? __( 'Attributes',
						'frontend-dashboard-custom-post' ) : $menu['attributes'];
					$parent_item_colon = fed_request_empty( $menu['parent_item_colon'] ) ?
						__( 'Parent Page: Attributes', 'frontend-dashboard-custom-post' ) : $menu['parent_item_colon'];
					$all_items         = fed_request_empty( $menu['all_items'] ) ? __( 'All Posts',
						'frontend-dashboard-custom-post' ) : $menu['all_items'];
					$add_new_item      = fed_request_empty( $menu['add_new_item'] ) ?
						/* translators: 1:  New Item */
						sprintf( __( 'Add New %1$s', 'frontend-dashboard-custom-post' ), $name ) :
						$menu['add_new_item'];
					$add_new           = fed_request_empty( $menu['add_new'] ) ? __( 'Add New',
						'frontend-dashboard-custom-post' ) : $menu['add_new'];
					$new_item          = fed_request_empty( $menu['new_item'] ) ?
						/* translators: 1:  New Item */
						sprintf( __( 'New %1$s', 'frontend-dashboard-custom-post' ), $name ) :
						$menu['new_item'];
					$edit_item         = fed_request_empty( $menu['edit_item'] ) ?
						/* translators: 1:  Edit Item */
						sprintf( __( 'Edit %1$s', 'frontend-dashboard-custom-post' ), $name ) :
						$menu['edit_item'];
					$view_item         = fed_request_empty( $menu['view_item'] ) ?
						/* translators: 1:  View Item */
						sprintf( __( 'View %1$s', 'frontend-dashboard-custom-post' ), $name ) :
						$menu['view_item'];
					/* translators: 1:  View Items */
					$view_items            = fed_request_empty( $menu['view_items'] ) ?
						sprintf( __( 'View %1$s', 'frontend-dashboard-custom-post' ), $menu_name ) :
						$menu['view_items'];
					$search_items          = fed_request_empty( $menu['search_items'] ) ?
						/* translators: 1:  Search Items */
						sprintf( __( 'Search %1$s', 'frontend-dashboard-custom-post' ), $name ) :
						$menu['search_items'];
					$not_found             = fed_request_empty( $menu['not_found'] ) ?
						__( 'No Post Found', 'frontend-dashboard-custom-post' ) :
						$menu['not_found'];
					$not_found_in_trash    = fed_request_empty( $menu['not_found_in_trash'] ) ?
						/* translators: 1:  Items */
						sprintf( __( 'No %1$s found in Trash', 'frontend-dashboard-custom-post' ), $name ) :
						$menu['not_found_in_trash'];
					$featured_image        = fed_request_empty( $menu['featured_image'] ) ?
						__( 'Featured image', 'frontend-dashboard-custom-post' ) :
						$menu['featured_image'];
					$set_featured_image    = fed_request_empty( $menu['set_featured_image'] ) ?
						__( 'Set featured image', 'frontend-dashboard-custom-post' ) :
						$menu['set_featured_image'];
					$remove_featured_image = fed_request_empty( $menu['remove_featured_image'] ) ?
						__( 'Remove featured image', 'frontend-dashboard-custom-post' ) :
						$menu['remove_featured_image'];
					$use_featured_image    = fed_request_empty( $menu['use_featured_image'] ) ?
						__( 'Use featured image', 'frontend-dashboard-custom-post' ) :
						$menu['use_featured_image'];
					$insert_into_item      = fed_request_empty( $menu['insert_into_item'] ) ?
						/* translators: 1:  Name */
						sprintf( __( '%1$s insert into page', 'frontend-dashboard-custom-post' ), $name ) :
						$menu['insert_into_item'];
					$uploaded_to_this_item = fed_request_empty( $menu['uploaded_to_this_item'] ) ?
						__( 'Uploaded to this page', 'frontend-dashboard-custom-post' ) :
						$menu['uploaded_to_this_item'];
					$items_list            = fed_request_empty( $menu['items_list'] ) ?
						__( 'Items list', 'frontend-dashboard-custom-post' ) :
						$menu['items_list'];
					$items_list_navigation = fed_request_empty( $menu['items_list_navigation'] ) ?
						__( 'Items list navigation', 'frontend-dashboard-custom-post' ) :
						$menu['items_list_navigation'];
					$filter_items_list     = fed_request_empty( $menu['filter_items_list'] ) ?
						__( 'Filter items list', 'frontend-dashboard-custom-post' ) :
						$menu['filter_items_list'];


					$capability_type = fed_request_empty( $menu['capability_type'] ) ? 'post' : $menu['capability_type'];
					if ( fed_is_true_false( $menu['rewrite'] ) ) {
						$rewrite = true;
						if ( ! fed_request_empty( $menu['rewrite_slug'] ) ) {
							$rewrite = array( 'slug' => $menu['rewrite_slug'] );
						}
					} else {
						$rewrite = false;
					}
					if ( isset( $menu['supports'] ) ) {
						$supports = array_keys( $menu['supports'] );
					}
					if ( isset( $menu['taxonomies'] ) ) {
						$taxonomies = array_keys( $menu['taxonomies'] );
					}

					$labels = array(
						'name'                  => _x( $name, 'post type General Name',
							'frontend-dashboard-custom-post' ),
						'singular_name'         => _x( $menu['singular_name'], 'post type singular name',
							'frontend-dashboard-custom-post' ),
						/* translators: 1: menu name */
						'menu_name'             => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ),
							$menu_name ),
						/* translators: 1:  name admin bar */
						'name_admin_bar'        => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ),
							$name_admin_bar ),
						/* translators: 1:  archives */
						'archives'              => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ), $archives ),
						/* translators: 1:  attributes */
						'attributes'            => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ),
							$attributes ),
						/* translators: 1:  parent_item_colon */
						'parent_item_colon'     => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ),
							$parent_item_colon ),
						/* translators: 1:  all_items */
						'all_items'             => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ),
							$all_items ),
						/* translators: 1:  add_new_item */
						'add_new_item'          => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ),
							$add_new_item ),
						/* translators: 1:  add_new */
						'add_new'               => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ), $add_new ),
						/* translators: 1:  new_item */
						'new_item'              => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ), $new_item ),
						/* translators: 1: edit_item */
						'edit_item'             => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ),
							$edit_item ),
						/* translators: 1:  view_item */
						'view_item'             => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ),
							$view_item ),
						/* translators: 1:  view_items */
						'view_items'            => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ),
							$view_items ),
						/* translators: 1:  search_items */
						'search_items'          => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ),
							$search_items ),
						/* translators: 1:  not_found */
						'not_found'             => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ),
							$not_found ),
						/* translators: 1:  not_found_in_trash */
						'not_found_in_trash'    => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ),
							$not_found_in_trash ),
						/* translators: 1:  featured_image */
						'featured_image'        => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ),
							$featured_image ),
						/* translators: 1:  set_featured_image */
						'set_featured_image'    => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ),
							$set_featured_image ),
						/* translators: 1:  remove_featured_image */
						'remove_featured_image' => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ),
							$remove_featured_image ),
						/* translators: 1:  use_featured_image */
						'use_featured_image'    => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ),
							$use_featured_image ),
						/* translators: 1:  insert_into_item */
						'insert_into_item'      => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ),
							$insert_into_item ),
						/* translators: 1:  uploaded_to_this_item */
						'uploaded_to_this_item' => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ),
							$uploaded_to_this_item ),
						/* translators: 1:  items_list */
						'items_list'            => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ),
							$items_list ),
						/* translators: 1:  items_list_navigation */
						'items_list_navigation' => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ),
							$items_list_navigation ),
						/* translators: 1:  filter_items_list */
						'filter_items_list'     => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ),
							$filter_items_list ),
					);
					$args   = array(
						/* translators: 1:  label */
						'label'               => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ),
							$menu['label'] ),
						/* translators: 1: description */
						'description'         => sprintf( __( '%1$s', 'frontend-dashboard-custom-post' ),
							$menu['description'] ),
						'labels'              => $labels,
						'supports'            => $supports,
						'hierarchical'        => fed_is_true_false( $menu['hierarchical'] ),
						'public'              => fed_is_true_false( $menu['public'] ),
						'show_ui'             => fed_is_true_false( $menu['show_ui'] ),
						'show_in_menu'        => fed_is_true_false( $menu['show_in_menu'] ),
						'menu_position'       => $menu['menu_position'],
						'show_in_admin_bar'   => fed_is_true_false( $menu['show_in_admin_bar'] ),
						'show_in_nav_menus'   => fed_is_true_false( $menu['show_in_nav_menus'] ),
						'can_export'          => fed_is_true_false( $menu['can_export'] ),
						'has_archive'         => fed_is_true_false( $menu['has_archive'] ),
						'exclude_from_search' => fed_is_true_false( $menu['exclude_from_search'] ),
						'publicly_queryable'  => fed_is_true_false( $menu['publicly_queryable'] ),
						'capability_type'     => $capability_type,
						'menu_icon'           => $menu['menu_icon'],
						'rewrite'             => $rewrite,
						'query_var'           => fed_is_true_false( $menu['query_var'] ),
						'delete_with_user'    => fed_is_true_false( $menu['delete_with_user'] ),
						'show_in_rest'        => fed_is_true_false( $menu['show_in_rest'] ),
						'rest_base'           => $menu['rest_base'],
						'taxonomies'          => $taxonomies,
					);
					register_post_type( $menu['slug'], $args );
				}
			}
		}

		/**
		 * Add Custom Post Type
		 */
		protected function fed_cp_add_custom_post_type() {

			$cpt = fed_cp_get_custom_post_types();
			$pt  = get_option( 'fed_cp_custom_posts' );
			?>
			<div class="bc_fed container">
				<!-- Show Empty form to add Dashboard Menu-->
				<?php if ( isset( $_GET['error'] ) && $_GET['error'] === 'invalid_post_type' ) { ?>
					<div class="row padd_top_20">
						<div class="alert alert-danger">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
							<strong><?php _e( 'Sorry! You are trying to get the invalid Custom Post Type, Please add
								new one', 'frontend-dashboard-custom-post' ); ?></strong>
						</div>
					</div>
				<?php } ?>
				<div class="row padd_top_20">
					<div class="col-md-9">
						<form method="post"
								class="fed_admin_menu fed_ajax"
								action="<?php echo admin_url( 'admin-ajax.php?action=fed_cp_add_custom_post_type' ) ?>">

							<?php wp_nonce_field( 'fed_nonce', 'fed_nonce' ) ?>

							<?php echo fed_loader(); ?>
							<?php $this->fed_cp_custom_post_type_form( $cpt, 'Add' ); ?>
						</form>
					</div>
					<div class="col-md-3">
						<?php
						$this->fed_cp_custom_post_type_sidebar( $pt );
						?>
					</div>
				</div>
			</div>

			<?php
		}

		/**
		 * Edit Custom Post Type
		 *
		 * @param  array $request  Request.
		 */
		protected function fed_cp_edit_custom_post_type( $request ) {
			$pt = get_option( 'fed_cp_custom_posts' );
			if ( ! isset( $pt[ $request['fed_type_id'] ] ) ) {
				$url = menu_page_url( 'fed_custom_post', false ) . '&error=invalid_post_type';
				wp_safe_redirect( $url );
			}
			$cpt = fed_cp_get_custom_post_types( $pt[ $request['fed_type_id'] ] );
			?>
			<div class="bc_fed container">
				<!-- Show Empty form to add Dashboard Menu-->
				<?php if ( isset( $_GET['error'] ) && $_GET['error'] === 'invalid_post_type' ) { ?>
					<div class="row padd_top_20">
						<div class="alert alert-danger">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
							<strong><?php _e( 'Sorry! You are trying to delete the invalid Custom Post Type',
									'frontend-dashboard-custom-post' ); ?></strong>
						</div>
					</div>
				<?php } ?>
				<div class="row padd_top_20">
					<div class="col-md-9">
						<form method="post"
								class="fed_admin_menu fed_ajax"
								action="<?php echo admin_url( 'admin-ajax.php?action=fed_cp_add_custom_post_type' ) ?>">

							<?php wp_nonce_field( 'fed_nonce', 'fed_nonce' ) ?>

							<?php echo fed_loader(); ?>
							<input type="hidden" name="fed_cpt_edit" value="yes"/>
							<?php $this->fed_cp_custom_post_type_form( $cpt, 'Edit' ); ?>
						</form>
					</div>
					<div class="col-md-3">
						<?php
						$this->fed_cp_custom_post_type_sidebar( $pt );
						?>
					</div>
				</div>
			</div>

			<?php
		}

		/**
		 * Custom POst Type Form
		 *
		 * @param  array  $cpt  Custom Post Type.
		 * @param  string $type  Type.
		 */
		protected function fed_cp_custom_post_type_form( $cpt, $type ) {
			$cpt_name   = '';
			$delete_btn = '';
			if ( 'Edit' === $type ) {
				$cpt_name   = isset( $cpt['Basic Settings']['label']['input']['user_value'] ) ? $cpt['Basic Settings']['label']['input']['user_value'] : '';
				$cpt_index  = isset( $cpt['Basic Settings']['slug']['input']['user_value'] ) ? $cpt['Basic Settings']['slug']['input']['user_value'] : '';
				$delete_btn = '<div data-toggle="popover" data-url=' . admin_url( 'admin-ajax.php?fed_nonce=' . wp_create_nonce( 'fed_nonce' ) . '&action=fed_cp_delete_custom_post_type' ) . ' data-id="' . $cpt_index . '" class="btn btn-danger fd_cp_custom_post_delete"> <i class="fa fa-trash fa-2x" aria-hidden="true"></i></div>';
			}
			?>
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">
						<b><?php echo $type; ?>
							<?php _e( 'Custom Post',
								'frontend-dashboard-custom-post' ); ?>
							<?php echo $cpt_name; ?></b>
						<span class="pull-right m-t-5">
											<a href="<?php echo menu_page_url( 'fed_custom_post',
												false ) ?>" class="fed_add_new_custom_post">
												<i class="fa fa-plus"></i>
												<?php _e( 'Add New Custom Post', 'frontend-dashboard-custom-post' ) ?>
											</a>
										</span>
					</h3>
				</div>
				<div class="panel-body">
					<div class="text-right p-b-10">
						<button type="submit" class="btn btn-danger">
							<i class="fa fa-save fa-2x" aria-hidden="true"></i>
						</button>
						<?php echo $delete_btn; ?>
					</div>
					<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
						<?php
						$first = 0;
						foreach ( $cpt as $index => $post_types ) {
							$id = sanitize_title( $index );
							$in = 0 === $first ? 'in' : '';
							?>
							<div class="panel panel-primary">
								<div class="panel-heading" id="<?php echo $id; ?>" role="button" data-toggle="collapse"
										data-parent="#accordion" href="#collapse<?php echo $id; ?>" aria-expanded="true"
										aria-controls="collapse<?php echo $id; ?>">
									<h4 class="panel-title">
										<?php echo $index; ?>
									</h4>
								</div>
								<div id="collapse<?php echo $id; ?>" class="panel-collapse collapse <?php echo $in; ?>"
										role="tabpanel" aria-labelledby="<?php echo $id; ?>">
									<div class="panel-body">
										<?php foreach ( $post_types as $pindex => $post_type ) { ?>
											<div class="col-md-6">
												<div class="form-group">
													<?php if ( $post_type['name'] !== null ) { ?>
														<label>
															<?php echo isset( $post_type['required'] ) ? '<span class="bg-red-font">' . $post_type['name'] . '</span>' : $post_type['name']; ?>
															<?php echo isset( $post_type['help_message'] ) ? $post_type['help_message'] : '' ?>
														</label>
													<?php } ?>
													<?php
													if ( 'Edit' === $type && $pindex === 'slug' ) {
														$post_type['input']['readonly'] = true;
														echo fed_get_input_details( $post_type['input'] );
													} else {
														echo fed_get_input_details( $post_type['input'] );
													}

													?>
												</div>
											</div>
										<?php } ?>
									</div>
								</div>
							</div>
							<?php
							$first ++;
						}
						?>
					</div>
					<div class="text-right">
						<button type="submit" class="btn btn-danger">
							<i class="fa fa-save fa-2x" aria-hidden="true"></i>
						</button>
						<?php echo $delete_btn; ?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Custom Post Type Sidebar
		 *
		 * @param  array $pt  Post Type.
		 */
		protected function fed_cp_custom_post_type_sidebar( $pt ) {
			?>
			<div class="row p-b-20">
				<div href="#" class="col-md-12 btn btn-warning">
					<?php _e( 'Custom Post Types', 'frontend-dashboard-custom-post' ); ?>
				</div>
			</div>

			<?php
			if ( $pt ) {
				?>
				<div class="list-group">
					<?php
					foreach ( $pt as $index => $post ) {
						$active = ( isset( $_GET['fed_type_id'] ) && $_GET['fed_type_id'] === $index ) ? 'active' : ''
						?>
						<a href="<?php echo menu_page_url( 'fed_custom_post',
								false ) . '&fed_type_id=' . $index ?>" class="list-group-item <?php echo $active; ?>">
							<?php echo $post['label']; ?>
						</a>

						<?php
					}
					?>
				</div>
				<?php

			} else {
				?>
				<div class="row">
					<div class="col-md-12">
						<a href="#" class="list-group-item">
							<?php echo __( 'No Custom Post Type Added', 'frontend-dashboard-custom-post' ) ?>
						</a>
					</div>
				</div>
				<?php
			}

		}
	}

	new Fed_Cp_Custom_Posts();
}