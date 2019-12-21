<div class="mec-login-form" id="mec-login-form">
    <div class="mec-login-input">
        <label>
            <i class="mec-sl-user"></i>
            <input name="email" id="email" type="text" />
        </label>
    </div>
    <div class="mec-login-input">
        <label>
            <i class="mec-sl-key"></i>
            <input name="password" id="password" type="password" />
        </label>
    </div>
    <div class="mec-login-form-footer">
        <div class="mec-login-forgotpassword">
            <a class="mec-color-hover" href="<?php echo wp_lostpassword_url(); ?>">Forgot Password?</a>
        </div>
        <div class="mec-login-submit">
            <button class="mec-bg-color mec-box-shadow-color">Login</button>
        </div>
    </div>
    <?php wp_nonce_field( 'mec-ajax-login-nonce', 'mec_login_nonce' ); ?>
</div>

<script type="text/javascript">

    jQuery('.mec-login-input #password').keypress(function (e) {
        var key = e.which;
        if(key == 13)  // the enter key code
        {
            jQuery('.mec-login-form-footer button').click();
            return false;  
        }
    });   
    
    jQuery(".mec-login-form-footer button").click(function (e) { 
        e.preventDefault();
        var mec_email   =  jQuery(".mec-login-form #email").val(),
        mec_pass    =  jQuery(".mec-login-form #password").val(),
        mec_nonce   =  jQuery(".mec-login-form #mec_login_nonce").val();

        jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'post',
            data: {
                action: 'mec_ajax_login_data',
                username: mec_email,
                password : mec_pass,
                mec_login_nonce : mec_nonce,
            },
            beforeSend: function(msg){
                jQuery(".mec-login-form").append("<div class=\"mec-ajax-login-loading\"><div class=\"lds-ripple\"><div></div><div></div></div></div>");
            },
            success: function(data) {
                var message = jQuery.parseJSON(data);
                jQuery(".mec-ajax-login-loading").append("<div class=\"mec-ajax-login-loading-text\"></div>");
                if ( !message.loggedin ){
                    jQuery(".mec-ajax-login-loading .lds-ripple").remove();
                    jQuery(".mec-ajax-login-loading-text").addClass('error').append(message.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                    
                } else {
                    jQuery(".mec-ajax-login-loading-text").addClass('success').append(message.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
            },
        });
    });
    
</script>