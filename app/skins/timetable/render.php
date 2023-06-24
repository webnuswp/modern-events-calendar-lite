<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_skin_timetable $this */

$has_events = array();
$settings = $this->main->get_settings();
$styling = $this->main->get_styling();

$this->localtime = isset($this->skin_options['include_local_time']) ? $this->skin_options['include_local_time'] : false;
$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;

$dark_mode = (isset($styling['dark_mode']) ? $styling['dark_mode'] : '');
if($dark_mode == 1) $set_dark = 'mec-dark-mode';
else $set_dark = '';
?>
<?php if($this->style == 'modern'): ?>
<div class="mec-timetable-day-events mec-clear mec-weekly-view-dates-events <?php echo esc_attr($set_dark); ?>">
    <?php foreach($this->events as $date=>$events): if(strtotime($date) < strtotime($this->today)) continue; $week = (isset($this->week_of_days[$date]) ? $this->week_of_days[$date] : 0); ?>
    <?php
        if(!isset($has_events[$week]) and isset($this->weeks[$week]))
        {
            foreach($this->weeks[$week] as $weekday) if(isset($this->events[$weekday]) and count($this->events[$weekday])) $has_events[$week] = true;
        }
    ?>
    <?php if(count($events)): ?>
    <div class="mec-timetable-events-list <?php echo ($date == $this->active_date ? '' : 'mec-util-hidden'); ?> mec-weekly-view-date-events mec-calendar-day-events mec-clear mec-weekly-view-week-<?php echo esc_attr($this->id); ?>-<?php echo date('Ym', strtotime($date)).$week; ?> mec_weekly_view_date_events<?php echo esc_attr($this->id); ?>_<?php echo date('Ymd', strtotime($date)); ?>" id="mec_weekly_view_date_events<?php echo esc_attr($this->id); ?>_<?php echo date('Ymd', strtotime($date)); ?>" data-week-number="<?php echo esc_attr($week); ?>">
        <?php foreach($events as $event): ?>
            <?php
                $location_id = $this->main->get_master_location_id($event);
                $location = ($location_id ? $this->main->get_location_data($location_id) : array());

                $organizer_id = $this->main->get_master_organizer_id($event);
                $organizer = ($organizer_id ? $this->main->get_organizer_data($organizer_id) : array());

                $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
                $event_color = $this->get_event_color_dot($event);
                $event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';

                // MEC Schema
                do_action('mec_schema', $event);
            ?>
            <article class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-timetable-event mec-timetable-day-<?php echo esc_attr($this->id); ?>-<?php echo date('Ymd', strtotime($date)); ?> <?php echo esc_attr($this->get_event_classes($event)); ?>">
                <span class="mec-timetable-event-span mec-timetable-event-time">
                    <i class="mec-sl-clock"></i>
                    <?php if(trim($start_time)): ?>
                    <span><?php echo esc_html($start_time.(trim($end_time) ? ' - '.$end_time : '')); ?></span>
                    <?php endif; ?>
                </span>
                <span class="mec-timetable-event-span mec-timetable-event-title">
                    <?php echo MEC_kses::element($this->display_link($event)); ?><?php echo MEC_kses::element($this->main->get_flags($event).$event_color.$this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation)); ?>
                    <?php echo MEC_kses::embed($this->display_custom_data($event)); ?>
                    <?php echo MEC_kses::element($this->get_label_captions($event,'mec-fc-style')); ?>
                    <?php if($this->localtime) echo MEC_kses::full($this->main->module('local-time.type3', array('event' => $event))); ?>
                </span>

                <span class="mec-timetable-event-span mec-timetable-event-location">
                    <i class="mec-sl-location-pin"></i>
                    <?php if(isset($location['name']) and trim($location['name'])): ?>
                    <span><?php echo (isset($location['name']) ? esc_html($location['name']) : ''); ?></span>
                    <?php endif; ?>
                </span>
                <span class="mec-timetable-event-span mec-timetable-event-organizer">
                    <i class="mec-sl-user"></i>
                    <?php if(isset($organizer['name']) and trim($organizer['name'])): ?>
                    <span><?php echo (isset($organizer['name']) ? esc_html($organizer['name']) : ''); ?></span>
                    <?php endif; ?>
                </span>
            </article>
            <?php do_action('mec_timetable_view_content', $event, $this, $date); ?>
        <?php endforeach; ?>
    </div>

    <?php elseif(!isset($has_events[$week])): $has_events[$week] = 'printed'; ?>
    <div class="mec-timetable-events-list mec-weekly-view-date-events mec-util-hidden mec-calendar-day-events mec-clear mec-weekly-view-week-<?php echo esc_attr($this->id); ?>-<?php echo date('Ym', strtotime($date)).$week; ?> mec_weekly_view_date_events<?php echo esc_attr($this->id); ?>_<?php echo date('Ymd', strtotime($date)); ?>" id="mec_weekly_view_date_events<?php echo esc_attr($this->id); ?>_<?php echo date('Ymd', strtotime($date)); ?>" data-week-number="<?php echo esc_attr($week); ?>">
        <article class="mec-event-article"><h4 class="mec-event-title"><?php esc_html_e('No Events', 'modern-events-calendar-lite'); ?></h4><div class="mec-event-detail"></div></article>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>
