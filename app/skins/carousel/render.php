<?php
/** no direct access **/
defined('MECEXEC') or die();

$styling = $this->main->get_styling();
$event_colorskin = (isset($styling['mec_colorskin']) or isset($styling['color'])) ? 'colorskin-custom' : '';
$settings = $this->main->get_settings();
?>
<div class="mec-wrap <?php echo $event_colorskin; ?>">
    <div class="mec-event-carousel-<?php echo $this->style; ?>">
        <?php 
            if( $this->style == 'type4' ) 
            {
                $carousel_type = 'type4' ;
            } 
            elseif ( $this->style == 'type1' )
            {
                $carousel_type = 'type1' ;
            } 
            else
            {
                $carousel_type = 'type2' ;
            }
        ?>
        <div class='mec-owl-crousel-skin-<?php echo $carousel_type; ?> mec-owl-carousel mec-owl-theme'>
            <?php
                foreach($this->events as $date):
                foreach($date as $event):

                // Skip to next event if there is no image
                // if(empty($event->data->thumbnails['meccarouselthumb'])) continue;

                $location = isset($event->data->locations[$event->data->meta['mec_location_id']])? $event->data->locations[$event->data->meta['mec_location_id']] : array();
                $organizer = isset($event->data->organizers[$event->data->meta['mec_organizer_id']])? $event->data->organizers[$event->data->meta['mec_organizer_id']] : array();
                $event_color = isset($event->data->meta['mec_color']) ? '<span class="event-color" style="background: #'.$event->data->meta['mec_color'].'"></span>' : '';
                $event_date = (isset($event->date['start']) ? $event->date['start']['date'] : $event->data->meta['mec_start_date']);

                $label_style = '';
                if(!empty($event->data->labels))
                {
                    foreach($event->data->labels as $label)
                    {
                        if(!isset($label['style']) or (isset($label['style']) and !trim($label['style']))) continue;
                        if($label['style']  == 'mec-label-featured') $label_style = esc_html__('Featured', 'modern-events-calendar-lite');
                        elseif($label['style'] == 'mec-label-canceled') $label_style = esc_html__('Canceled', 'modern-events-calendar-lite');
                    }
                }

                $speakers = '""';
                if(!empty($event->data->speakers))
                {
                    $speakers = [];
                    foreach($event->data->speakers as $key=>$value)
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
            ?>
            <article data-style="<?php echo $label_style; ?>" class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-event-article mec-clear <?php echo $this->get_event_classes($event); ?>" itemscope>
            <?php
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
                if($this->style == 'type1'):  ?>
                <div class="event-carousel-type1-head clearfix">
                    <div class="mec-event-date mec-color">
                        <div class="mec-event-image">
                        <?php 
                            if ($event->data->thumbnails['meccarouselthumb']) {
                                echo $event->data->thumbnails['meccarouselthumb'];
                            } else {
                                echo '<img src="'. plugin_dir_url(__FILE__ ) .'../../../assets/img/no-image.png'.'" />';
                            }
                        ?>
                        </div>
                        <div class="mec-event-date-carousel">
                            <?php echo date_i18n($this->date_format_type1_1, strtotime($event->date['start']['date'])); ?>
                            <div class="mec-event-date-info"><?php echo date_i18n($this->date_format_type1_2, strtotime($event->date['start']['date'])); ?></div>
                            <div class="mec-event-date-info-year"><?php echo date_i18n($this->date_format_type1_3, strtotime($event->date['start']['date'])); ?></div>
                        </div>
                    </div>
                </div>
                <div class="mec-event-carousel-content">
                    <h4 class="mec-event-carousel-title"><a class="mec-color-hover" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a></h4>
                    <p><?php echo (isset($location['name']) ? $location['name'] : ''); echo (isset($location['address']) ? '<br>'.$location['address'] : ''); ?></p>
                </div>
                <?php elseif($this->style == 'type2'): ?>
                <div class="event-carousel-type2-head clearfix">
                    <div class="mec-event-image">
                        <?php 
                            if ($event->data->thumbnails['meccarouselthumb']) {
                                echo $event->data->thumbnails['meccarouselthumb'];
                            } else {
                                echo '<img src="'. plugin_dir_url(__FILE__ ) .'../../../assets/img/no-image.png'.'" />';
                            }
                        ?>
                    </div>
                    <div class="mec-event-carousel-content-type2">
                        <?php if(isset($settings['multiple_day_show_method']) && $settings['multiple_day_show_method'] == 'all_days') : ?>
                            <span class="mec-event-date-info"><?php echo date_i18n($this->date_format_type2_1, strtotime($event->date['start']['date'])); ?></span>
                        <?php else: ?>
                            <span class="mec-event-date-info"><?php echo $this->main->date_label($event->date['start'], $event->date['end'], $this->date_format_type2_1); ?></span>
                        <?php endif; ?>
                        <?php do_action('mec_carousel_type2_before_title' , $event); ?>
                        <h4 class="mec-event-carousel-title"><a class="mec-color-hover" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a></h4>
                        <?php do_action('mec_carousel_type2_after_title' , $event); ?>
                        <p><?php echo (isset($location['name']) ? $location['name'] : ''); echo (isset($location['address']) ? '<br>'.$location['address'] : ''); ?></p>
                    </div>
                    <div class="mec-event-footer-carousel-type2">
                    <?php if($settings['social_network_status'] != '0') : ?>
                        <ul class="mec-event-sharing-wrap">
                            <li class="mec-event-share">
                                <a href="#" class="mec-event-share-icon">
                                    <i class="mec-sl-share mec-bg-color-hover mec-border-color-hover"></i>
                                </a>
                            </li>
                            <li>
                                <ul class="mec-event-sharing">
                                    <?php echo $this->main->module('links.list', array('event'=>$event)); ?>
                                </ul>
                            </li>
                        </ul>
                    <?php endif; ?>
                        <a class="mec-booking-button mec-bg-color-hover mec-border-color-hover" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>" target="_self"><?php echo (is_array($event->data->tickets) and count($event->data->tickets)) ? $this->main->m('register_button', __('REGISTER', 'modern-events-calendar-lite')) : $this->main->m('view_detail', __('View Detail', 'modern-events-calendar-lite')) ; ?></a>
                    </div>
                </div>
                <?php elseif($this->style == 'type3'): ?>
                <div class="event-carousel-type3-head clearfix">
                    <div class="mec-event-image">
                        <?php 
                            if ($event->data->thumbnails['meccarouselthumb']) {
                                echo $event->data->thumbnails['meccarouselthumb'];
                            } else {
                                echo '<img src="'. plugin_dir_url(__FILE__ ) .'../../../assets/img/no-image.png'.'" />';
                            }
                        ?>
                    </div>
                    <div class="mec-event-footer-carousel-type3">
                        <?php if(isset($settings['multiple_day_show_method']) && $settings['multiple_day_show_method'] == 'all_days') : ?>
                            <div class="mec-event-date-info"><?php echo date_i18n($this->date_format_type3_1, strtotime($event->date['start']['date'])); ?></div>
                        <?php else: ?>
                            <span class="mec-event-date-info"><?php echo $this->main->date_label($event->date['start'], $event->date['end'], $this->date_format_type3_1); ?></span>
                        <?php endif; ?>
                        <h4 class="mec-event-carousel-title"><a class="mec-color-hover" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a></h4>
                        <p><?php echo (isset($location['name']) ? $location['name'] : ''); echo (isset($location['address']) ? '<br>'.$location['address'] : ''); ?></p>
                        <?php if($settings['social_network_status'] != '0') : ?>
                            <ul class="mec-event-sharing-wrap">
                                <li class="mec-event-share">
                                    <a href="#" class="mec-event-share-icon">
                                        <i class="mec-sl-share mec-bg-color-hover mec-border-color-hover"></i>
                                    </a>
                                </li>
                                <li>
                                    <ul class="mec-event-sharing">
                                        <?php echo $this->main->module('links.list', array('event'=>$event)); ?>
                                    </ul>
                                </li>
                            </ul>
                        <?php endif; ?>
                        <a class="mec-booking-button mec-bg-color-hover mec-border-color-hover" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>" target="_self"><?php echo (is_array($event->data->tickets) and count($event->data->tickets)) ? $this->main->m('register_button', __('REGISTER', 'modern-events-calendar-lite')) : $this->main->m('view_detail', __('View Detail', 'modern-events-calendar-lite')) ; ?></a>
                    </div>
                </div>
                <?php elseif($this->style == 'type4'): ?>
                <div class="event-carousel-type4-head clearfix">
                    <div class="mec-event-image">
                        <?php 
                            if ($event->data->thumbnails['full']) {
                                echo $event->data->thumbnails['full'];
                            } else {
                                echo '<img src="'. plugin_dir_url(__FILE__ ) .'../../../assets/img/no-image.png'.'" />';
                            }
                        ?>
                    </div>
                    <div class="mec-event-overlay"></div>
                    <div class="mec-event-hover-carousel-type4">
                        <i class="mec-event-icon mec-bg-color mec-fa-calendar"></i>
                        <div class="mec-event-date">
                            <span class="mec-color"><?php echo date_i18n('F d', strtotime($event_date)); ?></span> <?php echo date_i18n('l', strtotime($event_date)); ?>
                        </div>
                        <h4 class="mec-event-title"><?php echo $event->data->title . $event_color; ?><?php if ( !empty($label_style) ) echo '<span class="mec-fc-style">'.$label_style.'</span>'; ?></h4>
                        <div class="mec-btn-wrapper"><a class="mec-event-button" href="<?php echo $this->main->get_event_date_permalink($event->data->permalink, $event->date['start']['date']); ?>"><?php echo $this->main->m('event_detail', __('EVENT DETAIL', 'modern-events-calendar-lite')); ?></a></div>
                    </div>
                </div>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
        <?php if($this->style == 'type4'):  ?>
        <div class="row mec-carousel-type4-head">
            <div class="col-md-6 col-xs-6">
                <div class="mec-carousel-type4-head-link">
                    <?php if(!empty( $this->archive_link )) : ?><a class="mec-bg-color-hover" href="<?php echo esc_html($this->archive_link); ?>"><?php esc_html_e('View All Events' , 'modern-events-calendar-lite'); ?></a><?php endif; ?>
                </div>
            </div>
            <div class="col-md-6 col-xs-6">
                <div class="mec-carousel-type4-head-title">
                    <?php if(!empty( $this->head_text )) : ?><?php esc_html_e($this->head_text); ?><?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
	</div>
</div>