<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC events class.
 *
 * @author Webnus <info@webnus.biz>
 */
class MEC_feature_events extends MEC_base
{
    public $factory;
    public $main;
    public $db;
    public $PT;
    public $settings;
    public $render;
    /**
     * Constructor method
     *
     * @author Webnus <info@webnus.biz>
     */
    public function __construct()
    {
        // Import MEC Factory
        $this->factory = $this->getFactory();

        // Import MEC Main
        $this->main = $this->getMain();

        // Import MEC DB
        $this->db = $this->getDB();

        // MEC Post Type Name
        $this->PT = $this->main->get_main_post_type();

        // MEC Settings
        $this->settings = $this->main->get_settings();
    }

    /**
     * Initialize events feature
     *
     * @author Webnus <info@webnus.biz>
     */
    public function init()
    {
        $this->factory->action('init', array($this, 'register_post_type'));
        $this->factory->action('mec_category_add_form_fields', array($this, 'add_category_custom_icon'), 10, 2);
        $this->factory->action('mec_category_edit_form_fields', array($this, 'edit_category_custom_icon'), 10, 2);
        $this->factory->action('edited_mec_category', array($this, 'save_metadata'));
        $this->factory->action('created_mec_category', array($this, 'save_metadata'));

        $this->factory->action('init', array($this, 'register_endpoints'));
        $this->factory->action('add_meta_boxes_' . $this->PT, array($this, 'remove_taxonomies_metaboxes'));
        $this->factory->action('save_post', array($this, 'save_event'), 10);
        $this->factory->action('delete_post', array($this, 'delete_event'), 10);
        $this->factory->action('transition_post_status', array($this, 'event_published'), 10 , 3);

        $this->factory->filter('post_row_actions', array($this, 'action_links'), 10, 2);
        $this->factory->action('init', array($this, 'duplicate_event'));

        $this->factory->action('add_meta_boxes', array($this, 'register_meta_boxes'), 1);
        $this->factory->action('restrict_manage_posts', array($this, 'add_filters'));
        $this->factory->action('pre_get_posts', array($this, 'sort'));

        $this->factory->action('mec_metabox_details', array($this, 'meta_box_nonce'), 10);
        $this->factory->action('mec_metabox_details', array($this, 'meta_box_dates'), 20);
        $this->factory->action('mec_metabox_details', array($this, 'meta_box_hourly_schedule'), 30);
        $this->factory->action('mec_metabox_details', array($this, 'meta_box_links'), 40);
        $this->factory->action('mec_metabox_details', array($this, 'meta_box_cost'), 50);

        // Hourly Schedule for FES
        if(!isset($this->settings['fes_section_hourly_schedule']) or (isset($this->settings['fes_section_hourly_schedule']) and $this->settings['fes_section_hourly_schedule']))
        {
            $this->factory->action('mec_fes_metabox_details', array($this, 'meta_box_hourly_schedule'), 30);
        }

        // Show exceptional days if enabled
        if(isset($this->settings['exceptional_days']) and $this->settings['exceptional_days'])
        {
            $this->factory->action('mec_metabox_details', array($this, 'meta_box_exceptional_days'), 25);
            $this->factory->action('mec_fes_metabox_details', array($this, 'meta_box_exceptional_days'), 25);
        }

        // Show Booking meta box only if booking module is enabled
        $booking_status = (isset($this->settings['booking_status']) and $this->settings['booking_status']) ? true : false;
        if($booking_status)
        {
            $this->factory->action('mec_metabox_booking', array($this, 'meta_box_booking_options'), 5);
            $this->factory->action('mec_metabox_booking', array($this, 'meta_box_tickets'), 10);
            $this->factory->action('mec_metabox_booking', array($this, 'meta_box_regform'), 20);

            // Booking Options for FES
            if(!isset($this->settings['fes_section_booking']) or (isset($this->settings['fes_section_booking']) and $this->settings['fes_section_booking']))
            {
                $this->factory->action('mec_fes_metabox_details', array($this, 'meta_box_booking_options'), 35);
                $this->factory->action('mec_fes_metabox_details', array($this, 'meta_box_tickets'), 40);
                $this->factory->action('mec_fes_metabox_details', array($this, 'meta_box_regform'), 45);
            }
        }

        // Show fees meta box only if fees module is enabled
        if(isset($this->settings['taxes_fees_status']) and $this->settings['taxes_fees_status'])
        {
            $this->factory->action('mec_metabox_booking', array($this, 'meta_box_fees'), 15);

            // Fees for FES
            if($booking_status and (!isset($this->settings['fes_section_fees']) or (isset($this->settings['fes_section_fees']) and $this->settings['fes_section_fees'])))
            {
                $this->factory->action('mec_fes_metabox_details', array($this, 'meta_box_fees'), 45);
            }
        }

        // Show ticket variations meta box only if the module is enabled
        if(isset($this->settings['ticket_variations_status']) and $this->settings['ticket_variations_status'])
        {
            $this->factory->action('mec_metabox_booking', array($this, 'meta_box_ticket_variations'), 16);

            // Ticket Variations for FES
            if($booking_status and (!isset($this->settings['fes_section_ticket_variations']) or (isset($this->settings['fes_section_ticket_variations']) and $this->settings['fes_section_ticket_variations'])))
            {
                $this->factory->action('mec_fes_metabox_details', array($this, 'meta_box_ticket_variations'), 46);
            }
        }

        $this->factory->filter('manage_' . $this->PT . '_posts_columns', array($this, 'filter_columns'));
        $this->factory->filter('manage_edit-' . $this->PT . '_sortable_columns', array($this, 'filter_sortable_columns'));
        $this->factory->action('manage_' . $this->PT . '_posts_custom_column', array($this, 'filter_columns_content'), 10, 2);

        $this->factory->action('admin_footer-edit.php', array($this, 'add_bulk_actions'));
        $this->factory->action('load-edit.php', array($this, 'do_bulk_actions'));
        $this->factory->action('pre_post_update', array($this, 'bulk_edit'), 10);
    }

    /**
     * Registers events post type and assign it to some taxonomies
     *
     * @author Webnus <info@webnus.biz>
     */
    public function register_post_type()
    {
        // Get supported features for event post type
        $supports = apply_filters('mec_event_supports', array('editor', 'title', 'excerpt', 'author', 'thumbnail', 'comments'));

        register_post_type(
            $this->PT,
            array(
                'labels' => array(
                    'name' => __('Events', 'modern-events-calendar-lite'),
                    'singular_name' => __('Event', 'modern-events-calendar-lite'),
                    'add_new' => __('Add Event', 'modern-events-calendar-lite'),
                    'add_new_item' => __('Add New Event', 'modern-events-calendar-lite'),
                    'not_found' => __('No events found!', 'modern-events-calendar-lite'),
                    'all_items' => __('All Events', 'modern-events-calendar-lite'),
                    'edit_item' => __('Edit Event', 'modern-events-calendar-lite'),
                    'view_item' => __('View Event', 'modern-events-calendar-lite'),
                    'not_found_in_trash' => __('No events found in Trash!', 'modern-events-calendar-lite'),
                ),
                'public' => true,
                'has_archive' => ($this->main->get_archive_status() ? true : false),
                'menu_icon' => plugin_dir_url(__FILE__ ) . '../../assets/img/mec.svg',
                'menu_position' => 26,
                'show_in_menu' => 'mec-intro',
                'rewrite' => array(
                    'slug' => $this->main->get_main_slug(),
                    'ep_mask' => EP_MEC_EVENTS,
                ),
                'supports' => $supports,
                'show_in_rest' => true,

            )
        );

        $singular_label = $this->main->m('taxonomy_category', __('Category', 'modern-events-calendar-lite'));
        $plural_label = $this->main->m('taxonomy_categories', __('Categories', 'modern-events-calendar-lite'));

        register_taxonomy(
            'mec_category',
            $this->PT,
            array(
                'label' => $plural_label,
                'labels' => array(
                    'name' => $plural_label,
                    'singular_name' => $singular_label,
                    'all_items' => sprintf(__('All %s', 'modern-events-calendar-lite'), $plural_label),
                    'edit_item' => sprintf(__('Edit %s', 'modern-events-calendar-lite'), $singular_label),
                    'view_item' => sprintf(__('View %s', 'modern-events-calendar-lite'), $singular_label),
                    'update_item' => sprintf(__('Update %s', 'modern-events-calendar-lite'), $singular_label),
                    'add_new_item' => sprintf(__('Add New %s', 'modern-events-calendar-lite'), $singular_label),
                    'new_item_name' => sprintf(__('New %s Name', 'modern-events-calendar-lite'), $singular_label),
                    'popular_items' => sprintf(__('Popular %s', 'modern-events-calendar-lite'), $plural_label),
                    'search_items' => sprintf(__('Search %s', 'modern-events-calendar-lite'), $plural_label),
                ),
                'public' => true,
                'show_ui' => true,
                'show_in_rest' => true,
                'hierarchical' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => $this->main->get_category_slug()),
            )
        );

