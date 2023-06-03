<?php

use MEC\SingleBuilder\Widgets\EventOrganizers\EventOrganizers;

/** @var MEC_single_widget $this */
/** @var MEC_skin_single $single */
/** @var stdClass $event */
/** @var array $occurrence_full */
/** @var array $occurrence_end_full */
/** @var string $cost */
/** @var string $more_info */
/** @var string $more_info_target */
/** @var string $more_info_title */
/** @var array $location */
/** @var int $location_id */
/** @var array $organizer */
/** @var int $organizer_id */

if($this->is_enabled('data_time') || $this->is_enabled('local_time') || $this->is_enabled('event_cost') || $this->is_enabled('more_info') || $this->is_enabled('event_label') || $this->is_enabled('event_location') || $this->is_enabled('event_categories') || $this->is_enabled('event_orgnizer') || $this->is_enabled('register_btn')): ?>
    <div class="mec-event-info-desktop mec-event-meta mec-color-before mec-frontbox">
        <?php
        // Event Date and Time
        if(isset($event->data->meta['mec_date']['start']) and !empty($event->data->meta['mec_date']['start']) and $this->is_enabled('data_time'))
        {
            $midnight_event = $single->main->is_midnight_event($event);
            ?>
            <div class="mec-single-event-date">
                <i class="mec-sl-calendar"></i>
                <h3 class="mec-date"><?php esc_html_e('Date', 'modern-events-calendar-lite' ); ?></h3>
                <dl>
                    <?php if($midnight_event): ?>
                        <dd><abbr class="mec-events-abbr"><?php echo MEC_kses::element($single->main->dateify($event, $single->date_format1)); ?></abbr></dd>
                    <?php else: ?>
                        <dd><abbr class="mec-events-abbr"><?php echo MEC_kses::element($single->main->date_label($occurrence_full, $occurrence_end_full, $single->date_format1, ' - ', true, 0, $event)); ?></abbr></dd>
                    <?php endif; ?>
                </dl>
                <?php echo MEC_kses::element($single->main->holding_status($event)); ?>
            </div>

            <?php do_action( 'mec_single_after_event_date', $event ) ?>

            <?php
            if(isset($event->data->meta['mec_hide_time']) and $event->data->meta['mec_hide_time'] == '0')
            {
                $time_comment = isset($event->data->meta['mec_comment']) ? $event->data->meta['mec_comment'] : '';
                $allday = isset($event->data->meta['mec_allday']) ? $event->data->meta['mec_allday'] : 0;
                ?>
                <div class="mec-single-event-time">
                    <i class="mec-sl-clock " style=""></i>
                    <h3 class="mec-time"><?php esc_html_e('Time', 'modern-events-calendar-lite' ); ?></h3>
                    <i class="mec-time-comment"><?php echo (isset($time_comment) ? esc_html($time_comment) : ''); ?></i>
                    <dl>
                        <?php if($allday == '0' and isset($event->data->time) and trim($event->data->time['start'])): ?>
                            <dd><abbr class="mec-events-abbr"><?php echo esc_html($event->data->time['start']); ?><?php echo esc_html(trim($event->data->time['end']) ? ' - '.esc_html($event->data->time['end']) : ''); ?></abbr></dd>
                        <?php else: ?>
                            <dd><abbr class="mec-events-abbr"><?php echo esc_html($single->main->m('all_day', esc_html__('All Day' , 'modern-events-calendar-lite' ))); ?></abbr></dd>
                        <?php endif; ?>
                    </dl>
                </div>
                <?php
            }
        }

        // Local Time Module
        if($this->is_enabled('local_time')) echo MEC_kses::full($single->main->module('local-time.details', array('event' => $event)));
        ?>

        <?php
        // Event Cost
        if($cost and $this->is_enabled('event_cost'))
        {
            ?>
            <div class="mec-event-cost">
                <i class="mec-sl-wallet"></i>
                <h3 class="mec-cost"><?php echo esc_html($single->main->m('cost', esc_html__('Cost', 'modern-events-calendar-lite' ))); ?></h3>
                <dl><dd class="mec-events-event-cost"><?php echo MEC_kses::element($cost); ?></dd></dl>
            </div>
            <?php
        }
        ?>

        <?php
        // More Info
        if($more_info and $this->is_enabled('more_info'))
        {
            ?>
            <div class="mec-event-more-info">
                <i class="mec-sl-info"></i>
                <h3 class="mec-cost"><?php echo esc_html($single->main->m('more_info_link', esc_html__('More Info', 'modern-events-calendar-lite' ))); ?></h3>
                <dl><dd class="mec-events-event-more-info"><a class="mec-more-info-button mec-color-hover" target="<?php echo esc_attr($more_info_target); ?>" href="<?php echo esc_url($more_info); ?>"><?php echo esc_html($more_info_title); ?></a></dd></dl>
            </div>
            <?php
        }
        ?>

        <?php
        // Event labels
        if(isset($event->data->labels) and !empty($event->data->labels) and $this->is_enabled('event_label'))
        {
            $mec_items = count($event->data->labels);
            $mec_i = 0; ?>
            <div class="mec-single-event-label">
                <i class="mec-fa-bookmark-o"></i>
                <h3 class="mec-cost"><?php echo esc_html($single->main->m('taxonomy_labels', esc_html__('Labels', 'modern-events-calendar-lite' ))); ?></h3>
                <?php foreach($event->data->labels as $labels=>$label) :
                    $seperator = (++$mec_i === $mec_items ) ? '' : ',';
                    echo '<dl><dd style="color:' . esc_attr($label['color']) . '">' . esc_html($label["name"] . $seperator) . '</dd></dl>';
                endforeach; ?>
            </div>
            <?php
        }
        ?>

        <?php do_action('mec_single_virtual_badge', $event->data ); ?>
        <?php do_action('mec_single_zoom_badge', $event->data ); ?>
        <?php do_action('mec_single_webex_badge', $event->data); ?>

        <?php
        // Event Location
        if($location_id and count($location) and $this->is_enabled('event_location'))
        {
            $single->display_location_widget($event); // Show Location Widget
            $single->show_other_locations($event); // Show Additional Locations
        }
        ?>

        <?php
        // Event Categories
        if(isset($event->data->categories) and !empty($event->data->categories) and $this->is_enabled('event_categories'))
        {
            ?>
            <div class="mec-single-event-category">
                <i class="mec-sl-folder"></i>
                <dt><?php echo esc_html($single->main->m('taxonomy_categories', esc_html__('Category', 'modern-events-calendar-lite' ))); ?></dt>
                <dl>
                <?php
                foreach($event->data->categories as $category)
                {
                    $color = ((isset($category['color']) and trim($category['color'])) ? $category['color'] : '');

                    $color_html = '';
                    if($color) $color_html .= '<span class="mec-event-category-color" style="--background-color: '.esc_attr($color).';background-color: '.esc_attr($color).'">&nbsp;</span>';

                    $icon = (isset($category['icon']) ? $category['icon'] : '');
                    $icon = isset($icon) && $icon != '' ? '<i class="' . esc_attr($icon) . ' mec-color"></i>' : '<i class="mec-fa-angle-right"></i>';

                    echo '<dd class="mec-events-event-categories"><a href="'.get_term_link($category['id'], 'mec_category').'" class="mec-color-hover" rel="tag">' . MEC_kses::element($icon . esc_html($category['name']) . $color_html) . '</a></dd>';
                }
                ?>
                </dl>
            </div>
            <?php
        }
        ?>
        <?php do_action('mec_single_event_under_category', $event); ?>
        <?php
        // Event Organizer
        if($organizer_id and count($organizer) and $this->is_enabled('event_orgnizer'))
        {
            ?>
            <div class="mec-single-event-organizer">
                <?php if(isset($organizer['thumbnail']) and trim($organizer['thumbnail'])): ?>
                    <img class="mec-img-organizer" src="<?php echo esc_url($organizer['thumbnail']); ?>" alt="<?php echo (isset($organizer['name']) ? esc_attr($organizer['name']) : ''); ?>">
                <?php endif; ?>
                <h3 class="mec-events-single-section-title"><?php echo esc_html($single->main->m('taxonomy_organizer', esc_html__('Organizer', 'modern-events-calendar-lite' ))); ?></h3>
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
                            <h6><?php esc_html_e('Phone', 'modern-events-calendar-lite' ); ?></h6>
                            <a href="tel:<?php echo esc_attr($organizer['tel']); ?>"><?php echo esc_html($organizer['tel']); ?></a>
                        </dd>
                    <?php endif;
                    if(isset($organizer['email']) && !empty($organizer['email'])): ?>
                        <dd class="mec-organizer-email">
                            <i class="mec-sl-envelope"></i>
                            <h6><?php esc_html_e('Email', 'modern-events-calendar-lite' ); ?></h6>
                            <a href="mailto:<?php echo esc_attr($organizer['email']); ?>"><?php echo esc_html($organizer['email']); ?></a>
                        </dd>
                    <?php endif;
                    if(isset($organizer['url']) && !empty($organizer['url'])): ?>
                        <dd class="mec-organizer-url">
                            <i class="mec-sl-sitemap"></i>
                            <h6><?php esc_html_e('Website', 'modern-events-calendar-lite' ); ?></h6>
                            <span><a href="<?php echo esc_url($organizer['url']); ?>" class="mec-color-hover" target="_blank"><?php echo (isset($organizer['page_label']) and trim($organizer['page_label'])) ? esc_html($organizer['page_label']) : esc_html($organizer['url']); ?></a></span>
                            <?php do_action('mec_single_default_organizer', $organizer); ?>
                        </dd>
                    <?php endif;
                    $organizer_description_setting = isset( $settings['organizer_description'] ) ? $settings['organizer_description'] : ''; $organizer_terms = get_the_terms($event->data, 'mec_organizer'); if($organizer_description_setting == '1' and is_array($organizer_terms) and count($organizer_terms)): foreach($organizer_terms as $organizer_term) { if ($organizer_term->term_id == $organizer['id'] ) {  if(isset($organizer_term->description) && !empty($organizer_term->description)): ?>
                        <dd class="mec-organizer-description"><p><?php echo esc_html($organizer_term->description);?></p></dd>
                    <?php endif; } } endif; ?>
                </dl>
                <?php EventOrganizers::display_social_links( $organizer_id ); ?>
            </div>
            <?php
            $single->show_other_organizers($event); // Show Additional Organizers
        }
        ?>

        <!-- Register Booking Button -->
        <?php if($single->main->can_show_booking_module($event) and $this->is_enabled('register_btn')): ?>
            <?php $data_lity_class = ''; if(isset($settings['single_booking_style']) and $settings['single_booking_style'] == 'modal' ){ $data_lity_class = 'mec-booking-data-lity'; }  ?>
            <a class="mec-booking-button mec-bg-color <?php echo esc_attr($data_lity_class); ?> <?php if(isset($settings['single_booking_style']) and $settings['single_booking_style'] != 'modal' ) echo 'simple-booking'; ?>" href="#mec-events-meta-group-booking-<?php echo esc_attr($single->uniqueid); ?>"><?php echo esc_html($single->main->m('register_button', esc_html__('REGISTER', 'modern-events-calendar-lite' ))); ?></a>
        <?php elseif($this->is_enabled('register_btn') == 'on' and $more_info and !$single->main->is_expired($event)): ?>
            <a class="mec-booking-button mec-bg-color" target="<?php echo esc_attr($more_info_target); ?>" href="<?php echo esc_url($more_info); ?>"><?php if($more_info_title) echo esc_html__($more_info_title, 'modern-events-calendar-lite' ); else echo esc_html($single->main->m('register_button', esc_html__('REGISTER', 'modern-events-calendar-lite' ))); ?></a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Speakers Module -->
