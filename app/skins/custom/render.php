<?php
/** no direct access **/
defined('MECEXEC') or die();
use Elementor\Plugin;
if ( ! did_action( 'elementor/loaded' ) ) {
    return;
}

$styling = $this->main->get_styling();
$event_colorskin = (isset($styling['mec_colorskin'] ) || isset($styling['color'])) ? 'colorskin-custom' : '';
$settings = $this->main->get_settings();

// colorful
$colorful_flag = $colorful_class = '';
if($this->style == 'colorful')
{
	$colorful_flag = true;
	$this->style = 'modern';
	$colorful_class = ' mec-event-custom-colorful';
}
  
?>
<div class="mec-wrap <?php echo $event_colorskin . $colorful_class; ?>">
    <div class="mec-event-custom-<?php echo $this->style; ?>">       
        
            <?php 
            $count = $this->count;

            if($count == 0 or $count == 5) $col = 4;
            else $col = 12 / $count;

            $rcount = 1 ;

            if($this->show_only_expired_events)
            {
                $start = $this->start_date;
                $end = date('Y-m-01', strtotime('-10 Year', strtotime($start)));
            }
            else
            {
                $start = $this->start_date;
                $end = date('Y-m-t', strtotime('+10 Year', strtotime($start)));
            }

            // Date Events
            $dates = $this->period($start, $end, true);

            // Limit
            $this->args['posts_per_page'] = 1000;

            $i = 0;
            $found = 0;
            $events = array();

            foreach($dates as $date=>$IDs)
            {
                // Include Available Events
                $this->args['post__in'] = $IDs;

                // Check Finish Date
                if(isset($this->maximum_date) and strtotime($date) > strtotime($this->maximum_date)) break;

                // Extending the end date
                $this->end_date = $date;

                // Continue to load rest of events in the first date
                if($i === 0) $this->args['offset'] = $this->offset;
                // Load all events in the rest of dates
                else 
                {
                    $this->offset = 0;
                    $this->args['offset'] = 0;
                }

                // The Query
                $query = new WP_Query($this->args);
                if($query->have_posts())
                {
                    // The Loop
                    while($query->have_posts())
                    {
                        $query->the_post();

                        if(!isset($events[$date])) $events[$date] = array();

                        $rendered = $this->render->data(get_the_ID());

                        $data = new stdClass();
                        $data->ID = get_the_ID();
                        $data->data = $rendered;
                        update_option( 'mec_sd_time_option', $date, true);
                        update_option( 'mec_esd_time_option', $this->end_date, true);
                        $data->date = array
                        (
                            'start'=>array('date'=>$date),
                            'end'=>array('date'=>$this->main->get_end_date($date, $rendered))
                        );
                        echo ($rcount == 1) ? '<div class="row">' : '';
                        echo '<div class="col-md-'.$col.' col-sm-'.$col.'">';
                        echo '<article class="mec-event-article mec-clear" itemscope>';
                        echo Plugin::instance()->frontend->get_builder_content_for_display( $this->style, true );
                        echo '</article></div>';
                        if($rcount == $count)
                        {
                            echo '</div>';
                            $rcount = 0;
                        }

                        $rcount++;
                        $events[$date][] = $data;
                        $map_events[] = $events[$date];
                        $found++;

                        if($found >= $this->limit)
                        {
                            // Next Offset
                            $this->next_offset = ($query->post_count-($query->current_post+1)) >= 0 ? ($query->current_post+1)+$this->offset : 0;

                            // Restore original Post Data
                            wp_reset_postdata();

                            break 2;
                        }
                    }
                }

                // Restore original Post Data
                wp_reset_postdata();

                $i++;
            }
            ?>
	</div>
</div>

<?php
foreach ($map_events as $key => $value) {
    foreach ($value as $keyy => $valuee) {
        $map_eventss[] = $valuee;
    }
}

if ( isset($this->map_on_top) and $this->map_on_top ) :
if(isset($map_eventss) and !empty($map_eventss))
{
    // Include Map Assets such as JS and CSS libraries
    $this->main->load_map_assets();

    $map_javascript = '<script type="text/javascript">
    jQuery(document).ready(function()
    {
        var jsonPush = gmapSkin('.json_encode($this->render->markers($map_eventss)).');
        jQuery("#mec_googlemap_canvas'.$this->id.'").mecGoogleMaps(
        {
            id: "'.$this->id.'",
            atts: "'.http_build_query(array('atts'=>$this->atts), '', '&').'",
            zoom: '.(isset($settings['google_maps_zoomlevel']) ? $settings['google_maps_zoomlevel'] : 14).',
            icon: "'.apply_filters('mec_marker_icon', $this->main->asset('img/m-04.png')).'",
            styles: '.((isset($settings['google_maps_style']) and trim($settings['google_maps_style']) != '') ? $this->main->get_googlemap_style($settings['google_maps_style']) : "''").',
            markers: jsonPush,
            clustering_images: "'.$this->main->asset('img/cluster1/m').'",
            getDirection: 0,
            ajax_url: "'.admin_url('admin-ajax.php', NULL).'",
        });
    });
    </script>';

    // Include javascript code into the page
    if($this->main->is_ajax()) echo $map_javascript;
    else $this->factory->params('footer', $map_javascript);
}
endif;