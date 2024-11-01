<?php

	function tractionapps_monnify_load_scripts(){
		
			$my_js_ver  = '1.0';

			wp_enqueue_script( 'tractionPayMonnify_appjs', plugins_url( '/app.js', __FILE__ ), array('jquery'), '', true );
			wp_enqueue_script( 'tractionPayPopup_js', plugins_url( 'assets/jquery.simple-popup.min.js', __FILE__ ), array('jquery'), '', true );
			
			
    		wp_register_style('tractioPayPopup_maincss',  plugins_url( '/assets/jquery.simple-popup.min.css', __FILE__ ), '','');
    		wp_register_style('tractioPayPopup_settingscss',  plugins_url( '/assets/jquery.simple-popup.settings.css', __FILE__ ), '','');
		    wp_enqueue_style ( 'tractioPayPopup_maincss' );
		    wp_enqueue_style ( 'tractioPayPopup_settingscss' );
			

		}
	add_action('wp_enqueue_scripts', 'tractionapps_monnify_load_scripts');

	function tractionapps_Monnify_addAjaxFrontend(){

		?>
			<script>
				var ajax_url = "<?php echo admin_url('admin-ajax.php') ?>";
			</script>
		<?php
	}
	add_action('wp_head', 'tractionapps_Monnify_addAjaxFrontend');
	
	function tractionapps_sirl_check_token($usr_token){
        	$token = 'fee93cfd';
        	//Will generate token later later...
        	if($token == $usr_token){
            	return true;
        	}else{
            	return false;
        	}
    }
    
    function tractionapps_save_settings(){
        $bank_transfer = sanitize_text_field($_POST['bank_transfer']);
        $card_payments = sanitize_text_field($_POST['card_payments']);
        
        $bank_transfer = ($bank_transfer == "on")?1:0;
        $card_payments = ($card_payments == "on")?1:0;
        
        update_option('tractionapps_settings_bank_transfer', $bank_transfer);
        update_option('tractionapps_settings_card_payment', $card_payments);
        
        echo json_encode(1);
        die();
    }
    add_action('wp_ajax_tractionapps_save_settings', 'tractionapps_save_settings');
    add_action('wp_ajax_nopriv_tractionapps_save_settings', 'tractionapps_save_settings');

	function traction_monnify_payment_action(){

		$price = sanitize_text_field($_POST['price']);
		$storeUrl = get_site_url().'/';
		$orderId = sanitize_text_field($_POST['orderId']);
		$hash = sanitize_text_field($_POST['hash']);
		$currency = sanitize_text_field($_POST['currency']);
		
		
    	$protocols = array('http://', 'http://www','www.', 'https://', 'https://www');
    	$store_url = str_replace($protocols, '', $storeUrl);
    	$store_url = $store_url."/";

		$paidOn = date('Y-m-d H:i:s');
        
        $hash = 'DAWGzZa7934A!t20183BG3|'.$storeUrl.'|'.$orderId.'|'.$price;
    	$transactionHash = hash('sha512', $hash);
    	
    	$businessId = get_option('business_id');
    	$main_store = get_option('main_store');
        
		$postRequest = array(
    		"price" =>  $price,
    		"storeUrl" =>  $storeUrl,
    		"orderId" =>  $orderId,
    		"transactionHash" => $transactionHash,
    		"currency" => $currency,
    		"paymentMethod" => "monnify",
    		"businessId" => $businessId,
    		"storeId" => $main_store
		);
	    


		$url = "https://tractionapps.herokuapp.com/multistore/monnify/";
		//$url = "https://tractionapp-stage.herokuapp.com/multistore/monnify/";
	    $response = wp_remote_post( $url, array(
	        'method'      => 'POST',
	        'timeout'     => 45,
	        'redirection' => 5,
	        'httpversion' => '1.0',
	        'blocking'    => true,
	        'headers'     => array(),
	        'data_format' => 'body',
	        'body'        => json_encode($postRequest),
	        'cookies'     => array()
	        )
	    );
	 
	    if ( is_wp_error( $response ) ) {
	        
	        $error_message = $response->get_error_message();
	        $status =  "Something went wrong: $error_message";
	        
	    } else {
	        
	        $status =  $response['body'];
	        
	    }
        
	    echo $status;
	   //echo json_encode($postRequest);
	    die();
	}
	add_action('wp_ajax_traction_monnify_payment_action', 'traction_monnify_payment_action');
	add_action('wp_ajax_nopriv_traction_monnify_payment_action', 'traction_monnify_payment_action');
	
	function traction_paystack_payment_action(){

		$price = sanitize_text_field($_POST['price']);
		$orderId = sanitize_text_field($_POST['orderId']);
		$currency = sanitize_text_field($_POST['currency']);
		$paidOn = date('Y-m-d H:i:s');
        
        
    	$businessId = get_option('business_id');
    	$main_store = get_option('main_store');
    	
    	$storeUrl = get_site_url();
    	$protocols = array('http://', 'http://www','www.', 'https://', 'https://www');
    	$store_url = str_replace($protocols, '', $storeUrl);
    	$storeUrl = $store_url."/";
        
        $hash = 'DAWGzZa7934A!t20183BG3|'.$storeUrl.'|'.$orderId.'|'.$price;
    	$transactionHash = hash('sha512', $hash);
    
        
        
		$postRequest = array(
    		"price" =>  $price,
    		"storeUrl" =>  $storeUrl,
    		"orderId" =>  $orderId,
    		"transactionHash" => $transactionHash,
    		"currency" => $currency,
    		"paymentMethod" => "paystack",
    		"businessId" => $businessId,
    		"storeId" => $main_store,
    		"redirectUrl" => $storeUrl.'checkout/order-received'
		);
	    


		$url = "https://tractionapps.herokuapp.com/multistore/paystackpay/";
		//$url = "https://tractionapp-stage.herokuapp.com/multistore/monnify/";
	    $response = wp_remote_post( $url, array(
	        'method'      => 'POST',
	        'timeout'     => 45,
	        'redirection' => 5,
	        'httpversion' => '1.0',
	        'blocking'    => true,
	        'headers'     => array(),
	        'data_format' => 'body',
	        'body'        => json_encode($postRequest),
	        'cookies'     => array()
	        )
	    );
	 
	    if ( is_wp_error( $response ) ) {
	        
	        $error_message = $response->get_error_message();
	        $status =  "Something went wrong: $error_message";
	        
	    } else {
	        
	        $status =  $response['body'];
	        
	    }
	    
	    //print_r($status);
        
	    $response = json_decode($status, true);
	    if(isset($response['data']) )
	        echo json_encode($response['data']['paymentLink']);
	    
	    die();
	}
	add_action('wp_ajax_traction_paystack_payment_action', 'traction_paystack_payment_action');
	add_action('wp_ajax_nopriv_traction_paystack_payment_action', 'traction_paystack_payment_action');
	
	function traction_monnify_update_order(){
	    
	    $order_id = sanitize_text_field($_POST['order_id']);
	    
	    $payment_status = get_post_meta($order_id, 'trac_bank_payment_received', true);
	    
	    //echo 'Payment status =>'.$payment_status;
	    
	    if( $payment_status > 0 ){
	    
    	    $order = wc_get_order( $order_id );
    	    
            // Mark as processing (payment received)
            $order->update_status( 'processing', __( 'Payment received', 'wc-gateway-offline' ) );
                            
            // Reduce stock levels
            $order->reduce_order_stock();
            
            echo json_encode(1);
            
	    }else{
	        
	        echo json_encode(0);
	        
	    }
        die();
	}
	add_action('wp_ajax_traction_monnify_update_order', 'traction_monnify_update_order');
	add_action('wp_ajax_nopriv_traction_monnify_update_order', 'traction_monnify_update_order');
	
	
	function tractionpay_pps_func($request){
	
     	$params = $request->get_params();
	    $test_mode = sanitize_text_field($params['test_mode']);
	    $public_key = sanitize_text_field($params['public_key']);
	    $public_test_key = sanitize_text_field($params['public_test_key']);
	    $secret_key = sanitize_text_field($params['secret_key']);
	    $secret_test_key = sanitize_text_field($params['secret_test_key']);
	    $subaccount_code = sanitize_text_field($params['subaccount_code']);
	    $token = sanitize_text_field($params['token']);

     	 if(tractionapps_sirl_check_token($token) == true && !empty($token) && !empty($public_key) && !empty($public_test_key) && !empty($secret_key) && !empty($secret_test_key) && !empty($subaccount_code) ){

	     	$settings = get_option('woocommerce_paystack_settings');
	     	$settings['enabled'] = 'yes';
	     	update_option('woocommerce_paystack_settings', $settings);


	     	 

	     	 $paystack = new WC_Gateway_Paystack();
	     	 $paystack->update_option('live_public_key', $public_key);
	     	 $paystack->update_option('test_public_key', $public_test_key);
	     	 $paystack->update_option('live_secret_key', $secret_key);
	     	 $paystack->update_option('test_secret_key', $secret_test_key);
	     	 $paystack->update_option('split_payment', 'yes');
	     	 $paystack->update_option('subaccount_code', $subaccount_code);
	     	 $paystack->update_option('testmode', $test_mode);
	     	 

	     	 $data = array(
	     	 	'status' => 'success',
	     	 	'remarks' => '7 settings were updated'
	     	 );
	     	 return $data;
	    }else{

	    	$data = array(
	    		'status' => 'error',
	    		'remarks' => 'Required fields were found empty'
	    	);
	    	return $data;
	    }
	    

     }
     
     function tractionpay_mps_on_func($request){
        $params = $request->get_params();
        $token = sanitize_text_field($params['token']);
        if(tractionapps_sirl_check_token($token) == true){

	   	   $settings['enabled'] = 'yes';
	   	   $settings['title'] = 'Bank Transfer Using Monnify';
	   	   $settings['description'] = 'Transfer cash from your bank to this merchant';
	   	   $flag = update_option('woocommerce_tractionpay_settings', $settings);
           $settings = get_option('woocommerce_tractionpay_settings');
	     
	     	 $data = array(
	     	 	'status' => 'success',
	     	 	'remarks' => 'Monnify was turned on successfully'
	     	 	//'results' => $settings
	     	 );
	     	 return $data;
	    }else{
	        $data = array(
	     	 	'status' => 'error',
	     	 	'remarks' => 'Token is wrong'
	     	 	//'results' => $settings
	     	 );
	     	return $data;
	        
	    }
     }


    function tractionpay_btps_on_func($request){
        $params = $request->get_params();
        $token = $params['token'];
        //Format: GTBank~~Tobi Lekan Adeosun~~0102003344
        //        Bank~~Acct_name~~Acct_num
        $bank_details = sanitize_text_field($params['bank_details']);
        if(tractionapps_sirl_check_token($token) == true){

	   	   $settings['enabled'] = 'yes';
	   	   $settings['title'] = 'TractionPay Direct Bank Transfer';
	   	   $settings['description'] = 'Transfer cash from your bank to this merchant';
	   	   $settings['payment_description'] = $bank_details;
	   	   $flag = update_option('woocommerce_tractionpay_banktransfer_settings', $settings);
           $settings = get_option('woocommerce_tractionpay_banktransfer_settings');
	     
	     	 $data = array(
	     	 	'status' => 'success',
	     	 	'remarks' => 'TractionPay Direct Bank Transfer was turned on successfully',
	     	 	'results' => $settings
	     	 );
	     	 return $data;
	     	 
	    }else{
	        $data = array(
	     	 	'status' => 'error',
	     	 	'remarks' => 'Token is wrong'
	     	 	//'results' => $settings
	     	 );
	     	return $data;
	        
	    }
	    return false;
    }




     function traction_alert_local_app($order_total, $order_id, $store_url, $paystack_ref, $payment_currency){
     	//curl nigga
     	$paidOn = date('Y-m-d H:i:s');
     	$transactionHash = hash('ripemd128', 'bgHfzZa7934At20183BG3').'|'.hash('ripemd128', $store_url).'|'.hash('ripemd128', $order_id).'|'.hash('ripemd128', $order_total);
     	traction_update_tranx_tbl($paystack_ref, $order_id, $paidOn, $order_total, '');

     	$postRequest = array(
    		"amountPaid" =>  $order_total,
			"total" =>  $order_total,
			"paidOn" =>  $paidOn,
			"orderId" =>  $order_id,
			"storeUrl" =>  $store_url,
			"transactionHash" => $transactionHash,
			"currency" => $payment_currency,
			"paymentMethod" => "paystack"
		);
		
		
		
		$url = "https://tractionapps.herokuapp.com/multistore/paystack/";
		//$url = "https://tractionapp-stage.herokuapp.com/multistore/monnify/";
	    $response = wp_remote_post( $url, array(
	        'method'      => 'POST',
	        'timeout'     => 45,
	        'redirection' => 5,
	        'httpversion' => '1.0',
	        'blocking'    => true,
	        'headers'     => array(),
	        'data_format' => 'body',
	        'body'        => json_encode($postRequest),
	        'cookies'     => array()
	        )
	    );
	 
	    if ( is_wp_error( $response ) ) {
	        
	        $error_message = $response->get_error_message();
	        $status =  "Something went wrong: $error_message";
	        
	    } else {
	        
	        $status =  $response['body'];
	        
	    }


// 		$cURLConnection = curl_init('https://tractionapps.herokuapp.com/multistore/paystack/');
// 		curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $postRequest);
// 		curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

// 		$apiResponse = curl_exec($cURLConnection);
// 		curl_close($cURLConnection);

// 		// $apiResponse - available data from the API request
// 		$jsonArrayResponse - json_decode($apiResponse);
     }
     
     function traction_update_tranx_tbl($transaction_ref, $order_id, $date, $amount, $call_url){
         global $wpdb;
         $prefix = $wpdb->prefix."wp6789_tractionpay_records";
         
         $postman = $wpdb->query($wpdb->prepare("INSERT INTO `wp6789_tractionpay_records` ( `transaction_ref`, `order_id`, `date`, `amount`, `call_url`) VALUES (%s, %d, %s, %d, %s)",$transaction_ref, $order_id, $date, $amount, $call_url) );
         //$wpdb->query($wpdb->prepare("INSERT INTO $wpdb->postmeta( post_id, meta_key, meta_value )VALUES ( %d, %s, %s )",10,$metakey,$metavalue));
         return $wpdb->insert_id;
     }
     
     
     function tractionpay_subacct_func(){
        $params = $request->get_params();
	    $token = $params['token'];
	    $sub_account_code = $params['subaccount_code'];
     	if(tractionapps_sirl_check_token($token) == true){
	     	$settings = get_option('woocommerce_paystack_settings');
	     	$settings['subaccount_code'] = $sub_account_code;
	     	update_option('woocommerce_paystack_settings', $settings);

	     	 $data = array(
	     	 	'status' => 'success',
	     	 	'remarks' => '1 setting was updated'
	     	 );
	     	 return $data;
	    }else{
	        $data = array(
	     	 	'status' => 'error',
	     	 	'remarks' => 'Token is wrong'
	     	 	//'results' => $settings
	     	 );
	     	return $data;
	        
	    }
	    return false;
     }
     
    function tractionpay_pps_toggle_func($request){
     	$params = $request->get_params();
	    $token = $params['token'];
	    //state can either be 'yes' or 'no'
	    $new_state = $params['state'];
     	if(tractionapps_sirl_check_token($token) == true){
     	    if( $new_state != '' || !empty($new_state) ){
    	     	$settings = get_option('woocommerce_paystack_settings');
    	     	$settings['enabled'] = $new_state;
    	     	update_option('woocommerce_paystack_settings', $settings);
                $remarks = ($new_state == 'no')?'Paystack settings was turned off':'Paystack settings was turned on';
    	     	 $data = array(
    	     	 	'status' => 'success',
    	     	 	'remarks' => $remarks,
    	     	 	//'state' => $settings
    	     	 );
    	     	 return $data;
     	    }else{
     	        $data = array(
    	     	 	'status' => 'error',
    	     	 	'remarks' => 'You didn\'t specify the state parameter' 
    	     	 );
    	     	 return $data;
     	    }
	    }else{
	        $data = array(
	     	 	'status' => 'error',
	     	 	'remarks' => 'Token is wrong'
	     	 	//'results' => $settings
	     	 );
	     	return $data;
	        
	    }
	    return false;
     }
     
    function tractionpay_pps_check($request){
        $params = $request->get_params();
	    $token = $params['token'];
	   // $sub_account_code = $params['subaccount_code'];
	    $site_url = home_url();
     	if(tractionapps_sirl_check_token($token) == true){
	     	$settings = get_option('woocommerce_paystack_settings');
	     	$paystack_status = $settings['enabled'];
	     	//update_option('woocommerce_paystack_settings', $settings);
            $remark = ($paystack_status == 'no')?'Paystack is currently turned off':'Paystack is currently turned on';
            $state  = ($paystack_status == 'no')?'off':'on';
	     	 $data = array(
	     	 	'status' => 'success',
	     	 	'state' => $state,
	     	 	'remarks' => $remark,
	     	 	'details' => $settings
	     	 );
	     	 return $data;
	    }else{
	        $data = array(
	     	 	'status' => 'error',
	     	 	'remarks' => 'Token is wrong'
	     	 	//'results' => $settings
	     	 );
	     	return $data;
	        
	    }
	    return false;
    }
    
    function tractionpay_mps_check($request){
         $params = $request->get_params();
	    $token = $params['token'];
	   // $sub_account_code = $params['subaccount_code'];
	    $site_url = home_url();
     	if(tractionapps_sirl_check_token($token) == true){
	     	$settings = get_option('woocommerce_tractionpay_settings');
	     	$paystack_status = $settings['enabled'];
	     	//update_option('woocommerce_paystack_settings', $settings);
            $remark = ($paystack_status == 'no')?'Monnify is currently turned off':'Monnify is currently turned on';
            $state  = ($paystack_status == 'no')?'off':'on';
	     	 $data = array(
	     	 	'status' => 'success',
	     	 	'state' => $state,
	     	 	'remarks' => $remark,
	     	 	'details' => $settings
	     	 );
	     	 return $data;
	    }else{
	        $data = array(
	     	 	'status' => 'error',
	     	 	'remarks' => 'Token is wrong'
	     	 	//'results' => $settings
	     	 );
	     	return $data;
	        
	    }
	    return false;
    }
    
    function tractionpay_btps_check($request){
         $params = $request->get_params();
	    $token = $params['token'];
	   // $sub_account_code = $params['subaccount_code'];
	    $site_url = home_url();
     	if(tractionapps_sirl_check_token($token) == true){
	     	$settings = get_option('woocommerce_tractionpay_banktransfer_settings');
	     	$paystack_status = $settings['enabled'];
	     	//update_option('woocommerce_paystack_settings', $settings);
            $remark = ($paystack_status == 'no')?'Bank Transfer is currently turned off':'Bank Transfer is currently turned on';
            $state  = ($paystack_status == 'no')?'off':'on';
            $settings = ( $settings == false )?'Bank Transfer is not activated on this system':$settings;
	     	 $data = array(
	     	 	'status' => 'success',
	     	 	'state' => $state,
	     	 	'remarks' => $remark,
	     	 	'details' => $settings
	     	 );
	     	 return $data;
	    }else{
	        $data = array(
	     	 	'status' => 'error',
	     	 	'remarks' => 'Token is wrong'
	     	 	//'results' => $settings
	     	 );
	     	return $data;
	        
	    }
	    return false;
    }
    
    
    function tractionpay_btps_bankdetails_func($request){
        $params = $request->get_params();
        $token = $params['token'];
        $bank_details = $params['bank_details'];
        if(tractionapps_sirl_check_token($token) == true){
            //Format: GTBank~~Tobi Lekan Adeosun~~0102003344
            //        Bank~~Acct_name~~Acct_num
            $settings = get_option('woocommerce_tractionpay_banktransfer_settings');
            $settings['payment_description'] = $bank_details;
            $flag = update_option('woocommerce_tractionpay_banktransfer_settings', $settings);
            $data = array(
	     	 	'status' => 'success',
	     	 	'remarks' => $flag,
	     	 	//'check' => $settings
	     	 );
	     	 return $data;
            
        }else{
	        $data = array(
	     	 	'status' => 'error',
	     	 	'remarks' => 'Token is wrong'
	     	 	//'results' => $settings
	     	 );
	     	return $data;
	        
	    }
        return false;
    }
    
    function tractionpay_mps_toggle_func($request){
        $params = $request->get_params();
        //state can either be 'yes' or 'no'
	    $token = $params['token'];
	    $new_state = $params['state'];
     	if(tractionapps_sirl_check_token($token) == true){
     	    if( $new_state != '' || !empty($new_state) ){
    	     	$settings = get_option('woocommerce_tractionpay_settings');
    	     	$settings['enabled'] = $new_state;
    	     	update_option('woocommerce_tractionpay_settings', $settings);
                $remarks = ($new_state == 'no')?'Monnify settings was turned off':'Monnify settings was turned on';
    	     	 $data = array(
    	     	 	'status' => 'success',
    	     	 	'remarks' => $remarks,
    	     	 	//'state' => $settings
    	     	 );
    	     	 return $data;
     	    }else{
     	        $data = array(
    	     	 	'status' => 'error',
    	     	 	'remarks' => 'You didn\'t specify the state parameter' 
    	     	 );
    	     	 return $data;
     	    }
	    }else{
	        $data = array(
	     	 	'status' => 'error',
	     	 	'remarks' => 'Token is wrong'
	     	 	//'results' => $settings
	     	 );
	     	return $data;
	        
	    }
	    return false;
    }
    
    function tractionpay_btps_toggle_func($request){
        $params = $request->get_params();
        //state can either be 'yes' or 'no'
        $token = $params['token'];
	    $new_state = $params['state'];
     	if(tractionapps_sirl_check_token($token) == true){
     	    if( $new_state != '' || !empty($new_state) ){
    	     	$settings = get_option('woocommerce_tractionpay_banktransfer_settings');
    	     	$settings['enabled'] = $new_state;
    	     	update_option('woocommerce_tractionpay_banktransfer_settings', $settings);
                $remarks = ($new_state == 'no')?'Bank Transfer settings was turned off':'Bank Transfer settings was turned on';
    	     	 $data = array(
    	     	 	'status' => 'success',
    	     	 	'remarks' => $remarks,
    	     	 	//'state' => $settings
    	     	 );
    	     	 return $data;
     	    }else{
     	        $data = array(
    	     	 	'status' => 'error',
    	     	 	'remarks' => 'You didn\'t specify the state parameter' 
    	     	 );
    	     	 return $data;
     	    }
	    }else{
	        $data = array(
	     	 	'status' => 'error',
	     	 	'remarks' => 'Token is wrong'
	     	 	//'results' => $settings
	     	 );
	     	return $data;
	        
	    }
	    return false;
    }
    
    
    function trac_set_shipping_zone($request){
        $params = $request->get_params();
        $token = sanitize_text_field($params['token']);
        $states = sanitize_text_field($params['states']);
        $update = sanitize_text_field($params['update']);
        if(tractionapps_sirl_check_token($token) == true){
         
            $datum = create_shipping_zones($states, $update);
             $data = array(
    	     	 	'status' => 'success',
    	     	 	'details' => $datum
    	     	 );
    	     	 return $data;
            
          if( $datum ){
                $data = array(
    	     	 	'status' => 'success',
    	     	 	'details' => $datum
    	     	 );
    	     	 return $data;
          }else{
              $data = array(
    	     	 	'status' => 'error',
    	     	 	'remarks' => 'Couldn\'t add woocommerce records',
    	     	 	'details' => $success_rate
    	     	 );
    	        return $data;
          }
        
        }else{
	        $data = array(
	     	 	'status' => 'error',
	     	 	'remarks' => 'Token is wrong'
	     	 	//'results' => $settings
	     	 );
	     	return $data;
	        
	    }
	    
	    return false;
    }
    
    function create_shipping_zones($states, $update){
        //$states = json_decode($states, true);
        $zone_ids = array();
        $data = array();
        $res = array();
        $res_2 = array();
        
        
        foreach($states as $state){
            $zone_id = trac_set_shipping_zone_step_one($state[0]);
            $cost = $state[1];
            //array_push($res_2, $zone_id);
            //return $res_2;
            if( $update == "false" ){
                
                $set_state = set_state_with_country($state[0], $zone_id[0]);
                $datum = update_shipping_zone_with_traction_zone_method($zone_id[0]);
                $set_cost = trac_set_shipping_cost($cost, $datum[0]['instance_id'], $zone_id[0]);
                array_push($res_2, $set_cost);
                
            }else{
                
                $instance_id = traction_get_shipping_method_instance_id($zone_id[0]);
                $set_cost = trac_set_shipping_cost($cost, $instance_id, $zone_id[0]);
                array_push($res_2, $set_cost);
            }
            
            
        }
        
        return $res_2;
    }
    

    function trac_set_shipping_zone_step_one($state){
        $data = [
            'name' => $state
        ];
        
        $site_url = site_url();
    	$url = $site_url.'/wp-json/wc/v3/shipping/zones';
    	
    	//return $url;
    	
    	$response = wp_remote_post( $url, array(
	        'method'      => 'GET',
	        'timeout'     => 45,
	        'redirection' => 5,
	        'httpversion' => '1.0',
	        'blocking'    => true,
	        'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( 'ck_96112d7cafe7607997df7ecd006907283b7009cc' . ':' . 'cs_a1c17922367ce8d59192ada76f51c151c2af18c7' )
            ),
	        'data_format' => 'body',
	        'body'        => $data,
	        'cookies'     => array()
	        )
	    );
	   $response = json_decode($response['body'], true); 
	   //return $response;
	  
	   
	   $arr = array();
	   foreach($response as $response){
	   
	       if( $response['name'] == $state ){
	            array_push($arr, $response['id']);
	       }
	   }
	   
	   
	   if( empty($arr ) ){
	   
    	
	    $response = wp_remote_post( $url, array(
	        'method'      => 'POST',
	        'timeout'     => 45,
	        'redirection' => 5,
	        'httpversion' => '1.0',
	        'blocking'    => true,
	        'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( 'ck_96112d7cafe7607997df7ecd006907283b7009cc' . ':' . 'cs_a1c17922367ce8d59192ada76f51c151c2af18c7' )
            ),
	        'data_format' => 'body',
	        'body'        => $data,
	        'cookies'     => array()
	        )
	    );
	    
	   // //return $url;
	 
	    if ( is_wp_error( $response ) ) {
	        
	        $error_message = $response->get_error_message();
	        $status =  "Something went wrong: $error_message";
	        
	    } else {
	        
	        $status =  json_decode($response['body'], true);
	        $zone_id  = $status['id'];
	        
	    }
	    
	    return array($zone_id);
	    
	   }else{
	       return $arr;
	   }
    }
    
    function update_shipping_zone_with_traction_zone_method($zone_id){
        $method_id = "flat_rate";
        
         $data = [
            "method_id" => $method_id
        ];
        
        // /wp-json/wc/v3/shipping/zones/<id>/methods
        $site_url = site_url();
    	$url = $site_url.'/wp-json/wc/v3/shipping/zones/'.$zone_id.'/methods';
    	
    	$response = wp_remote_post( $url, array(
	        'method'      => 'GET',
	        'timeout'     => 45,
	        'redirection' => 5,
	        'httpversion' => '1.0',
	        'blocking'    => true,
	        'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( 'ck_96112d7cafe7607997df7ecd006907283b7009cc' . ':' . 'cs_a1c17922367ce8d59192ada76f51c151c2af18c7' )
            ),
	        'data_format' => 'body',
	        'body'        => $data,
	        'cookies'     => array()
	        )
	    );
	    
	   $response = json_decode($response['body'], true); 
	  
	   
	   $arr = array();
	   foreach($response as $response){
	   
	       if( $response['method_id'] == $method_id ){
	            array_push($arr, $response);
	       }
	   }
	   
	   if( !empty($arr) ){
	       return $arr;
	   }else{
    	
    	    $response = wp_remote_post( $url, array(
    	        'method'      => 'POST',
    	        'timeout'     => 45,
    	        'redirection' => 5,
    	        'httpversion' => '1.0',
    	        'blocking'    => true,
    	        'headers' => array(
                    'Authorization' => 'Basic ' . base64_encode( 'ck_96112d7cafe7607997df7ecd006907283b7009cc' . ':' . 'cs_a1c17922367ce8d59192ada76f51c151c2af18c7' )
                ),
    	        'data_format' => 'body',
    	        'body'        => $data,
    	        'cookies'     => array()
    	        )
    	    );
    	 
    	    if( is_wp_error( $response ) ) {
    	        
    	        $error_message = $response->get_error_message();
    	        $status =  "Something went wrong: $error_message";
    	        
    	    }else{
    	        
    	        $status =  json_decode($response['body'], true);
    	        array_push($arr, $status);
    	    }
    	    
    	    return $arr;
	   }
    }
    
    function set_state_with_country($state_name, $zone_id){
        $data = array(
            array(
                "code" => trac_decode_state_codes($state_name),
                "type" => "state"
            )    
        );
        // /wp-json/wc/v3/shipping/zones/<id>/locations
        $site_url = site_url();
    	$url = $site_url.'/wp-json/wc/v3/shipping/zones/'.$zone_id.'/locations';
    	
    	//return $url;
    	
	    $response = wp_remote_post( $url, array(
	        'method'      => 'PUT',
	        'timeout'     => 45,
	        'redirection' => 5,
	        'httpversion' => '1.0',
	        'blocking'    => true,
	        'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( 'ck_96112d7cafe7607997df7ecd006907283b7009cc' . ':' . 'cs_a1c17922367ce8d59192ada76f51c151c2af18c7' ),
                'Content-Type'   => 'application/json',
            ),
	        'data_format' => 'body',
	        'body'        => json_encode($data),
	        'cookies'     => array(),
	        )
	    );
	 
	    if ( is_wp_error( $response ) ) {
	        
	        $error_message = $response->get_error_message();
	        $status =  "Something went wrong: $error_message";
	        
	    } else {
	        
	        $status =  json_decode($response['body'], true);
	        
	    }
	     
	    return $status;
    }
    
    
    function trac_set_shipping_cost($cost, $instance_id, $zone_id){


        $data = array(
                    "settings" => array(
                           'cost' => $cost
                    )
                );
                

        $data = json_encode($data);
        
        
        $site_url = site_url();
    	$url = $site_url.'/wp-json/wc/v3/shipping/zones/'.$zone_id.'/methods/'.$instance_id;
    	
    	//return $url;
    	
	    $response = wp_remote_post( $url, array(
	        'method'      => 'PUT',
	        'timeout'     => 45,
	        'redirection' => 5,
	        'httpversion' => '1.0',
	        'blocking'    => true,
	        'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( 'ck_96112d7cafe7607997df7ecd006907283b7009cc' . ':' . 'cs_a1c17922367ce8d59192ada76f51c151c2af18c7' ),
                'Content-Type'   => 'application/json',
            ),
	        'data_format' => 'body',
	        'body'        => $data,
	        'cookies'     => array(),
	        )
	    );
	    
	    if ( is_wp_error( $response ) ) {
	        
	        $error_message = $response->get_error_message();
	        $status =  "Something went wrong: $error_message";
	        
	    } else {
	        
	        $status =  json_decode($response['body'], true);
	        //$status  = $status['instance_id'];
	        
	    }
	    
	    return $status;
	    
    }
    
    function trac_set_shipping_method($zone_id){
        $data = array(
                        "method_id" => 'flat_rate'
                );
                
        $data = json_encode($data);
        $site_url = site_url();
    	$url = $site_url.'/wp-json/wc/v3/shipping/zones/'.$zone_id.'/methods';
    	
    	//return $url;
    	
	    $response = wp_remote_post( $url, array(
	        'method'      => 'POST',
	        'timeout'     => 45,
	        'redirection' => 5,
	        'httpversion' => '1.0',
	        'blocking'    => true,
	        'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( 'ck_96112d7cafe7607997df7ecd006907283b7009cc' . ':' . 'cs_a1c17922367ce8d59192ada76f51c151c2af18c7' ),
                'Content-Type'   => 'application/json',
            ),
	        'data_format' => 'body',
	        'body'        => $data,
	        'cookies'     => array(),
	        )
	    );
	    
	    if ( is_wp_error( $response ) ) {
	        
	        $error_message = $response->get_error_message();
	        $status =  "Something went wrong: $error_message";
	        
	    } else {
	        
	        $status =  json_decode($response['body'], true);
	        $status  = $status['instance_id'];
	        
	    }
	    
	    return $status;
        
    }
    
    
    function traction_get_shipping_method_instance_id($zone_id){
       
        //$data = json_encode($data);
        
        
        // /wp-json/wc/v3/shipping/zones/<id>/methods
        $site_url = site_url();
    	$url = $site_url.'/wp-json/wc/v3/shipping/zones/'.$zone_id.'/methods';
    	
	    $response = wp_remote_post( $url, array(
	        'method'      => 'GET',
	        'timeout'     => 45,
	        'redirection' => 5,
	        'httpversion' => '1.0',
	        'blocking'    => true,
	        'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( 'ck_96112d7cafe7607997df7ecd006907283b7009cc' . ':' . 'cs_a1c17922367ce8d59192ada76f51c151c2af18c7' ),
                'Content-Type'   => 'application/json',
            ),
	        'data_format' => 'body',
	       // 'body'        => $data,
	        'cookies'     => array(),
	        )
	    );
	    
	    
	    
	    if ( is_wp_error( $response ) ) {
	        
	        $error_message = $response->get_error_message();
	        $status =  "Something went wrong: $error_message";
	        
	    } else {
	        
	        $status =  json_decode($response['body'],true)[0]['instance_id'];
	        
	    }
	    
	    return $status;
        
    }
    
    
    function trac_decode_state_codes($state){
    	$arr = array(
    		"NG-FC" =>	"Abuja",
    		"NG-AB" =>	"Abia",
    		"NG-AD" =>	"Adamawa",
    		"NG-AK" =>	"Akwa Ibom",
    		"NG-AN" =>	"Anambra",
    		"NG-BA" =>	"Bauchi",
    		"NG-BY" =>	"Bayelsa",
    		"NG-BE" =>	"Benue",
    		"NG-BO" =>	"Borno",
    		"NG-CR" =>	"Cross River",
    		"NG-DE" =>	"Delta",
    		"NG-EB" =>	"Ebonyi",
    		"NG-ED" =>	"Edo",
    		"NG-EK" =>	"Ekiti",
    		"NG-EN" =>	"Enugu",
    		"NG-GO" =>	"Gombe",
    		"NG-IM" =>	"Imo",
    		"NG-JI" =>	"Jigawa",
    		"NG-KD" =>	"Kaduna",
    		"NG-KN" =>	"Kano",
    		"NG-KT" =>	"Katsina",
    		"NG-KE" =>	"Kebbi",
    		"NG-KO" =>	"Kogi",
    		"NG-KW" =>	"Kwara"	,
    		"NG-LA" =>	"Lagos",	
    		"NG-NA" =>	"Nasarawa",	
    		"NG-NI" =>	"Niger",	
    		"NG-OG" =>	"Ogun",	
    		"NG-ON" =>	"Ondo",	
    		"NG-OS" =>	"Osun",	
    		"NG-OY" =>	"Oyo",	
    		"NG-PL" =>	"Plateau",	
    		"NG-RI" =>	"Rivers",	
    		"NG-SO" =>	"Sokoto",	
    		"NG-TA" =>	"Taraba",	
    		"NG-YO" =>	"Yobe",	
    		"NG-ZA" =>	"Zamfara",
    	);
    	$key = array_search($state, $arr);
    	$key = str_replace('-', ':', $key);
    	return $key;
    }
    
    
    
    function tractionpay_btps_get_details($request){
        $params = $request->get_params();
        
        $token = sanitize_text_field($params['token']);
	    
     	if(tractionapps_sirl_check_token($token) == true){
     	    
    	     $settings = get_option('woocommerce_tractionpay_banktransfer_settings');
    	     if(!empty($settings) ){
    	     	 $data = array(
    	     	 	'status' => 'success',
    	     	 	'response' => $settings,
    	     	 	//'state' => $settings
    	     	 );
    	     	 return $data;
     	    }else{
     	        $data = array(
    	     	 	'status' => 'error',
    	     	 	'remarks' => 'No record was found' 
    	     	 );
    	     	 return $data;
     	    }
	    }else{
	       $data = array(
	                'status' => 'error',
	                'response' => 'Token is wrong'
	            );
	       return $data;
	    }
	    return false;
    }
    
    function trac_get_shipping_zones($request){
        $params = $request->get_params();
        
        $token = sanitize_text_field($params['token']);
	    
     	if(tractionapps_sirl_check_token($token) == true){
     	    
    	     $shipping_zones = trac_get_all_shipping_zones();
    	     if(!empty($shipping_zones) ){
    	     	 $data = array(
    	     	 	'status' => 'success',
    	     	 	'response' => $shipping_zones,
    	     	 	
    	     	 );
    	     	 return $data;
     	    }else{
     	        $data = array(
    	     	 	'status' => 'error',
    	     	 	'remarks' => 'No record was found' 
    	     	 );
    	     	 return $data;
     	    }
	    }else{
	       $data = array(
	                'status' => 'error',
	                'response' => 'Token is wrong'
	            );
	       return $data;
	    }
	    return false;
    }
    
    function trac_get_all_shipping_zones(){
        $site_url = site_url();
    	$url = $site_url.'/wp-json/wc/v3/shipping/zones';
    	
	    $response = wp_remote_post( $url, array(
	        'method'      => 'GET',
	        'timeout'     => 45,
	        'redirection' => 5,
	        'httpversion' => '1.0',
	        'blocking'    => true,
	        'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( 'ck_96112d7cafe7607997df7ecd006907283b7009cc' . ':' . 'cs_a1c17922367ce8d59192ada76f51c151c2af18c7' ),
                'Content-Type'   => 'application/json',
            ),
	        'data_format' => 'body',
	       // 'body'        => $data,
	        'cookies'     => array(),
	        )
	    );
	    
	    
	    
	    if ( is_wp_error( $response ) ) {
	        
	        $error_message = $response->get_error_message();
	        $status =  "Something went wrong: $error_message";
	        
	    } else {
	        
	        $status =  json_decode($response['body'],true);
	        
	    }
	    
	    return $status;
    }
    
    function trac_bank_tranfer_webhook($request){
        $params = $request->get_params();
        $order_id = sanitize_text_field($params['order_id']);
        $token = 'fee93cfd';
        $key = $order_id.'|'.$token;
        $encrypted = hash('sha512', $key);
        $flag = update_post_meta($order_id, 'trac_bank_payment_received', 1);
        
        if($flag == true){
            $res = array(
              'status'  => 'success',
              'response' => 'Order updated successfully'
            );
            return $res;
        }else{
            $res = array(
              'status'  => 'error',
              'response' => 'Could not update order or order already updated.'
            );
            return $res;
        }
        
    }
    
    
    function trac_paystack_webhook($request){
        $params = $request->get_params();
        $order_id = sanitize_text_field($params['order_id']);
        
	    $order = wc_get_order( $order_id );
	    
        // Mark as processing (payment received)
        $order->update_status( 'processing', __( 'Payment received', 'WC_TractionPayPaystack_Gateway' ) );
                        
        // Reduce stock levels
        $order->reduce_order_stock();
        
        if($order_id != ''){
            $res = array(
              'status'  => 'success',
              'response' => 'Order updated successfully'
            );
            return $res;
        }else{
            $res = array(
              'status'  => 'error',
              'response' => 'Order ID is required'
            );
            return $res;
        }
        
    }