<?php
/** no direct access **/
defined('MECEXEC') or die();

$settings = $this->main->get_settings();

// WordPress Pages
$pages = get_pages();

?>
<div class="wns-be-container wns-be-container-sticky">
    <div id="wns-be-infobar">
        <div class="mec-search-settings-wrap">
            <i class="mec-sl-magnifier"></i>
            <input id="mec-search-settings" type="text" placeholder="<?php esc_html_e('Search...' ,'modern-events-calendar-lite'); ?>">
        </div>
        <a id="" class="dpr-btn dpr-save-btn"><?php _e('Save Changes', 'modern-events-calendar-lite'); ?></a>
    </div>

    <div class="wns-be-sidebar">
        <?php $this->main->get_sidebar_menu('single_event'); ?>
    </div>

    <div class="wns-be-main">
        <div id="wns-be-notification"></div>
        <div id="wns-be-content">
            <div class="wns-be-group-tab">
                <div class="mec-container">

                    <form id="mec_single_form">

                        <div id="event_options" class="mec-options-fields active">

                            <h4 class="mec-form-subtitle"><?php _e('Single Event Page', 'modern-events-calendar-lite'); ?></h4>
                            <div class="mec-form-row">
                                <label class="mec-col-3" for="mec_settings_single_event_date_format1"><?php _e('Single Event Date Format', 'modern-events-calendar-lite'); ?></label>
                                <div class="mec-col-4">
                                    <input type="text" id="mec_settings_single_event_date_format1" name="mec[settings][single_date_format1]" value="<?php echo ((isset($settings['single_date_format1']) and trim($settings['single_date_format1']) != '') ? $settings['single_date_format1'] : 'M d Y'); ?>" />
                                    <span class="mec-tooltip">
                                        <div class="box">
                                            <h5 class="title"><?php _e('Single Event Date Format', 'modern-events-calendar-lite'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e("Default is M d Y", 'modern-events-calendar-lite'); ?><a href="https://webnus.net/dox/modern-events-calendar/event-detailssingle-event-page/" target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>    
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="mec-form-row">
                                <label class="mec-col-3" for="mec_settings_single_event_date_method"><?php _e('Date Method', 'modern-events-calendar-lite'); ?></label>
                                <div class="mec-col-4">
                                    <select id="mec_settings_single_event_date_method" name="mec[settings][single_date_method]">
                                        <option value="next" <?php echo (isset($settings['single_date_method']) and $settings['single_date_method'] == 'next') ? 'selected="selected"' : ''; ?>><?php _e('Next occurrence date', 'modern-events-calendar-lite'); ?></option>
                                        <option value="referred" <?php echo (isset($settings['single_date_method']) and $settings['single_date_method'] == 'referred') ? 'selected="selected"' : ''; ?>><?php _e('Referred date', 'modern-events-calendar-lite'); ?></option>
                                    </select>
                                    <span class="mec-tooltip">
                                        <div class="box">
                                            <h5 class="title"><?php _e('Date Method', 'modern-events-calendar-lite'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e('Referred date" shows the event date based on referred date in event list.', 'modern-events-calendar-lite'); ?><a href="https://webnus.net/dox/modern-events-calendar/event-detailssingle-event-page/" target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>    
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="mec-form-row">
                                <label class="mec-col-3" for="mec_settings_single_event_single_style"><?php _e('Single Event Style', 'modern-events-calendar-lite'); ?></label>
                                <div class="mec-col-4">
                                    <select id="mec_settings_single_event_single_style" name="mec[settings][single_single_style]">
                                        <option value="default" <?php echo (isset($settings['single_single_style']) and $settings['single_single_style'] == 'default') ? 'selected="selected"' : ''; ?>><?php _e('Default Style', 'modern-events-calendar-lite'); ?></option>
                                        <option value="modern" <?php echo (isset($settings['single_single_style']) and $settings['single_single_style'] == 'modern') ? 'selected="selected"' : ''; ?>><?php _e('Modern Style', 'modern-events-calendar-lite'); ?></option>
                                        <?php if ( is_plugin_active( 'mec-single-builder/mec-single-builder.php' ) ) : ?>
                                        <option value="builder" <?php echo (isset($settings['single_single_style']) and $settings['single_single_style'] == 'builder') ? 'selected="selected"' : ''; ?>><?php _e('Elementor Single Builder', 'modern-events-calendar-lite'); ?></option>
                                        <?php endif; ?>
                                    </select>
                                    <span class="mec-tooltip">
                                        <div class="box top">
                                            <h5 class="title"><?php _e('Single Event Style', 'modern-events-calendar-lite'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e("Choose your single event style.", 'modern-events-calendar-lite'); ?><a href="https://webnus.net/dox/modern-events-calendar/event-detailssingle-event-page/" target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>    
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                            </div>
                            <?php if($this->main->getPRO() and isset($this->settings['booking_status']) and $this->settings['booking_status']): ?>
                            <div class="mec-form-row">
                                <label class="mec-col-3" for="mec_settings_single_event_booking_style"><?php _e('Booking Style', 'modern-events-calendar-lite'); ?></label>
                                <div class="mec-col-4">
                                    <select id="mec_settings_single_event_booking_style" name="mec[settings][single_booking_style]">
                                        <option value="default" <?php echo (isset($settings['single_booking_style']) and $settings['single_booking_style'] == 'default') ? 'selected="selected"' : ''; ?>><?php _e('Default', 'modern-events-calendar-lite'); ?></option>
                                        <option value="modal" <?php echo (isset($settings['single_booking_style']) and $settings['single_booking_style'] == 'modal') ? 'selected="selected"' : ''; ?>><?php _e('Modal', 'modern-events-calendar-lite'); ?></option>
                                    </select>
                                    <span class="mec-tooltip">
                                        <div class="box top">
                                            <h5 class="title"><?php _e('Booking Style', 'modern-events-calendar-lite'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e("Choose your Booking style, Please Note: When you set this feature to modal you can not see booking box if you set popoup module view on shortcodes", 'modern-events-calendar-lite'); ?><a href="https://webnus.net/dox/modern-events-calendar/event-detailssingle-event-page/" target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>    
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                            </div>
                            <?php endif;?> 
                            <div class="mec-form-row">
                            <label class="mec-col-3" for="mec_settings_gutenberg"><?php _e('Disable Block Editor (Gutenberg)', 'modern-events-calendar-lite'); ?></label>
                                <label id="mec_settings_gutenberg" >
                                    <input type="hidden" name="mec[settings][gutenberg]" value="0" />
                                    <input value="1" type="checkbox" name="mec[settings][gutenberg]" <?php if(!isset($settings['gutenberg']) or (isset($settings['gutenberg']) and $settings['gutenberg'])) echo 'checked="checked"'; ?> /> <?php _e('Disable Block Editor', 'modern-events-calendar-lite'); ?>
                                </label>
                                <span class="mec-tooltip">
                                    <div class="box top">
                                        <h5 class="title"><?php _e('Block Editor', 'modern-events-calendar-lite'); ?></h5>
                                        <div class="content"><p><?php esc_attr_e("If you want to use the new WordPress block editor you should keep this checkbox unchecked.", 'modern-events-calendar-lite'); ?><a href="https://webnus.net/dox/modern-events-calendar/event-detailssingle-event-page/" target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
                                    </div>
                                    <i title="" class="dashicons-before dashicons-editor-help"></i>
                                </span>
                            </div>
                            <div class="mec-form-row">
                            <label class="mec-col-3" for="mec_settings_breadcrumbs"><?php _e('Breadcrumbs', 'modern-events-calendar-lite'); ?></label>
                                <label id="mec_settings_breadcrumbs" >
                                    <input type="hidden" name="mec[settings][breadcrumbs]" value="0" />
                                    <input type="checkbox" name="mec[settings][breadcrumbs]" id="mec_settings_breadcrumbs" <?php echo ((isset($settings['breadcrumbs']) and $settings['breadcrumbs']) ? 'checked="checked"' : ''); ?> value="1" /><?php _e('Enable Breadcrumbs.', 'modern-events-calendar-lite'); ?>
                                </label>
                                <span class="mec-tooltip">
                                    <div class="box top">
                                        <h5 class="title"><?php _e('Breadcrumbs', 'modern-events-calendar-lite'); ?></h5>
                                        <div class="content"><p><?php esc_attr_e("Check this option, for showing the breadcrumbs on single event page", 'modern-events-calendar-lite'); ?><a href="https://webnus.net/dox/modern-events-calendar/event-detailssingle-event-page/" target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
                                    </div>
                                    <i title="" class="dashicons-before dashicons-editor-help"></i>
                                </span>
                            </div>

                        </div>

                        <div id="countdown_option" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php _e('Countdown Options', 'modern-events-calendar-lite'); ?></h4>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[settings][countdown_status]" value="0" />
                                    <input onchange="jQuery('#mec_count_down_container_toggle').toggle();" value="1" type="checkbox" name="mec[settings][countdown_status]" <?php if(isset($settings['countdown_status']) and $settings['countdown_status']) echo 'checked="checked"'; ?> /> <?php _e('Show countdown module on event page', 'modern-events-calendar-lite'); ?>
                                </label>
                            </div>
                            <div id="mec_count_down_container_toggle" class="<?php if((isset($settings['countdown_status']) and !$settings['countdown_status']) or !isset($settings['countdown_status'])) echo 'mec-util-hidden'; ?>">
                                <div class="mec-form-row">
                                    <label class="mec-col-3" for="mec_settings_countdown_list"><?php _e('Countdown Style', 'modern-events-calendar-lite'); ?></label>
                                    <div class="mec-col-4">
                                        <select id="mec_settings_countdown_list" name="mec[settings][countdown_list]">
                                            <option value="default" <?php echo ((isset($settings['countdown_list']) and $settings['countdown_list'] == "default") ? 'selected="selected"' : ''); ?> ><?php _e('Plain Style', 'modern-events-calendar-lite'); ?></option>
                                            <option value="flip" <?php echo ((isset($settings['countdown_list']) and $settings['countdown_list'] == "flip") ? 'selected="selected"' : ''); ?> ><?php _e('Flip Style', 'modern-events-calendar-lite'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="exceptional_option" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php _e('Exceptional days (Exclude Dates)', 'modern-events-calendar-lite'); ?></h4>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[settings][exceptional_days]" value="0" />
                                    <input value="1" type="checkbox" name="mec[settings][exceptional_days]" <?php if(isset($settings['exceptional_days']) and $settings['exceptional_days']) echo 'checked="checked"'; ?> /> <?php _e('Show exceptional days option on Add/Edit events page', 'modern-events-calendar-lite'); ?>
                                    <span class="mec-tooltip">
                                        <div class="box">
                                            <h5 class="title"><?php _e('Exceptional days (Exclude Dates)', 'modern-events-calendar-lite'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e("Using this option you can exclude certain days from event occurrence dates.", 'modern-events-calendar-lite'); ?><a href="https://webnus.net/dox/modern-events-calendar/exceptional-days/" target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>    
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div id="additional_organizers" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php _e('Additional Organizers', 'modern-events-calendar-lite'); ?></h4>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[settings][additional_organizers]" value="0" />
                                    <input value="1" type="checkbox" name="mec[settings][additional_organizers]" <?php if(!isset($settings['additional_organizers']) or (isset($settings['additional_organizers']) and $settings['additional_organizers'])) echo 'checked="checked"'; ?> /> <?php _e('Show additional organizers option on Add/Edit events page and single event page.', 'modern-events-calendar-lite'); ?>
                                </label>
                            </div>
                        </div>

                        <div id="additional_locations" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php _e('Additional locations', 'modern-events-calendar-lite'); ?></h4>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[settings][additional_locations]" value="0" />
                                    <input value="1" type="checkbox" name="mec[settings][additional_locations]" <?php if(!isset($settings['additional_locations']) or (isset($settings['additional_locations']) and $settings['additional_locations'])) echo 'checked="checked"'; ?> /> <?php _e('Show additional locations option on Add/Edit events page and single event page.', 'modern-events-calendar-lite'); ?>
                                </label>
                            </div>
                        </div>

                        <div id="related_events" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php _e('Related Events', 'modern-events-calendar-lite'); ?></h4>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[settings][related_events]" value="0" />
                                    <input onchange="jQuery('#mec_related_events_container_toggle').toggle();" value="1" type="checkbox" name="mec[settings][related_events]" <?php if(isset($settings['related_events']) and $settings['related_events']) echo 'checked="checked"'; ?> /> <?php _e('Display related events based on taxonomy in single event page.', 'modern-events-calendar-lite'); ?>
                                </label>
                            </div>
                            <div id="mec_related_events_container_toggle" class="<?php if((isset($settings['related_events']) and !$settings['related_events']) or !isset($settings['related_events'])) echo 'mec-util-hidden'; ?>">
                            
                                <div class="mec-form-row" style="margin-top:20px;">
                                    <label style="margin-right:20px;" for="mec_settings_countdown_list"><?php _e('Select Taxonomies:', 'modern-events-calendar-lite'); ?></label>
                                    <label style="margin-right:20px;margin-bottom: 20px">
                                        <input type="hidden" name="mec[settings][related_events_basedon_category]" value="0" />
                                        <input value="1" type="checkbox" name="mec[settings][related_events_basedon_category]" <?php if(isset($settings['related_events_basedon_category']) and $settings['related_events_basedon_category']) echo 'checked="checked"'; ?> /> <?php _e('Category', 'modern-events-calendar-lite'); ?>
                                    </label>
                                    <label style="margin-right:20px;">
                                        <input type="hidden" name="mec[settings][related_events_basedon_organizer]" value="0" />
                                        <input value="1" type="checkbox" name="mec[settings][related_events_basedon_organizer]" <?php if(isset($settings['related_events_basedon_organizer']) and $settings['related_events_basedon_organizer']) echo 'checked="checked"'; ?> /> <?php _e('Organizer', 'modern-events-calendar-lite'); ?>
                                    </label>
                                    <label style="margin-right:20px;">
                                        <input type="hidden" name="mec[settings][related_events_basedon_location]" value="0" />
                                        <input value="1" type="checkbox" name="mec[settings][related_events_basedon_location]" <?php if(isset($settings['related_events_basedon_location']) and $settings['related_events_basedon_location']) echo 'checked="checked"'; ?> /> <?php _e('Location', 'modern-events-calendar-lite'); ?>
                                    </label>
                                    <?php if(isset($settings['speakers_status']) and $settings['speakers_status']) : ?>
                                    <label style="margin-right:20px;">
                                        <input type="hidden" name="mec[settings][related_events_basedon_speaker]" value="0" />
                                        <input value="1" type="checkbox" name="mec[settings][related_events_basedon_speaker]" <?php if(isset($settings['related_events_basedon_speaker']) and $settings['related_events_basedon_speaker']) echo 'checked="checked"'; ?> /> <?php _e('Speaker', 'modern-events-calendar-lite'); ?>
                                    </label>
                                    <?php endif; ?>
                                    <label style="margin-right:20px;">
                                        <input type="hidden" name="mec[settings][related_events_basedon_label]" value="0" />
                                        <input value="1" type="checkbox" name="mec[settings][related_events_basedon_label]" <?php if(isset($settings['related_events_basedon_label']) and $settings['related_events_basedon_label']) echo 'checked="checked"'; ?> /> <?php _e('Label', 'modern-events-calendar-lite'); ?>
                                    </label>
                                    <label style="margin-right:20px;">
                                        <input type="hidden" name="mec[settings][related_events_basedon_tag]" value="0" />
                                        <input value="1" type="checkbox" name="mec[settings][related_events_basedon_tag]" <?php if(isset($settings['related_events_basedon_tag']) and $settings['related_events_basedon_tag']) echo 'checked="checked"'; ?> /> <?php _e('Tag', 'modern-events-calendar-lite'); ?>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mec-options-fields">
                            <?php wp_nonce_field('mec_options_form'); ?>
                            <button style="display: none;" id="mec_single_form_button" class="button button-primary mec-button-primary" type="submit"><?php _e('Save Changes', 'modern-events-calendar-lite'); ?></button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <div id="wns-be-footer">
        <a id="" class="dpr-btn dpr-save-btn"><?php _e('Save Changes', 'modern-events-calendar-lite'); ?></a>
    </div>

</div>

<script type="text/javascript">
jQuery(document).ready(function()
{   
    jQuery(".dpr-save-btn").on('click', function(event)
    {
        event.preventDefault();
        jQuery("#mec_single_form_button").trigger('click');
    });
});

jQuery("#mec_single_form").on('submit', function(event)
{
    event.preventDefault();
    
    // Add loading Class to the button
    jQuery(".dpr-save-btn").addClass('loading').text("<?php echo esc_js(esc_attr__('Saved', 'modern-events-calendar-lite')); ?>");
    jQuery('<div class="wns-saved-settings"><?php echo esc_js(esc_attr__('Settings Saved!', 'modern-events-calendar-lite')); ?></div>').insertBefore('#wns-be-content');

    if(jQuery(".mec-purchase-verify").text() != '<?php echo esc_js(esc_attr__('Verified', 'modern-events-calendar-lite')); ?>')
    {
        jQuery(".mec-purchase-verify").text("<?php echo esc_js(esc_attr__('Checking ...', 'modern-events-calendar-lite')); ?>");
    } 
    
    var settings = jQuery("#mec_single_form").serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=mec_save_settings&"+settings,
        beforeSend: function () {
            jQuery('.wns-be-main').append('<div class="mec-loarder-wrap mec-settings-loader"><div class="mec-loarder"><div></div><div></div><div></div></div></div>');
        },
        success: function(data)
        {
            // Remove the loading Class to the button
            setTimeout(function()
            {
                jQuery(".dpr-save-btn").removeClass('loading').text("<?php echo esc_js(esc_attr__('Save Changes', 'modern-events-calendar-lite')); ?>");
                jQuery('.wns-saved-settings').remove();
                jQuery('.mec-loarder-wrap').remove();
                if(jQuery(".mec-purchase-verify").text() != '<?php echo esc_js(esc_attr__('Verified', 'modern-events-calendar-lite')); ?>')
                {
                    jQuery(".mec-purchase-verify").text("<?php echo esc_js(esc_attr__('Please Refresh Page', 'modern-events-calendar-lite')); ?>");
                }
            }, 1000);
        },
        error: function(jqXHR, textStatus, errorThrown)
        {
            // Remove the loading Class to the button
            setTimeout(function()
            {
                jQuery(".dpr-save-btn").removeClass('loading').text("<?php echo esc_js(esc_attr__('Save Changes', 'modern-events-calendar-lite')); ?>");
                jQuery('.wns-saved-settings').remove();
                jQuery('.mec-loarder-wrap').remove();
            }, 1000);
        }
    });
});
</script>