jQuery( document ).ready( function( $ ) {
    
    $("#tr_saveSettings").click(function(e){
        e.preventDefault();
        const res = confirm('Are you sure you want to save this settings?');
        const bank_transfer = $('input[name="bank_transfer"]:checked').val();
        const card_payments = $('input[name="card_payments"]:checked').val();
        
        if(res == true){
            $(".statusBox").html('Saving Settings, please wait.');
             $.ajax({
    		    		url: ajaxurl,
    		    		type: 'post',
    		    		data : {
    		    			action: 'tractionapps_save_settings',
    		    			bank_transfer,
                            card_payments
    		    		},
    		    	
    		        success:function(response){
    		        	let res = JSON.parse(response);
    		        	$(".loader-spin").removeClass('loader');
    		        	if( res != 0 ){
    		        		$(".statusBox").html('Settings Saved');
    		        		//window.location.reload();
    		        	}else{
    		        	    $(".statusBox").html('<span style="color:red">Failed to save settings.</span>');
    		        	}
    		        
    		        }
    			});
        }
    });
    
    $("#deauthorizeApp").click(function(e){
        e.preventDefault();
        const trac_username = $("#trac_username").val();
		const trac_password = $("#trac_password").val();
		if(trac_username != '' && trac_password != ''){
            $(".statusBox").html('Removing authorization...');
            $.ajax({
    		    		url: ajaxurl,
    		    		type: 'post',
    		    		data : {
    		    			action: 'tractionapps_deauthenticate_user_account',
    		    			trac_username: trac_username,
                            trac_password:trac_password
    		    		},
    		    	
    		        success:function(response){
    		        	let res = JSON.parse(response);
    		        	$(".loader-spin").removeClass('loader');
    		        	if( res != 0 ){
    		        		$(".statusBox").html('Authorization removed');
    		        		window.location.reload();
    		        	}else{
    		        	    $(".statusBox").html('<span style="color:red">Failed to verify User.</span>');
    		        	}
    		        
    		        }
    			});
		}else{
		    
		}
    });
    
    $(".store_box").hide();

    $("#authorizeApp").click(function(e){
        
        e.preventDefault();
        const trac_username = $("#trac_username").val();
		const trac_password = $("#trac_password").val();
		const trac_store_id = $("#trac_store_id").val();
		const btn = $("#authorizeApp");
		
		if(trac_username != '' && trac_password != '' ){
			$(".loader-spin").addClass('loader');
	        $.ajax({
		    		url: ajaxurl,
		    		type: 'post',
		    		data : {
		    			action: 'tractionapps_authenticate_user_account',
		    			trac_username: trac_username,
						trac_password: trac_password,
						trac_store_id: trac_store_id
						
		    		},
		    	
		        success:function(response){
		        	let res = JSON.parse(response);
		        	$(".loader-spin").removeClass('loader');
		        	if( res[0] != 2 ){
		        		$(".statusBox").html('User verified...fetching stores<br>');
		        		var text = '<option value="">Select an option</option>';
		        		$(".store_box").show();
		        		res.forEach(function(el){
		        		    text += '<option value="'+el['store_id']+'">'+el['store_name']+'</option>';
		        		});
		        		
		        		$("#trac_store_id").html(text);
		        		
		        		$(".statusBox").html('Store list updated. Please select store.');
		        		
		        		$('#authorizeApp').attr('id', 'activateApp').text('Activate App').removeClass('btn-primary').addClass('btn-secondary');
		        		//$('#authorizeApp').
		        		//$(btn).attr("id","newId");
		        		
		        		//window.location.href=res;
		        		//window.location.reload();
		        	}else if(res[0] == 2){
		        	    $(".statusBox").html('<span style="color:red">'+res[1]+'</span>');
		        	}else{
		        	    $(".statusBox").html('<span style="color:red">User verification failed. Please check username and password.</span>');
		        	}
		        
		        },
		        error:function(response){
		            $(".loader-spin").removeClass('loader');
		            $(".statusBox").html('<span style="color:red">Network Failed. No internet connection.</span>');
		        }
			});
		}else{
			$(".requiredTracUsername").html('This field is required.');
			$(".requiredTracPassword").html('This field is required.');
			$(".requiredTracStoreUrl").html('This field is required.');
		}
    });
    
    $("#btnRow").on("click", "#activateApp", function(e){
        
        e.preventDefault();
        const trac_username = $("#trac_username").val();
		const trac_password = $("#trac_password").val();
		const trac_store_id = $("#trac_store_id").val();
		const btn = $("#authorizeApp");
		
		if(trac_username != '' && trac_password != '' && trac_store_id != ''){
			$(".loader-spin").addClass('loader');
	        $.ajax({
		    		url: ajaxurl,
		    		type: 'post',
		    		data : {
		    			action: 'tractionapps_activate_user_account',
		    			trac_username: trac_username,
						trac_password: trac_password,
						trac_store_id: trac_store_id
						
		    		},
		    	
		        success:function(response){
		        	let res = JSON.parse(response);
		        	$(".loader-spin").removeClass('loader');
		        	if( res.length > 0 ){
		        		$(".statusBox").html('Connecting to Traction server.<br>');
		        		
		        		window.location.href=res;
		        		//window.location.reload();
		        	}else{
		        	    $(".statusBox").html('<span style="color:red">Failed to verify User.</span>');
		        	}
		        
		        }
			});
		}else{
			$(".requiredTracUsername").html('This field is required.');
			$(".requiredTracPassword").html('This field is required.');
			$(".requiredTracStoreUrl").html('This field is required.');
		}
    });



 });