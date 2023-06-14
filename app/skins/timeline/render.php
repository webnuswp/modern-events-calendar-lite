<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_skin_timeline $this */

$current_month_divider = isset($_REQUEST['current_month_divider']) ? sanitize_text_field($_REQUEST['current_month_divider']) : 0;
$settings = $this->main->get_settings();
$styling = $this->main->get_styling();
$this->localtime  = isset($this->skin_options['include_local_time']) ? $this->skin_options['include_local_time'] : false;
$display_categories = isset($this->skin_options['display_categories']) ? $this->skin_options['display_categories'] : false;
$display_organizer  = isset($this->skin_options['display_organizer']) ? $this->skin_options['display_organizer'] : false;
$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;
$event_colorskin = (isset($styling['mec_colorskin']) || isset($styling['color'])) ? 'colorskin-custom' : '';
$sed_method = isset($this->skin_options['sed_method']) ? $this->skin_options['sed_method'] : '0';
?>
<div class="mec-events-timeline-wrap mec-wrap <?php echo esc_attr($event_colorskin); ?>">
<?php foreach($this->events as $date=>$events): ?>

    <?php $month_id = date('Ym', strtotime($date)); if($this->month_divider and $month_id != $current_month_divider): $current_month_divider = $month_id; ?>
        <div class="mec-timeline-month-divider"><span><?php echo esc_html($this->main->date_i18n('F Y', strtotime($date))); ?></span></div>
    <?php endif; ?>

    <div class="mec-timeline-events-container">
        <?php
            foreach($events as $event)
            {
                $location_id = $this->main->get_master_location_id($event);
                $location = ($location_id ? $this->main->get_location_data($location_id) : array());

                $organizer_id = $this->main->get_master_organizer_id($event);
                $organizer = ($organizer_id ? $this->main->get_organizer_data($organizer_id) : array());

                $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
                $event_color = $this->get_event_color_dot($event);
                $excerpt = trim($event->data->post->post_excerpt) ? $event->data->post->post_excerpt : '';
                $event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';

                // Safe Excerpt for UTF-8 Strings
                if(!trim($excerpt))
                {
                    $raw_text = strip_shortcodes($event->data->post->post_content);
                    $raw_text = preg_replace('/\[.*]/', '', $raw_text);
                    $raw_text = preg_replace('/\[\/.*]/', '', $raw_text);

                    $ex = explode(' ', strip_tags($raw_text));
                    $words = array_slice($ex, 0, 16);

                    $excerpt = implode(' ', $words);
                }

                // MEC Schema
                do_action('mec_schema', $event);
                ?>
                <div class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-timeline-event clearfix <?php echo esc_attr($this->get_event_classes($event)); ?>">
                    <div class="mec-timeline-event-date mec-color<?php echo ($event->date['start']['date'] != $event->date['end']['date']) ? ' mec-timeline-dates' : '' ; ?>"><?php echo ($event->date['start']['date'] == $event->date['end']['date']) ? esc_html($this->main->date_i18n($this->date_format_classic_1, strtotime($event->date['start']['date']))) : MEC_kses::element($this->main->date_i18n($this->date_format_classic_1, strtotime($event->date['start']['date'])) . '<br>' . $this->main->date_i18n($this->date_format_classic_1, strtotime($event->date['end']['date']))); ?> </div>
                    <div class="mec-timeline-event-content">
                        <div class="clearfix">
                            <div class="mec-timeline-right-content">
                                <div class="mec-timeline-event-image"><?php echo MEC_kses::element($this->display_link($event, $event->data->thumbnails['thumblist'], '')); ?></div>
                            </div>
                            <div class="mec-timeline-left-content">
                                <div class="mec-timeline-main-content">
                                    <?php $soldout = $this->main->get_flags($event); ?>
                                    <h4 class="mec-event-title"><?php echo MEC_kses::element($this->display_link($event)); ?><?php echo MEC_kses::embed($this->display_custom_data($event)); ?><?php echo MEC_kses::element($soldout.$event_color); ?><?php echo MEC_kses::element($this->get_label_captions($event,'mec-fc-style')); ?></h4>
                                    <?php echo MEC_kses::element($this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation)); ?><?php do_action('mec_shortcode_virtual_badge', $event->data->ID); ?>
                                    <p><?php echo MEC_kses::element($excerpt.(trim($excerpt) ? ' ...' : '')); ?></p>
                                    <?php if($display_categories): ?>
                                        <div class="mec-timeline-event-details">
                                            <div class="mec-timeline-event-categories mec-color">
                                               <?php echo MEC_kses::element($this->display_categories($event)); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if($display_organizer): ?>
                                        <div class="mec-timeline-event-details">
                                            <div class="mec-timeline-event-organizer mec-color">
                                                <?php echo MEC_kses::element($this->display_organizers($event)); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="mec-timeline-event-details">
                                        <div class="mec-timeline-event-time mec-color">
                                            <i class="mec-sl-clock"></i><?php echo MEC_kses::element($this->main->display_time($start_time, $end_time)); ?>
                                        </div>
                                    </div>
                                    <?php if(!empty($location['address'])): ?>
                                        <div class="mec-timeline-event-details">
                                            <div class="mec-timeline-event-location mec-color">
                                                <address class="mec-timeline-event-address"><i class="mec-sl-location-pin"></i><span><?php echo (isset($location['address']) ? esc_html($location['address']) : ''); ?></span></address>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                        <?php if($this->localtime): ?>
                                        <div class="mec-timeline-event-details">
                                            <div class="mec-timeline-event-local-time mec-color">
                                                <?php echo MEC_kses::full($this->main->module('local-time.type2', array('event' => $event))); ?>
                                            </div>
                                        </div>
                                <?php endif; ?>
                                <?php echo MEC_kses::form($this->booking_button($event)); ?>
                                </div>
                            </div>
                        </div>
                        <?php if($sed_method != 'no') echo MEC_kses::element($this->display_link($event, ((is_array($event->data->tickets) and count($event->data->tickets) and !strpos($soldout, '%%soldout%%') and !$this->booking_button and !$this->main->is_expired($event)) ? $this->main->m('register_button', esc_html__('Register for event', 'modern-events-calendar-lite')) : $this->main->m('view_detail', esc_html__('View Details', 'modern-events-calendar-lite'))).'<i class="mec-sl-arrow-right"></i>', 'mec-booking-button mec-timeline-readmore mec-bg-color')); ?>
                    </div>
                </div>

        <?php } ?>
    </div>

<?php endforeach; ?>
</div>