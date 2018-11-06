<?php

// Enforce single parent category items in cart at a time based on first item in cart
function get_product_top_level_category ( $product_id ) {

    $product_terms            =  get_the_terms ( $product_id, 'product_cat' );
    $product_category_term    =  $product_terms[0];
    $product_category_parent  =  $product_terms[0]->parent;

    while ( $product_category_parent  !=  0 ) {
            $product_category_term    =  get_term($product_category_parent, 'product_cat' );
            $product_category_parent  =  $product_category_term->parent;
    }

    return $product_category_term;

}

add_filter ( 'woocommerce_before_cart', 'restrict_cart_to_single_category' );
function restrict_cart_to_single_category() {
        global $woocommerce;
        $cart_contents    =  $woocommerce->cart->get_cart( );
        $cart_item_keys   =  array_keys ( $cart_contents );
        $cart_item_count  =  count ( $cart_item_keys );

        // Do nothing if the cart is empty
        // Do nothing if the cart only has one item
        if ( ! $cart_contents || $cart_item_count == 1 ) {
                return null;
        }

        // Multiple Items in cart
        $first_item                    =  $cart_item_keys[0];
        $first_item_id                 =  $cart_contents[$first_item]['product_id'];
        $first_item_top_category       =  get_product_top_level_category ( $first_item_id );
        $first_item_top_category_term  =  get_term ( $first_item_top_category, 'product_cat' );
        $first_item_top_category_name  =  $first_item_top_category_term->name;

        // Now we check each subsequent items top-level parent category
        foreach ( $cart_item_keys as $key ) {
                if ( $key  ==  $first_item ) {
                        continue;
                }
                else {
                        $product_id            =  $cart_contents[$key]['product_id'];
                        $product_top_category  =  get_product_top_level_category( $product_id );

                        if ( $product_top_category  !=  $first_item_top_category ) {
                                $woocommerce->cart->set_quantity ( $key, 0, true );
                                $mismatched_categories  =  1;
                        }
                }
        }

        // we really only want to display this message once for anyone, including those that have carts already prefilled
        if ( isset ( $mismatched_categories ) ) {
                echo '<p class="woocommerce-error">Only one category allowed in cart at a time.<br />You are currently allowed only <strong>'.$first_item_top_category_name.'</strong> items in your cart.<br />To order a different category empty your cart first.</p>';
        }
}
?>
