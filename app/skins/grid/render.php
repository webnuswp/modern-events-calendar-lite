<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_skin_grid $this */

$styling = $this->main->get_styling();
$event_colorskin = (isset($styling['mec_colorskin'] ) || isset($styling['color'])) ? 'colorskin-custom' : '';
$settings = $this->main->get_settings();
$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;
$map_events = array();

// colorful
$colorful_flag = $colorful_class = '';
if($this->style == 'colorful')
{
	$colorful_flag = true;
	$this->style = 'modern';
	$colorful_class = ' mec-event-grid-colorful';
}
?>
<div class="mec-wrap <?php echo esc_attr($event_colorskin . $colorful_class); ?>">
    <div class="mec-event-grid-<?php echo esc_attr($this->style); ?>" data-widget-autoplay="<?php echo ($this->widget_autoplay ? 1 : 0); ?>" data-widget-loop="<?php echo ($this->widget_loop ? 1 : 0); ?>" data-widget-autoplay-time="<?php echo esc_attr($this->widget_autoplay_time); ?>">
        <?php
        $count      = $this->count;
        $grid_div   = $this->count;
        $grid_limit = $this->limit;

        if($count == 0 or $count == 5) $col = 4;
        else $col = 12 / $count;

        $close_row = true;
        $rcount = 1 ;

        foreach($this->events as $date):
        foreach($date as $event):

        $map_events[] = $event;
        if($rcount == 1)
        {
            echo '<div class="row">';
            $close_row = true;
        }

        echo '<div class="col-md-'.esc_attr($col).' col-sm-'.esc_attr($col).'">';

        $location_id = $this->main->get_master_location_id($event);
        $location = ($location_id ? $this->main->get_location_data($location_id) : array());

        $organizer_id = $this->main->get_master_organizer_id($event);
        $organizer = ($organizer_id ? $this->main->get_organizer_data($organizer_id) : array());
        $event_color = $this->get_event_color_dot($event);
        $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
        $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
        $event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';
        $mec_data = $this->display_custom_data($event);
        $custom_data_class = !empty($mec_data) ? 'mec-custom-data' : '';

        // colorful
		$colorful_bg_color = ($colorful_flag && isset($event->data->meta['mec_color'])) ? ' style="background: #' . esc_attr($event->data->meta['mec_color']) . '"' : '';

        // MEC Schema
        do_action('mec_schema', $event);

        echo '<article class="'.((isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event' : '').' mec-event-article mec-clear ' . esc_attr($custom_data_class) . ' '.esc_attr($this->get_event_classes($event)).'"' . $colorful_bg_color . ' itemscope>';
        ?>
        <?php if($this->style == 'modern'): ?>
            <div class="event-grid-modern-head clearfix">
                <?php if(isset($settings['multiple_day_show_method']) && $settings['multiple_day_show_method'] == 'all_days') : ?>
                    <div class="mec-event-date"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_1, strtotime($event->date['start']['date']))); ?></div>
                    <div class="mec-event-month"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_2, strtotime($event->date['start']['date']))); ?></div>
                <?php else: ?>
                    <div class="mec-event-month"><?php echo MEC_kses::element($this->main->dateify($event, $this->date_format_modern_1 .' '. $this->date_format_modern_2)); ?></div>
                <?php endif; ?>
                <div class="mec-event-detail">
                    <?php echo (isset($location['name']) ? '<div class="mec-event-loc-place"><i class="mec-sl-location-pin"></i>' . esc_html($location['name']) . '</div>' : ''); ?>
                    <?php echo MEC_kses::element($this->display_categories($event)); ?>
                    <?php echo MEC_kses::element($this->display_organizers($event)); ?>
                    <?php echo MEC_kses::element($this->get_label_captions($event)); ?>
                </div>
                <div class="mec-event-day"><?php echo esc_html($this->main->date_i18n($this->date_format_modern_3, strtotime($event->date['start']['date']))); ?></div>
            </div>
            <div class="mec-event-content">
                <?php $soldout = $this->main->get_flags($event); ?>
                <h4 class="mec-event-title"><?php echo MEC_kses::element($this->display_link($event)); ?><?php echo MEC_kses::embed($this->display_custom_data($event)); ?><?php echo MEC_kses::element($soldout.$event_color.$this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation)); ?><?php do_action('mec_shortcode_virtual_badge', $event->data->ID ); ?></h4>
                <?php if($this->localtime) echo MEC_kses::full($this->main->module('local-time.type3', array('event' => $event))); ?>
                <?php if($this->include_events_times and trim($start_time)) echo MEC_kses::element($this->main->display_time($start_time, $end_time)); ?>
                <p class="mec-grid-event-location"><?php echo (isset($location['address']) ? esc_html($location['address']) : ''); ?></p>
                <?php echo MEC_kses::element($this->display_cost($event)); ?>
            </div>
            <div class="mec-event-footer">
                <?php echo MEC_kses::element($this->display_link($event, ((is_array($event->data->tickets) and count($event->data->tickets) and !strpos($soldout, '%%soldout%%') and !$this->booking_button and !$this->main->is_expired($event)) ? $this->main->m('register_button', esc_html__('REGISTER', 'modern-events-calendar-lite')) : $this->main->m('view_detail', esc_html__('View Detail', 'modern-events-calendar-lite'))), 'mec-booking-button')); ?>
                <?php echo MEC_kses::form($this->booking_button($event)); ?>
                <?php if($settings['social_network_status'] != '0'): ?>
                <ul class="mec-event-sharing-wrap">
                    <li class="mec-event-share">
                        <a href="#" class="mec-event-share-icon">
                            <i class="mec-sl-share" title="<?php echo esc_html__('Share', 'modern-events-calendar-lite') ?>"></i>
                        </a>
                    </li>
                    <li>
                        <ul class="mec-event-sharing">
                            <?php echo MEC_kses::full($this->main->module('links.list', array('event' => $event))); ?>
                        </ul>
                    </li>
                </ul>
                <?php endif; ?>
            </div>
        <?php elseif($this->style == 'classic'): ?>
            <div class="mec-event-image"><?php echo MEC_kses::element($this->display_link($event, $event->data->thumbnails['medium'], '')); ?></div>
            <?php do_action('mec_grid_classic_image', $event); ?>
            <div class="mec-event-content">
                <?php if(isset($settings['multiple_day_show_method']) && $settings['multiple_day_show_method'] == 'all_days') : ?>
                    <div class="mec-event-date mec-bg-color">
                        <?php echo esc_html($this->main->date_i18n($this->date_format_classic_1, strtotime($event->date['start']['date']))); ?>
                        <?php if($this->localtime) echo MEC_kses::full($this->main->module('local-time.type3', array('event' => $event))); ?>
                        <?php if($this->include_events_times and trim($start_time)) echo MEC_kses::element($this->main->display_time($start_time, $end_time)); ?>
                    </div>
                <?php else: ?>
                    <div class="mec-event-date mec-bg-color">
                        <?php echo MEC_kses::element($this->main->dateify($event, $this->date_format_classic_1)); ?>
                        <?php if($this->localtime) echo MEC_kses::full($this->main->module('local-time.type3', array('event' => $event))); ?>
                        <?php if($this->include_events_times and trim($start_time)) echo MEC_kses::element($this->main->display_time($start_time, $end_time)); ?>
                    </div>
                <?php endif; ?>
                <?php do_action('mec_classic_before_title', $event); ?>
                <?php $soldout = $this->main->get_flags($event); ?>
                <h4 class="mec-event-title"><?php echo MEC_kses::element($this->display_link($event)); ?><?php echo MEC_kses::embed($this->display_custom_data($event)); ?><?php echo MEC_kses::element($soldout.$event_color.$this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation)); ?><?php do_action('mec_shortcode_virtual_badge', $event->data->ID ); ?></h4>
                <?php echo MEC_kses::element($this->get_label_captions($event)); ?>
                <p class="mec-grid-event-location"><?php echo trim((isset($location['name']) ? $location['name'] : '').', '.(isset($location['address']) ? $location['address'] : ''), ', '); ?></p>
                <?php echo MEC_kses::element($this->display_categories($event)); ?>
                <?php echo MEC_kses::element($this->display_organizers($event)); ?>
                <?php echo MEC_kses::element($this->display_cost($event)); ?>
                <?php do_action('mec_classic_view_action', $event); ?>
            </div>
            <div class="mec-event-footer">
                <?php if($settings['social_network_status'] != '0') : ?>
                <ul class="mec-event-sharing-wrap">
                    <li class="mec-event-share">
                        <a href="#" class="mec-event-share-icon">
                            <i class="mec-sl-share" title="<?php echo esc_html__('Share', 'modern-events-calendar-lite') ?>"></i>
                        </a>
                    </li>
                    <li>
                        <ul class="mec-event-sharing">
                            <?php echo MEC_kses::full($this->main->module('links.list', array('event' => $event))); ?>
                        </ul>
                    </li>
                </ul>
                <?php endif; ?>
                <?php echo MEC_kses::form($this->booking_button($event)); ?>
                <?php echo MEC_kses::element($this->display_link($event, ((is_array($event->data->tickets) and count($event->data->tickets) and !strpos($soldout, '%%soldout%%') and !$this->booking_button and !$this->main->is_expired($event)) ? $this->main->m('register_button', esc_html__('REGISTER', 'modern-events-calendar-lite')) : $this->main->m('view_detail', esc_html__('View Detail', 'modern-events-calendar-lite'))), 'mec-booking-button')); ?>
            </div>
        <?php elseif($this->style == 'minimal'): ?>
            <div class="mec-event-date mec-bg-color-hover mec-border-color-hover mec-color"><span><?php echo esc_html($this->main->date_i18n($this->date_format_minimal_1, strtotime($event->date['start']['date']))); ?></span><?php echo esc_html($this->main->date_i18n($this->date_format_minimal_2, strtotime($event->date['start']['date']))); ?></div>
            <div class="event-detail-wrap">
                <h4 class="mec-event-title"><?php echo MEC_kses::element($this->display_link($event)); ?><?php echo MEC_kses::embed($this->display_custom_data($event)); ?><?php echo MEC_kses::element($this->main->get_flags($event).$event_color); echo MEC_kses::element($this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation)); ?><?php do_action('mec_shortcode_virtual_badge', $event->data->ID); ?><?php echo MEC_kses::element($this->get_label_captions($event,'mec-fc-style')); ?></h4>
                <?php if($this->localtime) echo MEC_kses::full($this->main->module('local-time.type2', array('event' => $event))); ?>
                <?php if($this->include_events_times) echo MEC_kses::element($this->main->display_time($start_time, $end_time)); ?>
                <div class="mec-event-detail">
                    <?php echo (isset($location['name']) ? '<div class="mec-event-loc-place"><i class="mec-sl-location-pin"></i>' . esc_html($location['name']) . '</div>' : ''); ?>
                    <?php echo MEC_kses::element($this->display_categories($event)); ?>
                    <?php echo MEC_kses::element($this->display_organizers($event)); ?>
                    <?php echo MEC_kses::element($this->display_cost($event)); ?>
                    <?php echo MEC_kses::form($this->booking_button($event)); ?>
                </div>
            </div>
        <?php elseif($this->style == 'clean'): ?>
            <div class="event-grid-t2-head mec-bg-color clearfix">
                <?php if(isset($settings['multiple_day_show_method']) && $settings['multiple_day_show_method'] == 'all_days') : ?>
                    <div class="mec-event-date"><?php echo esc_html($this->main->date_i18n($this->date_format_clean_1, strtotime($event->date['start']['date']))); ?></div>
                    <div class="mec-event-month"><?php echo esc_html($this->main->date_i18n($this->date_format_clean_2, strtotime($event->date['start']['date']))); ?></div>
                    <?php if($this->localtime) echo MEC_kses::full($this->main->module('local-time.type3', array('event' => $event))); ?>
                    <?php if($this->include_events_times and trim($start_time)) echo MEC_kses::element($this->main->display_time($start_time, $end_time)); ?>
                    <?php do_action('display_mec_tad', $event); ?>
                <?php else: ?>
                    <div class="mec-event-month"><?php echo MEC_kses::element($this->main->dateify($event, $this->date_format_clean_1.' '.$this->date_format_clean_2)); ?></div>
                    <?php do_action('display_mec_tad', $event); ?>
                    <?php if($this->localtime) echo MEC_kses::full($this->main->module('local-time.type3', array('event' => $event))); ?>
                    <?php if($this->include_events_times and trim($start_time)) echo MEC_kses::element($this->main->display_time($start_time, $end_time)); ?>
                <?php endif; ?>
                <div class="mec-event-detail">
                    <?php echo (isset($location['name']) ? '<div class="mec-event-loc-place"><i class="mec-sl-location-pin"></i>' . esc_html($location['name']) . '</div>' : ''); ?>
                    <?php echo MEC_kses::element($this->display_categories($event)); ?>
                    <?php echo MEC_kses::element($this->display_organizers($event)); ?>
                    <?php echo MEC_kses::element($this->display_cost($event)); ?>
                    <?php echo MEC_kses::element($this->get_label_captions($event)); ?>
                </div>
            </div>
            <div class="mec-event-image"><?php do_action('display_mec_clean_image', $event ); ?><?php echo MEC_kses::element($this->display_link($event, $event->data->thumbnails['medium'], '')); ?></div>
            <div class="mec-event-content">
                <?php do_action('display_mec_tai', $event); ?>
                <?php do_action('mec_clean_custom_head', $event, $event_color); ?>
                <?php $soldout = $this->main->get_flags($event); ?>
                <h4 class="mec-event-title"><?php echo MEC_kses::element($this->display_link($event)); ?><?php echo MEC_kses::embed($this->display_custom_data($event)); ?><?php echo MEC_kses::element($soldout.$event_color.$this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation)); ?><?php do_action('mec_shortcode_virtual_badge', $event->data->ID ); ?></h4>
                <p class="mec-grid-event-location"><?php echo (isset($location['address']) ? esc_html($location['address']) : ''); ?></p>
            </div>
            <div class="mec-event-footer mec-color">
                <?php if($settings['social_network_status'] != '0') : ?>
                <ul class="mec-event-sharing-wrap">
                    <li class="mec-event-share">
                        <a href="#" class="mec-event-share-icon">
                            <i class="mec-sl-share" title="<?php echo esc_html__('Share', 'modern-events-calendar-lite') ?>"></i>
                        </a>
                    </li>
                    <li>
                        <ul class="mec-event-sharing">
                            <?php echo MEC_kses::full($this->main->module('links.list', array('event' => $event))); ?>
                        </ul>
                    </li>
                </ul>
                <?php endif; ?>
                <?php do_action('mec_grid_clean_booking_button', $event); ?>
                <?php echo MEC_kses::form($this->booking_button($event)); ?>
                <?php echo MEC_kses::element($this->display_link($event, ((is_array($event->data->tickets) and count($event->data->tickets) and !strpos($soldout, '%%soldout%%') and !$this->booking_button and !$this->main->is_expired($event)) ? $this->main->m('register_button', esc_html__('REGISTER', 'modern-events-calendar-lite')) : $this->main->m('view_detail', esc_html__('View Detail', 'modern-events-calendar-lite'))), 'mec-booking-button')); ?>
            </div>
        <?php elseif($this->style == 'novel'): ?>
            <div class="novel-grad-bg"></div>
            <div class="mec-event-content">
                <div class="mec-event-image"><?php echo MEC_kses::element($this->display_link($event, $event->data->thumbnails['thumblist'], '')); ?></div>
                <div class="mec-event-detail-wrap">
                    <?php $soldout = $this->main->get_flags($event); ?>
                    <h4 class="mec-event-title"><?php echo MEC_kses::element($this->display_link($event)); ?><?php echo MEC_kses::embed($this->display_custom_data($event)); ?><?php echo MEC_kses::element($soldout.$this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation)); ?><?php do_action('mec_shortcode_virtual_badge', $event->data->ID ); ?></h4>
                    <?php if(isset($settings['multiple_day_show_method']) && $settings['multiple_day_show_method'] == 'all_days') : ?>
                        <div class="mec-event-month"><?php echo esc_html($this->main->date_i18n($this->date_format_novel_1, strtotime($event->date['start']['date']))); ?></div>
                    <?php else: ?>
                        <div class="mec-event-month"><?php echo MEC_kses::element($this->main->dateify($event, $this->date_format_novel_1)); ?></div>
                    <?php endif; ?>
                    <?php
                        if($this->include_events_times and trim($start_time)) echo MEC_kses::element($this->main->display_time($start_time, $end_time, array('class' => 'mec-event-detail')));
                        if(isset($location['address'])) echo '<div class="mec-event-address">'.esc_html($location['address']).'</div>';
                        if($this->localtime) echo MEC_kses::full($this->main->module('local-time.type1', array('event' => $event)));
                        echo MEC_kses::element($this->display_categories($event));
                        echo MEC_kses::element($this->display_organizers($event));
                        echo MEC_kses::element($this->display_cost($event));
                    ?>
                    <?php echo MEC_kses::element($this->get_label_captions($event)); ?>
                    <div class="mec-event-footer mec-color">
                        <?php if($settings['social_network_status'] != '0') : ?>
                        <ul class="mec-event-sharing-wrap">
                            <li class="mec-event-share">
                                <a href="#" class="mec-event-share-icon">
                                    <i class="mec-sl-share" title="<?php echo esc_html__('Share', 'modern-events-calendar-lite') ?>"></i>
                                </a>
                            </li>
                            <li>
                                <ul class="mec-event-sharing">
                                    <?php echo MEC_kses::full($this->main->module('links.list', array('event' => $event))); ?>
                                </ul>
                            </li>
                        </ul>
                        <?php endif; ?>
                        <?php echo MEC_kses::element($this->booking_button($event, 'icon')); ?>
                        <?php echo MEC_kses::element($this->display_link($event, ((is_array($event->data->tickets) and count($event->data->tickets) and !strpos($soldout, '%%soldout%%') and !$this->booking_button and !$this->main->is_expired($event)) ? $this->main->m('register_button', esc_html__('REGISTER', 'modern-events-calendar-lite')) : $this->main->m('view_detail', esc_html__('View Detail', 'modern-events-calendar-lite'))), 'mec-booking-button')); ?>
                    </div>
                </div>
            </div>
        <?php elseif($this->style == 'simple'): ?>
            <?php do_action('mec_skin_grid_simple', $event); ?>
            <div class="mec-event-date mec-color"><?php echo MEC_kses::element($this->main->dateify($event, $this->date_format_simple_1)); ?></div>
            <h4 class="mec-event-title"><?php echo MEC_kses::element($this->display_link($event)); ?><?php echo MEC_kses::embed($this->display_custom_data($event)); ?><?php echo MEC_kses::element($this->main->get_flags($event).$event_color); echo MEC_kses::element($this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation)); ?><?php do_action('mec_shortcode_virtual_badge', $event->data->ID); ?><?php echo MEC_kses::element($this->get_label_captions($event,'mec-fc-style')); ?></h4>
            <div class="mec-event-detail">
                <span class="mec-event-loc-place"><?php echo (isset($location['name']) ? esc_html($location['name']) : ''); ?></span>
                <?php if($this->localtime) echo MEC_kses::full($this->main->module('local-time.type3', array('event' => $event))); ?>
                <?php if($this->include_events_times and trim($start_time)) echo MEC_kses::element($this->main->display_time($start_time, $end_time)); ?>
                <?php echo MEC_kses::element($this->display_categories($event)); ?>
                <?php echo MEC_kses::element($this->display_organizers($event)); ?>
                <?php echo MEC_kses::element($this->display_cost($event)); ?>
            </div>
            <?php echo MEC_kses::form($this->booking_button($event)); ?>
        <?php endif;
        echo '</article></div>';

        if($rcount == $count)
        {
            echo '</div>';
            $rcount = 0;
            $close_row = false;
        }

        $rcount++;
        ?>
        <?php endforeach; ?>
        <?php endforeach; ?>
        <?php if($close_row) echo '</div>'; ?>
	</div>
</div>

<?php
if(isset($this->map_on_top) and $this->map_on_top and isset($map_events) and !empty($map_events))
{
    // Include Map Assets such as JS and CSS libraries
    $this->main->load_map_assets();

    // It changing geolocation focus, because after done filtering, if it doesn't. then the map position will not set correctly.
    if((isset($_REQUEST['action']) and sanitize_text_field($_REQUEST['action']) == 'mec_grid_load_more') and isset($_REQUEST['sf'])) $this->geolocation_focus = true;

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
            geolocation_focus: '.esc_js($this->geolocation_focus).',
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

    $map_data = new stdClass;
    $map_data->id = $this->id;
    $map_data->atts = $this->atts;
    $map_data->events =  $map_events;
    $map_data->render = $this->render;
    $map_data->geolocation = $this->geolocation;
    $map_data->sf_status = null;
    $map_data->main = $this->main;

    $map_javascript = apply_filters('mec_map_load_script', $map_javascript, $this, $settings);

    // Include javascript code into the page
    if($this->main->is_ajax()) echo MEC_kses::full($map_javascript);
    else $this->factory->params('footer', $map_javascript);
}