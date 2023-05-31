<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_skin_monthly_view $this */

// table headings
$headings = $this->main->get_weekday_abbr_labels();
echo '<dl class="mec-calendar-table-head"><dt class="mec-calendar-day-head">'.MEC_kses::element(implode('</dt><dt class="mec-calendar-day-head">', $headings)).'</dt></dl>';

// Start day of week
$week_start = $this->main->get_first_day_of_week();

$this->localtime = isset($this->skin_options['include_local_time']) ? $this->skin_options['include_local_time'] : false;
$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;

// days and weeks vars
$running_day = date('w', mktime(0, 0, 0, $month, 1, $year));
$days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
$days_in_previous_month = date('t', strtotime('-1 month', strtotime($this->active_day)));

$days_in_this_week = 1;
$day_counter = 0;
$styles_str = '';

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

$events_str = '';
if($this->display_all) $events_str .= '<h3 class="mec-table-side-title">'.esc_html__('Events', 'modern-events-calendar-lite').'</h3>';

$date_format = get_option('date_format');
?>
<dl class="mec-calendar-row">
    <?php
        // print "blank" days until the first of the current week
        for($x = 0; $x < $running_day; $x++)
        {
            $list_day = ($days_in_previous_month - ($running_day-1-$x));
            $time = strtotime(($month == 1 ? ($year - 1) : $year).'-'.($month == 1 ? 12 : ($month - 1)).'-'.$list_day);

            $today = date('Y-m-d', $time);
            $day_id = date('Ymd', $time);
            $selected_day = (str_replace('-', '', $this->active_day) == $day_id) ? ' mec-selected-day' : '';

            // Print events
            if(isset($events[$today]) and count($events[$today]))
            {
                echo '<dt class="mec-calendar-day mec-has-event '.esc_attr($selected_day).'" data-mec-cell="'.esc_attr($day_id).'" data-day="'.esc_attr($list_day).'" data-month="'.date('Ym', strtotime($year.'-'.$month.'-01')).'"><a href="'.($this->display_all ? '#mec-calendar-events-sec-'.esc_attr($this->id.'-'.$day_id) : '#').'" class="mec-has-event-a">'.MEC_kses::full(apply_filters('mec_filter_list_day_value', $list_day, $today, $this)).'</a></dt>';
                $events_str .= '<div class="mec-calendar-events-sec" id="mec-calendar-events-sec-'.esc_attr($this->id.'-'.$day_id).'" data-mec-cell="'.esc_attr($day_id).'" '.((trim($selected_day) != '' or $this->display_all) ? ' style="display: block;"' : '').'>'.MEC_kses::element($this->day_label($time));

                foreach($events[$today] as $event)
                {
                    $location_id = $this->main->get_master_location_id($event);
                    $location = ($location_id ? $this->main->get_location_data($location_id) : array());

                    $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                    $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
                    $startDate = !empty($event->data->meta['mec_date']['start']['date']) ? $event->data->meta['mec_date']['start']['date'] : '';
                    $endDate = !empty($event->data->meta['mec_date']['end']['date']) ? $event->data->meta['mec_date']['end']['date'] : '' ;
                    $event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';

                    $events_filter = $after_time_filter = '';

                    $start_datetime =  isset($event->date['start']) && !empty($event->date['start']) ? $event->date['start'] : array();
                    $end_datetime = isset($event->date['end']) && !empty($event->date['end']) ? $event->date['end'] : array();
                    $start_timestamp = isset( $start_datetime['timestamp'] ) ? $start_datetime['timestamp'] : ( isset( $start_datetime['date'] ) ? strtotime( $start_datetime['date'] ) : '' );

                    $multiple_day_show_method = \MEC\Settings\Settings::getInstance()->get_settings('multiple_day_show_method');
                    if( 'first_day_listgrid' === $multiple_day_show_method ){

                        $date_label = $this->main->date_label( $start_datetime, $end_datetime, $date_format );
                    }else{

                        $date_label = $this->main->date_i18n( $date_format, $start_timestamp );
                    }


                    $midnight_event = $this->main->is_midnight_event($event);

                    if($midnight_event){

                        $event_date = $this->main->dateify( $event, $date_format );
                    }else{

                        $event_date = $date_label;
                    }

                    // MEC Schema
                    do_action('mec_schema', $event);

                    $events_str .= '<article class="'.($this->main->is_expired($event) ? 'mec-past-event ' : '').'mec-event-article '.esc_attr($this->get_event_classes($event)).'">';
                    $events_str .= '<div class="mec-event-image">'.MEC_kses::element($event->data->thumbnails['thumblist']).'</div>';
                    $events_str .= $this->get_label_captions($event);

                    if($this->display_all) $events_str .= '<div class="mec-event-date">'.MEC_kses::element( $event_date ).'</div>';

                    if($this->display_detailed_time and $this->main->is_multipleday_occurrence($event)) $events_str .= '<div class="mec-event-detailed-time mec-event-time mec-color"><i class="mec-sl-clock-o"></i> '.MEC_kses::element($this->display_detailed_time($event)).'</div>';
                    elseif(trim($start_time)) $events_str .= '<div class="mec-event-time mec-color"><i class="mec-sl-clock-o"></i> '.MEC_kses::element($start_time.(trim($end_time) ? ' - '.$end_time : '')).'</div>';

                    if(has_filter('monthly_event_after_time')) $after_time_filter = apply_filters('monthly_event_after_time', $events_str, $event);

                    $events_str .= $after_time_filter;
                    $event_color = $this->get_event_color_dot($event);
                    $events_str .= '<h4 class="mec-event-title">'.MEC_kses::element($this->display_link($event).$this->main->get_flags($event).$event_color.$this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation));
                    if(has_filter('mec_monthly_virtual_badge')) $events_str .= apply_filters('mec_monthly_virtual_badge', '', $event->data->ID);
                    $events_str .= '</h4>';

                    $events_str .= apply_filters('mec_calendar_clean_after_title', '', $event->ID, $event);

                    if($this->localtime) $events_str .= MEC_kses::full($this->main->module('local-time.type3', array('event' => $event)));
                    $events_str .= '<div class="mec-event-detail">
                        <div class="mec-event-loc-place">'.(isset($location['name']) ? esc_html($location['name']) : '').'</div>
                        '.MEC_kses::element($this->display_organizers($event)).'
                    </div>';
                    $events_str .= MEC_kses::element($this->booking_button($event));
                    $events_str .= MEC_kses::embed($this->display_custom_data($event));
                    $events_str .= MEC_kses::element($this->display_cost($event));

                    if(has_filter('monthly_event_right_box')) $events_filter = apply_filters('monthly_event_right_box', $events_str, $event);

                    $events_str .= MEC_kses::full($events_filter);
                    $events_str .= '</article>';
                }

                $events_str .= '</div>';
            }
            else
            {
                echo '<dt class="mec-table-nullday">'.apply_filters('mec_filter_list_day_value', $list_day, $today, $this).'</dt>';
            }

            $days_in_this_week++;
        }

        // keep going with days ....
        for($list_day = 1; $list_day <= $days_in_month; $list_day++)
        {
            $time = strtotime($year.'-'.$month.'-'.$list_day);

            $today = date('Y-m-d', $time);
            $day_id = date('Ymd', $time);
            $selected_day = (str_replace('-', '', $this->active_day) == $day_id) ? ' mec-selected-day' : '';

            // Print events
            if(isset($events[$today]) and count($events[$today]))
            {
                echo '<dt class="mec-calendar-day mec-has-event '.esc_attr($selected_day).'" data-mec-cell="'.esc_attr($day_id).'" data-day="'.esc_attr($list_day).'" data-month="'.date('Ym', $time).'"><a href="'.($this->display_all ? '#mec-calendar-events-sec-'.esc_attr($this->id.'-'.$day_id) : '#').'" class="mec-has-event-a">'.MEC_kses::full(apply_filters('mec_filter_list_day_value', $list_day, $today, $this)).'</a></dt>';
                $events_str .= '<div class="mec-calendar-events-sec" id="mec-calendar-events-sec-'.esc_attr($this->id.'-'.$day_id).'" data-mec-cell="'.esc_attr($day_id).'" '.((trim($selected_day) != '' or $this->display_all) ? ' style="display: block;"' : '').'>'.MEC_kses::element($this->day_label($time));

                foreach($events[$today] as $event)
                {
                    $location_id = $this->main->get_master_location_id($event);
                    $location = ($location_id ? $this->main->get_location_data($location_id) : array());

                    $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                    $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
                    $startDate = !empty($event->data->meta['mec_date']['start']['date'] ) ? $event->data->meta['mec_date']['start']['date'] : '';
                    $endDate = !empty($event->data->meta['mec_date']['end']['date'] ) ? $event->data->meta['mec_date']['end']['date'] : '' ;
                    $event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';

                    $events_filter = $after_time_filter = '';

                    $start_datetime =  isset($event->date['start']) && !empty($event->date['start']) ? $event->date['start'] : array();
                    $end_datetime = isset($event->date['end']) && !empty($event->date['end']) ? $event->date['end'] : array();
                    $start_timestamp = isset( $start_datetime['timestamp'] ) ? $start_datetime['timestamp'] : ( isset( $start_datetime['date'] ) ? strtotime( $start_datetime['date'] ) : '' );

                    $multiple_day_show_method = \MEC\Settings\Settings::getInstance()->get_settings('multiple_day_show_method');
                    if( 'first_day_listgrid' === $multiple_day_show_method ){

                        $date_label = $this->main->date_label( $start_datetime, $end_datetime, $date_format );
                    }else{

                        $date_label = $this->main->date_i18n( $date_format, $start_timestamp );
                    }

                    $midnight_event = $this->main->is_midnight_event($event);
                    if($midnight_event){

                        $event_date = $this->main->dateify( $event, $date_format );
                    }else{

                        $event_date = $date_label;
                    }

                    // MEC Schema
                    do_action('mec_schema', $event);

                    $events_str .= '<article class="'.($this->main->is_expired($event) ? 'mec-past-event ' : '').'mec-event-article '.esc_attr($this->get_event_classes($event)).'">';
                    $content_style = !empty($event->data->thumbnails['thumblist']) ? 'width: calc(100% - 86px)' : 'width: 100%';
                    $events_str .= '<div class="mec-event-image">'.MEC_kses::element($event->data->thumbnails['thumblist']).'</div><div class="mec-monthly-contents" style="'. $content_style .'">';
                    $events_str .= MEC_kses::element($this->get_label_captions($event));

                    if($this->display_all) $events_str .= '<div class="mec-event-date">'.MEC_kses::element( $event_date ).'</div>';

                    if($this->display_detailed_time and $this->main->is_multipleday_occurrence($event)) $events_str .= '<div class="mec-event-detailed-time mec-event-time mec-color"><i class="mec-sl-clock-o"></i> '.MEC_kses::element($this->display_detailed_time($event)).'</div>';
                    elseif(trim($start_time)) $events_str .= '<div class="mec-event-time mec-color"><i class="mec-sl-clock-o"></i> '.esc_html($start_time.(trim($end_time) ? ' - '.$end_time : '')).'</div>';

                    if(has_filter('monthly_event_after_time')) $after_time_filter = apply_filters('monthly_event_after_time', $events_str, $event);

                    $events_str .= $after_time_filter;
                    $event_color = $this->get_event_color_dot($event);
                    $events_str .= '<h4 class="mec-event-title">'.MEC_kses::element($this->display_link($event).$this->main->get_flags($event).$event_color.$this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation));
                    if(has_filter('mec_monthly_virtual_badge')) $events_str .= apply_filters('mec_monthly_virtual_badge', '', $event->data->ID);
                    $events_str .='</h4>';

                    $events_str .= apply_filters('mec_calendar_clean_after_title', '', $event->ID, $event);

                    if($this->localtime) $events_str .= MEC_kses::full($this->main->module('local-time.type3', array('event' => $event)));
                    $events_str .= '<div class="mec-event-detail">
                        <div class="mec-event-loc-place">'.esc_html((isset($location['name']) ? $location['name'] : '')).'</div>
                        '.MEC_kses::element($this->display_organizers($event)).'
                    </div>';
                    $events_str .= MEC_kses::element($this->booking_button($event));
                    $events_str .= MEC_kses::embed($this->display_custom_data($event));
                    $events_str .= MEC_kses::element($this->display_cost($event));

                    if(has_filter('monthly_event_right_box')) $events_filter = apply_filters('monthly_event_right_box', $events_str, $event);

                    $events_str .= MEC_kses::full($events_filter);
                    $events_str .= '</div><div style="clear:both"></div></article>';
                }

                $events_str .= '</div>';
            }
            else
            {
                echo '<dt class="mec-calendar-day '.esc_attr($selected_day).'" data-mec-cell="'.esc_attr($day_id).'" data-day="'.esc_attr($list_day).'" data-month="'.date('Ym', $time).'">'.MEC_kses::full(apply_filters('mec_filter_list_day_value', $list_day, $today, $this)).'</dt>';

                if(!$this->display_all)
                {
                    $events_str .= '<div class="mec-calendar-events-sec" id="mec-calendar-events-sec-'.esc_attr($this->id.'-'.$day_id).'" data-mec-cell="'.esc_attr($day_id).'" '.((trim($selected_day) != '' or $this->display_all) ? ' style="display: block;"' : '').'>'.MEC_kses::element($this->day_label($time));
                    $events_str .= '<article class="mec-event-article">';
                    $events_str .= '<div class="mec-event-detail">'.esc_html__('No Events', 'modern-events-calendar-lite').'</div>';
                    $events_str .= '</article>';
                    $events_str .= '</div>';
                }
            }

            echo '</dt>';

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
                $list_day = $x;
                $time = strtotime(($month == 12 ? ($year + 1) : $year).'-'.($month == 12 ? 1 : ($month + 1)).'-'.$list_day);

                $today = date('Y-m-d', $time);
                $day_id = date('Ymd', $time);
                $selected_day = (str_replace('-', '', $this->active_day) == $day_id) ? ' mec-selected-day' : '';

                // Print events
                if(isset($events[$today]) and count($events[$today]))
                {
                    echo '<dt class="mec-calendar-day mec-has-event '.esc_attr($selected_day).'" data-mec-cell="'.esc_attr($day_id).'" data-day="'.esc_attr($list_day).'" data-month="'.date('Ym', strtotime($year.'-'.$month.'-01')).'"><a href="'.($this->display_all ? '#mec-calendar-events-sec-'.esc_attr($this->id.'-'.$day_id) : '#').'" class="mec-has-event-a">'.MEC_kses::full(apply_filters('mec_filter_list_day_value', $list_day, $today, $this)).'</a></dt>';
                    $events_str .= '<div class="mec-calendar-events-sec" id="mec-calendar-events-sec-'.esc_attr($this->id.'-'.$day_id).'" data-mec-cell="'.esc_attr($day_id).'" '.((trim($selected_day) != '' or $this->display_all) ? ' style="display: block;"' : '').'>'.MEC_kses::element($this->day_label($time));

                    foreach($events[$today] as $event)
                    {
                        $location_id = $this->main->get_master_location_id($event);
                        $location = ($location_id ? $this->main->get_location_data($location_id) : array());

                        $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                        $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
                        $startDate = !empty($event->data->meta['mec_date']['start']['date'] ) ? $event->data->meta['mec_date']['start']['date'] : '';
                        $endDate = !empty($event->data->meta['mec_date']['end']['date'] ) ? $event->data->meta['mec_date']['end']['date'] : '' ;
                        $event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';

                        $events_filter = $after_time_filter = '';

                        $start_datetime =  isset($event->date['start']) && !empty($event->date['start']) ? $event->date['start'] : array();
                        $end_datetime = isset($event->date['end']) && !empty($event->date['end']) ? $event->date['end'] : array();
                        $start_timestamp = isset($start_datetime['timestamp']) ? $start_datetime['timestamp'] : (isset($start_datetime['date']) ? strtotime($start_datetime['date']) : '');

                        $multiple_day_show_method = \MEC\Settings\Settings::getInstance()->get_settings('multiple_day_show_method');

                        if('first_day_listgrid' === $multiple_day_show_method) $date_label = $this->main->date_label($start_datetime, $end_datetime, $date_format);
                        else $date_label = $this->main->date_i18n($date_format, $start_timestamp);

                        $midnight_event = $this->main->is_midnight_event($event);

                        if($midnight_event) $event_date = $this->main->dateify($event, $date_format);
                        else $event_date = $date_label;

                        // MEC Schema
                        do_action('mec_schema', $event);

                        $events_str .= '<article class="'.($this->main->is_expired($event) ? 'mec-past-event ' : '').'mec-event-article '.esc_attr($this->get_event_classes($event)).'">';
                        $events_str .= '<div class="mec-event-image">'.MEC_kses::element($event->data->thumbnails['thumblist']).'</div>';
                        $events_str .= MEC_kses::element($this->get_label_captions($event));

                        if($this->display_all) $events_str .= '<div class="mec-event-date">'.MEC_kses::element( $event_date ).'</div>';

                        if($this->display_detailed_time and $this->main->is_multipleday_occurrence($event)) $events_str .= '<div class="mec-event-detailed-time mec-event-time mec-color"><i class="mec-sl-clock-o"></i> '.MEC_kses::element($this->display_detailed_time($event)).'</div>';
                        elseif(trim($start_time)) $events_str .= '<div class="mec-event-time mec-color"><i class="mec-sl-clock-o"></i> '.esc_html($start_time.(trim($end_time) ? ' - '.$end_time : '')).'</div>';

                        if(has_filter('monthly_event_after_time')) $after_time_filter = apply_filters('monthly_event_after_time', $events_str, $event);

                        $events_str .= $after_time_filter;
                        $event_color = $this->get_event_color_dot($event);

                        $events_str .= '<h4 class="mec-event-title">'.MEC_kses::element($this->display_link($event).$this->main->get_flags($event).$event_color.$this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation));
                        if(has_filter('mec_monthly_virtual_badge')) $events_str .= apply_filters('mec_monthly_virtual_badge', '', $event->data->ID);
                        $events_str .='</h4>';

                        $events_str .= apply_filters('mec_calendar_clean_after_title', '', $event->ID, $event);

                        if($this->localtime) $events_str .= MEC_kses::full($this->main->module('local-time.type3', array('event' => $event)));
                        $events_str .= '<div class="mec-event-detail">
                            <div class="mec-event-loc-place">'.esc_html((isset($location['name']) ? $location['name'] : '')).'</div>
                            '.MEC_kses::element($this->display_organizers($event)).'
                        </div>';
                        $events_str .= MEC_kses::element($this->booking_button($event));
                        $events_str .= MEC_kses::embed($this->display_custom_data($event));
                        $events_str .= MEC_kses::element($this->display_cost($event));

                        if(has_filter('monthly_event_right_box')) $events_filter = apply_filters('monthly_event_right_box', $events_str, $event);

                        $events_str .= MEC_kses::full($events_filter);
                        $events_str .= '</article>';
                    }

                    $events_str .= '</div>';
                }
                else
                {
                    echo '<dt class="mec-table-nullday">'.esc_html($x).'</dt>';
                }
            }
        }

        if(trim($styles_str)) $this->factory->params('footer', '<style type="text/css">'.MEC_kses::full($styles_str).'</style>');
    ?>
</dl>
<?php if($this->style == 'classic'): ?>
<div class="mec-calendar-events-side mec-clear">
    <?php echo MEC_kses::full($events_str); ?>
</div>
<?php else:
    $this->events_str = $events_str;
endif;