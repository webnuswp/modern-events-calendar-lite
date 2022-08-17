<?php if($single->found_value('event_orgnizer', $settings) == 'on' || $single->found_value('register_btn', $settings) == 'on'): ?>
    <div class="mec-event-meta mec-color-before mec-frontbox <?php echo ((!$single->main->can_show_booking_module($event) and in_array($organizer_id, array('0', '1')) and !$more_info) ? 'mec-util-hidden' : ''); ?>">
        <?php
        // Event Organizer
        if($organizer_id and count($organizer) and $single->found_value('event_orgnizer', $settings) == 'on')
        {
            ?>
            <div class="mec-single-event-organizer">
                <?php if(isset($organizer['thumbnail']) and trim($organizer['thumbnail'])): ?>
                    <img class="mec-img-organizer" src="<?php echo esc_url($organizer['thumbnail']); ?>" alt="<?php echo (isset($organizer['name']) ? esc_attr($organizer['name']) : ''); ?>">
                <?php endif; ?>
                <h3 class="mec-events-single-section-title"><?php echo esc_html($single->main->m('taxonomy_organizer', esc_html__('Organizer', 'modern-events-calendar-lite'))); ?></h3>
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
            $single->show_other_organizers($event); // Show Additional Organizers
        }
        ?>

        <!-- Register Booking Button -->
        <?php if($single->main->can_show_booking_module($event) and $single->found_value('register_btn', $settings) == 'on'): ?>
            <?php
            $data_lity_class = '';
            $target_id = 'mec-events-meta-group-booking-'.$single->uniqueid;
            if(isset($settings['single_booking_style']) and $settings['single_booking_style'] == 'modal' ){
                $data_lity_class = 'mec-booking-data-lity';
                $target_id = 'mec-events-meta-group-booking-box-'.$single->uniqueid;
            }  ?>
            <a class="mec-booking-button mec-bg-color <?php echo esc_attr($data_lity_class); ?> <?php if(isset($settings['single_booking_style']) and $settings['single_booking_style'] != 'modal' ) echo 'simple-booking'; ?>" href="#<?php echo esc_attr($target_id); ?>"><?php echo esc_html($single->main->m('register_button', esc_html__('REGISTER', 'modern-events-calendar-lite'))); ?></a>
        <?php elseif($more_info): ?>
            <a class="mec-booking-button mec-bg-color" target="<?php echo esc_attr($more_info_target); ?>" href="<?php echo esc_url($more_info); ?>"><?php if($more_info_title) echo esc_html__($more_info_title, 'modern-events-calendar-lite'); else echo esc_html($single->main->m('register_button', esc_html__('REGISTER', 'modern-events-calendar-lite'))); ?></a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Speakers Module -->
<?php if($single->found_value('event_speakers', $settings) == 'on') echo MEC_kses::full($single->main->module('speakers.details', array('event' => $event))); ?>

<!-- Sponsors Module -->
<?php if($single->found_value('event_sponsors', $settings) == 'on') echo MEC_kses::full($single->main->module('sponsors.details', array('event' => $event))); ?>

<!-- Local Time Module -->
<?php if($single->found_value('local_time', $settings) == 'on') echo MEC_kses::full($single->main->module('local-time.details', array('event' => $event))); ?>

