<?php
/** no direct access **/
defined('MECEXEC') or die();

// Generating javascript code of countdown module
$javascript = '<script type="text/javascript">
jQuery(document).ready(function()
{
    var mec_fes_form_ajax = false;
    jQuery("#mec_fes_form").on("submit", function(event)
    {
        event.preventDefault();
        
        // Hide the message
        jQuery("#mec_fes_form_message").removeClass("mec-success").removeClass("mec-success").html("").hide();

        // Add loading Class to the form
        jQuery("#mec_fes_form").addClass("mec-fes-loading");
        jQuery(".mec-fes-form-cntt").hide();
        jQuery(".mec-fes-form-sdbr").hide();
        jQuery(".mec-fes-submit-wide").hide();

        
        // Fix WordPress editor issue
        jQuery("#mec_fes_content-html").click();
        jQuery("#mec_fes_content-tmce").click();
        
        // Abort previous request
        if(mec_fes_form_ajax) mec_fes_form_ajax.abort();
        
        var data = jQuery("#mec_fes_form").serialize();
        mec_fes_form_ajax = jQuery.ajax(
        {
            type: "POST",
            url: "'.admin_url('admin-ajax.php', NULL).'",
            data: data,
            dataType: "JSON",
            success: function(response)
            {
                // Remove the loading Class from the form
                jQuery("#mec_fes_form").removeClass("mec-fes-loading");
                jQuery(".mec-fes-form-cntt").show();
                jQuery(".mec-fes-form-sdbr").show();
                jQuery(".mec-fes-submit-wide").show();
                
                if(response.success == "1")
                {
                    // Show the message
                    jQuery("#mec_fes_form_message").removeClass("mec-success").addClass("mec-success").html(response.message).css("display","inline-block");
                    
                    // Set the event id
                    jQuery(".mec-fes-post-id").val(response.data.post_id);
                }
                else
                {
                    // Show the message
                    jQuery("#mec_fes_form_message").removeClass("mec-error").addClass("mec-error").html(response.message).css("display","inline-block");
                }
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                // Remove the loading Class from the form
                jQuery("#mec_fes_form").removeClass("loading");
            }
        });
    });
});

function mec_fes_upload_featured_image()
{
    var fd = new FormData();
    fd.append("action", "mec_fes_upload_featured_image");
    fd.append("_wpnonce", "'.wp_create_nonce('mec_fes_upload_featured_image').'");
    fd.append("file", jQuery("#mec_featured_image_file").prop("files")[0]);
    
    jQuery.ajax(
    {
        url: "'.admin_url('admin-ajax.php', NULL).'",
        type: "POST",
        data: fd,
        dataType: "json",
        processData: false,
        contentType: false
    })
    .done(function(data)
    {
        jQuery("#mec_fes_thumbnail").val(data.data.url);
        jQuery("#mec_featured_image_file").val("");
        jQuery("#mec_fes_thumbnail_img").html("<img src=\""+data.data.url+"\" />");
        jQuery("#mec_fes_remove_image_button").removeClass("mec-util-hidden");
    });
    
    return false;
}

function mec_fes_upload_location_thumbnail()
{
    var fd = new FormData();
    
    fd.append("action", "mec_fes_upload_featured_image");
    fd.append("_wpnonce", "'.wp_create_nonce('mec_fes_upload_featured_image').'");
    fd.append("file", jQuery("#mec_fes_location_thumbnail_file").prop("files")[0]);
    
    jQuery.ajax(
    {
        url: "'.admin_url('admin-ajax.php', NULL).'",
        type: "POST",
        data: fd,
        dataType: "json",
        processData: false,
        contentType: false
    })
    .done(function(data)
    {
        jQuery("#mec_fes_location_thumbnail").val(data.data.url);
        jQuery("#mec_fes_location_thumbnail_file").val("");
        jQuery("#mec_fes_location_thumbnail_img").html("<img src=\""+data.data.url+"\" />");
        jQuery("#mec_fes_location_remove_image_button").removeClass("mec-util-hidden");
    });
    
    return false;
}

function mec_fes_upload_organizer_thumbnail()
{
    var fd = new FormData();
    
    fd.append("action", "mec_fes_upload_featured_image");
    fd.append("_wpnonce", "'.wp_create_nonce('mec_fes_upload_featured_image').'");
    fd.append("file", jQuery("#mec_fes_organizer_thumbnail_file").prop("files")[0]);
    
    jQuery.ajax(
    {
        url: "'.admin_url('admin-ajax.php', NULL).'",
        type: "POST",
        data: fd,
        dataType: "json",
        processData: false,
        contentType: false
    })
    .done(function(data)
    {
        jQuery("#mec_fes_organizer_thumbnail").val(data.data.url);
        jQuery("#mec_fes_organizer_thumbnail_file").val("");
        jQuery("#mec_fes_organizer_thumbnail_img").html("<img src=\""+data.data.url+"\" />");
        jQuery("#mec_fes_organizer_remove_image_button").removeClass("mec-util-hidden");
    });
    
    return false;
}
</script>';

