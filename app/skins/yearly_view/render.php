<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_skin_yearly_view $this */

$months_html = '';
$calendar_type = 'calendar';

$count = 1;
for($i = 1; $i <= 12; $i++)
{
    if(isset($this->months_to_display[$i]) and !$this->months_to_display[$i]) continue;

    $months_html .= $this->draw_monthly_calendar($this->year, $i, $this->events, $calendar_type);
}

$settings = $this->main->get_settings();
$this->localtime = isset($this->skin_options['include_local_time']) ? $this->skin_options['include_local_time'] : false;
$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;
?>
<div class="mec-yearly-calendar-sec">
    <?php echo MEC_kses::full($months_html); ?>
</div>
<div class="mec-yearly-agenda-sec">

    <?php foreach($this->events as $date=>$events): ?>
    <div class="<?php echo ($count > 20) ? 'mec-events-agenda mec-util-hidden' : 'mec-events-agenda'; ?>">

        <div class="mec-agenda-date-wrap" id="mec_yearly_view<?php echo esc_attr($this->id); ?>_<?php echo date('Ymd', strtotime($date)); ?>">
            <i class="mec-sl-calendar"></i>
            <span class="mec-agenda-day"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_1, strtotime($date))); ?></span>
            <span class="mec-agenda-date"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_2, strtotime($date))); ?></span>
        </div>

        <div class="mec-agenda-events-wrap">
            <?php
            foreach($events as $event)
            {
                $count++;

                $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
                $event_color = $this->get_event_color_dot($event);

                // MEC Schema
                do_action('mec_schema', $event);
                ?>
                <?php if($this->style == 'modern'): ?>
                    <div class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-agenda-event <?php echo esc_attr($this->get_event_classes($event)); ?>">
                        <i class="mec-sl-clock "></i>
                        <span class="mec-agenda-time">
                            <?php
                            if(trim($start_time))
                            {
                                echo '<span class="mec-start-time">'.esc_html($start_time).'</span>';
                                if(trim($end_time)) echo ' - <span class="mec-end-time">'.esc_html($end_time).'</span>';
                            }
                            ?>
                        </span>
                        <span class="mec-agenda-event-title">
                            <?php echo MEC_kses::element($this->display_link($event)); ?>
                            <?php echo MEC_kses::element($event_color); ?>
                            <?php echo MEC_kses::element($this->main->get_normal_labels($event, $display_label)).MEC_kses::element($this->main->display_cancellation_reason($event, $reason_for_cancellation)); ?><?php do_action('mec_shortcode_virtual_badge', $event->data->ID ); ?>
                            <?php echo MEC_kses::form($this->booking_button($event)); ?>
                            <?php if($this->localtime) echo MEC_kses::full($this->main->module('local-time.type2', array('event' => $event))); ?>
                        </span>
                        <?php echo MEC_kses::embed($this->display_custom_data($event)); ?>
                        <?php echo MEC_kses::element($this->get_label_captions($event, 'mec-fc-style')); ?>
                    </div>
                <?php endif; ?>
            <?php } ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <?php endforeach; ?>
    <span class="mec-yearly-max" data-count="<?php echo esc_attr($count); ?>"></span>

    <?php if($count > 20): ?>
    <div class="mec-load-more-wrap"><div tabindex="0" onkeydown="if(event.keyCode==13){jQuery(this).trigger('click');}" class="mec-load-more-button"><?php echo esc_html__('Load More', 'modern-events-calendar-lite'); ?></div></div>
    <?php endif; ?>
</div>
