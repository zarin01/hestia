<?php
/**
 * Hestia functions and definitions
 *
 * @package Hestia
 * @since   Hestia 1.0
 */

define( 'HESTIA_VERSION', '3.0.17' );
define( 'HESTIA_VENDOR_VERSION', '1.0.2' );
define( 'HESTIA_PHP_INCLUDE', trailingslashit( get_template_directory() ) . 'inc/' );
define( 'HESTIA_CORE_DIR', HESTIA_PHP_INCLUDE . 'core/' );

if ( ! defined( 'HESTIA_DEBUG' ) ) {
	define( 'HESTIA_DEBUG', false );
}

// Load hooks
require_once( HESTIA_PHP_INCLUDE . 'hooks/hooks.php' );

// Load Helper Globally Scoped Functions
require_once( HESTIA_PHP_INCLUDE . 'helpers/sanitize-functions.php' );
require_once( HESTIA_PHP_INCLUDE . 'helpers/layout-functions.php' );

if ( class_exists( 'WooCommerce', false ) ) {
	require_once( HESTIA_PHP_INCLUDE . 'compatibility/woocommerce/functions.php' );
}

if ( function_exists( 'max_mega_menu_is_enabled' ) ) {
	require_once( HESTIA_PHP_INCLUDE . 'compatibility/max-mega-menu/functions.php' );
}

// Load starter content
require_once( HESTIA_PHP_INCLUDE . 'compatibility/class-hestia-starter-content.php' );


/**
 * Adds notice for PHP < 5.3.29 hosts.
 */
function hestia_no_support_5_3() {
	$message = __( 'Hey, we\'ve noticed that you\'re running an outdated version of PHP which is no longer supported. Make sure your site is fast and secure, by upgrading PHP to the latest version.', 'hestia' );

	printf( '<div class="error"><p>%1$s</p></div>', esc_html( $message ) );
}


if ( version_compare( PHP_VERSION, '5.3.29' ) < 0 ) {
	/**
	 * Add notice for PHP upgrade.
	 */
	add_filter( 'template_include', '__return_null', 99 );
	switch_theme( WP_DEFAULT_THEME );
	unset( $_GET['activated'] );
	add_action( 'admin_notices', 'hestia_no_support_5_3' );

	return;
}

/**
 * Begins execution of the theme core.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function hestia_run() {

	require_once HESTIA_CORE_DIR . 'class-hestia-autoloader.php';
	$autoloader = new Hestia_Autoloader();

	spl_autoload_register( array( $autoloader, 'loader' ) );

	new Hestia_Core();

	$vendor_file = trailingslashit( get_template_directory() ) . 'vendor/composer/autoload_files.php';
	if ( is_readable( $vendor_file ) ) {
		$files = require_once $vendor_file;
		foreach ( $files as $file ) {
			if ( is_readable( $file ) ) {
				include_once $file;
			}
		}
	}
	add_filter( 'themeisle_sdk_products', 'hestia_load_sdk' );

	if ( class_exists( 'Ti_White_Label', false ) ) {
		Ti_White_Label::instance( get_template_directory() . '/style.css' );
	}
}

/**
 * Loads products array.
 *
 * @param array $products All products.
 *
 * @return array Products array.
 */
function hestia_load_sdk( $products ) {
	$products[] = get_template_directory() . '/style.css';

	return $products;
}

require_once( HESTIA_CORE_DIR . 'class-hestia-autoloader.php' );

/**
 * The start of the app.
 *
 * @since   1.0.0
 */
hestia_run();

/**
 * Append theme name to the upgrade link
 * If the active theme is child theme of Hestia
 *
 * @param string $link - Current link.
 *
 * @return string $link - New upgrade link.
 * @package hestia
 * @since   1.1.75
 */
function hestia_upgrade_link( $link ) {

	$theme_name = wp_get_theme()->get_stylesheet();

	$hestia_child_themes = array(
		'orfeo',
		'fagri',
		'tiny-hestia',
		'christmas-hestia',
		'jinsy-magazine',
	);

	if ( $theme_name === 'hestia' ) {
		return $link;
	}

	if ( ! in_array( $theme_name, $hestia_child_themes, true ) ) {
		return $link;
	}

	$link = add_query_arg(
		array(
			'theme' => $theme_name,
		),
		$link
	);

	return $link;
}

