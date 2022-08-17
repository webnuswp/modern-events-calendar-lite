<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_skin_single $this */
/** @var boolean $fes */

$booking_options = get_post_meta($event->data->ID, 'mec_booking', true);
if(!is_array($booking_options)) $booking_options = array();

$event_link = MEC_feature_occurrences::param($event->ID, $event->date['start']['timestamp'], 'read_more', (isset($event->data->meta['mec_read_more']) ? $event->data->meta['mec_read_more'] : ''));

$more_info = (isset($event->data->meta['mec_more_info']) and trim($event->data->meta['mec_more_info']) and $event->data->meta['mec_more_info'] != 'http://') ? $event->data->meta['mec_more_info'] : '';
if(isset($event->date) and isset($event->date['start']) and isset($event->date['start']['timestamp'])) $more_info = MEC_feature_occurrences::param($event->ID, $event->date['start']['timestamp'], 'more_info', $more_info);

$more_info_target = MEC_feature_occurrences::param($event->ID, $event->date['start']['timestamp'], 'more_info_target', (isset($event->data->meta['mec_more_info_target']) ? $event->data->meta['mec_more_info_target'] : '_self'));
$more_info_title = MEC_feature_occurrences::param($event->ID, $event->date['start']['timestamp'], 'more_info_title', ((isset($event->data->meta['mec_more_info_title']) and trim($event->data->meta['mec_more_info_title'])) ? $event->data->meta['mec_more_info_title'] : esc_html__('Read More', 'modern-events-calendar-lite')));

$location_id = $this->main->get_master_location_id($event);
$location = ($location_id ? $this->main->get_location_data($location_id) : array());

