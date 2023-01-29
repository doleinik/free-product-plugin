<?php

add_action('woocommerce_after_cart_table', 'quadlayers_woocommerce_hooks');

function quadlayers_woocommerce_hooks()
{
    cart_products_list();
}

function getTotal()
{
    global $woocommerce;

    $items = $woocommerce->cart->get_cart();
    $total = 0;
    $categorySale = get_option('free_product_settings')['category_for_free'];

    foreach ($items as $item) {
        $terms = get_the_terms($item['product_id'], 'product_cat');
        foreach ($terms as $term) {
            if ($term->term_id == $categorySale) {
                if (!isset($item['free_product'])) {
                    $total += $item['quantity'];
                }
            }
        }
    }

    return $total;
}

function cart_products_list()
{
    global $woocommerce;

    $items = $woocommerce->cart->get_cart();
    $total = getTotal();
    $countForFree = get_option('free_product_settings')['count_for_free'];
    $categoryFreeProduct = get_option('free_product_settings')['category_free_product'];

    if ($total >= $countForFree) {
        ?>
        <div class="free-product-title">
            Free product
        </div>

        <select name="add_free_product" id="add_free_product">
            <option>
                Select free product
            </option>
            <?php
            $args = [
                'post_type' => 'product',
                'post_status' => 'publish',
                'tax_query' => [
                    [
                        'taxonomy' => 'product_cat',
                        'field' => 'id',
                        'terms' => $categoryFreeProduct,
                    ]
                ],
            ];
            $loop = new WP_Query($args);
            while ($loop->have_posts()) : $loop->the_post(); ?>
                <option value="<?= get_the_ID() ?>">
                    <?php the_title(); ?></a>
                </option>
            <?php endwhile;
            wp_reset_query(); ?>
        </select>
        <input type="hidden" id="free-product" name="free-product" value="free">
        <button type="submit" name="apply_free_product">Apply free product</button>
        <?php
    }
}

add_action('init', 'saveCustomForm');

function saveCustomForm()
{
    if (isset($_POST['add_free_product'])) {
        update_option('choice_free_product_option', $_POST['add_free_product']);
        header('Location: /cart');
    }
}

add_filter('woocommerce_add_cart_item_data', 'filter_add_cart_item_data', 10, 3);

function filter_add_cart_item_data($cart_item_data)
{
    if (!empty ($_POST['free-product'])) {
        $cart_item_data['free_product'] = sanitize_text_field($_POST['free-product']);
    }
    return $cart_item_data;
}

add_filter('woocommerce_get_item_data', 'filter_woocommerce_get_item_data', 99, 2);

function filter_woocommerce_get_item_data($cart_data, $cart_item = null)
{
    if (isset($cart_item['free_product'])) {
        $cart_data[] = array(
            'name' => 'This is',
            'value' => 'free'
        );
    }
    return $cart_data;
}

add_action('woocommerce_before_calculate_totals', 'add_custom_price', 1000, 1);


function add_custom_price($cart)
{
    if (did_action('woocommerce_before_calculate_totals') >= 2) {
        return;
    }
    $total = getTotal();
    $countForFree = get_option('free_product_settings')['count_for_free'];

    if ($total < $countForFree) {
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if (isset($cart_item['free_product'])) {
                WC()->cart->remove_cart_item($cart_item_key);
            }
        }
    }
    
    $freeProduct = get_option('choice_free_product_option');
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if (!isset($cart_item['free_product'])) {
            if (isset($_POST['add_free_product'])) {
                WC()->cart->add_to_cart($freeProduct);
            }
        } else {
            if (isset($_POST['add_free_product'])) {
                if ((int)$cart_item_key !== (int)$freeProduct) {
                    WC()->cart->remove_cart_item($cart_item_key);
                    WC()->cart->add_to_cart($freeProduct);
                }
            }
            $cart_item['data']->set_price(0);
            WC()->cart->set_quantity($cart_item_key, 1);
        }
    }
} ?>