add_filter( 'hestia_upgrade_link_from_child_theme_filter', 'hestia_upgrade_link' );

/**
 * Check if $no_seconds have passed since theme was activated.
 * Used to perform certain actions, like displaying upsells or add a new recommended action in About Hestia page.
 *
 * @param integer $no_seconds number of seconds.
 *
 * @return bool
 * @since  1.1.45
 * @access public
 */
function hestia_check_passed_time( $no_seconds ) {
	$activation_time = get_option( 'hestia_time_activated' );
	if ( ! empty( $activation_time ) ) {
		$current_time    = time();
		$time_difference = (int) $no_seconds;
		if ( $current_time >= $activation_time + $time_difference ) {
			return true;
		} else {
			return false;
		}
	}

	return true;
}

/**
 * Legacy code function.
 */
function hestia_setup_theme() {
	return;
}




/**
 * Register Custom Post Type
 */

add_action( 'init', 'email_location_post_type' );
function email_location_post_type() {
    register_post_type('email_post_type', array(
        'description' => 'Last Location',
        'has_archive' => 'Locations', // The archive slug
        'rewrite' => array('slug' => 'location_email'), // The individual Flipbook slug
        'supports' => array('title', 'thumbnail', 'post-formats', 'page-attributes'),
        'public' => true,
        'show_ui' => true,
        'exclude_from_search' => true,
        'labels' => array(
                'name' => 'locations',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Location',
                'edit' => 'Edit',
                'edit_item' => 'Edit Location',
                'new_item' => 'New Location',
                'view' => 'View Locations',
                'view_item' => 'View Locations',
                'search_items' => 'Search Locations',
                'not_found' => 'No Locations found',
                'not_found_in_trash' => 'No Locations found in Trash',
            )
        )
    );


    
    register_taxonomy('Location-categories', array('email_post_type'), array(
            'public' => true,
            'labels' => array('name' => 'Location Categories', 'singular_name' => 'Location Category'),
            'hierarchical' => true,
            'rewrite' => array('slug' => 'Location-types')
            )
    );
}




function getProductNamesInCart()
{
    $productName = array();
    foreach ( WC()->cart->get_cart() as $cart_item )
    {
        $item = $cart_item['data'];
        if(!empty($item)){
            $product = new WC_product($item->id);
            $productName[] = $product->name;
        }
    }
    return $productName;
}


add_action( 'woocommerce_before_order_notes', 'add_checkout_custom_text_fields', 20, 1 );
function add_checkout_custom_text_fields( $checkout) {
   

	global $wpdb;

    $pickup_locations = $wpdb->get_results("SELECT meta_value
    FROM `wp_piskyd_postmeta`
    WHERE (`meta_key` = 'billing_pickup_location')");

    $all_active_locations = array();
    foreach($pickup_locations as $location){
        $all_active_locations[] = $location->meta_value;
    }

        
        

            woocommerce_form_field("_billing_pickup_location", array(
                'type'      => 'select',
				'required'  => true,
				'options'   => array_combine($all_active_locations, $all_active_locations),
                'class'     => array('form-row-wide'),
                'label'     => __('Pickup Locations', 'woocommerce'),
				'priority'  => 110,
            ), $checkout->get_value("_billing_pickup_location"));
        
    
}




// Save fields in order meta data
add_action('woocommerce_checkout_create_order', 'save_custom_fields_to_order_meta_data', 20, 2 );
function save_custom_fields_to_order_meta_data( $order, $data ) {
        $order->update_meta_data( "_billing_pickup_location", esc_attr( $_POST["_billing_pickup_location"] ) );
}





// Display fields in order edit pages
add_action('woocommerce_admin_order_data_after_billing_address','display_custom_fields_in_admin_order', 20, 1);
function display_custom_fields_in_admin_order( $order ){

            $my_field = get_post_meta($order->get_id(),"_billing_pickup_location", true );
            if (! empty($my_field) ){
                echo '<p><strong>'.__('Pickup Location').':</strong> ' . $my_field . '</p>';
            }
    }


// Display fields in order edit pages
add_action('woocommerce_billing_fields','display_custom_fields_in_billing_info');
function display_custom_fields_in_billing_info( $order ){

            $my_field = get_post_meta($order,"_billing_pickup_location", true );
            if (! empty($my_field) ){
                echo '<p><strong>'.__('Pickup Location').':</strong> ' . $my_field . '</p>';
            }
    }