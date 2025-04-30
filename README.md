<div align="center">
    <h1 align="center">WooCommerce custom product page</h1>
</div>

WordPress is nice, until you realize that every little thing costs either money or more time than it's worth. This is a basic hand-made custom product page for WordPress' WooCommerce, meant more as an example on how to do things rather than something ready-to-go.

<p align="center">
  <img src="https://github.com/user-attachments/assets/bce44872-e3b7-4c77-8afc-557a459035e3" alt="Customized WooCommerce product page" height="500"/>
  <img src="https://github.com/user-attachments/assets/c21d2edf-0d29-45cf-9c42-7e138e12ea6a" alt="Customized WooCommerce mobile product page" height="500"/>
</p>

## Explanation
The default product page is very basic. It works, but either you buy a custom theme (that changes it) or add a bunch of plugins to do the dirty work (which will also end up costing money). The solution is to customize via code.

### Hooks
WooCommerce offers some hooks that can be called inside `functions.php`. These hooks are system-wide and affect your WordPress as a whole, but for the most part, we're focusing on the ones related to the product page. The following is an example of a hook being used to remove the product's price:

```
remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
```

What this does is remove the `woocommerce_template_loop_add_to_cart()` function when the hook `woocommerce_after_shop_loop_item` gets called, with a priority of 10 (the default value; don't pay it much attention).

Similarly, we can use this method to, say, add something (like a custom function) to an existing hook:

```
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);

add_action('woocommerce_single_product_summary', 'custom_wrapped_single_price', 10);
function custom_wrapped_single_price() {
    echo '<div class="custom-price-wrapper">';
        woocommerce_template_single_price();
    echo '</div>';
}
```

Like this, you can yank the default stuff and re-add it however you like, building HTML with your custom CSS. A comprehensive diagram of the relevant hooks for the product page can be found [on this post](https://www.businessbloomer.com/woocommerce-visual-hook-guide-single-product-page/) by Business Bloomer.

### Product data
Then, you can pull information from the product itself for even more customization via the two main global variables `$product` and `$post`:

```
function custom_wrapped_single_price() {
    global $product;

    $product_id = $product->get_id();
	$brands = wp_get_post_terms($product_id, 'product_brand');
    $brand_name = get_term_link($brands[0]);

    echo '<div class="custom-price-wrapper">';
        echo '<p>Sold by ' . esc_url($brand_name) . '</p>';
        woocommerce_template_single_price();
    echo '</div>';
}
```

The full list of WooCommerce functions to pull data from the product can be found [in the official documentation](https://woocommerce.github.io/code-reference/packages/WooCommerce-Functions.html).

The attributes for the `WC_Product` ($product) Class can be found [here](https://woocommerce.github.io/code-reference/classes/WC-Product.html).

Since a WC product page is still, technically, a WP post, the `WP_Post` ($post) Class can be found [here](https://developer.wordpress.org/reference/classes/wp_post/).

### Custom attributes
In the WooCommerce product edit page, you can click in "Screen options" at around the top-right of the page and then toggle "Customized fields". This allows you to add custom attributes which can be accessed via code to do fun stuff, like add discount codes or per-product messages (like a discount countdown, for example):

```
global $product;

$unavailable = metadata_exists('post', $product->get_id(), 'unavailable');

echo '<p>The product is ' . ($unavailable ? 'unavailable' : 'available') . '.</p>';
```

This checks if the custom attribute `unavailable` simply exists, no matter what data it holds. 

```
global $post;
	
$product_id = $post->ID;
$coupon = get_post_meta($product_id, 'coupon', true);

echo '<p>Discount coupon: ' . esc_html(coupon) . '</p>';
```
Now, this grabs the data from the very first `coupon` custom field and adds it as escaped HTML to the paragraph. You can grab every single coupon by replacing the "true" boolean with "false", turning `$coupon` into an array of metadata which can be looped:

```
$coupons = get_post_meta($product_id, 'coupon', true);

if (!empty($coupons)) {
    foreach ($coupons as $coupon) {
        echo '<span class="coupon-text">' . esc_html($coupon) . '</span>';
    }
}
```

### CSS & JavaScript
You can call CSS from anywhere in the theme, be it re-using some of the default classes or pulling from "Additional CSS". External CSS (be it via a file inside your theme's folder) or some CDN, you'll need to reference it via `wp_enqueue_script()` in `functions.php`. The same applies for JavaScript (which can be vanilla or jQuery).

```
add_action('wp_enqueue_scripts', 'enqueue_styles');
function enqueue_styles() {
    wp_enqueue_style(
        'my-theme-style', // Handle (unique name for the stylesheet).
        get_template_directory_uri() . '/css/style.css', // Path to the file (relative to the theme root).
        array(), // Dependencies (if any, can be left empty if none).
        date("h:i:s"), // File version number; having it be a date() will prevent cache schenanigans.
        'all' // Media type (default is 'all').
    );
}
```

### Shortcodes
Both WooCommerce and other plugins (meant to complement WooCommerce) have [shortcodes](https://codex.wordpress.org/Shortcode) that allow for easy placement of pre-made blocks inside a given page via the `do_shortcode()` function.

A list of WooCommerce's shortcodes can be found [here](https://docs.woocommerce.com/document/woocommerce-shortcodes/).

Shortcodes can also be custom-made and be called from a post (meaning the body of a product page, in our scenario) like so:

```
function custom_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'name' => 'John Doe', // Default 'name' attribute
        ),
        $atts,
        'my-shortcode'
    );

    // Shortcode body.
    $output = '<div id="my-shortcode">' . esc_html($atts['name']) . '</div>';

    // JavaScript if need be.
    $output .= '
        <script type='text/javascript'>
        </script>
    ';

    return $output;
}

add_shortcode('my-shortcode', 'custom_shortcode');
```

Then, it can be called in a post body using `[my-shortcode name="John Not Doe"]` or via code by `do_shortcode([my-shortcode name="John Not Doe"])`.

## Installation
- Copy and paste everything from `functions.php` into your theme's function.php file;
- Either add `additional.css` to your theme's folder and reference it inside `functions.php` or acess "Appearences" and go into "Personalize" from the WP admin side-pannel and add all the CSS into "Additional CSS" tab;
- Throw all the `.js` files inside your theme's folder and properly reference them inside `functions.php`. As it is, they'll be searched inside "/assets/js" inside your theme's folder.
