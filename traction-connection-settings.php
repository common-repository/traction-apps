<?php
    function tractionapps_options_page(){
		?>
		<style>
		    .display-4{
		        font-size: 1.3rem;
		    }
		    .border-bottom{
		        border-bottom: solid 1px #d8d5d5;
		        padding-bottom: 15px;
		    }
		    .p-2{
		        padding: 1rem;
		    }
		    
		    .p-1{
		        padding: 0.6rem;
		    }
		    .py-2{
		        padding-top: 1rem;
		        padding-bottom: 1rem;
		    }
		</style>
	  <div>
	     
	  <div style="background:white;width:50%">
		 <h1 class="display-4 border-bottom p-2">Traction Connection Settings</h1> 
		  <p class="lead p-1">Connect Your Store with Traction App.</p>
		  <?php
		    if( get_option('trac_conn_settings') == 1 ){ 
	  	        echo '<p style="color:teal" class="p-1"><span class="dashicons dashicons-yes-alt"></span> Account successfully linked</p>';    
	  	    }
		  
		  ?>
		  <!-- <hr class="my-4"> -->
		
	  <form method="post" action="options.php" >
	  <?php settings_fields( 'trac_options_group' ); 

	  	$store_url = !empty(get_option('trac_store_url') )?get_option('trac_store_url'):get_site_url();
	  	
	  	$store_lists = get_option('business_list') ;
	  	
	  	$main_store = get_option('main_store');
	    
	  ?>
	  
	 
	  <table style="background-color: #fff;margin-left:10px;padding:20px;">
	 
	  <tr valign="top">
	  <th scope="row" width="30%"><label for="trac_username">Username</label></th>
	  <td width="60%"><input type="text" id="trac_username" name="trac_username" value="<?php echo get_option('trac_username'); ?>" /><br>
	  	<span class="requiredTracUsername"></td>
	  </tr>
	  <tr valign="top">
	  	<th scope="row" width="30%"><label for="trac_password">Password</label></th>
	  	<td width="60%"><input type="password" id="trac_password" name="trac_password" value="<?php echo get_option('trac_password'); ?>" /><br>
	  	<span class="requiredTracPassword"></td>
	  	</td>
	  </tr>
	  <?php
	
	  
	  if( get_option('trac_conn_settings') > 1 && !empty($store_lists) ){ ?>
	  <tr valign="top" class="">
	  	<th scope="row" width="30%"><label for="trac_password">Store ID</label></th>
	  	<td width="60%">
      	    <select id="trac_store_id" name="trac_store_id" value="<?php echo get_option('trac_store_id'); ?>">
      	        <option value="">Select Store</option>
      	        <?php foreach($store_lists as $list){ ?>
      	        <option value="<?php echo $list['store_id'] ?>"><?php echo $list['store_name'] ?></option>
      	        <?php } ?>
      	    </select>
	  	<br>
	  	<span class="requiredTracPassword"></td>
	  	</td>
	  </tr>
	  <?php }else if(get_option('trac_conn_settings') == 0 && get_option('main_store') == '' && empty($store_lists)){ ?>
	  <tr valign="top" class="store_box">
	  	<th scope="row" width="30%"><label for="trac_password">Store ID</label></th>
	  	<td width="60%">
      	    <select id="trac_store_id" name="trac_store_id" value="<?php echo get_option('trac_store_id'); ?>">
      	        <option value="">Select Store</option>
      	        <?php foreach($store_lists as $list){ 
      	            $selected = ($list['store_id'] == $main_store)?'selected':'';
      	        ?>
      	            <option value="<?php echo $list['store_id'] ?>" <?php echo $selected ?>><?php echo $list['store_name'] ?></option>
      	        <?php  } ?>
      	    </select>
	  	<br>
	  	<span class="requiredTracPassword"></td>
	  	</td>
	  </tr>
	  <?php }else if(get_option('trac_conn_settings') == 1 && get_option('main_store') != '' && !empty($store_lists)){ ?>
	  <tr valign="top">
	  	<th scope="row" width="30%"><label for="trac_password">Store ID</label></th>
	  	<td width="60%">
      	    <select id="trac_store_id" name="trac_store_id" value="<?php echo get_option('trac_store_id'); ?>">
      	        <option value="">Select Store</option>
      	        <?php foreach($store_lists as $list){ 
      	            $selected = ($list['store_id'] == $main_store)?'selected':'';
      	        ?>
      	            <option value="<?php echo $list['store_id'] ?>" <?php echo $selected ?>><?php echo $list['store_name'] ?></option>
      	        <?php  } ?>
      	    </select>
	  	<br>
	  	<span class="requiredTracPassword"></td>
	  	</td>
	  </tr>
	  <?php } 
	    
	            $traction_settings_bank_transfer = (get_option('tractionapps_settings_bank_transfer') == 1)?'checked':'';
                $traction_settings_card_payment = (get_option('tractionapps_settings_card_payment') == 1)?'checked':'';
	  ?>
	  <!--<tr valign="top" class="py-2">-->
	  <!--    <th scope="row" with="30%"><label for="payment_methods">Payment Method</label></th>-->
	  <!--    <th width="60%">-->
	  <!--        <input type="checkbox" name="bank_transfer" <?php echo $traction_settings_bank_transfer ?>><label for="bank_transfer">Bank Transfer</label>-->
	  <!--        <input type="checkbox" name="card_payments" <?php echo $traction_settings_card_payment ?>><label for="card_payments">Card Payment</label>-->
	  <!--    </th>-->
	  <!--</tr>-->
	  <tr valign="top">
	  <th scope="row" width="30%"></th>
	  <td width="70%" id="btnRow">
	      <div>
	      <?php 
	   //   echo get_option('trac_conn_settings');
	   //   echo count($store_lists);
	      
	      if( get_option('trac_conn_settings') > 1 && count($store_lists) > 0 ){ 
	      ?>
	        <button class="btn btn-primary" id="activateApp">Activate App</button><div class="loader-spin" role="status" aria-hidden="true"></div>
	        
	      <?php }else if( get_option('trac_con_settings') == 0 && empty($store_lists) ){ ?>
	        
	        <button class="btn btn-primary" id="authorizeApp">Authenticate User</button><div class="loader-spin" role="status" aria-hidden="true"></div>
	        
	      <?php }else{ ?>
	        <button class="btn btn-primary" id="deauthorizeApp">De-Authorize App</button><div class="loader-spin" role="status" aria-hidden="true"></div>
	      <?php } ?>
	      
	      <!--<button class="btn btn-primary" id="tr_saveSettings">Save Settings</button>-->
	      </div>
	  	<br>
	  	<span class="statusBox"></span>
	  </td>
	  </tr>
	  </table>
	  <?php  //submit_button(); ?>
	  </form>
	  </div>
	  </div>
	<?php
	}
	function trac_register_options_page() {
  		add_options_page('Traction Connection Settings', 'Traction Connection Settings', 'manage_options', 'traction-connect', 'tractionapps_options_page');
	}
	add_action('admin_menu', 'trac_register_options_page');

	add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'traction_add_plugin_page_settings_link');
	function traction_add_plugin_page_settings_link( $links ) {
		$links[] = '<a href="' .
			admin_url( 'options-general.php?page=traction-connect' ) .
			'">' . __('Settings') . '</a>';
		return $links;
	}