<?php
/** no direct access **/
defined('MECEXEC') or die();

$styling = $this->main->get_styling();
$event_colorskin = (isset($styling['mec_colorskin'] ) || isset($styling['color'])) ? 'colorskin-custom' : '';
$settings = $this->main->get_settings();

// colorful
$colorful_flag = $colorful_class = '';
if($this->style == 'colorful')
{
	$colorful_flag = true;
	$this->style = 'modern';
	$colorful_class = ' mec-event-grid-colorful';
}
?>
<div class="mec-wrap <?php echo $event_colorskin . $colorful_class; ?>">
    <div class="mec-event-grid-<?php echo $this->style; ?>">
        <?php
        $count = $this->count;

        if($count == 0 or $count == 5) $col = 4;
        else $col = 12 / $count;

        $rcount = 1 ;
        foreach($this->events as $date):
        foreach($date as $event):
        $map_events[] = $event;
        echo ($rcount == 1) ? '<div class="row">' : '';
        echo '<div class="col-md-'.$col.' col-sm-'.$col.'">';
        
        $location = isset($event->data->locations[$event->data->meta['mec_location_id']])? $event->data->locations[$event->data->meta['mec_location_id']] : array();
        $organizer = isset($event->data->organizers[$event->data->meta['mec_organizer_id']])? $event->data->organizers[$event->data->meta['mec_organizer_id']] : array();
        $event_color = isset($event->data->meta['mec_color']) ? '<span class="event-color" style="background: #'.$event->data->meta['mec_color'].'"></span>' : '';
        $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
        $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');

        // colorful
		$colorful_bg_color = ($colorful_flag && isset($event->data->meta['mec_color'])) ? ' style="background: #' . $event->data->meta['mec_color'] . '"' : '';

        $label_style = '';
        if(!empty($event->data->labels))
        {
            foreach( $event->data->labels as $label)
            {
                if(!isset($label['style']) or (isset($label['style']) and !trim($label['style']))) continue;

                if($label['style'] == 'mec-label-featured') $label_style = esc_html__('Featured' , 'modern-events-calendar-lite');
                elseif($label['style'] == 'mec-label-canceled') $label_style = esc_html__('Canceled' , 'modern-events-calendar-lite');
            }
        }

        $speakers = '""';
        if(!empty($event->data->speakers))
        {
            $speakers= [];
            foreach($event->data->speakers as $key => $value)
            {
                $speakers[] = array(
                    "@type" 	=> "Person",
                    "name"		=> $value['name'],
                    "image"		=> $value['thumbnail'],
                    "sameAs"	=> $value['facebook'],
                );
            }

            $speakers = json_encode($speakers);
        }

        $schema_settings = isset($settings['schema']) ? $settings['schema'] : '';
        if($schema_settings == '1' ):
        ?>
        <script type="application/ld+json">
        {
            "@context" 		: "http://schema.org",
            "@type" 		: "Event",
            "startDate" 	: "<?php echo !empty( $event->data->meta['mec_date']['start']['date'] ) ? $event->data->meta['mec_date']['start']['date'] : '' ; ?>",
            "endDate" 		: "<?php echo !empty( $event->data->meta['mec_date']['end']['date'] ) ? $event->data->meta['mec_date']['end']['date'] : '' ; ?>",
            "location" 		:
            {
                "@type" 		: "Place",
                "name" 			: "<?php echo (isset($location['name']) ? $location['name'] : ''); ?>",
                "image"			: "<?php echo (isset($location['thumbnail']) ? esc_url($location['thumbnail'] ) : '');; ?>",
                "address"		: "<?php echo (isset($location['address']) ? $location['address'] : ''); ?>"
            },
            "offers": {
                "url": "<?php echo $event->data->permalink; ?>",
                "price": "<?php echo isset($event->data->meta['mec_cost']) ? $event->data->meta['mec_cost'] : '' ; ?>",
                "priceCurrency" : "<?php echo isset($settings['currency']) ? $settings['currency'] : ''; ?>"
            },
            "performer": <?php echo $speakers; ?>,
            "description" 	: "<?php  echo esc_html(preg_replace('/<p>\\s*?(<a .*?><img.*?><\\/a>|<img.*?>)?\\s*<\\/p>/s', '<div class="figure">$1</div>', $event->data->post->post_content)); ?>",
            "image" 		: "<?php echo !empty($event->data->featured_image['full']) ? esc_html($event->data->featured_image['full']) : '' ; ?>",
            "name" 			: "<?php esc_html_e($event->data->title); ?>",
            "url"			: "<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"
        }
        </script>
        <?php endif;
        echo '<article data-style="'.$label_style.'" class="'.((isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event' : '').' mec-event-article mec-clear '.$this->get_event_classes($event).'"' . $colorful_bg_color . ' itemscope>';
        ?>
        <?php if($this->style == 'modern'): ?>
            <div class="event-grid-modern-head clearfix">
                <?php if(isset($settings['multiple_day_show_method']) && $settings['multiple_day_show_method'] == 'all_days') : ?>
                    <div class="mec-event-date"><?php echo date_i18n($this->date_format_modern_1, strtotime($event->date['start']['date'])); ?></div>
                    <div class="mec-event-month"><?php echo date_i18n($this->date_format_modern_2, strtotime($event->date['start']['date'])); ?></div>
                <?php else: ?>
                    <div class="mec-event-month"><?php echo $this->main->date_label($event->date['start'], $event->date['end'], $this->date_format_modern_1 .' '. $this->date_format_modern_2); ?></div>
                <?php endif; ?>
                <div class="mec-event-detail"><?php echo (isset($location['name']) ? $location['name'] : ''); ?></div>
                <div class="mec-event-day"><?php echo date_i18n($this->date_format_modern_3, strtotime($event->date['start']['date'])); ?></div>
            </div>
            <div class="mec-event-content">
                <h4 class="mec-event-title"><a class="mec-color-hover" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a><?php echo $event_color; ?></h4>
                <p><?php echo (isset($location['address']) ? $location['address'] : ''); ?></p>
            </div>
            <div class="mec-event-footer">
                <a class="mec-booking-button" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>" target="_self"><?php echo (is_array($event->data->tickets) and count($event->data->tickets)) ? $this->main->m('register_button', __('REGISTER', 'modern-events-calendar-lite')) : $this->main->m('view_detail', __('View Detail', 'modern-events-calendar-lite')) ; ?></a>
                <?php if($settings['social_network_status'] != '0') : ?>
                <ul class="mec-event-sharing-wrap">
                    <li class="mec-event-share">
                        <a href="#" class="mec-event-share-icon">
                            <i class="mec-sl-share"></i>
                        </a>
                    </li>
                    <li>
                        <ul class="mec-event-sharing">
                            <?php echo $this->main->module('links.list', array('event'=>$event)); ?>
                        </ul>
                    </li>
                </ul>
                <?php endif; ?>
            </div>
        <?php elseif($this->style == 'classic'): ?>
            <div class="mec-event-image"><a data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo $event->data->thumbnails['medium']; ?></a></div>
            <?php do_action('mec_grid_classic_image', $event); ?>
            <div class="mec-event-content">
                <?php if(isset($settings['multiple_day_show_method']) && $settings['multiple_day_show_method'] == 'all_days') : ?>
                    <div class="mec-event-date mec-bg-color"><?php echo date_i18n($this->date_format_classic_1, strtotime($event->date['start']['date'])); ?></div>
                <?php else: ?>
                    <div class="mec-event-date mec-bg-color"><?php echo $this->main->date_label($event->date['start'], $event->date['end'], $this->date_format_classic_1); ?></div>
                <?php endif; ?>
                <?php do_action('mec_classic_before_title' , $event ); ?>
                <h4 class="mec-event-title"><a class="mec-color-hover" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a><?php echo $event_color; ?></h4>
                <?php if ( !empty($label_style) ) echo '<span class="mec-fc-style">'.$label_style.'</span>'; ?>
                <p><?php echo trim((isset($location['name']) ? $location['name'] : '').', '.(isset($location['address']) ? $location['address'] : ''), ', '); ?></p>
                <?php do_action('mec_classic_view_action' , $event); ?>
            </div>
            <div class="mec-event-footer">
                <?php if($settings['social_network_status'] != '0') : ?>
                <ul class="mec-event-sharing-wrap">
                    <li class="mec-event-share">
                        <a href="#" class="mec-event-share-icon">
                            <i class="mec-sl-share"></i>
                        </a>
                    </li>
                    <li>
                        <ul class="mec-event-sharing">
                            <?php echo $this->main->module('links.list', array('event'=>$event)); ?>
                        </ul>
                    </li>
                </ul>
                <?php endif; ?>
                <a class="mec-booking-button" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>" target="_self"><?php echo (is_array($event->data->tickets) and count($event->data->tickets)) ? $this->main->m('register_button', __('REGISTER', 'modern-events-calendar-lite')) : $this->main->m('view_detail', __('View Detail', 'modern-events-calendar-lite')) ; ?></a>
            </div>
        <?php elseif($this->style == 'minimal'): ?>
            <div class="mec-event-date mec-bg-color-hover mec-border-color-hover mec-color"><span><?php echo date_i18n($this->date_format_minimal_1, strtotime($event->date['start']['date'])); ?></span><?php echo date_i18n($this->date_format_minimal_2, strtotime($event->date['start']['date'])); ?></div>
            <div class="event-detail-wrap">
                <h4 class="mec-event-title"><a class="mec-color-hover" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a><?php echo $event_color; ?><?php if ( !empty($label_style) ) echo '<span class="mec-fc-style">'.$label_style.'</span>'; ?></h4>
                <div class="mec-event-detail"><?php echo (isset($location['name']) ? $location['name'] : ''); ?></div>
            </div>
        <?php elseif($this->style == 'clean'): ?>
            <div class="event-grid-t2-head mec-bg-color clearfix">
                <?php if(isset($settings['multiple_day_show_method']) && $settings['multiple_day_show_method'] == 'all_days') : ?>
                    <div class="mec-event-date"><?php echo date_i18n($this->date_format_clean_1, strtotime($event->date['start']['date'])); ?></div>
                    <div class="mec-event-month"><?php echo date_i18n($this->date_format_clean_2, strtotime($event->date['start']['date'])); ?></div>
                    <?php do_action('display_mec_tad' , $event ); ?>
                <?php else: ?>
                    <div class="mec-event-month"><?php echo $this->main->date_label($event->date['start'], $event->date['end'], $this->date_format_clean_1.' '.$this->date_format_clean_2); ?></div>
                    <?php do_action('display_mec_tad' , $event ); ?>
                <?php endif; ?>
                <div class="mec-event-detail"><?php echo (isset($location['name']) ? $location['name'] : ''); ?></div>
            </div>
            <div class="mec-event-image"><?php do_action('display_mec_clean_image' , $event ); ?><a data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo $event->data->thumbnails['medium']; ?></a></div>
            <div class="mec-event-content">
                <?php do_action('display_mec_tai' , $event ); ?>
                <?php do_action('mec_clean_custom_head' , $event , $event_color ); ?>
                <h4 class="mec-event-title"><a class="mec-color-hover" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a><?php echo $event_color; ?></h4>
                <p><?php echo (isset($location['address']) ? $location['address'] : ''); ?></p>
            </div>
            <div class="mec-event-footer mec-color">
                <?php if($settings['social_network_status'] != '0') : ?>
                <ul class="mec-event-sharing-wrap">
                    <li class="mec-event-share">
                        <a href="#" class="mec-event-share-icon">
                            <i class="mec-sl-share"></i>
                        </a>
                    </li>
                    <li>
                        <ul class="mec-event-sharing">
                            <?php echo $this->main->module('links.list', array('event'=>$event)); ?>
                        </ul>
                    </li>
                </ul>
                <?php endif; ?>
                <?php do_action( 'mec_grid_clean_booking_button', $event ); ?>
                <a class="mec-booking-button" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>" target="_self"><?php echo (is_array($event->data->tickets) and count($event->data->tickets)) ? $this->main->m('register_button', __('REGISTER', 'modern-events-calendar-lite')) : $this->main->m('view_detail', __('View Detail', 'modern-events-calendar-lite')) ; ?></a>
            </div>
        <?php elseif($this->style == 'novel'): ?>
            <div class="novel-grad-bg"></div>
            <div class="mec-event-content">
                <div class="mec-event-image"><a data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo $event->data->thumbnails['thumblist']; ?></a></div>
                <div class="mec-event-detail-wrap">
                    <h4 class="mec-event-title"><a class="mec-color-hover" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a></h4>
                    <?php if(isset($settings['multiple_day_show_method']) && $settings['multiple_day_show_method'] == 'all_days') : ?>
                        <div class="mec-event-month"><?php echo date_i18n($this->date_format_novel_1, strtotime($event->date['start']['date'])); ?></div>
                    <?php else: ?>
                        <div class="mec-event-month"><?php echo $this->main->date_label($event->date['start'], $event->date['end'], $this->date_format_novel_1); ?></div>
                    <?php endif; ?>
                    <?php
                        if(trim($start_time))
                        {
                            echo '<div class="mec-event-detail"><span class="mec-start-time">'.$start_time.'</span>';
                            if(trim($end_time)) echo ' - <span class="mec-end-time">'.$end_time.'</span>';
                            echo '</div>';
                        }

                    if( isset($location['address'] ) ) {
                    ?>
                        <div class="mec-event-address"><?php echo $location['address']; ?></div>
                    <?php 
                    }
                    ?>
                    <div class="mec-event-footer mec-color">
                        <a class="mec-booking-button" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>" target="_self"><?php echo (is_array($event->data->tickets) and count($event->data->tickets)) ? $this->main->m('view_detail', __('View Detail', 'modern-events-calendar-lite')) : $this->main->m('register_button', __('REGISTER', 'modern-events-calendar-lite'))  ; ?></a>
                        <?php if($settings['social_network_status'] != '0') : ?>
                        <ul class="mec-event-sharing-wrap">
                            <li class="mec-event-share">
                                <a href="#" class="mec-event-share-icon">
                                    <i class="mec-sl-share"></i>
                                </a>
                            </li>
                            <li>
                                <ul class="mec-event-sharing">
                                    <?php echo $this->main->module('links.list', array('event'=>$event)); ?>
                                </ul>
                            </li>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php elseif($this->style == 'simple'): ?>
            <div class="mec-event-date mec-color"><?php echo $this->main->date_label($event->date['start'], $event->date['end'], $this->date_format_simple_1); ?></div>
            <h4 class="mec-event-title"><a class="mec-color-hover" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a><?php echo $event_color; ?><?php if ( !empty($label_style) ) echo '<span class="mec-fc-style">'.$label_style.'</span>'; ?></h4>
            <div class="mec-event-detail"><?php echo (isset($location['name']) ? $location['name'] : ''); ?></div>
        <?php endif;
        echo '</article></div>';

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
</div>

<?php
if ( isset($this->map_on_top) and $this->map_on_top ) :
if(isset($map_events) and !empty($map_events))
{
    // Include Map Assets such as JS and CSS libraries
    $this->main->load_map_assets();

    $map_javascript = '<script type="text/javascript">
    jQuery(document).ready(function()
    {
        var jsonPush = gmapSkin('.json_encode($this->render->markers($map_events)).');
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