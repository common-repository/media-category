<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    Wp_Media_Category
 * @subpackage Wp_Media_Category/admin
 */

if ( ! class_exists( 'Wp_Media_Category_Admin' ) ) :

	/**
	 * The admin-specific functionality of the plugin.
	 *
	 * Defines the plugin name, version, and two examples hooks for how to
	 * enqueue the admin-specific stylesheet and JavaScript.
	 *
	 * @package    Wp_Media_Category
	 * @subpackage Wp_Media_Category/admin
	 * @author     Wbcom Designs <admin@wbcomdesigns.com>
	 */
	class Wp_Media_Category_Admin {

		/**
		 * The ID of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $plugin_name    The ID of this plugin.
		 */
		private $plugin_name;

		/**
		 * The version of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $version    The current version of this plugin.
		 */
		private $version;

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 * @param      string $plugin_name       The name of this plugin.
		 * @param      string $version    The version of this plugin.
		 */
		public function __construct( $plugin_name, $version ) {
			$this->plugin_name = $plugin_name;
			$this->version     = $version;
			add_filter( 'admin_body_class', array( $this, 'wpmc_add_body_class' ) );
			add_filter( 'body_class', array( $this, 'wpmc_add_body_class_for_video' ) );
			
		}

		/**
		 * Register the stylesheets for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function wpmc_enqueue_styles() {

			/**
			 * This function is provided for demonstration purposes only.
			 *
			 * An instance of this class should be passed to the run() function
			 * defined in Wp_Media_Category_Loader as all of the hooks are defined
			 * in that particular class.
			 *
			 * The Wp_Media_Category_Loader will then create the relationship
			 * between the defined hooks and the functions defined in this
			 * class.
			 */
			wp_enqueue_media();
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-media-category-admin.css', array(), $this->version, 'all' );

		}

		/**
		 * Register the JavaScript for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function wpmc_enqueue_scripts() {
			global $pagenow;
			/**
			 * This function is provided for demonstration purposes only.
			 *
			 * An instance of this class should be passed to the run() function
			 * defined in Wp_Media_Category_Loader as all of the hooks are defined
			 * in that particular class.
			 *
			 * The Wp_Media_Category_Loader will then create the relationship
			 * between the defined hooks and the functions defined in this
			 * class.
			 */

			if ( 'upload.php' === $pagenow || 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {
				wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-media-category-admin.js', array( 'jquery' ), $this->version, false );
				wp_localize_script(
					$this->plugin_name,
					'wpmc_admin_js',
					array(
						'ajax_url'    => admin_url( 'admin-ajax.php' ),
						'spinner_url' => includes_url() . '/images/spinner.gif',
						'terms'       => get_terms( 'media_category', array( 'hide_empty' => false ) ),
					)
				);
			}
		}

		public function wpmc_add_body_class( $classes ) {
			global $pagenow;
			if ( 'post.php' === $pagenow || 'post-new.php' === $pagenow  ) {
				$classes .= 'wp-media-category';
			}
			return $classes;

		}

		/**
		 *
		 */
		public function wpmc_create_media_taxonomy() {
			$labels = array(
				'name'             => __( 'Media Categories', 'media-category' ),
				'singular_name'    => __( 'Media Category', 'media-category' ),
				'search_items'     => __( 'Search Media Categories', 'media-category' ),
				'all_items'        => __( 'All Media Categories', 'media-category' ),
				'change_term_item' => __( 'Change Media Category Media Categories', 'media-category' ),
				'update_item'      => __( 'Update Media Category', 'media-category' ),
				'add_new_item'     => __( 'Add New Media Category', 'media-category' ),
				'new_item_name'    => __( 'New Media Category Name', 'media-category' ),
				'menu_name'        => __( 'Media Category', 'media-category' ),
			);

			$args = array(
				'hierarchical'          => true,
				'show_ui'               => true,
				'show_admin_column'     => true,
				'public'                => false,
				'show_in_nav_menus'     => true,
				'query_var'             => true,
				'rewrite'               => array( 'slug' => 'media_category' ),
				'update_count_callback' => '_update_generic_term_count',
				'labels'                => $labels,
			);
			register_taxonomy( 'media_category', array( 'attachment' ), $args );
		}

		/**
		 *
		 */
		public function wpmc_bulk_change_term_media_notices() {
			global $media_type, $pagenow;
			if ( $pagenow == 'upload.php' && isset( $_REQUEST['change_term'] ) && (int) $_REQUEST['change_term'] ) {
				$change_term = isset($_REQUEST['change_term']) ? sanitize_text_field(wp_unslash($_REQUEST['change_term'])) : '';
				$message = sprintf( _n( 'Attachment change_term.', '%s attachments category changed.', $change_term, 'media-category' ), number_format_i18n( $change_term ) );
				echo "<div class=\"updated\"><p>{".esc_html($message)."}</p></div>";
			}
		}

		/**
		 *
		 */
		public function wpmc_list_terms() {
			$terms = get_terms(
				array(
					'taxonomy'   => 'media_category',
					'hide_empty' => false,
				)
			);

			echo '<select class="terms_form" name="terms" id="terms_cat">';

			foreach ( $terms as $term => $term_obj ) {
				$option_value = esc_html($term_obj->name);
				echo "<option value='".esc_html($term_obj->name)."'>".esc_html($option_value)."</option>\n";
			}

			echo '</select>';
			die;
		}

		public function wpmc_add_media_category_bulk_action( $bulk_actions ) {
			$bulk_actions['change_term'] = __( 'Change Media Category', 'media-category' );
			return $bulk_actions;
		}

		function wpmc_media_category_bulk_action_handler( $redirect_to, $action_name, $post_ids ) {
			if ( 'change_term' === $action_name ) {
				$terms = isset($_GET['terms']) ? sanitize_text_field(wp_unslash($_GET['terms'])) : '';
				if ( $terms ) {
					$terms    = sanitize_text_field( $terms );
					$taxonomy = 'media_category';
					foreach ( $post_ids as $post_id ) {
						$post = get_post( $post_id );
						wp_set_object_terms( $post_id, $terms, $taxonomy );
					}
				}
				$redirect_to = add_query_arg( 'bulk_media_category_processed', count( $post_ids ), $redirect_to );
				return $redirect_to;
			}
			return $redirect_to;
		}

		public function wpmc_updated_media_category() {
			if ( ! empty( $_REQUEST['bulk_media_category_processed'] ) ) {
				$posts_count = intval( $_REQUEST['bulk_media_category_processed'] );
				$post_text   = ( $posts_count > 1 ) ? esc_html__( 'posts', 'media-category' ) : esc_html__( 'post', 'media-category' );
				printf(
					'
				' .  esc_html__( '<div class="notice notice-info is-dismissible"><p>Updated media category for %1$s %2$s.</p></div>', 'media-category' ) . ' ',
				esc_html($posts_count),
				esc_html($post_text)
				);
			}
		}

		public function wpmc_add_media_category_filter() {
			$scr = get_current_screen();
			if ( $scr->base !== 'upload' ) {
				return;
			}

			$taxonomies = array( 'media_category' );

			foreach ( $taxonomies as $tax_slug ) {
				$tax_obj  = get_taxonomy( esc_attr($tax_slug) );
				$tax_name = esc_html($tax_obj->labels->name);
				$terms    = get_terms( $tax_slug );

				if ( count( $terms ) > 0 ) {
					echo "<select name='".esc_html($tax_slug)."' id='". esc_html($tax_slug). "' class='postform'>";
					echo "<option value=''>" . esc_html__( 'Show all', 'media-category' ) . " ". esc_html($tax_name). "</option>";
					echo "<option value='0'>" . esc_html__( 'Show Media Without Category', 'media-category' ) . "</option>";
					foreach ( $terms as $term ) {
						printf(
							'<option value="%1$s" %2$s>%3$s (%4$s)</option>',
							esc_html($term->slug),
							( ( isset( $_GET[ $tax_slug ] ) && ( $_GET[ $tax_slug ] == $term->slug ) ) ? ' selected="selected"' : '' ),
							esc_html($term->name),
							esc_html($term->count)
						);
					}
					echo '</select>';
				}
			}
		}

		public function wpmc_add_body_class_for_video( $c ) {
			global $post;
			if ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'wbmedia' ) ) {
				$c[] = 'wbmedia-shortcode';
			}
			return $c;
		}

		public function filter_media_without_taxonomy($query) {
			if (!is_admin() || !$query->is_main_query()) {
				return;
			}
			if ( isset( $_GET['media_category'] ) && $_GET['media_category'] == 0) {
				if ($query->get('post_type') === 'attachment') {
					// Add your taxonomy parameter name or slug here
					$taxonomy = 'media_category';
			
					// Exclude media with assigned taxonomy
					$query->set('tax_query', array(
						array(
							'taxonomy' => $taxonomy,
							'operator' => 'NOT EXISTS',
						),
					));
				}
			}
		}
	}

endif;
