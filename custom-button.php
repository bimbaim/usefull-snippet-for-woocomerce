<?php 

//display after button add to cart
add_action('woocommerce_after_add_to_cart_button','cmk_additional_button');
function cmk_additional_button() {
    echo '<button type="submit" class="button alt">Change me please</button>';
}


//If you want to display a custom button for a specific product category on product category archives pages below the description of this product category you will use:

add_action( 'woocommerce_archive_description', 'extra_button_on_product_category_archives', 20 );
function extra_button_on_product_category_archives() {
    if ( is_product_category('bracelets') ) {
        echo '<a class="button" href="www.test.com">Extra Button</a>';
    }
}

//If you want to display a custom button in single product pages for a specific product category below the short description of this product you will use:

add_action( 'woocommerce_single_product_summary', 'extra_button_on_product_page', 22 );
function extra_button_on_product_page() {
    global $post, $product;
    if ( has_term( 'bracelets', 'product_cat' ) ) {
        echo '<a class="button" href="www.test.com">Extra Button</a>';
    }
}

//If you want to display a custom button in single product pages for a specific product category below the description (in the product tab) of this product you will use:

add_filter( 'the_content', 'add_button_to_product_content', 20, 1 );
function add_button_to_product_content( $content ) {
    global $post;

    if ( is_product() && has_term( 'bracelets', 'product_cat' ) )
        $content .= '<a class="button" href="www.test.com">Extra Button</a>';

    // Returns the content.
    return $content;
}

//If you want to display a custom button in single product pages for a specific product category below the product tabs, you will use:

add_action( 'woocommerce_after_single_product_summary', 'extra_button_on_product_page', 12 );
function extra_button_on_product_page() {
    global $post, $product;
    if ( has_term( 'bracelets', 'product_cat' ) ) {
        echo '<a class="button" href="www.test.com">Extra Button</a>';
    }
}
