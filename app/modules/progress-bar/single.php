<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_main $this */
/** @var MEC_factory $factory */
/** @var stdClass $event */

// MEC Settings
$settings = $this->get_settings();

// Module is not enabled
if(!isset($settings['progress_bar_status']) or !$settings['progress_bar_status']) return '';

$date = $event->date;

$start_datetime = isset($date['start'], $date['start']['timestamp']) ? $date['start']['timestamp'] : null;
$end_datetime = isset($date['end'], $date['end']['timestamp']) ? $date['end']['timestamp'] : null;

// Invalid Date & Time
if(!$start_datetime or !$end_datetime) return '';

$start = date('D M j Y G:i:s', $start_datetime);
$end = date('D M j Y G:i:s', $end_datetime);

// Timezone
$TZO = $this->get_TZO($event);

$d1 = new DateTime($start, $TZO);
$d2 = new DateTime('now', $TZO);
$d3 = new DateTime($end, $TZO);

// Event is Finished
if($d3 < $d2) return '';
// Event is not Started
elseif($d1 > $d2) return '';

$duration = $d3->getTimestamp() - $d1->getTimestamp();
$passed = $d2->getTimestamp() - $d1->getTimestamp();

// Generating javascript code of countdown default module
$js = '<script>
jQuery(document).ready(function()
{
    jQuery("#mec_progress_bar_single_'.esc_js($event->ID).'").mecProgressBar();
});
</script>';

if(!function_exists('is_plugin_active')) include_once ABSPATH . 'wp-admin/includes/plugin.php';

if($this->is_ajax() || is_plugin_active('mec-single-builder/mec-single-builder.php')) echo MEC_kses::full($js);
else $factory->params('footer', $js);
?>
<div class="mec-events-progress-bar" id="mec_progress_bar_single_<?php echo esc_attr($event->ID); ?>" data-event-id="<?php echo esc_attr($event->ID); ?>">
    <span class="mec-progress-bar-time-passed"></span>
    <style>.mec-events-progress-bar progress:after{left: <?php echo $passed/$duration*100; ?>%}</style>
    <progress value="<?php echo esc_attr($passed); ?>" max="<?php echo esc_attr($duration); ?>"></progress>
    <span class="mec-progress-bar-time-remained"></span>
</div>
