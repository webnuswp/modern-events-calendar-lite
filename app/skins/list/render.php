<?php
/** no direct access **/
defined('MECEXEC') or die();

$styling = $this->main->get_styling();
$settings = $this->main->get_settings();
$current_month_divider = $this->request->getVar('current_month_divider', 0);
$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;

$event_colorskin = (isset($styling['mec_colorskin']) || isset($styling['color'])) ? 'colorskin-custom' : '';
$map_events = array();
?>
<div class="mec-wrap <?php echo $event_colorskin; ?>">
	<div class="mec-event-list-<?php echo $this->style; ?>">
		<?php foreach($this->events as $date=>$events): ?>

            <?php $month_id = date('Ym', strtotime($date)); if($this->month_divider and $month_id != $current_month_divider): $current_month_divider = $month_id; ?>
            <div class="mec-month-divider" data-toggle-divider="mec-toggle-<?php echo date('Ym', strtotime($date)); ?>-<?php echo $this->id; ?>"><span><?php echo $this->main->date_i18n('F Y', strtotime($date)); ?></span><i class="mec-sl-arrow-down"></i></div>
            <?php endif; ?>

            <?php
                foreach($events as $event)
                {
                    $map_events[] = $event;
                    $location = isset($event->data->locations[$event->data->meta['mec_location_id']]) ? $event->data->locations[$event->data->meta['mec_location_id']] : array();
                    $organizer = isset($event->data->organizers[$event->data->meta['mec_organizer_id']]) ? $event->data->organizers[$event->data->meta['mec_organizer_id']] : array();
                    $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                    $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
                    $event_color = isset($event->data->meta['mec_color']) ? '<span class="event-color" style="background: #'.$event->data->meta['mec_color'].'"></span>' : '';
                    $event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';

                    $label_style = '';
                    if(!empty($event->data->labels))
                    {
                        foreach($event->data->labels as $label)
                        {
                            if(!isset($label['style']) or (isset($label['style']) and !trim($label['style']))) continue;
                            if($label['style']  == 'mec-label-featured') $label_style = esc_html__('Featured' , 'modern-events-calendar-lite');
                            elseif($label['style']  == 'mec-label-canceled') $label_style = esc_html__('Canceled' , 'modern-events-calendar-lite');
                        }
                    }

                    // MEC Schema
                    do_action('mec_schema', $event);
            ?>
            <article data-style="<?php echo $label_style; ?>" class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-event-article mec-clear <?php echo $this->get_event_classes($event); ?> mec-divider-toggle mec-toggle-<?php echo date('Ym', strtotime($date)); ?>-<?php echo $this->id; ?>" itemscope>
                <?php if($this->style == 'modern'): ?>
                    <div class="col-md-2 col-sm-2">
                        <div class="mec-event-date">
                            <div class="event-d mec-color"><?php echo $this->main->date_i18n($this->date_format_modern_1, strtotime($event->date['start']['date'])); ?></div>
                            <div class="event-f"><?php echo $this->main->date_i18n($this->date_format_modern_2, strtotime($event->date['start']['date'])); ?></div>
                            <div class="event-da"><?php echo $this->main->date_i18n($this->date_format_modern_3, strtotime($event->date['start']['date'])); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <?php do_action('list_std_title_hook', $event); ?>
                        <?php $soldout = $this->main->get_flags($event); ?>
                        <h4 class="mec-event-title"><a class="mec-color-hover" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a><?php echo $soldout.$event_color; if (!empty($label_style)) echo '<span class="mec-fc-style">'.$label_style.'</span>'; echo $this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event->data->ID, $reason_for_cancellation); ?></h4>
                        <?php if($this->localtime) echo $this->main->module('local-time.type2', array('event'=>$event)); ?>
                        <div class="mec-event-detail"><?php echo (isset($location['name']) ? $location['name'] : '') . (isset($location['address']) ? ' | '.$location['address'] : ''); ?>
                        <?php if($this->include_events_times) echo $this->main->display_time($start_time, $end_time); ?>
                    </div>
                        <ul class="mec-event-sharing"><?php echo $this->main->module('links.list', array('event'=>$event)); ?></ul>
                    </div>
                    <div class="col-md-4 col-sm-4 mec-btn-wrapper">
                        <a class="mec-booking-button" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event, $event->date['start']['date']); ?>"><?php echo (is_array($event->data->tickets) and count($event->data->tickets) and !strpos($soldout, '%%soldout%%')) ? $this->main->m('register_button', __('REGISTER', 'modern-events-calendar-lite')) : $this->main->m('view_detail', __('View Detail', 'modern-events-calendar-lite')); ?></a>
                        <?php do_action('mec_list_modern_style' , $event); ?>
                    </div>
                <?php elseif($this->style == 'classic'): ?>
                    <div class="mec-event-image"><a data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event, $event->date['start']['date']); ?>"><?php echo $event->data->thumbnails['thumbnail']; ?></a></div>
                    <?php if(isset($settings['multiple_day_show_method']) && $settings['multiple_day_show_method'] == 'all_days') : ?>
                        <div class="mec-event-date mec-color"><i class="mec-sl-calendar"></i> <?php echo $this->main->date_i18n($this->date_format_classic_1, strtotime($event->date['start']['date'])); ?></div>
                    <?php else: ?>
                        <div class="mec-event-date mec-color"><i class="mec-sl-calendar"></i> <?php echo $this->main->dateify($event, $this->date_format_classic_1); ?></div>
                        <div class="mec-event-time mec-color"><?php if($this->include_events_times) {echo '<i class="mec-sl-clock"></i>'; echo $this->main->display_time($start_time, $end_time); } ?></div>
                    <?php endif; ?>
                    <?php if($this->localtime) echo $this->main->module('local-time.type2', array('event'=>$event)); ?>
                    <h4 class="mec-event-title"><a class="mec-color-hover" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a><?php echo $this->main->get_flags($event).$event_color.$this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event->data->ID, $reason_for_cancellation); ?></h4>
                    <?php if(isset($location['name'])): ?><div class="mec-event-detail"><i class="mec-sl-map-marker"></i> <?php echo (isset($location['name']) ? $location['name'] : ''); ?></div><?php endif; ?>
                <?php elseif($this->style == 'minimal'): ?>
                    <div class="col-md-9 col-sm-9">
                        <div class="mec-event-date mec-bg-color"><span><?php echo $this->main->date_i18n($this->date_format_minimal_1, strtotime($event->date['start']['date'])); ?></span><?php echo $this->main->date_i18n($this->date_format_minimal_2, strtotime($event->date['start']['date'])); ?></div>
                        <?php if($this->include_events_times) echo $this->main->display_time($start_time, $end_time); ?>
                        <h4 class="mec-event-title"><a class="mec-color-hover" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a><?php echo $this->main->get_flags($event).$event_color.$this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event->data->ID, $reason_for_cancellation); ?></h4>
                        <div class="mec-event-detail"><?php echo $this->main->date_i18n($this->date_format_minimal_3, strtotime($event->date['start']['date'])); ?><?php echo (isset($location['name']) ? ', ' . $location['name'] : ''); ?> <?php if($this->localtime) echo $this->main->module('local-time.type2', array('event'=>$event)); ?> </div>
                    </div>
                    <div class="col-md-3 col-sm-3 btn-wrapper"><?php do_action('before_mec_list_minimal_button',$event); ?><a class="mec-detail-button" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event, $event->date['start']['date']); ?>"><?php echo $this->main->m('event_detail', __('EVENT DETAIL', 'modern-events-calendar-lite')); ?></a></div>
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
                        <div class="col-md-3 mec-event-image-wrap mec-col-table-c">
                            <div class="mec-event-image"><a data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event, $event->date['start']['date']); ?>"><?php echo $event->data->thumbnails['thumblist']; ?></a></div>
                        </div>
                        <div class="col-md-6 mec-col-table-c mec-event-content-wrap">
                            <div class="mec-event-content">
                                <?php $soldout = $this->main->get_flags($event); ?>
                                <h3 class="mec-event-title"><a class="mec-color-hover" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a><?php echo $soldout.$event_color.$this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event->data->ID, $reason_for_cancellation); ?></h3>
                                <div class="mec-event-description"><?php echo $excerpt.(trim($excerpt) ? ' <span>...</span>' : ''); ?></div>
                            </div>
                        </div>
                        <div class="col-md-3 mec-col-table-c mec-event-meta-wrap">
                            <div class="mec-event-meta mec-color-before">
                                <div class="mec-date-details">
                                    <?php if(isset($settings['multiple_day_show_method']) && $settings['multiple_day_show_method'] == 'all_days') : ?>
                                        <span class="mec-event-d"><?php echo $this->main->date_i18n($this->date_format_standard_1, strtotime($event->date['start']['date'])); ?></span>
                                    <?php else: ?>
                                        <span class="mec-event-d"><?php echo $this->main->dateify($event, $this->date_format_standard_1); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php echo $this->main->display_time($start_time, $end_time); ?>
                                <?php if($this->localtime) echo $this->main->module('local-time.type1', array('event'=>$event)); ?>
                                <?php if(isset($location['name'])): ?>
                                <div class="mec-venue-details">
                                    <span><?php echo (isset($location['name']) ? $location['name'] : ''); ?></span><address class="mec-event-address"><span><?php echo (isset($location['address']) ? $location['address'] : ''); ?></span></address>
                                </div>
                                <?php endif; ?>
                                <?php if($this->display_price and isset($event->data->meta['mec_cost']) and $event->data->meta['mec_cost'] != ''): ?>
                                <div class="mec-price-details">
                                    <i class="mec-sl-wallet"></i>
                                    <span><?php echo (is_numeric($event->data->meta['mec_cost']) ? $this->main->render_price($event->data->meta['mec_cost']) : $event->data->meta['mec_cost']); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php do_action( 'mec_list_standard_right_box', $event); ?>
                            </div>
                        </div>
                    </div>
                    <div class="mec-event-footer">
                        <?php if(isset($settings['social_network_status']) and $settings['social_network_status'] != '0') : ?>
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
                        <?php do_action('mec_standard_booking_button' ,$event ); ?>
                        <a class="mec-booking-button" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event, $event->date['start']['date']); ?>"><?php echo (is_array($event->data->tickets) and count($event->data->tickets) and !strpos($soldout, '%%soldout%%')) ? $this->main->m('register_button', __('REGISTER', 'modern-events-calendar-lite')) : $this->main->m('view_detail', __('View Detail', 'modern-events-calendar-lite')); ?></a>
                    </div>
                <?php elseif($this->style == 'accordion'): ?>
                    <!-- toggles wrap start -->
                    <div class="mec-events-toggle">
                        <!-- toggle item start -->
                        <div class="mec-toggle-item">
                            <div class="mec-toggle-item-inner<?php if ( $this->toggle_month_divider == '1' ) echo ' mec-toogle-inner-month-divider'; ?>" tabindex="0" aria-controls="" aria-expanded="false">
                                <?php if ( $this->toggle_month_divider == '1' ) : ?>
                                <div class="mec-toggle-month-inner-image">
                                    <a href="<?php echo $this->main->get_event_date_permalink($event, $event->date['start']['date']); ?>"><?php echo $event->data->thumbnails['thumbnail']; ?></a>
                                </div>
                                <?php endif; ?>
                                <div class="mec-toggle-item-col">
                                    <?php if(isset($settings['multiple_day_show_method']) && $settings['multiple_day_show_method'] == 'all_days') : ?>
                                        <div class="mec-event-date"><?php echo $this->main->date_i18n($this->date_format_acc_1, strtotime($event->date['start']['date'])); ?></div>
                                        <div class="mec-event-month"><?php echo $this->main->date_i18n($this->date_format_acc_2, strtotime($event->date['start']['date'])); ?></div>
                                    <?php else: ?>
                                        <div class="mec-event-month"><?php echo $this->main->dateify($event, $this->date_format_acc_1.' '.$this->date_format_acc_2); ?></div>
                                    <?php endif; ?>
                                    <?php echo $this->main->display_time($start_time, $end_time); ?>
                                </div>
                                <h3 class="mec-toggle-title"><?php echo $event->data->title; ?><?php echo $this->main->get_flags($event).$event_color; ?></h3>
                                <?php if (!empty($label_style)) echo '<span class="mec-fc-style">'.$label_style.'</span>'; echo $this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event->data->ID, $reason_for_cancellation); ?><i class="mec-sl-arrow-down"></i>
                            </div>
                            <div class="mec-content-toggle" aria-hidden="true" style="display: none;">
                                <div class="mec-toggle-content">
                                    <?php echo $this->render->vsingle(array('id'=>$event->data->ID, 'layout'=>'m2')); ?>
                                </div>
                            </div>
                        </div><!-- toggle item end -->
                    </div><!-- toggles wrap end -->
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
    if((isset($_REQUEST['action']) and $_REQUEST['action'] == 'mec_list_load_more') and isset($_REQUEST['sf'])) $this->geolocation_focus = true;

    $map_javascript = '<script type="text/javascript">
    var mecmap'.$this->id.';
    jQuery(document).ready(function()
    {
        var jsonPush = gmapSkin('.json_encode($this->render->markers($map_events)).');
        mecmap'.$this->id.' = jQuery("#mec_googlemap_canvas'.$this->id.'").mecGoogleMaps(
        {
            id: "'.$this->id.'",
            autoinit: false,
            atts: "'.http_build_query(array('atts'=>$this->atts), '', '&').'",
            zoom: '.(isset($settings['google_maps_zoomlevel']) ? $settings['google_maps_zoomlevel'] : 14).',
            icon: "'.apply_filters('mec_marker_icon', $this->main->asset('img/m-04.png')).'",
            styles: '.((isset($settings['google_maps_style']) and trim($settings['google_maps_style']) != '') ? $this->main->get_googlemap_style($settings['google_maps_style']) : "''").',
            markers: jsonPush,
            clustering_images: "'.$this->main->asset('img/cluster1/m').'",
            getDirection: 0,
            ajax_url: "'.admin_url('admin-ajax.php', NULL).'",
            geolocation: "'.$this->geolocation.'",
            geolocation_focus: '.$this->geolocation_focus.'
        });
        
        var mecinterval'.$this->id.' = setInterval(function()
        {
            if(jQuery("#mec_googlemap_canvas'.$this->id.'").is(":visible"))
            {
                mecmap'.$this->id.'.init();
                clearInterval(mecinterval'.$this->id.');
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
    if($this->main->is_ajax()) echo $map_javascript;
    else $this->factory->params('footer', $map_javascript);
}