        register_taxonomy_for_object_type('mec_category', $this->PT);
        register_taxonomy_for_object_type('post_tag', $this->PT);
    }

    /**
     * Register meta field to taxonomies
     *
     * @author Webnus <info@webnus.biz>
     */
    public function add_category_custom_icon()
    {
        add_thickbox();
        ?>
        <div class="form-field">
            <label for="mec_cat_icon"><?php _e('Category Icon', 'modern-events-calendar-lite'); ?></label>
            <input type="hidden" name="mec_cat_icon" id="mec_cat_icon" value=""/>
            <a href="<?php echo $this->main->asset('icon.html'); ?>"
               class="thickbox mec_category_icon button"><?php echo __('Select icon', 'modern-events-calendar-lite'); ?></a>
        </div>

        <?php
    }

    /**
     * Edit icon meta for categories
     *
     * @author Webnus <info@webnus.biz>
     */
    public function edit_category_custom_icon($term)
    {
        add_thickbox();
        $icon = get_metadata('term', $term->term_id, 'mec_cat_icon', true);
        ?>
        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="mec_cat_icon"><?php _e('Category Icon', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <input type="hidden" name="mec_cat_icon" id="mec_cat_icon" value="<?php echo $icon; ?>"/>
                <a href="<?php echo $this->main->asset('icon.html'); ?>"
                   class="thickbox mec_category_icon button"><?php echo __('Select icon', 'modern-events-calendar-lite'); ?></a>
                <?php if (isset($icon)) : ?>
                    <div class="mec-webnus-icon"><i class="<?php echo $icon; ?> mec-color"></i></div>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }

    /**
     * Save meta data for mec categories
     *
     * @author Webnus <info@webnus.biz>
     * @param int $term_id
     */
    public function save_metadata($term_id)
    {
        $icon = isset($_POST['mec_cat_icon']) ? sanitize_text_field($_POST['mec_cat_icon']) : '';

        update_term_meta($term_id, 'mec_cat_icon', $icon);
    }

    public function register_endpoints()
    {
        add_rewrite_endpoint('verify', EP_MEC_EVENTS);
        add_rewrite_endpoint('cancel', EP_MEC_EVENTS);
        add_rewrite_endpoint('gateway-cancel', EP_MEC_EVENTS);
        add_rewrite_endpoint('gateway-return', EP_MEC_EVENTS);
    }

    /**
     * Remove normal meta boxes for some taxonomies
     *
     * @author Webnus <info@webnus.biz>
     */
    public function remove_taxonomies_metaboxes()
    {
        remove_meta_box('tagsdiv-mec_location', $this->PT, 'side');
        remove_meta_box('tagsdiv-mec_organizer', $this->PT, 'side');
        remove_meta_box('tagsdiv-mec_label', $this->PT, 'side');
    }

    /**
     * Registers 2 meta boxes for event data
     *
     * @author Webnus <info@webnus.biz>
     */
    public function register_meta_boxes()
    {
        add_meta_box('mec_metabox_details', __('Event Details', 'modern-events-calendar-lite'), array($this, 'meta_box_details'), $this->main->get_main_post_type(), 'normal', 'high');

        // Show Booking meta box onnly if booking module is enabled
        if($this->getPRO() and isset($this->settings['booking_status']) and $this->settings['booking_status'])
        {
            add_meta_box('mec_metabox_booking', __('Booking', 'modern-events-calendar-lite'), array($this, 'meta_box_booking'), $this->main->get_main_post_type(), 'normal', 'high');
        }
    }

    /**
     * Show content of details meta box
     *
     * @author Webnus <info@webnus.biz>
     * @param object $post
     */
    public function meta_box_details($post)
    {
        global $post;
        $note = get_post_meta($post->ID, 'mec_note', true);
        $note_visibility = $this->main->is_note_visible($post->post_status);

        $fes_guest_email = get_post_meta($post->ID, 'fes_guest_email', true);
        $fes_guest_name = get_post_meta($post->ID, 'fes_guest_name', true);
    ?>
        <div class="mec-add-event-tabs-wrap">
            <div class="mec-add-event-tabs-left">
                <?php if ( ($note_visibility and trim($note)) || (trim($fes_guest_email) and trim($fes_guest_name)) ) : ?>
                <a class="mec-add-event-tabs-link" data-href="mec_meta_box_fes_form" href="#"><?php echo esc_html('FES Details' ,'modern-events-calendar-lite'); ?></a>
                <?php endif; ?>
                <a class="mec-add-event-tabs-link" data-href="mec_meta_box_date_form" href="#"><?php echo esc_html('Date And Time' ,'modern-events-calendar-lite'); ?></a>
                <a class="mec-add-event-tabs-link" data-href="mec_meta_box_repeat_form" href="#"><?php echo esc_html('Event Repeating' ,'modern-events-calendar-lite'); ?></a>
                <?php if(isset($this->settings['exceptional_days']) and $this->settings['exceptional_days']) : ?>
                <a class="mec-add-event-tabs-link" data-href="mec-exceptional-days" href="#"><?php echo esc_html('Exceptional Days' ,'modern-events-calendar-lite'); ?></a>
                <?php endif; ?>
                <a class="mec-add-event-tabs-link" data-href="mec-hourly-schedule" href="#"><?php echo esc_html('Hourly Schedule' ,'modern-events-calendar-lite'); ?></a>
                <a class="mec-add-event-tabs-link" data-href="mec-location" href="#"><?php echo esc_html('Location/Venue' ,'modern-events-calendar-lite'); ?></a>
                <a class="mec-add-event-tabs-link" data-href="mec-read-more" href="#"><?php echo esc_html('Links' ,'modern-events-calendar-lite'); ?></a>
                <a class="mec-add-event-tabs-link" data-href="mec-organizer" href="#"><?php echo esc_html('Organizer' ,'modern-events-calendar-lite'); ?></a>
                <a class="mec-add-event-tabs-link" data-href="mec-cost" href="#"><?php echo esc_html('Cost' ,'modern-events-calendar-lite'); ?></a>
            </div>
            <div class="mec-add-event-tabs-right">
                <?php do_action('mec_metabox_details', $post); ?>
            </div>
        </div>
        <script>
            jQuery(".mec-meta-box-fields .mec-event-tab-content:first-of-type,.mec-add-event-tabs-left .mec-add-event-tabs-link:first-of-type").addClass("mec-tab-active");
            jQuery(".mec-add-event-tabs-link").on("click", function (e) {
                e.preventDefault();
                var href = jQuery(this).attr("data-href");
                jQuery(".mec-event-tab-content,.mec-add-event-tabs-link").removeClass("mec-tab-active");
                jQuery(this).addClass("mec-tab-active");
                jQuery("#" + href ).addClass("mec-tab-active");
            });
        </script>
    <?php
    }

    /**
     * Add a security nonce to the Add/Edit events page
     *
     * @author Webnus <info@webnus.biz>
     */
    public function meta_box_nonce()
    {
        // Add a nonce field so we can check for it later.
        wp_nonce_field('mec_event_data', 'mec_event_nonce');
    }

    
    /**
     * Show date options of event into the Add/Edit event page
     *
     * @author Webnus <info@webnus.biz>
     * @param object $post
     */
    public function meta_box_dates($post)
    {
        global $post;

        $allday = get_post_meta($post->ID, 'mec_allday', true);
        $comment = get_post_meta($post->ID, 'mec_comment', true);
        $hide_time = get_post_meta($post->ID, 'mec_hide_time', true);
        $hide_end_time = get_post_meta($post->ID, 'mec_hide_end_time', true);
        $start_date = get_post_meta($post->ID, 'mec_start_date', true);

        // Advanced Repeating Day
        $advanced_days = get_post_meta($post->ID, 'mec_advanced_days', true);
        $advanced_days = is_array($advanced_days) ? $advanced_days : array();
        $advanced_str = count($advanced_days) ? implode('-', $advanced_days) : '';

        $start_time_hour = get_post_meta($post->ID, 'mec_start_time_hour', true);
        if (trim($start_time_hour) == '') {
            $start_time_hour = 8;
        }

        $start_time_minutes = get_post_meta($post->ID, 'mec_start_time_minutes', true);
        if (trim($start_time_minutes) == '') {
            $start_time_minutes = 0;
        }

        $start_time_ampm = get_post_meta($post->ID, 'mec_start_time_ampm', true);
        if (trim($start_time_ampm) == '') {
            $start_time_minutes = 'AM';
        }

        $end_date = get_post_meta($post->ID, 'mec_end_date', true);

        $end_time_hour = get_post_meta($post->ID, 'mec_end_time_hour', true);
        if (trim($end_time_hour) == '') {
            $end_time_hour = 6;
        }

        $end_time_minutes = get_post_meta($post->ID, 'mec_end_time_minutes', true);
        if (trim($end_time_minutes) == '') {
            $end_time_minutes = 0;
        }

        $end_time_ampm = get_post_meta($post->ID, 'mec_end_time_ampm', true);
        if (trim($end_time_ampm) == '') {
            $end_time_ampm = 'PM';
        }

        $repeat_status = get_post_meta($post->ID, 'mec_repeat_status', true);
        $repeat_type = get_post_meta($post->ID, 'mec_repeat_type', true);

        $repeat_interval = get_post_meta($post->ID, 'mec_repeat_interval', true);
        if (trim($repeat_interval) == '' and in_array($repeat_type, array('daily', 'weekly'))) {
            $repeat_interval = 1;
        }

        $certain_weekdays = get_post_meta($post->ID, 'mec_certain_weekdays', true);
        if ($repeat_type != 'certain_weekdays') {
            $certain_weekdays = array();
        }

        $in_days_str = get_post_meta($post->ID, 'mec_in_days', true);
        $in_days = trim($in_days_str) ? explode(',', $in_days_str) : array();

        $mec_repeat_end = get_post_meta($post->ID, 'mec_repeat_end', true);
        if (trim($mec_repeat_end) == '') {
            $mec_repeat_end = 'never';
        }

        $repeat_end_at_occurrences = get_post_meta($post->ID, 'mec_repeat_end_at_occurrences', true);
        if (trim($repeat_end_at_occurrences) == '') {
            $repeat_end_at_occurrences = 9;
        }

        $repeat_end_at_date = get_post_meta($post->ID, 'mec_repeat_end_at_date', true);

        $note = get_post_meta($post->ID, 'mec_note', true);
        $note_visibility = $this->main->is_note_visible($post->post_status);

        $fes_guest_email = get_post_meta($post->ID, 'fes_guest_email', true);
        $fes_guest_name = get_post_meta($post->ID, 'fes_guest_name', true);
        ?>
        <div class="mec-meta-box-fields" id="mec-date-time">
            <?php if ( ($note_visibility and trim($note)) || (trim($fes_guest_email) and trim($fes_guest_name)) ) : ?>
                <div id="mec_meta_box_fes_form" class="mec-event-tab-content">
            <?php endif; ?>
            <?php if ($note_visibility and trim($note)) : ?>
                <div class="mec-event-note">
                    <h4><?php _e('Note for reviewer', 'modern-events-calendar-lite'); ?></h4>
                    <p><?php echo $note; ?></p>
                </div>
            <?php endif; ?>
            <?php if (trim($fes_guest_email) and trim($fes_guest_name)) : ?>
                <div class="mec-guest-data">
                    <h4><?php _e('Guest Data', 'modern-events-calendar-lite'); ?></h4>
                    <p><strong><?php _e('Name', 'modern-events-calendar-lite'); ?>:</strong> <?php echo $fes_guest_name; ?></p>
                    <p><strong><?php _e('Email', 'modern-events-calendar-lite'); ?>:</strong> <?php echo $fes_guest_email; ?></p>
                </div>
            <?php endif; ?>
            <?php if ( ($note_visibility and trim($note)) || (trim($fes_guest_email) and trim($fes_guest_name)) ) : ?>
                </div>
            <?php endif; ?>
            <?php do_action('start_mec_custom_fields', $post); ?>
            <div id="mec_meta_box_date_form" class="mec-event-tab-content">
                <h4><?php _e('Date and Time', 'modern-events-calendar-lite'); ?></h4>
                <div class="mec-title">
                    <span class="mec-dashicons dashicons dashicons-calendar-alt"></span>
                    <label for="mec_start_date"><?php _e('Start Date', 'modern-events-calendar-lite'); ?></label>
                </div>
                <div class="mec-form-row">
                    <div class="mec-col-4">
                        <input type="text" name="mec[date][start][date]" id="mec_start_date"
                               value="<?php echo esc_attr($start_date); ?>"
                               placeholder="<?php _e('Start Date', 'modern-events-calendar-lite'); ?>" autocomplete="off"/>
                    </div>
                    <div class="mec-col-6 mec-time-picker
					<?php
                    if ($allday == 1) {
                        echo 'mec-util-hidden';
                    }
                    ?>
					">
                        <?php
                        if (isset($this->settings['time_format']) and $this->settings['time_format'] == 24) :
                            if ($start_time_ampm == 'PM' and $start_time_hour != 12) {
                                $start_time_hour += 12;
                            }
                            if ($start_time_ampm == 'AM' and $start_time_hour == 12) {
                                $start_time_hour += 12;
                            }
                            ?>
                            <select name="mec[date][start][hour]" id="mec_start_hour">
                                <?php for ($i = 0; $i <= 23; $i++) : ?>
                                    <option
                                        <?php
                                        if ($start_time_hour == $i) {
                                            echo 'selected="selected"';
                                        }
                                        ?>
                                            value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <span class="time-dv">:</span>
                            <select name="mec[date][start][minutes]" id="mec_start_minutes">
                                <?php for ($i = 0; $i <= 11; $i++) : ?>
                                    <option
                                        <?php
                                        if ($start_time_minutes == ($i * 5)) {
                                            echo 'selected="selected"';
                                        }
                                        ?>
                                            value="<?php echo($i * 5); ?>"><?php echo sprintf('%02d', ($i * 5)); ?></option>
                                <?php endfor; ?>
                            </select>
                        <?php else : ?>
                            <select name="mec[date][start][hour]" id="mec_start_hour">
                                <?php for ($i = 1; $i <= 12; $i++) : ?>
                                    <option
                                        <?php
                                        if ($start_time_hour == $i) {
                                            echo 'selected="selected"';
                                        }
                                        ?>
                                            value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <span class="time-dv">:</span>
                            <select name="mec[date][start][minutes]" id="mec_start_minutes">
                                <?php for ($i = 0; $i <= 11; $i++) : ?>
                                    <option
                                        <?php
                                        if ($start_time_minutes == ($i * 5)) {
                                            echo 'selected="selected"';
                                        }
                                        ?>
                                            value="<?php echo($i * 5); ?>"><?php echo sprintf('%02d', ($i * 5)); ?></option>
                                <?php endfor; ?>
                            </select>
                            <select name="mec[date][start][ampm]" id="mec_start_ampm">
                                <option
                                    <?php
                                    if ($start_time_ampm == 'AM') {
                                        echo 'selected="selected"';
                                    }
                                    ?>
                                        value="AM"><?php _e('AM', 'modern-events-calendar-lite'); ?></option>
                                <option
                                    <?php
                                    if ($start_time_ampm == 'PM') {
                                        echo 'selected="selected"';
                                    }
                                    ?>
                                        value="PM"><?php _e('PM', 'modern-events-calendar-lite'); ?></option>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mec-title">
                    <span class="mec-dashicons dashicons dashicons-calendar-alt"></span>
                    <label for="mec_end_date"><?php _e('End Date', 'modern-events-calendar-lite'); ?></label>
                </div>
                <div class="mec-form-row">
                    <div class="mec-col-4">
                        <input type="text" name="mec[date][end][date]" id="mec_end_date"
                               value="<?php echo esc_attr($end_date); ?>" placeholder="<?php _e('End Date', 'modern-events-calendar-lite'); ?>"
                               autocomplete="off"/>
                    </div>
                    <div class="mec-col-6 mec-time-picker
					<?php
                    if ($allday == 1) {
                        echo 'mec-util-hidden';
                    }
                    ?>
					">
                        <?php
                        if (isset($this->settings['time_format']) and $this->settings['time_format'] == 24) :
                            if ($end_time_ampm == 'PM' and $end_time_hour != 12) {
                                $end_time_hour += 12;
                            }
                            if ($end_time_ampm == 'AM' and $end_time_hour == 12) {
                                $end_time_hour += 12;
                            }
                            ?>
                            <select name="mec[date][end][hour]" id="mec_end_hour">
                                <?php for ($i = 0; $i <= 23; $i++) : ?>
                                    <option
                                        <?php
                                        if ($end_time_hour == $i) {
                                            echo 'selected="selected"';
                                        }
                                        ?>
                                            value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <span class="time-dv">:</span>
                            <select name="mec[date][end][minutes]" id="mec_end_minutes">
                                <?php for ($i = 0; $i <= 11; $i++) : ?>
                                    <option
                                        <?php
                                        if ($end_time_minutes == ($i * 5)) {
                                            echo 'selected="selected"';
                                        }
                                        ?>
                                            value="<?php echo($i * 5); ?>"><?php echo sprintf('%02d', ($i * 5)); ?></option>
                                <?php endfor; ?>
                            </select>
                        <?php else : ?>
                            <select name="mec[date][end][hour]" id="mec_end_hour">
                                <?php for ($i = 1; $i <= 12; $i++) : ?>
                                    <option
                                        <?php
                                        if ($end_time_hour == $i) {
                                            echo 'selected="selected"';
                                        }
                                        ?>
                                            value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <span class="time-dv">:</span>
                            <select name="mec[date][end][minutes]" id="mec_end_minutes">
                                <?php for ($i = 0; $i <= 11; $i++) : ?>
                                    <option
                                        <?php
                                        if ($end_time_minutes == ($i * 5)) {
                                            echo 'selected="selected"';
                                        }
                                        ?>
                                            value="<?php echo($i * 5); ?>"><?php echo sprintf('%02d', ($i * 5)); ?></option>
                                <?php endfor; ?>
                            </select>
                            <select name="mec[date][end][ampm]" id="mec_end_ampm">
                                <option
                                    <?php
                                    if ($end_time_ampm == 'AM') {
                                        echo 'selected="selected"';
                                    }
                                    ?>
                                        value="AM"><?php _e('AM', 'modern-events-calendar-lite'); ?></option>
                                <option
                                    <?php
                                    if ($end_time_ampm == 'PM') {
                                        echo 'selected="selected"';
                                    }
                                    ?>
                                        value="PM"><?php _e('PM', 'modern-events-calendar-lite'); ?></option>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mec-form-row">
                    <input
                        <?php
                        if ($allday == '1') {
                            echo 'checked="checked"';
                        }
                        ?>
                            type="checkbox" name="mec[date][allday]" id="mec_allday" value="1"
                            onchange="jQuery('.mec-time-picker').toggle();"/><label
                            for="mec_allday"><?php _e('All Day Event', 'modern-events-calendar-lite'); ?></label>
                </div>
                <div class="mec-form-row">
                    <input
                        <?php
                        if ($hide_time == '1') {
                            echo 'checked="checked"';
                        }
                        ?>
                            type="checkbox" name="mec[date][hide_time]" id="mec_hide_time" value="1"/><label
                            for="mec_hide_time"><?php _e('Hide Event Time', 'modern-events-calendar-lite'); ?></label>
                </div>
                <div class="mec-form-row">
                    <input
                        <?php
                        if ($hide_end_time == '1') {
                            echo 'checked="checked"';
                        }
                        ?>
                            type="checkbox" name="mec[date][hide_end_time]" id="mec_hide_end_time" value="1"/><label
                            for="mec_hide_end_time"><?php _e('Hide Event End Time', 'modern-events-calendar-lite'); ?></label>
                </div>
                <div class="mec-form-row">
                    <div class="mec-col-4">
                        <input type="text" class="" name="mec[date][comment]" id="mec_comment"
                               placeholder="<?php _e('Time Comment', 'modern-events-calendar-lite'); ?>"
                               value="<?php echo esc_attr($comment); ?>"/>
                        <span class="mec-tooltip">
							<div class="box top">
								<h5 class="title"><?php _e('Time Comment', 'modern-events-calendar-lite'); ?></h5>
								<div class="content"><p><?php esc_attr_e('It shows next to event time on single event page. You can insert Timezone etc. in this field.', 'modern-events-calendar-lite'); ?>
                                        <a href="https://webnus.net/dox/modern-events-calendar/add-event/"
                                           target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
							</div>
							<i title="" class="dashicons-before dashicons-editor-help"></i>
						</span>
                    </div>
                </div>
            </div>
            <div id="mec_meta_box_repeat_form" class="mec-event-tab-content">
                <h4><?php _e('Repeating', 'modern-events-calendar-lite'); ?></h4>
                <div class="mec-form-row">
                    <input
                        <?php
                        if ($repeat_status == '1') {
                            echo 'checked="checked"';
                        }
                        ?>
                            type="checkbox" name="mec[date][repeat][status]" id="mec_repeat" value="1"/><label
                            for="mec_repeat"><?php _e('Event Repeating (Recurring events)', 'modern-events-calendar-lite'); ?></label>
                </div>
                <div class="mec-form-repeating-event-row">
                    <div class="mec-form-row">
                        <label class="mec-col-3" for="mec_repeat_type"><?php _e('Repeats', 'modern-events-calendar-lite'); ?></label>
                        <select class="mec-col-2" name="mec[date][repeat][type]" id="mec_repeat_type">
                            <option
                                <?php
                                if ($repeat_type == 'daily') {
                                    echo 'selected="selected"';
                                }
                                ?>
                                    value="daily"><?php _e('Daily', 'modern-events-calendar-lite'); ?></option>
                            <option
                                <?php
                                if ($repeat_type == 'weekday') {
                                    echo 'selected="selected"';
                                }
                                ?>
                                    value="weekday"><?php _e('Every Weekday', 'modern-events-calendar-lite'); ?></option>
                            <option
                                <?php
                                if ($repeat_type == 'weekend') {
                                    echo 'selected="selected"';
                                }
                                ?>
                                    value="weekend"><?php _e('Every Weekend', 'modern-events-calendar-lite'); ?></option>
                            <option
                                <?php
                                if ($repeat_type == 'certain_weekdays') {
                                    echo 'selected="selected"';
                                }
                                ?>
                                    value="certain_weekdays"><?php _e('Certain Weekdays', 'modern-events-calendar-lite'); ?></option>
                            <option
                                <?php
                                if ($repeat_type == 'weekly') {
                                    echo 'selected="selected"';
                                }
                                ?>
                                    value="weekly"><?php _e('Weekly', 'modern-events-calendar-lite'); ?></option>
                            <option
                                <?php
                                if ($repeat_type == 'monthly') {
                                    echo 'selected="selected"';
                                }
                                ?>
                                    value="monthly"><?php _e('Monthly', 'modern-events-calendar-lite'); ?></option>
                            <option
                                <?php
                                if ($repeat_type == 'yearly') {
                                    echo 'selected="selected"';
                                }
                                ?>
                                    value="yearly"><?php _e('Yearly', 'modern-events-calendar-lite'); ?></option>
                            <option
                                <?php
                                if ($repeat_type == 'custom_days') {
                                    echo 'selected="selected"';
                                }
                                ?>
                                    value="custom_days"><?php _e('Custom Days', 'modern-events-calendar-lite'); ?></option>
                            <option
                                <?php
                                if ($repeat_type == 'advanced') {
                                    echo 'selected="selected"';
                                }
                                ?>
                                    value="advanced"><?php _e('Advanced', 'modern-events-calendar-lite'); ?></option>
                        </select>
                    </div>
                    <div class="mec-form-row" id="mec_repeat_interval_container">
                        <label class="mec-col-3"
                               for="mec_repeat_interval"><?php _e('Repeat Interval', 'modern-events-calendar-lite'); ?></label>
                        <input class="mec-col-2" type="text" name="mec[date][repeat][interval]" id="mec_repeat_interval"
                               placeholder="<?php _e('Repeat interval', 'modern-events-calendar-lite'); ?>"
                               value="<?php echo($repeat_type == 'weekly' ? ($repeat_interval / 7) : $repeat_interval); ?>"/>
                    </div>
                    <div class="mec-form-row" id="mec_repeat_certain_weekdays_container">
                        <label class="mec-col-3"><?php _e('Week Days', 'modern-events-calendar-lite'); ?></label>
                        <label> <input type="checkbox" name="mec[date][repeat][certain_weekdays][]"
                                       value="1" <?php echo(in_array(1, $certain_weekdays) ? 'checked="checked"' : ''); ?> /><?php _e('Monday', 'modern-events-calendar-lite'); ?>
                        </label>
                        <label>&nbsp;<input type="checkbox" name="mec[date][repeat][certain_weekdays][]"
                                            value="2" <?php echo(in_array(2, $certain_weekdays) ? 'checked="checked"' : ''); ?> /><?php _e('Tuesday', 'modern-events-calendar-lite'); ?>
                        </label>
                        <label>&nbsp;<input type="checkbox" name="mec[date][repeat][certain_weekdays][]"
                                            value="3" <?php echo(in_array(3, $certain_weekdays) ? 'checked="checked"' : ''); ?> /><?php _e('Wednesday', 'modern-events-calendar-lite'); ?>
                        </label>
                        <label>&nbsp;<input type="checkbox" name="mec[date][repeat][certain_weekdays][]"
                                            value="4" <?php echo(in_array(4, $certain_weekdays) ? 'checked="checked"' : ''); ?> /><?php _e('Thursday', 'modern-events-calendar-lite'); ?>
                        </label>
                        <label>&nbsp;<input type="checkbox" name="mec[date][repeat][certain_weekdays][]"
                                            value="5" <?php echo(in_array(5, $certain_weekdays) ? 'checked="checked"' : ''); ?> /><?php _e('Friday', 'modern-events-calendar-lite'); ?>
                        </label>
                        <label>&nbsp;<input type="checkbox" name="mec[date][repeat][certain_weekdays][]"
                                            value="6" <?php echo(in_array(6, $certain_weekdays) ? 'checked="checked"' : ''); ?> /><?php _e('Saturday', 'modern-events-calendar-lite'); ?>
                        </label>
                        <label>&nbsp;<input type="checkbox" name="mec[date][repeat][certain_weekdays][]"
                                            value="7" <?php echo(in_array(7, $certain_weekdays) ? 'checked="checked"' : ''); ?> /><?php _e('Sunday', 'modern-events-calendar-lite'); ?>
                        </label>
                    </div>
                    <div class="mec-form-row" id="mec_exceptions_in_days_container">
                        <div class="mec-form-row">
                            <div class="mec-col-6">
                                <input type="text" id="mec_exceptions_in_days_start_date" value=""
                                       placeholder="<?php _e('Start', 'modern-events-calendar-lite'); ?>" class="mec_date_picker"/>
                                <input type="text" id="mec_exceptions_in_days_end_date" value=""
                                       placeholder="<?php _e('End', 'modern-events-calendar-lite'); ?>" class="mec_date_picker"/>
                                <button class="button" type="button"
                                        id="mec_add_in_days"><?php _e('Add', 'modern-events-calendar-lite'); ?></button>
                                <span class="mec-tooltip">
									<div class="box top">
										<h5 class="title"><?php _e('Custom Days Repeating', 'modern-events-calendar-lite'); ?></h5>
										<div class="content"><p><?php esc_attr_e('Add certain days to event occurrence dates. If you have single day event, start and end date should be the same, If you have multiple day event the start and end dates must be commensurate with the initial date.', 'modern-events-calendar-lite'); ?>
                                                <a href="https://webnus.net/dox/modern-events-calendar/date-and-time/"
                                                   target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
									</div>
									<i title="" class="dashicons-before dashicons-editor-help"></i>
								</span>
                            </div>
                        </div>
                        <div class="mec-form-row mec-certain-day" id="mec_in_days">
                            <?php $i = 1;
                            foreach ($in_days as $in_day) : ?>
                                <div class="mec-form-row" id="mec_in_days_row<?php echo $i; ?>">
                                    <input type="hidden" name="mec[in_days][<?php echo $i; ?>]"
                                           value="<?php echo $in_day; ?>"/>
                                    <span class="mec-in-days-day"><?php echo str_replace(':', ' - ', $in_day); ?></span>
                                    <span class="mec-not-in-days-remove"
                                          onclick="mec_in_days_remove(<?php echo $i; ?>);">x</span>
                                </div>
                                <?php
                                $i++;
                            endforeach;
                            ?>
                        </div>
                        <input type="hidden" id="mec_new_in_days_key" value="<?php echo $i + 1; ?>"/>
                        <div class="mec-util-hidden" id="mec_new_in_days_raw">
                            <div class="mec-form-row" id="mec_in_days_row:i:">
                                <input type="hidden" name="mec[in_days][:i:]" value=":val:"/>
                                <span class="mec-in-days-day">:label:</span>
                                <span class="mec-not-in-days-remove" onclick="mec_in_days_remove(:i:);">x</span>
                            </div>
                        </div>
                    </div>
                    <div id="mec-advanced-wraper">
                        <div class="mec-form-row">
                            <ul>
								<li>
									<?php _e('First', 'modern-events-calendar-lite'); ?>
								</li>
								<ul>
									<?php $day_1th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 1); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_1th}.1"); ?>">
										<?php _e($day_1th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_1th ?>.1-</span>
									</li>
									<?php $day_2th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 2); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_2th}.1"); ?>">
										<?php _e($day_2th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_2th ?>.1-</span>
									</li>
									<?php $day_3th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 3); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_3th}.1"); ?>">
										<?php _e($day_3th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_3th ?>.1-</span>
									</li>
									<?php $day_4th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 4); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_4th}.1"); ?>">
										<?php _e($day_4th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_4th ?>.1-</span>
									</li>
									<?php $day_5th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 5); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_5th}.1"); ?>">
										<?php _e($day_5th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_5th ?>.1-</span>
									</li>
									<?php $day_6th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 6); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_6th}.1"); ?>">
										<?php _e($day_6th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_6th ?>.1-</span>
									</li>
									<?php $day_7th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 7); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_7th}.1"); ?>">
										<?php _e($day_7th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_7th ?>.1-</span>
									</li>
								</ul>
							</ul>
                            <ul>
								<li>
									<?php _e('Second', 'modern-events-calendar-lite'); ?>
								</li>
								<ul>
									<?php $day_1th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 1); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_1th}.2"); ?>">
										<?php _e($day_1th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_1th ?>.2-</span>
									</li>
									<?php $day_2th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 2); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_2th}.2"); ?>">
										<?php _e($day_2th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_2th ?>.2-</span>
									</li>
									<?php $day_3th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 3); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_3th}.2"); ?>">
										<?php _e($day_3th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_3th ?>.2-</span>
									</li>
									<?php $day_4th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 4); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_4th}.2"); ?>">
										<?php _e($day_4th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_4th ?>.2-</span>
									</li>
									<?php $day_5th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 5); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_5th}.2"); ?>">
										<?php _e($day_5th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_5th ?>.2-</span>
									</li>
									<?php $day_6th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 6); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_6th}.2"); ?>">
										<?php _e($day_6th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_6th ?>.2-</span>
									</li>
									<?php $day_7th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 7); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_7th}.2"); ?>">
										<?php _e($day_7th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_7th ?>.2-</span>
									</li>
								</ul>
							</ul>
                            <ul>
								<li>
									<?php _e('Third', 'modern-events-calendar-lite'); ?>
								</li>
								<ul>
									<?php $day_1th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 1); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_1th}.3"); ?>">
										<?php _e($day_1th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_1th ?>.3-</span>
									</li>
									<?php $day_2th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 2); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_2th}.3"); ?>">
										<?php _e($day_2th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_2th ?>.3-</span>
									</li>
									<?php $day_3th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 3); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_3th}.3"); ?>">
										<?php _e($day_3th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_3th ?>.3-</span>
									</li>
									<?php $day_4th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 4); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_4th}.3"); ?>">
										<?php _e($day_4th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_4th ?>.3-</span>
									</li>
									<?php $day_5th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 5); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_5th}.3"); ?>">
										<?php _e($day_5th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_5th ?>.3-</span>
									</li>
									<?php $day_6th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 6); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_6th}.3"); ?>">
										<?php _e($day_6th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_6th ?>.3-</span>
									</li>
									<?php $day_7th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 7); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_7th}.3"); ?>">
										<?php _e($day_7th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_7th ?>.3-</span>
									</li>
								</ul>
							</ul>
                            <ul>
								<li>
									<?php _e('Fourth', 'modern-events-calendar-lite'); ?>
								</li>
								<ul>
									<?php $day_1th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 1); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_1th}.4"); ?>">
										<?php _e($day_1th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_1th ?>.4-</span>
									</li>
									<?php $day_2th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 2); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_2th}.4"); ?>">
										<?php _e($day_2th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_2th ?>.4-</span>
									</li>
									<?php $day_3th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 3); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_3th}.4"); ?>">
										<?php _e($day_3th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_3th ?>.4-</span>
									</li>
									<?php $day_4th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 4); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_4th}.4"); ?>">
										<?php _e($day_4th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_4th ?>.4-</span>
									</li>
									<?php $day_5th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 5); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_5th}.4"); ?>">
										<?php _e($day_5th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_5th ?>.4-</span>
									</li>
									<?php $day_6th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 6); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_6th}.4"); ?>">
										<?php _e($day_6th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_6th ?>.4-</span>
									</li>
									<?php $day_7th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 7); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_7th}.4"); ?>">
										<?php _e($day_7th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_7th ?>.4-</span>
									</li>
								</ul>
							</ul>
                			<ul>
								<li>
									<?php _e('Last', 'modern-events-calendar-lite'); ?>
								</li>
								<ul>
									<?php $day_1th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 1); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_1th}.l"); ?>">
										<?php _e($day_1th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_1th ?>.l-</span>
									</li>
									<?php $day_2th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 2); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_2th}.l"); ?>">
										<?php _e($day_2th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_2th ?>.l-</span>
									</li>
									<?php $day_3th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 3); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_3th}.l"); ?>">
										<?php _e($day_3th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_3th ?>.l-</span>
									</li>
									<?php $day_4th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 4); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_4th}.l"); ?>">
										<?php _e($day_4th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_4th ?>.l-</span>
									</li>
									<?php $day_5th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 5); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_5th}.l"); ?>">
										<?php _e($day_5th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_5th ?>.l-</span>
									</li>
									<?php $day_6th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 6); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_6th}.l"); ?>">
										<?php _e($day_6th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_6th ?>.l-</span>
									</li>
									<?php $day_7th = $this->main->advanced_repeating_sort_day($this->main->get_first_day_of_week(), 7); ?>
									<li class="<?php $this->main->mec_active($advanced_days, "{$day_7th}.l"); ?>">
										<?php _e($day_7th, 'modern-events-calendar-lite'); ?>
										<span class="key"><?php echo $day_7th ?>.l-</span>
									</li>
								</ul>
							</ul>
                            <input class="mec-col-2" type="hidden" name="mec[date][repeat][advanced]"
                                   id="mec_date_repeat_advanced" value="<?php echo esc_attr($advanced_str); ?>"/>
                        </div>
                    </div>
                    <div id="mec_end_wrapper">
                        <div class="mec-form-row">
                            <label for="mec_repeat_ends_never">
                                <h4 class="mec-title"><?php _e('Ends Repeat', 'modern-events-calendar-lite'); ?></h4>
                            </label>
                        </div>
                        <div class="mec-form-row">
                            <input
                                <?php
                                if ($mec_repeat_end == 'never') {
                                    echo 'checked="checked"';
                                }
                                ?>
                                    type="radio" value="never" name="mec[date][repeat][end]"
                                    id="mec_repeat_ends_never"/>
                            <label for="mec_repeat_ends_never"><?php _e('Never', 'modern-events-calendar-lite'); ?></label>
                        </div>
                        <div class="mec-form-row">
                            <div class="mec-col-3">
                                <input
                                    <?php
                                    if ($mec_repeat_end == 'date') {
                                        echo 'checked="checked"';
                                    }
                                    ?>
                                        type="radio" value="date" name="mec[date][repeat][end]"
                                        id="mec_repeat_ends_date"/>
                                <label for="mec_repeat_ends_date"><?php _e('On', 'modern-events-calendar-lite'); ?></label>
                            </div>
                            <input class="mec-col-2" type="text" name="mec[date][repeat][end_at_date]"
                                   id="mec_date_repeat_end_at_date" autocomplete="off" 
                                   value="<?php echo esc_attr($repeat_end_at_date); ?>"/>
                        </div>
                        <div class="mec-form-row">
                            <div class="mec-col-3">
                                <input
                                    <?php
                                    if ($mec_repeat_end == 'occurrences') {
                                        echo 'checked="checked"';
                                    }
                                    ?>
                                        type="radio" value="occurrences" name="mec[date][repeat][end]"
                                        id="mec_repeat_ends_occurrences"/>
                                <label for="mec_repeat_ends_occurrences"><?php _e('After', 'modern-events-calendar-lite'); ?></label>
                            </div>
                            <input class="mec-col-2" type="text" name="mec[date][repeat][end_at_occurrences]"
                                   id="mec_date_repeat_end_at_occurrences" autocomplete="off"
                                   placeholder="<?php _e('Occurrences times', 'modern-events-calendar-lite'); ?>"
                                   value="<?php echo esc_attr(($repeat_end_at_occurrences + 1)); ?>"/>
                            <span class="mec-tooltip">
								<div class="box top">
									<h5 class="title"><?php _e('Occurrences times', 'modern-events-calendar-lite'); ?></h5>
									<div class="content"><p><?php esc_attr_e('The event will finish after certain repeats. For example if you set it to 10, the event will finish after 10 repeats.', 'modern-events-calendar-lite'); ?>
                                            <a href="https://webnus.net/dox/modern-events-calendar/date-and-time/"
                                               target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
								</div>
								<i title="" class="dashicons-before dashicons-editor-help"></i>
							</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Show cost option of event into the Add/Edit event page
     *
     * @author Webnus <info@webnus.biz>
     * @param object $post
     */
    public function meta_box_cost($post)
    {
        $cost = get_post_meta($post->ID, 'mec_cost', true);
        ?>
        <div class="mec-meta-box-fields mec-event-tab-content" id="mec-cost">
            <h4><?php echo $this->main->m('event_cost', __('Event Cost', 'modern-events-calendar-lite')); ?></h4>
            <div id="mec_meta_box_cost_form">
                <div class="mec-form-row">
                    <input type="text" class="mec-col-6" name="mec[cost]" id="mec_cost"
                           value="<?php echo esc_attr($cost); ?>" placeholder="<?php _e('Cost', 'modern-events-calendar-lite'); ?>"/>
                </div>
            </div>
        </div>
        <?php
    }



    /**
     * Show exceptions options of event into the Add/Edit event page
     *
     * @author Webnus <info@webnus.biz>
     * @param object $post
     */
    public function meta_box_exceptional_days($post)
    {
        $not_in_days_str = get_post_meta($post->ID, 'mec_not_in_days', true);
        $not_in_days = trim($not_in_days_str) ? explode(',', $not_in_days_str) : array();
        ?>
        <div class="mec-meta-box-fields mec-event-tab-content" id="mec-exceptional-days">
            <h4><?php _e('Exceptional Days (Exclude Dates)', 'modern-events-calendar-lite'); ?></h4>
            <div id="mec_meta_box_exceptions_form">

                <div id="mec_exceptions_not_in_days_container">
                    <div class="mec-title">
                        <span class="mec-dashicons dashicons dashicons-calendar-alt"></span>
                        <label for="mec_exceptions_not_in_days_date"><?php _e('Exclude certain days', 'modern-events-calendar-lite'); ?></label>
                    </div>
                    <div class="mec-form-row">
                        <div class="mec-col-6">
                            <input type="text" id="mec_exceptions_not_in_days_date" value=""
                                   placeholder="<?php _e('Date', 'modern-events-calendar-lite'); ?>" class="mec_date_picker" autocomplete="off"/>
                            <button class="button" type="button"
                                    id="mec_add_not_in_days"><?php _e('Add', 'modern-events-calendar-lite'); ?></button>
                            <span class="mec-tooltip">
								<div class="box top">
									<h5 class="title"><?php _e('Exclude certain days', 'modern-events-calendar-lite'); ?></h5>
									<div class="content"><p><?php esc_attr_e('Exclude certain days from event occurrence dates. Please note that you can exclude only single day occurrences and you cannot exclude one day from multiple day occurrences.', 'modern-events-calendar-lite'); ?>
                                            <a href="https://webnus.net/dox/modern-events-calendar/exceptional-days/"
                                               target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
								</div>
								<i title="" class="dashicons-before dashicons-editor-help"></i>
							</span>
                        </div>
                    </div>
                    <div class="mec-form-row mec-certain-day" id="mec_not_in_days">
                        <?php $i = 1;
                        foreach ($not_in_days as $not_in_day) : ?>
                            <div class="mec-form-row" id="mec_not_in_days_row<?php echo $i; ?>">
                                <input type="hidden" name="mec[not_in_days][<?php echo $i; ?>]"
                                       value="<?php echo $not_in_day; ?>"/>
                                <span class="mec-not-in-days-day"><?php echo $not_in_day; ?></span>
                                <span class="mec-not-in-days-remove"
                                      onclick="mec_not_in_days_remove(<?php echo $i; ?>);">x</span>
                            </div>
                            <?php
                            $i++;
                        endforeach;
                        ?>
                    </div>
                    <input type="hidden" id="mec_new_not_in_days_key" value="<?php echo $i + 1; ?>"/>
                    <div class="mec-util-hidden" id="mec_new_not_in_days_raw">
                        <div class="mec-form-row" id="mec_not_in_days_row:i:">
                            <input type="hidden" name="mec[not_in_days][:i:]" value=":val:"/>
                            <span class="mec-not-in-days-day">:val:</span>
                            <span class="mec-not-in-days-remove" onclick="mec_not_in_days_remove(:i:);">x</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php
    }

    /**
     * Show hourly schedule options of event into the Add/Edit event page
     *
     * @author Webnus <info@webnus.biz>
     * @param object $post
     */
    public function meta_box_hourly_schedule($post)
    {
        $meta_hourly_schedules = get_post_meta($post->ID, 'mec_hourly_schedules', true);

        if (is_array($meta_hourly_schedules) and count($meta_hourly_schedules)) {
            $first_key = key($meta_hourly_schedules);

            $hourly_schedules = array();
            if (!isset($meta_hourly_schedules[$first_key]['schedules'])) {
                $hourly_schedules[] = array(
                    'title' => __('Day 1', 'modern-events-calendar-lite'),
                    'schedules' => $meta_hourly_schedules,
                );
            } else {
                $hourly_schedules = $meta_hourly_schedules;
            }
        } else {
            $hourly_schedules = array();
        }

        // Status of Speakers Feature
        $speakers_status = (!isset($this->settings['speakers_status']) or (isset($this->settings['speakers_status']) and !$this->settings['speakers_status'])) ? false : true;
        $speakers = get_terms(
            'mec_speaker',
            array(
                'orderby' => 'name',
                'order' => 'ASC',
                'hide_empty' => '0',
            )
        );
        ?>
        <div class="mec-meta-box-fields mec-event-tab-content" id="mec-hourly-schedule">
            <h4><?php _e('Hourly Schedule', 'modern-events-calendar-lite'); ?></h4>
            <div id="mec_meta_box_hourly_schedule_day_form">
                <div class="mec-form-row">
                    <button class="button" type="button"
                            id="mec_add_hourly_schedule_day_button"><?php _e('Add Day', 'modern-events-calendar-lite'); ?></button>
                    <span class="description"><?php esc_attr_e('Add new days for schedule. For example if your event is multiple days, you can add a different schedule for each day!', 'modern-events-calendar-lite'); ?></span>
                </div>
            </div>
            <div id="mec_meta_box_hourly_schedule_days">
                <?php $d = 0;
                foreach ($hourly_schedules as $day) : ?>
                    <div id="mec_meta_box_hourly_schedule_day_<?php echo $d; ?>">
                        <h4><?php echo isset($day['title']) ? $day['title'] : sprintf(__('Day %s', 'modern-events-calendar-lite'), $d + 1); ?></h4>
                        <div id="mec_meta_box_hourly_schedule_form<?php echo $d; ?>">
                            <div class="mec-form-row">
                                <div class="mec-col-1"><label
                                            for="mec_add_hourly_schedule_day<?php echo $d; ?>_title"><?php echo __('Title', 'modern-events-calendar-lite'); ?></label>
                                </div>
                                <div class="mec-col-10"><input type="text"
                                                               id="mec_add_hourly_schedule_day<?php echo $d; ?>_title"
                                                               name="mec[hourly_schedules][<?php echo $d; ?>][title]"
                                                               value="<?php echo isset($day['title']) ? $day['title'] : ''; ?>"
                                                               class="widefat"></div>
                                <div class="mec-col-1">
                                    <button class="button" type="button"
                                            onclick="mec_hourly_schedule_day_remove(<?php echo $d; ?>)"><?php echo __('Remove', 'modern-events-calendar-lite'); ?></button>
                                </div>
                            </div>
                            <div class="mec-form-row">
                                <button class="button mec-add-hourly-schedule-button" type="button"
                                        id="mec_add_hourly_schedule_button<?php echo $d; ?>"
                                        data-day="<?php echo $d; ?>"><?php _e('Add', 'modern-events-calendar-lite'); ?></button>
                                <span class="description"><?php esc_attr_e('Add new hourly schedule row', 'modern-events-calendar-lite'); ?></span>
                            </div>
                            <div id="mec_hourly_schedules<?php echo $d; ?>">
                                <?php
                                $i = 0;
                                foreach ($day['schedules'] as $key => $hourly_schedule) :
                                    if (!is_numeric($key)) {
                                        continue;
                                    }
                                    $i = max($i, $key);
                                    ?>
                                    <div class="mec-form-row mec-box"
                                         id="mec_hourly_schedule_row<?php echo $d; ?>_<?php echo $key; ?>">
                                        <input class="mec-col-1" type="text"
                                               name="mec[hourly_schedules][<?php echo $d; ?>][schedules][<?php echo $key; ?>][from]"
                                               placeholder="<?php esc_attr_e('From e.g. 8:15', 'modern-events-calendar-lite'); ?>"
                                               value="<?php echo esc_attr($hourly_schedule['from']); ?>"/>
                                        <input class="mec-col-1" type="text"
                                               name="mec[hourly_schedules][<?php echo $d; ?>][schedules][<?php echo $key; ?>][to]"
                                               placeholder="<?php esc_attr_e('To e.g. 8:45', 'modern-events-calendar-lite'); ?>"
                                               value="<?php echo esc_attr($hourly_schedule['to']); ?>"/>
                                        <input class="mec-col-3" type="text"
                                               name="mec[hourly_schedules][<?php echo $d; ?>][schedules][<?php echo $key; ?>][title]"
                                               placeholder="<?php esc_attr_e('Title', 'modern-events-calendar-lite'); ?>"
                                               value="<?php echo esc_attr($hourly_schedule['title']); ?>"/>
                                        <input class="mec-col-6" type="text"
                                               name="mec[hourly_schedules][<?php echo $d; ?>][schedules][<?php echo $key; ?>][description]"
                                               placeholder="<?php esc_attr_e('Description', 'modern-events-calendar-lite'); ?>"
                                               value="<?php echo esc_attr($hourly_schedule['description']); ?>"/>
                                        <button class="button" type="button"
                                                onclick="mec_hourly_schedule_remove(<?php echo $d; ?>, <?php echo $key; ?>)"><?php _e('Remove', 'modern-events-calendar-lite'); ?></button>
                                        <?php if ($speakers_status) : ?>
                                            <div class="mec-col-12 mec-hourly-schedule-form-speakers">
                                                <strong><?php echo $this->main->m('taxonomy_speakers', __('Speakers', 'modern-events-calendar-lite')); ?></strong>
                                                <?php foreach ($speakers as $speaker) : ?>
                                                    <label><input type="checkbox"
                                                                  name="mec[hourly_schedules][<?php echo $d; ?>][schedules][<?php echo $key; ?>][speakers][]"
                                                                  value="<?php echo $speaker->term_id; ?>" <?php echo (isset($hourly_schedule['speakers']) and in_array($speaker->term_id, $hourly_schedule['speakers'])) ? 'checked="checked"' : ''; ?>><?php echo $speaker->name; ?>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <input type="hidden" id="mec_new_hourly_schedule_key<?php echo $d; ?>"
                               value="<?php echo $i + 1; ?>"/>
                        <div class="mec-util-hidden" id="mec_new_hourly_schedule_raw<?php echo $d; ?>">
                            <div class="mec-form-row mec-box" id="mec_hourly_schedule_row<?php echo $d; ?>_:i:">
                                <input class="mec-col-1" type="text"
                                       name="mec[hourly_schedules][<?php echo $d; ?>][schedules][:i:][from]"
                                       placeholder="<?php esc_attr_e('From e.g. 8:15', 'modern-events-calendar-lite'); ?>"/>
                                <input class="mec-col-1" type="text"
                                       name="mec[hourly_schedules][<?php echo $d; ?>][schedules][:i:][to]"
                                       placeholder="<?php esc_attr_e('To e.g. 8:45', 'modern-events-calendar-lite'); ?>"/>
                                <input class="mec-col-3" type="text"
                                       name="mec[hourly_schedules][<?php echo $d; ?>][schedules][:i:][title]"
                                       placeholder="<?php esc_attr_e('Title', 'modern-events-calendar-lite'); ?>"/>
                                <input class="mec-col-6" type="text"
                                       name="mec[hourly_schedules][<?php echo $d; ?>][schedules][:i:][description]"
                                       placeholder="<?php esc_attr_e('Description', 'modern-events-calendar-lite'); ?>"/>
                                <button class="button" type="button"
                                        onclick="mec_hourly_schedule_remove(<?php echo $d; ?>, :i:)"><?php _e('Remove', 'modern-events-calendar-lite'); ?></button>
                                <?php if ($speakers_status) : ?>
                                    <div class="mec-col-12 mec-hourly-schedule-form-speakers">
                                        <strong><?php echo $this->main->m('taxonomy_speakers', __('Speakers', 'modern-events-calendar-lite')); ?></strong>
                                        <?php foreach ($speakers as $speaker) : ?>
                                            <label><input type="checkbox"
                                                          name="mec[hourly_schedules][<?php echo $d; ?>][schedules][:i:][speakers][]"
                                                          value="<?php echo $speaker->term_id; ?>"><?php echo $speaker->name; ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    $d++;
                endforeach;
                ?>
            </div>
            <input type="hidden" id="mec_new_hourly_schedule_day_key" value="<?php echo $d; ?>"/>
            <div class="mec-util-hidden" id="mec_new_hourly_schedule_day_raw">
                <div id="mec_meta_box_hourly_schedule_day_:d:">
                    <h4><?php echo __('New Day', 'modern-events-calendar-lite'); ?></h4>
                    <div id="mec_meta_box_hourly_schedule_form:d:">
                        <div class="mec-form-row">
                            <div class="mec-col-1"><label
                                        for="mec_add_hourly_schedule_day:d:_title"><?php echo __('Title', 'modern-events-calendar-lite'); ?></label>
                            </div>
                            <div class="mec-col-10"><input type="text" id="mec_add_hourly_schedule_day:d:_title"
                                                           name="mec[hourly_schedules][:d:][title]"
                                                           value="<?php echo __('New Day', 'modern-events-calendar-lite'); ?>" class="widefat">
                            </div>
                            <div class="mec-col-1">
                                <button class="button" type="button"
                                        onclick="mec_hourly_schedule_day_remove(:d:)"><?php echo __('Remove', 'modern-events-calendar-lite'); ?></button>
                            </div>
                        </div>
                        <div class="mec-form-row">
                            <button class="button mec-add-hourly-schedule-button" type="button"
                                    id="mec_add_hourly_schedule_button:d:"
                                    data-day=":d:"><?php _e('Add', 'modern-events-calendar-lite'); ?></button>
                            <span class="description"><?php esc_attr_e('Add new hourly schedule row', 'modern-events-calendar-lite'); ?></span>
                        </div>
                        <div id="mec_hourly_schedules:d:">
                        </div>
                    </div>
                    <input type="hidden" id="mec_new_hourly_schedule_key:d:" value="1"/>
                    <div class="mec-util-hidden" id="mec_new_hourly_schedule_raw:d:">
                        <div class="mec-form-row mec-box" id="mec_hourly_schedule_row:d:_:i:">
                            <input class="mec-col-1" type="text" name="mec[hourly_schedules][:d:][schedules][:i:][from]"
                                   placeholder="<?php esc_attr_e('From e.g. 8:15', 'modern-events-calendar-lite'); ?>"/>
                            <input class="mec-col-1" type="text" name="mec[hourly_schedules][:d:][schedules][:i:][to]"
                                   placeholder="<?php esc_attr_e('To e.g. 8:45', 'modern-events-calendar-lite'); ?>"/>
                            <input class="mec-col-3" type="text"
                                   name="mec[hourly_schedules][:d:][schedules][:i:][title]"
                                   placeholder="<?php esc_attr_e('Title', 'modern-events-calendar-lite'); ?>"/>
                            <input class="mec-col-6" type="text"
                                   name="mec[hourly_schedules][:d:][schedules][:i:][description]"
                                   placeholder="<?php esc_attr_e('Description', 'modern-events-calendar-lite'); ?>"/>
                            <button class="button" type="button"
                                    onclick="mec_hourly_schedule_remove(:d:, :i:)"><?php _e('Remove', 'modern-events-calendar-lite'); ?></button>
                            <?php if ($speakers_status) : ?>
                                <div class="mec-col-12 mec-hourly-schedule-form-speakers">
                                    <strong><?php echo $this->main->m('taxonomy_speakers', __('Speakers', 'modern-events-calendar-lite')); ?></strong>
                                    <?php foreach ($speakers as $speaker) : ?>
                                        <label><input type="checkbox"
                                                      name="mec[hourly_schedules][:d:][schedules][:i:][speakers][]"
                                                      value="<?php echo $speaker->term_id; ?>"><?php echo $speaker->name; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Show read more option of event into the Add/Edit event page
     *
     * @author Webnus <info@webnus.biz>
     * @param object $post
     */
    public function meta_box_links($post)
    {
        $read_more = get_post_meta($post->ID, 'mec_read_more', true);
        $more_info = get_post_meta($post->ID, 'mec_more_info', true);
        $more_info_title = get_post_meta($post->ID, 'mec_more_info_title', true);
        $more_info_target = get_post_meta($post->ID, 'mec_more_info_target', true);
        ?>
        <div class="mec-meta-box-fields mec-event-tab-content" id="mec-read-more">
            <h4><?php _e('Event Links', 'modern-events-calendar-lite'); ?></h4>
            <div class="mec-form-row">
                <label class="mec-col-2"
                       for="mec_read_more_link"><?php echo $this->main->m('read_more_link', __('Event Link', 'modern-events-calendar-lite')); ?></label>
                <input class="mec-col-7" type="text" name="mec[read_more]" id="mec_read_more_link"
                       value="<?php echo esc_attr($read_more); ?>"
                       placeholder="<?php _e('eg. http://yoursite.com/your-event', 'modern-events-calendar-lite'); ?>"/>
                <span class="mec-tooltip">
					<div class="box top">
						<h5 class="title"><?php _e('Event Link', 'modern-events-calendar-lite'); ?></h5>
						<div class="content"><p><?php esc_attr_e('If you fill it, it will be replaced instead of default event page link. Insert full link including http(s):// - Also, if you use advertising URL, can use URL Shortener', 'modern-events-calendar-lite'); ?>
                                <a href="https://bit.ly/"
                                   target="_blank"><?php _e('URL Shortener', 'modern-events-calendar-lite'); ?></a></p></div>
					</div>
					<i title="" class="dashicons-before dashicons-editor-help"></i>
				</span>
            </div>
            <div class="mec-form-row">
                <label class="mec-col-2"
                       for="mec_more_info_link"><?php echo $this->main->m('more_info_link', __('More Info', 'modern-events-calendar-lite')); ?></label>
                <input class="mec-col-5" type="text" name="mec[more_info]" id="mec_more_info_link"
                       value="<?php echo esc_attr($more_info); ?>"
                       placeholder="<?php _e('eg. http://yoursite.com/your-event', 'modern-events-calendar-lite'); ?>"/>
                <input class="mec-col-2" type="text" name="mec[more_info_title]" id="mec_more_info_title"
                       value="<?php echo esc_attr($more_info_title); ?>"
                       placeholder="<?php _e('More Information', 'modern-events-calendar-lite'); ?>"/>
                <select class="mec-col-2" name="mec[more_info_target]" id="mec_more_info_target">
                    <option value="_self" <?php echo($more_info_target == '_self' ? 'selected="selected"' : ''); ?>><?php _e('Current Window', 'modern-events-calendar-lite'); ?></option>
                    <option value="_blank" <?php echo($more_info_target == '_blank' ? 'selected="selected"' : ''); ?>><?php _e('New Window', 'modern-events-calendar-lite'); ?></option>
                </select>
                <span class="mec-tooltip">
					<div class="box top">
						<h5 class="title"><?php _e('More Info', 'modern-events-calendar-lite'); ?></h5>
						<div class="content"><p><?php esc_attr_e('If you fill it, it will be shown in event details page as an optional link. Insert full link including http(s)://', 'modern-events-calendar-lite'); ?>
                                <a href="https://webnus.net/dox/modern-events-calendar/add-event/"
                                   target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
					</div>
					<i title="" class="dashicons-before dashicons-editor-help"></i>
				</span>
            </div>
        </div>
        <?php
    }

    /**
     * Show booking meta box contents
     *
     * @author Webnus <info@webnus.biz>
     * @param object $post
     */
    public function meta_box_booking($post)
    {
        $gateway_settings = $this->main->get_gateways_options()
    ?>
        <div class="mec-add-booking-tabs-wrap">
            <div class="mec-add-booking-tabs-left">
                <a class="mec-add-booking-tabs-link mec-tab-active" data-href="mec_meta_box_booking_options_form_1" href="#"><?php echo esc_html('Total Booking Limits' ,'modern-events-calendar-lite'); ?></a>
                <a class="mec-add-booking-tabs-link" data-href="mec_meta_box_booking_options_form_2" href="#"><?php echo esc_html('Total User Booking Limits' ,'modern-events-calendar-lite'); ?></a>
                <a class="mec-add-booking-tabs-link" data-href="mec-tickets" href="#"><?php echo esc_html('Tickets' ,'modern-events-calendar-lite'); ?></a>
                <?php if(isset($this->settings['taxes_fees_status']) and $this->settings['taxes_fees_status']) : ?>
                <a class="mec-add-booking-tabs-link" data-href="mec-fees" href="#"><?php echo esc_html('Fees' ,'modern-events-calendar-lite'); ?></a>
                <?php endif; ?>
                <?php if(isset($this->settings['ticket_variations_status']) and $this->settings['ticket_variations_status']) : ?>
                <a class="mec-add-booking-tabs-link" data-href="mec-ticket-variations" href="#"><?php echo esc_html('Ticket Variations / Options' ,'modern-events-calendar-lite'); ?></a>
                <?php endif; ?>
                <a class="mec-add-booking-tabs-link" data-href="mec-reg-fields" href="#"><?php echo esc_html('Booking Form' ,'modern-events-calendar-lite'); ?></a>
                <?php if ( isset( $gateway_settings['op_status'] ) && $gateway_settings['op_status'] == 1 ) : ?>
                <a class="mec-add-booking-tabs-link" data-href="mec_meta_box_op_form" href="#"><?php echo esc_html('Organizer Payment' ,'modern-events-calendar-lite'); ?></a>
                <?php endif; ?>
                <?php do_action( 'add_event_booking_sections_left_menu' ); ?>
            </div>
            <div class="mec-add-booking-tabs-right">
                <?php do_action('mec_metabox_booking', $post); ?>
            </div>
        </div>
        <script>
            jQuery(".mec-add-booking-tabs-link").on("click", function (e) {
                console.log(jQuery(this));
                e.preventDefault();
                var href = jQuery(this).attr("data-href");
                jQuery(".mec-booking-tab-content,.mec-add-booking-tabs-link").removeClass("mec-tab-active");
                jQuery(this).addClass("mec-tab-active");
                jQuery("#" + href ).addClass("mec-tab-active");
            });
        </script>
    <?php
    }

    /**
     * Show booking options of event into the Add/Edit event page
     *
     * @author Webnus <info@webnus.biz>
     * @param object $post
     */
    public function meta_box_booking_options($post)
    {
        $booking_options = get_post_meta($post->ID, 'mec_booking', true);
        if (!is_array($booking_options)) {
            $booking_options = array();
        }

        $bookings_limit = isset($booking_options['bookings_limit']) ? $booking_options['bookings_limit'] : '';
        $bookings_limit_unlimited = isset($booking_options['bookings_limit_unlimited']) ? $booking_options['bookings_limit_unlimited'] : 0;
        $bookings_user_limit = isset($booking_options['bookings_user_limit']) ? $booking_options['bookings_user_limit'] : '';
        $bookings_user_limit_unlimited = isset($booking_options['bookings_user_limit_unlimited']) ? $booking_options['bookings_user_limit_unlimited'] : true;
        ?>
        <div id="mec-booking">
            <div class="mec-meta-box-fields mec-booking-tab-content mec-tab-active" id="mec_meta_box_booking_options_form_1">
                <label for="mec_bookings_limit"><h4 class="mec-title"><?php _e('Total booking limits', 'modern-events-calendar-lite'); ?></h4>
                </label>
                <div class="mec-form-row">
                    <label class="mec-col-4" for="mec_bookings_limit_unlimited" id="mec_bookings_limit_unlimited_label">
                        <input type="hidden" name="mec[booking][bookings_limit_unlimited]" value="0"/>
                        <input id="mec_bookings_limit_unlimited"
                            <?php
                            if ($bookings_limit_unlimited == 1) {
                                echo 'checked="checked"';
                            }
                            ?>
                               type="checkbox" value="1" name="mec[booking][bookings_limit_unlimited]"/>
                        <?php _e('Unlimited', 'modern-events-calendar-lite'); ?>
                        <span class="mec-tooltip">
                            <div class="box">
                                <h5 class="title"><?php _e('Total booking limits', 'modern-events-calendar-lite'); ?></h5>
                                <div class="content">
                                    <p>
                                    <?php esc_attr_e('If you want to set a limit to all tickets, uncheck this checkbox and put a limitation number.', 'modern-events-calendar-lite'); ?>
                                        <a href="https://webnus.net/dox/modern-events-calendar/total-booking-limits/" target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a>
                                        <a href="https://webnus.net/dox/modern-events-calendar/add-a-booking-system/" target="_blank"><?php _e('Read About A Booking System', 'modern-events-calendar-lite'); ?></a>
                                    </p>
                                </div>
                            </div>
                            <i title="" class="dashicons-before dashicons-editor-help"></i>
                        </span>                        
                    </label>
                    <input class="mec-col-4 <?php echo ($bookings_limit_unlimited == 1) ? 'mec-util-hidden' : ''; ?>" type="text" name="mec[booking][bookings_limit]" id="mec_bookings_limit"
                           value="<?php echo esc_attr($bookings_limit); ?>" placeholder="<?php _e('100', 'modern-events-calendar-lite'); ?>"/>
                </div>
            </div>
            <div class="mec-meta-box-fields mec-booking-tab-content" id="mec_meta_box_booking_options_form_2">
                <label for="mec_bookings_user_limit">
                    <h4 class="mec-title"><?php _e('Total user booking limits', 'modern-events-calendar-lite'); ?></h4>
                </label>
                <div class="mec-form-row">
                    <label class="mec-col-4" for="mec_bookings_user_limit_unlimited" id="mec_bookings_user_limit_unlimited_label">
                        <input type="hidden" name="mec[booking][bookings_user_limit_unlimited]" value="0"/>
                        <input id="mec_bookings_user_limit_unlimited"
                            <?php
                            if ($bookings_user_limit_unlimited == 1) {
                                echo 'checked="checked"';
                            }
                            ?>
                                type="checkbox" value="1" name="mec[booking][bookings_user_limit_unlimited]" onchange="javascript: $(this).parent().parent().find('input[type=text]').toggle().val('');"/>
                        <?php _e('Inherit from global options', 'modern-events-calendar-lite'); ?>
                    </label>
                    <input class="mec-col-4  <?php echo ($bookings_user_limit_unlimited == 1) ? 'mec-util-hidden' : ''; ?>" type="text" name="mec[booking][bookings_user_limit]" id="mec_bookings_user_limit"
                            value="<?php echo esc_attr($bookings_user_limit); ?>" placeholder="<?php _e('12', 'modern-events-calendar-lite'); ?>"/>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Show tickets options of event into the Add/Edit event page
     *
     * @author Webnus <info@webnus.biz>
     * @param object $post
     */
    public function meta_box_tickets($post)
    {
        $tickets = get_post_meta($post->ID, 'mec_tickets', true);
        if (!is_array($tickets)) {
            $tickets = array();
        }
        ?>
        <div class="mec-meta-box-fields mec-booking-tab-content" id="mec-tickets">
            <h4 class="mec-meta-box-header"><?php echo $this->main->m('tickets', __('Tickets', 'modern-events-calendar-lite')); ?></h4>

            <?php if ($post->ID != $this->main->get_original_event($post->ID)) : ?>
                <p class="warning-msg"><?php _e("You're translating an event so MEC will use the original event for tickets and booking. You can only translate the ticket name and description. Please define exact tickets that you defined in the original event here.", 'modern-events-calendar-lite'); ?></p>
            <?php endif; ?>

            <div id="mec_meta_box_tickets_form">
                <div class="mec-form-row">
                    <button class="button" type="button" id="mec_add_ticket_button"><?php _e('Add', 'modern-events-calendar-lite'); ?></button>
                </div>
                <div id="mec_tickets">
                    <?php
                    $i = 0;
                    foreach ($tickets as $key => $ticket) :
                        if (!is_numeric($key)) {
                            continue;
                        }
                        $i = max($i, $key);
                        ?>
                        <div class="mec-box" id="mec_ticket_row<?php echo $key; ?>">
                            <div class="mec-form-row">
                                <input type="text" class="mec-col-12" name="mec[tickets][<?php echo $key; ?>][name]"
                                       placeholder="<?php esc_attr_e('Ticket Name', 'modern-events-calendar-lite'); ?>"
                                       value="<?php echo(isset($ticket['name']) ? esc_attr($ticket['name']) : ''); ?>"/>
                            </div>
                            <div class="mec-form-row wn-ticket-time">
                                <div class="mec-ticket-start-time mec-col-12">
                                    <span><?php esc_html_e('Start Time', 'modern-events-calendar-lite'); ?></span>
                                    <select name="mec[tickets][<?php echo $key; ?>][ticket_start_time_hour]">
                                        <?php if(!isset($this->settings['time_format']) or (isset($this->settings['time_format']) and $this->settings['time_format'] != 24)) 
                                        {
                                            $hour_format = 'h';
                                            $count_info = array('start'=>1, 'length'=>12);
                                        }
                                        else
                                        {
                                            $hour_format = 'H';
                                            $count_info = array('start'=>0, 'length'=>23);
                                        }

                                        $render_hour = date($hour_format, strtotime(sprintf('%02d', $ticket['ticket_start_time_hour']) . ':' . sprintf('%02d', $ticket['ticket_start_time_minute']) . (isset($ticket['ticket_start_time_ampm']) ? strtoupper($ticket['ticket_start_time_ampm']) : '')));
                                        for ($j = $count_info['start']; $j <= $count_info['length']; $j++) : ?>
                                            <option
                                                <?php
                                                if ($render_hour == $j) 
                                                {
                                                    echo 'selected="selected"';
                                                }
                                                ?>
                                                    value="<?php echo $j; ?>"><?php echo sprintf('%02d', $j); ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <span class="time-dv">:</span>
                                    <select name="mec[tickets][<?php echo $key; ?>][ticket_start_time_minute]">
                                        <?php for ($k = 0; $k <= 11; $k++) : ?>
                                            <option
                                                <?php
                                                if ($ticket['ticket_start_time_minute'] == ($k * 5)) {
                                                    echo 'selected="selected"';
                                                }
                                                ?>
                                                    value="<?php echo($k * 5); ?>"><?php echo sprintf('%02d', ($k * 5)); ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <!-----Start AM/PM Section----->
                                    <?php if(!isset($this->settings['time_format']) or (isset($this->settings['time_format']) and $this->settings['time_format'] != 24)): ?>
                                    <select name="mec[tickets][<?php echo $key; ?>][ticket_start_time_ampm]">
                                        <option
                                            <?php
                                            if ($ticket['ticket_start_time_ampm'] == 'AM') {
                                                echo 'selected="selected"';
                                            }
                                            ?>
                                                value="AM"><?php _e('AM', 'modern-events-calendar-lite'); ?></option>
                                        <option
                                            <?php
                                            if ($ticket['ticket_start_time_ampm'] == 'PM') {
                                                echo 'selected="selected"';
                                            }
                                            ?>
                                                value="PM"><?php _e('PM', 'modern-events-calendar-lite'); ?></option>
                                    </select>
                                    <?php endif; ?>
                                    <!-----End AM/PM Section----->
                                </div>
                                <div class="mec-ticket-end-time mec-col-12">
                                    <span><?php esc_html_e('End Time', 'modern-events-calendar-lite'); ?></span>
                                    <select name="mec[tickets][<?php echo $key; ?>][ticket_end_time_hour]">
                                    <?php if(!isset($this->settings['time_format']) or (isset($this->settings['time_format']) and $this->settings['time_format'] != 24))
                                        {
                                            $hour_format = 'h';
                                            $count_info = array('start'=>1, 'length'=>12);
                                        } 
                                        else
                                        {
                                            $hour_format = 'H';
                                            $count_info = array('start'=>0, 'length'=>23);
                                        }

                                        $render_hour = date($hour_format, strtotime(sprintf('%02d', $ticket['ticket_end_time_hour']) . ':' . sprintf('%02d', $ticket['ticket_end_time_minute']) . (isset($ticket['ticket_end_time_ampm']) ? strtoupper($ticket['ticket_end_time_ampm']) : '')));
                                        for ($j = $count_info['start']; $j <= $count_info['length']; $j++) : ?>
                                            <option
                                                <?php
                                                if ($render_hour == $j) 
                                                {
                                                    echo 'selected="selected"';
                                                }
                                                ?>
                                                value="<?php echo $j; ?>"><?php echo sprintf('%02d', $j); ?></option>
                                    <?php endfor; ?>
                                    </select>
                                    <span class="time-dv">:</span>
                                    <select name="mec[tickets][<?php echo $key; ?>][ticket_end_time_minute]">
                                        <?php for ($k = 0; $k <= 11; $k++) : ?>
                                            <option
                                                <?php
                                                if ($ticket['ticket_end_time_minute'] == ($k * 5)) {
                                                    echo 'selected="selected"';
                                                }
                                                ?>
                                                    value="<?php echo($k * 5); ?>"><?php echo sprintf('%02d', ($k * 5)); ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <!-----Start AM/PM Section----->
                                    <?php if(!isset($this->settings['time_format']) or (isset($this->settings['time_format']) and $this->settings['time_format'] != 24)): ?>
                                    <select name="mec[tickets][<?php echo $key; ?>][ticket_end_time_ampm]">
                                        <option
                                            <?php
                                            if ($ticket['ticket_end_time_ampm'] == 'AM') {
                                                echo 'selected="selected"';
                                            }
                                            ?>
                                                value="AM"><?php _e('AM', 'modern-events-calendar-lite'); ?></option>
                                        <option
                                            <?php
                                            if ($ticket['ticket_end_time_ampm'] == 'PM') {
                                                echo 'selected="selected"';
                                            }
                                            ?>
                                                value="PM"><?php _e('PM', 'modern-events-calendar-lite'); ?></option>
                                    </select>
                                    <?php endif; ?>
                                    <!-----End AM/PM Section----->
                                </div>
                            </div>
                            <div class="mec-form-row">
                                <input type="text" class="mec-col-12"
                                       name="mec[tickets][<?php echo $key; ?>][description]"
                                       placeholder="<?php esc_attr_e('Description', 'modern-events-calendar-lite'); ?>"
                                       value="<?php echo(isset($ticket['description']) ? esc_attr($ticket['description']) : ''); ?>"/>
                            </div>
                            <div class="mec-form-row">
							<span class="mec-col-4">
								<input type="text" name="mec[tickets][<?php echo $key; ?>][price]"
                                       placeholder="<?php esc_attr_e('Price', 'modern-events-calendar-lite'); ?>"
                                       value="<?php echo(isset($ticket['price']) ? esc_attr($ticket['price']) : ''); ?>"/>
								<span class="mec-tooltip">
									<div class="box top">
										<h5 class="title"><?php _e('Price', 'modern-events-calendar-lite'); ?></h5>
										<div class="content"><p><?php esc_attr_e('Insert 0 for free ticket. Only numbers please.', 'modern-events-calendar-lite'); ?>
                                                <a href="https://webnus.net/dox/modern-events-calendar/add-a-booking-system/"
                                                   target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
									</div>
									<i title="" class="dashicons-before dashicons-editor-help"></i>
								</span>
							</span>
                                <span class="mec-col-8">
								<input type="text" name="mec[tickets][<?php echo $key; ?>][price_label]"
                                       placeholder="<?php esc_attr_e('Price Label', 'modern-events-calendar-lite'); ?>"
                                       value="<?php echo(isset($ticket['price_label']) ? esc_attr($ticket['price_label']) : ''); ?>"
                                       class="mec-col-12"/>
								<span class="mec-tooltip">
									<div class="box top">
										<h5 class="title"><?php _e('Price Label', 'modern-events-calendar-lite'); ?></h5>
										<div class="content"><p><?php esc_attr_e('For showing on website. e.g. $15', 'modern-events-calendar-lite'); ?>
                                                <a href="https://webnus.net/dox/modern-events-calendar/add-a-booking-system/"
                                                   target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
									</div>
									<i title="" class="dashicons-before dashicons-editor-help"></i>
								</span>
							</span>
                            </div>
                            <div class="mec-form-row">
                                <input class="mec-col-4" type="text" name="mec[tickets][<?php echo $key; ?>][limit]"
                                       placeholder="<?php esc_attr_e('Available Tickets', 'modern-events-calendar-lite'); ?>"
                                       value="<?php echo(isset($ticket['limit']) ? esc_attr($ticket['limit']) : '100'); ?>"/>
                                <label class="mec-col-2" for="mec_tickets_unlimited_<?php echo $key; ?>"
                                       id="mec_bookings_limit_unlimited_label<?php echo $key; ?>">
                                    <input type="hidden" name="mec[tickets][<?php echo $key; ?>][unlimited]" value="0"/>
                                    <input id="mec_tickets_unlimited_<?php echo $key; ?>" type="checkbox" value="1"
                                           name="mec[tickets][<?php echo $key; ?>][unlimited]"
                                        <?php
                                        if (isset($ticket['unlimited']) and $ticket['unlimited']) {
                                            echo 'checked="checked"';
                                        }
                                        ?>
                                    />
                                    <?php _e('Unlimited', 'modern-events-calendar-lite'); ?>
                                </label>
                                <button class="button" type="button"
                                        onclick="mec_ticket_remove(<?php echo $key; ?>);"><?php _e('Remove', 'modern-events-calendar-lite'); ?></button>
                            </div>
                            <?php do_action('custom_field_ticket', $ticket, $key); ?>
                            <div id="mec_price_per_dates_container">
                                <div class="mec-form-row">
                                    <h4><?php _e('Price per Date', 'modern-events-calendar-lite'); ?></h4>
                                    <button class="button mec_add_price_date_button" type="button"
                                            data-key="<?php echo $key; ?>"><?php _e('Add', 'modern-events-calendar-lite'); ?></button>
                                </div>
                                <div id="mec-ticket-price-dates-<?php echo $key; ?>">
                                    <?php if (isset($ticket['dates']) and count($ticket['dates'])) : ?>
                                        <?php
                                        $j = 0;
                                        foreach ($ticket['dates'] as $p => $price_date) :
                                            if (!is_numeric($p)) {
                                                continue;
                                            }
                                            $j = max($j, $p);
                                            ?>
                                            <div id="mec_ticket_price_raw_<?php echo $key; ?>_<?php echo $p; ?>">
                                                <div class="mec-form-row">
                                                    <input class="mec-col-3 mec_date_picker" type="text"
                                                           name="mec[tickets][<?php echo $key; ?>][dates][<?php echo $p; ?>][start]"
                                                           value="<?php echo isset($price_date['start']) ? $price_date['start'] : date('Y-m-d'); ?>"
                                                           placeholder="<?php esc_attr_e('Start', 'modern-events-calendar-lite'); ?>"/>
                                                    <input class="mec-col-3 mec_date_picker" type="text"
                                                           name="mec[tickets][<?php echo $key; ?>][dates][<?php echo $p; ?>][end]"
                                                           value="<?php echo isset($price_date['end']) ? $price_date['end'] : date('Y-m-d', strtotime('+10 days')); ?>"
                                                           placeholder="<?php esc_attr_e('End', 'modern-events-calendar-lite'); ?>"/>
                                                    <input class="mec-col-3" type="text"
                                                           name="mec[tickets][<?php echo $key; ?>][dates][<?php echo $p; ?>][price]"
                                                           value="<?php echo isset($price_date['price']) ? $price_date['price'] : ''; ?>"
                                                           placeholder="<?php esc_attr_e('Price', 'modern-events-calendar-lite'); ?>"/>
                                                    <input class="mec-col-2" type="text"
                                                           name="mec[tickets][<?php echo $key; ?>][dates][<?php echo $p; ?>][label]"
                                                           value="<?php echo isset($price_date['label']) ? $price_date['label'] : ''; ?>"
                                                           placeholder="<?php esc_attr_e('Label', 'modern-events-calendar-lite'); ?>"/>
                                                    <button class="button mec-col-1" type="button"
                                                            onclick="mec_ticket_price_remove(<?php echo $key; ?>, <?php echo $p; ?>)"><?php _e('Remove', 'modern-events-calendar-lite'); ?></button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" id="mec_new_ticket_price_key_<?php echo $key; ?>"
                                       value="<?php echo $j + 1; ?>"/>
                                <div class="mec-util-hidden" id="mec_new_ticket_price_raw_<?php echo $key; ?>">
                                    <div id="mec_ticket_price_raw_<?php echo $key; ?>_:j:">
                                        <div class="mec-form-row">
                                            <input class="mec-col-3 new_added" type="text"
                                                   name="mec[tickets][<?php echo $key; ?>][dates][:j:][start]"
                                                   value="<?php echo date('Y-m-d'); ?>"
                                                   placeholder="<?php esc_attr_e('Start', 'modern-events-calendar-lite'); ?>"/>
                                            <input class="mec-col-3 new_added" type="text"
                                                   name="mec[tickets][<?php echo $key; ?>][dates][:j:][end]"
                                                   value="<?php echo date('Y-m-d', strtotime('+10 days')); ?>"
                                                   placeholder="<?php esc_attr_e('End', 'modern-events-calendar-lite'); ?>"/>
                                            <input class="mec-col-3" type="text"
                                                   name="mec[tickets][<?php echo $key; ?>][dates][:j:][price]"
                                                   placeholder="<?php esc_attr_e('Price', 'modern-events-calendar-lite'); ?>"/>
                                            <input class="mec-col-2" type="text"
                                                   name="mec[tickets][<?php echo $key; ?>][dates][:j:][label]"
                                                   placeholder="<?php esc_attr_e('Label', 'modern-events-calendar-lite'); ?>"/>
                                            <button class="button mec-col-1" type="button"
                                                    onclick="mec_ticket_price_remove(<?php echo $key; ?>, :j:)"><?php _e('Remove', 'modern-events-calendar-lite'); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <input type="hidden" id="mec_new_ticket_key" value="<?php echo $i + 1; ?>"/>
            <div class="mec-util-hidden" id="mec_new_ticket_raw">
                <div class="mec-box" id="mec_ticket_row:i:">
                    <div class="mec-form-row">
                        <input class="mec-col-12" type="text" name="mec[tickets][:i:][name]"
                               placeholder="<?php esc_attr_e('Ticket Name', 'modern-events-calendar-lite'); ?>"/>
                    </div>
                    <div class="mec-form-row wn-ticket-time">
                        <div class="mec-ticket-start-time mec-col-12">
                            <span><?php esc_html_e('Start Time', 'modern-events-calendar-lite'); ?></span>
                            <select name="mec[tickets][:i:][ticket_start_time_hour]">
                                <?php if(!isset($this->settings['time_format']) or (isset($this->settings['time_format']) and $this->settings['time_format'] != 24)) $count_info = array('start' => 1, 'length' => 12);
                                    else $count_info = array('start' => 0, 'length' => 23);
                                        for ($j = $count_info['start']; $j <= $count_info['length']; $j++) : ?>
                                    <option
                                        <?php
                                        if (8 == $j) {
                                            echo 'selected="selected"';
                                        }
                                        ?>
                                            value="<?php echo $j; ?>"><?php echo sprintf('%02d', $j); ?></option>
                                <?php endfor;?>
                            </select>
                            <span class="time-dv">:</span>
                            <select name="mec[tickets][:i:][ticket_start_time_minute]">
                                <?php for ($k = 0; $k <= 11; $k++) : ?>
                                    <option value="<?php echo($k * 5); ?>"><?php echo sprintf('%02d', ($k * 5)); ?></option>
                                <?php endfor; ?>
                            </select>
                            <!------ Start Ticket AM/PM Section ------>
                            <?php  if(!isset($this->settings['time_format']) or (isset($this->settings['time_format']) and $this->settings['time_format'] != 24)): ?>
                            <select name="mec[tickets][:i:][ticket_start_time_ampm]">
                                <option selected="selected" value="AM"><?php _e('AM', 'modern-events-calendar-lite'); ?></option>
                                <option value="PM"><?php _e('PM', 'modern-events-calendar-lite'); ?></option>
                            </select>
                            <?php endif; ?>
                            <!------ End Ticket AM/PM Section ------>
                        </div>
                        <div class="mec-ticket-start-time mec-col-12">
                            <span><?php esc_html_e('End Time', 'modern-events-calendar-lite'); ?></span>
                            <select name="mec[tickets][:i:][ticket_end_time_hour]">
                                <?php if(!isset($this->settings['time_format']) or (isset($this->settings['time_format']) and $this->settings['time_format'] != 24))
                                            {
                                                $count_info = array('start' => 1, 'length' => 12);
                                                $end_selected = 6;
                                            } 
                                            else
                                            {
                                                $count_info = array('start' => 0, 'length' => 23);
                                                $end_selected = 18;
                                            }
                                            for ($j = $count_info['start']; $j <= $count_info['length']; $j++) : ?>
                                        <option
                                            <?php
                                            if ($end_selected == $j) 
                                            {
                                                echo 'selected="selected"';
                                            }
                                            ?>
                                                value="<?php echo $j; ?>"><?php echo sprintf('%02d', $j); ?></option>
                                <?php endfor;?>
                            </select>
                            <span class="time-dv">:</span>
                            <select name="mec[tickets][:i:][ticket_end_time_minute]">
                                <?php for ($k = 0; $k <= 11; $k++) : ?>
                                    <option value="<?php echo($k * 5); ?>"><?php echo sprintf('%02d', ($k * 5)); ?></option>
                                <?php endfor; ?>
                            </select>
                            <!------ Start Ticket AM/PM Section ------>
                            <?php  if(!isset($this->settings['time_format']) or (isset($this->settings['time_format']) and $this->settings['time_format'] != 24)): ?>
                            <select name="mec[tickets][:i:][ticket_end_time_ampm]">
                                <option value="AM"><?php _e('AM', 'modern-events-calendar-lite'); ?></option>
                                <option selected="selected" value="PM"><?php _e('PM', 'modern-events-calendar-lite'); ?></option>
                            </select>
                            <?php endif; ?>
                            <!------ End Ticket AM/PM Section ------>
                        </div>
                    </div>
                    <div class="mec-form-row">
                        <input class="mec-col-12" type="text" name="mec[tickets][:i:][description]"
                               placeholder="<?php esc_attr_e('Description', 'modern-events-calendar-lite'); ?>"/>
                    </div>
                    <div class="mec-form-row">
						<span class="mec-col-4">
							<input type="text" name="mec[tickets][:i:][price]"
                                   placeholder="<?php esc_attr_e('Price', 'modern-events-calendar-lite'); ?>"/>
							<span class="mec-tooltip">
								<div class="box top">
									<h5 class="title"><?php _e('Price', 'modern-events-calendar-lite'); ?></h5>
									<div class="content"><p><?php esc_attr_e('Insert 0 for free ticket. Only numbers please.', 'modern-events-calendar-lite'); ?>
                                            <a href="https://webnus.net/dox/modern-events-calendar/add-a-booking-system/"
                                               target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
								</div>
								<i title="" class="dashicons-before dashicons-editor-help"></i>
							</span>
						</span>
                        <span class="mec-col-8">
							<input type="text" name="mec[tickets][:i:][price_label]"
                                   placeholder="<?php esc_attr_e('Price Label', 'modern-events-calendar-lite'); ?>" class="mec-col-12"/>
							<span class="mec-tooltip">
								<div class="box top">
									<h5 class="title"><?php _e('Price Label', 'modern-events-calendar-lite'); ?></h5>
									<div class="content"><p><?php esc_attr_e('For showing on website. e.g. $15', 'modern-events-calendar-lite'); ?>
                                            <a href="https://webnus.net/dox/modern-events-calendar/add-a-booking-system/"
                                               target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
								</div>
								<i title="" class="dashicons-before dashicons-editor-help"></i>
							</span>
						</span>
                    </div>
                    <div class="mec-form-row">
                        <input class="mec-col-4" type="text" name="mec[tickets][:i:][limit]"
                               placeholder="<?php esc_attr_e('Available Tickets', 'modern-events-calendar-lite'); ?>"/>
                        <label class="mec-col-4" for="mec_tickets_unlimited_:i:"
                               id="mec_bookings_limit_unlimited_label">
                            <input type="hidden" name="mec[tickets][:i:][unlimited]" value="0"/>
                            <input id="mec_tickets_unlimited_:i:" type="checkbox" value="1"
                                   name="mec[tickets][:i:][unlimited]"/>
                            <?php _e('Unlimited', 'modern-events-calendar-lite'); ?>
                        </label>
                        <button class="button" type="button"
                                onclick="mec_ticket_remove(:i:)"><?php _e('Remove', 'modern-events-calendar-lite'); ?></button>
                    </div>
                    <div id="mec_price_per_dates_container_:i:">
                        <div class="mec-form-row">
                            <h4><?php _e('Price per Date', 'modern-events-calendar-lite'); ?></h4>
                            <button class="button mec_add_price_date_button" type="button"
                                    data-key=":i:"><?php _e('Add', 'modern-events-calendar-lite'); ?></button>
                        </div>
                        <div id="mec-ticket-price-dates-:i:">
                        </div>
                        <input type="hidden" id="mec_new_ticket_price_key_:i:" value="1"/>
                        <div class="mec-util-hidden" id="mec_new_ticket_price_raw_:i:">
                            <div id="mec_ticket_price_raw_:i:_:j:">
                                <div class="mec-form-row">
                                    <input class="mec-col-3 new_added" type="text"
                                           name="mec[tickets][:i:][dates][:j:][start]"
                                           value="<?php echo date('Y-m-d'); ?>"
                                           placeholder="<?php esc_attr_e('Start', 'modern-events-calendar-lite'); ?>"/>
                                    <input class="mec-col-3 new_added" type="text"
                                           name="mec[tickets][:i:][dates][:j:][end]"
                                           value="<?php echo date('Y-m-d', strtotime('+10 days')); ?>"
                                           placeholder="<?php esc_attr_e('End', 'modern-events-calendar-lite'); ?>"/>
                                    <input class="mec-col-3" type="text" name="mec[tickets][:i:][dates][:j:][price]"
                                           placeholder="<?php esc_attr_e('Price', 'modern-events-calendar-lite'); ?>"/>
                                    <input class="mec-col-2" type="text" name="mec[tickets][:i:][dates][:j:][label]"
                                           placeholder="<?php esc_attr_e('Label', 'modern-events-calendar-lite'); ?>"/>
                                    <button class="button mec-col-1" type="button"
                                            onclick="mec_ticket_price_remove(:i:, :j:)"><?php _e('Remove', 'modern-events-calendar-lite'); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Show fees of event into the Add/Edit event page
     *
     * @author Webnus <info@webnus.biz>
     * @param object $post
     */
    public function meta_box_fees($post)
    {
        $global_inheritance = get_post_meta($post->ID, 'mec_fees_global_inheritance', true);
        if (trim($global_inheritance) == '') {
            $global_inheritance = 1;
        }

        $fees = get_post_meta($post->ID, 'mec_fees', true);

        $global_fees = isset($this->settings['fees']) ? $this->settings['fees'] : array();
        if (!is_array($fees) and trim($fees) == '') {
            $fees = $global_fees;
        }

        if (!is_array($fees)) {
            $fees = array();
        }
        ?>
        <div class="mec-meta-box-fields mec-booking-tab-content" id="mec-fees">
            <h4 class="mec-meta-box-header"><?php _e('Fees', 'modern-events-calendar-lite'); ?></h4>
            <div id="mec_meta_box_fees_form">
                <div class="mec-form-row">
                    <label>
                        <input type="hidden" name="mec[fees_global_inheritance]" value="0"/>
                        <input onchange="jQuery('#mec_taxes_fees_container_toggle').toggle();" value="1" type="checkbox"
                               name="mec[fees_global_inheritance]"
                            <?php
                            if ($global_inheritance) {
                                echo 'checked="checked"';
                            }
                            ?>
                        /> <?php _e('Inherit from global options', 'modern-events-calendar-lite'); ?>
                    </label>
                </div>
                <div id="mec_taxes_fees_container_toggle" class="
				<?php
                if ($global_inheritance) {
                    echo 'mec-util-hidden';
                }
                ?>
				">
                    <div class="mec-form-row">
                        <button class="button" type="button" id="mec_add_fee_button"><?php _e('Add', 'modern-events-calendar-lite'); ?></button>
                    </div>
                    <div id="mec_fees_list">
                        <?php
                        $i = 0;
                        foreach ($fees as $key => $fee) :
                            if (!is_numeric($key)) {
                                continue;
                            }
                            $i = max($i, $key);
                            ?>
                            <div class="mec-box" id="mec_fee_row<?php echo $i; ?>">
                                <div class="mec-form-row">
                                    <input class="mec-col-12" type="text" name="mec[fees][<?php echo $i; ?>][title]"
                                           placeholder="<?php esc_attr_e('Fee Title', 'modern-events-calendar-lite'); ?>"
                                           value="<?php echo(isset($fee['title']) ? esc_attr($fee['title']) : ''); ?>"/>
                                </div>
                                <div class="mec-form-row">
								<span class="mec-col-4">
									<input type="text" name="mec[fees][<?php echo $i; ?>][amount]"
                                           placeholder="<?php esc_attr_e('Amount', 'modern-events-calendar-lite'); ?>"
                                           value="<?php echo(isset($fee['amount']) ? esc_attr($fee['amount']) : ''); ?>"/>
									<span class="mec-tooltip">
										<div class="box top">
											<h5 class="title"><?php _e('Amount', 'modern-events-calendar-lite'); ?></h5>
											<div class="content"><p><?php esc_attr_e('Fee amount, considered as fixed amount if you set the type to amount otherwise considered as percentage', 'modern-events-calendar-lite'); ?>
                                                    <a href="https://webnus.net/dox/modern-events-calendar/tickets-and-taxes-fees/"
                                                       target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
										</div>
										<i title="" class="dashicons-before dashicons-editor-help"></i>
									</span>
								</span>
                                    <span class="mec-col-4">
									<select name="mec[fees][<?php echo $i; ?>][type]">
										<option value="percent" <?php echo((isset($fee['type']) and $fee['type'] == 'percent') ? 'selected="selected"' : ''); ?>><?php _e('Percent', 'modern-events-calendar-lite'); ?></option>
										<option value="amount" <?php echo((isset($fee['type']) and $fee['type'] == 'amount') ? 'selected="selected"' : ''); ?>><?php _e('Amount (Per Ticket)', 'modern-events-calendar-lite'); ?></option>
										<option value="amount_per_booking" <?php echo((isset($fee['type']) and $fee['type'] == 'amount_per_booking') ? 'selected="selected"' : ''); ?>><?php _e('Amount (Per Booking)', 'modern-events-calendar-lite'); ?></option>
									</select>
								</span>
                                    <button class="button" type="button" id="mec_remove_fee_button<?php echo $i; ?>"
                                            onclick="mec_remove_fee(<?php echo $i; ?>);"><?php _e('Remove', 'modern-events-calendar-lite'); ?></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <input type="hidden" id="mec_new_fee_key" value="<?php echo $i + 1; ?>"/>
            <div class="mec-util-hidden" id="mec_new_fee_raw">
                <div class="mec-box" id="mec_fee_row:i:">
                    <div class="mec-form-row">
                        <input class="mec-col-12" type="text" name="mec[fees][:i:][title]"
                               placeholder="<?php esc_attr_e('Fee Title', 'modern-events-calendar-lite'); ?>"/>
                    </div>
                    <div class="mec-form-row">
						<span class="mec-col-4">
							<input type="text" name="mec[fees][:i:][amount]"
                                   placeholder="<?php esc_attr_e('Amount', 'modern-events-calendar-lite'); ?>"/>
							<span class="mec-tooltip">
								<div class="box top">
									<h5 class="title"><?php _e('Amount', 'modern-events-calendar-lite'); ?></h5>
									<div class="content"><p><?php esc_attr_e('Fee amount, considered as fixed amount if you set the type to amount otherwise considered as percentage', 'modern-events-calendar-lite'); ?>
                                            <a href="https://webnus.net/dox/modern-events-calendar/tickets-and-taxes-fees/"
                                               target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
								</div>
								<i title="" class="dashicons-before dashicons-editor-help"></i>
							</span>
						</span>
                        <span class="mec-col-4">
							<select name="mec[fees][:i:][type]">
								<option value="percent"><?php _e('Percent', 'modern-events-calendar-lite'); ?></option>
								<option value="amount"><?php _e('Amount (Per Ticket)', 'modern-events-calendar-lite'); ?></option>
								<option value="amount_per_booking"><?php _e('Amount (Per Booking)', 'modern-events-calendar-lite'); ?></option>
							</select>
						</span>
                        <button class="button" type="button" id="mec_remove_fee_button:i:"
                                onclick="mec_remove_fee(:i:);"><?php _e('Remove', 'modern-events-calendar-lite'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Show ticket variations into the Add/Edit event page
     *
     * @author Webnus <info@webnus.biz>
     * @param object $post
     */
    public function meta_box_ticket_variations($post)
    {
        $global_inheritance = get_post_meta($post->ID, 'mec_ticket_variations_global_inheritance', true);
        if (trim($global_inheritance) == '') {
            $global_inheritance = 1;
        }

        $ticket_variations = get_post_meta($post->ID, 'mec_ticket_variations', true);

        $global_variations = isset($this->settings['ticket_variations']) ? $this->settings['ticket_variations'] : array();
        if (!is_array($ticket_variations) and trim($ticket_variations) == '') {
            $ticket_variations = $global_variations;
        }

        if (!is_array($ticket_variations)) {
            $ticket_variations = array();
        }
        ?>
        <div class="mec-meta-box-fields mec-booking-tab-content" id="mec-ticket-variations">
            <h4 class="mec-meta-box-header"><?php _e('Ticket Variations / Options', 'modern-events-calendar-lite'); ?></h4>
            <div id="mec_meta_box_ticket_variations_form">
                <div class="mec-form-row">
                    <label>
                        <input type="hidden" name="mec[ticket_variations_global_inheritance]" value="0"/>
                        <input onchange="jQuery('#mec_taxes_ticket_variations_container_toggle').toggle();" value="1"
                               type="checkbox" name="mec[ticket_variations_global_inheritance]"
                            <?php
                            if ($global_inheritance) {
                                echo 'checked="checked"';
                            }
                            ?>
                        /> <?php _e('Inherit from global options', 'modern-events-calendar-lite'); ?>
                    </label>
                </div>
                <div id="mec_taxes_ticket_variations_container_toggle" class="
				<?php
                if ($global_inheritance) {
                    echo 'mec-util-hidden';
                }
                ?>
				">
                    <div class="mec-form-row">
                        <button class="button" type="button"
                                id="mec_add_ticket_variation_button"><?php _e('Add', 'modern-events-calendar-lite'); ?></button>
                    </div>
                    <div id="mec_ticket_variations_list">
                        <?php
                        $i = 0;
                        foreach ($ticket_variations as $key => $ticket_variation) :
                            if (!is_numeric($key)) {
                                continue;
                            }
                            $i = max($i, $key);
                            ?>
                            <div class="mec-box" id="mec_ticket_variation_row<?php echo $i; ?>">
                                <div class="mec-form-row">
                                    <input class="mec-col-12" type="text"
                                           name="mec[ticket_variations][<?php echo $i; ?>][title]"
                                           placeholder="<?php esc_attr_e('Title', 'modern-events-calendar-lite'); ?>"
                                           value="<?php echo(isset($ticket_variation['title']) ? esc_attr($ticket_variation['title']) : ''); ?>"/>
                                </div>
                                <div class="mec-form-row">
									<span class="mec-col-4">
										<input type="text" name="mec[ticket_variations][<?php echo $i; ?>][price]"
                                               placeholder="<?php esc_attr_e('Price', 'modern-events-calendar-lite'); ?>"
                                               value="<?php echo(isset($ticket_variation['price']) ? esc_attr($ticket_variation['price']) : ''); ?>"/>
										<span class="mec-tooltip">
											<div class="box top">
												<h5 class="title"><?php _e('Price', 'modern-events-calendar-lite'); ?></h5>
												<div class="content"><p><?php esc_attr_e('Option Price', 'modern-events-calendar-lite'); ?><a
                                                                href="https://webnus.net/dox/modern-events-calendar/ticket-variations/"
                                                                target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
											</div>
											<i title="" class="dashicons-before dashicons-editor-help"></i>
										</span>
									</span>
                                    <span class="mec-col-4">
										<input type="number" min="0"
                                               name="mec[ticket_variations][<?php echo $i; ?>][max]"
                                               placeholder="<?php esc_attr_e('Maximum Per Ticket', 'modern-events-calendar-lite'); ?>"
                                               value="<?php echo(isset($ticket_variation['max']) ? $ticket_variation['max'] : ''); ?>"/>
										<span class="mec-tooltip">
											<div class="box top">
												<h5 class="title"><?php _e('Maximum Per Ticket', 'modern-events-calendar-lite'); ?></h5>
												<div class="content"><p><?php esc_attr_e('Maximum Per Ticket. Leave it blank for unlimited.', 'modern-events-calendar-lite'); ?>
                                                        <a href="https://webnus.net/dox/modern-events-calendar/ticket-variations/"
                                                           target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
											</div>
											<i title="" class="dashicons-before dashicons-editor-help"></i>
										</span>
									</span>
                                    <button class="button" type="button"
                                            id="mec_remove_ticket_variation_button<?php echo $i; ?>"
                                            onclick="mec_remove_ticket_variation(<?php echo $i; ?>);"><?php _e('Remove', 'modern-events-calendar-lite'); ?></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <input type="hidden" id="mec_new_ticket_variation_key" value="<?php echo $i + 1; ?>"/>
            <div class="mec-util-hidden" id="mec_new_ticket_variation_raw">
                <div class="mec-box" id="mec_ticket_variation_row:i:">
                    <div class="mec-form-row">
                        <input class="mec-col-12" type="text" name="mec[ticket_variations][:i:][title]"
                               placeholder="<?php esc_attr_e('Title', 'modern-events-calendar-lite'); ?>"/>
                    </div>
                    <div class="mec-form-row">
						<span class="mec-col-4">
							<input type="text" name="mec[ticket_variations][:i:][price]"
                                   placeholder="<?php esc_attr_e('Price', 'modern-events-calendar-lite'); ?>"/>
							<span class="mec-tooltip">
								<div class="box top">
									<h5 class="title"><?php _e('Price', 'modern-events-calendar-lite'); ?></h5>
									<div class="content"><p><?php esc_attr_e('Option Price', 'modern-events-calendar-lite'); ?><a
                                                    href="https://webnus.net/dox/modern-events-calendar/ticket-variations/"
                                                    target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
								</div>
								<i title="" class="dashicons-before dashicons-editor-help"></i>
							</span>
						</span>
                        <span class="mec-col-4">
							<input type="number" min="0" name="mec[ticket_variations][:i:][max]"
                                   placeholder="<?php esc_attr_e('Maximum Per Ticket', 'modern-events-calendar-lite'); ?>" value="1"/>
							<span class="mec-tooltip">
								<div class="box top">
									<h5 class="title"><?php _e('Maximum Per Ticket', 'modern-events-calendar-lite'); ?></h5>
									<div class="content"><p><?php esc_attr_e('Maximum Per Ticket. Leave it blank for unlimited.', 'modern-events-calendar-lite'); ?>
                                            <a href="https://webnus.net/dox/modern-events-calendar/ticket-variations/"
                                               target="_blank"><?php _e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
								</div>
								<i title="" class="dashicons-before dashicons-editor-help"></i>
							</span>
						</span>
                        <button class="button" type="button" id="mec_remove_ticket_variation_button:i:"
                                onclick="mec_remove_ticket_variation(:i:);"><?php _e('Remove', 'modern-events-calendar-lite'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Show registration form of event into the Add/Edit event page
     *
     * @author Webnus <info@webnus.biz>
     * @param object $post
     */
    public function meta_box_regform($post)
    {
        do_action('mec_events_meta_box_regform_start', $post);
        $global_inheritance = get_post_meta($post->ID, 'mec_reg_fields_global_inheritance', true);

        if (trim($global_inheritance) == '') {
            $global_inheritance = 1;
        }
        $reg_fields = get_post_meta($post->ID, 'mec_reg_fields', true);

        $global_reg_fields = $this->main->get_reg_fields();
        if ((is_array($reg_fields) and !count($reg_fields)) or (!is_array($reg_fields) and trim($reg_fields) == '')) {
            $reg_fields = $global_reg_fields;
        }

        if (!is_array($reg_fields)) {
            $reg_fields = array();
        }

        $mec_name = false;
        $mec_email = false;
        foreach ($reg_fields as $field) {
            if (isset($field['type'])) {
                if ($field['type'] == 'mec_email') {
                    $mec_email = true;
                }
                if ($field['type'] == 'name') {
                    $mec_name = true;
                }
            } else {
                break;
            }
        }

        if (!$mec_name) {
            array_unshift(
                $reg_fields,
                [
                    'mandatory' => '0',
                    'type' => 'name',
                    'label' => esc_html__('Name', 'modern-events-calendar-lite'),
                ]
            );
        }

        if (!$mec_email) {
            array_unshift(
                $reg_fields,
                [
                    'mandatory' => '0',
                    'type' => 'mec_email',
                    'label' => esc_html__('Email', 'modern-events-calendar-lite'),
                ]
            );
        }

        ?>
        <div class="mec-meta-box-fields mec-booking-tab-content" id="mec-reg-fields">
            <h4 class="mec-meta-box-header"><?php _e('Booking Form', 'modern-events-calendar-lite'); ?></h4>
            <div id="mec_meta_box_reg_fields_form">
                <div class="mec-form-row">
                    <label>
                        <input type="hidden" name="mec[reg_fields_global_inheritance]" value="0"/>
                        <input onchange="jQuery('#mec_regform_container_toggle').toggle();" value="1" type="checkbox"
                               name="mec[reg_fields_global_inheritance]"
                            <?php
                            if ($global_inheritance) {
                                echo 'checked="checked"';
                            }
                            ?>
                        /> <?php _e('Inherit from global options', 'modern-events-calendar-lite'); ?>
                    </label>
                </div>
                <?php do_action('mec_meta_box_reg_fields_form', $post->ID); ?>
                <div id="mec_regform_container_toggle" class="
				<?php
                if ($global_inheritance) {
                    echo 'mec-util-hidden';
                }
                ?>
				">

                    <?php /** Don't remove this hidden field **/ ?>
                    <input type="hidden" name="mec[reg_fields]" value=""/>

                    <ul id="mec_reg_form_fields">
                        <?php
                        $i = 0;
                        foreach ($reg_fields as $key => $reg_field) {
                            if (!is_numeric($key)) {
                                continue;
                            }
                            $i = max($i, $key);
                            if ($reg_field['type'] == 'text') {
                                echo $this->main->field_text($key, $reg_field);
                            } elseif ($reg_field['type'] == 'mec_email') {
                                echo $this->main->field_mec_email($key, $reg_field);
                            } elseif ($reg_field['type'] == 'name') {
                                echo $this->main->field_name($key, $reg_field);
                            } elseif ($reg_field['type'] == 'email') {
                                echo $this->main->field_email($key, $reg_field);
                            } elseif ($reg_field['type'] == 'date') {
                                echo $this->main->field_date($key, $reg_field);
                            } elseif ($reg_field['type'] == 'file') {
                                echo $this->main->field_file($key, $reg_field);
                            } elseif ($reg_field['type'] == 'tel') {
                                echo $this->main->field_tel($key, $reg_field);
                            } elseif ($reg_field['type'] == 'textarea') {
                                echo $this->main->field_textarea($key, $reg_field);
                            } elseif ($reg_field['type'] == 'p') {
                                echo $this->main->field_p($key, $reg_field);
                            } elseif ($reg_field['type'] == 'checkbox') {
                                echo $this->main->field_checkbox($key, $reg_field);
                            } elseif ($reg_field['type'] == 'radio') {
                                echo $this->main->field_radio($key, $reg_field);
                            } elseif ($reg_field['type'] == 'select') {
                                echo $this->main->field_select($key, $reg_field);
                            } elseif ($reg_field['type'] == 'agreement') {
                                echo $this->main->field_agreement($key, $reg_field);
                            }
                        }
                        ?>
                    </ul>
                    <div id="mec_reg_form_field_types">
                        <button type="button" class="button red" data-type="name"><?php _e('MEC Name', 'modern-events-calendar-lite'); ?></button>
                        <button type="button" class="button red" data-type="mec_email"><?php _e('MEC Email', 'modern-events-calendar-lite'); ?></button>
                        <button type="button" class="button" data-type="text"><?php _e('Text', 'modern-events-calendar-lite'); ?></button>
                        <button type="button" class="button" data-type="email"><?php _e('Email', 'modern-events-calendar-lite'); ?></button>
                        <button type="button" class="button" data-type="date"><?php _e('Date', 'modern-events-calendar-lite'); ?></button>
                        <button type="button" class="button" data-type="tel"><?php _e('Tel', 'modern-events-calendar-lite'); ?></button>
                        <button type="button" class="button" data-type="file"><?php _e('File', 'modern-events-calendar-lite'); ?></button>
                        <button type="button" class="button" data-type="textarea"><?php _e('Textarea', 'modern-events-calendar-lite'); ?></button>
                        <button type="button" class="button" data-type="checkbox"><?php _e('Checkboxes', 'modern-events-calendar-lite'); ?></button>
                        <button type="button" class="button" data-type="radio"><?php _e('Radio Buttons', 'modern-events-calendar-lite'); ?></button>
                        <button type="button" class="button" data-type="select"><?php _e('Dropdown', 'modern-events-calendar-lite'); ?></button>
                        <button type="button" class="button" data-type="agreement"><?php _e('Agreement', 'modern-events-calendar-lite'); ?></button>
                        <button type="button" class="button" data-type="p"><?php _e('Paragraph', 'modern-events-calendar-lite'); ?></button>
                    </div>
                    <input type="hidden" id="mec_new_reg_field_key" value="<?php echo $i + 1; ?>"/>
                    <div class="mec-util-hidden">
                        <div id="mec_reg_field_text">
                            <?php echo $this->main->field_text(':i:'); ?>
                        </div>
                        <div id="mec_reg_field_email">
                            <?php echo $this->main->field_email(':i:'); ?>
                        </div>
                        <div id="mec_reg_field_mec_email">
                            <?php echo $this->main->field_mec_email(':i:'); ?>
                        </div>
                        <div id="mec_reg_field_name">
                            <?php echo $this->main->field_name(':i:'); ?>
                        </div>
                        <div id="mec_reg_field_tel">
                            <?php echo $this->main->field_tel(':i:'); ?>
                        </div>
                        <div id="mec_reg_field_date">
                            <?php echo $this->main->field_date(':i:'); ?>
                        </div>
                        <div id="mec_reg_field_file">
                            <?php echo $this->main->field_file(':i:'); ?>
                        </div>
                        <div id="mec_reg_field_textarea">
                            <?php echo $this->main->field_textarea(':i:'); ?>
                        </div>
                        <div id="mec_reg_field_checkbox">
                            <?php echo $this->main->field_checkbox(':i:'); ?>
                        </div>
                        <div id="mec_reg_field_radio">
                            <?php echo $this->main->field_radio(':i:'); ?>
                        </div>
                        <div id="mec_reg_field_select">
                            <?php echo $this->main->field_select(':i:'); ?>
                        </div>
                        <div id="mec_reg_field_agreement">
                            <?php echo $this->main->field_agreement(':i:'); ?>
                        </div>
                        <div id="mec_reg_field_p">
                            <?php echo $this->main->field_p(':i:'); ?>
                        </div>
                        <div id="mec_reg_field_option">
                            <?php echo $this->main->field_option(':fi:', ':i:'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        do_action('mec_events_meta_box_regform_end', $post->ID);
    }

    /**
     * Save event data
     *
     * @author Webnus <info@webnus.biz>
     * @param int $post_id
     * @return void
     */
    public function save_event($post_id)
    {
        // Check if our nonce is set.
        if (!isset($_POST['mec_event_nonce'])) {
            return;
        }

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($_POST['mec_event_nonce'], 'mec_event_data')) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') and DOING_AUTOSAVE) {
            return;
        }

        // Get Modern Events Calendar Data
        $_mec = isset($_POST['mec']) ? $_POST['mec'] : array();

        $start_date = (isset($_mec['date']['start']['date']) and trim($_mec['date']['start']['date'])) ? $_mec['date']['start']['date'] : date('Y-m-d');
        $end_date = (isset($_mec['date']['end']['date']) and trim($_mec['date']['end']['date'])) ? $_mec['date']['end']['date'] : date('Y-m-d');
        
        $event = $this->db->select("SELECT * FROM `#__mec_events` WHERE `post_id` = {$post_id}", 'loadAssoc');
        if(!is_array($event)) $event = array();

        $booking_date_update = false;
        if(count($event))
        {
            $past_start_date = (isset($event['start']) and trim($event['start'])) ? $event['start'] : '';
            $past_end_date = (isset($event['end']) and trim($event['end'])) ? $event['end'] : '';
            
            if(trim($start_date) != trim($past_start_date) or trim($end_date) != trim($past_end_date)) $booking_date_update = true;
        }

        // Remove Cached Data
        wp_cache_delete($post_id, 'mec-events-data');

        $location_id = isset($_mec['location_id']) ? sanitize_text_field($_mec['location_id']) : 0;
        $dont_show_map = isset($_mec['dont_show_map']) ? sanitize_text_field($_mec['dont_show_map']) : 0;
        $organizer_id = isset($_mec['organizer_id']) ? sanitize_text_field($_mec['organizer_id']) : 0;
        $read_more = isset($_mec['read_more']) ? sanitize_text_field($_mec['read_more']) : '';
        $more_info = (isset($_mec['more_info']) and trim($_mec['more_info'])) ? (strpos($_mec['more_info'], 'http') === false ? 'http://' . sanitize_text_field($_mec['more_info']) : sanitize_text_field($_mec['more_info'])) : '';
        $more_info_title = isset($_mec['more_info_title']) ? sanitize_text_field($_mec['more_info_title']) : '';
        $more_info_target = isset($_mec['more_info_target']) ? sanitize_text_field($_mec['more_info_target']) : '';
        $cost = isset($_mec['cost']) ? sanitize_text_field($_mec['cost']) : '';

        update_post_meta($post_id, 'mec_location_id', $location_id);
        update_post_meta($post_id, 'mec_dont_show_map', $dont_show_map);
        update_post_meta($post_id, 'mec_organizer_id', $organizer_id);
        update_post_meta($post_id, 'mec_read_more', $read_more);
        update_post_meta($post_id, 'mec_more_info', $more_info);
        update_post_meta($post_id, 'mec_more_info_title', $more_info_title);
        update_post_meta($post_id, 'mec_more_info_target', $more_info_target);
        update_post_meta($post_id, 'mec_cost', $cost);

        do_action('update_custom_dev_post_meta', $_mec, $post_id);

        // Additional Organizers
        $additional_organizer_ids = isset($_mec['additional_organizer_ids']) ? $_mec['additional_organizer_ids'] : array();

        foreach ($additional_organizer_ids as $additional_organizer_id) {
            wp_set_object_terms($post_id, (int)$additional_organizer_id, 'mec_organizer', true);
        }
        update_post_meta($post_id, 'mec_additional_organizer_ids', $additional_organizer_ids);

        // Additional locations
        $additional_location_ids = isset($_mec['additional_location_ids']) ? $_mec['additional_location_ids'] : array();

        foreach ($additional_location_ids as $additional_location_id) {
            wp_set_object_terms($post_id, (int)$additional_location_id, 'mec_location', true);
        }
        update_post_meta($post_id, 'mec_additional_location_ids', $additional_location_ids);

        // Date Options
        $date = isset($_mec['date']) ? $_mec['date'] : array();

        $start_date = date('Y-m-d', strtotime($start_date));

        // Set the date if it's empty
        if (trim($date['start']['date']) == '') {
            $date['start']['date'] = $start_date;
        }

        $start_time_hour = isset($date['start']) ? $date['start']['hour'] : '8';
        $start_time_minutes = isset($date['start']) ? $date['start']['minutes'] : '00';
        $start_time_ampm = (isset($date['start']) and isset($date['start']['ampm'])) ? $date['start']['ampm'] : 'AM';

        $end_date = date('Y-m-d', strtotime($end_date));

        // Fix end_date if it's smaller than start_date
        if (strtotime($end_date) < strtotime($start_date)) {
            $end_date = $start_date;
        }

        // Set the date if it's empty
        if (trim($date['end']['date']) == '') {
            $date['end']['date'] = $end_date;
        }

        $end_time_hour = isset($date['end']) ? $date['end']['hour'] : '6';
        $end_time_minutes = isset($date['end']) ? $date['end']['minutes'] : '00';
        $end_time_ampm = (isset($date['end']) and isset($date['end']['ampm'])) ? $date['end']['ampm'] : 'PM';

        // If 24 hours format is enabled then convert it back to 12 hours
        if (isset($this->settings['time_format']) and $this->settings['time_format'] == 24) {
            if ($start_time_hour < 12) {
                $start_time_ampm = 'AM';
            } elseif ($start_time_hour == 12) {
                $start_time_ampm = 'PM';
            } elseif ($start_time_hour > 12) {
                $start_time_hour -= 12;
                $start_time_ampm = 'PM';
            } elseif ($start_time_hour == 0) {
                $start_time_hour = 12;
                $start_time_ampm = 'AM';
            }

            if ($end_time_hour < 12) {
                $end_time_ampm = 'AM';
            } elseif ($end_time_hour == 12) {
                $end_time_ampm = 'PM';
            } elseif ($end_time_hour > 12) {
                $end_time_hour -= 12;
                $end_time_ampm = 'PM';
            } elseif ($end_time_hour == 0) {
                $end_time_hour = 12;
                $end_time_ampm = 'AM';
            }

            // Set converted values to date array
            $date['start']['hour'] = $start_time_hour;
            $date['start']['ampm'] = $start_time_ampm;

            $date['end']['hour'] = $end_time_hour;
            $date['end']['ampm'] = $end_time_ampm;
        }

        $allday = isset($date['allday']) ? 1 : 0;
        $hide_time = isset($date['hide_time']) ? 1 : 0;
        $hide_end_time = isset($date['hide_end_time']) ? 1 : 0;
        $comment = isset($date['comment']) ? $date['comment'] : '';

        // Set start time and end time if event is all day
        if ($allday == 1) {
            $start_time_hour = '8';
            $start_time_minutes = '00';
            $start_time_ampm = 'AM';

            $end_time_hour = '6';
            $end_time_minutes = '00';
            $end_time_ampm = 'PM';
        }

        // Repeat Options
        $repeat = isset($date['repeat']) ? $date['repeat'] : array();
        $certain_weekdays = isset($repeat['certain_weekdays']) ? $repeat['certain_weekdays'] : array();

        $repeat_status = isset($repeat['status']) ? 1 : 0;
        $repeat_type = ($repeat_status and isset($repeat['type'])) ? $repeat['type'] : '';

        $repeat_interval = ($repeat_status and isset($repeat['interval']) and trim($repeat['interval'])) ? $repeat['interval'] : 1;

        // Advanced Repeat
        $advanced = isset($repeat['advanced']) ? sanitize_text_field($repeat['advanced']) : '';

        if (!is_numeric($repeat_interval)) {
            $repeat_interval = null;
        }

        if ($repeat_type == 'weekly') {
            $interval_multiply = 7;
        } else {
            $interval_multiply = 1;
        }

        // Reset certain weekdays if repeat type is not set to certain weekdays
        if ($repeat_type != 'certain_weekdays') {
            $certain_weekdays = array();
        }

        if (!is_null($repeat_interval)) {
            $repeat_interval = $repeat_interval * $interval_multiply;
        }

        // String To Array
        if ($repeat_type == 'advanced' and trim($advanced)) $advanced = explode('-', $advanced);
        else $advanced = array();

        $repeat_end = ($repeat_status and isset($repeat['end'])) ? $repeat['end'] : '';
        $repeat_end_at_occurrences = ($repeat_status and isset($repeat['end_at_occurrences'])) ? ($repeat['end_at_occurrences'] - 1) : '';
        $repeat_end_at_date = ($repeat_status and isset($repeat['end_at_date'])) ? $repeat['end_at_date'] : '';
        if (trim($repeat_end_at_date) != '') {
            $repeat_end_at_date = date('Y-m-d', strtotime($repeat_end_at_date));
        }

        $day_start_seconds = $this->main->time_to_seconds($this->main->to_24hours($start_time_hour, $start_time_ampm), $start_time_minutes);
        $day_end_seconds = $this->main->time_to_seconds($this->main->to_24hours($end_time_hour, $end_time_ampm), $end_time_minutes);

        update_post_meta($post_id, 'mec_date', $date);
        update_post_meta($post_id, 'mec_repeat', $repeat);
        update_post_meta($post_id, 'mec_certain_weekdays', $certain_weekdays);
        update_post_meta($post_id, 'mec_allday', $allday);
        update_post_meta($post_id, 'mec_hide_time', $hide_time);
        update_post_meta($post_id, 'mec_hide_end_time', $hide_end_time);
        update_post_meta($post_id, 'mec_comment', $comment);

        do_action('update_custom_post_meta', $date, $post_id);

        update_post_meta($post_id, 'mec_start_date', $start_date);
        update_post_meta($post_id, 'mec_start_time_hour', $start_time_hour);
        update_post_meta($post_id, 'mec_start_time_minutes', $start_time_minutes);
        update_post_meta($post_id, 'mec_start_time_ampm', $start_time_ampm);
        update_post_meta($post_id, 'mec_start_day_seconds', $day_start_seconds);

        update_post_meta($post_id, 'mec_end_date', $end_date);
        update_post_meta($post_id, 'mec_end_time_hour', $end_time_hour);
        update_post_meta($post_id, 'mec_end_time_minutes', $end_time_minutes);
        update_post_meta($post_id, 'mec_end_time_ampm', $end_time_ampm);
        update_post_meta($post_id, 'mec_end_day_seconds', $day_end_seconds);

        update_post_meta($post_id, 'mec_repeat_status', $repeat_status);
        update_post_meta($post_id, 'mec_repeat_type', $repeat_type);
        update_post_meta($post_id, 'mec_repeat_interval', $repeat_interval);
        update_post_meta($post_id, 'mec_repeat_end', $repeat_end);
        update_post_meta($post_id, 'mec_repeat_end_at_occurrences', $repeat_end_at_occurrences);
        update_post_meta($post_id, 'mec_repeat_end_at_date', $repeat_end_at_date);
        update_post_meta($post_id, 'mec_advanced_days', $advanced);

        // Creating $event array for inserting in mec_events table
        $event = array(
            'post_id' => $post_id,
            'start' => $start_date,
            'repeat' => $repeat_status,
            'rinterval' => (!in_array($repeat_type, array('daily', 'weekly')) ? null : $repeat_interval),
            'time_start' => $day_start_seconds,
            'time_end' => $day_end_seconds,
        );

        $year = null;
        $month = null;
        $day = null;
        $week = null;
        $weekday = null;
        $weekdays = null;

        // MEC weekdays
        $mec_weekdays = $this->main->get_weekdays();

        // MEC weekends
        $mec_weekends = $this->main->get_weekends();

        $plus_date = '';
        if ($repeat_type == 'daily') {
            $plus_date = '+' . $repeat_end_at_occurrences * $repeat_interval . ' Days';
        } elseif ($repeat_type == 'weekly') {
            $plus_date = '+' . $repeat_end_at_occurrences * ($repeat_interval) . ' Days';
        } elseif ($repeat_type == 'weekday') {
            $repeat_interval = 1;
            $plus_date = '+' . $repeat_end_at_occurrences * $repeat_interval . ' Weekdays';

            $weekdays = ',' . implode(',', $mec_weekdays) . ',';
        } elseif ($repeat_type == 'weekend') {
            $repeat_interval = 1;
            $plus_date = '+' . round($repeat_end_at_occurrences / 2) * ($repeat_interval * 7) . ' Days';

            $weekdays = ',' . implode(',', $mec_weekends) . ',';
        } elseif ($repeat_type == 'certain_weekdays') {

            $repeat_interval = 1;
            $plus_date = '+' . ceil(($repeat_end_at_occurrences * $repeat_interval) * (7 / count($certain_weekdays))) . ' days';

            $weekdays = ',' . implode(',', $certain_weekdays) . ',';
        } elseif ($repeat_type == 'monthly') {
            $plus_date = '+' . $repeat_end_at_occurrences * $repeat_interval . ' Months';

            $year = '*';
            $month = '*';

            $s = $start_date;
            $e = $end_date;

            $_days = array();
            while (strtotime($s) <= strtotime($e)) {
                $_days[] = date('d', strtotime($s));
                $s = date('Y-m-d', strtotime('+1 Day', strtotime($s)));
            }

            $day = ',' . implode(',', array_unique($_days)) . ',';

            $week = '*';
            $weekday = '*';
        } elseif ($repeat_type == 'yearly') {
            $plus_date = '+' . $repeat_end_at_occurrences * $repeat_interval . ' Years';

            $year = '*';

            $s = $start_date;
            $e = $end_date;

            $_months = array();
            $_days = array();
            while (strtotime($s) <= strtotime($e)) {
                $_months[] = date('m', strtotime($s));
                $_days[] = date('d', strtotime($s));

                $s = date('Y-m-d', strtotime('+1 Day', strtotime($s)));
            }

            $_months = array_unique($_months);

            $month = ',' . implode(',', array($_months[0])) . ',';
            $day = ',' . implode(',', array_unique($_days)) . ',';

            $week = '*';
            $weekday = '*';
        } elseif ($repeat_type == "advanced") 
        {
            // Render class object
            $this->render = $this->getRender();

            // Get finish date
            $event_info = array('start' => $date['start'], 'end' => $date['end']);
            $dates = $this->render->generate_advanced_days($advanced, $event_info, $repeat_end_at_occurrences +1, date( 'Y-m-d', current_time( 'timestamp', 0 )), 'events');
            
            $period_date = $this->main->date_diff($start_date, end($dates)['end']['date']);

            $plus_date = '+' . $period_date->days . ' Days';
        }

        $in_days_arr = (isset($_mec['in_days']) and is_array($_mec['in_days']) and count($_mec['in_days'])) ? array_unique($_mec['in_days']) : array();
        $not_in_days_arr = (isset($_mec['not_in_days']) and is_array($_mec['not_in_days']) and count($_mec['not_in_days'])) ? array_unique($_mec['not_in_days']) : array();

        $in_days = '';
        if (count($in_days_arr)) {
           if(isset($in_days_arr[':i:'])) unset($in_days_arr[':i:']);
            usort($in_days_arr, function($a, $b)
            {
                return strtotime(explode(':', $a)[0]) - strtotime(explode(':', $b)[0]);
            });

            if(!isset($in_days_arr[':i:'])) $in_days_arr[':i:'] = ':val:';
            foreach ($in_days_arr as $key => $in_day_arr) {
                if (is_numeric($key)) {
                    $in_days .= $in_day_arr . ',';
                }
            }
        }

        $not_in_days = '';
        if (count($not_in_days_arr)) {
            foreach ($not_in_days_arr as $key => $not_in_day_arr) {
                if (is_numeric($key)) {
                    $not_in_days .= $not_in_day_arr . ',';
                }
            }
        }

        $in_days = trim($in_days, ', ');
        $not_in_days = trim($not_in_days, ', ');

        update_post_meta($post_id, 'mec_in_days', $in_days);
        update_post_meta($post_id, 'mec_not_in_days', $not_in_days);

        // Repeat End Date
        if ($repeat_end == 'never') {
            $repeat_end_date = '0000-00-00';
        } elseif ($repeat_end == 'date') {
            $repeat_end_date = $repeat_end_at_date;
        } elseif ($repeat_end == 'occurrences') {
            if ($plus_date) {
                $repeat_end_date = date('Y-m-d', strtotime($plus_date, strtotime($end_date)));
            } else {
                $repeat_end_date = '0000-00-00';
            }
        } else {
            $repeat_end_date = '0000-00-00';
        }

        // If event is not repeating then set the end date of event correctly
        if (!$repeat_status or $repeat_type == 'custom_days') {
            $repeat_end_date = $end_date;
        }

        // Add parameters to the $event
        $event['end'] = $repeat_end_date;
        $event['year'] = $year;
        $event['month'] = $month;
        $event['day'] = $day;
        $event['week'] = $week;
        $event['weekday'] = $weekday;
        $event['weekdays'] = $weekdays;
        $event['days'] = $in_days;
        $event['not_in_days'] = $not_in_days;

        // Update MEC Events Table
        $mec_event_id = $this->db->select("SELECT `id` FROM `#__mec_events` WHERE `post_id`='$post_id'", 'loadResult');

        if (!$mec_event_id) {
            $q1 = '';
            $q2 = '';

            foreach ($event as $key => $value) {
                $q1 .= "`$key`,";

                if (is_null($value)) {
                    $q2 .= 'NULL,';
                } else {
                    $q2 .= "'$value',";
                }
            }

            $this->db->q('INSERT INTO `#__mec_events` (' . trim($q1, ', ') . ') VALUES (' . trim($q2, ', ') . ')', 'INSERT');
        } else {
            $q = '';

            foreach ($event as $key => $value) {
                if (is_null($value)) {
                    $q .= "`$key`=NULL,";
                } else {
                    $q .= "`$key`='$value',";
                }
            }

            $this->db->q('UPDATE `#__mec_events` SET ' . trim($q, ', ') . " WHERE `id`='$mec_event_id'");
        }

        // Update Schedule
        $schedule = $this->getSchedule();
        $schedule->reschedule($post_id, $schedule->get_reschedule_maximum($repeat_type));

        // Hourly Schedule Options
        $raw_hourly_schedules = isset($_mec['hourly_schedules']) ? $_mec['hourly_schedules'] : array();
        unset($raw_hourly_schedules[':d:']);

        $hourly_schedules = array();
        foreach ($raw_hourly_schedules as $raw_hourly_schedule) {
            unset($raw_hourly_schedule['schedules'][':i:']);
            $hourly_schedules[] = $raw_hourly_schedule;
        }

        update_post_meta($post_id, 'mec_hourly_schedules', $hourly_schedules);

        // Booking and Ticket Options
        $booking = isset($_mec['booking']) ? $_mec['booking'] : array();
        update_post_meta($post_id, 'mec_booking', $booking);

        $tickets = isset($_mec['tickets']) ? $_mec['tickets'] : array();
        unset($tickets[':i:']);

        // Unset Ticket Dats
        if (count($tickets)) 
        {
            $new_tickets = array();
            foreach ($tickets as $key => $ticket) 
            {
                unset($ticket['dates'][':j:']);
                $ticket_start_time_ampm = ((intval($ticket['ticket_start_time_hour']) > 0 and intval($ticket['ticket_start_time_hour']) < 13) and isset($ticket['ticket_start_time_ampm'])) ? $ticket['ticket_start_time_ampm'] : '';
                $ticket_render_start_time = date('h:ia', strtotime(sprintf('%02d', $ticket['ticket_start_time_hour']) . ':' . sprintf('%02d', $ticket['ticket_start_time_minute']) . $ticket_start_time_ampm));
                $ticket_end_time_ampm = ((intval($ticket['ticket_end_time_hour']) > 0 and intval($ticket['ticket_end_time_hour']) < 13) and isset($ticket['ticket_end_time_ampm'])) ? $ticket['ticket_end_time_ampm'] : '';
                $ticket_render_end_time = date('h:ia', strtotime(sprintf('%02d', $ticket['ticket_end_time_hour']) . ':' . sprintf('%02d', $ticket['ticket_end_time_minute']) . $ticket_end_time_ampm));

                $ticket['ticket_start_time_hour'] = substr($ticket_render_start_time, 0, 2);
                $ticket['ticket_start_time_ampm'] = strtoupper(substr($ticket_render_start_time, 5, 6));
                $ticket['ticket_end_time_hour'] = substr($ticket_render_end_time, 0, 2);
                $ticket['ticket_end_time_ampm'] = strtoupper(substr($ticket_render_end_time, 5, 6));
                
                $new_tickets[$key] = $ticket;
            }

            $tickets = $new_tickets;
        }
        
        update_post_meta($post_id, 'mec_tickets', $tickets);

        // Fee options
        $fees_global_inheritance = isset($_mec['fees_global_inheritance']) ? $_mec['fees_global_inheritance'] : 1;
        update_post_meta($post_id, 'mec_fees_global_inheritance', $fees_global_inheritance);

        $fees = isset($_mec['fees']) ? $_mec['fees'] : array();
        update_post_meta($post_id, 'mec_fees', $fees);

        // Ticket Variations options
        $ticket_variations_global_inheritance = isset($_mec['ticket_variations_global_inheritance']) ? $_mec['ticket_variations_global_inheritance'] : 1;
        update_post_meta($post_id, 'mec_ticket_variations_global_inheritance', $ticket_variations_global_inheritance);

        $ticket_variations = isset($_mec['ticket_variations']) ? $_mec['ticket_variations'] : array();
        unset($ticket_variations[':i:']);

        update_post_meta($post_id, 'mec_ticket_variations', $ticket_variations);

        // Registration Fields options
        $reg_fields_global_inheritance = isset($_mec['reg_fields_global_inheritance']) ? $_mec['reg_fields_global_inheritance'] : 1;
        update_post_meta($post_id, 'mec_reg_fields_global_inheritance', $reg_fields_global_inheritance);

        $reg_fields = isset($_mec['reg_fields']) ? $_mec['reg_fields'] : array();
        if ($reg_fields_global_inheritance) {
            $reg_fields = array();
        }
        do_action('mec_save_reg_fields', $post_id, $reg_fields);
        update_post_meta($post_id, 'mec_reg_fields', $reg_fields);

        // Organizer Payment Options
        $op = isset($_mec['op']) ? $_mec['op'] : array();
        update_post_meta($post_id, 'mec_op', $op);
        update_user_meta(get_post_field('post_author', $post_id), 'mec_op', $op);

        if($booking_date_update)
        {
            $render_date = $past_start_date . ':' . $past_end_date;
            $new_date = $start_date . ':' . $end_date;

            $books_query = new WP_Query(array(
                'post_type' => 'mec-books', 
                'nopaging' => true,
                'post_status' => array('publish','pending','draft','future','private'),
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'mec_event_id',
                        'value'   => $post_id.'',
                        'type'    => 'numeric',
                        'compare' => '='
                    ),
                    array(
                        'key'     => 'mec_date',
                        'value'   => $render_date,
                        'compare' => '=',
                    )
                )
            ));

            if($books_query->have_posts())
            {
                $book = $this->getBook();

                while($books_query->have_posts())
                {
                    $books_query->the_post();
                    $booking_id = get_the_ID();

                    // Update Booking
                    update_post_meta($booking_id, 'mec_date', trim($new_date));
                    wp_update_post(array(
                        'ID' => $booking_id,
                        'post_date' => $start_date
                    ));

                    // Update Transaction
                    $transaction_id = get_post_meta($booking_id, 'mec_transaction_id', true);
                    $transaction = $book->get_transaction($transaction_id);

                    $transaction['date'] = trim($new_date);
                    $book->update_transaction($transaction_id, $transaction);
                }

                wp_reset_postdata();
            }
        }
    }

     /**
     * Publish a event
     * @author Webnus <info@webnus.biz>
     * @param string $new
     * @param string $old
     * @param object $post
     * @return void
     */
    public function event_published($new, $old, $post)
    {
        // Fires after publish a event to send notifications etc.
        do_action('mec_event_published', $new, $old, $post);
    }

    /**
     * Remove MEC event data after deleting a post permanently
     *
     * @author Webnus <info@webnus.biz>
     * @param int $post_id
     * @return boolean
     */
    public function delete_event($post_id)
    {
        $this->db->q("DELETE FROM `#__mec_events` WHERE `post_id`='$post_id'");
        $this->db->q("DELETE FROM `#__mec_dates` WHERE `post_id`='$post_id'");

        return true;
    }

    /**
     * Add filter options in manage events page
     *
     * @author Webnus <info@webnus.biz>
     * @param string $post_type
     * @return void
     */
    public function add_filters($post_type)
    {
        if ($post_type != $this->PT) {
            return;
        }

        $taxonomy = 'mec_label';
        if (wp_count_terms($taxonomy)) {
            wp_dropdown_categories(
                array(
                    'show_option_all' => sprintf(__('Show all %s', 'modern-events-calendar-lite'), $this->main->m('taxonomy_labels', __('labels', 'modern-events-calendar-lite'))),
                    'taxonomy' => $taxonomy,
                    'name' => $taxonomy,
                    'value_field' => 'slug',
                    'orderby' => 'name',
                    'order' => 'ASC',
                    'selected' => (isset($_GET[$taxonomy]) ? sanitize_text_field($_GET[$taxonomy]) : ''),
                    'show_count' => false,
                    'hide_empty' => false,
                )
            );
        }

        $taxonomy = 'mec_location';
        if (wp_count_terms($taxonomy)) {
            wp_dropdown_categories(
                array(
                    'show_option_all' => sprintf(__('Show all %s', 'modern-events-calendar-lite'), $this->main->m('taxonomy_locations', __('locations', 'modern-events-calendar-lite'))),
                    'taxonomy' => $taxonomy,
                    'name' => $taxonomy,
                    'value_field' => 'slug',
                    'orderby' => 'name',
                    'order' => 'ASC',
                    'selected' => (isset($_GET[$taxonomy]) ? sanitize_text_field($_GET[$taxonomy]) : ''),
                    'show_count' => false,
                    'hide_empty' => false,
                )
            );
        }

        $taxonomy = 'mec_organizer';
        if (wp_count_terms($taxonomy)) {
            wp_dropdown_categories(
                array(
                    'show_option_all' => sprintf(__('Show all %s', 'modern-events-calendar-lite'), $this->main->m('taxonomy_organizers', __('organizers', 'modern-events-calendar-lite'))),
                    'taxonomy' => $taxonomy,
                    'name' => $taxonomy,
                    'value_field' => 'slug',
                    'orderby' => 'name',
                    'order' => 'ASC',
                    'selected' => (isset($_GET[$taxonomy]) ? sanitize_text_field($_GET[$taxonomy]) : ''),
                    'show_count' => false,
                    'hide_empty' => false,
                )
            );
        }

        $taxonomy = 'mec_category';
        if (wp_count_terms($taxonomy)) {
            wp_dropdown_categories(
                array(
                    'show_option_all' => sprintf(__('Show all %s', 'modern-events-calendar-lite'), $this->main->m('taxonomy_categorys', __('Categories', 'modern-events-calendar-lite'))),
                    'taxonomy' => $taxonomy,
                    'name' => $taxonomy,
                    'value_field' => 'slug',
                    'orderby' => 'name',
                    'order' => 'ASC',
                    'selected' => (isset($_GET[$taxonomy]) ? sanitize_text_field($_GET[$taxonomy]) : ''),
                    'show_count' => false,
                    'hide_empty' => false,
                )
            );
        }
    }

    /**
     * Filters columns of events feature
     *
     * @author Webnus <info@webnus.biz>
     * @param array $columns
     * @return array
     */
    public function filter_columns($columns)
    {
        unset($columns['comments']);
        unset($columns['date']);
        unset($columns['author']);
        unset($columns['tags']);

        $columns['title'] = __('Title', 'modern-events-calendar-lite');
        $columns['category'] = __('Category', 'modern-events-calendar-lite');
        $columns['location'] = $this->main->m('taxonomy_location', __('Location', 'modern-events-calendar-lite'));
        $columns['organizer'] = $this->main->m('taxonomy_organizer', __('Organizer', 'modern-events-calendar-lite'));
        $columns['start_date'] = __('Start Date', 'modern-events-calendar-lite');
        $columns['end_date'] = __('End Date', 'modern-events-calendar-lite');

        $columns['repeat'] = __('Repeat', 'modern-events-calendar-lite');
        $columns['author'] = __('Author', 'modern-events-calendar-lite');

        return $columns;
    }

    /**
     * Filters sortable columns of events feature
     *
     * @author Webnus <info@webnus.biz>
     * @param array $columns
     * @return array
     */
    public function filter_sortable_columns($columns)
    {
        $columns['start_date'] = 'start_date';
        $columns['end_date'] = 'end_date';

        return $columns;
    }

    /**
     * Filters columns content of events feature
     *
     * @author Webnus <info@webnus.biz>
     * @param string $column_name
     * @param int $post_id
     * @return string
     */
    public function filter_columns_content($column_name, $post_id)
    {
        if ($column_name == 'location') {
            $location = get_term(get_post_meta($post_id, 'mec_location_id', true));
            echo(isset($location->name) ? $location->name : '----');
        } elseif ($column_name == 'organizer') {
            $organizer = get_term(get_post_meta($post_id, 'mec_organizer_id', true));
            echo(isset($organizer->name) ? $organizer->name : '----');
        } elseif ($column_name == 'start_date') {
            echo date('Y-n-d', strtotime(get_post_meta($post_id, 'mec_start_date', true)));
        } elseif ($column_name == 'end_date') {
            echo date('Y-n-d', strtotime(get_post_meta($post_id, 'mec_end_date', true)));
        } elseif ($column_name == 'repeat') {
            $repeat_type = get_post_meta($post_id, 'mec_repeat_type', true);
            echo ucwords(str_replace('_', ' ', $repeat_type));
        } elseif ($column_name == 'category') {
            $post_categories = get_the_terms($post_id, 'mec_category');
            if($post_categories) foreach($post_categories as $post_category) $categories[] = $post_category->name;
            if (!empty($categories))
            {
                $category_name = implode(",", $categories);
                echo $category_name;
            }

        }
    }

    /**
     * Sort events if sorted by custom columns
     *
     * @author Webnus <info@webnus.biz>
     * @param object $query
     * @return void
     */
    public function sort($query)
    {
        if (!is_admin() or $query->get('post_type') != $this->PT) {
            return;
        }

        $orderby = $query->get('orderby');

        if ($orderby == 'start_date') {
            $query->set(
                'meta_query',
                array(
                    'mec_start_date' => array(
                        'key' => 'mec_start_date',
                    ),
                    'mec_start_day_seconds' => array(
                        'key' => 'mec_start_day_seconds',
                    ),
                )
            );

            $query->set(
                'orderby',
                array(
                    'mec_start_date' => $query->get('order'),
                    'mec_start_day_seconds' => $query->get('order'),
                )
            );
        } elseif ($orderby == 'end_date') {
            $query->set(
                'meta_query',
                array(
                    'mec_end_date' => array(
                        'key' => 'mec_end_date',
                    ),
                    'mec_end_day_seconds' => array(
                        'key' => 'mec_end_day_seconds',
                    ),
                )
            );

            $query->set(
                'orderby',
                array(
                    'mec_end_date' => $query->get('order'),
                    'mec_end_day_seconds' => $query->get('order'),
                )
            );
        }
    }

    public function add_bulk_actions()
    {
        global $post_type;

        if ($post_type == $this->PT) {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function () {
                    jQuery('<option>').val('ical-export').text('<?php echo __('iCal Export', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action']");
                    jQuery('<option>').val('ical-export').text('<?php echo __('iCal Export', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action2']");

                    jQuery('<option>').val('csv-export').text('<?php echo __('CSV Export', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action']");
                    jQuery('<option>').val('csv-export').text('<?php echo __('CSV Export', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action2']");

                    jQuery('<option>').val('ms-excel-export').text('<?php echo __('MS Excel Export', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action']");
                    jQuery('<option>').val('ms-excel-export').text('<?php echo __('MS Excel Export', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action2']");

                    jQuery('<option>').val('xml-export').text('<?php echo __('XML Export', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action']");
                    jQuery('<option>').val('xml-export').text('<?php echo __('XML Export', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action2']");

                    jQuery('<option>').val('json-export').text('<?php echo __('JSON Export', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action']");
                    jQuery('<option>').val('json-export').text('<?php echo __('JSON Export', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action2']");

                    jQuery('<option>').val('duplicate').text('<?php echo __('Duplicate', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action']");
                    jQuery('<option>').val('duplicate').text('<?php echo __('Duplicate', 'modern-events-calendar-lite'); ?>').appendTo("select[name='action2']");
                });
            </script>
            <?php
        }
    }

    public function do_bulk_actions()
    {
        $wp_list_table = _get_list_table('WP_Posts_List_Table');

        $action = $wp_list_table->current_action();
        if (!$action) {
            return false;
        }

        $post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : 'post';
        if ($post_type != $this->PT) {
            return false;
        }

        check_admin_referer('bulk-posts');

        // MEC Render Library
        $render = $this->getRender();

        switch ($action) {
            case 'ical-export':
                $post_ids = $_GET['post'];
                $events = '';

                foreach ($post_ids as $post_id) {
                    $events .= $this->main->ical_single((int)$post_id);
                }

                $ical_calendar = $this->main->ical_calendar($events);

                header('Content-type: application/force-download; charset=utf-8');
                header('Content-Disposition: attachment; filename="mec-events-' . date('YmdTHi') . '.ics"');

                echo $ical_calendar;
                exit;

                break;
            case 'csv-export':
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename=bookings-' . md5(time() . mt_rand(100, 999)) . '.csv');

                $post_ids = $_GET['post'];
                $columns = array(__('ID', 'modern-events-calendar-lite'), __('Title', 'modern-events-calendar-lite'), __('Start Date', 'modern-events-calendar-lite'), __('Start Time', 'modern-events-calendar-lite'), __('End Date', 'modern-events-calendar-lite'), __('End Time', 'modern-events-calendar-lite'), __('Link', 'modern-events-calendar-lite'), $this->main->m('taxonomy_location', __('Location', 'modern-events-calendar-lite')), $this->main->m('taxonomy_organizer', __('Organizer', 'modern-events-calendar-lite')), sprintf(__('%s Tel', 'modern-events-calendar-lite'), $this->main->m('taxonomy_organizer', __('Organizer', 'modern-events-calendar-lite'))), sprintf(__('%s Email', 'modern-events-calendar-lite'), $this->main->m('taxonomy_organizer', __('Organizer', 'modern-events-calendar-lite'))), $this->main->m('event_cost', __('Event Cost', 'modern-events-calendar-lite')));

                $output = fopen('php://output', 'w');
                fputcsv($output, $columns);

                foreach ($post_ids as $post_id) {
                    $post_id = (int)$post_id;

                    $data = $render->data($post_id);

                    $dates = $render->dates($post_id, $data);
                    $date = $dates[0];

                    $location = isset($data->locations[$data->meta['mec_location_id']]) ? $data->locations[$data->meta['mec_location_id']] : array();
                    $organizer = isset($data->organizers[$data->meta['mec_organizer_id']]) ? $data->organizers[$data->meta['mec_organizer_id']] : array();

                    $event = array(
                        $post_id,
                        $data->title,
                        $date['start']['date'],
                        $data->time['start'],
                        $date['end']['date'],
                        $data->time['end'],
                        $data->permalink,
                        (isset($location['address']) ? $location['address'] : (isset($location['name']) ? $location['name'] : '')),
                        (isset($organizer['name']) ? $organizer['name'] : ''),
                        (isset($organizer['tel']) ? $organizer['tel'] : ''),
                        (isset($organizer['email']) ? $organizer['email'] : ''),
                        (is_numeric($data->meta['mec_cost']) ? $this->main->render_price($data->meta['mec_cost']) : $data->meta['mec_cost']),
                    );

                    fputcsv($output, $event);
                }

                exit;

                break;
            case 'ms-excel-export':
                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header('Content-Disposition: attachment; filename=bookings-' . md5(time() . mt_rand(100, 999)) . '.csv');

                $post_ids = $_GET['post'];
                $columns = array(__('ID', 'modern-events-calendar-lite'), __('Title', 'modern-events-calendar-lite'), __('Start Date', 'modern-events-calendar-lite'), __('Start Time', 'modern-events-calendar-lite'), __('End Date', 'modern-events-calendar-lite'), __('End Time', 'modern-events-calendar-lite'), __('Link', 'modern-events-calendar-lite'), $this->main->m('taxonomy_location', __('Location', 'modern-events-calendar-lite')), $this->main->m('taxonomy_organizer', __('Organizer', 'modern-events-calendar-lite')), sprintf(__('%s Tel', 'modern-events-calendar-lite'), $this->main->m('taxonomy_organizer', __('Organizer', 'modern-events-calendar-lite'))), sprintf(__('%s Email', 'modern-events-calendar-lite'), $this->main->m('taxonomy_organizer', __('Organizer', 'modern-events-calendar-lite'))), $this->main->m('event_cost', __('Event Cost', 'modern-events-calendar-lite')));

                $output = fopen('php://output', 'w');
                fwrite($output, "sep=\t" . PHP_EOL);
                fputcsv($output, $columns, "\t");

                foreach($post_ids as $post_id)
                {
                    $post_id = (int)$post_id;

                    $data = $render->data($post_id);

                    $dates = $render->dates($post_id, $data);
                    $date = $dates[0];

                    $location = isset($data->locations[$data->meta['mec_location_id']]) ? $data->locations[$data->meta['mec_location_id']] : array();
                    $organizer = isset($data->organizers[$data->meta['mec_organizer_id']]) ? $data->organizers[$data->meta['mec_organizer_id']] : array();

                    $event = array(
                        $post_id,
                        $data->title,
                        $date['start']['date'],
                        $data->time['start'],
                        $date['end']['date'],
                        $data->time['end'],
                        $data->permalink,
                        (isset($location['address']) ? $location['address'] : (isset($location['name']) ? $location['name'] : '')),
                        (isset($organizer['name']) ? $organizer['name'] : ''),
                        (isset($organizer['tel']) ? $organizer['tel'] : ''),
                        (isset($organizer['email']) ? $organizer['email'] : ''),
                        (is_numeric($data->meta['mec_cost']) ? $this->main->render_price($data->meta['mec_cost']) : $data->meta['mec_cost']),
                    );

                    fputcsv($output, $event, "\t");
                }

                exit;

                break;
            case 'xml-export':
                $post_ids = $_GET['post'];
                $events = array();

                foreach ($post_ids as $post_id) {
                    $events[] = $this->main->export_single((int)$post_id);
                }

                $xml_feed = $this->main->xml_convert(array('events' => $events));

                header('Content-type: application/force-download; charset=utf-8');
                header('Content-Disposition: attachment; filename="mec-events-' . date('YmdTHi') . '.xml"');

                echo $xml_feed;
                exit;

                break;
            case 'json-export':
                $post_ids = $_GET['post'];
                $events = array();

                foreach ($post_ids as $post_id) {
                    $events[] = $this->main->export_single((int)$post_id);
                }

                header('Content-type: application/force-download; charset=utf-8');
                header('Content-Disposition: attachment; filename="mec-events-' . date('YmdTHi') . '.json"');

                echo json_encode($events);
                exit;

                break;
            case 'duplicate':
                $post_ids = $_GET['post'];

                foreach ($post_ids as $post_id) {
                    $this->main->duplicate((int)$post_id);
                }

                break;
            default:
                return;
        }

        wp_redirect('edit.php?post_type=' . $this->main->get_main_post_type());
        exit;
    }

    public function action_links($actions, $post)
    {
        if($post->post_type != $this->PT) return $actions;

        $actions['mec-duplicate'] = '<a href="'.$this->main->add_qs_vars(array('mec-action'=>'duplicate-event', 'id'=>$post->ID)).'">'.__('Duplicate', 'modern-events-calendar-lite').'</a>';
        return $actions;
    }

    public function duplicate_event()
    {
        // It's not a duplicate request
        if(!isset($_GET['mec-action']) or (isset($_GET['mec-action']) and $_GET['mec-action'] != 'duplicate-event')) return false;

        // Event ID to duplicate
        $id = isset($_GET['id']) ? $_GET['id'] : 0;
        if(!$id) return false;

        // Duplicate
        $new_post_id = $this->main->duplicate((int) $id);

        wp_redirect('post.php?post=' . $new_post_id . '&action=edit');
        exit;
    }

     /**
     * Do bulk edit Action
     *
     * @author Webnus <info@webnus.biz>
     * @return void
     */
    public function bulk_edit()
    {
        $post_ids = (isset($_GET['post']) and is_array($_GET['post']) and count($_GET['post'])) ? array_map('sanitize_text_field', wp_unslash($_GET['post'])) : array();
        if(!is_array($post_ids) or !count($post_ids)) return;

        $mec_locations = (isset($_GET['tax_input']['mec_location']) and trim($_GET['tax_input']['mec_location'])) ? array_filter(explode(',', sanitize_text_field($_GET['tax_input']['mec_location']))) : NULL;
        $mec_organizers = (isset($_GET['tax_input']['mec_organizer']) and trim($_GET['tax_input']['mec_organizer'])) ? array_filter(explode(',', sanitize_text_field($_GET['tax_input']['mec_organizer']))) : NULL;

        foreach($post_ids as $post_id)
        {
            // MEC Location Bulk Edit
            if(!is_null($mec_locations))
            {
                $term_location = current($mec_locations);
                if(!term_exists($term_location, 'mec_location')) wp_insert_term($term_location, 'mec_location', array());
                
                $location_id =  get_term_by('name', $term_location, 'mec_location')->term_id;
                update_post_meta($post_id, 'mec_location_id', $location_id);

                if(count($mec_locations) > 1)
                {
                    // Additional locations
                    $additional_location_ids = array();

                    for($i = 1; $i < count($mec_locations); $i++)
                    {
                        if(!term_exists($mec_locations[$i], 'mec_location')) wp_insert_term($mec_locations[$i], 'mec_location', array());

                        $additional_location_id =  get_term_by('name', $mec_locations[$i], 'mec_location')->term_id;
                        wp_set_object_terms($post_id, (int)$additional_location_id, 'mec_location', true);
                        $additional_location_ids[] = (int)$additional_location_id;
                    }

                    update_post_meta($post_id, 'mec_additional_location_ids', $additional_location_ids);
                }
            }

            // MEC Organizer Bulk Edit
            if(!is_null($mec_organizers))
            {
                $term_organizer = current($mec_organizers);
                if(!term_exists($term_organizer, 'mec_organizer')) wp_insert_term($term_organizer, 'mec_organizer', array());
                
                $organizer_id =  get_term_by('name', current($mec_organizers), 'mec_organizer')->term_id;
                update_post_meta($post_id, 'mec_organizer_id', $organizer_id);

                if(count($mec_organizers) > 1)
                {
                    // Additional organizers
                    $additional_organizer_ids = array();

                    for($i = 1; $i < count($mec_organizers); $i++)
                    {
                        if(!term_exists($mec_organizers[$i], 'mec_organizer')) wp_insert_term($mec_organizers[$i], 'mec_organizer', array());

                        $additional_organizer_id =  get_term_by('name', $mec_organizers[$i], 'mec_organizer')->term_id;
                        wp_set_object_terms($post_id, (int)$additional_organizer_id, 'mec_organizer', true);
                        $additional_organizer_ids[] = (int)$additional_organizer_id;
                    }

                    update_post_meta($post_id, 'mec_additional_organizer_ids', $additional_organizer_ids);
                }
            }
        }
    }
}