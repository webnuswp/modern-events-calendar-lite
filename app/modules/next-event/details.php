<?php
/** no direct access **/
defined('MECEXEC') or die();

// MEC Settings
$settings = $this->get_settings();

// The module is disabled
if(!isset($settings['next_event_module_status']) or (isset($settings['next_event_module_status']) and !$settings['next_event_module_status'])) return;

// Date Format
$date_format1 = isset($settings['next_event_module_date_format1']) ? $settings['next_event_module_date_format1'] : 'M d Y';

// Next Event Method
$method = isset($settings['next_event_module_method']) ? $settings['next_event_module_method'] : 'occurrence';

$date = '';
if ( !empty($event->date) )
{
    $date = $event->date;
}

$start_date = (isset($date['start']) and isset($date['start']['date'])) ? $date['start']['date'] : date('Y-m-d');
if(isset($_GET['occurrence']) and trim($_GET['occurrence'])) $start_date = sanitize_text_field($_GET['occurrence']);

// Show next occurrence from other events
if($method == 'event')
{
    $start_hour = (isset($date['start']) and isset($date['start']['hour'])) ? $date['start']['hour'] : 8;
    $start_minutes = (isset($date['start']) and isset($date['start']['minutes'])) ? $date['start']['minutes'] : 0;
    $start_ampm = (isset($date['start']) and isset($date['start']['ampm'])) ? $date['start']['ampm'] : 'AM';

    $next = $this->get_next_event(array
    (
        'show_past_events'=>0,
        'sk-options'=>array
        (
            'list'=>array
            (
                'start_date_type'=>'date',
                'start_date'=>($method == 'occurrence' ? date('Y-m-d', strtotime('+1 Day', strtotime($start_date))) : $start_date),
                'limit'=>1,
            )
        ),
        'seconds_date'=>($method == 'occurrence' ? date('Y-m-d', strtotime('+1 Day', strtotime($start_date))) : $start_date),
        'seconds'=>$this->time_to_seconds($this->to_24hours($start_hour, $start_ampm), $start_minutes),
        'exclude'=>($method == 'event' ? array($event->ID) : NULL),
        'include'=>($method == 'occurrence' ? array($event->ID) : NULL),
    ));

    // Nothing Found!
    if(!isset($next->data)) return false;
}
else
{
    // Nothing Found!
    if(!isset($event->dates) or (isset($event->dates) and !is_array($event->dates)) or (isset($event->dates) and is_array($event->dates) and !count($event->dates))) return false;

    $next = $event;

    // Occurrences
    $found = false;
    foreach($event->dates as $occ)
    {
        if(strtotime($occ['start']['date']) > strtotime($start_date))
        {
            $found = true;
            $next->date = $occ;
            break;
        }
    }

    // Nothing Found!
    if(!$found) return false;
}

$time_comment = isset($next->data->meta['mec_comment']) ? $next->data->meta['mec_comment'] : '';
$allday = isset($next->data->meta['mec_allday']) ? $next->data->meta['mec_allday'] : 0;
?>
<div class="mec-next-event-details mec-frontbox" id="mec_next_event_details">
    <div class="mec-next-<?php echo $method; ?>">
        <h3 class="mec-frontbox-title"><?php echo ($method == 'occurrence' ? __('Next Occurrence', 'modern-events-calendar-lite') : __('Next Event', 'modern-events-calendar-lite')); ?></h3>
        
        <ul>
            <li>
                <a href="<?php echo $this->get_event_date_permalink($next->data->permalink, $next->date['start']['date'], true); ?>"><?php echo ($method == 'occurrence' ? __('Go to occurrence page', 'modern-events-calendar-lite') : $next->data->title); ?></a>
            </li>
            <li>
                <i class="mec-sl-calendar"></i>
                <h6><?php _e('Date', 'modern-events-calendar-lite'); ?></h6>
                <dd><abbr class="mec-events-abbr"><?php echo $this->date_label($next->date['start'], (isset($next->date['end']) ? $next->date['end'] : NULL), $date_format1); ?></abbr></dd>
            </li>
            <li>
                <i class="mec-sl-clock"></i>
                <h6><?php _e('Time', 'modern-events-calendar-lite'); ?></h6>
                <i class="mec-time-comment"><?php echo (isset($time_comment) ? $time_comment : ''); ?></i>
                
                <?php if($allday == '0' and isset($next->data->time) and trim($next->data->time['start'])): ?>
                <dd><abbr class="mec-events-abbr"><?php echo $next->data->time['start']; ?><?php echo (trim($next->data->time['end']) ? ' - '.$next->data->time['end'] : ''); ?></abbr></dd>
                <?php else: ?>
                <dd><abbr class="mec-events-abbr"><?php _e('All of the day', 'modern-events-calendar-lite'); ?></abbr></dd>
                <?php endif; ?>
            </li>
        </ul>
    </div>
</div>