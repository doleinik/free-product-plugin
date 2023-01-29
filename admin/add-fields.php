<?php

function add_free_product_plugin_menu()
{
    add_submenu_page(
        'options-general.php',
        'Free product',
        'Free product',
        'manage_options',
        'free-product-plugin',
        'free_product_plugin_function'
    );
}

add_action('admin_menu', 'add_free_product_plugin_menu');

function free_product_settings_init()
{
    register_setting('free-product-setting', 'free_product_settings');
    add_settings_section('free-product-plugin-section', __('Free products', 'free-product-plugin'), 'free_product_settings_section_callback', 'free-product-setting');
    add_settings_field('category_for_free', __('Discount category:', 'free-product-plugin'), 'category_for_free', 'free-product-setting', 'free-product-plugin-section');
    add_settings_field('count_for_free', __('Number of products from this category:', 'free-product-plugin'), 'count_for_free', 'free-product-setting', 'free-product-plugin-section');
    add_settings_field('category_free_product', __('Free products category:', 'free-product-plugin'), 'category_free_product', 'free-product-setting', 'free-product-plugin-section');
}

add_action('admin_init', 'free_product_settings_init');

function free_product_settings_section_callback()
{
    echo __('Select category and quantity for free products', 'free-product-plugin');
}

function category_for_free()
{
    $woocommerce_category_id = get_queried_object_id();
    $args = array(
        'parent' => $woocommerce_category_id,
        'hide_empty' => false
    );
    $terms = get_terms('product_cat', $args);

    $options = get_option('free_product_settings');
    if (!$options) {
        $options = '';
    } else {
        $options = $options['category_for_free'];
    }
    ?>
    <select name='free_product_settings[category_for_free]'>
        <?php foreach ($terms as $term) {
            echo '<option value="' . $term->term_id . '" '. selected($options, $term->term_id).' >' . $term->name . '</option>';
        } ?>
    </select>
    <?php
}

function count_for_free()
{
    $options = get_option('free_product_settings');
    if (!$options) {
        $options = '';
    } else {
        $options = $options['count_for_free'];
    } ?>
    <input type='text' name='free_product_settings[count_for_free]' value='<?= $options; ?>'> <?php
}

function category_free_product()
{
    $woocommerce_category_id = get_queried_object_id();
    $args = array(
        'parent' => $woocommerce_category_id,
        'hide_empty' => false
    );
    $terms = get_terms('product_cat', $args);

    $options = get_option('free_product_settings');
    if (!$options) {
        $options = '';
    } else {
        $options = $options['category_free_product'];
    }
    ?>
    <select name='free_product_settings[category_free_product]'>
        <?php foreach ($terms as $term) {
            echo '<option value="' . $term->term_id . '" '. selected($options, $term->term_id).' >' . $term->name . '</option>';
        } ?>
    </select>
    <?php
}

function free_product_plugin_function()
{ ?>
    <form action='options.php' method='post'> <?php
        settings_fields('free-product-setting');
        do_settings_sections('free-product-setting');
        submit_button(); ?>
    </form> <?php
}
