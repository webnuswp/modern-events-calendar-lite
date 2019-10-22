<?php 
wp_enqueue_style('mec-lity-style', $this->main->asset('packages/lity/lity.min.css'));
wp_enqueue_script('mec-lity-script', $this->main->asset('packages/lity/lity.min.js'));
?>
<div id="webnus-dashboard" class="wrap about-wrap">
    <div class="welcome-head w-clearfix">
        <div class="w-row">
            <div class="w-col-sm-9">
                <h1> <?php echo __('Support', 'modern-events-calendar-lite'); ?> </h1>
                <div class="w-welcome">
                    <div class="support-page-links link-to-doc"><a href="https://webnus.net/dox/modern-events-calendar/" target="_blank"><?php esc_html_e('Documentation' , 'modern-events-calendar-lite'); ?></a></div>
                    <div class="support-page-links link-to-videos"><a href="https://webnus.net/dox/modern-events-calendar/video-tutorials/" target="_blank"><?php esc_html_e('All videos' , 'modern-events-calendar-lite'); ?></a></div>
                    <div class="support-page-links link-to-articles"><a href="https://webnus.ticksy.com/articles/100004962/" target="_blank"><?php esc_html_e('All Articles' , 'modern-events-calendar-lite'); ?></a></div>
                    <p>
                        <?php esc_html_e('If you have any questions regarding Modern Events Calendar and how to use it, you can use the following four methods we have prepared in this page. The detailed documentations of MEC along with its instructional videos will help you have a great experience working with it.So, if  you need futher instructions using the plugin, please first refer to the following to find your answers.' , 'modern-events-calendar-lite'); ?>
                    </p>
                </div>
            </div>
            <div class="w-col-sm-3">
                <img src="<?php echo plugin_dir_url(__FILE__ ) . '../../../assets/img/mec-logo-w.png'; ?>" />
                <span class="w-theme-version"><?php echo __('Version', 'modern-events-calendar-lite'); ?> <?php echo MEC_VERSION; ?></span>
            </div>
        </div>
    </div>
    <div class="welcome-content w-clearfix extra">
    <?php if(current_user_can('read')): ?>
        <div class="w-row">
            <div class="w-col-sm-12">
                <div class="w-box support-page searchbox">
                    <div class="w-box-content">
                        <p><?php esc_html_e('Advice and answers from the Webnus Team'); ?></p>  
                        <div class="search-form">
                            <form action="https://webnus.crisp.help/en/" method="">
                                <input type="text" placeholder="<?php esc_html_e('Search...'); ?>" name="search_query">
                                <button><i class="mec-sl-magnifier"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>       
        </div>
        <div class="w-row">
            <div class="w-col-sm-3">
                <div class="w-box support-page videobox">
                    <div class="w-box-head">
                        <?php _e('Quick Setup', 'modern-events-calendar-lite'); ?>
                    </div>
                    <div class="w-box-content">
                        <ul>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('Download the Plugin', 'modern-events-calendar-lite'); ?></li>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('Install and Activate the Plugin', 'modern-events-calendar-lite'); ?></li>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('Add a New Event', 'modern-events-calendar-lite'); ?></li>
                        </ul>
                        <div class="w-button">
                            <a href="https://youtu.be/ZDzcAEtdkC0" data-lity><i class="mec-sl-control-play"></i><?php echo esc_html__('Watch Video', 'modern-events-calendar-lite'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-col-sm-3">
                <div class="w-box support-page videobox">
                    <div class="w-box-head">
                        <?php _e('Activate License', 'modern-events-calendar-lite'); ?>
                    </div>
                    <div class="w-box-content">
                        <ul>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('Login to Dashboard', 'modern-events-calendar-lite'); ?></li>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('Get the License Key', 'modern-events-calendar-lite'); ?></li>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('Activate the plugin', 'modern-events-calendar-lite'); ?></li>
                        </ul>
                        <div class="w-button">
                            <a href="https://youtu.be/V8DAZXuVxrQ" data-lity><i class="mec-sl-control-play"></i><?php echo esc_html__('Watch Video', 'modern-events-calendar-lite'); ?></a>
                        </div>
                    </div>
                </div>
            </div> 
            <div class="w-col-sm-3">
                <div class="w-box support-page videobox">
                    <div class="w-box-head">
                        <?php _e('New Event', 'modern-events-calendar-lite'); ?>
                    </div>
                    <div class="w-box-content">
                        <ul>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('Add New Events, Date and Time', 'modern-events-calendar-lite'); ?></li>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('Tags, Categories, Organizer, Location', 'modern-events-calendar-lite'); ?></li>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('Hourly Schedule,  Set Up Shortcodes', 'modern-events-calendar-lite'); ?></li>
                        </ul>
                        <div class="w-button">
                            <a href="https://youtu.be/cnVy2YzDMOk" data-lity><i class="mec-sl-control-play"></i><?php echo esc_html__('Watch Video', 'modern-events-calendar-lite'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-col-sm-3">
                <div class="w-box support-page videobox">
                    <div class="w-box-head">
                        <?php _e('Enable Booking', 'modern-events-calendar-lite'); ?>
                    </div>
                    <div class="w-box-content">
                        <ul>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('Enable Booking', 'modern-events-calendar-lite'); ?></li>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('Customize the Booking Form', 'modern-events-calendar-lite'); ?></li>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('Set Up a Payment Gateway', 'modern-events-calendar-lite'); ?></li>
                        </ul>
                        <div class="w-button">
                            <a href="https://youtu.be/7X58lpjFDh8" data-lity><i class="mec-sl-control-play"></i><?php echo esc_html__('Watch Video', 'modern-events-calendar-lite'); ?></a>
                        </div>
                    </div>
                </div>
            </div>    
        </div>

        <div class="w-row">
            <div class="w-col-sm-3">
                <div class="w-box support-page videobox">
                    <div class="w-box-head">
                        <?php _e('Add booking form to event', 'modern-events-calendar-lite'); ?>
                    </div>
                    <div class="w-box-content">
                        <ul>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('Enable Booking from Settings', 'modern-events-calendar-lite'); ?></li>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('Set Up a Booking Form', 'modern-events-calendar-lite'); ?></li>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('Customize the Booking Form', 'modern-events-calendar-lite'); ?></li>
                        </ul>
                        <div class="w-button">
                            <a href="https://youtu.be/difbDGz6blU" data-lity><i class="mec-sl-control-play"></i><?php echo esc_html__('Watch Video', 'modern-events-calendar-lite'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-col-sm-3">
                <div class="w-box support-page videobox">
                    <div class="w-box-head">
                        <?php _e('Create Shortcodes', 'modern-events-calendar-lite'); ?>
                    </div>
                    <div class="w-box-content">
                        <ul>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('Create a New Shortcode', 'modern-events-calendar-lite'); ?></li>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('Customize Shortcodes', 'modern-events-calendar-lite'); ?></li>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('Use Filters in Shortcodes', 'modern-events-calendar-lite'); ?></li>
                        </ul>
                        <div class="w-button">
                            <a href="https://youtu.be/iRIBZZlYq54" data-lity><i class="mec-sl-control-play"></i><?php echo esc_html__('Watch Video', 'modern-events-calendar-lite'); ?></a>
                        </div>
                    </div>
                </div>
            </div> 
            <div class="w-col-sm-3">
                <div class="w-box support-page videobox">
                    <div class="w-box-head">
                        <?php _e('WooCommerce gateways', 'modern-events-calendar-lite'); ?>
                    </div>
                    <div class="w-box-content">
                        <ul>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('Install and Activate WooCommerce', 'modern-events-calendar-lite'); ?></li>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('Integrate with MEC', 'modern-events-calendar-lite'); ?></li>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('Set Up WooCommerce Gateway', 'modern-events-calendar-lite'); ?></li>
                        </ul>
                        <div class="w-button">
                            <a href="https://youtu.be/ZI9aWMtwYWE" data-lity><i class="mec-sl-control-play"></i><?php echo esc_html__('Watch Video', 'modern-events-calendar-lite'); ?></a>
                        </div>
                    </div>
            </div>
            </div>
            <div class="w-col-sm-3">
                <div class="w-box support-page videobox">
                    <div class="w-box-head">
                        <?php _e('Settings', 'modern-events-calendar-lite'); ?>
                    </div>
                    <div class="w-box-content">
                        <ul>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('An Overview of the Settings', 'modern-events-calendar-lite'); ?></li>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('Set Up Different Settings', 'modern-events-calendar-lite'); ?></li>
                            <li><i class="mec-sl-arrow-right-circle"></i><?php echo esc_html__('Use Different Options', 'modern-events-calendar-lite'); ?></li>
                        </ul>
                        <div class="w-button">
                            <a href="https://youtu.be/mdXWngl4Lso" data-lity><i class="mec-sl-control-play"></i><?php echo esc_html__('Watch Video', 'modern-events-calendar-lite'); ?></a>
                        </div>
                    </div>
                </div>
            </div>    
        </div>

        <div class="w-row">
            <div class="w-col-sm-6">
                <div class="w-box support-page faq-box">
                    <div class="w-box-head">
                        <?php _e('FAQ', 'modern-events-calendar-lite'); ?>
                    </div>
                    <div class="w-box-content">
                        <div class="mec-faq-accordion">
                            <div class="mec-faq-accordion-trigger"><a href="" class="active"><?php echo esc_html__('How should I update plugin?', 'modern-events-calendar-lite'); ?></a></div>
                            <div class="mec-faq-accordion-content active">
                            <?php echo sprintf(__('You have two options:<br>
                            1-	Uploading the plugin file using FTP. For more information, please <a href="%s" target="_blank">click here</a>.<br>
                            2-	Using the auto-update feature which needs the adding of the purchase code in the corresponding section in the plugin. For more information, please <a href="%s" target="_blank">click here</a>.', 'modern-events-calendar-lite') , 'https://webnus.net/dox/modern-events-calendar/how-to-update-manually-plugin/', 'https://webnus.net/dox/modern-events-calendar/how-to-auto-update-plugin/' ); ?>
                            </div>

                            <div class="mec-faq-accordion-trigger"><a href=""><?php echo esc_html__('Do I lose all my data or customization if I update MEC?', 'modern-events-calendar-lite'); ?></a></div>
                            <div class="mec-faq-accordion-content"><?php esc_html_e('If you’ve added a file to the main folder of MEC, this file will be removed after the update. Therefore, please do get a full back up before proceeding with the update process.', 'modern-events-calendar-lite'); ?>
                            </div>

                            <div class="mec-faq-accordion-trigger"><a href=""><?php echo esc_html__('Can I customize the event pages?', 'modern-events-calendar-lite'); ?></a></div>
                            <div class="mec-faq-accordion-content">
                            <?php echo sprintf(__('Yes, it is possible. In order to see the related documentations, please <a href="%s" target="_blank">click here</a>.', 'modern-events-calendar-lite') , 'https://webnus.net/dox/modern-events-calendar/mec-theme-integration-guide/' ); ?>
                            </div>

                            <div class="mec-faq-accordion-trigger"><a href=""><?php echo esc_html__('Does MEC have default languages or it needs to be translated?', 'modern-events-calendar-lite'); ?></a></div>
                            <div class="mec-faq-accordion-content">
                            <?php echo sprintf(__('Yes, for some of the languages MEC has the translation file as default. However, since these translations have been done by the users, they may be incomplete, hence updating them might be required. For more information, please <a href="%s" target="_blank">click here</a>.', 'modern-events-calendar-lite') , 'https://webnus.net/dox/modern-events-calendar/translate-mec/' ); ?>
                            </div>

                            <div class="mec-faq-accordion-trigger"><a href=""><?php echo esc_html__('Can I have more than one calendar in one website?', 'modern-events-calendar-lite'); ?></a></div>
                            <div class="mec-faq-accordion-content"><?php esc_html_e( 'Unfortunately, MEC does not support more than 1 calendar in a single website, however, it will be added in its upcoming updates.' , 'modern-events-calendar-lite' ); ?></div>

                            <div class="mec-faq-accordion-trigger"><a href=""><?php echo esc_html__('Can I import/export from/to MEC?', 'modern-events-calendar-lite'); ?></a></div>
                            <div class="mec-faq-accordion-content"><?php esc_html_e( 'Yes, you can get an XML export from MEC data or import the file you\'ve exported to MEC. Also, if you are using one of the following plugins (The event calendar, calendarize it, EventOn, Events Schedule WP Plugin), then you can easily transfer your events to MEC.', 'modern-events-calendar-lite' ); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-col-sm-6">
                <div class="w-box support-page articles-box">
                    <div class="w-box-head">
                        <?php _e('Articles', 'modern-events-calendar-lite'); ?>
                    </div>
                    <div class="w-box-content">
                        <a href="https://webnus.ticksy.com/article/13645/" target="_blank"><?php esc_html_e('MEC And Integrate With WooCommerce' , 'modern-events-calendar-lite'); ?></a>
                        <a href="https://webnus.ticksy.com/article/13642/" target="_blank"><?php esc_html_e('Create Events With Your Page Builder' , 'modern-events-calendar-lite'); ?></a>
                        <a href="https://webnus.ticksy.com/article/9621/" target="_blank"><?php esc_html_e('Why can\'t I use HTML tags?' , 'modern-events-calendar-lite'); ?></a>
                        <a href="https://webnus.ticksy.com/article/13484/" target="_blank"><?php esc_html_e('Setup Date Option On Shortcodes' , 'modern-events-calendar-lite'); ?></a>
                        <a href="https://webnus.ticksy.com/article/8604/" target="_blank"><?php esc_html_e('I want to export booking, what should I do?' , 'modern-events-calendar-lite'); ?></a>
                        <a href="https://webnus.ticksy.com/article/8601/" target="_blank"><?php esc_html_e('I Can\'t Export iCal' , 'modern-events-calendar-lite'); ?></a>
                        <a href="https://webnus.ticksy.com/article/8600/" target="_blank"><?php esc_html_e('Booking Module Not Working' , 'modern-events-calendar-lite'); ?></a>
                        <a href="https://webnus.ticksy.com/article/8598/" target="_blank"><?php esc_html_e('Translate MEC' , 'modern-events-calendar-lite'); ?></a>
                        <a href="https://webnus.ticksy.com/article/8602/" target="_blank"><?php esc_html_e('No Event Found!' , 'modern-events-calendar-lite'); ?></a>
                        <a href="https://webnus.ticksy.com/article/10488/" target="_blank"><?php esc_html_e('MEC Theme Integration Guide' , 'modern-events-calendar-lite'); ?></a>
                        <a href="https://webnus.ticksy.com/article/8603/" target="_blank"><?php esc_html_e('Can I Override MEC Template ?' , 'modern-events-calendar-lite'); ?></a>
                    </div>
                </div>
            </div>
        </div>
        <?php if($this->getPRO()) : ?>
        <div class="w-row">
            <div class="w-col-sm-12">
                <div class="w-box support-page mec-ticksy">
                    <div class="w-box-content">
                        <p><?php esc_html_e('Webnus is an elite and trusted author with a high percentage of satisfied users. If you have any issues please don\'t hesitate to contact us, we will reply as soon as possible.' , 'modern-events-calendar-lite'); ?></p>
                        <a href="https://webnus.ticksy.com/" target="_blank"><?php esc_html_e('Create a support ticket','modern-events-calendar-lite'); ?></a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
    </div>
</div>
<script>
(function($) {

    var allPanels = $('.mec-faq-accordion > .mec-faq-accordion-content');
    $('.mec-faq-accordion>.mec-faq-accordion-content.active').show();

    $('.mec-faq-accordion > .mec-faq-accordion-trigger > a').click(function() {
        $this = $(this);
        $target =  $this.parent().next();
        
        if(!$target.hasClass('active')){
            allPanels.removeClass('active').slideUp();
            $target.addClass('active').slideDown();
            $('.mec-faq-accordion > .mec-faq-accordion-trigger > a').removeClass('active')
            $this.addClass('active');
        } else {
            $this.removeClass('active');
            $target.removeClass('active').slideUp();
        }
    return false;
    });

})(jQuery);
</script>
<?php if($this->getPRO()) : ?>
<script type="text/javascript">window.$crisp=[];window.CRISP_WEBSITE_ID="4c1b818f-12d2-4b87-a045-d843628eb07c";(function(){d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);})();</script>
<?php endif; ?>