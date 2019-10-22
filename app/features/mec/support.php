<?php
/** no direct access **/
defined('MECEXEC') or die();
?>

<div class="wns-be-container wns-be-container-sticky">

    <div id="wns-be-infobar"></div>

    <div class="wns-be-sidebar">

        <ul class="wns-be-group-menu">

            <li class="wns-be-group-menu-li has-sub">
                <a href="<?php echo $this->main->remove_qs_var('tab'); ?>" id="" class="wns-be-group-tab-link-a">
                    <span class="extra-icon">
                        <i class="sl-arrow-down"></i>
                    </span>
                    <i class="mec-sl-settings"></i> 
                    <span class="wns-be-group-menu-title"><?php _e('Settings', 'modern-events-calendar-lite'); ?></span>
                </a>
            </li>

            <?php if($this->main->getPRO() and isset($this->settings['booking_status']) and $this->settings['booking_status']): ?>

                <li class="wns-be-group-menu-li">
                    <a href="<?php echo $this->main->add_qs_var('tab', 'MEC-reg-form'); ?>" id="" class="wns-be-group-tab-link-a">
                        <i class="mec-sl-layers"></i> 
                        <span class="wns-be-group-menu-title"><?php _e('Booking Form', 'modern-events-calendar-lite'); ?></span>
                    </a>
                </li>            

                <li class="wns-be-group-menu-li">
                    <a href="<?php echo $this->main->add_qs_var('tab', 'MEC-gateways'); ?>" id="" class="wns-be-group-tab-link-a">
                        <i class="mec-sl-wallet"></i> 
                        <span class="wns-be-group-menu-title"><?php _e('Payment Gateways', 'modern-events-calendar-lite'); ?></span>
                    </a>
                </li>

            <?php endif; ?>

            <li class="wns-be-group-menu-li">
                <a href="<?php echo $this->main->add_qs_var('tab', 'MEC-notifications'); ?>" id="" class="wns-be-group-tab-link-a">
                    <i class="mec-sl-envelope"></i> 
                    <span class="wns-be-group-menu-title"><?php _e('Notifications', 'modern-events-calendar-lite'); ?></span>
                </a>
            </li>

            <li class="wns-be-group-menu-li">
                <a href="<?php echo $this->main->add_qs_var('tab', 'MEC-styling'); ?>" id="" class="wns-be-group-tab-link-a">
                    <i class="mec-sl-equalizer"></i> 
                    <span class="wns-be-group-menu-title"><?php _e('Styling Options', 'modern-events-calendar-lite'); ?></span>
                </a>
            </li>            

            <li class="wns-be-group-menu-li">
                <a href="<?php echo $this->main->add_qs_var('tab', 'MEC-customcss'); ?>" id="" class="wns-be-group-tab-link-a">
                    <i class="mec-sl-wrench"></i> 
                    <span class="wns-be-group-menu-title"><?php _e('Custom CSS', 'modern-events-calendar-lite'); ?></span>
                </a>
            </li>            

            <li class="wns-be-group-menu-li">
                <a href="<?php echo $this->main->add_qs_var('tab', 'MEC-messages'); ?>" id="" class="wns-be-group-tab-link-a">
                    <i class="mec-sl-bubble"></i> 
                    <span class="wns-be-group-menu-title"><?php _e('Messages', 'modern-events-calendar-lite'); ?></span>
                </a>
            </li>

            <li class="wns-be-group-menu-li">
                <a href="<?php echo $this->main->add_qs_var('tab', 'MEC-ie'); ?>" id="" class="wns-be-group-tab-link-a">
                    <i class="mec-sl-refresh"></i> 
                    <span class="wns-be-group-menu-title"><?php _e('Import / Export', 'modern-events-calendar-lite'); ?></span>
                </a>
            </li>

            <!-- <li class="wns-be-group-menu-li active">
                <a href="<?php echo $this->main->add_qs_var('tab', 'MEC-support'); ?>" id="" class="wns-be-group-tab-link-a">
                    <i class="mec-sl-support"></i> 
                    <span class="wns-be-group-menu-title"><?php _e('Support', 'modern-events-calendar-lite'); ?></span>
                </a>
            </li> -->

        </ul>
    </div>

    <div class="wns-be-main">

        <div id="wns-be-notification"></div>

        <div id="wns-be-content">
            <div class="wns-be-group-tab">
                <h2><?php _e('Support', 'modern-events-calendar-lite'); ?></h2>
                <div class="mec-container">
                    <div id="webnus-dashboard" class="mec-container">
                        <div class="welcome-content w-clearfix extra">
                            <div class="w-col-sm-6">
                                <div class="w-box doc">
                                    <div class="w-box-head">
                                        <?php _e('Documentation', 'modern-events-calendar-lite'); ?>
                                    </div>
                                    <div class="w-box-content">
                                        <p>
                                            <?php echo esc_html__('Our documentation is simple and functional with full details and cover all essential aspects from beginning to the most advanced parts.', 'modern-events-calendar-lite'); ?>
                                        </p>
                                        <div class="w-button">
                                            <a href="http://webnus.net/dox/modern-events-calendar/" target="_blank"><?php echo esc_html__('DOCUMENTATION', 'modern-events-calendar-lite'); ?></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="w-col-sm-1"></div>
                            <div class="w-col-sm-6">
                                <div class="w-box support">
                                    <div class="w-box-head">
                                        <?php echo esc_html__('Support Forum', 'modern-events-calendar-lite'); ?>
                                    </div>
                                    <div class="w-box-content">
                                        <?php if(!$this->getPRO()): ?>
                                            <p><?php echo esc_html__("Webnus is elite and trusted author with high percentage of satisfied user. If you want to use this service you need to upgrade your plugin to Pro version. Click on the following button.", 'modern-events-calendar-lite'); ?></p>
                                        <?php else: ?>
                                            <p><?php echo esc_html__("Webnus is elite and trusted author with high percentage of satisfied user. If you have any issues please don't hesitate to contact us, we will reply as soon as possible.", 'modern-events-calendar-lite'); ?></p>
                                        <?php endif; ?>
                                        <div class="w-button">
                                            <?php if(!$this->getPRO()): ?>
                                                <a href="https://webnus.net/mec-purchase/" target="_blank"><?php echo esc_html__('GO PREMIUM', 'modern-events-calendar-lite'); ?></a>
                                            <?php else: ?>
                                                <a href="https://webnus.ticksy.com/" target="_blank"><?php echo esc_html__('OPEN A TICKET', 'modern-events-calendar-lite'); ?></a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="w-col-sm-1"></div>
                            <div class="w-col-sm-12">
                                <div class="w-box knowledgebase">
                                    <div class="w-box-head w-Knowledgebase">
                                        <?php _e('Knowledgebase', 'modern-events-calendar-lite'); ?>
                                    </div>
                                    <div class="w-box-content">
                                        <ul>
                                            <li><a href="https://webnus.ticksy.com/article/8597/"><?php _e('How to create a new event?', 'modern-events-calendar-lite'); ?></a></li>
                                            <li><a href="https://webnus.ticksy.com/article/8600/"><?php _e("Booking module doesn't work", 'modern-events-calendar-lite'); ?></a></li>
                                            <li><a href="https://webnus.ticksy.com/article/8601/"><?php _e("How to export events in iCal format?", 'modern-events-calendar-lite'); ?></a></li>
                                            <li><a href="https://webnus.ticksy.com/article/8603/"><?php _e("How to override MEC template files?", 'modern-events-calendar-lite'); ?></a></li>
                                            <li><a href="https://webnus.ticksy.com/article/8599/"><?php _e("How to add/manage shortcodes?", 'modern-events-calendar-lite'); ?></a></li>
                                            <li class="mec-view-all-articles"><a href="https://webnus.ticksy.com/articles/100004962/"><?php _e("All Articles", 'modern-events-calendar-lite'); ?></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>