// Include javascript code into the footer
$this->factory->params('footer', $javascript);
?>
<div class="mec-fes-form">
    <?php if(is_user_logged_in()): ?>
    <div class="mec-fes-form-top-actions">
        <?php do_action('mec_fes_form_top_actions'); ?>
        <a class="mec-fes-form-back-to" href="<?php echo $this->link_list_events(); ?>"><?php echo __('Go back to events list', 'modern-events-calendar-lite'); ?></a>
        <?php $status = $this->main->get_event_label_status(get_post_status($post_id)); ?>
        <?php if(trim($status['label']) != "Empty"): ?>
        <span class="post-status <?php echo $status['status_class'];  ?>"><?php echo $status['label'];  ?></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="mec-util-hidden" id="mec_fes_form_message"></div>
    <form id="mec_fes_form" enctype="multipart/form-data">
        <?php
            $allday = get_post_meta($post_id, 'mec_allday', true);
            $comment = get_post_meta($post_id, 'mec_comment', true);
            $hide_time = get_post_meta($post_id, 'mec_hide_time', true);
            $hide_end_time = get_post_meta($post_id, 'mec_hide_end_time', true);
        
            $start_date = get_post_meta($post_id, 'mec_start_date', true);

            // Advanced Repeating Day
		    $advanced_days = get_post_meta( $post->ID, 'mec_advanced_days', true );
		    $advanced_days = (is_array($advanced_days)) ? $advanced_days : array();
		    $advanced_str = (count($advanced_days)) ? implode('-', $advanced_days) : '';

            $start_time_hour = get_post_meta($post_id, 'mec_start_time_hour', true);
            if(trim($start_time_hour) == '') $start_time_hour = 8;

            $start_time_minutes = get_post_meta($post_id, 'mec_start_time_minutes', true);
            if(trim($start_time_minutes) == '') $start_time_minutes = 0;

            $start_time_ampm = get_post_meta($post_id, 'mec_start_time_ampm', true);
            if(trim($start_time_ampm) == '') $start_time_minutes = 'AM';

            $end_date = get_post_meta($post_id, 'mec_end_date', true);

            $end_time_hour = get_post_meta($post_id, 'mec_end_time_hour', true);
            if(trim($end_time_hour) == '') $end_time_hour = 6;

            $end_time_minutes = get_post_meta($post_id, 'mec_end_time_minutes', true);
            if(trim($end_time_minutes) == '') $end_time_minutes = 0;

            $end_time_ampm = get_post_meta($post_id, 'mec_end_time_ampm', true);
            if(trim($end_time_ampm) == '') $end_time_ampm = 'PM';

            $repeat_status = get_post_meta($post_id, 'mec_repeat_status', true);
            $repeat_type = get_post_meta($post_id, 'mec_repeat_type', true);

            $repeat_interval = get_post_meta($post_id, 'mec_repeat_interval', true);
            if(trim($repeat_interval) == '' and in_array($repeat_type, array('daily', 'weekly'))) $repeat_interval = 1;

            $certain_weekdays = get_post_meta($post_id, 'mec_certain_weekdays', true);
            if($repeat_type != 'certain_weekdays') $certain_weekdays = array();
            
            $in_days_str = get_post_meta($post_id, 'mec_in_days', true);
            $in_days = trim($in_days_str) ? explode(',', $in_days_str) : array();
            
            $mec_repeat_end = get_post_meta($post_id, 'mec_repeat_end', true);
            if(trim($mec_repeat_end) == '') $mec_repeat_end = 'never';

            $repeat_end_at_occurrences = get_post_meta($post_id, 'mec_repeat_end_at_occurrences', true);
            if(trim($repeat_end_at_occurrences) == '') $repeat_end_at_occurrences = 9;

            $repeat_end_at_date = get_post_meta($post_id, 'mec_repeat_end_at_date', true);
        ?>

        <div class="mec-fes-form-cntt">
            <div class="mec-form-row">
                <label for="mec_fes_title"><?php _e('Title', 'modern-events-calendar-lite'); ?></label>
                <input type="text" name="mec[title]" id="mec_fes_title" value="<?php echo (isset($post->post_title) ? $post->post_title : ''); ?>" required="required" />
            </div>
            <div class="mec-form-row">
                <?php wp_editor((isset($post->post_content) ? $post->post_content : ''), 'mec_fes_content', array('textarea_name'=>'mec[content]')); ?>
            </div>
            <div class="mec-meta-box-fields" id="mec-date-time">
                <h4><?php _e('Date and Time', 'modern-events-calendar-lite'); ?></h4>
                <div id="mec_meta_box_date_form">
                    <div class="mec-title">
                        <span class="mec-dashicons dashicons dashicons-calendar-alt"></span>
                        <label for="mec_start_date"><?php _e('Start Date', 'modern-events-calendar-lite'); ?></label>
                    </div>
                    <div class="mec-form-row">
                        <div class="mec-col-4">
                            <input type="text" name="mec[date][start][date]" id="mec_start_date" value="<?php echo esc_attr($start_date); ?>" placeholder="<?php _e('Start Date', 'modern-events-calendar-lite'); ?>" class="" />
                        </div>
                        <div class="mec-col-6 mec-time-picker">
                            <?php if(isset($this->settings['time_format']) and $this->settings['time_format'] == 24): if($start_time_ampm == 'PM' and $start_time_hour != 12) $start_time_hour += 12; if($start_time_ampm == 'AM' and $start_time_hour == 12) $start_time_hour += 12; ?>
                            <select name="mec[date][start][hour]" id="mec_start_hour">
                                <?php for($i=0; $i<=23; $i++): ?>
                                <option <?php if($start_time_hour == $i) echo 'selected="selected"'; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <span class="time-dv">:</span>
                            <select name="mec[date][start][minutes]" id="mec_start_minutes">
                                <?php for($i=0; $i<=11; $i++): ?>
                                <option <?php if($start_time_minutes == ($i*5)) echo 'selected="selected"'; ?> value="<?php echo ($i*5); ?>"><?php echo sprintf("%02d", ($i*5)); ?></option>
                                <?php endfor; ?>
                            </select>
                            <?php else: ?>
                            <select name="mec[date][start][hour]" id="mec_start_hour">
                                <?php for($i=1; $i<=12; $i++): ?>
                                <option <?php if($start_time_hour == $i) echo 'selected="selected"'; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <span class="time-dv">:</span>
                            <select name="mec[date][start][minutes]" id="mec_start_minutes">
                                <?php for($i=0; $i<=11; $i++): ?>
                                <option <?php if($start_time_minutes == ($i*5)) echo 'selected="selected"'; ?> value="<?php echo ($i*5); ?>"><?php echo sprintf("%02d", ($i*5)); ?></option>
                                <?php endfor; ?>
                            </select>
                            <select name="mec[date][start][ampm]" id="mec_start_ampm">
                                <option <?php if($start_time_ampm == 'AM') echo 'selected="selected"'; ?> value="AM"><?php _e('AM', 'modern-events-calendar-lite'); ?></option>
                                <option <?php if($start_time_ampm == 'PM') echo 'selected="selected"'; ?> value="PM"><?php _e('PM', 'modern-events-calendar-lite'); ?></option>
                            </select>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mec-title">
                        <span class="mec-dashicons dashicons dashicons-calendar-alt"></span>
                        <label for="mec_end_date"><?php _e('End Date', 'modern-events-calendar-lite'); ?></label>
                    </div>
                    <div class="mec-form-row">
                        <div class="mec-col-4">
                            <input type="text" name="mec[date][end][date]" id="mec_end_date" value="<?php echo esc_attr($end_date); ?>" placeholder="<?php _e('End Date', 'modern-events-calendar-lite'); ?>" class="" />
                        </div>
                        <div class="mec-col-6 mec-time-picker">
                            <?php if(isset($this->settings['time_format']) and $this->settings['time_format'] == 24): if($end_time_ampm == 'PM' and $end_time_hour != 12) $end_time_hour += 12; if($end_time_ampm == 'AM' and $end_time_hour == 12) $end_time_hour += 12; ?>
                            <select name="mec[date][end][hour]" id="mec_end_hour">
                                <?php for($i=0; $i<=23; $i++): ?>
                                <option <?php if($end_time_hour == $i) echo 'selected="selected"'; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <span class="time-dv">:</span>
                            <select name="mec[date][end][minutes]" id="mec_end_minutes">
                                <?php for($i=0; $i<=11; $i++): ?>
                                <option <?php if($end_time_minutes == ($i*5)) echo 'selected="selected"'; ?> value="<?php echo ($i*5); ?>"><?php echo sprintf("%02d", ($i*5)); ?></option>
                                <?php endfor; ?>
                            </select>
                            <?php else: ?>
                            <select name="mec[date][end][hour]" id="mec_end_hour">
                                <?php for($i=1; $i<=12; $i++): ?>
                                <option <?php if($end_time_hour == $i) echo 'selected="selected"'; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <span class="time-dv">:</span>
                            <select name="mec[date][end][minutes]" id="mec_end_minutes">
                                <?php for($i=0; $i<=11; $i++): ?>
                                <option <?php if($end_time_minutes == ($i*5)) echo 'selected="selected"'; ?> value="<?php echo ($i*5); ?>"><?php echo sprintf("%02d", ($i*5)); ?></option>
                                <?php endfor; ?>
                            </select>
                            <select name="mec[date][end][ampm]" id="mec_end_ampm">
                                <option <?php if($end_time_ampm == 'AM') echo 'selected="selected"'; ?> value="AM"><?php _e('AM', 'modern-events-calendar-lite'); ?></option>
                                <option <?php if($end_time_ampm == 'PM') echo 'selected="selected"'; ?> value="PM"><?php _e('PM', 'modern-events-calendar-lite'); ?></option>
                            </select>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mec-form-row">
                        <input <?php if($allday == '1') echo 'checked="checked"'; ?> type="checkbox" name="mec[date][allday]" id="mec_allday" value="1" onchange="jQuery('.mec-time-picker').toggle();" /><label for="mec_allday"><?php _e('All Day Event', 'modern-events-calendar-lite'); ?></label>
                    </div>
                    <div class="mec-form-row">
                        <input <?php if($hide_time == '1') echo 'checked="checked"'; ?> type="checkbox" name="mec[date][hide_time]" id="mec_hide_time" value="1" /><label for="mec_hide_time"><?php _e('Hide Event Time', 'modern-events-calendar-lite'); ?></label>
                    </div>
                    <div class="mec-form-row">
                        <input <?php if($hide_end_time == '1') echo 'checked="checked"'; ?> type="checkbox" name="mec[date][hide_end_time]" id="mec_hide_end_time" value="1" /><label for="mec_hide_end_time"><?php _e('Hide Event End Time', 'modern-events-calendar-lite'); ?></label>
                    </div>
                    <div class="mec-form-row">
                        <div class="mec-col-4">
                            <input type="text" class="" name="mec[date][comment]" id="mec_comment" placeholder="<?php _e('Time Comment', 'modern-events-calendar-lite'); ?>" value="<?php echo esc_attr($comment); ?>" />
                            <p class="description"><?php _e('It shows next to event time on single event page. You can insert Timezone etc. in this field.', 'modern-events-calendar-lite'); ?></p>
                        </div>
                    </div>
                </div>
                <div id="mec_meta_box_repeat_form">
                    <div class="mec-form-row">
                        <input <?php if($repeat_status == '1') echo 'checked="checked"'; ?> type="checkbox" name="mec[date][repeat][status]" id="mec_repeat" value="1" /><label for="mec_repeat"><?php _e('Event Repeating', 'modern-events-calendar-lite'); ?></label>
                    </div>
                    <div class="mec-form-repeating-event-row">
                        <div class="mec-form-row">
                            <label class="mec-col-3" for="mec_repeat_type"><?php _e('Repeats', 'modern-events-calendar-lite'); ?></label>
                            <select class="mec-col-2" name="mec[date][repeat][type]" id="mec_repeat_type">
                                <option <?php if($repeat_type == 'daily') echo 'selected="selected"'; ?> value="daily"><?php _e('Daily', 'modern-events-calendar-lite'); ?></option>
                                <option <?php if($repeat_type == 'weekday') echo 'selected="selected"'; ?> value="weekday"><?php _e('Every Weekday', 'modern-events-calendar-lite'); ?></option>
                                <option <?php if($repeat_type == 'weekend') echo 'selected="selected"'; ?> value="weekend"><?php _e('Every Weekend', 'modern-events-calendar-lite'); ?></option>
                                <option <?php if($repeat_type == 'certain_weekdays') echo 'selected="selected"'; ?> value="certain_weekdays"><?php _e('Certain Weekdays', 'modern-events-calendar-lite'); ?></option>
                                <option <?php if($repeat_type == 'weekly') echo 'selected="selected"'; ?> value="weekly"><?php _e('Weekly', 'modern-events-calendar-lite'); ?></option>
                                <option <?php if($repeat_type == 'monthly') echo 'selected="selected"'; ?> value="monthly"><?php _e('Monthly', 'modern-events-calendar-lite'); ?></option>
                                <option <?php if($repeat_type == 'yearly') echo 'selected="selected"'; ?> value="yearly"><?php _e('Yearly', 'modern-events-calendar-lite'); ?></option>
                                <option <?php if($repeat_type == 'custom_days') echo 'selected="selected"'; ?> value="custom_days"><?php _e('Custom Days', 'modern-events-calendar-lite'); ?></option>
                                <option <?php if($repeat_type == 'advanced') echo 'selected="selected"'; ?> value="advanced"><?php _e('Advanced', 'modern-events-calendar-lite'); ?></option>
                            </select>
                        </div>
                        <div class="mec-form-row" id="mec_repeat_interval_container">
                            <label class="mec-col-3" for="mec_repeat_interval"><?php _e('Repeat Interval', 'modern-events-calendar-lite'); ?></label>
                            <input class="mec-col-2" type="text" name="mec[date][repeat][interval]" id="mec_repeat_interval" placeholder="<?php _e('Repeat interval', 'modern-events-calendar-lite'); ?>" value="<?php echo ($repeat_type == 'weekly' ? ($repeat_interval/7) : $repeat_interval); ?>" />
                        </div>
                        <div class="mec-form-row" id="mec_repeat_certain_weekdays_container">
                            <label class="mec-col-3"><?php _e('Week Days', 'modern-events-calendar-lite'); ?></label>
                            <label><input type="checkbox" name="mec[date][repeat][certain_weekdays][]" value="1" <?php echo (in_array(1, $certain_weekdays) ? 'checked="checked"' : ''); ?> /><?php _e('Monday', 'modern-events-calendar-lite'); ?></label>
                            <label>&nbsp;<input type="checkbox" name="mec[date][repeat][certain_weekdays][]" value="2" <?php echo (in_array(2, $certain_weekdays) ? 'checked="checked"' : ''); ?> /><?php _e('Tuesday', 'modern-events-calendar-lite'); ?></label>
                            <label>&nbsp;<input type="checkbox" name="mec[date][repeat][certain_weekdays][]" value="3" <?php echo (in_array(3, $certain_weekdays) ? 'checked="checked"' : ''); ?> /><?php _e('Wednesday', 'modern-events-calendar-lite'); ?></label>
                            <label>&nbsp;<input type="checkbox" name="mec[date][repeat][certain_weekdays][]" value="4" <?php echo (in_array(4, $certain_weekdays) ? 'checked="checked"' : ''); ?> /><?php _e('Thursday', 'modern-events-calendar-lite'); ?></label>
                            <label>&nbsp;<input type="checkbox" name="mec[date][repeat][certain_weekdays][]" value="5" <?php echo (in_array(5, $certain_weekdays) ? 'checked="checked"' : ''); ?> /><?php _e('Friday', 'modern-events-calendar-lite'); ?></label>
                            <label>&nbsp;<input type="checkbox" name="mec[date][repeat][certain_weekdays][]" value="6" <?php echo (in_array(6, $certain_weekdays) ? 'checked="checked"' : ''); ?> /><?php _e('Saturday', 'modern-events-calendar-lite'); ?></label>
                            <label>&nbsp;<input type="checkbox" name="mec[date][repeat][certain_weekdays][]" value="7" <?php echo (in_array(7, $certain_weekdays) ? 'checked="checked"' : ''); ?> /><?php _e('Sunday', 'modern-events-calendar-lite'); ?></label>
                        </div>
                        <div class="mec-form-row" id="mec_exceptions_in_days_container">
                            <div class="mec-form-row">
                                <div class="mec-col-6">
                                    <input type="text" id="mec_exceptions_in_days_start_date" value="" placeholder="<?php _e('Start', 'modern-events-calendar-lite'); ?>" class="mec_date_picker" />
                                    <input type="text" id="mec_exceptions_in_days_end_date" value="" placeholder="<?php _e('End', 'modern-events-calendar-lite'); ?>" class="mec_date_picker" />
                                    <button class="button" type="button" id="mec_add_in_days"><?php _e('Add', 'modern-events-calendar-lite'); ?></button>
                                    <p class="description"><?php _e('Add certain days to event occurrence dates.', 'modern-events-calendar-lite'); ?></p>
                                </div>
                            </div>
                            <div class="mec-form-row" id="mec_in_days">
                                <?php $i = 1; foreach($in_days as $in_day): ?>
                                <div class="mec-form-row" id="mec_in_days_row<?php echo $i; ?>">
                                    <input type="hidden" name="mec[in_days][<?php echo $i; ?>]" value="<?php echo $in_day; ?>" />
                                    <span class="mec-in-days-day"><?php echo str_replace(':', ' - ', $in_day); ?></span>
                                    <span class="mec-in-days-remove" onclick="mec_in_days_remove(<?php echo $i; ?>);">x</span>
                                </div>
                                <?php $i++; endforeach; ?>
                            </div>
                            <input type="hidden" id="mec_new_in_days_key" value="<?php echo $i+1; ?>" />
                            <div class="mec-util-hidden" id="mec_new_in_days_raw">
                                <div class="mec-form-row" id="mec_in_days_row:i:">
                                    <input type="hidden" name="mec[in_days][:i:]" value=":val:" />
                                    <span class="mec-in-days-day">:label:</span>
                                    <span class="mec-in-days-remove" onclick="mec_in_days_remove(:i:);">x</span>
                                </div>
                            </div>
                        </div>
                        <div id="mec-advanced-wraper">
                            <div class="mec-form-row">
                                <ul>
                                    <li>
                                        <?php _e('First', 'modern-events-calendar-lite'); ?>
                                    </li>
                                    <ul>
                                        <?php $day_1th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 1); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_1th}.1"); ?>">
                                            <?php _e($day_1th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_1th ?>.1-</span>
                                        </li>
                                        <?php $day_2th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 2); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_2th}.1"); ?>">
                                            <?php _e($day_2th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_2th ?>.1-</span>
                                        </li>
                                        <?php $day_3th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 3); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_3th}.1"); ?>">
                                            <?php _e($day_3th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_3th ?>.1-</span>
                                        </li>
                                        <?php $day_4th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 4); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_4th}.1"); ?>">
                                            <?php _e($day_4th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_4th ?>.1-</span>
                                        </li>
                                        <?php $day_5th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 5); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_5th}.1"); ?>">
                                            <?php _e($day_5th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_5th ?>.1-</span>
                                        </li>
                                        <?php $day_6th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 6); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_6th}.1"); ?>">
                                            <?php _e($day_6th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_6th ?>.1-</span>
                                        </li>
                                        <?php $day_7th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 7); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_7th}.1"); ?>">
                                            <?php _e($day_7th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_7th ?>.1-</span>
                                        </li>
                                    </ul>
                                </ul>
                                <ul>
                                    <li>
                                        <?php _e('Second', 'modern-events-calendar-lite'); ?>
                                    </li>
                                    <ul>
                                        <?php $day_1th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 1); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_1th}.2"); ?>">
                                            <?php _e($day_1th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_1th ?>.2-</span>
                                        </li>
                                        <?php $day_2th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 2); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_2th}.2"); ?>">
                                            <?php _e($day_2th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_2th ?>.2-</span>
                                        </li>
                                        <?php $day_3th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 3); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_3th}.2"); ?>">
                                            <?php _e($day_3th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_3th ?>.2-</span>
                                        </li>
                                        <?php $day_4th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 4); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_4th}.2"); ?>">
                                            <?php _e($day_4th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_4th ?>.2-</span>
                                        </li>
                                        <?php $day_5th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 5); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_5th}.2"); ?>">
                                            <?php _e($day_5th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_5th ?>.2-</span>
                                        </li>
                                        <?php $day_6th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 6); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_6th}.2"); ?>">
                                            <?php _e($day_6th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_6th ?>.2-</span>
                                        </li>
                                        <?php $day_7th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 7); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_7th}.2"); ?>">
                                            <?php _e($day_7th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_7th ?>.2-</span>
                                        </li>
                                    </ul>
                                </ul>
                                <ul>
                                    <li>
                                        <?php _e('Third', 'modern-events-calendar-lite'); ?>
                                    </li>
                                    <ul>
                                        <?php $day_1th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 1); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_1th}.3"); ?>">
                                            <?php _e($day_1th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_1th ?>.3-</span>
                                        </li>
                                        <?php $day_2th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 2); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_2th}.3"); ?>">
                                            <?php _e($day_2th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_2th ?>.3-</span>
                                        </li>
                                        <?php $day_3th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 3); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_3th}.3"); ?>">
                                            <?php _e($day_3th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_3th ?>.3-</span>
                                        </li>
                                        <?php $day_4th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 4); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_4th}.3"); ?>">
                                            <?php _e($day_4th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_4th ?>.3-</span>
                                        </li>
                                        <?php $day_5th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 5); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_5th}.3"); ?>">
                                            <?php _e($day_5th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_5th ?>.3-</span>
                                        </li>
                                        <?php $day_6th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 6); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_6th}.3"); ?>">
                                            <?php _e($day_6th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_6th ?>.3-</span>
                                        </li>
                                        <?php $day_7th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 7); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_7th}.3"); ?>">
                                            <?php _e($day_7th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_7th ?>.3-</span>
                                        </li>
                                    </ul>
                                </ul>
                                <ul>
                                    <li>
                                        <?php _e('Fourth', 'modern-events-calendar-lite'); ?>
                                    </li>
                                    <ul>
                                        <?php $day_1th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 1); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_1th}.4"); ?>">
                                            <?php _e($day_1th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_1th ?>.4-</span>
                                        </li>
                                        <?php $day_2th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 2); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_2th}.4"); ?>">
                                            <?php _e($day_2th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_2th ?>.4-</span>
                                        </li>
                                        <?php $day_3th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 3); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_3th}.4"); ?>">
                                            <?php _e($day_3th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_3th ?>.4-</span>
                                        </li>
                                        <?php $day_4th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 4); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_4th}.4"); ?>">
                                            <?php _e($day_4th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_4th ?>.4-</span>
                                        </li>
                                        <?php $day_5th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 5); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_5th}.4"); ?>">
                                            <?php _e($day_5th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_5th ?>.4-</span>
                                        </li>
                                        <?php $day_6th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 6); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_6th}.4"); ?>">
                                            <?php _e($day_6th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_6th ?>.4-</span>
                                        </li>
                                        <?php $day_7th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 7); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_7th}.4"); ?>">
                                            <?php _e($day_7th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_7th ?>.4-</span>
                                        </li>
                                    </ul>
                                </ul>
                                <ul>
                                    <li>
                                        <?php _e('Last', 'modern-events-calendar-lite'); ?>
                                    </li>
                                    <ul>
                                        <?php $day_1th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 1); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_1th}.l"); ?>">
                                            <?php _e($day_1th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_1th ?>.l-</span>
                                        </li>
                                        <?php $day_2th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 2); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_2th}.l"); ?>">
                                            <?php _e($day_2th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_2th ?>.l-</span>
                                        </li>
                                        <?php $day_3th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 3); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_3th}.l"); ?>">
                                            <?php _e($day_3th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_3th ?>.l-</span>
                                        </li>
                                        <?php $day_4th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 4); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_4th}.l"); ?>">
                                            <?php _e($day_4th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_4th ?>.l-</span>
                                        </li>
                                        <?php $day_5th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 5); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_5th}.l"); ?>">
                                            <?php _e($day_5th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_5th ?>.l-</span>
                                        </li>
                                        <?php $day_6th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 6); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_6th}.l"); ?>">
                                            <?php _e($day_6th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_6th ?>.l-</span>
                                        </li>
                                        <?php $day_7th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 7); ?>
                                        <li class="<?php $this->main->mec_active($advanced_days, "{$day_7th}.l"); ?>">
                                            <?php _e($day_7th, 'modern-events-calendar-lite'); ?>
                                            <span class="key"><?php echo $day_7th ?>.l-</span>
                                        </li>
                                    </ul>
                                </ul>
                                <input class="mec-col-2" type="hidden" name="mec[date][repeat][advanced]"
                                 id="mec_date_repeat_advanced"  value="<?php echo esc_attr($advanced_str); ?>" />
                            </div>
                        </div>
                        <div id="mec_end_wrapper">
                            <div class="mec-form-row">
                                <label for="mec_repeat_ends_never"><h5 class="mec-title"><?php _e('Ends Repeat', 'modern-events-calendar-lite'); ?></h5></label>
                            </div>
                            <div class="mec-form-row">
                                <input <?php if($mec_repeat_end == 'never') echo 'checked="checked"'; ?> type="radio" value="never" name="mec[date][repeat][end]" id="mec_repeat_ends_never" />
                                <label for="mec_repeat_ends_never"><?php _e('Never', 'modern-events-calendar-lite'); ?></label>
                            </div>
                            <div class="mec-form-row">
                                <div class="mec-col-3">
                                    <input <?php if($mec_repeat_end == 'date') echo 'checked="checked"'; ?> type="radio" value="date" name="mec[date][repeat][end]" id="mec_repeat_ends_date" />
                                    <label for="mec_repeat_ends_date"><?php _e('On', 'modern-events-calendar-lite'); ?></label>
                                </div>
                                <input class="mec-col-2" type="text" name="mec[date][repeat][end_at_date]" id="mec_date_repeat_end_at_date" autocomplete="off" value="<?php echo esc_attr($repeat_end_at_date); ?>" />
                            </div>
                            <div class="mec-form-row">
                                <div class="mec-col-3">
                                    <input <?php if($mec_repeat_end == 'occurrences') echo 'checked="checked"'; ?> type="radio" value="occurrences" name="mec[date][repeat][end]" id="mec_repeat_ends_occurrences" />
                                    <label for="mec_repeat_ends_occurrences"><?php _e('After', 'modern-events-calendar-lite'); ?></label>
                                </div>
                                <input class="mec-col-2" type="text" name="mec[date][repeat][end_at_occurrences]" id="mec_date_repeat_end_at_occurrences" autocomplete="off" placeholder="<?php _e('Occurrences times', 'modern-events-calendar-lite'); ?>"  value="<?php echo esc_attr(($repeat_end_at_occurrences+1)); ?>" />
                                <span class="mec-tooltip">
                                    <div class="box">
                                        <h5 class="title"><?php _e('Occurrences times', 'modern-events-calendar-lite'); ?></h5>
                                        <div class="content"><p><?php esc_attr_e('The event will finish after certain repeats. For example if you set it to 10, the event will finish after 10 repeats.', 'modern-events-calendar-lite'); ?><a href="https://webnus.net/dox/modern-events-calendar/event-detailssingle-event-page/" target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>    
                                    </div>
                                    <i title="" class="dashicons-before dashicons-editor-help"></i>
                                </span>	                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php do_action('mec_fes_metabox_details', $post); ?>
            
            <?php /* Note feature is enabled */ if($this->main->is_note_visible(get_post_status($post_id))): $note = get_post_meta($post_id, 'mec_note', true); ?>
            <div class="mec-meta-box-fields" id="mec-event-note">
                <h4><?php _e('Note to reviewer', 'modern-events-calendar-lite'); ?></h4>
                <div id="mec_meta_box_event_note">
                    <textarea name="mec[note]"><?php echo $note; ?></textarea>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
        
        <div class="mec-fes-form-sdbr">
            
            <!-- Guest Email and Name -->
            <?php if(!is_user_logged_in() and isset($this->settings['fes_guest_name_email']) and $this->settings['fes_guest_name_email']): ?>
            <?php
                $guest_email = get_post_meta($post_id, 'fes_guest_email', true);
                $guest_name = get_post_meta($post_id, 'fes_guest_name', true);
            ?>
            <div class="mec-meta-box-fields" id="mec-guest-email-link">
                <h4><?php _e('User Data', 'modern-events-calendar-lite'); ?></h4>
                <div class="mec-form-row">
                    <label class="mec-col-2" for="mec_guest_email"><?php _e('Email', 'modern-events-calendar-lite'); ?></label>
                    <input class="mec-col-7" type="email" required="required" name="mec[fes_guest_email]" id="mec_guest_email" value="<?php echo esc_attr($guest_email); ?>" placeholder="<?php _e('eg. yourname@gmail.com', 'modern-events-calendar-lite'); ?>" />
                </div>
                <div class="mec-form-row">
                    <label class="mec-col-2" for="mec_guest_name"><?php _e('Name', 'modern-events-calendar-lite'); ?></label>
                    <input class="mec-col-7" type="text" required="required" name="mec[fes_guest_name]" id="mec_guest_name" value="<?php echo esc_attr($guest_name); ?>" placeholder="<?php _e('eg. John Smith', 'modern-events-calendar-lite'); ?>" />
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Event Links Section -->
            <?php if(!isset($this->settings['fes_section_event_links']) or (isset($this->settings['fes_section_event_links']) and $this->settings['fes_section_event_links'])): ?>
            <?php
                $read_more = get_post_meta($post_id, 'mec_read_more', true);
                $more_info = get_post_meta($post_id, 'mec_more_info', true);
                $more_info_title = get_post_meta($post_id, 'mec_more_info_title', true);
                $more_info_target = get_post_meta($post_id, 'mec_more_info_target', true);
            ?>
            <div class="mec-meta-box-fields" id="mec-event-links">
                <h4><?php _e('Event Links', 'modern-events-calendar-lite'); ?></h4>
                <div class="mec-form-row">
                    <label class="mec-col-2" for="mec_read_more_link"><?php echo $this->main->m('read_more_link', __('Event Link', 'modern-events-calendar-lite')); ?></label>
                    <input class="mec-col-7" type="text" name="mec[read_more]" id="mec_read_more_link" value="<?php echo esc_attr($read_more); ?>" placeholder="<?php _e('eg. http://yoursite.com/your-event', 'modern-events-calendar-lite'); ?>" />
                    <p class="description"><?php _e('If you fill it, it will be replaced instead of default event page link. Insert full link including http(s)://', 'modern-events-calendar-lite'); ?></p>
                </div>
                <div class="mec-form-row">
                    <label class="mec-col-2" for="mec_more_info_link"><?php echo $this->main->m('more_info_link', __('More Info', 'modern-events-calendar-lite')); ?></label>
                    <input class="mec-col-5" type="text" name="mec[more_info]" id="mec_more_info_link" value="<?php echo esc_attr($more_info); ?>" placeholder="<?php _e('eg. http://yoursite.com/your-event', 'modern-events-calendar-lite'); ?>" />
                    <input class="mec-col-2" type="text" name="mec[more_info_title]" id="mec_more_info_title" value="<?php echo esc_attr($more_info_title); ?>" placeholder="<?php _e('More Information', 'modern-events-calendar-lite'); ?>" />
                    <select class="mec-col-2" name="mec[more_info_target]" id="mec_more_info_target">
                        <option value="_self" <?php echo ($more_info_target == '_self' ? 'selected="selected"' : ''); ?>><?php _e('Current Window', 'modern-events-calendar-lite'); ?></option>
                        <option value="_blank" <?php echo ($more_info_target == '_blank' ? 'selected="selected"' : ''); ?>><?php _e('New Window', 'modern-events-calendar-lite'); ?></option>
                    </select>
                    <p class="description"><?php _e('If you fill it, it will be shown in event details page as an optional link. Insert full link including http(s)://', 'modern-events-calendar-lite'); ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Event Cost Section -->
            <?php if(!isset($this->settings['fes_section_cost']) or (isset($this->settings['fes_section_cost']) and $this->settings['fes_section_cost'])): ?>
            <?php $cost = get_post_meta($post_id, 'mec_cost', true); ?>
            <div class="mec-meta-box-fields" id="mec-event-cost">
                <h4><?php echo $this->main->m('event_cost', __('Event Cost', 'modern-events-calendar-lite')); ?></h4>
                <div id="mec_meta_box_cost_form">
                    <div class="mec-form-row">
                        <input type="text" class="mec-col-6" name="mec[cost]" id="mec_cost" value="<?php echo esc_attr($cost); ?>" placeholder="<?php _e('Cost', 'modern-events-calendar-lite'); ?>" />
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Event Featured Image Section -->
            <?php if(!isset($this->settings['fes_section_featured_image']) or (isset($this->settings['fes_section_featured_image']) and $this->settings['fes_section_featured_image'])): ?>
            <?php
                $attachment_id = get_post_thumbnail_id($post_id);
                $featured_image = wp_get_attachment_image_src($attachment_id, 'large');
                if(isset($featured_image[0])) $featured_image = $featured_image[0];
            ?>
            <div class="mec-meta-box-fields" id="mec-featured-image">
                <h4><?php _e('Featured Image', 'modern-events-calendar-lite'); ?></h4>
                <div class="mec-form-row">
                    <span id="mec_fes_thumbnail_img"><?php echo (trim($featured_image) ? '<img src="'.$featured_image.'" />' : ''); ?></span>
                    <input type="hidden" id="mec_fes_thumbnail" name="mec[featured_image]" value="<?php if(isset($attachment_id) and intval($attachment_id)) the_guid($attachment_id); ?>" />
                    <input type="file" id="mec_featured_image_file" onchange="mec_fes_upload_featured_image();" />
                    <span id="mec_fes_remove_image_button" class="<?php echo (trim($featured_image) ? '' : 'mec-util-hidden'); ?>"><?php _e('Remove Image', 'modern-events-calendar-lite'); ?></span>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Event Category Section -->
            <?php if(!isset($this->settings['fes_section_categories']) or (isset($this->settings['fes_section_categories']) and $this->settings['fes_section_categories'])): ?>
            <div class="mec-meta-box-fields" id="mec-categories">
                <h4><?php echo $this->main->m('taxonomy_categories', __('Categories', 'modern-events-calendar-lite')); ?></h4>
                <div class="mec-form-row">
                    <?php 
                        wp_list_categories(array(
                            'taxonomy'    => 'mec_category',
                            'hide_empty' => false,
                            'title_li'           => '',
                            'walker'          => new FES_Custom_Walker($post_id),
                        ));
                    ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Event Label Section -->
            <?php if(!isset($this->settings['fes_section_labels']) or (isset($this->settings['fes_section_labels']) and $this->settings['fes_section_labels'])): ?>
            <?php
                $post_labels = get_the_terms($post_id, 'mec_label');

                $labels = array();
                if($post_labels) foreach($post_labels as $post_label) $labels[] = $post_label->term_id;
                
                $label_terms = get_terms(array('taxonomy'=>'mec_label', 'hide_empty'=>false));
            ?>
            <?php if(count($label_terms)): ?>
            <div class="mec-meta-box-fields" id="mec-labels">
                <h4><?php echo $this->main->m('taxonomy_labels', __('Labels', 'modern-events-calendar-lite')); ?></h4>
                <div class="mec-form-row">
                    <?php foreach($label_terms as $label_term): ?>
                    <label for="mec_fes_labels<?php echo $label_term->term_id; ?>">
                        <input type="checkbox" name="mec[labels][<?php echo $label_term->term_id; ?>]" id="mec_fes_labels<?php echo $label_term->term_id; ?>" value="1" <?php echo (in_array($label_term->term_id, $labels) ? 'checked="checked"' : ''); ?> />
                        <?php do_action('mec_label_to_checkbox_frontend', $label_term, $labels ) ?>
                        <?php echo $label_term->name; ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
            
            <!-- Event Color Section -->
            <?php if(!isset($this->settings['fes_section_event_color']) or (isset($this->settings['fes_section_event_color']) and $this->settings['fes_section_event_color'])): ?>
            <?php
                $color = get_post_meta($post_id, 'mec_color', true);
                $available_colors = $this->main->get_available_colors();

                if(!trim($color)) $color = $available_colors[0];
            ?>
            <?php if(count($available_colors)): ?>
            <div class="mec-meta-box-fields" id="mec-event-color">
                <h4><?php _e('Event Color', 'modern-events-calendar-lite'); ?></h4>
                <div class="mec-form-row">
                    <div class="mec-form-row mec-available-color-row">
                        <input type="hidden" id="mec_event_color" name="mec[color]" value="#<?php echo $color; ?>" />
                        <?php foreach($available_colors as $available_color): ?>
                        <span class="mec-color <?php echo ($available_color == $color ? 'color-selected' : ''); ?>" onclick="mec_set_event_color('<?php echo $available_color; ?>');" style="background-color: #<?php echo $available_color; ?>"></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
            
            <!-- Event Tags Section -->
            <?php if(!isset($this->settings['fes_section_tags']) or (isset($this->settings['fes_section_tags']) and $this->settings['fes_section_tags'])): ?>
            <?php
                $post_tags = wp_get_post_tags($post_id);

                $tags = '';
                foreach($post_tags as $post_tag) $tags .= $post_tag->name.',';
            ?>
            <div class="mec-meta-box-fields" id="mec-tags">
                <h4><?php _e('Tags', 'modern-events-calendar-lite'); ?></h4>
                <div class="mec-form-row">
                    <textarea name="mec[tags]" id="mec_fes_tags" placeholder="<?php esc_attr_e('Insert your desired tags, comma separated.', 'modern-events-calendar-lite'); ?>"><?php echo (trim($tags) ? trim($tags, ', ') : ''); ?></textarea>
                </div>
            </div>
            <?php endif; ?>

            <!-- Event Speakers Section -->
            <?php if((isset($this->settings['speakers_status']) and $this->settings['speakers_status']) and isset($this->settings['fes_section_speaker']) and $this->settings['fes_section_speaker']): ?>
                <?php
                $post_speakers = get_the_terms($post_id, 'mec_speaker');

                $speakers = array();
                if($post_speakers) foreach($post_speakers as $post_speaker)
                {
                    if(!isset($post_speaker->term_id)) continue;
                    $speakers[] = $post_speaker->term_id;
                }

                $speaker_terms = get_terms(array('taxonomy'=>'mec_speaker', 'hide_empty'=>false));
                ?>
                    <div class="mec-meta-box-fields" id="mec-speakers">
                        <h4><?php echo $this->main->m('taxonomy_speakers', __('Speakers', 'modern-events-calendar-lite')); ?></h4>
                        <div class="mec-form-row">
                            <input type="text" name="mec[speakers][datas][names]" id="mec_speaker_input_names" placeholder="<?php _e('Speakers Names', 'modern-events-calendar-lite'); ?>" class="" />
                            <p><?php _e('Separate names with commas Similar Justin, Cris', 'modern-events-calendar-lite'); ?></p>
                            <button class="button" type="button" id="mec_add_speaker_button"><?php _e('Add', 'modern-events-calendar-lite'); ?></button>
                        </div>
                        <div class="mec-form-row" id="mec-fes-speakers-list">
                        <?php if(count($speaker_terms)): ?>
                            <?php foreach($speaker_terms as $speaker_term): ?>
                                <label for="mec_fes_speakers<?php echo $speaker_term->term_id; ?>">
                                    <input type="checkbox" name="mec[speakers][<?php echo $speaker_term->term_id; ?>]" id="mec_fes_speakers<?php echo $speaker_term->term_id; ?>" value="1" <?php echo (in_array($speaker_term->term_id, $speakers) ? 'checked="checked"' : ''); ?> />
                                    <?php echo $speaker_term->name; ?>
                                </label>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
            <?php endif; ?>
        </div>
        <div class="mec-form-row mec-fes-submit-wide">
            <?php if($this->main->get_recaptcha_status('fes')): ?><div class="mec-form-row mec-google-recaptcha"><div class="g-recaptcha" data-sitekey="<?php echo $this->settings['google_recaptcha_sitekey']; ?>"></div></div><?php endif; ?>
            <button class="mec-fes-sub-button" type="submit"><?php _e('Submit', 'modern-events-calendar-lite'); ?></button>
            <div class="mec-util-hidden">
                <input type="hidden" name="mec[post_id]" value="<?php echo $post_id; ?>" id="mec_fes_post_id" class="mec-fes-post-id" />
                <input type="hidden" name="action" value="mec_fes_form" />
                <?php wp_nonce_field('mec_fes_form'); ?>
            </div>
        </div>
    </form>
</div>