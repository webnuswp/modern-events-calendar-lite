<?php
/** no direct access **/
defined('MECEXEC') or die();

// table headings
$headings = $this->main->get_weekday_abbr_labels();
echo '<dl class="mec-calendar-table-head"><dt class="mec-calendar-day-head">'.implode('</dt><dt class="mec-calendar-day-head">', $headings).'</dt></dl>';


// Start day of week
$week_start = $this->main->get_first_day_of_week();

// Get date suffix 
$settings = $this->main->get_settings();

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
            $date_suffix = (isset($settings['date_suffix']) && $settings['date_suffix'] == '0') ? date_i18n('jS', $time) : date_i18n('j', $time);

            $today = date('Y-m-d', $time);
            $day_id = date('Ymd', $time);
            $selected_day = (str_replace('-', '', $this->active_day) == $day_id) ? ' mec-selected-day' : '';
            $selected_day_date = (str_replace('-', '', $this->active_day) == $day_id) ? 'mec-color' : '';
            // Print events
            if(isset($events[$today]) and count($events[$today]))
            {
                echo '<dt class="mec-calendar-day'.$selected_day.'" data-mec-cell="'.$day_id.'" data-day="'.$list_day.'" data-month="'.date('Ym', $time).'"><div class="'.$selected_day_date.'">'.$list_day.'</div>';
                foreach($events[$today] as $event)
                {
                    $location = isset($event->data->locations[$event->data->meta['mec_location_id']])? $event->data->locations[$event->data->meta['mec_location_id']] : array();
                    $event_color = isset($event->data->meta['mec_color'])? '#'.$event->data->meta['mec_color']:'';
                    $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                    $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
                    
                    
                    echo '<div class="'.((isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : '').'ended-relative simple-skin-ended">';
                    echo '<a class="mec-monthly-tooltip event-single-link-simple" data-tooltip-content="#mec-tooltip-'.$event->data->ID.'-'.$day_id.'" data-event-id="'.$event->data->ID.'" href="'.$this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']).'">';
                    echo '<h4 class="mec-event-title">'.$event->data->title.'</h4>';
                    echo '</a>';
                    echo '</div>';

                    $tooltip_content = '';
                    $tooltip_content .= !empty( $event->data->title) ? '<div class="mec-tooltip-event-title">'.$event->data->title.'</div>' : '' ;
                    $tooltip_content .= trim($start_time) ? '<div class="mec-tooltip-event-time"><i class="mec-sl-clock-o"></i> '.$start_time.(trim($end_time) ? ' - '.$end_time : '').'</div>' : '' ;
                    $tooltip_content .= (!empty($event->data->thumbnails['thumbnail']) || !empty($event->data->content)) ? '<div class="mec-tooltip-event-content">' : '' ;
                    $tooltip_content .= !empty($event->data->thumbnails['thumbnail']) ? '<div class="mec-tooltip-event-featured">'.$event->data->thumbnails['thumbnail'].'</div>' : '' ;
                    $tooltip_content .= !empty(!empty($event->data->content)) ? '<div class="mec-tooltip-event-desc">'.substr($event->data->content, 0, 320).' , ...</div>' : '' ;
                    $tooltip_content .= (!empty($event->data->thumbnails['thumbnail']) || !empty($event->data->content)) ? '</div>' : '' ;
                    $speakers = '""';
                    if ( !empty($event->data->speakers)) 
                    {
                        $speakers= [];
                        foreach ($event->data->speakers as $key => $value) {
                            $speakers[] = array(
                                "@type" 	=> "Person",
                                "name"		=> $value['name'],
                                "image"		=> $value['thumbnail'],
                                "sameAs"	=> $value['facebook'],
                            );
                        } 
                        $speakers = json_encode($speakers);
                    }
                    $startDate = !empty( $event->data->meta['mec_date']['start']['date'] ) ? $event->data->meta['mec_date']['start']['date'] : '';
                    $endDate = !empty( $event->data->meta['mec_date']['end']['date'] ) ? $event->data->meta['mec_date']['end']['date'] : '' ;
                    $location_name = isset($location['name']) ? $location['name'] : '' ;
                    $location_image = isset($location['thumbnail']) ? esc_url($location['thumbnail'] ) : '' ;
                    $location_address = isset($location['address']) ? $location['address'] : '' ;
                    $image = !empty($event->data->featured_image['full']) ? esc_html($event->data->featured_image['full']) : '' ;
                    $price_schema = isset($event->data->meta['mec_cost']) ? $event->data->meta['mec_cost'] : '' ; 
                    $currency_schema = isset($settings['currency']) ? $settings['currency'] : '' ;
                    $schema_settings = isset( $settings['schema'] ) ? $settings['schema'] : '';
                    if($schema_settings == '1' ):                    
                    echo '
                    <script type="application/ld+json">
                    {
                        "@context" 		: "http://schema.org",
                        "@type" 		: "Event",
                        "startDate" 	: "' . $startDate . '",
                        "endDate" 		: "' . $endDate  . '",
                        "location" 		:
                        {
                            "@type" 	: "Place",
                            "name" 		: "' . $location_name . '",
                            "image"		: "' . $location_image  . '",
                            "address"	: "' .  $location_address . '"
                        },
                        "offers": {
                            "url": "'. $event->data->permalink .'",
                            "price": "' . $price_schema.'",
                            "priceCurrency" : "' . $currency_schema .'"
                        },
                        "performer":  '. $speakers . ',
                        "description" 	: "' . esc_html(preg_replace('/<p>\\s*?(<a .*?><img.*?><\\/a>|<img.*?>)?\\s*<\\/p>/s', '<div class="figure">$1</div>', $event->data->post->post_content)) . '",
                        "image" 		: "'. $image . '",
                        "name" 			: "' .esc_html($event->data->title) . '",
                        "url"			: "'. $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']) . '"
                    }
                    </script>
                    ';
                    endif;
                    echo '
                        <div class="tooltip_templates event-single-content-simple">
                            <div id="mec-tooltip-'.$event->data->ID.'-'.$day_id.'">
                                '.$tooltip_content.'
                            </div>
                        </div>';
                }
                echo '</dt>';
            }
            else
            {
                echo '<dt class="mec-calendar-day'.$selected_day.'" data-mec-cell="'.$day_id.'" data-day="'.$list_day.'" data-month="'.date('Ym', $time).'">'.$list_day.'</dt>';
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
                echo '<dt class="mec-table-nullday">'.$x.'</dt>';
            }
        }
    ?>
</dl>