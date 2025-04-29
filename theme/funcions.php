<?php
/**
 * -------------------------------------------------------------------------
 * ========================== CUSTOM PRODUCT PAGE ==========================
 * -------------------------------------------------------------------------
 * 
 * Add this to the bottom of your 'functions.php' WP theme file.
 * 
 */

// Loads external scripts.
add_action('wp_enqueue_scripts', 'load_external_scripts');
function load_external_scripts() {
    if (is_product()) {
		// Product image zoom
        wp_enqueue_script('image-zoom', get_template_directory_uri() . '/assets/js/image-zoom.js', array(), date("h:i:s"), true);
		
		// Product gallery switcher
		wp_enqueue_script('image-switch', get_template_directory_uri() . '/assets/js/image-switch.js', array(), date("h:i:s"), true);
		
		// Product gallery carousel
		wp_enqueue_script('image-carousel', get_template_directory_uri() . '/assets/js/image-carousel.js', array(), date("h:i:s"), true);
		
		// Recommended products sizing
		wp_enqueue_script('product-list', get_template_directory_uri() . '/assets/js/product-list.js', array(), date("h:i:s"), true);

		// Product countdown
		wp_enqueue_script('product-countdown', get_template_directory_uri() . '/assets/js/product-countdown.js', array(), date("h:i:s"), true);

		// Coupon clipboard
		wp_enqueue_script('coupon-clipboard', get_template_directory_uri() . '/assets/js/coupon-clipboard.js', array(), date("h:i:s"), true);
    }
}

// Wraps both product title and price inside a flexbox to properly
// properly align every title and price despite product image sizing.
add_action('woocommerce_shop_loop_item_title', 'custom_related_product_title_price_wrapper_start', 5);
function custom_related_product_title_price_wrapper_start() {
    echo '<div class="title-price-container">';
}
add_action('woocommerce_after_shop_loop_item_title', 'custom_related_product_title_price_wrapper_end', 15);
function custom_related_product_title_price_wrapper_end() {
    echo '</div>';
}

// Removes "Add to Cart" button everywhere in loops.
remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
add_filter('woocommerce_loop_add_to_cart_link', '__return_empty_string');

// Replaces default WooCommerce layout with a custom one.
add_action('woocommerce_before_single_product', 'replace_product_content');
function replace_product_content() {
    remove_all_actions('woocommerce_before_single_product_summary');
    remove_all_actions('woocommerce_single_product_summary');
    remove_all_actions('woocommerce_after_single_product_summary');

    add_action('woocommerce_before_single_product_summary', 'custom_product_display');
}

function custom_product_display() {
    global $product;

    if (!$product instanceof WC_Product) return;

	// Grabs the breadcrumb.
    ob_start();
    woocommerce_breadcrumb();
    $breadcrumb = ob_get_clean();

    echo '<div class="custom-product-wrapper">';
		echo '<div class="custom-breadcrumb">';
			echo $breadcrumb;
			echo '<p class="product-date">' . esc_html(get_the_date('Y/m/d at H:i', $product->get_id())) . '</p>';
		echo '</div>';

		echo '<div class="custom-product-wrapper">';

		// Gallery on the left.
		echo '<div class="custom-product-gallery">';
			custom_product_image_gallery($product);
		echo '</div>';

		// Product info on the right.
		echo '<div class="custom-product-info">';
			echo '<h1>' . $product->get_name() . '</h1>';
			display_coupons();
			echo '<div class="price-logo-container">';
				echo '<div class="price-logo-subcontainer">';
					echo '<p class="price">' . $product->get_price_html() . '</p>';
					display_shop_logo();
				echo '</div>';
				echo '<div class="short-description">' . wpautop($product->get_short_description()) . '</div>';
			echo '</div>';
			open_product_page_button();
			display_categories();
		echo '</div>';

		// Related procuts below everything.
		woocommerce_related_products(array(
			'posts_per_page' => 4,
			'columns'        => 4,
			'orderby'        => 'rand'
		));
    echo '</div>';
}

function display_coupons() {
    global $product;
	
	$product_id = $product->get_id();
    $coupons = get_post_meta($product_id, 'cupom', false);
	$sale_end_timestamp = get_post_meta($product_id, '_sale_price_dates_to', true);
	$expired = $sale_end_timestamp && time() > $sale_end_timestamp;
	$inactive = metadata_exists('post', $product_id, 'unavailable') || $expired;
	
    if (!empty($coupons) && !$inactive) {
        echo '<div class="coupon-container">';
        echo esc_html(count($coupons) === 1 ? 'Coupon available:' : 'Coupons available:') . ' ';

        foreach ($coupons as $coupon) {
			echo '<span class="coupon-text" title="Click to copy">' . esc_html($coupon) . '</span>';
        }

        echo '</div>';
    }
}

function display_categories() {
	global $product;
	
	$product_id = $product->get_id();
	$categories = wc_get_product_category_list($product_id, '; ');

	// Gets the raw category terms to count them.
	$category_terms = get_the_terms($product_id, 'product_cat');
	$category_count = is_array($category_terms) ? count($category_terms) : 0;

	$label = ($category_count === 1) ? 'Category' : 'Categories';
	
	echo '<p class="product-categories"><span>' . $label . ':</span> ' . $categories . '</p>';
}

