<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_main $this */

// MEC Settings
$settings = $this->get_settings();
$ml_settings = $this->get_ml_settings();

// The module is disabled
if(!isset($settings['local_time_module_status']) or (isset($settings['local_time_module_status']) and !$settings['local_time_module_status'])) return;

// Get the visitor Timezone
$timezone = $this->get_timezone_by_ip();

// Timezone is not detected!
if(!$timezone) return;

$start_time = isset($event->data->time['start_raw']) ? $event->data->time['start_raw'] : '';
$end_time = isset($event->data->time['end_raw']) ? $event->data->time['end_raw'] : '';

// Date Formats
$date_format1 = (isset($ml_settings['single_date_format1']) and trim($ml_settings['single_date_format1'])) ? $ml_settings['single_date_format1'] : 'M d Y';
$time_format = get_option('time_format', 'H:i');

$gmt_offset_seconds = $this->get_gmt_offset_seconds($event->date['start']['date'], $event);

$gmt_start_time = strtotime($event->date['start']['date'].' '.$start_time) - $gmt_offset_seconds;
$gmt_end_time = strtotime($event->date['end']['date'].' '.$end_time) - $gmt_offset_seconds;

$user_timezone = new DateTimeZone($timezone);
$gmt_timezone = new DateTimeZone('GMT');
$gmt_datetime = new DateTime(date('Y-m-d H:i:s', $gmt_start_time), $gmt_timezone);
$offset = $user_timezone->getOffset($gmt_datetime);

$user_start_time = $gmt_start_time + $offset;
$user_end_time = $gmt_end_time + $offset;

$allday = isset($event->data->meta['mec_allday']) ? $event->data->meta['mec_allday'] : 0;
$hide_time = isset($event->data->meta['mec_hide_time']) ? $event->data->meta['mec_hide_time'] : 0;
$hide_end_time = isset($event->data->meta['mec_hide_end_time']) ? $event->data->meta['mec_hide_end_time'] : 0;
?>
<div class="mec-local-time-details" id="mec_local_time_details">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="15.76" viewBox="0 0 16 15.76"><path id="local-time" d="M142.985,68.963a6.649,6.649,0,0,1,4.627,11.424l1.927,2.463a.69.69,0,0,1-1.088.85l-1.924-2.46a6.652,6.652,0,0,1-7.084,0l-1.924,2.46a.69.69,0,0,1-1.088-.85l1.927-2.463a6.649,6.649,0,0,1,4.627-11.424Zm1.12,6.574h2.967v1.212H144.1a1.274,1.274,0,1,1-1.72-1.73V71.382H143.6v3.644a1.279,1.279,0,0,1,.508.511Zm6.494-6.093-.849-.849a1.322,1.322,0,0,0-1.865,0L146.877,69.6a7.552,7.552,0,0,1,2.614,2.813l1.108-1.108A1.323,1.323,0,0,0,150.6,69.443Zm-15.231,0,.848-.849a1.323,1.323,0,0,1,1.865,0l1.009,1.009a7.555,7.555,0,0,0-2.615,2.813l-1.108-1.108A1.323,1.323,0,0,1,135.369,69.443Zm7.616.9a5.264,5.264,0,1,0,5.264,5.264A5.264,5.264,0,0,0,142.984,70.348Z" transform="translate(-134.984 -68.21)" fill="#60daf2"/></svg>
    <span class="mec-local-title"><?php esc_html_e('Local Time', 'modern-events-calendar-lite'); ?></span>
    <div class="mec-local-date"><?php echo sprintf(esc_html__('Date: %s', 'modern-events-calendar-lite'), $this->date_label(array('date'=>date('Y-m-d', $user_start_time)), array('date'=>date('Y-m-d', $user_end_time)), $date_format1)); ?></div>
    <?php if(!$hide_time and trim($time_format)): ?>
    <div class="mec-local-time"><?php echo sprintf(esc_html__('Time: %s', 'modern-events-calendar-lite'), '<span>'.($allday ? $this->m('all_day', esc_html__('All Day' , 'modern-events-calendar-lite')) : ($hide_end_time ? date($time_format, $user_start_time) : date($time_format, $user_start_time).' - '.date($time_format, $user_end_time))).'</span>'); ?></div>
    <?php endif; ?>
</div>