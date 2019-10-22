<?php
/** no direct access **/
defined('MECEXEC') or die();

$styling = $this->main->get_styling();
$event_colorskin = (isset($styling['mec_colorskin']) || isset($styling['color'])) ? 'colorskin-custom' : '';
$settings = $this->main->get_settings();
?>
<div class="mec-wrap <?php echo $event_colorskin; ?>">
    <div class="mec-event-masonry">
        <?php
        foreach($this->events as $date):
        foreach($date as $event):

        $location = isset($event->data->locations[$event->data->meta['mec_location_id']])? $event->data->locations[$event->data->meta['mec_location_id']] : array();
        $organizer = isset($event->data->organizers[$event->data->meta['mec_organizer_id']])? $event->data->organizers[$event->data->meta['mec_organizer_id']] : array();
        $event_color = isset($event->data->meta['mec_color']) ? '<span class="event-color" style="background: #'.$event->data->meta['mec_color'].'"></span>' : '';

        $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
        $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');

        $label_style = '';
        if(!empty($event->data->labels))
        {
        	foreach($event->data->labels as $label)
	        {
	            if(!isset($label['style']) or (isset($label['style']) and !trim($label['style']))) continue;
	            if($label['style'] == 'mec-label-featured') $label_style = esc_html__('Featured', 'modern-events-calendar-lite');
	            elseif($label['style'] == 'mec-label-canceled') $label_style = esc_html__('Canceled', 'modern-events-calendar-lite');
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
            $masonry_filter = '';
            if ( $this->filter_by == 'category' ) {
                if ( isset($event->data->categories) && !empty($event->data->categories) ) :
                    $masonry_filter = "[";
                    foreach ($event->data->categories as $key => $value) {
                        $masonry_filter .= '"' . $value['id'] . '",';
                    }
                    $masonry_filter .= "]";
                    $masonry_filter = str_replace(",]", "]", $masonry_filter);
                endif;
            } elseif ( $this->filter_by == 'label' ) {
                if ( isset($event->data->labels) && !empty($event->data->labels) ) :
                    $masonry_filter = "[";
                    foreach ($event->data->labels as $key => $value) {
                        $masonry_filter .= '"' . $value['id'] . '",';
                    }
                    $masonry_filter .= "]";
                    $masonry_filter = str_replace(",]", "]", $masonry_filter);
                endif;
            } elseif ( $this->filter_by == 'organizer' ) {
                if ( isset($event->data->organizers) && !empty($event->data->organizers) ) :
                    $masonry_filter = "[";
                    foreach ($event->data->organizers as $key => $value) {
                        $masonry_filter .= '"' . $value['id'] . '",';
                    }
                    $masonry_filter .= "]";
                    $masonry_filter = str_replace(",]", "]", $masonry_filter);
                endif;
            } elseif ( $this->filter_by == 'location' ) {
                if ( isset($event->data->locations) && !empty($event->data->locations) ) :
                    $masonry_filter = "[";
                    foreach ($event->data->locations as $key => $value) {
                        $masonry_filter .= '"' . $value['id'] . '",';
                    }
                    $masonry_filter .= "]";
                    $masonry_filter = str_replace(",]", "]", $masonry_filter);
                endif;
            }
            
            if ( empty($masonry_filter )) $masonry_filter = "[\"\"]";
            ?>
            <div data-sort-masonry="<?php echo $event->date['start']['date']; ?>" class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-masonry-item-wrap <?php echo $this->filter_by_classes($event->data->ID); ?>">
                <div class="mec-masonry">

                    <article data-style="<?php echo $label_style; ?>" class="mec-event-article mec-clear <?php echo $this->get_event_classes($event); ?>">
                        <?php if(isset($event->data->featured_image) and $this->masonry_like_grid ): ?>
                                <div class="mec-masonry-img" ><a href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo get_the_post_thumbnail($event->data->ID , 'thumblist'); ?></a></div>
                        <?php elseif( isset($event->data->featured_image) and isset($event->data->featured_image['full']) and trim($event->data->featured_image['full'])): ?>
                                <div class="mec-masonry-img" ><a href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo get_the_post_thumbnail($event->data->ID , 'full'); ?></a></div>
                        <?php endif; ?>

                        <div class="mec-masonry-content mec-event-grid-modern">
                            <div class="event-grid-modern-head clearfix">

                                <div class="mec-masonry-col<?php echo (isset($location['name']) and trim($location['name'])) ? '6' : '12'; ?>">
                                    <?php if(isset($settings['multiple_day_show_method']) and $settings['multiple_day_show_method'] == 'all_days') : ?>
                                        <div class="mec-event-date mec-color"><?php echo date_i18n($this->date_format_1, strtotime($event->date['start']['date'])); ?></div>
                                        <div class="mec-event-month"><?php echo date_i18n($this->date_format_2, strtotime($event->date['start']['date'])); ?></div>
                                    <?php else: ?>
                                        <div class="mec-event-date mec-color"><?php echo $this->main->date_label($event->date['start'], $event->date['end'], $this->date_format_1); ?></div>
                                        <div class="mec-event-month"><?php echo $this->main->date_label($event->date['start'], $event->date['end'], $this->date_format_2); ?></div>
                                    <?php endif; ?>
                                    <div class="mec-event-detail"><?php echo $start_time.(trim($end_time) ? ' - '.$end_time : ''); ?></div>
                                </div>

                                <?php if(isset($location['name']) and trim($location['name'])): ?>
                                <div class="mec-masonry-col6">
                                    <div class="mec-event-location">
                                        <i class="mec-sl-location-pin mec-color"></i>
                                        <div class="mec-event-location-det">
                                            <h6 class="mec-location"><?php echo (isset($location['name']) ? $location['name'] : ''); ?></h6>
                                            <address class="mec-events-address"><span class="mec-address"><?php echo (isset($location['address']) ? $location['address'] : ''); ?></span></address>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                            </div>
                            <?php do_action('print_extra_fields_masonry', $event); ?>
                            <?php
                                $excerpt = trim($event->data->post->post_excerpt) ? $event->data->post->post_excerpt : '';

                                // Safe Excerpt for UTF-8 Strings
                                if(!trim($excerpt))
                                {
                                    $excerpt_count  = apply_filters( 'MEC_masonry_excerpt', '9' );
                                    $ex = explode(' ', strip_tags(strip_shortcodes($event->data->post->post_content)));
                                    $words = array_slice($ex, 0, apply_filters( 'MEC_masonry_excerpt', '9' ));

                                    $excerpt = implode(' ', $words);
                                }
                            ?>
                            <div class="mec-event-content">
                                <h4 class="mec-event-title"><a class="mec-color-hover" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a><?php echo $event_color; ?></h4>
                                <div class="mec-event-description mec-events-content">
                                    <p><?php echo $excerpt.(trim($excerpt) ? ' ...' : ''); ?></p>
                                </div>
                            </div>
                            <div class="mec-event-footer">
                                <a class="mec-booking-button" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>" target="_self"><?php echo (is_array($event->data->tickets) and count($event->data->tickets)) ? $this->main->m('register_button', __('REGISTER', 'modern-events-calendar-lite')) : $this->main->m('view_detail', __('View Detail', 'modern-events-calendar-lite')) ; ?></a>
                                <?php do_action( 'mec_masonry_button', $event ); ?>
                            </div>
                        </div>
                    </article>

                </div>
            </div>
        <?php endforeach; ?>
        <?php endforeach; ?>
	</div>
</div>