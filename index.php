<?php
 
/**
 * Plugin Name: Traction Apps
 * Plugin URI: https://tractionshop.co/connect
 * Description: Traction Connect for Standalone stores
 * Version: 1.2.0
 * Author: Traction Apps
 * Author URI: https://tractionshop.co/author
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: /lang
 * Text Domain: traction
 */
 
    if ( ! defined( 'WPINC' ) ) {
     
        die;
     
    }

     function tractionapps_includes() { 

     	if ( ! defined( 'TRACTIONAPPS_PLUGIN_DIR' ) ) {
			define( 'TRACTIONAPPS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}
		
		require_once TRACTIONAPPS_PLUGIN_DIR . '/inc/functions.php';
		require_once TRACTIONAPPS_PLUGIN_DIR . '/class-wc-gateway-monnify.php';
		require_once TRACTIONAPPS_PLUGIN_DIR . '/class-wc-gateway-traction-paystack.php';
        require_once TRACTIONAPPS_PLUGIN_DIR . '/traction-connection-settings.php';
        require_once TRACTIONAPPS_PLUGIN_DIR . '/shipping.php';
		require_once TRACTIONAPPS_PLUGIN_DIR . '/functions.php';
	    require_once TRACTIONAPPS_PLUGIN_DIR . '/tgpa.php';

	}
	tractionapps_includes();



    function tractionapps_activation_setup($plugin){
    	if( $plugin == plugin_basename( __FILE__ ) ) {
        	exit( wp_redirect( admin_url( 'options-general.php?page=traction-connect' ) ) );
    	}

    	tractionapps_create_required_tables();
    // 	$settings = get_option( 'woocommerce_bacs_settings');
    //     $settings['enabled'] = 'no';
    //     update_option('woocommerce_bacs_settings', $settings);
    }
    register_activation_hook(__FILE__, 'tractionapps_activation_setup');


    function tractionapps_create_required_tables(){
    	global $wpdb;
    	$prefix = $wpdb->prefix.'private';
    	$query ="CREATE TABLE $prefix ( `ID` INT NOT NULL AUTO_INCREMENT , `c_key` TEXT NOT NULL , `c_secret` TEXT NOT NULL , `date` DATETIME NOT NULL , PRIMARY KEY (`ID`)) ENGINE = InnoDB;";
    	$wpdb->query($query);

    	$prefix = $wpdb->prefix.'private_tokens';
    	$query = "CREATE TABLE $prefix ( `ID` INT NOT NULL AUTO_INCREMENT , `token` TEXT NOT NULL , `expiry_date` DATETIME NOT NULL , PRIMARY KEY (`ID`)) ENGINE = InnoDB;";
    	$wpdb->query($query);
    }

	add_action( 'rest_api_init', function(){

        register_rest_route( 'traction-connect/v1','/PushAPIKeys', array(
            'methods' => 'POST',
            //'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'tractionapps_post_api_keys',
            //'args' => traction_func_args(),
            'permission_callback' => '__return_true'
        ));
        register_rest_route( 'tractionpay-api/v1','/PushPaymentSettings', array(
            'methods' => 'POST',
            //'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'tractionpay_pps_func',
            //'args' => traction_func_args(),
            'permission_callback' => '__return_true'
        ));

        register_rest_route( 'tractionpay-api/v1','/TurnPaystackPaymentSettingsOn', array(
            'methods' => 'POST',
            //'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'tractionpay_pps_on_func',
            //'args' => traction_func_args(),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route( 'tractionpay-api/v1','/TogglePaystackPaymentSettings', array(
            'methods' => 'POST',
            //'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'tractionpay_pps_toggle_func',
            //'args' => traction_func_args(),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route( 'tractionpay-api/v1','/CheckMonnifyPaymentSettings', array(
            'methods' => 'POST',
            //'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'tractionpay_mps_check',
            //'args' => traction_func_args(),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route( 'tractionpay-api/v1','/CheckBankTransferPaymentSettings', array(
            'methods' => 'POST',
            //'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'tractionpay_btps_check',
            //'args' => traction_func_args(),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route( 'tractionpay-api/v1','/CheckPaystackPaymentSettings', array(
            'methods' => 'POST',
            //'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'tractionpay_pps_check',
            //'args' => traction_func_args(),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route( 'tractionpay-api/v1','/UpdateSubAccount', array(
            'methods' => 'POST',
            //'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'tractionpay_subacct_func',
            //'args' => traction_func_args(),
            'permission_callback' => '__return_true'
        ));

        register_rest_route( 'tractionpay-api/v1','/TurnMonnifyPaymentSettingsOn', array(
            'methods' => 'POST',
            //'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'tractionpay_mps_on_func',
            //'args' => traction_func_args(),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route( 'tractionpay-api/v1','/TurnBankTransferPaymentSettingsOn', array(
            'methods' => 'POST',
            //'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'tractionpay_btps_on_func',
            //'args' => traction_func_args(),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route( 'tractionpay-api/v1','/UpdateBankDetailsForBankTransfer', array(
            'methods' => 'POST',
            //'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'tractionpay_btps_bankdetails_func',
            //'args' => traction_func_args(),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route( 'tractionpay-api/v1','/ToggleMonnifyPaymentSettings', array(
            'methods' => 'POST',
            //'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'tractionpay_mps_toggle_func',
            //'args' => traction_func_args(),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route( 'tractionpay-api/v1','/ToggleBankTransferPaymentSettings', array(
            'methods' => 'POST',
            //'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'tractionpay_btps_toggle_func',
            //'args' => traction_func_args(),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route( 'tractionpay-api/v1','/GetBankTransferPaymentDetails', array(
            'methods' => 'POST',
            //'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'tractionpay_btps_get_details',
            //'args' => traction_func_args(),
            'permission_callback' => '__return_true'
        ));
        
        
        register_rest_route( 'tractionpay-api/v1','/SetShippingZone', array(
            'methods' => 'POST',
            //'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'trac_set_shipping_zone',
            //'args' => traction_func_args(),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route( 'tractionpay-api/v1','/GetShippingZones', array(
            'methods' => 'GET',
            //'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'trac_get_shipping_zones',
            //'args' => traction_func_args(),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route( 'tractionpay-api/v1','/UpdateShippingZone', array(
            'methods' => 'POST',
            //'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'trac_update_shipping_zones',
            //'args' => traction_func_args(),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route( 'tractionpay-api/v1','/banktransferwebhook', array(
            'methods' => 'POST',
            //'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'trac_bank_tranfer_webhook',
            //'args' => traction_func_args(),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route( 'tractionpay-api/v1','/paystackwebhook', array(
            'methods' => 'POST',
            //'methods' => WP_REST_SERVER::READABLE,
            'callback' => 'trac_paystack_webhook',
            //'args' => traction_func_args(),
            'permission_callback' => '__return_true'
        ));

    });


    function tractionapps_post_api_keys($request){

    	$params = $request->get_params();
    	//print_r($params);
    	$consumer_key = $params['consumer_key'];
    	$consumer_secret = $params['consumer_secret'];
    	//$main_store = $params['main_store'];

    	$res = tractionapps_insert_keys($consumer_key, $consumer_secret);
    	$status = tractionapps_authorize_woocommerce_app($consumer_key, $consumer_secret);
    	//error_log($status);
    	if($res > 1){
    		return true;
    	}

    }
    
    if ( ! defined( 'WPINC' ) ) {
 
        die;
 
    }
 

    
    