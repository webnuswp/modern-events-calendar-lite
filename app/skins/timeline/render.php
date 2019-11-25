<?php
/** no direct access **/
defined('MECEXEC') or die();

$current_month_divider = $this->request->getVar('current_month_divider', 0);
$settings = $this->main->get_settings();
$styling = $this->main->get_styling();
$event_colorskin = (isset($styling['mec_colorskin']) || isset($styling['color'])) ? 'colorskin-custom' : '';
?>
<div class="mec-events-timeline-wrap mec-wrap <?php echo $event_colorskin; ?>">
<?php foreach($this->events as $date=>$events): ?>

    <?php $month_id = date('Ym', strtotime($date)); if($this->month_divider and $month_id != $current_month_divider): $current_month_divider = $month_id; ?>
        <div class="mec-timeline-month-divider"><span><?php echo date_i18n('F Y', strtotime($date)); ?></span></div>
    <?php endif; ?>

    <div class="mec-timeline-events-container">
        <?php
            foreach($events as $event)
            {
                $location = isset($event->data->locations[$event->data->meta['mec_location_id']]) ? $event->data->locations[$event->data->meta['mec_location_id']] : array();
                $organizer = isset($event->data->organizers[$event->data->meta['mec_organizer_id']]) ? $event->data->organizers[$event->data->meta['mec_organizer_id']] : array();
                $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
                $event_color = isset($event->data->meta['mec_color']) ? '<span class="event-color" style="background: #'.$event->data->meta['mec_color'].'"></span>' : '';
                $excerpt = trim($event->data->post->post_excerpt) ? $event->data->post->post_excerpt : '';
                        
                // Safe Excerpt for UTF-8 Strings
                if(!trim($excerpt))
                {
                    $ex = explode(' ', strip_tags(strip_shortcodes($event->data->post->post_content)));
                    $words = array_slice($ex, 0, 16);
                    
                    $excerpt = implode(' ', $words);
                }
                $label_style = '';
                if ( !empty($event->data->labels) ):
                foreach( $event->data->labels as $label)
                {
                    if(!isset($label['style']) or (isset($label['style']) and !trim($label['style']))) continue;
                    if ( $label['style']  == 'mec-label-featured' )
                    {
                        $label_style = esc_html__( 'Featured' , 'modern-events-calendar-lite' );
                    } 
                    elseif ( $label['style']  == 'mec-label-canceled' )
                    {
                        $label_style = esc_html__( 'Canceled' , 'modern-events-calendar-lite' );
                    }
                }
                endif;
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
                $schema_settings = isset( $settings['schema'] ) ? $settings['schema'] : '';
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
                <?php
                endif;
                ?>
                <div class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-timeline-event clearfix <?php echo $this->get_event_classes($event); ?>">
                    <div class="mec-timeline-event-date mec-color<?php echo ($event->date['start']['date'] != $event->date['end']['date']) ? ' mec-timeline-dates' : '' ; ?>"><?php echo ( $event->date['start']['date'] == $event->date['end']['date'] ) ? date_i18n( get_option( 'date_format' ), strtotime($event->date['start']['date'])) : date_i18n( get_option( 'date_format' ), strtotime($event->date['start']['date'])) . '<br>' . date_i18n( get_option( 'date_format' ), strtotime($event->date['end']['date'])) ; ?> </div>
                    <div class="mec-timeline-event-content">
                        <div class="clearfix">
                            <div class="mec-timeline-right-content">
                                <div class="mec-timeline-event-image"><a data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo $event->data->thumbnails['thumblist']; ?></a></div>
                            </div>
                            <div class="mec-timeline-left-content">
                                <div class="mec-timeline-main-content">
                                    <h4 class="mec-event-title"><a data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>" class="mec-color-hover"><?php echo $event->data->title; ?></a><?php echo $event_color; ?><?php if (!empty($label_style)) echo '<span class="mec-fc-style">'.$label_style.'</span>'; ?></h4>
                                    <p><?php echo $excerpt.(trim($excerpt) ? ' ...' : ''); ?></p>
                                    <div class="mec-timeline-event-details">
                                        <div class="mec-timeline-event-time mec-color">
                                            <i class="mec-sl-clock"></i><?php echo $this->main->mec_include_time_labels($start_time, $end_time); ?>
                                        </div>
                                    </div>
                                    <?php if(!empty($location['address'])): ?>
                                    <div class="mec-timeline-event-details">
                                        <div class="mec-timeline-event-location mec-color">
                                            <address class="mec-timeline-event-address"><i class="mec-sl-location-pin"></i><span><?php echo (isset($location['address']) ? $location['address'] : ''); ?></span></address>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <a class="mec-booking-button mec-timeline-readmore mec-bg-color" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo (is_array($event->data->tickets) and count($event->data->tickets)) ? $this->main->m('register_button', __('Register for event', 'modern-events-calendar-lite')) : $this->main->m('view_detail', __('View Details', 'modern-events-calendar-lite')); ?><i class="mec-sl-arrow-right"></i></a>
                    </div>
                </div>
                
        <?php } ?>
    </div>
    
<?php endforeach; ?>
</div>