<?php if($this->is_enabled('event_speakers')) echo MEC_kses::full($single->main->module('speakers.details', array('event' => $event))); ?>

<!-- Sponsors Module -->
<?php if($this->is_enabled('event_sponsors')) echo MEC_kses::full($single->main->module('sponsors.details', array('event' => $event))); ?>

<!-- Attendees List Module -->
<?php if($this->is_enabled('attende_module')) echo MEC_kses::full($single->main->module('attendees-list.details', array('event' => $event))); ?>

<!-- Next Previous Module -->
<?php if($this->is_enabled('next_module')) echo MEC_kses::full($single->main->module('next-event.details', array('event' => $event))); ?>

<!-- Links Module -->
<?php if($this->is_enabled('links_module')) echo MEC_kses::full($single->main->module('links.details', array('event' => $event))); ?>

<!-- Weather Module -->
<?php if($this->is_enabled('weather_module')) echo MEC_kses::full($single->main->module('weather.details', array('event' => $event))); ?>

<!-- Google Maps Module -->
<?php if($this->is_enabled('google_map')): ?>
<div class="mec-events-meta-group mec-events-meta-group-gmap">
    <?php echo MEC_kses::full($single->main->module('googlemap.details', array('event' => $single->events))); ?>
</div>
<?php endif; ?>

<!-- QRCode Module -->
<?php if($this->is_enabled('qrcode_module')) echo MEC_kses::full($single->main->module('qrcode.details', array('event' => $event))); ?>

<!-- Public Download Module -->
<?php if($this->is_enabled('public_download_module')) $single->display_public_download_module($event); ?>

<!-- Custom Fields Module -->
<?php if($this->is_enabled('custom_fields_module')) $single->display_data_fields($event, true); ?>