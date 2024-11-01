    function countdown( elementName, minutes, seconds ){
	    var element, endTime, hours, mins, msLeft, time;

	    function twoDigits( n )
	    {
	        return (n <= 9 ? "0" + n : n);
	    }

	    function updateTimer()
	    {
	        msLeft = endTime - (+new Date);
	        if ( msLeft < 1000 ) {
	            element.innerHTML = "";
	            document.getElementById("monify_form").innerHTML = 'Seems there is a delay in confirming your payment. <br>We will keep trying to confirm it and will apply it to your order once received.';
	        } else {
	            time = new Date( msLeft );
	            hours = time.getUTCHours();
	            mins = time.getUTCMinutes();
	            element.innerHTML = (hours ? hours + ':' + twoDigits( mins ) : mins) + ':' + twoDigits( time.getUTCSeconds() );
	            setTimeout( updateTimer, time.getUTCMilliseconds() + 500 );
	        }
	    }

	    element = document.getElementById( elementName );
	    endTime = (+new Date) + 1000 * (60*minutes + seconds) + 500;
	    updateTimer();
	}
	


jQuery( function( $ ) {

   
    $("#monify-payment-button").click(function(e){
        e.preventDefault();

        const price = $(this).data('price');
        const storeUrl = $(this).data('storeurl');
        const orderId = $(this).data('orderid');
        const hash = $(this).data('hash');
        const currency = $(this).data('currency');
        
        $(".loader").html('<div style="color: teal;background-color:#f8f8f8;padding: 10px;margin-top:10px;margin-bottom:10px">Retrieving payment details...please wait</div>');
        $.ajax({
                url: ajax_url,
                type: 'post',
                data : {
                    action: 'traction_monnify_payment_action',
                    price: price,
                    storeUrl: storeUrl,
                    orderId: orderId,
                    hash: hash,
                    currency: currency
                
                },
            
            success:function(response){
                let res = JSON.parse(response);
                $(".spinner").hide();
                if(res['data']){
                    $(".loader").html('');
                    $("#monify-payment-button").remove();
                    $(".cancel").remove();
                  $("#monify_form").append('<div style="padding: 10px; border: dotted 2px green">Please transfer to the account below within the next 10 minutes (account number will expire after 10 minutes).<br> Once you have made payment, please click on the button below.<br> <strong style="color:red">Please do not close this page till the payment is confirmed</strong><br><br><strong>Bank Name:</strong> '+res['data']['bankName']+'<br><strong>Account Number: </strong>'+res['data']['accountNumber']+'<br><strong>Account Name: </strong>'+res['data']['accountName']+'<br><button style="margin-top: 1rem;" class="button alt madePaymentButton" data-order_id="'+res['data']['orderId']+'">I have made payment</button></div>');
                }else{
                    $(".loader").html('<div style="color: red;padding:10px; background-color:#f8f8f8;margin-top:10px;margin-bottom:10px"><i class="bi bi-exclamation-triangle"></i> Sorry, we couldn\'t retrieve payment details. Please try again later.</div>');
                    //window.location.reload();
                    $("#passwordErrorField").html("Password is wrong");
                }
            },
            
            error:function(response){
                
                $(".loader").html('<span style="color: red;padding:10px;"><i class="bi bi-exclamation-triangle"></i> Please check your network connection.');
            }
            
        });

    });
    
    $("#traction-paystack-payment-button").click(function(e){
        e.preventDefault();

        const price = $(this).data('price');
        const storeUrl = $(this).data('storeurl');
        const orderId = $(this).data('orderid');
        const hash = $(this).data('hash');
        const currency = $(this).data('currency');
        
        $(".loader").html('<div style="color: teal;background-color:#f8f8f8;padding: 10px;margin-top:10px;margin-bottom:10px">Redirecting you to payment page...please wait</div>');
        $.ajax({
                url: ajax_url,
                type: 'post',
                data : {
                    action: 'traction_paystack_payment_action',
                    price: price,
                    storeUrl: storeUrl,
                    orderId: orderId,
                    hash: hash,
                    currency: currency
                
                },
            
            success:function(response){
                let res = JSON.parse(response);
                $(".spinner").hide();
                if(res.length > 0){
                    $(".loader").html('');
                    $("#traction-paystack-payment-button").remove();
                    $(".cancel").remove();
                    setTimeout(function(){
                        //window.open(res, "_blank") || window.location.replace(res);
                        window.location.href=res;
                    }, 1000);
                    //$(".loader").html('<div style="color: teal;background-color:#f8f8f8;padding: 10px;margin-top:10px;margin-bottom:10px">Please wait while we confirm if payment was successful.</div>');
                    
                    var seconds = 0;
                    $('.loader-spin').show();
                    $("#monify_form").append("<br><span style='color: teal'>Checking payment status, please wait.</span>");
                    countdown( "timeRemaining", 15, 0 );
                    const myVar = setInterval(function(){

                        $.ajax({
                            url: ajax_url,
                            type: 'post',
                            data : {
                                action: 'traction_monnify_update_order',
                                order_id: orderId,
                            
                            },
                        
                            success:function(response){
                                let res = JSON.parse(response);
                                $(".loader").hide();
                                if(res == 1 ){
                                    
                                    $("#monify_form").append("<br><span style='color: green'>Payment verified successfully, Merchant will contact you soon.</span>");
                                    setTimeout(function(){
                                        const url = 'http://'+window.location.host;
                                        window.location.href=url;
                                    }, 3000);
                                    
                                    clearInterval(myVar);
                                    
                                }else{
                                    $("#monify_form").append("<br><span style='color: orange'>Sorry, we couldn't verify your payment, trying again.</span>");
                                }
                                
                            }
                        });
                    }, 40000);
                    
                }else{
                    $(".loader").html('<div style="color: red;padding:10px; background-color:#f8f8f8;margin-top:10px;margin-bottom:10px"><i class="bi bi-exclamation-triangle"></i> Sorry, we couldn\'t retrieve payment details. Please try again later.</div>');
                    //window.location.reload();
                    $("#passwordErrorField").html("Password is wrong");
                }
            },
            
            error:function(response){
                
                $(".loader").html('<span style="color: red;padding:10px;">Network failed: Please check your network connection.');
            }
            
        });
    });
    
    $('.loader-spin').hide();
    
    $("#monify_form").on("click", ".madePaymentButton", function(){
        $(".madePaymentButton").hide();
        const order_id = $(this).data('order_id');
        var seconds = 0;
        $('.loader-spin').show();
        $("#monify_form").append("<br><span style='color: teal'>Checking payment status, please wait <span id='timeRemaining'></span></span>");
        const myVar = setInterval(function(){
            
            
            $.ajax({
                url: ajax_url,
                type: 'post',
                data : {
                    action: 'traction_monnify_update_order',
                    order_id: order_id,
                
                },
            
                success:function(response){
                    let res = JSON.parse(response);
                    $(".loader").hide();
                    if(res == 1 ){
                        
                        $("#monify_form").append("<br><span style='color: green'>Payment verified successfully, Merchant will contact you soon.</span>");
                        setTimeout(function(){
                            const url = 'http://'+window.location.host;
                            window.location.href=url;
                        }, 3000);
                        
                        clearInterval(myVar);
                        
                    }else{
                        $("#monify_form").append("<br><span style='color: orange'>Sorry, we couldn't verify your payment, trying again.</span>");
                    }
                    
                }
            });
        }, 40000);
        
    });
    
    $("#monify_form").on("click", ".madePaymentButton", function(){
        countdown( "timeRemaining", 15, 0 );
        
    });
    
    
    


} );