<?php

add_action('woocommerce_after_cart_table', 'quadlayers_woocommerce_hooks', 10);

function quadlayers_woocommerce_hooks($cart)
{
    global $woocommerce;
    $items = $woocommerce->cart->get_cart();
    $total = getTotal();
    $countForFree = get_option('free_product_settings')['count_for_free'];
    $categoryFreeProduct = get_option('free_product_settings')['category_free_product'];
    $freeProduct = get_option('choice_free_product_option');
    $status = true;
    foreach ($items as $cart_item) {
        if (isset($cart_item['free_product'])) {
            $status = false;
            update_option('choice_free_product_option', ['id' => '', 'status' => false]);
        }
    }
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
    if ($total >= $countForFree && $status && $loop->post_count > 0) {
        ?>
        <div class="free-product-title">
            Free product
        </div>

        <select name="add_free_product" id="add_free_product">
            <option>
                Select free product
            </option>
            <?php

            while ($loop->have_posts()) : $loop->the_post(); ?>
                <option value="<?= get_the_ID() ?>"
                    <?php if (get_the_ID() == (int)$freeProduct['id']) {
                        echo 'selected';
                    } ?>>
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


add_action('init', 'saveCustomForm');

function saveCustomForm()
{
    if (isset($_POST['add_free_product'])) {
        update_option('choice_free_product_option', ['id' => $_POST['add_free_product'], 'status' => true]);
        $url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('Location: ' . $url);
    }
}

add_action('woocommerce_before_calculate_totals', 'add_custom_price', 101, 4);

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
        update_option('choice_free_product_option', ['id' => '', 'status' => false]);
    }

    $freeProduct = get_option('choice_free_product_option');
    foreach ($cart->get_cart() as $cart_item) {
        if (isset($cart_item['free_product'])) {
            if ($cart_item['quantity'] > 1) {
                wc_add_notice(__('Sorry, you cannot change the quantity of this product.', 'woocommerce'), 'error');
            }
        }
    }

    if (isset($freeProduct['status']) && isset($freeProduct['id'])) {
        if ($freeProduct['status']) {
            WC()->cart->add_to_cart($freeProduct['id'], 1, 0, array(), ['free_product' => 'true']);
        }

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if (isset($cart_item['free_product'])) {
                $cart_item['data']->set_price(0);
                WC()->cart->set_quantity($cart_item_key, 1);
            }
        }
    }
}
?>