function display_shop_logo() {
	global $product;
	
	$brands = wp_get_post_terms($product->get_id(), 'product_brand');

	if (!empty($brands) && !is_wp_error($brands)) {
		$brand = $brands[0];

		$thumbnail_id = get_term_meta($brand->term_id, 'thumbnail_id', true);
		$brand_name = get_term_link($brand);

		if ($thumbnail_id) {
			$brand_image = wp_get_attachment_url($thumbnail_id);

			if ($brand_image) {
				echo '<div class="price-logo">';
					echo '<a href="' . esc_url($brand_name) . '">';
						echo '<img src="' . esc_url($brand_image) . '" alt="' . esc_attr($brand->name) . '">';
					echo '</a>';
				echo '</div>';
			}
		}
	}
}

function open_product_page_button() {
    global $product;

    if (!$product instanceof WC_Product) return;

	$product_id = $product->get_id();
    $product_url = esc_url($product->add_to_cart_url());
	$sale_end_timestamp = get_post_meta($product_id, '_sale_price_dates_to', true);
	$expired = $sale_end_timestamp && time() > $sale_end_timestamp;
	$inactive = metadata_exists('post', $product_id, 'unavailable') || $expired;
	
	echo '<div class="product-container">';
		if ($sale_end_timestamp && !$inactive) {
			// Converts to ISO 8601 format for JS compatibility.
			$sale_end_date = date('c', $sale_end_timestamp);

			echo '<div class="product-cooldown-container">';
				echo '<span>Offer ends in:</span>';
				echo '<div class="product-cooldown-subcontainer">';
					echo '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">';
						echo '<path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z"/>';
						echo '<path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0"/>';
					echo '</svg>';
					echo '<div id="product-countdown" data-deadline="' . esc_attr($sale_end_date) . '"></div>';
				echo '</div>';
			echo '</div>';
			echo '<a href="' . $product_url . '" class="product_button ' . join(' ', $product->get_tag_ids()) . '">BUY NOW</a>';
		}
		elseif (!$inactive) {
			echo '<a href="' . $product_url . '" class="product_button">BUY NOW</a>';
		} else {
			echo '<a href="#" class="product_button disabled" onclick="return false;" style="pointer-events: none; opacity: 0.4;">UNAVAILABLE</a>';
		}
    echo '</div>';
    
}

function custom_product_image_gallery($product) {
    $main_image_id = $product->get_image_id();
    $gallery_image_ids = $product->get_gallery_image_ids();

    // Adds the main image to the gallery.
    if ($main_image_id) 
        array_unshift($gallery_image_ids, $main_image_id);
	
    $main_image_url = $main_image_id ? wp_get_attachment_image_url($main_image_id, 'large') : '';
    $full_image_url = $main_image_id ? wp_get_attachment_image_url($main_image_id, 'full') : '';

	// Main image
    echo '<div class="main-image-wrapper">';
		echo '<a href="' . esc_url($full_image_url) . '" class="main-image-link">';
			echo '<img src="' . esc_url($main_image_url) . '" alt="' . esc_attr($product->get_name()) . '" class="main-image">';
		echo '</a>';
		echo '<span class="zoom-icon">';
			echo '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">';
  				echo '<path fill-rule="evenodd" d="M6.5 12a5.5 5.5 0 1 0 0-11 5.5 5.5 0 0 0 0 11M13 6.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0"/>';
  				echo '<path d="M10.344 11.742q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1 6.5 6.5 0 0 1-1.398 1.4z"/>';
  				echo '<path fill-rule="evenodd" d="M6.5 3a.5.5 0 0 1 .5.5V6h2.5a.5.5 0 0 1 0 1H7v2.5a.5.5 0 0 1-1 0V7H3.5a.5.5 0 0 1 0-1H6V3.5a.5.5 0 0 1 .5-.5"/>';
			echo '</svg>';
		echo '</span>';
    echo '</div>';

	// Thumbnails (below main image)
	echo '<div class="gallery-carousel-wrapper">';
		echo '<div class="gallery-fade left"></div>';
		echo '<div class="gallery-fade right"></div>';
	
		echo '<button class="carousel-btn prev">‹</button>';
		echo '<div class="gallery-thumbs">';
			foreach ($gallery_image_ids as $image_id) {
				$thumb_url = wp_get_attachment_image_url($image_id, 'thumbnail');
				$large_url = wp_get_attachment_image_url($image_id, 'large');
				$full_url  = wp_get_attachment_image_url($image_id, 'full');
				
				echo '<a data-full="' . esc_url($large_url) . '" data-large="' . esc_url($large_url) . '" class="thumb-link">';
					echo '<img src="' . esc_url($thumb_url) . '" class="thumb" />';
				echo '</a>';
			}
		echo '</div>';
		echo '<button class="carousel-btn next">›</button>';
	echo '</div>';
}