<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC Webhooks class.
 * @author Webnus <info@webnus.net>
 */
class MEC_feature_webhooks extends MEC_base
{
    public $factory;
    public $main;
    public $PT;
    public $settings;

    /**
     * Constructor method
     * @author Webnus <info@webnus.net>
     */
    public function __construct()
    {
        // Import MEC Factory
        $this->factory = $this->getFactory();

        // Import MEC Main
        $this->main = $this->getMain();

        // MEC Webhook Post Type Name
        $this->PT = $this->main->get_webhook_post_type();

        // MEC Settings
        $this->settings = $this->main->get_settings();
    }

    /**
     * Initialize Webhooks feature
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        // PRO Version is required
        if(!$this->getPRO()) return false;

        // Show Webhook feature only if module is enabled
        if(!isset($this->settings['webhooks_status']) or (isset($this->settings['webhooks_status']) and !$this->settings['webhooks_status'])) return false;

        $this->factory->action('init', [$this, 'register_post_type']);
        $this->factory->action('save_post', [$this, 'save_webhook'], 10);
        $this->factory->action('add_meta_boxes', [$this, 'register_meta_boxes'], 1);

        // Webhooks
        foreach([
            'mec_booking_confirmed',
            'mec_booking_verified',
            'mec_booking_added',
            'mec_booking_rejected',
            'mec_booking_canceled',
            'mec_booking_refunded',
        ] as $hook) $this->factory->action($hook, function($booking_id) use ($hook) {
            return $this->webhook_booking($booking_id, $hook);
        }, 999);

        return true;
    }

    /**
     * Registers email post type
     * @author Webnus <info@webnus.net>
     */
    public function register_post_type()
    {
        $singular_label = esc_html__('Webhook', 'modern-events-calendar-lite');
        $plural_label = esc_html__('Webhooks', 'modern-events-calendar-lite');

        $capability = 'manage_options';
        register_post_type($this->PT,
            [
                'labels'=>[
                    'name'=>$plural_label,
                    'singular_name'=>$singular_label,
                    'add_new'=>sprintf(esc_html__('Add %s', 'modern-events-calendar-lite'), $singular_label),
                    'add_new_item'=>sprintf(esc_html__('Add %s', 'modern-events-calendar-lite'), $singular_label),
                    'not_found'=>sprintf(esc_html__('No %s found!', 'modern-events-calendar-lite'), strtolower($plural_label)),
                    'all_items'=>$plural_label,
                    'edit_item'=>sprintf(esc_html__('Edit %s', 'modern-events-calendar-lite'), $plural_label),
                    'not_found_in_trash'=>sprintf(esc_html__('No %s found in Trash!', 'modern-events-calendar-lite'), strtolower($singular_label))
                ],
                'public'=>false,
                'show_ui'=>(current_user_can($capability) ? true : false),
                'show_in_menu'=>false,
                'show_in_admin_bar'=>false,
                'show_in_nav_menus'=>false,
                'has_archive'=>false,
                'exclude_from_search'=>true,
                'publicly_queryable'=>false,
                'supports'=>['title'],
                'capabilities'=> [
                    'read'=>$capability,
                    'read_post'=>$capability,
                    'read_private_posts'=>$capability,
                    'create_post'=>$capability,
                    'create_posts'=>$capability,
                    'edit_post'=>$capability,
                    'edit_posts'=>$capability,
                    'edit_private_posts'=>$capability,
                    'edit_published_posts'=>$capability,
                    'edit_others_posts'=>$capability,
                    'publish_posts'=>$capability,
                    'delete_post'=>$capability,
                    'delete_posts'=>$capability,
                    'delete_private_posts'=>$capability,
                    'delete_published_posts'=>$capability,
                    'delete_others_posts'=>$capability,
                ],
            ]
        );
    }

    /**
     * Registers meta boxes
     * @author Webnus <info@webnus.net>
     */
    public function register_meta_boxes()
    {
        add_meta_box('mec_webhook_metabox_details', esc_html__('Details', 'modern-events-calendar-lite'), [$this, 'meta_box_details'], $this->PT, 'normal', 'high');
    }

    public function meta_box_details($post)
    {
        $path = MEC::import('app.features.webhooks.details', true, true);

        ob_start();
        include $path;
        echo MEC_kses::full(ob_get_clean());
    }

