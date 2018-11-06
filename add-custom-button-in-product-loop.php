<?php
add_action( 'woocommerce_after_shop_loop_item', 'my_112757_add_product_link', 11 );

function my_112757_add_product_link() {
     global $product;

     echo '<a href="' . esc_url( get_permalink( $product->id ) ) . '">More Detail</a>';
}
