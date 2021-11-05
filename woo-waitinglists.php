<?php
if( ! class_exists( 'WhelloWaitringLists' ) ) :

	/**
	 * WhelloWaitringLists
	 *
	 * @since 1.0.0
	 */
	class WhelloWaitringLists {

		/**
		 * Instance
		 *
		 * @access private
		 * @since 1.0.0
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.0.0
		 * @return object initialized object of class.
		 */
		public static function instance(){
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			
			
				/* 
				* Custom Scripts & Style
				*/
				add_action('admin_print_scripts-post-new.php', array($this, 'waitinglists_scripts'));
				add_action('admin_print_scripts-post.php', array($this, 'waitinglists_scripts'));
				add_action('wp_footer', array($this, 'waitinglists_checkout_scripts'));
					
			
				/* Hook into the 'init' action so that the function
				* Containing our post type registration is not 
				* unnecessarily executed. 
				*/
				 
				add_action( 'init', array($this, 'waitinglists_post_type'));
				
				/* 
				* Create menu under woocomerce
				*/
				 
				add_action( 'admin_menu', array($this, 'add_waitinglists_menu'), 30 );
				
				
				/*
				 * Hooks custom columns
				 */
				 
				 add_filter( 'manage_edit-waiting-lists_columns',   array( $this, 'waitinglists_columns_table' ));
				 add_action( 'manage_waiting-lists_posts_custom_column',  array( $this, 'waitinglists_columns_table_content' ));
				 add_filter( 'manage_waiting-lists_posts_columns', array($this, 'waitinglists_reorder_columns' ));
 
				/*
				 * Hooks Change text button place order
				 */
				 
				 add_filter('woocommerce_order_button_html', array($this, 'waitinglists_disable_place_order_button_html' ));
				 
				 /*
				  * Clear cart after Waiting List success
				  */
				  add_action( 'init', array($this, 'woocommerce_clear_cart_url' ));
				 
				/*
				 * Disable Guttenberg
				 */
				 
				 add_filter('use_block_editor_for_post_type', array( $this, 'waitinglists_disable_gutenberg'), 10, 2);
				
				/*
				 * Hooks custom Metabox
				 */
				 
				 add_action( 'add_meta_boxes', array( $this, 'waitinglists_add_custom_box' ));

				/*
				 * Hooks process waitinglists
				 */
				 
				 add_action( 'woocommerce_checkout_process',  array( $this,'waitinglist_data' ));
			
		}
		
		/*
		 * Scripts
		 */
		public function waitinglists_scripts(){
				global $post_type;
					if( 'waiting-lists' != $post_type ){return;}
				?>
					<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
					<script>
								<!-- Custom Scripts -->
								/*jslint browser: true, plusplus: true */
							(function ($, window, document) {
								'use strict';
								// execute when the DOM is ready
								$(document).ready(function () {
									
																
									// js 'change' event triggered on the edit-waitinglist-customer form field
									$('.edit-waitinglist-customer').on('click', function () {
										// our code
										alert('click');
									});
									
									$('#waitinglists_customer .postbox-header').hide();
									$('#waitinglists_order .postbox-header').hide();
								});
							}(jQuery, window, document));			
					</script>
				<?php
				
			}
			
			public function waitinglists_checkout_scripts(){
				
				if(!is_checkout()){return;}
				$shop_page_url = get_permalink( wc_get_page_id( 'shop' ) )  . '?waitinglists-success';
				?>
				<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
					<script>
							(function( $ ) {
										$( 'body' ).on( 'updated_checkout',function( data ){
											if( !$('.shipping_method').length ){
												// popup code
												Swal.fire({
															  title: 'Delivery!',
															  text: 'Your zip code is out of our delivery. You will be put on the waiting list after clicking the "put on waiting list" button',
															  icon: 'warning',
															  confirmButtonText: 'I understand'
															})
											}
										} );
										
										$('.waitinglists').click(function(){
												Swal.fire({
															  title: 'Success!',
															  text: 'Your order in Waiting List',
															  icon: 'success',
															  confirmButtonText: 'Continue Shop!'
															}).then(function(){
																		window.location.href = '<?php echo $shop_page_url ; ?>';
																});
											});
									})(jQuery);
					</script>
				<?php	
				}
				
		/*
		 *  Clear cart after add to waiting		
		 */
		 
		 public function woocommerce_clear_cart_url() {
				if ( isset( $_GET['waitinglists-success'] ) ) {
					global $woocommerce;
					$woocommerce->cart->empty_cart();
				}
			}
		
		 /*
		  * Creating a function to create our CPT
		  */
			 
		public function waitinglists_post_type() {
			 
			// Set UI labels for Custom Post Type
				$labels = array(
					'name'                => _x( 'Waiting Lists', 'Post Type General Name', 'whello' ),
					'singular_name'       => _x( 'Waiting List', 'Post Type Singular Name', 'whello' ),
					'menu_name'           => __( 'Waiting Lists', 'whello' ),
					'parent_item_colon'   => __( 'Parent Waiting List', 'whello' ),
					'all_items'           => __( 'All Waiting Lists', 'whello' ),
					'view_item'           => __( 'View Waiting List', 'whello' ),
					'add_new_item'        => __( 'Add New Waiting List', 'whello' ),
					'add_new'             => __( 'Add New', 'whello' ),
					'edit_item'           => __( 'Edit Waiting List', 'whello' ),
					'update_item'         => __( 'Update Waiting List', 'whello' ),
					'search_items'        => __( 'Search Waiting List', 'whello' ),
					'not_found'           => __( 'Not Found', 'whello' ),
					'not_found_in_trash'  => __( 'Not found in Trash', 'whello' ),
				);
				 
			// Set other options for Custom Post Type
				 
				$args = array(
					'label'               => __( 'waiting-lists', 'whello' ),
					'description'         => __( 'Waiting List news and reviews', 'whello' ),
					'labels'              => $labels,
					// Features this CPT supports in Post Editor
					'supports'            => array('title'),
					// You can associate this CPT with a taxonomy or custom taxonomy. 
					'taxonomies'          => array(  ),
					/* A hierarchical CPT is like Pages and can have
					* Parent and child items. A non-hierarchical CPT
					* is like Posts.
					*/ 
					'hierarchical'        => false,
					'public'              => true,
					'show_ui'             => true,
					'show_in_menu'        => false,
					'show_in_nav_menus'   => false,
					'show_in_admin_bar'   => false,
					'can_export'          => true,
					'has_archive'         => true,
					'exclude_from_search' => false,
					'publicly_queryable'  => true,
					'capability_type'     => 'post',
					'show_in_rest' => true,
			 
				);
				 
				// Registering your Custom Post Type
				register_post_type( 'waiting-lists', $args );
			 
			}
			
			
		/*
		 *  Custom Columns
		 */	
		 public function waitinglists_columns_table($columns){
			 
				$columns['waitinglists_total'] = 'Total';
				$columns['waitinglists_postscode'] = 'Postcode';
				return $columns;
			 
			 }
		
		public function waitinglists_columns_table_content($column){ 
			
			  global $post;
				$order = wc_get_order( $post->ID );
				if ( 'waitinglists_total' === $column ) {
				
					$products = get_post_meta($post->ID, 'waitinglists_products', true);
					$sum = array();
					foreach($products as $product){
								$sum[] = $product['price'];
						}
					$total = array_sum($sum);
					echo $total;
				  
				}
				
				
				if ( 'waitinglists_postscode' === $column ) {
			 
					$postcode =  get_post_meta($post->ID, 'waitinglists_postcode', true);
					echo $postcode;
				  
				}
			
			}
			
		public function waitinglists_reorder_columns($columns){
			$columns = array(
					'cb' => $columns['cb'],
					'title' => __( 'Title' ),  // Post Title
					'waitinglists_total' => __( 'Total'), // not populated!
					'waitinglists_postscode' => __( 'Postcode'), // not populated!
				);

				  return $columns;
			
			}	 
		 
 
		/*
		 * Change text button Place order
		 */
		 public function waitinglists_disable_place_order_button_html( $button ) {
		if ( is_checkout()  ) {  

		
			// Get the chosen shipping method (if it exist)
			$selected_shipping= WC()->session->get('chosen_shipping_methods');
			
			// If the targeted shipping method is selected, we disable the button
			if( in_array( false, $selected_shipping ) ) {
				$text   = apply_filters( 'woocommerce_order_button_text', __( 'Place on Waiting Lists', 'woocommerce' ) );
				$button = '<button type="submit" class="button alt waitinglists" value="waitinglists" data-value="waitinglists">'.$text .'</button>';
			}
		}
		return $button;
	}
		 
		/*
		 * Disable Guttenberg
		 */
		public  function waitinglists_disable_gutenberg($current_status, $post_type)
			{
				// Use your post type key instead of 'product'
				if ($post_type === 'waiting-lists') return false;
				return $current_status;
			}
 
 
		/*
		  * Creating a Menu
		  */
		  
		public function add_waitinglists_menu() {
				add_submenu_page('woocommerce','Waiting Lists','Waiting Lists', 'manage_options', 'edit.php?post_type=waiting-lists');
			}
			
			
		/*
		 * Create Metabox
		 */
		 public function waitinglists_add_custom_box() {
			$screens = [ 'waiting-lists' ];
			foreach ( $screens as $screen ) {
				add_meta_box(
					'waitinglists_customer',                 // Unique ID
					'Customer',      // Box title
					array($this, 'waitinglists_customer_html'),  // Content callback, must be of type callable
					$screen                            // Post type
				);
				add_meta_box(
					'waitinglists_order',                 // Unique ID
					'Order',      // Box title
					array($this, 'waitinglists_order_html'),  // Content callback, must be of type callable
					$screen                            // Post type
				);
			}
		}
		
		public function waitinglists_customer_html($post){
			// Add nonce for security and authentication.
			wp_nonce_field( 'waitinglists_nonce_action', 'waitinglists_nonce' );
			
			$waitinglists_customer = get_post_meta($post->ID, 'waitinglists_customer', true);
			$customer = new WC_Customer( $waitinglists_customer);
			?>
			<h2 class="waitinglists-heading" style="font-size:18px; font-weight:500;">Waiting Lists #<?php echo get_the_id(); ?></h2>
			<div class="waitinglists-column-container" style="display:flex;">
					<div class="waitinglists-column" style="width:33.3%">
								<?php $order_statuses = wc_get_order_statuses(); ?>
								<label for="_waitinglists_order_status">Waiting lists Status</label>
								<select name="waitinglists_order_status" id="_waitinglists_order_status">
									<option value="">
										<?php esc_html_e( 'All Statues', 'wc-filter-orders-by-status' ); ?>
									</option>	
									<?php
									  foreach ( $order_statuses as $key=>$statusname ) {
									?>
										<option value="<?php echo esc_html($key); ?>">
											<?php echo esc_html($statusname); ?>
										</option>
									<?php
										}
									?>
								</select><br>
									<label for="_waitinglists_customer">Waiting lists Status</label>
								<?php 
										wp_dropdown_users( array( 
												'show_option_all'         => 'Select Customer',
												'show_option_none'        => '',
												//'role' => 'customer',
												'id' => '_waitinglists_customer',
												'class' => 'waitinglists_customer',
												'selected' => $waitinglists_customer,
												'name' => 'waitinglists_customer'
								) ); ?>
					</div>
					<div class="waitinglists-column" style="width:33.3%">
									<h3 style="font-size:14px;font-weight:400;">Billing</h3>
									<div>
											<?php 
															$billing_address_1  = $customer->get_billing_address_1();
															$billing_address_2  = $customer->get_billing_address_2();
															$billing_city       = $customer->get_billing_city();
															$billing_state      = $customer->get_billing_state();
															$billing_postcode   = $customer->get_billing_postcode();
															$billing_country    = $customer->get_billing_country();
															
															echo '<div>';
																	echo '<p>' . $billing_address_1 . '</p>';
																	echo '<p>' . $billing_address_2 . '</p>';
																	echo '<p>' . $billing_city . '</p>';
																	echo '<p>' . $billing_state . '</p>';
																	echo '<p>' . $billing_postcode . '</p>';
																	echo '<p>' . $billing_country . '</p>';
																	
															echo '</div>';
											?>
									</div>
					</div>
					<div class="waitinglists-column" style="width:33.3%">
									<h3 style="font-size:14px;font-weight:400;">Shipping</h3>
									<div>
											<?php 
															$shipping_address_1  = $customer->get_shipping_address_1();
															$shipping_address_2  = $customer->get_shipping_address_2();
															$shipping_city       = $customer->get_shipping_city();
															$shipping_state      = $customer->get_shipping_state();
															$shipping_postcode   = $customer->get_shipping_postcode();
															$shipping_country    = $customer->get_shipping_country();
															
															echo '<div>';
																	echo '<p>' . $shipping_address_1 . '</p>';
																	echo '<p>' . $shipping_address_2 . '</p>';
																	echo '<p>' . $shipping_city . '</p>';
																	echo '<p>' . $shipping_state . '</p>';
																	echo '<p>' . $shipping_postcode . '</p>';
																	echo '<p>' . $shipping_country . '</p>';
																	
															echo '</div>';
											?>
									</div>
					</div>
			</div>
			<?php
		
		}
		
		
		public function waitinglists_order_html($post){
			// Add nonce for security and authentication.
			wp_nonce_field( 'waitinglists_nonce_action', 'waitinglists_nonce' );
			$products = get_post_meta($post->ID, 'waitinglists_products', true);
			$products_total = get_post_meta($post->ID, 'waitinglists_total', true);
			$sum = array();
			echo '<div class="waitinglists-products">';
					foreach ($products as $product){
							echo '<div class="product-item">';
							echo '<p>Product Name: '. get_the_title($product["p_id"]) . '</p>';
							echo '<p>Quantity: '.$product["qty"] . '</p>';
							echo '</div><br>';
							echo '<p>Quantity: '.$product["price"] . '</p>';
							echo '</div><br>';
						}
			echo '</div>';
			echo array_sum($sum);
		
		}
		
		
		//Action to check if shipping method not available
		public function waitinglist_data() {
			$selected_shipping = WC()->session->get('chosen_shipping_methods');
			if(in_array(false, $selected_shipping)){
				wc_add_notice( __( 'Your Postcode is not in our area. We will put in waiting lists' ), 'error' );
				$current_user =  get_current_user_id();
				$firstname = $_POST['billing_first_name'];
				$lastname = $_POST['billing_last_name'];
				$ship_to_different_address = $_POST['ship_to_different_address'];
				if($ship_to_different_address != 1){
					$postcode = $_POST['billing_postcode'];
				}else{
						$postcode = $_POST['shipping_postcode'];
					}
					
				// Customer data
					$billing_address_1 = $_POST['billing_address_1'];
					$billing_address_2 = $_POST['billing_address_2'];
					$billing_city = $_POST['billing_city'];
					
					$shipping_address_1 = $_POST['shipping_address_1'];
					$shipping_address_2 = $_POST['shipping_address_2'];
					$shipping_city = $_POST['shipping_city'];
				
				//Products
				$products = array();
				foreach( WC()->cart->get_cart() as $cart_item_key => $cart_item ){
					$product_id = $cart_item['product_id']; // Product ID
					$product_obj = $cart_item['data']; // Product Object
					$product_qty = $cart_item['quantity']; // Product quantity
					$product_price = $cart_item['data']->get_price(); // Product price
					$product_total_stock = $cart_item['data']->get_stock_quantity(); // Product stock quantity
					$product_type = $cart_item['data']->get_type(); // Product type
					$product_name = $cart_item['data']->get_name(); // Product Title (Name)
					$product_description = $cart_item['data']->get_description(); // Product description
					$product_excerpt = $cart_item['data']->get_short_description(); // Product short description


					$cart_line_subtotal = $cart_item['line_subtotal']; // Cart item line subtotal
					$cart_line_subtotal_tax = $cart_item['line_subtotal_tax']; // Cart item line tax subtotal
					$cart_line_total = $cart_item['line_total']; // Cart item line total
					$cart_line_tax = $cart_item['line_tax']; // Cart item line tax total

					// variable products
					$variation_id = $cart_item['variation_id']; // Product Variation ID
					if($variation_id != 0){
						$product_variation_obj = $cart_item['data']; // Product variation Object
						$variation_array = $cart_item['variation']; // variation attributes + values
					}
					
					$products[] = array(
								'p_id' 	=> $product_id,
								'qty' 	=> $product_qty,
								'price' => $product_price
						);
					
					
				}
				
				$args = array(
						'post_type' => 'waiting-lists',
						'post_title' => $firstname . ' ' . $lastname,
						'post_author' => $current_user,
						'post_status'   => 'publish',
						'meta_input'   => array(
								'waitinglists_customer' => 	$current_user,
								'waitinglists_products' => 	$products,
								'waitinglists_postcode' =>  $postcode,
							),
						
				);
				// Insert the post into the database
				wp_insert_post( $args );
				
				$data = array(
					'billing_address_1'          			=> $billing_address_1,
					'billing_address_2'          			=> $billing_address_2,
					'billing_city'          						=> $billing_city,
					'billing_postcode'      				=> $postcode,
					'shipping_address_1'          	=> $shipping_address_1,
					'shipping_address_2'          	=> $shipping_address_2,
					'shipping_city'          					=> $shipping_city,
					'shipping_postcode'      			=> $postcode,
				);
				foreach ($data as $meta_key => $meta_value ) {
					update_user_meta( $current_user, $meta_key, $meta_value );
				}

			}
		}
		
		

	} //End Class

	/**
	 * Kicking this off by calling 'instance()' method
	 */
WhelloWaitringLists::instance();
	// OR
	// $whello_waiting_lists = WhelloWaitringLists::instance();

endif;
