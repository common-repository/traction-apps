<?php


/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter( 'woocommerce_payment_gateways', 'tractionpay_add_bank_transfer_gateway_class' );
function tractionpay_add_bank_transfer_gateway_class( $gateways ) {
	$gateways[] = 'WC_TractionPayBankTransfer_Gateway'; // your class name is here
	return $gateways;
}
 
/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'tractionpaybanktransfer_init_gateway_class' );
function tractionpaybanktransfer_init_gateway_class() {
 
    	class WC_TractionPayBankTransfer_Gateway extends WC_Payment_Gateway {
     
     		/**
     		 * Class constructor, more about it in Step 3
     		 */
     		public function __construct() {
     
    			$this->id = 'tractionpay_banktransfer'; // payment gateway plugin ID
    			$this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
    			$this->has_fields = true; // in case you need a custom credit card form
    			$this->method_title = 'TractionPay Bank Transfer Gateway';
    			$this->method_description = 'This payment gateway works exactly like the direct bank transfer payment method'; // will be displayed on the options page
    		 
    			// gateways can support subscriptions, refunds, saved payment methods,
    			// but in this tutorial we begin with simple payments
    			$this->supports = array(
    				'products'
    			);
    		 
    			// Method with all the options fields
    			$this->init_form_fields();
    		 
    			// Load the settings.
    			$this->init_settings();
    			$this->title = $this->get_option( 'title' );
    			$this->description = $this->get_option( 'description' );
    			$this->enabled = $this->get_option( 'enabled' );
                $this->payment_description = $this->get_option( 'payment_description' );
    			// $this->testmode = 'yes' === $this->get_option( 'testmode' );
    			// $this->private_key = $this->testmode ? $this->get_option( 'test_private_key' ) : $this->get_option( 'private_key' );
    			// $this->publishable_key = $this->testmode ? $this->get_option( 'test_publishable_key' ) : $this->get_option( 'publishable_key' );
    		 
    			// This action hook saves the settings
    			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    		 
    			// We need custom JavaScript to obtain a token
    			add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
    			
    			add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
    		 
    			// You can also register a webhook here
    			// add_action( 'woocommerce_api_{webhook name}', array( $this, 'webhook' ) );
     
     		}
     
    		/**
     		 * Plugin options, we deal with it in Step 3 too
     		 */
     		public function init_form_fields(){
     
    			$this->form_fields = array(
    				'enabled' => array(
    					'title'       => 'Enable/Disable',
    					'label'       => 'Enable Traction Bank Transfer Gateway',
    					'type'        => 'checkbox',
    					'description' => '',
    					'default'     => 'no'
    				),
    				'title' => array(
    					'title'       => 'Title',
    					'type'        => 'text',
    					'description' => 'This controls the title which the user sees during checkout.',
    					'default'     => 'Direct Bank Transfer',
    					'desc_tip'    => true,
    				),
    				'description' => array(
    					'title'       => 'Description',
    					'type'        => 'textarea',
    					'description' => 'This controls the description which the user sees during checkout.',
    					'default'     => 'Transfer cash from your bank to this merchant',
    				),
                    'payment_description' => array(
                        'title'       => 'Payment Description',
                        'type'        => 'textarea',
                        'description' => 'This controls the payment description which the user sees during checkout and it is also sent via email.',
                        'default'     => 'Bank Tranfer details',
                    ),
    			);
     
    	 	}
    	 	
    	 	/**
    		 * Display monify payment icon.
    		 */
    		public function get_icon() {
    
    			$icon = '<img src="' . plugins_url( 'monnify (1).png', __FILE__ ). '" alt="cards" />';
    
    			return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
    
    		}
     
    		/**
    		 * You will need it if you want your custom credit card form, Step 4 is about it
    		 */
    		public function payment_fields() {
                $payment_details = explode('~~', $this->payment_description);
                //foreach( $payment_details as $d){
                    //Format: GTBank~~Tobi Lekan Adeosun~~0102003344
                    //        Bank~~Acct_name~~Acct_num
                    echo '<p><strong>Bank Name: </strong>'.$payment_details[0].'</p>';
                    echo '<p><strong>Account Name: </strong>'.$payment_details[1].'</p>';
                    echo '<p><strong>Account No: </strong>'.$payment_details[2].'</p>';
    		
     
    		}
     
    		/*
    		 * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
    		 */
    	 	public function payment_scripts() {
     
    		
     
    	 	}
     
    		/*
     		 * Fields validation, more in Step 5
    		 */
    		public function validate_fields() {
     
    		
     
    		}
     
    		/*
    		 * We're processing the payments here, everything about it is in Step 5
    		 */
    		public function process_payment( $order_id ) {
     
    		    $order = wc_get_order( $order_id );
            
                
                        
                // Reduce stock levels
                $order->reduce_order_stock();
                        
                // Remove cart
                WC()->cart->empty_cart();
                        
                // Return thankyou redirect
                // return array(
                //     'result'    => 'success',
                //     'redirect'  => $this->get_return_url( $order )
                // );
                
                return array(
				    'result'   => 'success',
				    'redirect' => $order->get_checkout_payment_url( true ),
			    );
     
    	 	}
     
    		/*
    		 * In case you need a webhook, like PayPal IPN etc
    		 */
    		public function webhook() {
     
    		
     
    	 	}
    	 	
    	 	public function receipt_page( $order_id ) {
    
    		    $order = wc_get_order( $order_id );
    			echo '<p>' . __( 'Thank you for your order, please make payment to the account details below.', 'woo-' ) . '</p>';
    			
    
    		    // $order_total = $order->get_total();
    		    // $paidOn = date('Y-m-d H:i:s');
    		    // $store_url = get_site_url();
    		    // $protocols = array('http://', 'http://www','www.', 'https://', 'https://www');
    		    // $store_url = str_replace($protocols, '', $store_url);
    		    
    		    // $hash = '';
    		    
    		    
    		    $payment_currency   = method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->get_order_currency();
        
                $payment_details = explode('~~', $this->payment_description);
                //foreach( $payment_details as $d){
                    //Format: GTBank~~Tobi Lekan Adeosun~~0102003344
                    //        Bank~~Acct_name~~Acct_num
                    echo '<p><strong>Bank Name: </strong>'.$payment_details[0].'</p>';
                    echo '<p><strong>Account Name: </strong>'.$payment_details[1].'</p>';
                    echo '<p><strong>Account No: </strong>'.$payment_details[2].'</p>';
                //}
    			//echo '<div id="monify_form"><div class="loader"></div><button class="button alt" data-price="'.$order_total.'" data-storeUrl="'.$store_url.'" data-orderId="'.$order_id.'" data-hash="'.$transactionHash.'" data-currency="'.$payment_currency.'"  id="-payment-button">' . __( 'Pay Now', 'woo-' ) . '</button> <a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . __( 'Cancel order &amp; restore cart', 'woo-' ) . '</a></div>';
                
                if ( $order->get_total() > 0 ) {
			    // Mark as on-hold (we're awaiting the payment).
			        $order->update_status( apply_filters( 'woocommerce_bacs_process_payment_order_status', 'on-hold', $order ), __( 'Awaiting BACS payment', 'woocommerce' ) );
        		} else {
        			$order->payment_complete();
        		}
    	    
    
    	}
    	
    	/**
         * Output for the order received page.
         */
        public function thankyou_page() {
            if ( $this->instructions ) {
                echo wpautop( wptexturize( $this->instructions ) );
            }
        }
        
         
        
    	
    	
 	}
}