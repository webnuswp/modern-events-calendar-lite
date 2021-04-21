<?php
/** no direct access **/
defined('MECEXEC') or die();

$this->localtime = isset($this->skin_options['include_local_time']) ? $this->skin_options['include_local_time'] : false;
$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;
?>
<ul class="mec-daily-view-dates-events">
    <?php foreach($this->events as $date=>$events): ?>
    <li class="mec-daily-view-date-events mec-util-hidden" id="mec_daily_view_date_events<?php echo $this->id; ?>_<?php echo date('Ymd', strtotime($date)); ?>">
        <?php if(count($events)): ?>
        <?php foreach($events as $event): ?>
            <?php
                $location = isset($event->data->locations[$event->data->meta['mec_location_id']]) ? $event->data->locations[$event->data->meta['mec_location_id']] : array();
                $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
                $event_color =  isset($event->data->meta['mec_color']) ? '<span class="event-color" style="background: #'.$event->data->meta['mec_color'].'"></span>' : '';
                $event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';

                // MEC Schema
                do_action('mec_schema', $event);
            ?>
            <article class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-event-article <?php echo $this->get_event_classes($event); ?>">
                <div class="mec-event-image"><?php echo $event->data->thumbnails['thumbnail']; ?></div>
                <?php echo $this->get_label_captions($event); ?>
                <?php if(trim($start_time)): ?><div class="mec-event-time mec-color"><i class="mec-sl-clock-o"></i> <?php echo $start_time.(trim($end_time) ? ' - '.$end_time : ''); ?></div><?php endif; ?>
                <h4 class="mec-event-title"><?php echo $this->display_link($event); ?><?php echo $this->main->get_flags($event).$event_color.$this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation); ?><?php do_action('mec_shortcode_virtual_badge', $event->data->ID ); ?></h4>
                <?php if($this->localtime) echo $this->main->module('local-time.type3', array('event'=>$event)); ?>
                <div class="mec-event-detail"><div class="mec-event-loc-place"><?php echo (isset($location['name']) ? $location['name'] : ''); ?><div></div>
                <?php echo $this->display_categories($event); ?>
                <?php echo $this->display_organizers($event); ?>
                <?php if($this->display_price and isset($event->data->meta['mec_cost']) and $event->data->meta['mec_cost'] != ''): ?>
                    <div class="mec-price-details">
                        <i class="mec-sl-wallet"></i>
                        <span><?php echo (is_numeric($event->data->meta['mec_cost']) ? $this->main->render_price($event->data->meta['mec_cost'], $event->ID) : $event->data->meta['mec_cost']); ?></span>
                    </div>
                <?php endif; ?>
                <?php echo $this->booking_button($event); ?>
            </article>
        <?php endforeach; ?>
        <?php else: ?>
            <article class="mec-event-article"><div class="mec-daily-view-no-event mec-no-event"><?php _e('No event', 'modern-events-calendar-lite'); ?></div></article>
        <?php endif; ?>
    </li>
    <?php endforeach; ?>
</ul>
<div class="mec-event-footer"></div>