    /**
     * Save webhook data from backend
     * @author Webnus <info@webnus.net>
     * @param int $post_id
     * @return void
     */
    public function save_webhook($post_id)
    {
        // Check if our nonce is set.
        if(!isset($_POST['mec_webhook_nonce'])) return;

        // Verify that the nonce is valid.
        if(!wp_verify_nonce(sanitize_text_field($_POST['mec_webhook_nonce']), 'mec_webhook_data')) return;

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if(defined('DOING_AUTOSAVE') and DOING_AUTOSAVE) return;

        // MEC Data
        $mec = (isset($_POST['mec']) and is_array($_POST['mec'])) ? $this->main->sanitize_deep_array($_POST['mec']) : [];

        // All Options
        update_post_meta($post_id, 'mec', $mec);

        update_post_meta($post_id, 'mec_hook', isset($mec['hook']) ? sanitize_text_field($mec['hook']) : '');
        update_post_meta($post_id, 'mec_url', isset($mec['url']) ? sanitize_url($mec['url']) : '');
        update_post_meta($post_id, 'mec_method', isset($mec['method']) ? strtoupper(sanitize_text_field($mec['method'])) : 'POST');
    }

    public function webhook_booking($booking_id, $hook)
    {
        // Get Webhooks to Call
        $webhooks = $this->get($hook);

        // No Webhooks
        if(!is_array($webhooks) or (is_array($webhooks) and !count($webhooks))) return;

        // Booking Data
        $data = $this->booking_data($booking_id);

        // Call Webhooks
        foreach($webhooks as $webhook)
        {
            $method = strtoupper(get_post_meta($webhook->ID, 'mec_method', true));
            $url = get_post_meta($webhook->ID, 'mec_url', true);

            // Prepare Headers
            $headers = apply_filters('mec_webhooks_headers', [], $webhook, $url);

            if($method === 'GET')
            {
                wp_remote_get($url, apply_filters('mec_webhooks_args', [
                    'headers' => $headers,
                    'body' => $data
                ]));
            }
            else
            {
                wp_remote_post($url, apply_filters('mec_webhooks_args', [
                    'headers' => $headers,
                    'body' => $data
                ]));
            }
        }
    }

    public function get($hook)
    {
        return get_posts([
            'post_type' => $this->PT,
            'status' => 'publish',
            'numberposts' => -1,
            'meta_key' => 'mec_hook',
            'meta_value' => $hook,
        ]);
    }

