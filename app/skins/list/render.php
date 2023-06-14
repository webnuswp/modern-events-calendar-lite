<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_skin_list $this */

$styling = $this->main->get_styling();
$settings = $this->main->get_settings();
$current_month_divider = isset($_REQUEST['current_month_divider']) ? sanitize_text_field($_REQUEST['current_month_divider']) : 0;
$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;
$event_colorskin = (isset($styling['mec_colorskin']) || isset($styling['color'])) ? 'colorskin-custom' : '';
$map_events = array();
?>
<div class="mec-wrap <?php echo esc_attr($event_colorskin); ?>">
	<div class="mec-event-list-<?php echo esc_attr($this->style); ?>">
		<?php foreach($this->events as $date=>$events): ?>

            <?php $month_id = date('Ym', strtotime($date)); if($this->month_divider and $month_id != $current_month_divider): $current_month_divider = $month_id; ?>
            <div class="mec-month-divider" data-toggle-divider="mec-toggle-<?php echo date('Ym', strtotime($date)); ?>-<?php echo esc_attr($this->id); ?>"><span><?php echo esc_html($this->main->date_i18n('F Y', strtotime($date))); ?></span><i class="mec-sl-arrow-down"></i></div>
            <?php endif; ?>

            <?php
                foreach($events as $event)
                {
                    $map_events[] = $event;

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

                    // MEC Schema
                    do_action('mec_schema', $event);
            ?>
            <article class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-event-article <?php echo esc_attr($custom_data_class); ?> mec-clear <?php echo esc_attr($this->get_event_classes($event)); ?> mec-divider-toggle mec-toggle-<?php echo date('Ym', strtotime($date)); ?>-<?php echo esc_attr($this->id); ?>" itemscope>
                <?php if($this->style == 'modern'): ?>
                    <div class="col-md-2 col-sm-2">

                        <?php if($this->main->is_multipleday_occurrence($event, true)): ?>
                        <div class="mec-event-date">
                            <div class="event-d mec-color mec-multiple-dates">
                                <?php echo esc_html($this->main->date_i18n($this->date_format_modern_1, strtotime($event->date['start']['date']))); ?> -
                                <?php echo esc_html($this->main->date_i18n($this->date_format_modern_1, strtotime($event->date['end']['date']))); ?>
                            </div>
                            <div class="event-f"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_2, strtotime($event->date['start']['date']))); ?></div>
                            <div class="event-da"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_3, strtotime($event->date['start']['date']))); ?></div>
                        </div>
                        <?php elseif($this->main->is_multipleday_occurrence($event)): ?>
                        <div class="mec-event-date mec-multiple-date-event">
                            <div class="event-d mec-color"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_1, strtotime($event->date['start']['date']))); ?></div>
                            <div class="event-f"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_2, strtotime($event->date['start']['date']))); ?></div>
                            <div class="event-da"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_3, strtotime($event->date['start']['date']))); ?></div>
                        </div>
                        <div class="mec-event-date mec-multiple-date-event">
                            <div class="event-d mec-color"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_1, strtotime($event->date['end']['date']))); ?></div>
                            <div class="event-f"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_2, strtotime($event->date['end']['date']))); ?></div>
                            <div class="event-da"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_3, strtotime($event->date['end']['date']))); ?></div>
                        </div>
                        <?php else: ?>
                        <div class="mec-event-date">
                            <div class="event-d mec-color"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_1, strtotime($event->date['start']['date']))); ?></div>
                            <div class="event-f"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_2, strtotime($event->date['start']['date']))); ?></div>
                            <div class="event-da"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_3, strtotime($event->date['start']['date']))); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <?php do_action('list_std_title_hook', $event); ?>
                        <?php $soldout = $this->main->get_flags($event); ?>
                        <h4 class="mec-event-title"><?php echo MEC_kses::element($this->display_link($event)); ?><?php echo MEC_kses::element($soldout.$event_color); echo MEC_kses::element($this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation)); ?><?php echo MEC_kses::embed($this->display_custom_data($event)); ?><?php do_action('mec_shortcode_virtual_badge', $event->data->ID); ?><?php echo MEC_kses::element($this->get_label_captions($event,'mec-fc-style')); ?></h4>
                        <?php if($this->localtime) echo MEC_kses::full($this->main->module('local-time.type2', array('event' => $event))); ?>
                        <div class="mec-event-detail">
                            <div class="mec-event-loc-place"><?php echo (isset($location['name']) ? esc_html($location['name']) : '') . (isset($location['address']) && !empty($location['address']) ? ' | '.esc_html($location['address']) : ''); ?></div>
                            <?php if($this->include_events_times and trim($start_time)) echo MEC_kses::element($this->main->display_time($start_time, $end_time)); ?>
                            <?php echo MEC_kses::element($this->display_categories($event)); ?>
                            <?php echo MEC_kses::element($this->display_organizers($event)); ?>
                            <?php echo MEC_kses::element($this->display_cost($event)); ?>
                        </div>
                        <ul class="mec-event-sharing"><?php echo MEC_kses::full($this->main->module('links.list', array('event' => $event))); ?></ul>
                    </div>
                    <div class="col-md-4 col-sm-4 mec-btn-wrapper">
                        <?php echo MEC_kses::element($this->booking_button($event, 'icon')); ?>
                        <?php echo MEC_kses::element($this->display_link($event, ((is_array($event->data->tickets) and count($event->data->tickets) and !strpos($soldout, '%%soldout%%') and !$this->booking_button and !$this->main->is_expired($event)) ? $this->main->m('register_button', esc_html__('REGISTER', 'modern-events-calendar-lite')) : $this->main->m('view_detail', esc_html__('View Detail', 'modern-events-calendar-lite'))), 'mec-booking-button')); ?>
                        <?php do_action('mec_list_modern_style', $event); ?>
                    </div>
                <?php elseif($this->style == 'classic'): ?>
                    <div class="mec-event-image"><?php echo MEC_kses::element($this->display_link($event, $event->data->thumbnails['thumbnail'])); ?></div>
                    <?php if(isset($settings['multiple_day_show_method']) && $settings['multiple_day_show_method'] == 'all_days'): ?>
                        <div class="mec-event-date mec-color"><i class="mec-sl-calendar"></i> <?php echo esc_html($this->main->date_i18n($this->date_format_classic_1, strtotime($event->date['start']['date']))); ?></div>
                    <?php else: ?>
                        <div class="mec-event-date mec-color"><i class="mec-sl-calendar"></i> <?php echo MEC_kses::element($this->main->dateify($event, $this->date_format_classic_1)); ?></div>
                        <div class="mec-event-time mec-color"><?php if($this->include_events_times and trim($start_time)) {echo '<i class="mec-sl-clock"></i>'; echo MEC_kses::element($this->main->display_time($start_time, $end_time)); } ?></div>
                    <?php endif; ?>
                    <?php echo MEC_kses::element($this->get_label_captions($event)); ?>
                    <?php if($this->localtime) echo MEC_kses::full($this->main->module('local-time.type2', array('event' => $event))); ?>
                    <h4 class="mec-event-title"><?php echo MEC_kses::element($this->display_link($event)); ?><?php echo MEC_kses::embed($this->display_custom_data($event)); ?><?php echo MEC_kses::element($this->main->get_flags($event).$event_color.$this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation)); ?><?php do_action('mec_shortcode_virtual_badge', $event->data->ID ); ?></h4>
                    <?php if(isset($location['name'])): ?><div class="mec-event-detail"><div class="mec-event-loc-place"><i class="mec-sl-map-marker"></i> <?php echo (isset($location['name']) ? esc_html($location['name']) : ''); ?></div></div><?php endif; ?>
                    <?php echo MEC_kses::element($this->display_categories($event)); ?>
                    <?php echo MEC_kses::element($this->display_organizers($event)); ?>
                    <?php echo MEC_kses::element($this->display_cost($event)); ?>
                    <?php do_action('mec_list_classic_after_location', $event, $this->skin_options); ?>
                    <?php echo MEC_kses::form($this->booking_button($event)); ?>
                <?php elseif($this->style == 'minimal'): ?>
                    <?php echo MEC_kses::element($this->get_label_captions($event)); ?>
                    <div class="col-md-9 col-sm-9">
                        <?php if($this->main->is_multipleday_occurrence($event, true)): ?>
                        <div class="mec-event-date mec-bg-color">
                            <span class="mec-multiple-dates"><?php echo esc_html($this->main->date_i18n($this->date_format_minimal_1, strtotime($event->date['start']['date']))); ?> - <?php echo esc_html($this->main->date_i18n($this->date_format_minimal_1, strtotime($event->date['end']['date']))); ?></span>
                            <?php echo esc_html($this->main->date_i18n($this->date_format_minimal_2, strtotime($event->date['start']['date']))); ?>
                        </div>
                        <?php elseif($this->main->is_multipleday_occurrence($event)): ?>
                        <div class="mec-event-date mec-bg-color">
                            <span><?php echo esc_html($this->main->date_i18n($this->date_format_minimal_1, strtotime($event->date['start']['date']))); ?></span>
                            <?php echo esc_html($this->main->date_i18n($this->date_format_minimal_2, strtotime($event->date['start']['date']))); ?>
                        </div>
                        <div class="mec-event-date mec-bg-color">
                            <span><?php echo esc_html($this->main->date_i18n($this->date_format_minimal_1, strtotime($event->date['end']['date']))); ?></span>
                            <?php echo esc_html($this->main->date_i18n($this->date_format_minimal_2, strtotime($event->date['end']['date']))); ?>
                        </div>
                        <?php else: ?>
                        <div class="mec-event-date mec-bg-color">
                            <span><?php echo esc_html($this->main->date_i18n($this->date_format_minimal_1, strtotime($event->date['start']['date']))); ?></span>
                            <?php echo esc_html($this->main->date_i18n($this->date_format_minimal_2, strtotime($event->date['start']['date']))); ?>
                        </div>
                        <?php endif; ?>

                        <?php if($this->include_events_times and trim($start_time)) echo MEC_kses::element($this->main->display_time($start_time, $end_time)); ?>
                        <h4 class="mec-event-title"><?php echo MEC_kses::element($this->display_link($event)); ?><?php echo MEC_kses::embed($this->display_custom_data($event)); ?><?php echo MEC_kses::element($this->main->get_flags($event).$event_color.$this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation)); ?><?php do_action('mec_shortcode_virtual_badge', $event->data->ID ); ?></h4>
                        <div class="mec-event-detail">
                            <span class="mec-day-wrapper"><?php echo esc_html($this->main->date_i18n($this->date_format_minimal_3, strtotime($event->date['start']['date']))); ?></span><?php echo (isset($location['name']) ? '<span class="mec-comma-wrapper">,</span> <span class="mec-event-loc-place">' . esc_html($location['name']) .'</span>' : ''); ?><?php if($this->localtime) echo MEC_kses::full($this->main->module('local-time.type2', array('event' => $event))); ?>
                        </div>
                        <?php do_action('mec_list_minimal_after_details', $event); ?>
                        <?php echo MEC_kses::element($this->display_categories($event)); ?>
                        <?php echo MEC_kses::element($this->display_organizers($event)); ?>
                        <?php echo MEC_kses::element($this->display_cost($event)); ?>
                        <?php echo MEC_kses::form($this->booking_button($event)); ?>
                    </div>
                    <div class="col-md-3 col-sm-3 btn-wrapper"><?php do_action('before_mec_list_minimal_button', $event); ?><?php echo MEC_kses::element($this->display_link($event, $this->main->m('event_detail', esc_html__('EVENT DETAIL', 'modern-events-calendar-lite')), 'mec-detail-button')); ?></div>
                <?php elseif($this->style == 'standard'): ?>
                    <?php
                        $excerpt = trim($event->data->post->post_excerpt) ? $event->data->post->post_excerpt : '';

                        // Safe Excerpt for UTF-8 Strings
                        if(!trim($excerpt))
                        {
                            $ex = explode(' ', strip_tags(strip_shortcodes($event->data->post->post_content)));
                            $words = array_slice($ex, 0, 10);

                            $excerpt = implode(' ', $words);
                        }
                    ?>
                    <div class="mec-topsec">
                        <?php if (!empty($event->data->thumbnails['thumblist'])) : ?>
                        <div class="col-md-3 mec-event-image-wrap mec-col-table-c">
                            <div class="mec-event-image"><?php echo MEC_kses::element($this->display_link($event, $event->data->thumbnails['thumblist'], '')); ?></div>
                        </div>
                        <?php endif; ?>

                        <div class="<?php echo (!empty($event->data->thumbnails['thumblist'])) ? 'col-md-6' : 'col-md-9'; ?> mec-col-table-c mec-event-content-wrap">
                            <div class="mec-event-content">
                                <?php $soldout = $this->main->get_flags($event); ?>
                                <?php echo MEC_kses::element($this->display_status_bar($event)); ?>
                                <h3 class="mec-event-title"><?php echo MEC_kses::element($this->display_link($event)); ?><?php echo MEC_kses::embed($this->display_custom_data($event)); ?><?php echo MEC_kses::element($soldout.$event_color.$this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation)); ?><?php do_action('mec_shortcode_virtual_badge', $event->data->ID ); ?></h3>
                                <div class="mec-event-description"><?php echo MEC_kses::element($excerpt.(trim($excerpt) ? ' <span>...</span>' : '')); ?></div>
                            </div>
                        </div>
                        <div class="col-md-3 mec-col-table-c mec-event-meta-wrap">
                            <div class="mec-event-meta mec-color-before">
                                <div class="mec-date-details">
                                    <?php if(isset($settings['multiple_day_show_method']) && $settings['multiple_day_show_method'] == 'all_days') : ?>
                                        <span class="mec-event-d"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path id="calendar" d="M14.667,16H1.333A1.335,1.335,0,0,1,0,14.667v-12A1.335,1.335,0,0,1,1.333,1.333H4.667V.667A.667.667,0,1,1,6,.667v.667h4V.667a.667.667,0,1,1,1.333,0v.667h3.333A1.335,1.335,0,0,1,16,2.667v12A1.335,1.335,0,0,1,14.667,16ZM11.333,2.667v.667a.667.667,0,1,1-1.333,0V2.667H6v.667a.667.667,0,0,1-1.333,0V2.667H1.333v12H14.665l0-12ZM12.667,12H11.333a.667.667,0,0,1-.667-.667V10a.667.667,0,0,1,.667-.667h1.333a.667.667,0,0,1,.667.667v1.333A.667.667,0,0,1,12.667,12Zm0-4H11.333a.667.667,0,0,1-.667-.667V6a.667.667,0,0,1,.667-.667h1.333A.667.667,0,0,1,13.333,6V7.333A.667.667,0,0,1,12.667,8Zm-4,4H7.333a.667.667,0,0,1-.667-.667V10a.667.667,0,0,1,.667-.667H8.667A.667.667,0,0,1,9.333,10v1.333A.667.667,0,0,1,8.667,12Zm0-4H7.333a.667.667,0,0,1-.667-.667V6a.667.667,0,0,1,.667-.667H8.667A.667.667,0,0,1,9.333,6V7.333A.667.667,0,0,1,8.667,8Zm-4,4H3.333a.667.667,0,0,1-.667-.667V10a.667.667,0,0,1,.667-.667H4.667A.667.667,0,0,1,5.333,10v1.333A.667.667,0,0,1,4.667,12Zm0-4H3.333a.667.667,0,0,1-.667-.667V6a.667.667,0,0,1,.667-.667H4.667A.667.667,0,0,1,5.333,6V7.333A.667.667,0,0,1,4.667,8Z" fill="#60daf2" fill-rule="evenodd"/></svg><?php echo esc_html($this->main->date_i18n($this->date_format_standard_1, strtotime($event->date['start']['date']))); ?></span>
                                    <?php else: ?>
                                        <span class="mec-event-d"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path id="calendar" d="M14.667,16H1.333A1.335,1.335,0,0,1,0,14.667v-12A1.335,1.335,0,0,1,1.333,1.333H4.667V.667A.667.667,0,1,1,6,.667v.667h4V.667a.667.667,0,1,1,1.333,0v.667h3.333A1.335,1.335,0,0,1,16,2.667v12A1.335,1.335,0,0,1,14.667,16ZM11.333,2.667v.667a.667.667,0,1,1-1.333,0V2.667H6v.667a.667.667,0,0,1-1.333,0V2.667H1.333v12H14.665l0-12ZM12.667,12H11.333a.667.667,0,0,1-.667-.667V10a.667.667,0,0,1,.667-.667h1.333a.667.667,0,0,1,.667.667v1.333A.667.667,0,0,1,12.667,12Zm0-4H11.333a.667.667,0,0,1-.667-.667V6a.667.667,0,0,1,.667-.667h1.333A.667.667,0,0,1,13.333,6V7.333A.667.667,0,0,1,12.667,8Zm-4,4H7.333a.667.667,0,0,1-.667-.667V10a.667.667,0,0,1,.667-.667H8.667A.667.667,0,0,1,9.333,10v1.333A.667.667,0,0,1,8.667,12Zm0-4H7.333a.667.667,0,0,1-.667-.667V6a.667.667,0,0,1,.667-.667H8.667A.667.667,0,0,1,9.333,6V7.333A.667.667,0,0,1,8.667,8Zm-4,4H3.333a.667.667,0,0,1-.667-.667V10a.667.667,0,0,1,.667-.667H4.667A.667.667,0,0,1,5.333,10v1.333A.667.667,0,0,1,4.667,12Zm0-4H3.333a.667.667,0,0,1-.667-.667V6a.667.667,0,0,1,.667-.667H4.667A.667.667,0,0,1,5.333,6V7.333A.667.667,0,0,1,4.667,8Z" fill="#60daf2" fill-rule="evenodd"/></svg><?php echo MEC_kses::element($this->main->dateify($event, $this->date_format_standard_1)); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php echo MEC_kses::element($this->get_label_captions($event)); ?>
                                <?php echo MEC_kses::element($this->main->display_time($start_time, $end_time, ['display_svg' => true])); ?>
                                <?php if($this->localtime) echo MEC_kses::full($this->main->module('local-time.type1', array('event' => $event))); ?>
                                <?php if(isset($location['name'])): ?>
                                <div class="mec-venue-details">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12.308" height="16" viewBox="0 0 12.308 16"><path id="location" d="M6.6,15.839a.7.7,0,0,1-.89,0C5.476,15.644,0,11,0,6.029A6.1,6.1,0,0,1,6.154,0a6.1,6.1,0,0,1,6.154,6.029C12.308,11,6.832,15.644,6.6,15.839ZM6.154,1.333a4.747,4.747,0,0,0-4.786,4.7c0,3.6,3.52,7.215,4.786,8.4,1.266-1.184,4.787-4.8,4.787-8.4A4.747,4.747,0,0,0,6.154,1.333Zm0,7.383A2.7,2.7,0,0,1,3.419,6.049,2.7,2.7,0,0,1,6.154,3.383,2.7,2.7,0,0,1,8.889,6.049,2.7,2.7,0,0,1,6.154,8.716Zm0-4A1.334,1.334,0,1,0,7.521,6.049,1.353,1.353,0,0,0,6.154,4.716Z" fill="#40d9f1" fill-rule="evenodd"/></svg>
                                    <span><?php echo esc_html($location['name']); ?></span><address class="mec-event-address"><span><?php echo (isset($location['address']) ? esc_html($location['address']) : ''); ?></span></address>
                                </div>
                                <?php endif; ?>
                                <?php echo MEC_kses::element($this->display_categories($event)); ?>
                                <?php echo MEC_kses::element($this->display_organizers($event)); ?>
                                <?php echo MEC_kses::element($this->display_cost($event)); ?>
                                <?php do_action('mec_list_standard_right_box', $event); ?>
                            </div>
                        </div>
                    </div>
                    <div class="mec-event-footer">
                        <?php if(isset($settings['social_network_status']) and $settings['social_network_status'] != '0') : ?>
                        <ul class="mec-event-sharing-wrap">
                            <li class="mec-event-share">
                                <a href="#" class="mec-event-share-icon">
                                    <i class="mec-sl-share" title="social share"></i>
                                </a>
                            </li>
                            <li>
                                <ul class="mec-event-sharing">
                                    <?php echo MEC_kses::full($this->main->module('links.list', array('event' => $event))); ?>
                                </ul>
                            </li>
                        </ul>
                        <?php endif; ?>
                        <?php echo MEC_kses::full($this->main->display_progress_bar($event)); ?>
                        <?php do_action('mec_standard_booking_button', $event); ?>
                        <?php echo MEC_kses::form($this->booking_button($event)); ?>
                        <?php echo MEC_kses::element($this->display_link($event, ((is_array($event->data->tickets) and count($event->data->tickets) and !strpos($soldout, '%%soldout%%') and !$this->booking_button and !$this->main->is_expired($event)) ? $this->main->m('register_button', esc_html__('REGISTER', 'modern-events-calendar-lite')) : $this->main->m('view_detail', esc_html__('View Detail', 'modern-events-calendar-lite'))), 'mec-booking-button')); ?>
                    </div>
                <?php elseif($this->style == 'accordion'): ?>
                    <!-- toggles wrap start -->
                    <div class="mec-events-toggle">
                        <!-- toggle item start -->
                        <div class="mec-toggle-item">
                            <div class="mec-toggle-item-inner<?php if($this->toggle_month_divider == '1') echo ' mec-toogle-inner-month-divider'; ?>" tabindex="0">
                                <?php if($this->toggle_month_divider == '1'): ?>
                                <div class="mec-toggle-month-inner-image">
                                    <a href="<?php echo esc_url($this->main->get_event_date_permalink($event, $event->date['start']['date'])); ?>"><?php echo MEC_kses::element($event->data->thumbnails['thumbnail']); ?></a>
                                </div>
                                <?php endif; ?>
                                <div class="mec-toggle-item-col">
                                    <?php if(isset($settings['multiple_day_show_method']) && $settings['multiple_day_show_method'] == 'all_days') : ?>
                                        <div class="mec-event-date"><?php echo esc_html($this->main->date_i18n($this->date_format_acc_1, strtotime($event->date['start']['date']))); ?></div>
                                        <div class="mec-event-month"><?php echo esc_html($this->main->date_i18n($this->date_format_acc_2, strtotime($event->date['start']['date']))); ?></div>
                                    <?php else: ?>
                                        <div class="mec-event-month"><?php echo MEC_kses::element($this->main->dateify($event, $this->date_format_acc_1.' '.$this->date_format_acc_2)); ?></div>
                                    <?php endif; ?>
                                    <?php echo MEC_kses::element($this->main->display_time($start_time, $end_time)); ?>
                                </div>
                                <h3 class="mec-toggle-title">
                                    <?php
                                        echo apply_filters(
                                            'mec_events_toggle_title',
                                            MEC_kses::element($event->data->title),
                                            $event,
                                            $this
                                        );
                                    ?><?php echo MEC_kses::element($this->main->get_flags($event).$event_color); ?></h3>
                                <?php echo MEC_kses::element($this->get_label_captions($event,'mec-fc-style')); ?>
                                <?php echo MEC_kses::element($this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation)); ?><?php do_action('mec_shortcode_virtual_badge', $event->data->ID); ?><i class="mec-sl-arrow-down"></i>
                            </div>
                            <div class="mec-content-toggle" aria-hidden="true" style="display: none;">
                                <div class="mec-toggle-content">
                                    <?php echo MEC_kses::full($this->render->vsingle(array('id' => $event->data->ID, 'layout' => 'm2', 'occurrence' => $date))); ?>
                                </div>
                            </div>
                        </div><!-- toggle item end -->
                    </div><!-- toggles wrap end -->
                <?php elseif($this->style === 'admin'): ?>
                    <div class="col-md-2 col-sm-2">
                        <?php if($this->main->is_multipleday_occurrence($event, true)): ?>
                            <div class="mec-event-date">
                                <div class="event-d mec-color mec-multiple-dates">
                                    <?php echo esc_html($this->main->date_i18n($this->date_format_modern_1, strtotime($event->date['start']['date']))); ?> -
                                    <?php echo esc_html($this->main->date_i18n($this->date_format_modern_1, strtotime($event->date['end']['date']))); ?>
                                </div>
                                <div class="event-f"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_2, strtotime($event->date['start']['date']))); ?></div>
                                <div class="event-da"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_3, strtotime($event->date['start']['date']))); ?></div>
                            </div>
                        <?php elseif($this->main->is_multipleday_occurrence($event)): ?>
                            <div class="mec-event-date mec-multiple-date-event">
                                <div class="event-d mec-color"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_1, strtotime($event->date['start']['date']))); ?></div>
                                <div class="event-f"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_2, strtotime($event->date['start']['date']))); ?></div>
                                <div class="event-da"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_3, strtotime($event->date['start']['date']))); ?></div>
                            </div>
                            <div class="mec-event-date mec-multiple-date-event">
                                <div class="event-d mec-color"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_1, strtotime($event->date['end']['date']))); ?></div>
                                <div class="event-f"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_2, strtotime($event->date['end']['date']))); ?></div>
                                <div class="event-da"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_3, strtotime($event->date['end']['date']))); ?></div>
                            </div>
                        <?php else: ?>
                            <div class="mec-event-date">
                                <div class="event-d mec-color"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_1, strtotime($event->date['start']['date']))); ?></div>
                                <div class="event-f"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_2, strtotime($event->date['start']['date']))); ?></div>
                                <div class="event-da"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_3, strtotime($event->date['start']['date']))); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8 col-sm-8">
                        <?php $soldout = $this->main->get_flags($event); ?>
                        <h4 class="mec-event-title">
                            <a class="event-link-admin" href="<?php echo esc_url(get_edit_post_link($event->ID)); ?>" target="_blank">
                                <?php echo $event->data->title; ?>
                            </a>
                            <?php echo MEC_kses::element($soldout.$event_color); echo MEC_kses::element($this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation)); ?>
                            <?php do_action('mec_shortcode_virtual_badge', $event->data->ID); ?>
                            <?php echo MEC_kses::element($this->get_label_captions($event,'mec-fc-style')); ?>
                        </h4>
                        <div class="mec-event-detail">
                            <div class="mec-event-loc-place"><?php echo (isset($location['name']) ? esc_html($location['name']) : '') . (isset($location['address']) && !empty($location['address']) ? ' | '.esc_html($location['address']) : ''); ?></div>
                            <?php if($this->include_events_times and trim($start_time)) echo MEC_kses::element($this->main->display_time($start_time, $end_time)); ?>
                            <?php echo MEC_kses::element($this->display_categories($event)); ?>
                            <?php echo MEC_kses::element($this->display_organizers($event)); ?>
                            <?php echo MEC_kses::element($this->display_cost($event)); ?>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-2">
                        <?php if(isset($event->date['start']['timestamp']) && current_user_can(current_user_can('administrator') ? 'manage_options' : 'mec_bookings') && $total_attendees = $this->main->get_total_attendees_by_event_occurrence($event->data->ID, $event->date['start']['timestamp'])): ?>
                        <a href="<?php echo trim($this->main->URL('admin'), '/ ').'/?mec-dl-bookings=1&event_id='.$event->data->ID.'&occurrence='.$event->date['start']['timestamp']; ?>"><?php echo esc_html__('Download Attendees', 'modern-events-calendar-lite'); ?> (<?php echo esc_html($total_attendees); ?>)</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </article>
            <?php } ?>
		<?php endforeach; ?>
	</div>
