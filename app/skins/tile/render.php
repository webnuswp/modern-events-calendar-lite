<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_skin_tile $this */

$styling = $this->main->get_styling();
$event_colorskin = (isset($styling['mec_colorskin'] ) || isset($styling['color'])) ? 'colorskin-custom' : '';
$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;

$method = isset($this->skin_options['sed_method']) ? $this->skin_options['sed_method'] : false;
$map_events = array();
?>
<div class="mec-wrap <?php echo esc_attr($event_colorskin); ?>">
    <div class="mec-event-tile-view">
        <?php
        $count = $this->count;

        if($count == 0 or $count == 5) $col = 4;
        else $col = 12 / $count;

        $rcount = 1 ;
        foreach($this->events as $date):
            foreach($date as $event):
                $map_events[] = $event;
                echo ($rcount == 1) ? '<div class="row">' : '';
                echo '<div class="col-md-'.esc_attr($col).' col-sm-'.esc_attr($col).'">';

                $location_id = $this->main->get_master_location_id($event);
                $location = ($location_id ? $this->main->get_location_data($location_id) : array());

                $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                $event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';
                $event_color = ((isset($event->data->meta['mec_color']) and trim($event->data->meta['mec_color'])) ? '#'.$event->data->meta['mec_color'] : '');
                $background_image = (isset($event->data->featured_image['tileview']) && trim($event->data->featured_image['tileview'])) ? ' url(\''.trim($event->data->featured_image['tileview']).'\')' : '';

                $mec_data = $this->display_custom_data($event);
                $custom_data_class = !empty($mec_data) ? 'mec-custom-data' : '';

                // Multiple Day Event Class
                $me_class = $event_start_date == $event->date['end']['date'] || (isset($this->settings['multiple_day_show_method']) && $this->settings['multiple_day_show_method'] == 'all_days') ? '' : 'tile-multipleday-event';

                // MEC Schema
                do_action('mec_schema', $event);
                $date_format_clean_1 = apply_filters( 'mec_skin_tile_date_format_1', $this->date_format_clean_1 );
                $date_format_clean_2 = apply_filters( 'mec_skin_tile_date_format_2', $this->date_format_clean_2 );
                ?>
                    <article <?php if($method != 'no'): ?> data-href="<?php echo esc_url($this->main->get_event_date_permalink($event, $event->date['start']['date'])); ?>" data-target="<?php echo ($method == 'new' ? 'blank' : ($method ? $method : '')); ?>"<?php endif; ?> <?php echo 'style="background:' . esc_attr($event_color) . $background_image. '"'; ?> class="<?php echo ((isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event' : ''); ?> <?php echo ($method == 'no' ? 'mec-no-pointer' : ''); ?> mec-event-article mec-tile-item <?php echo esc_attr($me_class); ?> mec-clear <?php echo esc_attr($this->get_event_classes($event)); ?> <?php echo esc_attr($custom_data_class); ?>">
                        <?php do_action('mec_skin_tile_view', $event); ?>
                        <?php echo MEC_kses::element($this->get_label_captions($event)); ?>
                        <div class="event-tile-view-head clearfix">
                            <?php if(isset($this->settings['multiple_day_show_method']) && $this->settings['multiple_day_show_method'] == 'all_days'): ?>
                                <div class="mec-event-date"><?php echo esc_html($this->main->date_i18n($date_format_clean_1, strtotime($event->date['start']['date']))); ?></div>
                                <div class="mec-event-month"><?php echo esc_html($this->main->date_i18n($date_format_clean_2, strtotime($event->date['start']['date']))); ?></div>
                            <?php else: ?>
                                <div class="mec-event-month"><?php echo MEC_kses::element($this->main->dateify($event, $date_format_clean_1 .' '. $date_format_clean_2)); ?></div>
                            <?php endif; ?>
                            <div class="mec-event-time"><i class="mec-sl-clock"></i><?php echo esc_html($start_time); ?></div>
                        </div>
                        <div class="mec-event-content" data-target="<?php echo ($method == 'new' ? 'blank' : ($method ? $method : '')); ?>" data-event-id="<?php echo esc_attr($event->ID); ?>">
                            <?php if($method != 'no'): ?><a href="<?php echo esc_url($this->main->get_event_date_permalink($event, $event->date['start']['date'])); ?>" target="<?php echo ($method == 'new' ? 'blank' : ($method ? $method : '')); ?>" class="mec-tile-into-content-link"></a><?php endif; ?>
                            <div class="mec-tile-event-content">
                                <div class="mec-event-detail">
                                    <?php echo MEC_kses::element($this->display_categories($event)); ?>
                                    <?php echo MEC_kses::element($this->display_organizers($event)); ?>
                                    <?php echo (isset($location['name']) ? '<span class="mec-event-loc-place"><i class="mec-sl-location-pin"></i>' . esc_html($location['name']) . '</span>' : ''); ?>
                                    <?php echo MEC_kses::element($this->display_cost($event)); ?>
                                </div>
                                <?php echo MEC_kses::element($this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation)); ?><?php do_action('mec_shortcode_virtual_badge', $event->data->ID ); ?>
                                <h4 class="mec-event-title"><?php echo MEC_kses::element($this->display_link($event)); ?><?php echo MEC_kses::embed($this->display_custom_data($event)); ?><?php echo MEC_kses::element($this->main->get_flags($event)); ?></h4>
                                <?php echo MEC_kses::form($this->booking_button($event)); ?>
                            </div>
                        </div>
                    </article>
                <?php
                echo '</div>';
                if($rcount == $count)
                {
                    echo '</div>';
                    $rcount = 0;
                }

                $rcount++;
                ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
    <?php
        $div_count = count($map_events) - (floor(count($map_events) / $count) * $count);
        if($div_count > 0 and $div_count < $count) echo '</div>';
    ?>
</div>