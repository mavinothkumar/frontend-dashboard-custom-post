<?php
/**
 * Custom Post Taxonomies
 *
 * @package frontend-dashboard-custom-post
 */

if ( ! class_exists( 'Fed_Cp_Taxonomies' ) ) {
	/**
	 * Class Fed_Cp_Taxonomies
	 */
	class Fed_Cp_Taxonomies {
		/**
		 * FEDCP_Menu constructor.
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'fed_cp_register_custom_taxonomies' ) );
			add_filter( 'fed_add_main_sub_menu', array( $this, 'fed_cp_add_taxonomies_menu' ) );
			add_action( 'wp_ajax_fed_cp_add_custom_taxonomies', array( $this, 'fed_cp_add_custom_taxonomies' ) );
			add_action( 'wp_ajax_fed_cp_delete_custom_taxonomies_type', array(
				$this,
				'fed_cp_delete_custom_taxonomies_type_delete',
			) );
		}

		/**
		 * Add Taxonomies Menu
		 *
		 * @param  array $menu  Menu.
		 *
		 * @return mixed
		 */
		public function fed_cp_add_taxonomies_menu( $menu ) {
			$menu['fed_taxonomies'] = array(
				'page_title' => __( 'Custom Taxonomies', 'frontend-dashboard-custom-post' ),
				'menu_title' => __( 'Custom Taxonomies', 'frontend-dashboard-custom-post' ),
				'capability' => 'manage_options',
				'callback'   => array( $this, 'fed_admin_taxonomies_layout' ),
				'position'   => 46,
			);

			return $menu;
		}

		/**
		 * Admin Taxonomies Layout
		 */
		public function fed_admin_taxonomies_layout() {
			if ( isset( $_GET['page'] ) && 'fed_taxonomies' === $_GET['page'] ) {
				if ( isset( $_REQUEST['fed_type_id'] ) && ! empty( $_REQUEST['fed_type_id'] ) ) {
					/**
					 * Edit Custom Post Type by ID
					 */
					$this->fed_cp_edit_custom_taxonomies( $_REQUEST );
				} else {
					/**
					 * List Custom Post Type
					 */
					$this->fed_cp_add_custom_taxonomies_type();
				}
			}
		}

		/**
		 * Delete Custom Taxonomies Type
		 */
		public function fed_cp_delete_custom_taxonomies_type_delete() {
			$request = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			fed_verify_nonce( $_GET );
			$pt = get_option( 'fed_cp_custom_taxonomies' );
			if ( ! isset( $pt[ $request['id'] ] ) ) {
				wp_send_json_error( array(
					'message' => __( 'Invalid Custom Post ID', 'frontend-dashboard-custom-post' ),
				) );
			}
			$url = admin_url() . 'admin.php?page=fed_taxonomies';
			unset( $pt[ $request['id'] ] );
			update_option( 'fed_cp_custom_taxonomies', $pt );
			wp_send_json_success( array(
				'message' => __( 'Custom Post Type Successfully Deleted', 'frontend-dashboard-custom-post' ),
				'reload'  => $url,
			) );
		}

		/**
		 * Add Custom Taxonomies
		 */
		public function fed_cp_add_custom_taxonomies() {
			$request      = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$redirect_url = admin_url() . 'admin.php?page=fed_taxonomies';
			$status       = 'added';
			/**
			 * Check for Nonce
			 */
			fed_verify_nonce( $request );
			/**
			 * Check for mandatory fields
			 */
			if ( ! is_array( $request['object_type'] ) || ! isset( $request['slug'], $request['label'], $request['singular_name'], $request['object_type'] ) || fed_request_empty( $request['slug'] ) || fed_request_empty( $request['label'] ) || fed_request_empty( $request['singular_name'] ) ) {
				wp_send_json_error( array(
					'message' => __( 'Please fill mandatory fields', 'frontend-dashboard-custom-post' ),
				) );
			}
			/**
			 * Validate for same post key
			 */
			$old_cpt    = get_option( 'fed_cp_custom_taxonomies', array() );
			$public_cpt = get_post_types();
			$merge_cpt  = array_merge( $old_cpt, $public_cpt );
			if ( ! isset( $request['fed_cpt_edit'] ) && isset( $merge_cpt[ $request['slug'] ] ) ) {
				wp_send_json_error( array(
					'message' => $merge_cpt[ $request['slug'] ] . __( ' Custom Post Type slug already exist',
							'frontend-dashboard-custom-post' ),
				) );
			}
			if ( isset( $request['fed_cpt_edit'] ) ) {
				$redirect_url = admin_url() . 'admin.php?page=fed_taxonomies&fed_type_id=' . $request['slug'];
				$status       = 'updated';
			}

			/**
			 * Validate with default fields
			 */
			$default                     = fed_cp_default_taxonomies_key();
			$output                      = fed_compare_two_arrays_get_second_value( $default, $request );
			$old_cpt[ $request['slug'] ] = $output;


			update_option( 'fed_cp_custom_taxonomies', $old_cpt );

			wp_send_json_success( array(
				/* translators: 1:  Taxonomy Name, 2: Status */
				'message' => sprintf( __( 'Taxonomy %1$s successfully %2$s', 'frontend-dashboard-custom-post' ),
					$request['label'], $status ),
				'reload'  => $redirect_url,
			) );
		}

		/**
		 * Register Custom Taxonomies
		 */
		public function fed_cp_register_custom_taxonomies() {
			$menus = get_option( 'fed_cp_custom_taxonomies' );
			if ( $menus ) {
				foreach ( $menus as $index => $menu ) {
					$name              = fed_request_empty( $menu['name'] ) ? $menu['singular_name'] : $menu['name'];
					$menu_name         = fed_request_empty( $menu['menu_name'] ) ? $menu['label'] : $menu['menu_name'];
					$parent_item_colon = fed_request_empty( $menu['parent_item_colon'] ) ? 'Parent Page:' . ' Attributes' : $menu['parent_item_colon'];
					$all_items         = fed_request_empty( $menu['all_items'] ) ? __( 'All Posts',
						'frontend-dashboard-custom-post' ) : $menu['all_items'];
					$add_new_item      = fed_request_empty( $menu['add_new_item'] ) ? 'Add New ' . $name : $menu['add_new_item'];
					$edit_item         = fed_request_empty( $menu['edit_item'] ) ? 'Edit ' . $name : $menu['edit_item'];
					$view_item         = fed_request_empty( $menu['view_item'] ) ? 'View ' . $name : $menu['view_item'];

					$update_item                = fed_request_empty( $menu['update_item'] ) ? 'Update ' . $name : $menu['update_item'];
					$new_item_name              = fed_request_empty( $menu['new_item_name'] ) ? 'New ' . $name : $menu['new_item_name'];
					$popular_items              = fed_request_empty( $menu['popular_items'] ) ? 'Popular ' . $name : $menu['popular_items'];
					$choose_from_most_used      = fed_request_empty( $menu['choose_from_most_used'] ) ? 'Choose from most used ' . $name : $menu['choose_from_most_used'];
					$add_or_remove_items        = fed_request_empty( $menu['add_or_remove_items'] ) ? 'Add or Remove ' . $name : $menu['add_or_remove_items'];
					$separate_items_with_commas = fed_request_empty( $menu['separate_items_with_commas'] ) ? 'Separate ' . $name . ' with commas ' : $menu['separate_items_with_commas'];

					$search_items = fed_request_empty( $menu['search_items'] ) ? 'Search ' . $name : $menu['search_items'];
					$not_found    = fed_request_empty( $menu['not_found'] ) ? 'No Post Found' : $menu['not_found'];


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
						'name'                       => _x( $name, 'post type General Name',
							'frontend-dashboard-custom-post' ),
						'singular_name'              => _x( $menu['singular_name'], 'post type singular name',
							'frontend-dashboard-custom-post' ),
						'menu_name'                  => __( $menu_name, 'frontend-dashboard-custom-post' ),
						'parent_item_colon'          => __( $parent_item_colon, 'frontend-dashboard-custom-post' ),
						'all_items'                  => __( $all_items, 'frontend-dashboard-custom-post' ),
						'add_new_item'               => __( $add_new_item, 'frontend-dashboard-custom-post' ),
						'edit_item'                  => __( $edit_item, 'frontend-dashboard-custom-post' ),
						'update_item'                => __( $update_item, 'frontend-dashboard-custom-post' ),
						'new_item_name'              => __( $new_item_name, 'frontend-dashboard-custom-post' ),
						'popular_items'              => __( $popular_items, 'frontend-dashboard-custom-post' ),
						'choose_from_most_used'      => __( $choose_from_most_used,
							'frontend-dashboard-custom-post' ),
						'add_or_remove_items'        => __( $add_or_remove_items, 'frontend-dashboard-custom-post' ),
						'separate_items_with_commas' => __( $separate_items_with_commas,
							'frontend-dashboard-custom-post' ),
						'view_item'                  => __( $view_item, 'frontend-dashboard-custom-post' ),
						'search_items'               => __( $search_items, 'frontend-dashboard-custom-post' ),
						'not_found'                  => __( $not_found, 'frontend-dashboard-custom-post' ),
					);
					$args   = array(
						'label'              => __( $menu['label'], 'frontend-dashboard-custom-post' ),
						'description'        => __( $menu['description'], 'frontend-dashboard-custom-post' ),
						'labels'             => $labels,
						'hierarchical'       => fed_is_true_false( $menu['hierarchical'] ),
						'show_tagcloud'      => fed_is_true_false( $menu['show_tagcloud'] ),
						'show_in_quick_edit' => fed_is_true_false( $menu['show_in_quick_edit'] ),
						'show_admin_column'  => fed_is_true_false( $menu['show_admin_column'] ),
						'sort'               => fed_is_true_false( $menu['sort'] ),
						'public'             => fed_is_true_false( $menu['public'] ),
						'show_ui'            => fed_is_true_false( $menu['show_ui'] ),
						'show_in_menu'       => fed_is_true_false( $menu['show_in_menu'] ),
						'show_in_nav_menus'  => fed_is_true_false( $menu['show_in_nav_menus'] ),
						'publicly_queryable' => fed_is_true_false( $menu['publicly_queryable'] ),
						'rewrite'            => $rewrite,
						'query_var'          => fed_is_true_false( $menu['query_var'] ),
						'show_in_rest'       => fed_is_true_false( $menu['show_in_rest'] ),
						'rest_base'          => $menu['rest_base'],
					);

					register_taxonomy( $menu['slug'], array_keys( $menu['object_type'] ), $args );
				}
			}
		}

		/**
		 * Add Custom Taxonomies Type
		 */
		protected function fed_cp_add_custom_taxonomies_type() {
			$cpt = fed_cp_get_taxonomies_label();
			$pt  = get_option( 'fed_cp_custom_taxonomies', false );
			?>
			<div class="bc_fed container">
				<!-- Show Empty form to add Dashboard Menu-->
				<?php if ( isset( $_GET['error'] ) && $_GET['error'] === 'invalid_taxonomies' ) { ?>
					<div class="row padd_top_20">
						<div class="alert alert-danger">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
							<strong><?php _e( 'Sorry! You are trying to get the invalid Taxonomies, Please add new one',
									'frontend-dashboard-custom-post' ); ?></strong>
						</div>
					</div>
				<?php } ?>
				<div class="row padd_top_20">
					<div class="col-md-9">
						<form method="post"
								class="fed_admin_menu fed_ajax"
								action="<?php echo admin_url( 'admin-ajax.php?action=fed_cp_add_custom_taxonomies' ) ?>">

							<?php wp_nonce_field( 'fed_nonce', 'fed_nonce' ) ?>

							<?php echo fed_loader(); ?>
							<?php $this->fed_cp_custom_taxonomies_type_form( $cpt, 'Add' ); ?>
						</form>
					</div>
					<div class="col-md-3">
						<?php
						$this->fed_cp_custom_taxonomies_type_sidebar( $pt );
						?>
					</div>
				</div>
			</div>

			<?php
		}

		/**
		 * Edit Custom Taxonomies
		 *
		 * @param  array $request  Request.
		 */
		protected function fed_cp_edit_custom_taxonomies( $request ) {
			$pt = get_option( 'fed_cp_custom_taxonomies' );
			if ( ! isset( $pt[ $request['fed_type_id'] ] ) ) {
				$url = menu_page_url( 'fed_taxonomies', false ) . '&error=invalid_post_type';
				wp_safe_redirect( $url );
			}
			$cpt = fed_cp_get_taxonomies_label( $pt[ $request['fed_type_id'] ] );
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
								action="<?php echo admin_url( 'admin-ajax.php?action=fed_cp_add_custom_taxonomies' ) ?>">

							<?php wp_nonce_field( 'fed_nonce', 'fed_nonce' ) ?>

							<?php echo fed_loader(); ?>
							<input type="hidden" name="fed_cpt_edit" value="yes"/>
							<?php $this->fed_cp_custom_taxonomies_type_form( $cpt, 'Edit' ); ?>
						</form>
					</div>
					<div class="col-md-3">
						<?php
						$this->fed_cp_custom_taxonomies_type_sidebar( $pt );
						?>
					</div>
				</div>
			</div>

			<?php
		}

		/**
		 * Custom Taxonomies Type form
		 *
		 * @param  array  $cpt  Custom Post Type.
		 * @param  string $type  Type.
		 */
		protected function fed_cp_custom_taxonomies_type_form( $cpt, $type ) {
			$cpt_name   = '';
			$delete_btn = '';
			if ( 'Edit' === $type ) {
				$cpt_name   = isset( $cpt['Basic Settings']['label']['input']['user_value'] ) ? $cpt['Basic Settings']['label']['input']['user_value'] : '';
				$cpt_index  = isset( $cpt['Basic Settings']['slug']['input']['user_value'] ) ? $cpt['Basic Settings']['slug']['input']['user_value'] : '';
				$delete_btn = '<div data-toggle="popover" data-url=' . admin_url( 'admin-ajax.php?fed_nonce=' . wp_create_nonce( 'fed_nonce' ) . '&action=fed_cp_delete_custom_taxonomies_type' ) . ' data-id="' . $cpt_index . '" 
class="btn btn-danger fd_cp_custom_post_delete"> <i class="fa fa-trash fa-2x" aria-hidden="true"></i></div>';
			}
			?>
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">
						<b><?php echo $type; ?> Taxonomy <?php echo $cpt_name; ?></b>
						<span class="pull-right m-t-5">
											<a href="<?php echo menu_page_url( 'fed_taxonomies',
												false ) ?>" class="fed_add_new_custom_post">
												<i class="fa fa-plus"></i>
												<?php _e( 'Add New Taxonomies', 'frontend-dashboard-custom-post' ) ?>
											</a>
										</span>
					</h3>
				</div>
				<div class="panel-body">
					<div class="text-right p-b-10">
						<button type="submit" class="btn  btn-danger">
							<i class="fa fa-save fa-2x" aria-hidden="true"></i>
						</button>
						<?php echo $delete_btn; ?>
					</div>
					<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
						<?php
						$first = 0;
						foreach ( $cpt as $index => $taxs ) {
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
										<?php foreach ( $taxs as $key => $tax ) {
											if ( $key === 'fed_extra' && is_array( $tax ) ) {
												?>
												<div class="col-md-12">
													<div class="row">
														<div class="col-md-12">
															<label>
																<?php echo isset( $tax['required'] ) ? '<span class="bg-red-font">' . $tax['label'] . '</span>' : $tax['name']; ?>
																<?php echo isset( $tax['help_message'] ) ? $tax['help_message'] : '' ?>
															</label>
														</div>
														<?php
														foreach ( $tax['input'] as $k => $extra ) {
															?>
															<div class="col-md-4">
																<div class="form-group">
																	<?php if ( $extra['name'] !== null ) { ?>
																		<label>
																			<?php echo isset( $extra['required'] ) ? '<span class="bg-red-font">' . $extra['name'] . '</span>' : $extra['name']; ?>
																			<?php echo isset( $extra['help_message'] ) ? $extra['help_message'] : '' ?>
																		</label>
																	<?php } ?>
																	<?php echo fed_get_input_details( $extra['input'] ) ?>
																</div>
															</div>
															<?php
														}
														?>
													</div>
												</div>
												<?php
											} else {
												?>
												<div class="col-md-6">
													<div class="form-group">
														<?php if ( $tax['name'] !== null ) { ?>
															<label>
																<?php echo isset( $tax['required'] ) ? '<span class="bg-red-font">' . $tax['name'] . '</span>' : $tax['name']; ?>
																<?php echo isset( $tax['help_message'] ) ? $tax['help_message'] : '' ?>
															</label>
														<?php } ?>
														<?php
														if ( 'Edit' === $type && $key === 'slug' ) {
															$tax['input']['readonly'] = true;
															echo fed_get_input_details( $tax['input'] );
														} else {
															echo fed_get_input_details( $tax['input'] );
														}
														?>
													</div>
												</div>
												<?php
											}
										} ?>
									</div>
								</div>
							</div>
							<?php
							$first ++;
						}
						?>
					</div>
					<div class="text-right">
						<button type="submit" class="btn  btn-danger">
							<i class="fa fa-save fa-2x" aria-hidden="true"></i>
						</button>
						<?php echo $delete_btn; ?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Custom Taxonomies Type Sidebar
		 *
		 * @param  array $taxs  Taxonomies.
		 */
		protected function fed_cp_custom_taxonomies_type_sidebar( $taxs ) {
			?>
			<div class="row p-b-20">
				<div href="#" class="col-md-12 btn btn-warning">
					<?php echo __( 'Custom Taxonomies', 'frontend-dashboard-custom-post' ); ?>
				</div>
			</div>

			<?php
			if ( $taxs ) {
				?>
				<div class="list-group">
					<?php
					foreach ( $taxs as $index => $tax ) {
						$active = ( isset( $_GET['fed_type_id'] ) && $_GET['fed_type_id'] === $index ) ? 'active' : ''
						?>
						<a href="<?php echo menu_page_url( 'fed_taxonomies',
								false ) . '&fed_type_id=' . $index ?>"
								class="list-group-item <?php echo $active; ?>">
							<?php echo $tax['label']; ?>
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
							<?php _e( 'No Custom Taxonomies Added', 'frontend-dashboard-custom-post' ) ?>
						</a>
					</div>
				</div>
				<?php
			}

		}
	}

	new Fed_Cp_Taxonomies();
}