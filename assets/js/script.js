jQuery(document).ready(function($) {

    $("#myForm").submit(function(e) {

        // Prevent The Browser From Submitting The Form
        e.preventDefault();

        // Form Inputs
        var name    = $("#nameLabel").val();
        var email   = $("#emailLabel").val();
        var budget  = $("#budgetLabel").val();
        var phone   = $("#phoneLabel").val();
        var message = $("#messageLabel").val();
        var DateTime= $("#datetime").val();

        $.ajax({
            type: "POST",
            url : ajax_object.ajax_url, // Admin Ajax
            data : {
                action      : 'submit_data', // The Action
                security    : ajax_object.check_nonce,  // Security Check
                nameLabel   : name,
                emailLabel  : email,
                budgetLabel : budget,
                phoneLabel  : phone,
                messageLabel: message,
                datetime    : DateTime
            },
            success: function(data) {
                $('.response-message').html(data).show();
                $('html, body').animate({ 
                    scrollTop: $('.form-style').offset().top 
                }, 1000);
            },
            error: function (data) {
                $('.response-message').html(data).show();
                $('html, body').animate({ 
                    scrollTop: $('.form-style').offset().top 
                }, 1000);
            }
        });
    })

});

