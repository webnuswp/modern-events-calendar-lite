<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_main $this */

// get screen id
$current_user = wp_get_current_user();

// user event created
$count_events = wp_count_posts($this->get_main_post_type());
$user_post_count = isset($count_events->publish) ? $count_events->publish : '0';

// user calendar created
$count_calendars = wp_count_posts('mec_calendars');
$user_post_count_c = isset($count_calendars->publish) ? $count_calendars->publish : '0';

// mec location
$user_location_count_l = wp_count_terms('mec_location', array(
    'hide_empty'=>false,
    'parent'=>0
));

// mec orgnizer
$user_organizer_count_l = wp_count_terms('mec_organizer', array(
    'hide_empty'=>false,
    'parent'=>0
));

$version = $verify = NULL;
if($this->getPRO()) $mec_license_status = get_option( 'mec_license_status');

// MEC Database
$db = $this->getDB();

// MEC Settings
$settings = $this->get_settings();

// MEC Booking Status
$booking_status = ($this->getPRO() and isset($settings['booking_status']) and $settings['booking_status']) ? true : false;

// Add ChartJS library
if($booking_status) wp_enqueue_script('mec-chartjs-script', $this->asset('js/chartjs.min.js'));

// Whether to show dashboard boxes or not!
$box_support = apply_filters('mec_dashboard_box_support', true);
$box_stats = apply_filters('mec_dashboard_box_stats', true);
?>
<div id="webnus-dashboard" class="wrap about-wrap">
    <div class="welcome-head w-clearfix">
        <div class="w-row">
            <div class="w-col-sm-9">
                <h1> <?php echo sprintf(esc_html__('Welcome %s', 'modern-events-calendar-lite'), $current_user->user_firstname); ?> </h1>
                <div class="w-welcome">
                    <?php echo sprintf(esc_html__('%s - Most Powerful & Easy to Use Events Management System', 'modern-events-calendar-lite'), '<strong>'.($this->getPRO() ? esc_html__('Modern Events Calendar', 'modern-events-calendar-lite') : esc_html__('Modern Events Calendar (Lite)', 'modern-events-calendar-lite')).'</strong>'); ?>
                </div>
            </div>
            <div class="w-col-sm-3">
                <?php $styling = $this->get_styling(); $darkadmin_mode = ( isset($styling['dark_mode']) ) ? $styling['dark_mode'] : ''; if ($darkadmin_mode == 1): $darklogo = plugin_dir_url(__FILE__ ) . '../../../assets/img/mec-logo-w2.png'; else: $darklogo = plugin_dir_url(__FILE__ ) . '../../../assets/img/mec-logo-w.png'; endif; ?>
                <img src="<?php echo esc_url($darklogo); ?>" />
                <span class="w-theme-version"><?php echo esc_html__('Version', 'modern-events-calendar-lite'); ?> <?php echo MEC_VERSION; ?></span>
            </div>
        </div>
    </div>
    <div class="welcome-content w-clearfix extra">
        <div class="w-row" style="margin-bottom: 30px;">
            <div class="w-col-sm-12">
                <script>
                    (function()
                    {
                        var version = parseInt(Math.random()*10000);
                        var webformKey = "8dd552ab6041bd25d23d8a8467819f701f9196106be0e25edc6870c9cc922bdc_"+version;
                        var loaderHTML = '<div class="fs-webform-loader" style="margin:auto">  <style type="text/css">  .loader-box{    width:100%;    margin:auto;    margin-top:50px;    text-align:center;  }  .loader {      border-radius: 50%;      width: 20px;      height: 20px;      animation: spin 1s linear infinite;      border: 3px solid #12344D;      border-top: 3px solid #B3DFFF;      display:block;      margin: 25px auto;  }  @keyframes spin {      0% { transform: rotate(0deg); }      100% { transform: rotate(360deg); }  }  #loader-text{    vertical-align:middle;    text-align:center;    color: #333;    display: inline-block;    vertical-align: middle;    margin-top:-20px;    height:100%;  }  </style>  <div class="loader-box">    <div class="loader"></div>    <div id="loader-text">    </div>  </div></div>';
                        var containerHTML = '<div id="fs-webform-container_'+webformKey+'" class="fs-webform-container fs_8dd552ab6041bd25d23d8a8467819f701f9196106be0e25edc6870c9cc922bdc" style="display:none;"></div>';
                        var scriptTag = document.currentScript || document.getElementById("fs_8dd552ab6041bd25d23d8a8467819f701f9196106be0e25edc6870c9cc922bdc") || document.getElementById("fswebforms") || document.getElementById("formservjs");
                        var docHook = scriptTag.parentElement;
                        var content = document.createElement("div");
                        scriptTag.id = webformKey;
                        docHook.appendChild(content);
                        content.innerHTML = loaderHTML+containerHTML;

                        var webformOptions = {
                            key: "8dd552ab6041bd25d23d8a8467819f701f9196106be0e25edc6870c9cc922bdc",
                            url: "https://webform.freshsales.io/assets/webforms/8dd552ab6041bd25d23d8a8467819f701f9196106be0e25edc6870c9cc922bdc/10",
                            domainURL: "https://webnus.freshsales.io",
                            format: "js",
                            version: version,
                            formVersion: 10
                        };

                        if(window.WebFormQueue)
                        {
                            WebFormQueue.add(webformOptions);
                        }
                        else
                        {
                            var script = document.createElement("script");
                            script.src = "https://assets.freshsales.io/assets/webform-6a8bd10d9118645b79d2d3b3a3112e0901bf1beb.js";
                            script.onload = function(){
                                WebFormQueue.add(webformOptions);
                            };
                            var webformContainer = document.getElementById("fs-webform-container_"+webformKey);
                            webformContainer.appendChild(script);
                        }
                    })();
                </script>
            </div>
        </div>
        <?php if(!$this->getPRO()): ?>
        <div class="w-row mec-pro-notice"  style="margin-bottom: 30px;">
            <div class="w-col-sm-12">
                <div class="info-msg">
                    <p>
                        <?php echo sprintf(esc_html__("You're using %s version of Modern Events Calendar. To use advanced booking system, modern skins like Agenda, Timetable, Masonry, Yearly View, Available Spots, etc you should upgrade to the Pro version.", 'modern-events-calendar-lite'), '<strong>'.esc_html__('lite', 'modern-events-calendar-lite').'</strong>'); ?>
                    </p>
                    <a class="info-msg-link" href="<?php echo esc_url($this->get_pro_link()); ?>" target="_blank">
                        <?php esc_html_e('GO PREMIUM', 'modern-events-calendar-lite'); ?>
                    </a>
                    <div class="info-msg-coupon">

                    </div>
                    <div class="socialfollow">
                        <a target="_blank" href="https://www.facebook.com/WebnusCo/" class="facebook">
                            <i class="mec-sl-social-facebook"></i>
                        </a>
                        <a target="_blank" href="https://twitter.com/webnus" class="twitter">
                            <i class="mec-sl-social-twitter"></i>
                        </a>
                        <a target="_blank" href="https://www.instagram.com/webnus/" class="instagram">
                            <i class="mec-sl-social-instagram"></i>
                        </a>
                        <a target="_blank" href="https://www.youtube.com/channel/UCmQ-VeVK7nLR3bGpAkSYB1Q" class="youtube">
                            <i class="mec-sl-social-youtube"></i>
                        </a>
                        <a target="_blank" href="https://dribbble.com/Webnus" class="dribbble">
                            <i class="mec-sl-social-dribbble"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php echo MEC_kses::full($this->mec_custom_msg_2('yes', 'yes')); ?>
        <?php echo MEC_kses::full($this->mec_custom_msg('yes', 'yes')); ?>
        <div class="w-row">
            <div class="w-col-sm-12">
                <div class="w-box mec-intro-section">
                    <div class="w-box-content mec-intro-section-welcome">
                        <h3><?php esc_html_e('Getting started with Modern Events Calendar' , 'modern-events-calendar-lite'); ?></h3>
                        <p><?php esc_html_e('In this short video, you can learn how to make an event and put a calendar on your website. Please watch this 2 minutes video to the end.' , 'modern-events-calendar-lite'); ?></p>
                    </div>
                    <div class="w-box-content mec-intro-section-ifarme">
                        <iframe width="784" height="441" src="https://www.youtube.com/embed/FV_X341oyiw" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                    <div class="w-box-content mec-intro-section-links wp-core-ui">
                        <a class="mec-intro-section-link-tag button button-primary button-hero" href="<?php esc_html_e(admin_url( 'post-new.php?post_type=mec-events' )); ?>" target="_blank"><?php esc_html_e('Add New Event' , 'modern-events-calendar-lite'); ?>
                        <a class="mec-intro-section-link-tag button button-secondary button-hero" href="<?php esc_html_e(admin_url( 'admin.php?page=MEC-settings' )); ?>" target="_blank"><?php esc_html_e('Settings' , 'modern-events-calendar-lite'); ?>
                        <a class="mec-intro-section-link-tag button button-secondary button-hero" href="https://webnus.net/dox/modern-events-calendar/" target="_blank"><?php esc_html_e('Documentation' , 'modern-events-calendar-lite'); ?></a>
                    </div>
                </div>
            </div>
            <?php if(!$this->getPRO() && has_action('addons_activation') ) : ?>
            <div class="w-col-sm-12">
                <div class="w-box mec-activation">
                    <div class="w-box-head">
                        <?php esc_html_e('License Activation', 'modern-events-calendar-lite'); ?>
                    </div>
                    <?php if (current_user_can( 'administrator' )): ?>
                    <div class="w-box-content">
                        <div class="box-addons-activation">
                            <?php $mec_options = get_option('mec_options'); ?>
                            <div class="box-addon-activation-toggle-head"><i class="mec-sl-plus"></i><span><?php esc_html_e('Activate Addons', 'modern-events-calendar-lite'); ?></span></div>
                            <div class="box-addon-activation-toggle-content">
                                <?php do_action('addons_activation'); ?>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="w-box-content">
                        <p style="background: #f7f7f7f7;display: inline-block;padding: 17px 35px;border-radius: 3px;/* box-shadow: 0 1px 16px rgba(0,0,0,.034); */"><?php echo esc_html__('You cannot access this section.', 'modern-events-calendar-lite'); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if($this->getPRO()) : ?>
            <div class="w-col-sm-12">
                <div class="w-box mec-activation">
                    <div class="w-box-head">
                        <?php esc_html_e('License Activation', 'modern-events-calendar-lite'); ?>
                    </div>
                    <?php
                        if (current_user_can( 'administrator' )):
                    ?>
                    <div class="w-box-content">
                        <p><?php echo esc_html__('In order to use all plugin features and options, please enter your purchase code.', 'modern-events-calendar-lite'); ?></p>
                        <div class="box-mec-avtivation">
                            <?php
                                $mec_options = get_option('mec_options');
                                $product_license = '';
                                $license_status = '';
                                $class_name = 'mec_activate';
                                $button_value = esc_html__('submit' , 'modern-events-calendar-lite');

                                if(!empty($mec_options) and is_array($mec_options) and isset($mec_options['purchase_code'])) $product_license = $mec_options['purchase_code'];

                                if(!empty($mec_options['purchase_code']) && $mec_license_status == 'active')
                                {
                                    $license_status = 'PurchaseSuccess';
                                    $revoke = true;
                                    $class_name = 'mec_revoke';
                                    $button_value = esc_html__('revoke' , 'modern-events-calendar-lite');
                                }
                                elseif(!empty($mec_options['purchase_code']) && $mec_license_status == 'faild')
                                {
                                    $license_status = 'PurchaseError';
                                    $revoke = false;
                                }
                            ?>
                            <form id="MECActivation" action="#" method="post">
                                <div class="LicenseField">
                                    <input type="password" placeholder="Put your purchase code here" name="MECPurchaseCode" value="<?php echo esc_html($product_license); ?>">
                                    <input type="submit" class="<?php echo esc_html($class_name); ?>" value="<?php echo esc_html($button_value); ?>">
                                    <div class="MECPurchaseStatus <?php echo esc_html($license_status); ?>"></div>
                                </div>
                                <div class="MECLicenseMessage mec-message-hidden">
                                    <?php
                                    echo esc_html__('Activation failed. Please check your purchase code or license type. Note: Your purchase code should match your licesne type.', 'modern-events-calendar-lite') . '<a style="text-decoration: underline; padding-left: 7px;" href="https://webnus.net/dox/modern-events-calendar/auto-update-issue/" target="_blank">'  . esc_html__('Troubleshooting', 'modern-events-calendar-lite') . '</a>';
                                    ?>
                                </div>
                            </form>
                        </div>

                        <div class="box-addons-activation">
                            <?php $mec_options = get_option('mec_options'); ?>
                            <div class="box-addon-activation-toggle-head"><i class="mec-sl-plus"></i><span><?php esc_html_e('Activate Addons', 'modern-events-calendar-lite'); ?></span></div>
                            <div class="box-addon-activation-toggle-content">
                                <?php do_action('addons_activation'); ?>
                            </div>
                        </div>
                    </div>
                    <?php
                        else: ?>
                        <div class="w-box-content">
                            <p style="background: #f7f7f7f7;display: inline-block;padding: 17px 35px;border-radius: 3px;/* box-shadow: 0 1px 16px rgba(0,0,0,.034); */"><?php echo esc_html__('You cannot access this section.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                            <?php
                        endif;
                    ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if(current_user_can('read')): ?>
            <div class="w-col-sm-3">
                <div class="w-box doc">
                    <div class="w-box-child mec-count-child">
                        <p><?php echo '<p class="mec_dash_count">'.esc_html($user_post_count).'</p> '.esc_html__('Events', 'modern-events-calendar-lite'); ?></p>
                    </div>
                </div>
            </div>
            <div class="w-col-sm-3">
                <div class="w-box doc">
                    <div class="w-box-child mec-count-child">
                        <p><?php echo '<p class="mec_dash_count">'.esc_html($user_post_count_c).'</p> '.esc_html__('Shortcodes', 'modern-events-calendar-lite'); ?></p>
                    </div>
                </div>
            </div>
            <div class="w-col-sm-3">
                <div class="w-box doc">
                    <div class="w-box-child mec-count-child">
                        <p><?php echo '<p class="mec_dash_count">'.esc_html($user_location_count_l).'</p> '.esc_html__('Locations', 'modern-events-calendar-lite'); ?></p>
                    </div>
                </div>
            </div>
            <div class="w-col-sm-3">
                <div class="w-box doc">
                    <div class="w-box-child mec-count-child">
                        <p><?php echo '<p class="mec_dash_count">'.esc_html($user_organizer_count_l).'</p> '. esc_html__('Organizers', 'modern-events-calendar-lite'); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php if($box_stats): ?>
        <div class="w-row">
            <div class="w-col-sm-<?php echo ($booking_status ? 6 : 12); ?>">
                <div class="w-box upcoming-events">
                    <div class="w-box-head">
                        <?php esc_html_e('Upcoming Events', 'modern-events-calendar-lite'); ?>
                    </div>
                    <div class="w-box-content">
                        <?php
                            $render = $this->getRender();
                            echo MEC_kses::full($render->skin('list', array
                            (
                                'sk-options'=>array('list'=>array
                                (
                                    'style'=>'minimal',
                                    'start_date_type'=>'today',
                                    'load_more_button'=>'0',
                                    'limit'=>'6',
                                    'month_divider'=>'0',
                                    'ignore_js'=>true
                                ))
                            )));
                        ?>
                    </div>
                </div>
            </div>
            <?php if($booking_status): ?>
            <div class="w-col-sm-6">
                <div class="w-box gateways">
                    <div class="w-box-head">
                        <?php echo esc_html__('Popular Gateways', 'modern-events-calendar-lite'); ?>
                    </div>
                    <div class="w-box-content">
                        <?php
                            $results = $db->select("SELECT COUNT(`meta_id`) AS count, `meta_value` AS gateway FROM `#__postmeta` WHERE `meta_key`='mec_gateway' GROUP BY `meta_value`", 'loadAssocList');

                            $labels = '';
                            $data = '';
                            $bg_colors = '';

                            foreach($results as $result)
                            {
                                if (!class_exists($result['gateway'])) {
                                    continue;
                                }

                                $gateway = new $result['gateway'];
                                $stats[] = array('label'=>$gateway->title(), 'count'=>$result['count']);

                                $labels .= '"'.esc_html($gateway->title()).'",';
                                $data .= ((int) $result['count']).',';
                                $bg_colors .= "'".$gateway->color()."',";
                            }
                            echo '<canvas id="mec_gateways_chart" width="300" height="300"></canvas>';

                            $this->getFactory()->params('footer', '<script>
                            jQuery(document).ready(function()
                            {
                                var ctx = document.getElementById("mec_gateways_chart");
                                var mecGatewaysChart = new Chart(ctx,
                                {
                                    type: "doughnut",
                                    data:
                                    {
                                        labels: ['.trim($labels, ', ').'],
                                        datasets: [
                                        {
                                            data: ['.trim($data, ', ').'],
                                            backgroundColor: ['.trim($bg_colors, ', ').']
                                        }]
                                    }
                                });
                            });
                            </script>');
                        ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php if($booking_status and current_user_can('mec_settings')) echo (new MEC_feature_mec())->widget_total_bookings(); ?>
        <?php endif; ?>

        <?php if($this->getPRO()) echo (new MEC_feature_mec())->widget_print(); ?>

        <div class="w-row">
            <div class="w-col-sm-12">
                <div class="w-box change-log">
                    <div class="w-box-head">
                        <?php echo esc_html__('Change Log', 'modern-events-calendar-lite'); ?>
                    </div>
                    <div class="w-box-content">
                        <pre><?php echo file_get_contents(plugin_dir_path(__FILE__ ).'../../../changelog.txt'); ?></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>