    public function booking_data($booking_id)
    {
        // MEC User
        $u = $this->getUser();

        // MEC Booking
        $b = $this->getBook();

        $event_id = get_post_meta($booking_id, 'mec_event_id', true);
        $transaction_id = get_post_meta($booking_id, 'mec_transaction_id', true);
        $order_time = get_post_meta($booking_id, 'mec_booking_time', true);
        $tickets = get_post_meta($event_id, 'mec_tickets', true);
        $timestamps = explode(':', get_post_meta($booking_id, 'mec_date', true));

        $attendees = get_post_meta($booking_id, 'mec_attendees', true);

        $booker = $u->booking($booking_id);

        $confirmed = $this->main->get_confirmation_label(get_post_meta($booking_id, 'mec_confirmed', true));
        $verified = $this->main->get_verification_label(get_post_meta($booking_id, 'mec_verified', true));
        $transaction = $b->get_transaction($transaction_id);

        // Date & Time Format
        $datetime_format = get_option('date_format').' '.get_option('time_format');

        $other_dates_formatted = [];

        $other_dates = (isset($transaction['other_dates']) and is_array($transaction['other_dates'])) ? $transaction['other_dates'] : [];
        foreach($other_dates as $other_date)
        {
            $other_timestamps = explode(':', $other_date);
            $other_dates_formatted[] = [
                date($datetime_format, $other_timestamps[0]),
                date($datetime_format, $other_timestamps[1])
            ];
        }

        $reg_fields = $this->main->get_reg_fields($event_id);
        $bfixed_fields = $this->main->get_bfixed_fields($event_id);

        $bfixed_data = [];
        $bfixed_values = (isset($transaction['fields']) and is_array($transaction['fields'])) ? $transaction['fields'] : [];
        foreach($bfixed_fields as $bfixed_field_id => $bfixed_field)
        {
            if(!is_numeric($bfixed_field_id)) continue;

            $bfixed_label = isset($bfixed_field['label']) ? $bfixed_field['label'] : '';
            if(trim($bfixed_label) == '') continue;

            $bfixed_data[] = [
                'label' => $bfixed_label,
                'value' => isset($bfixed_values[$bfixed_field_id]) ? ((is_string($bfixed_values[$bfixed_field_id]) and trim($bfixed_values[$bfixed_field_id])) ? stripslashes($bfixed_values[$bfixed_field_id]) : (is_array($bfixed_values[$bfixed_field_id]) ? implode(' | ', $bfixed_values[$bfixed_field_id]) : '---')) : ''
            ];
        }

        $attendees_data = [];
        foreach($attendees as $key => $attendee)
        {
            if($key === 'attachments') continue;
            if(isset($attendee[0]['MEC_TYPE_OF_DATA'])) continue;

            $variations = [];
            if(isset($attendee['variations']) and is_array($attendee['variations']) and count($attendee['variations']))
            {
                $ticket_variations = $this->main->ticket_variations($event_id, $attendee['id']);
                foreach($attendee['variations'] as $a_variation_id => $a_variation_count)
                {
                    if((int) $a_variation_count > 0)
                    {
                        $variations[] = [
                            'id' => $a_variation_id,
                            'title' => isset($ticket_variations[$a_variation_id]) ? $ticket_variations[$a_variation_id]['title'] : 'N/A',
                            'count' => $a_variation_count,
                        ];
                    }
                }
            }

            $raw_price = $b->get_ticket_total_price($transaction, $attendee, $booking_id);
            $rendered_price = $this->main->render_price($raw_price, $event_id);

            $ticket_id = isset($attendee['id']) ? $attendee['id'] : get_post_meta($booking_id, 'mec_ticket_id', true);

            $reg_data = [];
            $reg_form = isset($attendee['reg']) ? $attendee['reg'] : [];
            foreach($reg_fields as $field_id=>$reg_field)
            {
                // Placeholder Keys
                if(!is_numeric($field_id)) continue;

                $type = isset($reg_field['type']) ? $reg_field['type'] : '';
                $label = isset($reg_field['label']) ? esc_html__($reg_field['label'], 'modern-events-calendar-lite') : '';

                if(trim($label) == '' or $type == 'name' or $type == 'mec_email') continue;

                $reg_data[] = [
                    'label' => $label,
                    'value' => isset($reg_form[$field_id]) ? ((is_string($reg_form[$field_id]) and trim($reg_form[$field_id])) ? stripslashes($reg_form[$field_id]) : (is_array($reg_form[$field_id]) ? implode(' | ', $reg_form[$field_id]) : '---')) : '',
                ];
            }

            $attendees_data[] = [
                'name' => isset($attendee['name']) ? $attendee['name'] : (isset($booker->first_name) ? trim($booker->first_name.' '.$booker->last_name) : ''),
                'email' => isset($attendee['email']) ? $attendee['email'] : @$booker->user_email,
                'ticket' => [
                    'id' => $ticket_id,
                    'name' => isset($tickets[$ticket_id], $tickets[$ticket_id]['name']) ? $tickets[$ticket_id]['name'] : esc_html__('Unknown', 'modern-events-calendar-lite')
                ],
                'price' => $raw_price,
                'price_rendered' => $rendered_price,
                'fields' => $reg_data,
                'variations' => $variations
            ];
        }

        return [
            'id' => $booking_id,
            'event' => [
                'id' => $event_id,
                'title' => get_the_title($event_id),
            ],
            'start' => date($datetime_format, $timestamps[0]),
            'end' => date($datetime_format, $timestamps[1]),
            'other_dates' => $other_dates_formatted,
            'order_time' => date($datetime_format, strtotime($order_time)),
            'attendees' => $attendees_data,
            'transaction_id' => $transaction_id,
            'gateway' => [
                'key' => get_post_meta($booking_id, 'mec_gateway', true),
                'label' => get_post_meta($booking_id, 'mec_gateway_label', true),
                'ref_id' => get_post_meta($booking_id, 'mec_gateway_ref_id', true)
            ],
            'confirmation' => [
                'key' => get_post_meta($booking_id, 'mec_confirmed', true),
                'label' => $confirmed
            ],
            'verification' => [
                'key' => get_post_meta($booking_id, 'mec_verified', true),
                'label' => $verified
            ],
            'fields' => $bfixed_data,
            'price' => [
                'price' => get_post_meta($booking_id, 'mec_price', true),
                'payable' => get_post_meta($booking_id, 'mec_payable', true),
                'coupon' => get_post_meta($booking_id, 'mec_coupon_code', true),
            ],
        ];
    }
}