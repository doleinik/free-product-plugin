<?php
/*
Plugin Name: Free product
Description: Free product for Woocommerce sites
Version: 1.0.0
Author: Dima Oleinik
Author URI: https://doleinik-portfolio.netlify.app/
Copyright: Doleinik
Text Domain: free_product
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    require_once __DIR__ . '/admin/add-fields.php';
    require_once __DIR__ . '/admin/cart.php';
}
