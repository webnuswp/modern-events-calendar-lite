<?php
/** no direct access **/
defined('MECEXEC') or die();

$events = $this->main->get_events('-1');

// Settings
$settings = $this->main->get_settings();
?>
<div class="wrap" id="mec-wrap">
    <h1><?php esc_html_e('MEC Import / Export', 'modern-events-calendar-lite'); ?></h1>
    <h2 class="nav-tab-wrapper">
        <a href="<?php echo esc_url($this->main->remove_qs_var('tab')); ?>" class="nav-tab"><?php echo esc_html__('Google Cal. Import', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-g-calendar-export')); ?>" class="nav-tab"><?php echo esc_html__('Google Cal. Export', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-f-calendar-import')); ?>" class="nav-tab"><?php echo esc_html__('Facebook Cal. Import', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-meetup-import')); ?>" class="nav-tab"><?php echo esc_html__('Meetup Import', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-sync')); ?>" class="nav-tab"><?php echo esc_html__('Synchronization', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-export')); ?>" class="nav-tab nav-tab-active"><?php echo esc_html__('Export', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-import')); ?>" class="nav-tab"><?php echo esc_html__('Import', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-thirdparty')); ?>" class="nav-tab"><?php echo esc_html__('Third Party Plugins', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-test-data')); ?>" class="nav-tab"><?php echo esc_html__('Test Data', 'modern-events-calendar-lite'); ?></a>
    </h2>
    <div class="mec-container">
        <div class="export-content w-clearfix extra">
            <div class="mec-export-all-events">
                <h3><?php esc_html_e('Export all events to file', 'modern-events-calendar-lite'); ?></h3>
                <p class="description"><?php esc_html_e("This will export all of your website events' data into your desired format.", 'modern-events-calendar-lite'); ?></p>
                <ul>
                    <li><a href="<?php echo esc_url($this->main->add_qs_vars(array('mec-ix-action'=>'export-events', 'format'=>'ical'))); ?>"><?php esc_html_e('iCal', 'modern-events-calendar-lite'); ?></a></li>
                    <li><a href="<?php echo esc_url($this->main->add_qs_vars(array('mec-ix-action'=>'export-events', 'format'=>'csv'))); ?>"><?php esc_html_e('CSV', 'modern-events-calendar-lite'); ?></a></li>
                    <li><a href="<?php echo esc_url($this->main->add_qs_vars(array('mec-ix-action'=>'export-events', 'format'=>'g-cal-csv'))); ?>"><?php esc_html_e('Google Calendar CSV', 'modern-events-calendar-lite'); ?></a></li>
                    <li><a href="<?php echo esc_url($this->main->add_qs_vars(array('mec-ix-action'=>'export-events', 'format'=>'ms-excel'))); ?>"><?php esc_html_e('MS Excel', 'modern-events-calendar-lite'); ?></a></li>
                    <li><a href="<?php echo esc_url($this->main->add_qs_vars(array('mec-ix-action'=>'export-events', 'format'=>'xml'))); ?>"><?php esc_html_e('XML', 'modern-events-calendar-lite'); ?></a></li>
                    <li><a href="<?php echo esc_url($this->main->add_qs_vars(array('mec-ix-action'=>'export-events', 'format'=>'json'))); ?>"><?php esc_html_e('JSON', 'modern-events-calendar-lite'); ?></a></li>
                </ul>
            </div>
            <div class="mec-export-certain-events">
                <h3><?php esc_html_e('Export certain events', 'modern-events-calendar-lite'); ?></h3>
                <p class="description"><?php echo sprintf(esc_html__("For exporting filtered events, you can use bulk actions in %s page.", 'modern-events-calendar-lite'), '<a href="'.esc_url($this->main->URL('backend')).'edit.php?post_type=mec-events">'.esc_html__('Events', 'modern-events-calendar-lite').'</a>'); ?></p>
            </div>

            <?php if(isset($settings['ical_feed']) and $settings['ical_feed']): ?>
            <div class="mec-export-certain-events">
                <h3><?php esc_html_e('iCal Feed', 'modern-events-calendar-lite'); ?></h3>
                <p class="description"><?php echo sprintf(esc_html__('You can use %s URL to export all events. Also you can include the URL into your website so your website users can subscribe to events.', 'modern-events-calendar-lite'), '<a href="'.trim($this->main->URL('site'), '/ ').'/?mec-ical-feed=1" target="_blank">'.trim($this->main->URL('site'), '/ ').'/?mec-ical-feed=1</a>'); ?></p>
            </div>
            <?php endif; ?>

            <hr>
            <div class="mec-export-all-bookings">
                <h3><?php esc_html_e('Export all bookings to file', 'modern-events-calendar-lite'); ?></h3>
                <p class="description"><?php esc_html_e("This will export all of your website bookings' data into your desired format.", 'modern-events-calendar-lite'); ?></p>
                <ul>
                    <li><a href="<?php echo esc_url($this->main->add_qs_vars(array('mec-ix-action'=>'export-bookings', 'format'=>'csv'))); ?>"><?php esc_html_e('CSV', 'modern-events-calendar-lite'); ?></a></li>
                    <li><a href="<?php echo esc_url($this->main->add_qs_vars(array('mec-ix-action'=>'export-bookings', 'format'=>'ms-excel'))); ?>"><?php esc_html_e('MS Excel', 'modern-events-calendar-lite'); ?></a></li>
                </ul>
            </div>
            <div class="mec-export-certain-bookings">
                <h3><?php esc_html_e('Export certain bookings', 'modern-events-calendar-lite'); ?></h3>
                <p class="description"><?php echo sprintf(esc_html__("For exporting bookings, you can use bulk actions in %s page.", 'modern-events-calendar-lite'), '<a href="'.esc_url($this->main->URL('backend')).'edit.php?post_type=mec-books">'.esc_html__('Bookings', 'modern-events-calendar-lite').'</a>'); ?></p>
            </div>

            <?php
                $tab = isset($_GET['tab']) ? sanitize_text_field( $_GET['tab'] ) : '';
                do_action( 'mec_import_export_page', $tab );
            ?>
        </div>
    </div>
</div>