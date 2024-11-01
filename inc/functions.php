<?php



	function tractionapps_add_required_items(){
    	
  		//wp_register_style('bootstrap_css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css', '', '');
		// wp_enqueue_style ( 'bootstrap_css' );
		// wp_enqueue_script( 'popperjs', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js', array('jquery'), '', true );
		// wp_enqueue_script( 'bootstrap_js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js', array('jquery'), '', true );

    	wp_enqueue_script( 'trac_con_js', plugins_url('../assets/app.js', __FILE__ ), array('jquery'), '', true );
		wp_register_style('trac_con_css', plugins_url( '../assets/app.css', __FILE__ ), '','');
		wp_enqueue_style ( 'trac_con_css' );
		
    }
    //add_action('wp_enqueue_scripts', 'add_required_items');
    add_action('admin_enqueue_scripts', 'tractionapps_add_required_items');


	
	function tractionapps_authenticate_user_account(){
		$email = sanitize_email($_POST['trac_username']);
		$password = sanitize_text_field($_POST['trac_password']);
	    $trac_store_id = sanitize_text_field($_POST['trac_store_id']);
	    
	    if( $email != '' || $password != ''){
	    
    	    $store_url = get_site_url();
    	    $protocols = array('http://', 'http://www','www.', 'https://', 'https://www');
    	    $store_url = str_replace($protocols, '', $store_url);
    	    $store_url = $store_url."/";
    		
    		$hash = 'DAWGzZa7934A!t20183BG3|'.$store_url.'|'.$password;
    		
        	$transactionHash = hash('sha512', $hash);
            
    		$postRequest = array(
        		"store_url" =>  $store_url,
        		"email" =>  $email,
        		"password" =>  $password,
        		"token" => $transactionHash,
    		);
    
    		// print_r($_POST);
    	    
    
    		$url = "https://tractionapp-stage.herokuapp.com/verify/user";
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
    	    
    	    $response = wp_remote_retrieve_body($response);
    
    	    //print_r($response);
    
    	    $response = json_decode($response,true);
    	   // $data = json_encode($response->data);
    	    if( $response['status'] == true  ){
                $data = $response['data'];
        	    tract_insert_live_token($data['token'], $data['expirationDate']);
        	    if($response['status'] == true){
        	        update_option('trac_conn_settings', 2);
                    
                    update_option('trac_username', $email);
                    update_option('trac_password', $password);
                    update_option('business_id', $data['business_id']);
                    update_option('business_list', $data['list_of_stores']);
                    echo json_encode($data['list_of_stores']);
                    
        	    	//$url = tract_woocommerce_retrieve_token();
        	    	//echo $url;
        	    }else{
        	        //print_r($response);
        	    	echo json_encode(1);
        	    }
    	    }else{
    	        $message = array(2, ucfirst($response['message']) );
    	        echo json_encode($message);
    	    }
    	    
	    }else{
	        $message = array(2);
	        echo json_encode($message);
	    }

	    
	    die();
        
    }
    add_action( 'wp_ajax_tractionapps_authenticate_user_account', 'tractionapps_authenticate_user_account', 10, 3 );
    
    
    function tractionapps_activate_user_account(){
        $url = tractionapps_woocommerce_retrieve_token();
        $trac_store_id = sanitize_text_field($_POST['trac_store_id']);
        update_option('main_store', $trac_store_id);
        echo $url;
        die();
    }
    add_action('wp_ajax_tractionapps_activate_user_account', 'tractionapps_activate_user_account');
    
    
    function tractionapps_woocommerce_retrieve_token(){
        $store_url = get_site_url();
		$endpoint = '/wc-auth/v1/authorize';
		$params = [
		    'app_name' => 'Traction App',
		    'scope' => 'read_write',
		    'user_id' => get_current_user_id(),
		    'return_url' => $store_url.'/'.'wp-admin/options-general.php?page=traction-connect',
		    'callback_url' => $store_url.'/wp-json/traction-connect/v1/PushAPIKeys'
		];
		$query_string = http_build_query( $params );

		$url = array($store_url . $endpoint . '?' . $query_string);
		$url =  json_encode($url);
		return $url;
    }
    
    
    function tractionapps_deauthenticate_user_account(){
        $email = sanitize_text_field($_POST['trac_username']);
		$password = sanitize_text_field($_POST['trac_password']);
	    //$store_url = sanitize_text_field($_POST['trac_store_url']);
	    $store_url = get_site_url();
	    $protocols = array('http://', 'http://www','www.', 'https://', 'https://www');
	    $store_url = str_replace($protocols, '', $store_url);
	    $store_url = $store_url."/";
		
		$hash = 'DAWGzZa7934A!t20183BG3|'.$store_url.'|'.$password;
		
    	$transactionHash = hash('sha512', $hash);
        
		$postRequest = array(
    		"store_url" =>  $store_url,
    		"email" =>  $email,
    		"password" =>  $password,
    		"token" => $transactionHash,
		);

		// print_r($_POST);
	    

		$url = "https://tractionapp-stage.herokuapp.com/verify/user";
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
	    
	    $response = wp_remote_retrieve_body($response);
	    $response = json_decode($response,true);
	    $data = $response['data'];
	    if($response['status'] == true){
            update_option('trac_conn_settings', 0);
            update_option('trac_username', '');
            update_option('trac_password', '');
            update_option('business_id', '');
            update_option('business_list', '');
            update_option('main_store', '');
            echo json_encode(1);
	    }
        die();
    }
    add_action('wp_ajax_tractionapps_deauthenticate_user_account', 'tractionapps_deauthenticate_user_account');
    add_action('wp_ajax_nopriv_tractionapps_deauthenticate_user_account', 'tractionapps_deauthenticate_user_account');


    function tractionapps_get_live_token(){
    	global $wpdb;
    	$prefix = $wpdb->prefix."private_tokens";
    	$postman = $wpdb->get_var("SELECT token FROM $prefix ORDER BY ID DESC");
    	return $postman;
    }

    function tract_insert_live_token($token, $expiry_date){
    	global $wpdb;
    	$prefix = $wpdb->prefix."private_tokens";
    	$postman = $wpdb->query($wpdb->prepare("INSERT INTO $prefix(`token`, `expiry_date`) VALUES (%s, %s)",$token, $expiry_date) );
    	
    	//$wpdb->query($wpdb->prepare("INSERT INTO $wpdb->postmeta( post_id, meta_key, meta_value )VALUES ( %d, %s, %s )",10,$metakey,$metavalue));
    	return $wpdb->insert_id;
    }

    function tractionapps_authorize_woocommerce_app($consumer_key, $consumer_secret){
    	$store_url = get_site_url();
    	$token = tractionapps_get_live_token();

		$store_url = get_site_url();
	    $protocols = array('http://', 'http://www','www.', 'https://', 'https://www');
	    $store_url = str_replace($protocols, '', $store_url);
	    $store_url = $store_url."/";
		
		//$hash = 'DAWGzZa7934A!t20183BG3|'.$store_url.'|'.$password;
		
    	$transactionHash = hash('sha512', $hash);
        
		$postRequest = array(
    		"store_url" =>  $store_url,
    		"consumer_key" =>  $consumer_key,
    		"consumer_secret" =>  $consumer_secret,
    		"token" => $token,
		);

		$url = "https://tractionapp-stage.herokuapp.com/store/sync";
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
	    
	    $response = wp_remote_retrieve_body($response);
	    $data = json_decode($response, true);
	    update_option('trac_conn_settings', 1);
	    $data = implode(" ",$data);
	    return $data;
	    
	    
    }
    

    function tractionapps_insert_keys($c_key, $c_secret){
    	global $wpdb;
    	$prefix = $wpdb->prefix.'private';
    	$date = date('Y-m-d H:i:s');
    	$postman = $wpdb->query($wpdb->prepare("INSERT INTO $prefix(`c_key`, `c_secret`, `date`) VALUES (%s, %s, %s)",$c_key, $c_secret, $date) );
    	//$wpdb->query($wpdb->prepare("INSERT INTO $wpdb->postmeta( post_id, meta_key, meta_value )VALUES ( %d, %s, %s )",10,$metakey,$metavalue));
    	return $wpdb->insert_id;
    }