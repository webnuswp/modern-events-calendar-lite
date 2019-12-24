<?php
/** no direct access **/
defined('MECEXEC') or die();

// MEC Settings
$settings = $this->get_settings();

// Export module on single page is disabled
if(!isset($settings['export_module_status']) or (isset($settings['export_module_status']) and !$settings['export_module_status'])) return;

$title = isset($event->data->title) ? $event->data->title : '';
$location = (isset($event->data->meta['mec_location_id']) and isset($event->data->locations[$event->data->meta['mec_location_id']])) ? '&location='.urlencode($event->data->locations[$event->data->meta['mec_location_id']]['address']) : '';
$content = (isset($event->data->post->post_content) and trim($event->data->post->post_content)) ? strip_shortcodes(strip_tags($event->data->post->post_content)) : $title;

$occurrence = isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : '';
$occurrence_end_date = trim($occurrence) ? $this->get_end_date_by_occurrence($event->data->ID, (isset($event->date['start']['date']) ? $event->date['start']['date'] : $occurrence)) : '';

$start_date_temp = $start_hour_temp = '';
if(!empty($event->date))
{
    $start_date_temp = isset($event->date['start']['date']) ? $event->date['start']['date'] : NULL;
    $start_hour_temp = isset($event->date['start']['hour']) ? $event->date['start']['hour'] : NULL;
}

$start_minutes_temp = isset($event->date['start']['minutes']) ? $event->date['start']['minutes'] : NULL;
$start_ampm_temp = isset($event->date['start']['ampm']) ? $event->date['start']['ampm'] : NULL;

$end_date_temp = isset($event->date['end']['date']) ? $event->date['end']['date'] : NULL;
$end_hour_temp = isset($event->date['end']['hour']) ? $event->date['end']['hour'] : NULL;
$end_minutes_temp = isset($event->date['end']['minutes']) ? $event->date['end']['minutes'] : NULL;
$end_ampm_temp = isset($event->date['end']['ampm']) ? $event->date['end']['ampm'] : NULL;

if((is_null($start_date_temp) or is_null($start_hour_temp) or is_null($start_minutes_temp) or is_null($start_ampm_temp) or is_null($end_date_temp) or is_null($end_hour_temp) or is_null($end_minutes_temp) or is_null($end_ampm_temp)) and !trim($occurrence))
{
    return;
}

$start_time = strtotime((trim($occurrence) ? $occurrence : $start_date_temp).' '.sprintf("%02d", $start_hour_temp).':'.sprintf("%02d", $start_minutes_temp).' '.$start_ampm_temp);
$end_time = strtotime((trim($occurrence_end_date) ? $occurrence_end_date : $end_date_temp).' '.sprintf("%02d", $end_hour_temp).':'.sprintf("%02d", $end_minutes_temp).' '.$end_ampm_temp);
$gmt_offset_seconds = $this->get_gmt_offset_seconds($start_time);
?>
<div class="mec-event-export-module mec-frontbox">
     <div class="mec-event-exporting">
        <div class="mec-export-details">
            <ul>
                <?php if($settings['sn']['googlecal']): ?><li><a class="mec-events-gcal mec-events-button mec-color mec-bg-color-hover mec-border-color" href="https://www.google.com/calendar/event?action=TEMPLATE&text=<?php echo urlencode($title); ?>&dates=<?php echo gmdate('Ymd\\THi00\\Z', ($start_time - $gmt_offset_seconds)); ?>/<?php echo gmdate('Ymd\\THi00\\Z', ($end_time - $gmt_offset_seconds)); ?>&details=<?php echo urlencode($content).$location; ?>" target="_blank"><?php echo __('+ Add to Google Calendar', 'modern-events-calendar-lite'); ?></a></li><?php endif; ?>
                <?php if($settings['sn']['ical']): ?><li><a class="mec-events-gcal mec-events-button mec-color mec-bg-color-hover mec-border-color" href="<?php echo $this->ical_URL($event->data->ID); ?>"><?php echo __('+ iCal export', 'modern-events-calendar-lite'); ?></a></li><?php endif; ?>
            </ul>
        </div>
    </div>
</div>