<?php if($single->found_value('event_location', $settings) == 'on' || $single->found_value('event_categories', $settings) == 'on' || $single->found_value('more_info', $settings) == 'on'): ?>
    <div class="mec-event-meta mec-color-before mec-frontbox <?php if((!count($location) or $single->found_value('event_location', $settings) == '') and (!isset($event->data->categories) or !is_array($event->data->categories) or !count($event->data->categories) or $single->found_value('event_categories', $settings) == '') and (!$more_info or $single->found_value('more_info', $settings) == '')) echo 'mec-util-hidden'; ?>">

        <?php
        // Event Location
        if($location_id and count($location) and $single->found_value('event_location', $settings) == 'on')
        {
            ?>
            <div class="mec-single-event-location">
                <?php if($location['thumbnail']): ?>
                    <img class="mec-img-location" src="<?php echo esc_url($location['thumbnail'] ); ?>" alt="<?php echo (isset($location['name']) ? esc_attr($location['name']) : ''); ?>">
                <?php endif; ?>
                <i class="mec-sl-location-pin"></i>
                <h3 class="mec-events-single-section-title mec-location"><?php echo esc_html($single->main->m('taxonomy_location', esc_html__('Location', 'modern-events-calendar-lite'))); ?></h3>
                <dl>
                    <dd class="author fn org"><?php echo MEC_kses::element($single->get_location_html($location)); ?></dd>
                    <dd class="location"><address class="mec-events-address"><span class="mec-address"><?php echo (isset($location['address']) ? esc_html($location['address']) : ''); ?></span></address></dd>

                    <?php if(isset($location['url']) and trim($location['url'])): ?>
                        <dd class="mec-location-url">
                            <i class="mec-sl-sitemap"></i>
                            <h6><?php esc_html_e('Website', 'modern-events-calendar-lite'); ?></h6>
                            <span><a href="<?php echo esc_url($location['url']); ?>" class="mec-color-hover" target="_blank"><?php echo esc_url($location['url']); ?></a></span>
                        </dd>
                    <?php endif;
                    $location_description_setting = isset( $settings['location_description'] ) ? $settings['location_description'] : ''; $location_terms = get_the_terms($event->data, 'mec_location'); if($location_description_setting == '1' and is_array($location_terms) and count($location_terms)): foreach($location_terms as $location_term) { if ($location_term->term_id == $location['id'] ) {  if(isset($location_term->description) && !empty($location_term->description)): ?>
                        <dd class="mec-location-description">
                            <p><?php echo esc_html($location_term->description);?></p>
                        </dd>
                    <?php endif; } } endif; ?>
                </dl>
            </div>
            <?php
            $single->show_other_locations($event); // Show Additional Locations
        }
        ?>

        <?php
        // Event Categories
        if(isset($event->data->categories) and !empty($event->data->categories) and $single->found_value('event_categories', $settings) == 'on')
        {
            ?>
            <div class="mec-single-event-category">
                <i class="mec-sl-folder"></i>
                <dt><?php echo esc_html($single->main->m('taxonomy_categories', esc_html__('Category', 'modern-events-calendar-lite'))); ?></dt>
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
        if($more_info and $single->found_value('more_info', $settings) == 'on')
        {
            ?>
            <div class="mec-event-more-info">
                <i class="mec-sl-info"></i>
                <h3 class="mec-cost"><?php echo esc_html($single->main->m('more_info_link', esc_html__('More Info', 'modern-events-calendar-lite'))); ?></h3>
                <dl><dd class="mec-events-event-more-info"><a class="mec-more-info-button mec-color-hover" target="<?php echo esc_attr($more_info_target); ?>" href="<?php echo esc_url($more_info); ?>"><?php echo esc_html($more_info_title); ?></a></dd></dl>
            </div>
            <?php
        }
        ?>

    </div>
<?php endif; ?>

<!-- Attendees List Module -->
<?php if($single->found_value('attende_module', $settings) == 'on') echo MEC_kses::full($single->main->module('attendees-list.details', array('event' => $event))); ?>

<!-- Next Previous Module -->
<?php if($single->found_value('next_module', $settings) == 'on') echo MEC_kses::full($single->main->module('next-event.details', array('event' => $event))); ?>

<!-- Weather Module -->
<?php if($single->found_value('weather_module', $settings) == 'on') echo MEC_kses::full($single->main->module('weather.details', array('event' => $event))); ?>

<!-- QRCode Module -->
<?php if($single->found_value('qrcode_module', $settings) == 'on') echo MEC_kses::full($single->main->module('qrcode.details', array('event' => $event))); ?>

<!-- Public Download Module -->
<?php if($single->found_value('public_download_module', $settings) == 'on') echo MEC_kses::full($single->display_public_download_module($event)); ?>

<!-- Custom Fields Module -->
<?php if($single->found_value('custom_fields_module', $settings) == 'on') echo MEC_kses::full($single->display_data_fields($event, true)); ?>