<?php
/**
 * Custom Post Taxonomies
 *
 * @package frontend-dashboard-custom-post
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
		 * @param  array $menu Menu.
		 * @return array
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
					$this->fed_cp_edit_custom_taxonomies( $_REQUEST );
				} else {
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
			$pt = get_option( 'fed_cp_custom_taxonomies', array() );
			if ( ! isset( $pt[ $request['id'] ] ) ) {
				wp_send_json_error( array(
					'message' => __( 'Invalid Custom Taxonomy ID', 'frontend-dashboard-custom-post' ),
				) );
			}
			$url = admin_url( 'admin.php?page=fed_taxonomies' );
			unset( $pt[ $request['id'] ] );
			update_option( 'fed_cp_custom_taxonomies', $pt );
			wp_send_json_success( array(
				'message' => __( 'Custom Taxonomy Successfully Deleted', 'frontend-dashboard-custom-post' ),
				'reload'  => $url,
			) );
		}

		/**
		 * Add / Update Custom Taxonomies
		 */
		public function fed_cp_add_custom_taxonomies() {
			$request      = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$redirect_url = admin_url( 'admin.php?page=fed_taxonomies' );
			$status       = __( 'added', 'frontend-dashboard-custom-post' );

			fed_verify_nonce( $request );

			if ( ! isset( $request['object_type'] ) || ! is_array( $request['object_type'] ) || ! isset( $request['slug'], $request['label'], $request['singular_name'] ) || fed_request_empty( $request['slug'] ) || fed_request_empty( $request['label'] ) || fed_request_empty( $request['singular_name'] ) ) {
				wp_send_json_error( array(
					'message' => __( 'Please fill mandatory fields (Slug, Plural Name, Singular Name, and select at least one Post Type)', 'frontend-dashboard-custom-post' ),
				) );
			}

			$old_cpt    = get_option( 'fed_cp_custom_taxonomies', array() );
			$public_cpt = get_taxonomies();
			$merge_cpt  = array_merge( $old_cpt, $public_cpt );

			if ( ! isset( $request['fed_cpt_edit'] ) && isset( $merge_cpt[ $request['slug'] ] ) ) {
				wp_send_json_error( array(
					'message' => sprintf( __( 'Custom Taxonomy slug "%s" already exists.', 'frontend-dashboard-custom-post' ), esc_html( $request['slug'] ) ),
				) );
			}

			if ( isset( $request['fed_cpt_edit'] ) ) {
				$redirect_url = admin_url( 'admin.php?page=fed_taxonomies&fed_type_id=' . $request['slug'] );
				$status       = __( 'updated', 'frontend-dashboard-custom-post' );
			}

			$default                     = fed_cp_default_taxonomies_key();
			$output                      = fed_compare_two_arrays_get_second_value( $default, $request );
			$old_cpt[ $request['slug'] ] = $output;

			update_option( 'fed_cp_custom_taxonomies', $old_cpt );

			wp_send_json_success( array(
				'message' => sprintf( __( 'Taxonomy "%1$s" successfully %2$s.', 'frontend-dashboard-custom-post' ), $request['label'], $status ),
				'reload'  => $redirect_url,
			) );
		}

		/**
		 * Register Custom Taxonomies
		 */
		public function fed_cp_register_custom_taxonomies() {
			$menus = get_option( 'fed_cp_custom_taxonomies' );
			if ( $menus && is_array( $menus ) ) {
				foreach ( $menus as $index => $menu ) {
					$name              = fed_request_empty( $menu['name'] ) ? $menu['singular_name'] : $menu['name'];
					$menu_name         = fed_request_empty( $menu['menu_name'] ) ? $menu['label'] : $menu['menu_name'];
					$parent_item_colon = fed_request_empty( $menu['parent_item_colon'] ) ? 'Parent Page: Attributes' : $menu['parent_item_colon'];
					$all_items         = fed_request_empty( $menu['all_items'] ) ? __( 'All Posts', 'frontend-dashboard-custom-post' ) : $menu['all_items'];
					$add_new_item      = fed_request_empty( $menu['add_new_item'] ) ? 'Add New ' . $name : $menu['add_new_item'];
					$edit_item         = fed_request_empty( $menu['edit_item'] ) ? 'Edit ' . $name : $menu['edit_item'];
					$view_item         = fed_request_empty( $menu['view_item'] ) ? 'View ' . $name : $menu['view_item'];
					$update_item                = fed_request_empty( $menu['update_item'] ) ? 'Update ' . $name : $menu['update_item'];
					$new_item_name              = fed_request_empty( $menu['new_item_name'] ) ? 'New ' . $name : $menu['new_item_name'];
					$popular_items              = fed_request_empty( $menu['popular_items'] ) ? 'Popular ' . $name : $menu['popular_items'];
					$choose_from_most_used      = fed_request_empty( $menu['choose_from_most_used'] ) ? 'Choose from most used ' . $name : $menu['choose_from_most_used'];
					$add_or_remove_items        = fed_request_empty( $menu['add_or_remove_items'] ) ? 'Add or Remove ' . $name : $menu['add_or_remove_items'];
					$separate_items_with_commas = fed_request_empty( $menu['separate_items_with_commas'] ) ? 'Separate ' . $name . ' with commas' : $menu['separate_items_with_commas'];
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

					$labels = array(
						'name'                       => _x( $name, 'post type General Name', 'frontend-dashboard-custom-post' ),
						'singular_name'              => _x( $menu['singular_name'], 'post type singular name', 'frontend-dashboard-custom-post' ),
						'menu_name'                  => __( $menu_name, 'frontend-dashboard-custom-post' ),
						'parent_item_colon'          => __( $parent_item_colon, 'frontend-dashboard-custom-post' ),
						'all_items'                  => __( $all_items, 'frontend-dashboard-custom-post' ),
						'add_new_item'               => __( $add_new_item, 'frontend-dashboard-custom-post' ),
						'edit_item'                  => __( $edit_item, 'frontend-dashboard-custom-post' ),
						'update_item'                => __( $update_item, 'frontend-dashboard-custom-post' ),
						'new_item_name'              => __( $new_item_name, 'frontend-dashboard-custom-post' ),
						'popular_items'              => __( $popular_items, 'frontend-dashboard-custom-post' ),
						'choose_from_most_used'      => __( $choose_from_most_used, 'frontend-dashboard-custom-post' ),
						'add_or_remove_items'        => __( $add_or_remove_items, 'frontend-dashboard-custom-post' ),
						'separate_items_with_commas' => __( $separate_items_with_commas, 'frontend-dashboard-custom-post' ),
						'view_item'                  => __( $view_item, 'frontend-dashboard-custom-post' ),
						'search_items'               => __( $search_items, 'frontend-dashboard-custom-post' ),
						'not_found'                  => __( $not_found, 'frontend-dashboard-custom-post' ),
					);

					$args = array(
						'labels'                => $labels,
						'hierarchical'          => fed_is_true_false( $menu['hierarchical'] ),
						'public'                => fed_is_true_false( $menu['public'] ),
						'show_ui'               => fed_is_true_false( $menu['show_ui'] ),
						'show_admin_column'     => fed_is_true_false( $menu['show_admin_column'] ),
						'show_in_nav_menus'     => fed_is_true_false( $menu['show_in_nav_menus'] ),
						'show_tagcloud'         => fed_is_true_false( $menu['show_tagcloud'] ),
						'show_in_rest'          => fed_is_true_false( $menu['show_in_rest'] ),
						'rest_base'             => $menu['rest_base'],
						'show_in_quick_edit'    => fed_is_true_false( $menu['show_in_quick_edit'] ),
						'rewrite'               => $rewrite,
						'query_var'             => fed_is_true_false( $menu['query_var'] ),
					);

					$object_type = isset( $menu['object_type'] ) && is_array( $menu['object_type'] ) ? array_keys( $menu['object_type'] ) : array( 'post' );
					register_taxonomy( $menu['slug'], $object_type, $args );
				}
			}
		}

		/**
		 * Add Custom Taxonomies View
		 */
		protected function fed_cp_add_custom_taxonomies_type() {
			$cpt = fed_cp_get_taxonomies_label();
			$pt  = get_option( 'fed_cp_custom_taxonomies', array() );
			$this->render_page( $cpt, $pt, 'Add' );
		}

		/**
		 * Edit Custom Taxonomies View
		 *
		 * @param array $request Request parameters.
		 */
		protected function fed_cp_edit_custom_taxonomies( $request ) {
			$pt = get_option( 'fed_cp_custom_taxonomies', array() );
			if ( ! isset( $pt[ $request['fed_type_id'] ] ) ) {
				$url = menu_page_url( 'fed_taxonomies', false ) . '&error=invalid_post_type';
				wp_safe_redirect( $url );
				exit;
			}
			$cpt = fed_cp_get_taxonomies_label( $pt[ $request['fed_type_id'] ] );
			$this->render_page( $cpt, $pt, 'Edit', $request['fed_type_id'] );
		}

		/**
		 * Render Unified Modern Taxonomies Page
		 *
		 * @param array  $cpt Form fields configuration.
		 * @param array  $pt Registered taxonomies list.
		 * @param string $mode 'Add' or 'Edit'.
		 * @param string $current_slug Current editing slug.
		 */
		protected function render_page( $cpt, $pt, $mode = 'Add', $current_slug = '' ) {
			$custom_post_url   = menu_page_url( 'fed_custom_post', false );
			$add_new_url       = menu_page_url( 'fed_taxonomies', false );
			$total_tax_count   = is_array( $pt ) ? count( $pt ) : 0;
			$is_editing        = ( 'Edit' === $mode );
			$current_name      = $is_editing && isset( $pt[ $current_slug ]['label'] ) ? $pt[ $current_slug ]['label'] : '';
			$ajax_url          = admin_url( 'admin-ajax.php' );
			$nonce             = wp_create_nonce( 'fed_nonce' );
			?>
			<!-- Scoped Styles -->
			<style>
				.fed-btn-primary,
				button.fed-btn-primary,
				a.fed-btn-primary {
					background-color: #4f46e5 !important;
					color: #ffffff !important;
					border: 1px solid #4338ca !important;
					box-shadow: 0 2px 4px -1px rgba(79, 70, 229, 0.2) !important;
				}
				.fed-btn-primary:hover,
				button.fed-btn-primary:hover,
				a.fed-btn-primary:hover {
					background-color: #4338ca !important;
					color: #ffffff !important;
					border-color: #3730a3 !important;
				}
				.fed-btn-secondary,
				button.fed-btn-secondary,
				a.fed-btn-secondary {
					background-color: #f8fafc !important;
					color: #334155 !important;
					border: 1px solid #e2e8f0 !important;
				}
				.fed-btn-secondary:hover,
				button.fed-btn-secondary:hover,
				a.fed-btn-secondary:hover {
					background-color: #f1f5f9 !important;
					color: #1e293b !important;
					border-color: #cbd5e1 !important;
				}
				.fed-btn-delete,
				button.fed-btn-delete {
					color: #475569 !important;
					background-color: #f8fafc !important;
					border: 1px solid #e2e8f0 !important;
				}
				.fed-btn-delete:hover,
				button.fed-btn-delete:hover {
					color: #e11d48 !important;
					background-color: #fff1f2 !important;
					border-color: #fecdd3 !important;
				}
				.bc_fed #fed_tax_filter_search {
					padding-left: 36px !important;
					border: 1px solid #e2e8f0 !important;
					border-radius: 12px !important;
					background-color: #f8fafc !important;
					height: 40px !important;
					min-height: 40px !important;
					font-size: 12px !important;
					color: #334155 !important;
					box-shadow: none !important;
				}
				.bc_fed #fed_tax_filter_search:focus {
					border-color: #6366f1 !important;
					background-color: #ffffff !important;
					box-shadow: 0 0 0 1px #6366f1 !important;
				}
				.bc_fed input[type="text"],
				.bc_fed select,
				.bc_fed textarea {
					border: 1px solid #e2e8f0 !important;
					border-radius: 12px !important;
					background-color: #f8fafc !important;
					padding: 8px 12px !important;
					font-size: 12px !important;
					color: #1e293b !important;
					width: 100% !important;
					box-sizing: border-box !important;
					transition: all 0.15s ease !important;
				}
				.bc_fed input[type="text"]:focus,
				.bc_fed select:focus,
				.bc_fed textarea:focus {
					border-color: #6366f1 !important;
					background-color: #ffffff !important;
					box-shadow: 0 0 0 1px #6366f1 !important;
					outline: none !important;
				}
			</style>

			<div class="bc_fed fed-admin-wrap w-full max-w-none px-4 sm:px-8 py-6 sm:py-8 font-sans text-slate-800">
				<?php echo fed_loader(); ?>

				<!-- Toast Notification Element -->
				<div id="fed_toast_notification" class="fixed bottom-6 right-6 transform translate-y-16 opacity-0 transition-all duration-300 pointer-events-none flex items-center gap-3 bg-slate-900 text-white px-5 py-3.5 rounded-2xl shadow-2xl border border-slate-700" style="z-index: 99999999 !important;">
					<span id="fed_toast_icon" class="text-emerald-400 text-base"><i class="fas fa-check-circle"></i></span>
					<span id="fed_toast_message" class="text-xs font-semibold tracking-wide">Changes saved successfully.</span>
				</div>

				<!-- Page Header -->
				<div class="bg-white rounded-2xl p-5 sm:p-6 shadow-xs border border-slate-200/80 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
					<div class="flex items-center gap-3.5">
						<div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-xs shrink-0" style="background-color: #4f46e5 !important; color: #ffffff !important;">
							<i class="fas fa-tags text-sm" style="color: #ffffff !important;"></i>
						</div>
						<div>
							<div class="flex items-center gap-2.5">
								<h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight m-0 p-0">
									<?php esc_html_e( 'Custom Taxonomies', 'frontend-dashboard-custom-post' ); ?>
								</h1>
								<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
									<?php echo (int) $total_tax_count; ?> <?php esc_html_e( 'Registered', 'frontend-dashboard-custom-post' ); ?>
								</span>
							</div>
							<p class="text-xs text-slate-500 m-0 mt-0.5 font-medium">
								<?php esc_html_e( 'Create and manage custom taxonomies (categories, tags, custom filters) attached to post types.', 'frontend-dashboard-custom-post' ); ?>
							</p>
						</div>
					</div>

					<div class="flex items-center gap-2.5 shrink-0">
						<a href="<?php echo esc_url( $custom_post_url ); ?>" class="fed-btn-secondary h-10 inline-flex items-center justify-center gap-2 px-4 rounded-xl font-semibold text-xs transition-all cursor-pointer shadow-2xs no-underline">
							<i class="fas fa-cubes text-xs"></i>
							<span><?php esc_html_e( 'Custom Posts', 'frontend-dashboard-custom-post' ); ?></span>
						</a>
						<?php if ( $is_editing ) : ?>
							<a href="<?php echo esc_url( $add_new_url ); ?>" class="fed-btn-primary h-10 inline-flex items-center justify-center gap-2 px-5 rounded-xl font-semibold text-xs transition-all active:scale-95 cursor-pointer shadow-sm no-underline">
								<i class="fas fa-plus text-xs" style="color: #ffffff !important;"></i>
								<span style="color: #ffffff !important;"><?php esc_html_e( 'Add New Taxonomy', 'frontend-dashboard-custom-post' ); ?></span>
							</a>
						<?php endif; ?>
					</div>
				</div>

				<!-- Main 2-Column Layout -->
				<div class="flex flex-col lg:flex-row gap-6 items-start">
					<!-- LEFT SIDEBAR: Registered Taxonomies -->
					<div class="w-full lg:w-72 xl:w-80 shrink-0 space-y-3">
						<div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-3">
							<div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
								<span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
									<?php esc_html_e( 'Registered Taxonomies', 'frontend-dashboard-custom-post' ); ?>
								</span>
								<span class="text-[11px] font-semibold text-slate-400">
									<?php echo (int) $total_tax_count; ?>
								</span>
							</div>

							<!-- Search Bar -->
							<div class="relative w-full">
								<span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
									<i class="fas fa-search text-[11px]"></i>
								</span>
								<input type="text" id="fed_tax_filter_search" placeholder="<?php esc_attr_e( 'Search taxonomies...', 'frontend-dashboard-custom-post' ); ?>" class="w-full pl-8 pr-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 outline-none" />
							</div>

							<!-- List of Taxonomies -->
							<div class="space-y-1.5 max-h-[60vh] overflow-y-auto" id="fed_tax_sidebar_list">
								<?php if ( ! empty( $pt ) ) : ?>
									<?php foreach ( $pt as $slug => $tax_data ) :
										$is_active = ( $current_slug === $slug );
										$label_name = ! empty( $tax_data['label'] ) ? $tax_data['label'] : $slug;
										$attached_pts = isset( $tax_data['object_type'] ) && is_array( $tax_data['object_type'] ) ? array_keys( $tax_data['object_type'] ) : array();
										?>
										<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'fed_taxonomies', 'fed_type_id' => $slug ), admin_url( 'admin.php' ) ) ); ?>"
											class="fed-tax-sidebar-item w-full flex items-center justify-between p-3 rounded-xl border transition-all no-underline group <?php echo $is_active ? 'bg-indigo-50/50 border-indigo-500 text-indigo-700 shadow-2xs' : 'bg-slate-50/70 border-slate-200/70 text-slate-700 hover:bg-slate-100/80 hover:border-slate-300'; ?>"
											data-slug="<?php echo esc_attr( strtolower( $slug ) ); ?>"
											data-label="<?php echo esc_attr( strtolower( $label_name ) ); ?>">
											<div class="flex items-center gap-3 min-w-0">
												<div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs shrink-0 transition-colors <?php echo $is_active ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200/80 text-slate-500 group-hover:text-indigo-600'; ?>">
													<i class="fas fa-tag"></i>
												</div>
												<div class="min-w-0">
													<div class="text-xs font-bold truncate">
														<?php echo esc_html( $label_name ); ?>
													</div>
													<div class="text-[10px] font-mono text-slate-400 truncate">
														<?php echo esc_html( $slug ); ?>
													</div>
												</div>
											</div>
											<i class="fas fa-chevron-right text-[10px] <?php echo $is_active ? 'text-indigo-600' : 'text-slate-300 group-hover:text-slate-600'; ?>"></i>
										</a>
									<?php endforeach; ?>
								<?php else : ?>
									<div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 text-center text-xs text-slate-500">
										<?php esc_html_e( 'No custom taxonomies registered yet.', 'frontend-dashboard-custom-post' ); ?>
									</div>
								<?php endif; ?>
							</div>

							<!-- Add New Taxonomy Button -->
							<div class="pt-2 border-t border-slate-100">
								<a href="<?php echo esc_url( $add_new_url ); ?>" class="w-full flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border border-dashed border-indigo-200/90 bg-indigo-50/40 hover:bg-indigo-50 hover:border-indigo-400 text-indigo-600 hover:text-indigo-700 text-xs font-semibold transition-all duration-150 cursor-pointer no-underline group shadow-2xs">
									<span class="w-5 h-5 rounded-lg bg-indigo-100 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white flex items-center justify-center text-[10px] transition-colors">
										<i class="fas fa-plus"></i>
									</span>
									<span><?php esc_html_e( 'Add New Taxonomy', 'frontend-dashboard-custom-post' ); ?></span>
								</a>
							</div>
						</div>
					</div>

					<!-- RIGHT MAIN CONTENT: Form Editor -->
					<div class="flex-1 min-w-0 w-full">
						<form method="post" class="fed_admin_menu fed_ajax space-y-6" action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=fed_cp_add_custom_taxonomies' ) ); ?>">
							<?php wp_nonce_field( 'fed_nonce', 'fed_nonce' ); ?>
							<?php if ( $is_editing ) : ?>
								<input type="hidden" name="fed_cpt_edit" value="yes"/>
							<?php endif; ?>

							<!-- Top Inspector Header Card -->
							<div class="bg-white rounded-3xl p-5 sm:p-6 shadow-xs border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
								<div class="flex items-center gap-3.5">
									<div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-lg shrink-0">
										<i class="fas fa-tags"></i>
									</div>
									<div>
										<h2 class="text-base sm:text-lg font-bold text-slate-900 m-0">
											<?php if ( $is_editing ) : ?>
												<?php esc_html_e( 'Edit Custom Taxonomy', 'frontend-dashboard-custom-post' ); ?>: <span class="text-indigo-600"><?php echo esc_html( $current_name ); ?></span>
											<?php else : ?>
												<?php esc_html_e( 'Add New Custom Taxonomy', 'frontend-dashboard-custom-post' ); ?>
											<?php endif; ?>
										</h2>
										<p class="text-xs text-slate-500 m-0 mt-0.5">
											<?php esc_html_e( 'Configure taxonomy identifiers, post type associations, and hierarchical behavior.', 'frontend-dashboard-custom-post' ); ?>
										</p>
									</div>
								</div>

								<div class="flex items-center gap-2.5 shrink-0">
									<?php if ( $is_editing ) : ?>
										<button type="button" class="fed-btn-delete fed-trigger-delete-tax h-10 inline-flex items-center justify-center gap-2 px-4 rounded-xl font-semibold text-xs transition-all cursor-pointer shadow-2xs" data-id="<?php echo esc_attr( $current_slug ); ?>" data-name="<?php echo esc_attr( $current_name ); ?>">
											<i class="fas fa-trash-alt text-xs"></i>
											<span><?php esc_html_e( 'Delete', 'frontend-dashboard-custom-post' ); ?></span>
										</button>
									<?php endif; ?>
									<button type="submit" class="fed-btn-primary h-10 inline-flex items-center justify-center gap-2 px-6 rounded-xl font-semibold text-xs transition-all active:scale-95 cursor-pointer shadow-sm">
										<i class="fas fa-save text-xs" style="color: #ffffff !important;"></i>
										<span style="color: #ffffff !important;"><?php echo $is_editing ? esc_html__( 'Save Changes', 'frontend-dashboard-custom-post' ) : esc_html__( 'Create Taxonomy', 'frontend-dashboard-custom-post' ); ?></span>
									</button>
								</div>
							</div>

							<!-- 1. Card: Basic Settings -->
							<?php if ( isset( $cpt['Basic Settings'] ) ) : ?>
								<div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-xs space-y-6">
									<div class="flex items-center gap-3.5 pb-5 border-b border-slate-100">
										<div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-base shrink-0">
											<i class="fas fa-sliders-h"></i>
										</div>
										<div>
											<h3 class="text-sm sm:text-base font-bold text-slate-900 m-0"><?php esc_html_e( 'Basic Settings', 'frontend-dashboard-custom-post' ); ?></h3>
											<p class="text-xs text-slate-500 m-0 mt-0.5"><?php esc_html_e( 'Configure the taxonomy slug and descriptive names.', 'frontend-dashboard-custom-post' ); ?></p>
										</div>
									</div>

									<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
										<?php foreach ( $cpt['Basic Settings'] as $pindex => $post_type ) :
											if ( $pindex === 'fed_extra' ) continue;
											?>
											<div class="space-y-2">
												<label class="block text-xs font-bold text-slate-700">
													<?php echo esc_html( $post_type['name'] ); ?>
													<?php if ( ! empty( $post_type['required'] ) ) : ?>
														<span class="text-rose-500">*</span>
													<?php endif; ?>
												</label>
												<?php
												if ( $is_editing && 'slug' === $pindex ) {
													$post_type['input']['readonly'] = true;
													$post_type['input']['class']    = 'bg-slate-100 font-mono text-xs text-slate-600';
												}
												echo fed_get_input_details( $post_type['input'] );
												?>
												<?php if ( ! empty( $post_type['help_message'] ) ) : ?>
													<p class="text-[11px] text-slate-400 m-0"><?php echo wp_strip_all_tags( $post_type['help_message'] ); ?></p>
												<?php endif; ?>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endif; ?>

							<!-- 2. Card: Post Type Associations -->
							<?php if ( isset( $cpt['Basic Settings']['fed_extra'] ) && is_array( $cpt['Basic Settings']['fed_extra']['input'] ) ) : ?>
								<div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-xs space-y-6">
									<div class="flex items-center gap-3.5 pb-5 border-b border-slate-100">
										<div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-base shrink-0">
											<i class="fas fa-newspaper"></i>
										</div>
										<div>
											<h3 class="text-sm sm:text-base font-bold text-slate-900 m-0"><?php esc_html_e( 'Target Post Types', 'frontend-dashboard-custom-post' ); ?></h3>
											<p class="text-xs text-slate-500 m-0 mt-0.5"><?php esc_html_e( 'Select which post types this taxonomy will be attached to.', 'frontend-dashboard-custom-post' ); ?></p>
										</div>
									</div>

									<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
										<?php foreach ( $cpt['Basic Settings']['fed_extra']['input'] as $k => $extra ) : 
											$input_data = $extra['input'];
											$item_label = isset( $input_data['label'] ) ? $input_data['label'] : $k;
											$input_data['label'] = '';
										?>
											<label class="p-3.5 bg-slate-50/80 hover:bg-slate-100/80 border border-slate-200/80 rounded-2xl flex items-center justify-between gap-2 cursor-pointer transition-colors">
												<span class="text-xs font-bold text-slate-800 select-none"><?php echo esc_html( $item_label ); ?></span>
												<?php echo fed_get_input_details( $input_data ); ?>
											</label>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endif; ?>

							<!-- 3. Card: Hierarchy & Visibility Settings -->
							<?php if ( isset( $cpt['Settings'] ) ) : ?>
								<div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-xs space-y-6">
									<div class="flex items-center gap-3.5 pb-5 border-b border-slate-100">
										<div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-base shrink-0">
											<i class="fas fa-sitemap"></i>
										</div>
										<div>
											<h3 class="text-sm sm:text-base font-bold text-slate-900 m-0"><?php esc_html_e( 'Hierarchy & Visibility', 'frontend-dashboard-custom-post' ); ?></h3>
											<p class="text-xs text-slate-500 m-0 mt-0.5"><?php esc_html_e( 'Configure hierarchical behavior (Categories vs Tags) and menu display.', 'frontend-dashboard-custom-post' ); ?></p>
										</div>
									</div>

									<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
										<?php foreach ( $cpt['Settings'] as $pindex => $post_type ) : ?>
											<div class="space-y-2">
												<label class="block text-xs font-bold text-slate-700">
													<?php echo esc_html( $post_type['name'] ); ?>
												</label>
												<?php echo fed_get_input_details( $post_type['input'] ); ?>
												<?php if ( ! empty( $post_type['help_message'] ) ) : ?>
													<p class="text-[11px] text-slate-400 m-0"><?php echo wp_strip_all_tags( $post_type['help_message'] ); ?></p>
												<?php endif; ?>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endif; ?>

							<!-- 4. Card: Labels & Translation (Accordion) -->
							<?php if ( isset( $cpt['Labels'] ) ) : ?>
								<div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-xs space-y-6">
									<div class="flex items-center justify-between pb-5 border-b border-slate-100 cursor-pointer fed-toggle-accordion-header" data-target="#fed_tax_labels_content">
										<div class="flex items-center gap-3.5">
											<div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-base shrink-0">
												<i class="fas fa-language"></i>
											</div>
											<div>
												<h3 class="text-sm sm:text-base font-bold text-slate-900 m-0"><?php esc_html_e( 'Labels & Translation Options', 'frontend-dashboard-custom-post' ); ?></h3>
												<p class="text-xs text-slate-500 m-0 mt-0.5"><?php esc_html_e( 'Customize singular, plural, and menu button strings for this taxonomy.', 'frontend-dashboard-custom-post' ); ?></p>
											</div>
										</div>
										<button type="button" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center transition-transform fed-accordion-arrow">
											<i class="fas fa-chevron-down text-xs"></i>
										</button>
									</div>

									<div id="fed_tax_labels_content" class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2">
										<?php foreach ( $cpt['Labels'] as $pindex => $post_type ) : ?>
											<div class="space-y-2">
												<label class="block text-xs font-bold text-slate-700">
													<?php echo esc_html( $post_type['name'] ); ?>
												</label>
												<?php echo fed_get_input_details( $post_type['input'] ); ?>
												<?php if ( ! empty( $post_type['help_message'] ) ) : ?>
													<p class="text-[11px] text-slate-400 m-0"><?php echo wp_strip_all_tags( $post_type['help_message'] ); ?></p>
												<?php endif; ?>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endif; ?>

							<!-- Bottom Action Bar -->
							<div class="bg-white rounded-3xl p-5 sm:p-6 shadow-xs border border-slate-200/80 flex items-center justify-between gap-4">
								<a href="<?php echo esc_url( $add_new_url ); ?>" class="fed-btn-secondary h-10 inline-flex items-center justify-center gap-2 px-5 rounded-xl font-semibold text-xs no-underline transition-all cursor-pointer">
									<i class="fas fa-times text-xs"></i>
									<span><?php esc_html_e( 'Cancel', 'frontend-dashboard-custom-post' ); ?></span>
								</a>

								<button type="submit" class="fed-btn-primary h-10 inline-flex items-center justify-center gap-2 px-6 rounded-xl font-semibold text-xs transition-all active:scale-95 cursor-pointer shadow-sm">
									<i class="fas fa-save text-xs" style="color: #ffffff !important;"></i>
									<span style="color: #ffffff !important;"><?php echo $is_editing ? esc_html__( 'Save Changes', 'frontend-dashboard-custom-post' ) : esc_html__( 'Create Taxonomy', 'frontend-dashboard-custom-post' ); ?></span>
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>

			<!-- Custom Delete Confirmation Modal -->
			<div id="fed_delete_tax_modal" class="fixed inset-0 z-[99999] hidden items-center justify-center bg-slate-900/60 backdrop-blur-xs transition-all duration-200" style="z-index: 99999 !important; display: none;">
				<div class="delete-modal-content bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full mx-4 shadow-2xl border border-slate-100 text-center transform scale-95 opacity-0 transition-all duration-200">
					<div class="w-14 h-14 rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center text-2xl mx-auto mb-4 shadow-xs">
						<i class="fas fa-trash-alt"></i>
					</div>
					<h3 class="text-base sm:text-lg font-bold text-slate-900 mb-1.5">
						<?php esc_html_e( 'Delete Custom Taxonomy?', 'frontend-dashboard-custom-post' ); ?>
					</h3>
					<p class="text-xs text-slate-500 leading-relaxed mb-6" id="fed_delete_tax_desc">
						<?php esc_html_e( 'Are you sure you want to delete this custom taxonomy? This action cannot be undone.', 'frontend-dashboard-custom-post' ); ?>
					</p>
					<div class="flex items-center justify-center gap-3">
						<button type="button" id="fed_cancel_delete_tax_btn" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs sm:text-sm font-semibold transition-all cursor-pointer">
							<?php esc_html_e( 'Cancel', 'frontend-dashboard-custom-post' ); ?>
						</button>
						<button type="button" id="fed_confirm_delete_tax_btn" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs sm:text-sm font-semibold transition-all cursor-pointer shadow-sm active:scale-95" style="background-color: #e11d48 !important; color: #ffffff !important;">
							<?php esc_html_e( 'Yes, Delete Taxonomy', 'frontend-dashboard-custom-post' ); ?>
						</button>
					</div>
				</div>
			</div>

			<!-- Client-Side JavaScript -->
			<script>
			(function($) {
				'use strict';

				$(document).ready(function() {
					var pendingDeleteId = null;
					var pendingDeleteName = null;

					// Toast notification helper
					function showToast(message, isError) {
						var $toast = $('#fed_toast_notification');
						var $msg = $('#fed_toast_message');
						var $icon = $('#fed_toast_icon');

						$msg.text(message);
						if (isError) {
							$icon.html('<i class="fas fa-exclamation-circle text-rose-400"></i>');
							$toast.addClass('border-rose-500/50');
						} else {
							$icon.html('<i class="fas fa-check-circle text-emerald-400"></i>');
							$toast.removeClass('border-rose-500/50');
						}

						$toast.removeClass('translate-y-16 opacity-0 pointer-events-none').addClass('translate-y-0 opacity-100');
						setTimeout(function() {
							$toast.removeClass('translate-y-0 opacity-100').addClass('translate-y-16 opacity-0 pointer-events-none');
						}, 3500);
					}

					// Live search sidebar items
					$('#fed_tax_filter_search').on('input keyup', function() {
						var q = $.trim($(this).val()).toLowerCase();
						$('.fed-tax-sidebar-item').each(function() {
							var $item = $(this);
							var slug = ($item.data('slug') || '').toString().toLowerCase();
							var label = ($item.data('label') || '').toString().toLowerCase();
							if (!q || slug.indexOf(q) !== -1 || label.indexOf(q) !== -1) {
								$item.removeClass('hidden');
							} else {
								$item.addClass('hidden');
							}
						});
					});

					// Accordion collapse toggle
					$(document).on('click', '.fed-toggle-accordion-header', function() {
						var $header = $(this);
						var target = $header.data('target');
						var $content = $(target);
						var $arrow = $header.find('.fed-accordion-arrow');

						$content.slideToggle(200);
						$arrow.toggleClass('rotate-180');
					});

					// Delete Confirmation Modal
					$(document).on('click', '.fed-trigger-delete-tax', function(e) {
						e.preventDefault();
						var $btn = $(this);
						pendingDeleteId = $btn.data('id');
						pendingDeleteName = $btn.data('name');

						$('#fed_delete_tax_desc').text('Are you sure you want to delete "' + pendingDeleteName + '"? This will unregister this custom taxonomy from WordPress.');
						$('#fed_delete_tax_modal').removeClass('hidden').css('display', 'flex');
						setTimeout(function() {
							$('#fed_delete_tax_modal .delete-modal-content').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
						}, 10);
					});

					function closeDeleteModal() {
						$('#fed_delete_tax_modal .delete-modal-content').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
						setTimeout(function() {
							$('#fed_delete_tax_modal').addClass('hidden').css('display', 'none');
							pendingDeleteId = null;
							pendingDeleteName = null;
						}, 200);
					}

					$('#fed_cancel_delete_tax_btn').on('click', function() {
						closeDeleteModal();
					});

					$('#fed_delete_tax_modal').on('click', function(e) {
						if ($(e.target).is('#fed_delete_tax_modal')) {
							closeDeleteModal();
						}
					});

					$('#fed_confirm_delete_tax_btn').on('click', function() {
						if (!pendingDeleteId) return;
						var slug = pendingDeleteId;
						var $loader = $('.fed_loader');

						closeDeleteModal();
						$loader.removeClass('hidden');

						$.ajax({
							type: 'POST',
							url: '<?php echo esc_url( admin_url( 'admin-ajax.php?action=fed_cp_delete_custom_taxonomies_type&fed_nonce=' . $nonce ) ); ?>',
							data: { id: slug },
							success: function(response) {
								$loader.addClass('hidden');
								if (response && response.success) {
									showToast(response.data && response.data.message ? response.data.message : 'Custom taxonomy deleted.', false);
									setTimeout(function() {
										window.location.href = response.data && response.data.reload ? response.data.reload : '<?php echo esc_url( $add_new_url ); ?>';
									}, 500);
								} else {
									showToast(response && response.data && response.data.message ? response.data.message : 'Could not delete custom taxonomy.', true);
								}
							},
							error: function() {
								$loader.addClass('hidden');
								showToast('Server error while deleting custom taxonomy.', true);
							}
						});
					});

					// Form submission
					$(document).on('submit', 'form.fed_admin_menu.fed_ajax', function(e) {
						e.preventDefault();
						var form = $(this);
						var $loader = $('.fed_loader');
						$loader.removeClass('hidden');

						$.ajax({
							type: 'POST',
							url: form.attr('action'),
							data: form.serialize(),
							success: function(response) {
								$loader.addClass('hidden');
								var isSuccess = (response && (response.success || response.status === 'success'));
								var msg = response && response.data && response.data.message ? response.data.message : (isSuccess ? 'Saved successfully.' : 'Error saving settings.');
								showToast(msg, !isSuccess);

								if (isSuccess && response.data && response.data.reload) {
									setTimeout(function() {
										window.location.href = response.data.reload;
									}, 600);
								}
							},
							error: function() {
								$loader.addClass('hidden');
								showToast('Network error while saving taxonomy.', true);
							}
						});
					});
				});
			})(jQuery);
			</script>
			<?php
		}
	}

	new Fed_Cp_Taxonomies();
}