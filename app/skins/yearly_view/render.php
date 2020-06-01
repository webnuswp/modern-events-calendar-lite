<?php
/** no direct access **/
defined('MECEXEC') or die();

$months_html = '';
$calendar_type = 'calendar';
$count = 1;
for($i = 1; $i <= 12; $i++)
{
    $months_html .= $this->draw_monthly_calendar($this->year, $i, $this->events, $calendar_type);
}

$settings = $this->main->get_settings();
$this->localtime = isset($this->skin_options['include_local_time']) ? $this->skin_options['include_local_time'] : false;
$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;

?>
<div class="mec-yearly-calendar-sec">
    <?php echo $months_html ?>
</div>
<div class="mec-yearly-agenda-sec">

    <?php foreach($this->events as $date=>$events): 

    $limitation_class = ( $count > 20 ) ? 'mec-events-agenda mec-util-hidden' : 'mec-events-agenda' ;
    ?>
    <div class="<?php echo $limitation_class; ?>">

        <div class="mec-agenda-date-wrap" id="mec_yearly_view<?php echo $this->id; ?>_<?php echo date('Ymd', strtotime($date)); ?>">
            <i class="mec-sl-calendar"></i>
            <span class="mec-agenda-day"><?php echo $this->main->date_i18n($this->date_format_modern_1, strtotime($date)); ?></span>
            <span class="mec-agenda-date"><?php echo $this->main->date_i18n($this->date_format_modern_2, strtotime($date)); ?></span>
        </div>

        <div class="mec-agenda-events-wrap">
            <?php
            foreach($events as $event)
            {
                $count++;
                $organizer = isset($event->data->organizers[$event->data->meta['mec_organizer_id']]) ? $event->data->organizers[$event->data->meta['mec_organizer_id']] : array();
                $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
                $event_color = isset($event->data->meta['mec_color']) ? '<span class="event-color" style="background: #'.$event->data->meta['mec_color'].'"></span>' : '';

                $label_style = '';
                if(!empty($event->data->labels))
                {
                    foreach($event->data->labels as $label)
                    {
                        if(!isset($label['style']) or (isset($label['style']) and !trim($label['style']))) continue;
                        if($label['style'] == 'mec-label-featured') $label_style = esc_html__('Featured', 'modern-events-calendar-lite');
                        elseif($label['style'] == 'mec-label-canceled') $label_style = esc_html__('Canceled', 'modern-events-calendar-lite');
                    }
                }

                // MEC Schema
                do_action('mec_schema', $event);
                ?>
                <?php if($this->style == 'modern'): ?>
                    <div data-style="" class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-agenda-event <?php echo $this->get_event_classes($event); ?>">
                        <i class="mec-sl-clock "></i>
                        <span class="mec-agenda-time">
                            <?php
                            if(trim($start_time))
                            {
                                echo '<span class="mec-start-time">'.$start_time.'</span>';
                                if(trim($end_time)) echo ' - <span class="mec-end-time">'.$end_time.'</span>';
                            }
                            ?>
                        </span>
                        <span class="mec-agenda-event-title">
                            <a class="mec-color-hover" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a>
                            <?php echo $event_color; ?>
                            <?php if ( !empty($label_style) ) echo '<span class="mec-fc-style">'.$label_style.'</span>'; echo $this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event->data->ID, $reason_for_cancellation); ?>
                            <?php if($this->localtime) echo $this->main->module('local-time.type2', array('event'=>$event)); ?>
                        </span>
                    </div>
                <?php endif; ?>
            <?php } ?>
        </div>
    </div>
    <?php endforeach; ?>
    <span class="mec-yearly-max" data-count="<?php echo $count; ?>" ></span>
    <?php if ($count > 20): ?>
        <div class="mec-load-more-wrap"><div class="mec-load-more-button" onclick=""><?php echo __('Load More', 'modern-events-calendar-lite'); ?></div></div>
    <?php endif; ?>
</div>
