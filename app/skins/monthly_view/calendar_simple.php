<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_skin_monthly_view $this */
/** @var int $month */
/** @var int $year */

// table headings
$headings = $this->main->get_weekday_abbr_labels();
echo '<dl class="mec-calendar-table-head"><dt class="mec-calendar-day-head">'.implode('</dt><dt class="mec-calendar-day-head">', $headings).'</dt></dl>';

// Start day of week
$week_start = $this->main->get_first_day_of_week();

// Single Event Display Method
$sed_method = isset($this->skin_options['sed_method']) ? $this->skin_options['sed_method'] : false;
$target_url = ($sed_method === 'new') ? 'target="_blank"' : '';

$this->localtime = isset($this->skin_options['include_local_time']) ? $this->skin_options['include_local_time'] : false;
$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;

// days and weeks vars
$running_day = date('w', mktime(0, 0, 0, $month, 1, $year));
$days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
$days_in_previous_month = date('t', strtotime('-1 month', strtotime($this->active_day)));

$days_in_this_week = 1;
$day_counter = 0;

if($week_start == 0) $running_day = $running_day; // Sunday
elseif($week_start == 1) // Monday
{
    if($running_day != 0) $running_day = $running_day - 1;
    else $running_day = 6;
}
elseif($week_start == 6) // Saturday
{
    if($running_day != 6) $running_day = $running_day + 1;
    else $running_day = 0;
}
elseif($week_start == 5) // Friday
{
    if($running_day < 4) $running_day = $running_day + 2;
    elseif($running_day == 5) $running_day = 0;
    elseif($running_day == 6) $running_day = 1;
}
?>
<dl class="mec-calendar-row">
    <?php
        // print "blank" days until the first of the current week
        for($x = 0; $x < $running_day; $x++)
        {
            echo '<dt class="mec-table-nullday">'.($days_in_previous_month - ($running_day-1-$x)).'</dt>';
            $days_in_this_week++;
        }

        // keep going with days ....
        for($list_day = 1; $list_day <= $days_in_month; $list_day++)
        {
            $time = strtotime($year.'-'.$month.'-'.$list_day);

            $today = date('Y-m-d', $time);
            $day_id = date('Ymd', $time);
            $selected_day = (str_replace('-', '', $this->active_day) == $day_id) ? ' mec-selected-day' : '';
            $selected_day_date = (str_replace('-', '', $this->active_day) == $day_id) ? 'mec-color' : '';

            // Print events
            if(isset($events[$today]) and count($events[$today]))
            {
                echo '<dt class="mec-calendar-day '.esc_attr($selected_day).'" data-mec-cell="'.esc_attr($day_id).'" data-day="'.esc_attr($list_day).'" data-month="'.date('Ym', $time).'"><div class="'.esc_attr($selected_day_date).'">'.apply_filters('mec_filter_list_day_value', $list_day, $today, $this).'</div>';
                foreach($events[$today] as $event)
                {
                    $event_color = isset($event->data->meta['mec_color']) && !empty($event->data->meta['mec_color']) ? '#'.$event->data->meta['mec_color'] : '';
                    $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                    $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');

                    $event_unique = (isset($event->data->time) ? $event->data->ID.$event->data->time['start_timestamp'] : $event->data->ID);

                    // Event Content
                    if(!$this->cache->has($event->data->ID.'_content'))
                    {
                        if(get_post_meta($event->data->ID, '_elementor_edit_mode', true)) $event_content = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display($event->data->ID);
                        else $event_content = ((isset($event->data->content) and trim($event->data->content) != '') ? mb_substr(strip_tags($event->data->content, '<style>'), 0, 320) : '');

                        $this->cache->set($event->data->ID.'_content', $event_content);
                    }
                    else $event_content = $this->cache->get($event->data->ID.'_content');

                    echo '<div class="'.($this->main->is_expired($event) ? 'mec-past-event ' : '').'ended-relative simple-skin-ended">';
                    if($sed_method !== 'no') echo '<a class="mec-monthly-tooltip event-single-link-simple" data-tooltip-content="#mec-tooltip-'.esc_attr($event_unique.'-'.$day_id).'" data-event-id="'.esc_attr($event->data->ID).'" href="'.esc_url($this->main->get_event_date_permalink($event, $event->date['start']['date'])).'" '.$target_url.'>';
                    echo '<h4 class="mec-event-title">'.esc_html($event->data->title).'</h4>'.MEC_kses::element($this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation));
                    do_action('mec_shortcode_virtual_badge', $event->data->ID);
                    if($sed_method !== 'no') echo '</a>';
                    echo '</div>';

                    $tooltip_content = !empty($event->data->title) ? '<div class="mec-tooltip-event-title">' . esc_html($event->data->title) . '</div>' : '';

                    if($this->display_detailed_time and $this->main->is_multipleday_occurrence($event)) $tooltip_content .= '<div class="mec-event-detailed-time mec-tooltip-event-time mec-color"><i class="mec-sl-clock-o"></i> '.MEC_kses::element($this->display_detailed_time($event)).'</div>';
                    elseif(trim($start_time)) $tooltip_content .= '<div class="mec-tooltip-event-time"><i class="mec-sl-clock-o"></i> '.esc_html($start_time.(trim($end_time) ? ' - '.$end_time : '')).'</div>';

                    $tooltip_content .= $this->display_cost($event);
                    $tooltip_content .= '<div class="mec-event-detail">
                        '.MEC_kses::element($this->display_organizers($event)).'
                    </div>';

                    $tooltip_content .= (!empty($event->data->thumbnails['thumbnail']) || !empty($event->data->content)) ? '<div class="mec-tooltip-event-content">' : '';
                    $tooltip_content .= !empty($event->data->thumbnails['thumbnail']) ? '<div class="mec-tooltip-event-featured">'.MEC_kses::element($event->data->thumbnails['thumbnail']).'</div>' : '';
                    $tooltip_content .= !empty($event->data->content) ? '<div class="mec-tooltip-event-desc">'.MEC_kses::full($event_content).' , ...</div>' : '';
                    if($this->localtime) $tooltip_content .= $this->main->module('local-time.type2', array('event' => $event));
                    $tooltip_content .= (!empty($event->data->thumbnails['thumbnail']) || !empty($event->data->content)) ? '</div>' : '';
                    $tooltip_content .= $this->booking_button($event);
                    $tooltip_content .= '<span class="mec-wrap"><span id="mec_skin_events_'.esc_attr($this->id).'_monthly_simple_'.$event->data->ID.'">'.$this->display_custom_data($event).'</span></span>';

                    // MEC Schema
                    do_action('mec_schema', $event);

                    echo '<div class="tooltip_templates event-single-content-simple">
                        <div id="mec-tooltip-'.esc_attr($event_unique.'-'.$day_id).'">
                            '.MEC_kses::full($tooltip_content).'
                        </div>
                    </div>';
                }

                echo '</dt>';
            }
            else
            {
                echo '<dt class="mec-calendar-day '.esc_attr($selected_day).'" data-mec-cell="'.esc_attr($day_id).'" data-day="'.esc_attr($list_day).'" data-month="'.date('Ym', $time).'">'.apply_filters('mec_filter_list_day_value', $list_day, $today, $this).'</dt>';
                echo '</dt>';
            }

            if($running_day == 6)
            {
                echo '</dl>';

                if((($day_counter+1) != $days_in_month) or (($day_counter+1) == $days_in_month and $days_in_this_week == 7))
                {
                    echo '<dl class="mec-calendar-row">';
                }

                $running_day = -1;
                $days_in_this_week = 0;
            }

            $days_in_this_week++; $running_day++; $day_counter++;
        }

        // finish the rest of the days in the week
        if($days_in_this_week < 8)
        {
            for($x = 1; $x <= (8 - $days_in_this_week); $x++)
            {
                echo '<dt class="mec-table-nullday">'.esc_html($x).'</dt>';
            }
        }
    ?>
</dl>