</div>

<?php
if(isset($this->map_on_top) and $this->map_on_top and isset($map_events) and !empty($map_events))
{
    // Include Map Assets such as JS and CSS libraries
    $this->main->load_map_assets();

    // It changing geolocation focus, because after done filtering, if it doesn't. then the map position will not set correctly.
    if((isset($_REQUEST['action']) and sanitize_text_field($_REQUEST['action']) == 'mec_list_load_more') and isset($_REQUEST['sf'])) $this->geolocation_focus = true;

    $map_javascript = '<script>
    var mecmap'.esc_js($this->id).';
    jQuery(document).ready(function()
    {
        var jsonPush = gmapSkin('.json_encode($this->render->markers($map_events, $this->style)).');
        mecmap'.esc_js($this->id).' = jQuery("#mec_googlemap_canvas'.esc_js($this->id).'").mecGoogleMaps(
        {
            id: "'.esc_js($this->id).'",
            autoinit: false,
            atts: "'.http_build_query(array('atts' => $this->atts), '', '&').'",
            zoom: '.(isset($settings['google_maps_zoomlevel']) ? $settings['google_maps_zoomlevel'] : 14).',
            icon: "'.apply_filters('mec_marker_icon', $this->main->asset('img/m-04.png')).'",
            styles: '.((isset($settings['google_maps_style']) and trim($settings['google_maps_style']) != '') ? $this->main->get_googlemap_style($settings['google_maps_style']) : "''").',
            markers: jsonPush,
            clustering_images: "'.esc_js($this->main->asset('img/cluster1/m')).'",
            getDirection: 0,
            ajax_url: "'.admin_url('admin-ajax.php', NULL).'",
            geolocation: "'.esc_js($this->geolocation).'",
            geolocation_focus: '.esc_js($this->geolocation_focus).'
        });

        var mecinterval'.esc_js($this->id).' = setInterval(function()
        {
            if(jQuery("#mec_googlemap_canvas'.esc_js($this->id).'").is(":visible"))
            {
                mecmap'.esc_js($this->id).'.init();
                clearInterval(mecinterval'.esc_js($this->id).');
            }
        }, 1000);
    });
    </script>';

    $map_javascript = apply_filters('mec_map_load_script', $map_javascript, $this, $settings);

    // Include javascript code into the page
    if($this->main->is_ajax()) echo MEC_kses::full($map_javascript);
    else $this->factory->params('footer', $map_javascript);
}