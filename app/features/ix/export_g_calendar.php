<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_feature_ix $this */

$events = $this->main->get_events('-1', array('publish', 'pending', 'future', 'draft', 'private'));
$ix_options = $this->main->get_ix_options();

// Start the export process if token is exists
if(isset($ix_options['google_export_token']) && $ix_options['google_export_token']) $this->action = 'google-calendar-export-start';
?>
<div class="wrap" id="mec-wrap">
    <h1><?php esc_html_e('MEC Import / Export', 'modern-events-calendar-lite'); ?></h1>
    <h2 class="nav-tab-wrapper">
        <a href="<?php echo esc_url($this->main->remove_qs_var('tab')); ?>" class="nav-tab"><?php echo esc_html__('Google Cal. Import', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-g-calendar-export')); ?>" class="nav-tab nav-tab-active"><?php echo esc_html__('Google Cal. Export', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-f-calendar-import')); ?>" class="nav-tab"><?php echo esc_html__('Facebook Cal. Import', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-meetup-import')); ?>" class="nav-tab"><?php echo esc_html__('Meetup Import', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-sync')); ?>" class="nav-tab"><?php echo esc_html__('Synchronization', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-export')); ?>" class="nav-tab"><?php echo esc_html__('Export', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-import')); ?>" class="nav-tab"><?php echo esc_html__('Import', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-thirdparty')); ?>" class="nav-tab"><?php echo esc_html__('Third Party Plugins', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-test-data')); ?>" class="nav-tab"><?php echo esc_html__('Test Data', 'modern-events-calendar-lite'); ?></a>
    </h2>
    <div class="mec-container">
        <div class="export-content w-clearfix extra">
            <div class="mec-export-events-g-calendar">
                <h3><?php esc_html_e('Add events to Google Calendar', 'modern-events-calendar-lite'); ?></h3>
                <p class="description"><?php esc_html_e("Add your desired website events to your Google Calendar.", 'modern-events-calendar-lite'); ?> <?php echo sprintf(esc_html__('You should set %s as redirect page in Google App Console.', 'modern-events-calendar-lite'), '<code>'.esc_url($this->main->add_qs_vars(array('mec-ix-action'=>'google-calendar-export-get-token'), $this->main->URL('backend').'admin.php?page=MEC-ix&tab=MEC-g-calendar-export')).'</code>'); ?></p>
                <form id="mec_g_calendar_export_form_authenticate" action="<?php echo esc_url($this->main->get_full_url()); ?>" method="POST">
                    <div class="mec-form-row">
                        <label class="mec-col-3" for="mec_ix_google_export_client_id"><?php esc_html_e('App Client ID', 'modern-events-calendar-lite'); ?></label>
                        <div class="mec-col-4">
                            <input type="text" id="mec_ix_google_export_client_id" name="ix[google_export_client_id]" value="<?php echo (isset($ix_options['google_export_client_id']) ? esc_attr($ix_options['google_export_client_id']) : ''); ?>" />
                        </div>
                    </div>
                    <div class="mec-form-row">
                        <label class="mec-col-3" for="mec_ix_google_export_client_secret"><?php esc_html_e('App Client Secret', 'modern-events-calendar-lite'); ?></label>
                        <div class="mec-col-4">
                            <input type="text" id="mec_ix_google_export_client_secret" name="ix[google_export_client_secret]" value="<?php echo (isset($ix_options['google_export_client_secret']) ? esc_attr($ix_options['google_export_client_secret']) : ''); ?>" />
                        </div>
                    </div>
                    <div class="mec-form-row">
                        <label class="mec-col-3" for="mec_ix_google_export_calendar_id"><?php esc_html_e('Calendar ID', 'modern-events-calendar-lite'); ?></label>
                        <div class="mec-col-4">
                            <input type="text" id="mec_ix_google_export_calendar_id" name="ix[google_export_calendar_id]" value="<?php echo (isset($ix_options['google_export_calendar_id']) ? esc_attr($ix_options['google_export_calendar_id']) : ''); ?>" />
                        </div>
                    </div>
                    <div class="mec-options-fields">
                        <button id="mec_ix_google_export_authenticate_form_button" class="button button-primary mec-button-primary" type="submit"><?php esc_html_e('Authenticate', 'modern-events-calendar-lite'); ?></button>
                    </div>
                    <p id="mec_ix_google_export_authenticate_message" class="mec-col-6 mec-util-hidden"></p>
                </form>
            </div>
            <?php if($this->action == 'google-calendar-export-start'): ?>
            <div>
                <form id="mec_g_calendar_export_form_do">
                    <ul class="mec-select-deselect-actions" data-for="#mec_export_g_calendar_events">
                        <li data-action="select-all"><?php esc_html_e('Select All', 'modern-events-calendar-lite'); ?></li>
                        <li data-action="deselect-all"><?php esc_html_e('Deselect All', 'modern-events-calendar-lite'); ?></li>
                        <li data-action="toggle"><?php esc_html_e('Toggle', 'modern-events-calendar-lite'); ?></li>
                    </ul>
                    <ul id="mec_export_g_calendar_events">
                        <?php foreach($events as $event): ?>
                        <li>
                            <label>
                                <input type="checkbox" name="mec-events[]" value="<?php echo esc_attr($event->ID); ?>" checked="checked" />
                                <strong><?php echo esc_html($event->post_title); ?></strong>
                            </label>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="mec-options-fields" style="padding-top: 0;">
                        <h4><?php esc_html_e('Import Options', 'modern-events-calendar-lite'); ?></h4>
                        <div class="mec-form-row">
                            <label>
                                <input type="checkbox" name="export_attendees" value="1" />
                                <?php esc_html_e('Export Attendees', 'modern-events-calendar-lite'); ?>
                            </label>
                        </div>
                        <button id="mec_ix_google_export_do_form_button" class="button button-primary mec-button-primary" type="submit"><?php esc_html_e('Add to Google Calendar', 'modern-events-calendar-lite'); ?></button>
                    </div>
                    <p id="mec_ix_google_export_do_message" class="mec-col-6 mec-util-hidden"></p>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $this->factory->params('footer', function()
{
    ?>
    <script>
    jQuery("#mec_g_calendar_export_form_authenticate").on('submit', function(e)
    {
        e.preventDefault();

        // Message
        var $message = jQuery('#mec_ix_google_export_authenticate_message');

        // Hide the Message
        $message.hide();

        // Add loading Class to the button
        jQuery("#mec_ix_google_export_authenticate_form_button").addClass('loading').text("<?php esc_attr_e('Checking ...', 'modern-events-calendar-lite'); ?>");

        var options = jQuery("#mec_g_calendar_export_form_authenticate").serialize();
        jQuery.ajax(
        {
            type: "POST",
            url: ajaxurl,
            data: "action=mec_ix_g_calendar_authenticate&"+options,
            dataType: "json",
            success: function(data)
            {
                // Remove the loading Class to the button
                setTimeout(function(){
                    jQuery("#mec_ix_google_export_authenticate_form_button").removeClass('loading').text("<?php esc_attr_e('Authenticate', 'modern-events-calendar-lite'); ?>");
                }, 1000);

                // Remove the classes
                $message.removeClass('mec-error').removeClass('mec-success');

                if(data.success) jQuery('#mec_ix_google_export_authenticate_message').addClass('mec-success');
                else jQuery('#mec_ix_google_export_authenticate_message').addClass('mec-error');

                $message.html(data.message).show();
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                // Remove the loading Class to the button
                setTimeout(function()
                {
                    jQuery("#mec_ix_google_export_authenticate_form_button").removeClass('loading').text("<?php esc_attr_e('Authenticate', 'modern-events-calendar-lite'); ?>");
                }, 1000);
            }
        });
    });

    jQuery("#mec_g_calendar_export_form_do").on('submit', function(e)
    {
        e.preventDefault();

        // Message
        var $message = jQuery('#mec_ix_google_export_do_message');

        // Hide the Message
        $message.hide();

        // Add loading Class to the button
        jQuery("#mec_ix_google_export_do_form_button").addClass('loading').text("<?php esc_attr_e('Exporting ...', 'modern-events-calendar-lite'); ?>");

        var options = jQuery("#mec_g_calendar_export_form_do").serialize();
        jQuery.ajax(
        {
            type: "POST",
            url: ajaxurl,
            data: "action=mec_ix_add_to_g_calendar&"+options,
            dataType: "json",
            success: function(data)
            {
                // Remove the loading Class to the button
                setTimeout(function()
                {
                    jQuery("#mec_ix_google_export_do_form_button").removeClass('loading').text("<?php esc_attr_e('Add to Google Calendar', 'modern-events-calendar-lite'); ?>");
                }, 1000);

                // Remove the classes
                $message.removeClass('mec-error').removeClass('mec-success');

                if(data.success) jQuery('#mec_ix_google_export_do_message').addClass('mec-success');
                else jQuery('#mec_ix_google_export_do_message').addClass('mec-error');

                $message.html(data.message).show();
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                // Remove the loading Class to the button
                setTimeout(function(){
                    jQuery("#mec_ix_google_export_do_form_button").removeClass('loading').text("<?php esc_attr_e('Add to Google Calendar', 'modern-events-calendar-lite'); ?>");
                }, 1000);
            }
        });
    });
    </script>
    <?php
});