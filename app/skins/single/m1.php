<?php
/** no direct access **/
defined('MECEXEC') or die();
?>
<div class="mec-wrap <?php echo $event_colorskin; ?> clearfix <?php echo $this->html_class; ?> mec-modal-wrap" id="mec_skin_<?php echo $this->uniqueid; ?>">
    <article class="mec-single-event mec-single-modern mec-single-modal">
        <h1 class="mec-single-title"><?php echo get_the_title($event->data->ID); ?></h1>
        <div class="mec-single-event-bar">
            <?php
            // Event Date and Time
            if(isset($event->data->meta['mec_date']['start']) and !empty($event->data->meta['mec_date']['start']))
            {
                ?>
                <div class="mec-single-event-date">
                    <i class="mec-sl-calendar"></i>
                    <h3 class="mec-date"><?php _e('Date', 'modern-events-calendar-lite'); ?></h3>
                    <dd><abbr class="mec-events-abbr"><?php echo $this->main->date_label((trim($occurrence) ? array('date'=>$occurrence) : $event->date['start']), (trim($occurrence_end_date) ? array('date'=>$occurrence_end_date) : (isset($event->date['end']) ? $event->date['end'] : NULL)), $this->date_format1); ?></abbr></dd>
                </div>

                <?php  
                if(isset($event->data->meta['mec_hide_time']) and $event->data->meta['mec_hide_time'] == '0')
                {
                    $time_comment = isset($event->data->meta['mec_comment']) ? $event->data->meta['mec_comment'] : '';
                    $allday = isset($event->data->meta['mec_allday']) ? $event->data->meta['mec_allday'] : 0;
                    ?>
                    <div class="mec-single-event-time">
                        <i class="mec-sl-clock " style=""></i>
                        <h3 class="mec-time"><?php _e('Time', 'modern-events-calendar-lite'); ?></h3>
                        <i class="mec-time-comment"><?php echo (isset($time_comment) ? $time_comment : ''); ?></i>

                        <?php if($allday == '0' and isset($event->data->time) and trim($event->data->time['start'])): ?>
                            <dd><abbr class="mec-events-abbr"><?php echo $event->data->time['start']; ?><?php echo (trim($event->data->time['end']) ? ' - '.$event->data->time['end'] : ''); ?></abbr></dd>
                        <?php else: ?>
                            <dd><abbr class="mec-events-abbr"><?php _e('All of the day', 'modern-events-calendar-lite'); ?></abbr></dd>
                        <?php endif; ?>
                    </div>
                    <?php
                }
            }
            ?>

            <?php
            // Event Cost
            if(isset($event->data->meta['mec_cost']) and $event->data->meta['mec_cost'] != '')
            {
                ?>
                <div class="mec-event-cost">
                    <i class="mec-sl-wallet"></i>
                    <h3 class="mec-cost"><?php echo $this->main->m('cost', __('Cost', 'modern-events-calendar-lite')); ?></h3>
                    <dd class="mec-events-event-cost"><?php echo (is_numeric($event->data->meta['mec_cost']) ? $this->main->render_price($event->data->meta['mec_cost']) : $event->data->meta['mec_cost']); ?></dd>
                </div>
                <?php
            }
            ?>

            <?php
            // Event labels
            if(isset($event->data->labels) && !empty($event->data->labels))
            {
                $mec_items = count($event->data->labels);
                $mec_i = 0; ?>
                <div class="mec-single-event-label">
                    <i class="mec-fa-bookmark-o"></i>
                    <h3 class="mec-cost"><?php echo $this->main->m('taxonomy_labels', __('Labels', 'modern-events-calendar-lite')); ?></h3>
                    <?php foreach($event->data->labels as $labels=>$label) : 
                    $seperator = (++$mec_i === $mec_items ) ? '' : ',';
                    echo '<dd style=color:"' . $label['color'] . '">' . $label["name"] . $seperator . '</dd>';
                    endforeach; ?>
                </div>
                <?php
            }
            ?>
        </div>

        <div class="mec-events-event-image"><?php echo $event->data->thumbnails['full']; ?></div>

        <div class="col-md-4">
            
            <div class="mec-event-meta mec-color-before mec-frontbox <?php echo ((!$this->main->can_show_booking_module($event) and in_array($event->data->meta['mec_organizer_id'], array('0', '1'))) ? 'mec-util-hidden' : '') ; ?>">
                <?php
                // Event Organizer
                if(isset($event->data->organizers[$event->data->meta['mec_organizer_id']]) && !empty($event->data->organizers[$event->data->meta['mec_organizer_id']]))
                {
                    $organizer = $event->data->organizers[$event->data->meta['mec_organizer_id']];
                    ?>
                    <div class="mec-single-event-organizer">
                        <?php if(isset($organizer['thumbnail']) and trim($organizer['thumbnail'])): ?>
                            <img class="mec-img-organizer" src="<?php echo esc_url($organizer['thumbnail']); ?>" alt="<?php echo (isset($organizer['name']) ? $organizer['name'] : ''); ?>">
                        <?php endif; ?>
                        <h3 class="mec-events-single-section-title"><?php echo $this->main->m('taxonomy_organizer', __('Organizer', 'modern-events-calendar-lite')); ?></h3>
                        <?php if(isset($organizer['thumbnail'])): ?>
                        <dd class="mec-organizer">
                            <i class="mec-sl-home"></i>
                            <h6><?php echo (isset($organizer['name']) ? $organizer['name'] : ''); ?></h6>
                        </dd>
                        <?php endif;
                        if(isset($organizer['tel']) && !empty($organizer['tel'])): ?>
                        <dd class="mec-organizer-tel">
                            <i class="mec-sl-phone"></i>
                            <h6><?php _e('Phone', 'modern-events-calendar-lite'); ?></h6>
                            <a href="tel:<?php echo $organizer['tel']; ?>"><?php echo $organizer['tel']; ?></a>
                        </dd>
                        <?php endif; 
                        if(isset($organizer['email']) && !empty($organizer['email'])): ?>
                        <dd class="mec-organizer-email">
                            <i class="mec-sl-envelope"></i>
                            <h6><?php _e('Email', 'modern-events-calendar-lite'); ?></h6>
                            <a href="mailto:<?php echo $organizer['email']; ?>"><?php echo $organizer['email']; ?></a>
                        </dd>
                        <?php endif;
                        if(isset($organizer['url']) && !empty($organizer['url']) and $organizer['url'] != 'http://'): ?>
                        <dd class="mec-organizer-url">
                            <i class="mec-sl-sitemap"></i>
                            <h6><?php _e('Website', 'modern-events-calendar-lite'); ?></h6>
                            <span><a href="<?php echo (strpos($organizer['url'], 'http') === false ? 'http://'.$organizer['url'] : $organizer['url']); ?>" class="mec-color-hover" target="_blank"><?php echo $organizer['url']; ?></a></span>
                        </dd>
                        <?php endif; ?>
                    </div>
                    <?php
                    $this->show_other_organizers($event); // Show Additional Organizers
                }
                ?>

                <!-- Register Booking Button -->
                <?php if($this->main->can_show_booking_module($event)): ?>
                    <a class="mec-booking-button mec-bg-color" href="#mec-events-meta-group-booking-<?php echo $this->uniqueid; ?>"><?php echo esc_html($this->main->m('register_button', __('REGISTER', 'modern-events-calendar-lite'))); ?></a>
                <?php elseif(isset($event->data->meta['mec_more_info']) and trim($event->data->meta['mec_more_info']) and $event->data->meta['mec_more_info'] != 'http://'): ?>
                    <a class="mec-booking-button mec-bg-color" href="<?php echo $event->data->meta['mec_more_info']; ?>"><?php if(isset($event->data->meta['mec_more_info_title']) and trim($event->data->meta['mec_more_info_title'])) echo esc_html(trim($event->data->meta['mec_more_info_title']), 'modern-events-calendar-lite'); else echo esc_html($this->main->m('register_button', __('REGISTER', 'modern-events-calendar-lite')));
                     ?></a>
                <?php endif; ?>
            </div>

            <!-- Weather Module -->
            <?php echo $this->main->module('weather.details', array('event'=>$event)); ?>
            
            <!-- QRCode Module -->
            <?php echo $this->main->module('qrcode.details', array('event'=>$event)); ?>

            <!-- Local Time Module -->
            <?php echo $this->main->module('local-time.details', array('event'=>$event)); ?>
            
            <div class="mec-event-meta mec-color-before mec-frontbox">
                
                <?php
                // Event Location
                if(isset($event->data->locations[$event->data->meta['mec_location_id']]) and !empty($event->data->locations[$event->data->meta['mec_location_id']]))
                {
                    $location = $event->data->locations[$event->data->meta['mec_location_id']];
                    ?>
                    <div class="mec-single-event-location">
                        <?php if($location['thumbnail']): ?>
                        <img class="mec-img-location" src="<?php echo esc_url($location['thumbnail'] ); ?>" alt="<?php echo (isset($location['name']) ? $location['name'] : ''); ?>">
                        <?php endif; ?>
                        <i class="mec-sl-location-pin"></i>
                        <h3 class="mec-events-single-section-title mec-location"><?php echo $this->main->m('taxonomy_location', __('Location', 'modern-events-calendar-lite')); ?></h3>
                        <dd class="author fn org"><?php echo (isset($location['name']) ? $location['name'] : ''); ?></dd>
                        <dd class="location"><address class="mec-events-address"><span class="mec-address"><?php echo (isset($location['address']) ? $location['address'] : ''); ?></span></address></dd>
                    </div>
                    <?php
                }
                ?>

                <?php
                // Event Categories
                if(isset($event->data->categories) and !empty($event->data->categories))
                {
                    ?>
                    <div class="mec-single-event-category">
                        <i class="mec-sl-folder"></i>
                        <dt><?php echo $this->main->m('taxonomy_categories', __('Category', 'modern-events-calendar-lite')); ?></dt>
                        <?php
                        foreach($event->data->categories as $category)
                        {
                            $icon = get_metadata('term', $category['id'], 'mec_cat_icon', true);
                            $icon = isset($icon) && $icon != '' ? '<i class="'.$icon.' mec-color"></i>' : '<i class="mec-fa-angle-right"></i>';
                            echo '<dd class="mec-events-event-categories">
                            <a href="'.get_term_link($category['id'], 'mec_category').'" class="mec-color-hover" rel="tag">'.$icon . $category['name'] .'</a></dd>';
                        }
                        ?>
                    </div>
                    <?php
                }
                ?>  
                <?php
                    // More Info
                    if(isset($event->data->meta['mec_more_info']) and trim($event->data->meta['mec_more_info']) and $event->data->meta['mec_more_info'] != 'http://')
                    {
                        ?>
                        <div class="mec-event-more-info">
                            <i class="mec-sl-info"></i>
                            <h3 class="mec-cost"><?php echo $this->main->m('more_info_link', __('More Info', 'modern-events-calendar-lite')); ?></h3>
                            <dd class="mec-events-event-more-info"><a class="mec-more-info-button mec-color-hover" target="<?php echo (isset($event->data->meta['mec_more_info_target']) ? $event->data->meta['mec_more_info_target'] : '_self'); ?>" href="<?php echo $event->data->meta['mec_more_info']; ?>"><?php echo ((isset($event->data->meta['mec_more_info_title']) and trim($event->data->meta['mec_more_info_title'])) ? $event->data->meta['mec_more_info_title'] : __('Read More', 'modern-events-calendar-lite')); ?></a></dd>
                        </div>
                        <?php
                    }
                ?>
                
            </div>

            <?php
            
            if(!empty($this->main->module('speakers.details', array('event'=>$event)))) {
                ?>
                <div class="mec-color-before mec-frontbox">
                    <?php echo $this->main->module('speakers.details', array('event'=>$event)); ?>
                </div>
                <?php
            }
            ?>

            <!-- Attendees List Module -->
            <?php echo $this->main->module('attendees-list.details', array('event'=>$event)); ?>
            
            <!-- Next Previous Module -->
            <?php echo $this->main->module('next-event.details', array('event'=>$event)); ?>                
        </div>

        <div class="col-md-8">

            <div class="mec-event-content">
                <div class="mec-single-event-description mec-events-content"><?php echo $this->main->get_post_content($event->data->ID); ?></div>
            </div>

            <!-- Links Module -->
            <?php echo $this->main->module('links.details', array('event'=>$event)); ?>

            <!-- Google Maps Module -->
            <div class="mec-events-meta-group mec-events-meta-group-gmap">
                <?php echo $this->main->module('googlemap.details', array('event'=>$this->events)); ?>
            </div>
            
            <!-- Export Module -->
            <?php echo $this->main->module('export.details', array('event'=>$event)); ?>

            <!-- Countdown module -->
            <?php if($this->main->can_show_countdown_module($event)): ?>
            <div class="mec-events-meta-group mec-events-meta-group-countdown">
                <?php echo $this->main->module('countdown.details', array('event'=>$this->events)); ?>
            </div> 
            <?php endif; ?>

            <!-- Hourly Schedule -->
            <?php $this->display_hourly_schedules_widget($event); ?>

            <!-- Booking Module -->
            <?php if($this->main->is_sold($event, (trim($occurrence) ? $occurrence : $event->date['start']['date'])) and count($event->dates) <= 1): ?>
            <div class="mec-sold-tickets warning-msg"><?php _e('Sold out!', 'wpl'); ?></div>
            <?php elseif($this->main->can_show_booking_module($event)): ?>
            <div id="mec-events-meta-group-booking-<?php echo $this->uniqueid; ?>" class="mec-events-meta-group mec-events-meta-group-booking">
                <?php echo $this->main->module('booking.default', array('event'=>$this->events)); ?>
            </div>
            <?php endif ?>

            <!-- Tags -->
            <div class="mec-events-meta-group mec-events-meta-group-tags">
                <?php the_tags(__('Tags: ', 'modern-events-calendar-lite'), ', ', '<br />'); ?>
            </div>
        </div>
    </article>
</div>
<script>
jQuery( ".mec-speaker-avatar a" ).click(function(e) {
    e.preventDefault();
    var id =  jQuery(this).attr('href');
    var instance = lity(id);
    jQuery(document).on('lity:close', function(event, instance) {
        jQuery( ".mec-hourly-schedule-speaker-info" ).addClass('lity-hide');
    });
});


</script>