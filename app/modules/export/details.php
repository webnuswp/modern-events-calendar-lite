<?php
/** no direct access **/
defined('MECEXEC') or die();

// MEC Settings
$settings = $this->get_settings();

// Export module on single page is disabled
if(!isset($settings['export_module_status']) or (isset($settings['export_module_status']) and !$settings['export_module_status'])) return;

$title = isset($event->data->title) ? $event->data->title : '';
$location = isset($event->data->locations[$event->data->meta['mec_location_id']]) ? $event->data->locations[$event->data->meta['mec_location_id']]['address'] : '';
$content = (isset($event->data->post->post_content) and trim($event->data->post->post_content)) ? strip_shortcodes(strip_tags($event->data->post->post_content)) : $title;

$occurrence = isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : '';
$occurrence_end_date = trim($occurrence) ? $this->get_end_date_by_occurrence($event->data->ID, (isset($event->date['start']['date']) ? $event->date['start']['date'] : $occurrence)) : '';

$start_time = strtotime((trim($occurrence) ? $occurrence : $event->date['start']['date']).' '.sprintf("%02d", $event->date['start']['hour']).':'.sprintf("%02d", $event->date['start']['minutes']).' '.$event->date['start']['ampm']);
$end_time = strtotime((trim($occurrence_end_date) ? $occurrence_end_date : $event->date['end']['date']).' '.sprintf("%02d", $event->date['end']['hour']).':'.sprintf("%02d", $event->date['end']['minutes']).' '.$event->date['end']['ampm']);

$gmt_offset_seconds = $this->get_gmt_offset_seconds($start_time);
?>
<div class="mec-event-export-module mec-frontbox">
     <div class="mec-event-exporting">
        <div class="mec-export-details">
            <ul>
                <?php if($settings['sn']['googlecal']): ?><li><a class="mec-events-gcal mec-events-button mec-color mec-bg-color-hover mec-border-color" href="https://www.google.com/calendar/event?action=TEMPLATE&text=<?php echo urlencode($title); ?>&dates=<?php echo gmdate('Ymd\\THi00\\Z', ($start_time - $gmt_offset_seconds)); ?>/<?php echo gmdate('Ymd\\THi00\\Z', ($end_time - $gmt_offset_seconds)); ?>&details=<?php echo urlencode($content); ?>&location=<?php echo urlencode($location); ?>" target="_blank"><?php echo __('+ Add to Google Calendar', 'modern-events-calendar-lite'); ?></a></li><?php endif; ?>
                <?php if($settings['sn']['ical']): ?><li><a class="mec-events-gcal mec-events-button mec-color mec-bg-color-hover mec-border-color" href="<?php echo $this->ical_URL($event->data->ID, $occurrence); ?>"><?php echo __('+ iCal export', 'modern-events-calendar-lite'); ?></a></li><?php endif; ?>
            </ul>
        </div>
    </div>
</div>