// models the account page
var accountModel = {
    
    type: ko.observable(''),
    
    status: ko.observable(''),
    renewalReadable: ko.observable(''),
    
    // plan
    planId: ko.observable(''),
    planName: ko.observable(''),
    amountReadable: ko.observable(''),
    customerLoading: ko.observable(true),
    
    // card
    hasCard: ko.observable(false),
    cardReadable: ko.observable(''),
    cardExpires: ko.observable(''),
    cardExpiredMonth: ko.observable(''),
    cardExpiredYear: ko.observable(''),
    
    // plans
    plans: ko.observableArray([]), // observables
	plansLoading: ko.observable(false),
	
	// payment
	showNewCard: ko.observable(false),
    
    init:function(){ // initializes the model
    
    	// set publishable key, #ref: https://stripe.com/docs/tutorials/forms
        var pubkey = $('body').attr('data-pubkey');
        Stripe.setPublishableKey(pubkey);	
    
        accountModel.updateCustomer();
        
		ko.applyBindings(accountModel);  // apply bindings
	},
    
    refresh:function(){
    
    	accountModel.type('');
	  
	   	// plan
	   	accountModel.status('');
	    accountModel.planId('');
	    accountModel.planName('');
	    accountModel.amountReadable('');
	    accountModel.customerLoading(true);
	    
	    // card
	    accountModel.hasCard(false);
	    accountModel.cardReadable('');
	    accountModel.cardExpires('');
	    
	    accountModel.updateCustomer();
	    
    },
    
    updateCustomer:function(id){
    
    	if(id==null)id='';
    
        $.ajax({
    		url: '../api/customer/get',
			type: 'POST',
			data: {id: id},
			dataType: 'json',
			success: function(data){
				accountModel.customerLoading(false);
				
				accountModel.type(data['type']);
				accountModel.renewalReadable(data['renewalReadable']);
				accountModel.status(data['status']);
                accountModel.planId(data['plan']);
                accountModel.planName(data['name']);
                accountModel.amountReadable(data['amountReadable']);
                accountModel.hasCard(data['hasCard']);
                accountModel.cardReadable(data['cardReadable']);
				accountModel.cardExpires(data['cardExpires']);
				accountModel.cardExpiredMonth(data['cardExpiredMonth']);
				accountModel.cardExpiredYear(data['cardExpiredYear']);
			}
		});
        
    },
    
    updatePlans:function(){  // updates the plans

		accountModel.plans.removeAll();
		accountModel.plansLoading(true);

		$.ajax({
			url: '../api/plan/list',
			type: 'GET',
			data: {},
			success: function(data){
			
				// #debug console.log(data);
				
				accountModel.plans(data);
				accountModel.plansLoading(false);

			}
		});

	},
    
    showUnsubscribe:function(o, e){
		$('#unsubscribeDialog').modal('show');   
    },
    
    unsubscribe:function(o, e){
    
    	message.showMessage('progress', 'Unsubscribing...');
    
    	$.ajax({
    		url: '../api/customer/plan/unsubscribe',
			type: 'POST',
			data: {},
			dataType: 'json',
			success: function(data){
				message.showMessage('success', 'You have successfully unsubscribed.');
				$('#unsubscribeDialog').modal('hide');
				accountModel.refresh();
			},
			error: function(xhr, errorText, thrownError){
				console.log(xhr.responseText);
				message.showMessage('error', xhr.responseText);
				$('#unsubscribeDialog').modal('hide');   
			}
		});
	    
    },
    
    showChangePlans:function(o, e){
    	
    	accountModel.updatePlans();
    
		$('#changePlanDialog').modal('show');   
    },
    
    changePlan:function(o, e){
	    var plan = 'not-selected';

        var selected = $("input[type='radio'][name='plan']:checked");
        if (selected.length > 0){
            plan = selected.val();
        }
        
        if(plan=='not-selected'){
            message.showMessage('error', 'Please select a plan');
            return;
        }
       
        message.showMessage('progress', 'Updating plan...');
        
        $.ajax({
            url: '../api/customer/plan/change',
            type: 'POST',
            data: {plan: plan},
            success: function(data){
            
                message.showMessage('success', 'Update successful');
				$('#changePlanDialog').modal('hide');
                
                // refresh the model
                accountModel.refresh();
            
            },
            error: function(data){
                message.showMessage('error', 'There was a problem changing the plan');
            }
        });

    },
    
    showUpdatePayment:function(o, e){
	    
	    // init
	    accountModel.showNewCard(false);
	    $('#update-cc').val('');
	    $('#update-cvc').val('');
	    $('#update-expiresMM').val('');
	    $('#update-expiresYY').val('');
	    
	    $('#updatePaymentDialog').modal('show'); 
	    
    },
    
    updatePayment:function(o, e){
    
    
    	if(accountModel.showNewCard()==true){ // handle new card
	    	
	    	var form = $('#newcard-form');
        
			message.showMessage('progress', 'Validating credit card...');
        
			Stripe.createToken(form, accountModel.stripeUpdateResponseHandler);

    	}
    	else{ // update the existing card
	    	
	    	var month = $('#changeMM').val();
			var year = $('#changeYY').val();
	        
	        message.showMessage('progress', 'Updating card...');
	        
	        $.ajax({
	    		url: '../api/card/update',
				type: 'POST',
				data: {month:month, year:year},
				dataType: 'json',
				success: function(data){
				
					message.showMessage('success', 'Card updated successfully');
					$('#updatePaymentDialog').modal('hide');
                
	                // refresh the model
	                accountModel.refresh();
	                
				},
				error: function(xhr, errorText, thrownError){
					
					// #debug console.log(xhr.responseText);
					message.showMessage('error', xhr.responseText);
					$('#updatePaymentDialog').modal('hide');
				
				}
			});
    	}
	    
    },
    
    // handles the create token response from stripe
    stripeUpdateResponseHandler:function(status, response){
      
        var form = $('#payment-form');
        
        if (response.error) { // errors
            message.showMessage('error', response.error.message);
        } 
        else {
        
        	var token = response.id;
           
            message.showMessage('progress', 'Adding new card...');
        
            $.ajax({
                url: '../api/card/new',
                type: 'POST',
                data: {token: token},
                success: function(data){
                
                    message.showMessage('success', 'New card added');
                    $('#updatePaymentDialog').modal('hide');
                
	                // refresh the model
	                accountModel.refresh();
                
                },
                statusCode: {
                    406: function() {  // CONFLICT
                        message.showMessage('error', 'The credit card was declined');
                    }
                },
                error: function(data){
                    message.showMessage('error', 'There was a problem updating the card');
                }
            });
        }
        
    },

    newCard:function(o,e){
	    
	    accountModel.showNewCard(true);
	    
    },
    
    showSubscribe:function(o,e){
    	
    	accountModel.updatePlans();
    
		$('#subscribeDialog').modal('show');   
    },
    
    // gets token from stripe
	subscribe:function(o,e){
	
        var form = $('#subscribe-form');
        
        message.showMessage('progress', 'Validating payment...');
        
        Stripe.createToken(form, accountModel.stripeResponseHandler);
		
	},
	
	// handles the create token response from stripe
    stripeResponseHandler:function(status, response){
      
        var form = $('#payment-form');
        
        if (response.error) { // errors
            message.showMessage('error', response.error.message);
        } 
        else {
            // token contains id, last4, and card type
            var token = response.id;
            var plan = 'not-selected';

            var selected = $("input[type='radio'][name='plan']:checked");
            if (selected.length > 0){
                plan = selected.val();
            }
            
            if(plan=='not-selected'){
	            message.showMessage('error', 'Please select a plan');
	            return;
            }

            message.showMessage('progress', 'Paying...');
        
            $.ajax({
                url: '../api/customer/subscribe',
                type: 'POST',
                data: {token: token, plan: plan},
                success: function(data){
                
                    message.showMessage('success', 'Payment successful');
                    
                    // redirect to account
                    $('#subscribeDialog').modal('hide');
                
	                // refresh the model
	                accountModel.refresh();
                
                },
                statusCode: {
                    406: function() {  // CONFLICT
                        message.showMessage('error', 'The credit card was declined');
                    }
                },
                error: function(data){
                    message.showMessage('error', 'There was a problem paying for the subscription');
                }
            });
        }
        
    }
    
}

accountModel.init();