$organizer_id = $this->main->get_master_organizer_id($event);
$organizer = ($organizer_id ? $this->main->get_organizer_data($organizer_id) : array());
?>
<div class="mec-wrap <?php echo esc_attr($event_colorskin); ?> clearfix <?php echo esc_attr($this->html_class); ?> mec-modal-wrap" id="mec_skin_<?php echo esc_attr($this->uniqueid); ?>" data-unique-id="<?php echo esc_attr($this->uniqueid); ?>">
    <article class="mec-single-event mec-single-modern mec-single-modal">
        <div class="mec-events-event-image">
            <?php echo MEC_kses::element($event->data->thumbnails['full']); ?>
            <?php if(isset($settings['featured_image_caption']) and $settings['featured_image_caption']) echo MEC_kses::element($this->main->display_featured_image_caption($event)); ?>
        </div>

        <div class="col-md-4">

            <div class="mec-event-meta mec-color-before mec-frontbox <?php echo ((!$this->main->can_show_booking_module($event) and in_array($organizer_id, array('0', '1')) and !$more_info) ? 'mec-util-hidden' : ''); ?>">
                <?php
                // Event Organizer
                if($organizer_id and count($organizer))
                {
                    ?>
                    <div class="mec-single-event-organizer">
                        <?php if(isset($organizer['thumbnail']) and trim($organizer['thumbnail'])): ?>
                            <img class="mec-img-organizer" src="<?php echo esc_url($organizer['thumbnail']); ?>" alt="<?php echo (isset($organizer['name']) ? esc_attr($organizer['name']) : ''); ?>">
                        <?php endif; ?>
                        <h3 class="mec-events-single-section-title"><?php echo esc_html($this->main->m('taxonomy_organizer', esc_html__('Organizer', 'modern-events-calendar-lite'))); ?></h3>
                        <dl>
                        <?php if(isset($organizer['thumbnail'])): ?>
                        <dd class="mec-organizer">
                            <i class="mec-sl-home"></i>
                            <h6><?php echo (isset($organizer['name']) ? esc_html($organizer['name']) : ''); ?></h6>
                        </dd>
                        <?php endif;
                        if(isset($organizer['tel']) && !empty($organizer['tel'])): ?>
                        <dd class="mec-organizer-tel">
                            <i class="mec-sl-phone"></i>
                            <h6><?php esc_html_e('Phone', 'modern-events-calendar-lite'); ?></h6>
                            <a href="tel:<?php echo esc_attr($organizer['tel']); ?>"><?php echo esc_html($organizer['tel']); ?></a>
                        </dd>
                        <?php endif;
                        if(isset($organizer['email']) && !empty($organizer['email'])): ?>
                        <dd class="mec-organizer-email">
                            <i class="mec-sl-envelope"></i>
                            <h6><?php esc_html_e('Email', 'modern-events-calendar-lite'); ?></h6>
                            <a href="mailto:<?php echo esc_attr($organizer['email']); ?>"><?php echo esc_html($organizer['email']);; ?></a>
                        </dd>
                        <?php endif;
                        if(isset($organizer['url']) && !empty($organizer['url']) and $organizer['url'] != 'http://'): ?>
                        <dd class="mec-organizer-url">
                            <i class="mec-sl-sitemap"></i>
                            <h6><?php esc_html_e('Website', 'modern-events-calendar-lite'); ?></h6>
                            <span><a href="<?php echo esc_url($organizer['url']); ?>" class="mec-color-hover" target="_blank"><?php echo (isset($organizer['page_label']) and trim($organizer['page_label'])) ? esc_html($organizer['page_label']) : esc_html($organizer['url']); ?></a></span>
                        </dd>
                        <?php endif;
                        $organizer_description_setting = isset($settings['organizer_description']) ? $settings['organizer_description'] : ''; $organizer_terms = get_the_terms($event->data, 'mec_organizer'); if($organizer_description_setting == '1' and is_array($organizer_terms) and count($organizer_terms)): foreach($organizer_terms as $organizer_term) { if ($organizer_term->term_id == $organizer['id'] ) {  if(isset($organizer_term->description) && !empty($organizer_term->description)): ?>
                        <dd class="mec-organizer-description">
                            <p><?php echo esc_html($organizer_term->description); ?></p>
                        </dd>
                        <?php endif; } } endif; ?>
                        </dl>
                    </div>
                    <?php
                    $this->show_other_organizers($event); // Show Additional Organizers
                }
                ?>

                <!-- Sponsors Module -->
                <?php echo MEC_kses::full($this->main->module('sponsors.details', array('event' => $event))); ?>

                <!-- Register Booking Button -->
                <?php if($this->main->can_show_booking_module($event)): ?>
                    <a class="mec-booking-button mec-bg-color" href="#mec-events-meta-group-booking-<?php echo esc_attr($this->uniqueid); ?>"><?php echo esc_html($this->main->m('register_button', esc_html__('REGISTER', 'modern-events-calendar-lite'))); ?></a>
                <?php elseif($event_link): ?>
                    <a class="mec-booking-button mec-bg-color" target="_blank" href="<?php echo esc_url($event_link); ?>"><?php echo esc_html($this->main->m('register_button', esc_html__('REGISTER', 'modern-events-calendar-lite'))); ?></a>
                <?php elseif($more_info): ?>
                    <a class="mec-booking-button mec-bg-color" target="<?php echo esc_attr($more_info_target); ?>" href="<?php echo esc_url($more_info); ?>"><?php if($more_info_title) echo esc_html__($more_info_title, 'modern-events-calendar-lite'); else echo esc_html($this->main->m('register_button', esc_html__('REGISTER', 'modern-events-calendar-lite'))); ?></a>
                <?php endif; ?>
            </div>

            <!-- Weather Module -->
            <?php echo MEC_kses::full($this->main->module('weather.details', array('event' => $event))); ?>

            <!-- QRCode Module -->
            <?php echo MEC_kses::full($this->main->module('qrcode.details', array('event' => $event))); ?>

            <!-- Public Download Module -->
            <?php $this->display_public_download_module($event); ?>

            <!-- Local Time Module -->
            <?php echo MEC_kses::full($this->main->module('local-time.details', array('event' => $event))); ?>

            <div class="mec-event-meta mec-color-before mec-frontbox">

                <?php do_action('mec_single_virtual_badge', $event->data ); ?>
                <?php do_action('mec_single_zoom_badge', $event->data ); ?>
                <?php do_action('mec_single_webex_badge', $event->data); ?>

                <?php
                // Event Location
                if(count($location))
                {
                    ?>
                    <div class="mec-single-event-location">
                        <?php if($location['thumbnail']): ?>
                        <img class="mec-img-location" src="<?php echo esc_url($location['thumbnail'] ); ?>" alt="<?php echo (isset($location['name']) ? esc_attr($location['name']) : ''); ?>">
                        <?php endif; ?>
                        <i class="mec-sl-location-pin"></i>
                        <h3 class="mec-events-single-section-title mec-location"><?php echo esc_html($this->main->m('taxonomy_location', esc_html__('Location', 'modern-events-calendar-lite'))); ?></h3>
                        <dl>
                        <dd class="author fn org"><?php echo MEC_kses::element($this->get_location_html($location)); ?></dd>
                        <dd class="location"><address class="mec-events-address"><span class="mec-address"><?php echo (isset($location['address']) ? esc_html($location['address']) : ''); ?></span></address></dd>

                        <?php if(isset($location['url']) and trim($location['url'])): ?>
                        <dd class="mec-location-url">
                            <i class="mec-sl-sitemap"></i>
                            <h6><?php esc_html_e('Website', 'modern-events-calendar-lite'); ?></h6>
                            <span><a href="<?php echo esc_url($location['url']); ?>" class="mec-color-hover" target="_blank"><?php echo esc_html($location['url']); ?></a></span>
                        </dd>
                        <?php endif;
                        $location_description_setting = isset($settings['location_description']) ? $settings['location_description'] : ''; $location_terms = get_the_terms($event->data, 'mec_location');  if($location_description_setting == '1' and is_array($location_terms) and count($location_terms)): foreach($location_terms as $location_term) { if ($location_term->term_id == $location['id'] ) {  if(isset($location_term->description) && !empty($location_term->description)): ?>
                        <dd class="mec-location-description">
                            <p><?php echo esc_html($location_term->description); ?></p>
                        </dd>
                        <?php endif; } } endif; ?>
                        </dl>
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
                        <dt><?php echo esc_html($this->main->m('taxonomy_categories', esc_html__('Category', 'modern-events-calendar-lite'))); ?></dt>
                        <?php
                            foreach($event->data->categories as $category)
                            {
                                $color = ((isset($category['color']) and trim($category['color'])) ? $category['color'] : '');

                                $color_html = '';
                                if($color) $color_html .= '<span class="mec-event-category-color" style="--background-color: '.esc_attr($color).';background-color: '.esc_attr($color).'">&nbsp;</span>';

                                $icon = (isset($category['icon']) ? $category['icon'] : '');
                                $icon = isset($icon) && $icon != '' ? '<i class="' . esc_attr($icon) . ' mec-color"></i>' : '<i class="mec-fa-angle-right"></i>';

                                echo '<dl><dd class="mec-events-event-categories"><a href="'.get_term_link($category['id'], 'mec_category').'" class="mec-color-hover" rel="tag">' . MEC_kses::element($icon . esc_html($category['name']) . $color_html) . '</a></dd></dl>';
                            }
                        ?>
                    </div>
                    <?php
                }
                ?>

                <?php
                    // More Info
                    if($more_info)
                    {
                        ?>
                        <div class="mec-event-more-info">
                            <i class="mec-sl-info"></i>
                            <h3 class="mec-cost"><?php echo esc_html($this->main->m('more_info_link', esc_html__('More Info', 'modern-events-calendar-lite'))); ?></h3>
                            <dd class="mec-events-event-more-info"><a class="mec-more-info-button mec-color-hover" target="<?php echo esc_attr($more_info_target); ?>" href="<?php echo esc_url($more_info); ?>"><?php echo esc_html($more_info_title); ?></a></dd>
                        </div>
                        <?php
                    }
                ?>

            </div>

            <!-- Speaker List Module -->
            <?php

            if(!empty($this->main->module('speakers.details', array('event' => $event)))) {
                ?>
                <div class="mec-color-before mec-frontbox">
                    <?php echo MEC_kses::full($this->main->module('speakers.details', array('event' => $event))); ?>
                </div>
                <?php
            }
            ?>

            <!-- Attendees List Module -->
            <?php echo MEC_kses::full($this->main->module('attendees-list.details', array('event' => $event))); ?>

            <!-- Next Previous Module -->
            <?php echo MEC_kses::full($this->main->module('next-event.details', array('event' => $event))); ?>
        </div>

        <div class="col-md-8">

            <div class="mec-event-content">
                <div class="mec-single-event-description mec-events-content">
                    <?php echo MEC_kses::full($this->main->get_post_content($event)); ?>
                    <?php do_action('mec_accordion_skin_after_content', $event); ?>
                    <?php echo MEC_kses::element($this->display_disclaimer($event)); ?>
                </div>
            </div>

            <?php do_action('mec_single_after_content', $event); ?>

            <!-- Custom Data Fields -->
            <?php $this->display_data_fields($event); ?>

            <!-- Links Module -->
            <?php echo MEC_kses::full($this->main->module('links.details', array('event' => $event))); ?>

            <!-- Google Maps Module -->
            <div class="mec-events-meta-group mec-events-meta-group-gmap">
                <?php echo MEC_kses::full($this->main->module('googlemap.details', array('event' => $this->events, 'auto_init'=>false, 'uniqueid' => $this->uniqueid))); ?>
            </div>

            <!-- Export Module -->
            <?php echo MEC_kses::full($this->main->module('export.details', array('event' => $event))); ?>

            <!-- Hourly Schedule -->
            <?php $this->display_hourly_schedules_widget($event); ?>

            <?php do_action( 'mec_before_booking_form', get_the_ID() ); ?>
			<!-- Booking Module -->
            <?php if($this->main->is_sold($event) and count($event->dates) <= 1): ?>
            <div id="mec-events-meta-group-booking-<?php echo esc_attr($this->uniqueid); ?>"class="mec-sold-tickets warning-msg"><?php esc_html_e('Sold out!', 'modern-events-calendar-lite'); do_action( 'mec_booking_sold_out',$event, null,null,array($event->date) );?> </div>
            <?php elseif($this->main->can_show_booking_module($event)): ?>
            <div id="mec-events-meta-group-booking-<?php echo esc_attr($this->uniqueid); ?>" class="mec-events-meta-group mec-events-meta-group-booking">
                <?php
                if(isset($settings['booking_user_login']) and $settings['booking_user_login'] == '1' and !is_user_logged_in() ) {
                    echo do_shortcode('[MEC_login]');
                } elseif ( isset($settings['booking_user_login']) and $settings['booking_user_login'] == '0' and !is_user_logged_in() and isset($booking_options['bookings_limit_for_users']) and $booking_options['bookings_limit_for_users'] == '1' ) {
                    echo do_shortcode('[MEC_login]');
                } else {
                    echo MEC_kses::full($this->main->module('booking.default', array('event' => $this->events, 'uniqueid' => $this->uniqueid)));
                }
                ?>
            </div>
            <?php endif ?>

            <!-- Tags -->
            <div class="mec-events-meta-group mec-events-meta-group-tags">
                <?php echo get_the_term_list(get_the_ID(), apply_filters('mec_taxonomy_tag', ''), esc_html__('Tags: ', 'modern-events-calendar-lite'), ', ', '<br />'); ?>
            </div>

        </div>
    </article>
</div>
<script>
// Fix modal speaker in some themes
jQuery("#mec_skin_<?php echo esc_attr($this->uniqueid); ?> .mec-speaker-avatar a").on('click', function(e)
{
    e.preventDefault();

    var id = jQuery(this).attr('href');
    lity(id);
});
</script>