</div>
<div class="mec-event-footer"></div>
<?php elseif($this->style == 'clean'): ?>
<div class="mec-timetable-t2-wrap <?php echo esc_attr($set_dark); ?>">
    <?php foreach($this->events as $date=>$events): ?>
    <div class="mec-timetable-t2-col mec-timetable-col-<?php echo esc_attr($this->number_of_days); ?>">
        <div class="mec-ttt2-title"> <?php echo esc_html($this->main->date_i18n('l', strtotime($date))); ?> </div>
        <?php foreach($events as $event): ?>
        <?php
            $location_id = $this->main->get_master_location_id($event);
            $location = ($location_id ? $this->main->get_location_data($location_id) : array());

            $organizer_id = $this->main->get_master_organizer_id($event);
            $organizer = ($organizer_id ? $this->main->get_organizer_data($organizer_id) : array());

            $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
            $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
            $event_color = $this->get_event_color_dot($event);
            $event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';

            $mec_data = $this->display_custom_data($event);
            $custom_data_class = !empty($mec_data) ? 'mec-custom-data' : '';
        ?>
        <article class="mec-event-article <?php echo esc_attr($this->get_event_classes($event)); ?> <?php echo esc_attr($custom_data_class); ?>">
            <?php echo MEC_kses::element($event_color); ?>
            <div class="mec-timetable-t2-content">
                <h4 class="mec-event-title">
                    <?php echo MEC_kses::element($this->display_link($event)); ?>
                    <?php echo MEC_kses::element($this->main->get_normal_labels($event, $display_label)); ?>
                    <?php echo MEC_kses::element($this->main->display_cancellation_reason($event, $reason_for_cancellation)); ?>
                    <?php echo MEC_kses::embed($this->display_custom_data($event)); ?>
                    <?php echo MEC_kses::element($this->main->get_flags($event)); ?>
                </h4>
                <?php echo MEC_kses::element($this->get_label_captions($event,'mec-fc-style')); ?>
                <div class="mec-event-time">
                    <i class="mec-sl-clock-o"></i>
                    <?php if(trim($start_time)): ?>
                    <span><?php echo esc_html($start_time.(trim($end_time) ? ' - '.$end_time : '')); ?></span>
                    <?php endif; ?>
                </div>
                <div class="mec-event-loction">
                    <i class="mec-sl-location-pin"></i>
                    <?php if(isset($location['name']) and trim($location['name'])): ?>
                        <span><?php echo (isset($location['name']) ? esc_html($location['name']) : ''); ?></span>
                    <?php endif; ?>
                </div>
                <div class="mec-event-organizer">
                    <i class="mec-sl-user"></i>
                    <?php if(isset($organizer['name']) and trim($organizer['name'])): ?>
                        <span><?php echo (isset($organizer['name']) ? esc_html($organizer['name']) : ''); ?></span>
                    <?php endif; ?>
                </div>
                <?php if($this->localtime) echo MEC_kses::full($this->main->module('local-time.type1', array('event' => $event))); ?>
                <?php echo MEC_kses::form($this->booking_button($event)); ?>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php elseif($this->style == 'classic'): ?>
<div class="mec-timetable-t3-wrap <?php echo esc_attr($set_dark); ?>">
    <table>
        <thead>
            <tr>
                <td><?php esc_html_e('Time/Date', 'modern-events-calendar-lite'); ?></td>
                <?php foreach($this->events as $date=>$events): ?>
                <td><?php echo esc_html($this->main->date_i18n('l', strtotime($date))); ?></td>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php for ($i=$this->start_time; $i <= $this->end_time; $i++): ?>
                <tr class="mec-timetable-row-wrap mec-timetable-row-<?php echo esc_attr($i); ?>" height="110">
                    <td style="vertical-align:middle;text-align: center;"><?php echo esc_html($i); ?>:00</td>
                    <?php foreach($this->events as $date=>$events): ?>
                        <?php if(!empty($events)): ?>
                        <td colspan="1" style="vertical-align:top;text-align: center;">
                            <?php foreach($events as $event): ?>
                                <?php if($event->data->meta['mec_date']['start']['hour'] == $i) echo MEC_kses::element($this->display_link($event, NULL, NULL, 'style="background: #'.esc_attr($event->data->meta['mec_color']).'"')); ?>
                                <?php echo MEC_kses::embed($this->display_custom_data($event)); ?>
                            <?php endforeach; ?>
                        </td>
                        <?php else: ?>
                        <td colspan="1" style="vertical-align:middle;text-align: center;"></td>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>