<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC fes (Frontend Event Submission) class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_feature_fes extends MEC_base
{
    public $factory;
    public $main;
    public $db;
    public $settings;
    public $PT;
    public $render;
    
    /**
     * Constructor method
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
        
        // MEC Settings
        $this->settings = $this->main->get_settings();
        
        // Event Post Type
        $this->PT = $this->main->get_main_post_type();
    }
    
    /**
     * Initialize colors feature
     * @author Webnus <info@webnus.biz>
     */
    public function init()
    {
        // Frontend Event Submission Form
        $this->factory->shortcode('MEC_fes_form', array($this, 'vform'));
        
        // Event Single Page
        $this->factory->shortcode('MEC_fes_list', array($this, 'vlist'));
        
        // Process the event form
        $this->factory->action('wp_ajax_mec_fes_form', array($this, 'fes_form'));
        $this->factory->action('wp_ajax_nopriv_mec_fes_form', array($this, 'fes_form'));
        
        // Upload featured image
        $this->factory->action('wp_ajax_mec_fes_upload_featured_image', array($this, 'fes_upload'));
        $this->factory->action('wp_ajax_nopriv_mec_fes_upload_featured_image', array($this, 'fes_upload'));
        
        // Export the event
        $this->factory->action('wp_ajax_mec_fes_csv_export', array($this, 'mec_fes_csv_export'));
        $this->factory->action('wp_ajax_mec_fes_msexcel_export', array($this, 'mec_fes_msexcel_export'));

        // Remove the event
        $this->factory->action('wp_ajax_mec_fes_remove', array($this, 'fes_remove'));
    }
    
    /**
     * Generate frontend event submission form view
     * @author Webnus <info@webnus.biz>
     * @param array $atts
     * @return string
     */
    public function vform($atts = array())
    {
        // Force to array
        if(!is_array($atts)) $atts = array();
        
        if(isset($_GET['vlist']) and $_GET['vlist'] == 1)
        {
            return $this->vlist();
        }
        
        // Show login/register message if user is not logged in and guest submission is not enabled.
        if(!is_user_logged_in() and (!isset($this->settings['fes_guest_status']) or (isset($this->settings['fes_guest_status']) and $this->settings['fes_guest_status'] == '0')))
        {
            // Show message
            $message = sprintf(__('Please %s/%s in order to submit new events.', 'modern-events-calendar-lite'), '<a href="'.wp_login_url($this->main->get_full_url()).'">'.__('Login', 'modern-events-calendar-lite').'</a>', '<a href="'.wp_registration_url().'">'.__('Register', 'modern-events-calendar-lite').'</a>');
            
            ob_start();
            include MEC::import('app.features.fes.message', true, true);
            return $output = ob_get_clean();
        }
        
        $post_id = isset($_GET['post_id']) ? sanitize_text_field($_GET['post_id']) : -1;
        
        // Selected post is not an event
        if($post_id > 0 and get_post_type($post_id) != $this->PT)
        {
            // Show message
            $message = __("Sorry! Selected post is not an event.", 'modern-events-calendar-lite');
            
            ob_start();
            include MEC::import('app.features.fes.message', true, true);
            return $output = ob_get_clean();
        }
        
        // Show a warning to current user if modification of post is not possible for him/her
        if($post_id != -1 and !current_user_can('edit_post', $post_id))
        {
            // Show message
            $message = __("Sorry! You don't have access to modify this event.", 'modern-events-calendar-lite');
            
            ob_start();
            include MEC::import('app.features.fes.message', true, true);
            return $output = ob_get_clean();
        }
        
        $post = get_post($post_id);
        
        if($post_id == -1)
        {
            $post = new stdClass();
            $post->ID = -1;
        }
        
        $path = MEC::import('app.features.fes.form', true, true);
        
        ob_start();
        include $path;
        return $output = ob_get_clean();
    }
    
    /**
     * Generate frontend event submission list view
     * @author Webnus <info@webnus.biz>
     * @param array $atts
     * @return string
     */
    public function vlist($atts = array())
    {
        // Force to array
        if(!is_array($atts)) $atts = array();
        
        $post_id = isset($_GET['post_id']) ? sanitize_text_field($_GET['post_id']) : NULL;
        
        // Show a warning to current user if modification of post is not possible for him/her
        if($post_id > 0 and !current_user_can('edit_post', $post_id))
        {
            // Show message
            $message = __("Sorry! You don't have access to modify this event.", 'modern-events-calendar-lite');
            
            ob_start();
            include MEC::import('app.features.fes.message', true, true);
            return $output = ob_get_clean();
        }
        elseif($post_id == -1 or ($post_id > 0 and current_user_can('edit_post', $post_id)))
        {
            return $this->vform();
        }
        
        // Show login/register message if user is not logged in
        if(!is_user_logged_in())
        {
            // Show message
            $message = sprintf(__('Please %s/%s in order to manage events.', 'modern-events-calendar-lite'), '<a href="'.wp_login_url($this->main->get_full_url()).'">'.__('Login', 'modern-events-calendar-lite').'</a>', '<a href="'.wp_registration_url().'">'.__('Register', 'modern-events-calendar-lite').'</a>');
            
            ob_start();
            include MEC::import('app.features.fes.message', true, true);
            return $output = ob_get_clean();
        }
        
        $path = MEC::import('app.features.fes.list', true, true);
        
        ob_start();
        include $path;
        return $output = ob_get_clean();
    }
    
    public function fes_remove()
    {
        // Check if our nonce is set.
        if(!isset($_POST['_wpnonce'])) $this->main->response(array('success'=>0, 'code'=>'NONCE_MISSING'));

        // Verify that the nonce is valid.
        if(!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'mec_fes_remove')) $this->main->response(array('success'=>0, 'code'=>'NONCE_IS_INVALID'));
        
        $post_id = isset($_POST['post_id']) ? sanitize_text_field($_POST['post_id']) : 0;
        
        // Verify current user can remove the event
        if(!current_user_can('delete_post', $post_id)) $this->main->response(array('success'=>0, 'code'=>'USER_CANNOT_REMOVE_EVENT'));
        
        // Trash the event
        wp_delete_post($post_id);
        
        $this->main->response(array('success'=>1, 'message'=>__('The event removed!', 'modern-events-calendar-lite')));
    }
    
    public function mec_fes_csv_export()
    {
        if((!isset($_POST['mec_event_id'])) or (!isset($_POST['booking_ids'])) or (!isset($_POST['fes_nonce'])) or (!wp_verify_nonce($_POST['fes_nonce'], 'mec_fes_nonce'))) die(json_encode(array('ex' => "error")));

        $event_id = intval($_POST['mec_event_id']);
        $booking_ids = sanitize_text_field($_POST['booking_ids']);
        
        ob_start();
        header('Content-Type: text/csv; charset=utf-8');

        $post_ids = trim($booking_ids) ? explode(',', $booking_ids) : array();
        
        if(!count($post_ids))
        {
            $books = $this->db->select("SELECT `post_id` FROM `#__postmeta` WHERE `meta_key`='mec_event_id' AND `meta_value`={$event_id}", 'loadAssocList');
            foreach ($books as $book) if(isset($book['post_id'])) $post_ids[] = $book['post_id'];
        }

        $event_ids = array();
        foreach($post_ids as $post_id) $event_ids[] = get_post_meta($post_id, 'mec_event_id', true);
        $event_ids = array_unique($event_ids);

        $main_event_id = NULL;
        if(count($event_ids) == 1) $main_event_id = $event_ids[0];

        $columns = array(__('ID', 'modern-events-calendar-lite'), __('Event', 'modern-events-calendar-lite'), __('Date', 'modern-events-calendar-lite'), $this->main->m('ticket', __('Ticket', 'modern-events-calendar-lite')), __('Transaction ID', 'modern-events-calendar-lite'), __('Total Price', 'modern-events-calendar-lite'), __('Name', 'modern-events-calendar-lite'), __('Email', 'modern-events-calendar-lite'), __('Confirmation', 'modern-events-calendar-lite'), __('Verification', 'modern-events-calendar-lite'));
        $columns = apply_filters('mec_csv_export_columns', $columns);
        $reg_fields = $this->main->get_reg_fields($main_event_id);
        foreach($reg_fields as $reg_field_key=>$reg_field)
        {
            // Placeholder Keys
            if(!is_numeric($reg_field_key)) continue;

            $type = isset($reg_field['type']) ? $reg_field['type'] : '';
            $label = isset($reg_field['label']) ? __($reg_field['label'], 'modern-events-calendar-lite') : '';

            if(trim($label) == '') continue;
            if($type == 'agreement') $label = sprintf($label, get_the_title($reg_field['page']));

            $columns[] = $label;
        }
        $columns[] = 'Attachments';
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, $columns);
        
        foreach($post_ids as $post_id)
        {
            $post_id = (int) $post_id;

            $event_id = get_post_meta($post_id, 'mec_event_id', true);
            $booker_id = get_post_field('post_author', $post_id);
            $transaction_id = get_post_meta($post_id, 'mec_transaction_id', true);

            $tickets = get_post_meta($event_id, 'mec_tickets', true);

            $attendees = get_post_meta($post_id, 'mec_attendees', true);
            if(!is_array($attendees) or (is_array($attendees) and !count($attendees))) $attendees = array(get_post_meta($post_id, 'mec_attendee', true));

            $price = get_post_meta($post_id, 'mec_price', true);
            $booker = get_userdata($booker_id);
            
            $confirmed = $this->main->get_confirmation_label(get_post_meta($post_id, 'mec_confirmed', true));
            $verified = $this->main->get_verification_label(get_post_meta($post_id, 'mec_verified', true));
            
            $attachments = '';
            if( isset( $attendees['attachments'] ) ) 
            {
                foreach ($attendees['attachments'] as $attachment) {
                    $attachments .= @$attachment['url'] . "\n";
                }
            }

            foreach($attendees as $key => $attendee)
            {
                if ($key === 'attachments') {
                    continue;
                }
                if (isset($attendee[0]['MEC_TYPE_OF_DATA'])) {
                    continue;
                }
                
                $ticket_id = isset($attendee['id']) ? $attendee['id'] : get_post_meta($post_id, 'mec_ticket_id', true);
                $booking = array($post_id, get_the_title($event_id), get_the_date('', $post_id), (isset($tickets[$ticket_id]['name']) ? $tickets[$ticket_id]['name'] : __('Unknown', 'modern-events-calendar-lite')), $transaction_id, $this->main->render_price(($price ? $price : 0)), (isset($attendee['name']) ? $attendee['name'] : (isset($booker->first_name) ? trim($booker->first_name.' '.$booker->last_name) : '')), (isset($attendee['email']) ? $attendee['email'] : @$booker->user_email), $confirmed, $verified);
                $booking = apply_filters('mec_csv_export_booking', $booking, $post_id, $event_id);

                $reg_form = isset($attendee['reg']) ? $attendee['reg'] : array();
                foreach($reg_fields as $field_id=>$reg_field)
                {
                    // Placeholder Keys
                    if(!is_numeric($field_id)) continue;

                    $label = isset($reg_field['label']) ? __($reg_field['label'], 'modern-events-calendar-lite') : '';
                    if(trim($label) == '') continue;

                    $booking[] = isset($reg_form[$field_id]) ? ((is_string($reg_form[$field_id]) and trim($reg_form[$field_id])) ? $reg_form[$field_id] : (is_array($reg_form[$field_id]) ? implode(' | ', $reg_form[$field_id]) : '---')) : '';
                }
                    if ($attachments) {
                    $booking[]  = $attachments;
                    $attachments = '';
                }
                fputcsv($output, $booking);
            }
        }
        
        die(json_encode(array('name' => md5(time().mt_rand(100, 999)), 'ex' => "data:text/csv; charset=utf-8;base64,".base64_encode(ob_get_clean()))));
    }

    public function mec_fes_msexcel_export()
    {   
        if((!isset($_POST['mec_event_id'])) or (!isset($_POST['booking_ids'])) or (!isset($_POST['fes_nonce'])) or (!wp_verify_nonce($_POST['fes_nonce'], 'mec_fes_nonce'))) die(json_encode(array('ex' => "error")));

        $event_id = intval($_POST['mec_event_id']);
        $booking_ids = sanitize_text_field($_POST['booking_ids']);

        ob_start();
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');

        $post_ids = trim($booking_ids) ? explode(',', $booking_ids) : array();
        
        if(!count($post_ids))
        {
            $books = $this->db->select("SELECT `post_id` FROM `#__postmeta` WHERE `meta_key`='mec_event_id' AND `meta_value`={$event_id}", 'loadAssocList');
            foreach ($books as $book) if(isset($book['post_id'])) $post_ids[] = $book['post_id'];
        }

        $event_ids = array();
        foreach($post_ids as $post_id) $event_ids[] = get_post_meta($post_id, 'mec_event_id', true);
        $event_ids = array_unique($event_ids);

        $main_event_id = NULL;
        if(count($event_ids) == 1) $main_event_id = $event_ids[0];

        $columns = array(__('ID', 'modern-events-calendar-lite'), __('Event', 'modern-events-calendar-lite'), __('Date', 'modern-events-calendar-lite'), $this->main->m('ticket', __('Ticket', 'modern-events-calendar-lite')), __('Transaction ID', 'modern-events-calendar-lite'), __('Total Price', 'modern-events-calendar-lite'), __('Name', 'modern-events-calendar-lite'), __('Email', 'modern-events-calendar-lite'), __('Confirmation', 'modern-events-calendar-lite'), __('Verification', 'modern-events-calendar-lite'));
        $columns = apply_filters('mec_excel_export_columns', $columns);
        $reg_fields = $this->main->get_reg_fields($main_event_id);
        foreach($reg_fields as $reg_field_key=>$reg_field)
        {
            // Placeholder Keys
            if(!is_numeric($reg_field_key)) continue;

            $type = isset($reg_field['type']) ? $reg_field['type'] : '';

            $label = isset($reg_field['label']) ? __($reg_field['label'], 'modern-events-calendar-lite') : '';
            if(trim($label) == '') continue;

            if($type == 'agreement') $label = sprintf($label, get_the_title($reg_field['page']));

            $columns[] = $label;
        }
        $columns[] = 'Attachments';
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, $columns, "\t");
        
        foreach($post_ids as $post_id)
        {
            $post_id = (int) $post_id;

            $event_id = get_post_meta($post_id, 'mec_event_id', true);
            $booker_id = get_post_field('post_author', $post_id);
            $transaction_id = get_post_meta($post_id, 'mec_transaction_id', true);

            $tickets = get_post_meta($event_id, 'mec_tickets', true);

            $attendees = get_post_meta($post_id, 'mec_attendees', true);
            if(!is_array($attendees) or (is_array($attendees) and !count($attendees))) $attendees = array(get_post_meta($post_id, 'mec_attendee', true));

            $price = get_post_meta($post_id, 'mec_price', true);
            $booker = get_userdata($booker_id);
            
            $confirmed = $this->main->get_confirmation_label(get_post_meta($post_id, 'mec_confirmed', true));
            $verified = $this->main->get_verification_label(get_post_meta($post_id, 'mec_verified', true));

                $attachments = '';
            if( isset( $attendees['attachments'] ) ) 
            {
                foreach ($attendees['attachments'] as $attachment) {
                    $attachments .= @$attachment['url'] . " - ";
                }
            }

            foreach($attendees as $key => $attendee)
            {
                if ($key === 'attachments') {
                    continue;
                }
                if (isset($attendee[0]['MEC_TYPE_OF_DATA'])) {
                    continue;
                }
                $ticket_id = isset($attendee['id']) ? $attendee['id'] : get_post_meta($post_id, 'mec_ticket_id', true);
                $booking = array($post_id, get_the_title($event_id), get_the_date('', $post_id), (isset($tickets[$ticket_id]['name']) ? $tickets[$ticket_id]['name'] : __('Unknown', 'modern-events-calendar-lite')), $transaction_id, $this->main->render_price(($price ? $price : 0)), (isset($attendee['name']) ? $attendee['name'] : (isset($booker->first_name) ? trim($booker->first_name.' '.$booker->last_name) : '')), (isset($attendee['email']) ? $attendee['email'] : $booker->user_email), $confirmed, $verified,$attachments);
                $booking = apply_filters('mec_excel_export_booking', $booking, $post_id, $event_id);

                $reg_form = isset($attendee['reg']) ? $attendee['reg'] : array();
                foreach($reg_fields as $field_id=>$reg_field)
                {
                    // Placeholder Keys
                    if(!is_numeric($field_id)) continue;

                    $label = isset($reg_field['label']) ? __($reg_field['label'], 'modern-events-calendar-lite') : '';
                    if(trim($label) == '') continue;

                    $booking[] = isset($reg_form[$field_id]) ? ((is_string($reg_form[$field_id]) and trim($reg_form[$field_id])) ? $reg_form[$field_id] : (is_array($reg_form[$field_id]) ? implode(' | ', $reg_form[$field_id]) : '---')) : '';
                }
                if ($attachments) {
                    $booking[]  = $attachments;
                    $attachments = '';
                }
                fputcsv($output, $booking, "\t");
            }
        }

        die(json_encode(array('name' => md5(time().mt_rand(100, 999)), 'ex' => "data:application/vnd.ms-excel; charset=utf-8;base64,".base64_encode(ob_get_clean()))));
    }

    public function fes_upload()
    {
        // Check if our nonce is set.
        if(!isset($_POST['_wpnonce'])) $this->main->response(array('success'=>0, 'code'=>'NONCE_MISSING'));

        // Verify that the nonce is valid.
        if(!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'mec_fes_upload_featured_image')) $this->main->response(array('success'=>0, 'code'=>'NONCE_IS_INVALID'));
        
        // Include the function
        if(!function_exists('wp_handle_upload')) require_once ABSPATH.'wp-admin/includes/file.php';

        $uploaded_file = isset($_FILES['file']) ? $_FILES['file'] : NULL;
        
        // No file
        if(!$uploaded_file) $this->main->response(array('success'=>0, 'code'=>'NO_FILE'));
        
        $allowed = array('gif', 'jpeg', 'jpg', 'png');
        
        $ex = explode('.', $uploaded_file['name']);
        $extension = end($ex);
        
        // Invalid Extension
        if(!in_array($extension, $allowed)) $this->main->response(array('success'=>0, 'code'=>'INVALID_EXTENSION'));
        
        $movefile = wp_handle_upload($uploaded_file, array('test_form'=>false));
        
        $success = 0;
        $data = array();
        
        if($movefile and !isset($movefile['error']))
        {
            $success = 1;
            $message = __('The image is uploaded!', 'modern-events-calendar-lite');
            
            $data['url'] = $movefile['url'];
        }
        else
        {
            $message = $movefile['error'];
        }
        
        $this->main->response(array('success'=>$success, 'message'=>$message, 'data'=>$data));
    }
    
    public function fes_form()
    {
        // Check if our nonce is set.
        if(!isset($_POST['_wpnonce'])) $this->main->response(array('success'=>0, 'code'=>'NONCE_MISSING'));

        // Verify that the nonce is valid.
        if(!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'mec_fes_form')) $this->main->response(array('success'=>0, 'code'=>'NONCE_IS_INVALID'));
        
        $mec = isset($_POST['mec']) ? $_POST['mec'] : array();
        
        // Google recaptcha
        if($this->main->get_recaptcha_status('fes'))
        {
            $g_recaptcha_response = isset($_POST['g-recaptcha-response']) ? sanitize_text_field($_POST['g-recaptcha-response']) : NULL;
            if(!$this->main->get_recaptcha_response($g_recaptcha_response)) $this->main->response(array('success'=>0, 'message'=>__('Captcha is invalid! Please try again.', 'modern-events-calendar-lite'), 'code'=>'CAPTCHA_IS_INVALID'));
        }

        $post_id = isset($mec['post_id']) ? sanitize_text_field($mec['post_id']) : -1;

        $start_date = (isset($mec['date']['start']['date']) and trim($mec['date']['start']['date'])) ? $mec['date']['start']['date'] : date('Y-m-d');
        $end_date = (isset($mec['date']['end']['date']) and trim($mec['date']['end']['date'])) ? $mec['date']['end']['date'] : date('Y-m-d');

        $event = $this->db->select("SELECT * FROM `#__mec_events` WHERE `post_id` = {$post_id}", 'loadAssoc');
        if(!is_array($event)) $event = array();

        $booking_date_update = false;
        if(count($event))
        {
            $past_start_date = (isset($event['start']) and trim($event['start'])) ? $event['start'] : '';
            $past_end_date = (isset($event['end']) and trim($event['end'])) ? $event['end'] : '';

            if(trim($start_date) != trim($past_start_date) or trim($end_date) != trim($past_end_date)) $booking_date_update = true;
        }

        $post_title = isset($mec['title']) ? sanitize_text_field($mec['title']) : '';
        $post_content = isset($mec['content']) ? $mec['content'] : '';
        $post_tags = isset($mec['tags']) ? sanitize_text_field($mec['tags']) : '';
        $post_categories = isset($mec['categories']) ? $mec['categories'] : array();
        $post_speakers = isset($mec['speakers']) ? $mec['speakers'] : array();
        $post_labels = isset($mec['labels']) ? $mec['labels'] : array();
        $featured_image = isset($mec['featured_image']) ? sanitize_text_field($mec['featured_image']) : '';
        
        if(!trim($post_title)) $this->main->response(array('success'=>0, 'message'=>__('Please fill event title field!', 'modern-events-calendar-lite'), 'code'=>'TITLE_IS_EMPTY'));
        
        // Post Status
        $status = 'pending';
        if(current_user_can('publish_posts')) $status = 'publish';
        
        $method = 'updated';
        
        // Create new event
        if($post_id == -1)
        {
            $post = array('post_title'=>$post_title, 'post_content'=>$post_content, 'post_type'=>$this->PT, 'post_status'=>$status);
            $post_id = wp_insert_post($post);
            
            $method = 'added';
        }
        
        wp_update_post(array('ID'=>$post_id, 'post_title'=>$post_title, 'post_content'=>$post_content));
        
        // Categories
        $categories = array();
        foreach($post_categories as $post_category=>$value) $categories[] = (int) $post_category;
        
        wp_set_post_terms($post_id, $categories, 'mec_category');

        // Speakers
        if(isset($this->settings['speakers_status']) and $this->settings['speakers_status'])
        {
            $speakers = array();
            foreach($post_speakers as $post_speaker=>$value) $speakers[] = (int) $post_speaker;

            wp_set_post_terms($post_id, $speakers, 'mec_speaker');
        }
        
        // Labels
        $labels = array();
        foreach($post_labels as $post_label=>$value) $labels[] = (int) $post_label;
        
        wp_set_post_terms($post_id, $labels, 'mec_label');
        do_action('mec_label_change_to_radio' , $labels, $post_labels,$post_id);
        
        // Color
        $color = isset($mec['color']) ? sanitize_text_field(trim($mec['color'], '# ')) : '';
        update_post_meta($post_id, 'mec_color', $color);
        
        // Tags
        wp_set_post_tags($post_id, $post_tags);
        
        // Featured Image
        if(trim($featured_image)) $this->main->set_featured_image($featured_image, $post_id);
        else delete_post_thumbnail($post_id);
        
        $read_more = isset($mec['read_more']) ? sanitize_text_field($mec['read_more']) : '';
        $more_info = isset($mec['more_info']) ? (strpos($mec['more_info'], 'http') === false ? 'http://'.sanitize_text_field($mec['more_info']) : sanitize_text_field($mec['more_info'])) : '';
        $more_info_title = isset($mec['more_info_title']) ? sanitize_text_field($mec['more_info_title']) : '';
        $more_info_target = isset($mec['more_info_target']) ? sanitize_text_field($mec['more_info_target']) : '';
        $cost = isset($mec['cost']) ? sanitize_text_field($mec['cost']) : '';
        $note = isset($mec['note']) ? sanitize_text_field($mec['note']) : '';
        
        update_post_meta($post_id, 'mec_read_more', $read_more);
        update_post_meta($post_id, 'mec_more_info', $more_info);
        update_post_meta($post_id, 'mec_more_info_title', $more_info_title);
        update_post_meta($post_id, 'mec_more_info_target', $more_info_target);
        update_post_meta($post_id, 'mec_cost', $cost);
        update_post_meta($post_id, 'mec_note', $note);
        
        // Guest Name and Email
        $fes_guest_email = isset($mec['fes_guest_email']) ? sanitize_email($mec['fes_guest_email']) : '';
        $fes_guest_name = isset($mec['fes_guest_name']) ? sanitize_text_field($mec['fes_guest_name']) : '';
        
        update_post_meta($post_id, 'fes_guest_email', $fes_guest_email);
        update_post_meta($post_id, 'fes_guest_name', $fes_guest_name);
        
        // Location
        $location_id = isset($mec['location_id']) ? sanitize_text_field($mec['location_id']) : 1;
        
        // Selected a saved location
        if($location_id)
        {
            // Set term to the post
            wp_set_object_terms($post_id, (int) $location_id, 'mec_location');
        }
        else
        {
            $address = (isset($mec['location']['address']) and trim($mec['location']['address'])) ? sanitize_text_field($mec['location']['address']) : '';
            $name = (isset($mec['location']['name']) and trim($mec['location']['name'])) ? sanitize_text_field($mec['location']['name']) : (trim($address) ? $address : 'Location Name');

            $term = get_term_by('name', $name, 'mec_location');

            // Term already exists
            if(is_object($term) and isset($term->term_id))
            {
                // Set term to the post
                wp_set_object_terms($post_id, (int) $term->term_id, 'mec_location');
            }
            else
            {
                $term = wp_insert_term($name, 'mec_location');

                $location_id = $term['term_id'];
                if($location_id)
                {
                    // Set term to the post
                    wp_set_object_terms($post_id, (int) $location_id, 'mec_location');

                    $latitude = (isset($mec['location']['latitude']) and trim($mec['location']['latitude'])) ? sanitize_text_field($mec['location']['latitude']) : 0;
                    $longitude = (isset($mec['location']['longitude']) and trim($mec['location']['longitude'])) ? sanitize_text_field($mec['location']['longitude']) : 0;
                    $thumbnail = (isset($mec['location']['thumbnail']) and trim($mec['location']['thumbnail'])) ? sanitize_text_field($mec['location']['thumbnail']) : '';

                    if(!trim($latitude) or !trim($longitude))
                    {
                        $geo_point = $this->main->get_lat_lng($address);

                        $latitude = $geo_point[0];
                        $longitude = $geo_point[1];
                    }

                    update_term_meta($location_id, 'address', $address);
                    update_term_meta($location_id, 'latitude', $latitude);
                    update_term_meta($location_id, 'longitude', $longitude);
                    update_term_meta($location_id, 'thumbnail', $thumbnail);
                }
                else $location_id = 1;
            }
        }
        
        update_post_meta($post_id, 'mec_location_id', $location_id);
        
        $dont_show_map = isset($mec['dont_show_map']) ? sanitize_text_field($mec['dont_show_map']) : 0;
        update_post_meta($post_id, 'mec_dont_show_map', $dont_show_map);
        
        // Organizer
        $organizer_id = isset($mec['organizer_id']) ? sanitize_text_field($mec['organizer_id']) : 1;
        
        // Selected a saved organizer
        if(isset($organizer_id) and $organizer_id)
        {
            // Set term to the post
            wp_set_object_terms($post_id, (int) $organizer_id, 'mec_organizer');
        }
        else
        {
            $name = (isset($mec['organizer']['name']) and trim($mec['organizer']['name'])) ? sanitize_text_field($mec['organizer']['name']) : 'Organizer Name';

            $term = get_term_by('name', $name, 'mec_organizer');

            // Term already exists
            if(is_object($term) and isset($term->term_id))
            {
                // Set term to the post
                wp_set_object_terms($post_id, (int) $term->term_id, 'mec_organizer');
            }
            else
            {
                $term = wp_insert_term($name, 'mec_organizer');

                $organizer_id = $term['term_id'];
                if($organizer_id)
                {
                    // Set term to the post
                    wp_set_object_terms($post_id, (int) $organizer_id, 'mec_organizer');

                    $tel = (isset($mec['organizer']['tel']) and trim($mec['organizer']['tel'])) ? sanitize_text_field($mec['organizer']['tel']) : '';
                    $email = (isset($mec['organizer']['email']) and trim($mec['organizer']['email'])) ? sanitize_text_field($mec['organizer']['email']) : '';
                    $url = (isset($mec['organizer']['url']) and trim($mec['organizer']['url'])) ? (strpos($mec['organizer']['url'], 'http') === false ? 'http://'.sanitize_text_field($mec['organizer']['url']) : sanitize_text_field($mec['organizer']['url'])) : '';
                    $thumbnail = (isset($mec['organizer']['thumbnail']) and trim($mec['organizer']['thumbnail'])) ? sanitize_text_field($mec['organizer']['thumbnail']) : '';

                    update_term_meta($organizer_id, 'tel', $tel);
                    update_term_meta($organizer_id, 'email', $email);
                    update_term_meta($organizer_id, 'url', $url);
                    update_term_meta($organizer_id, 'thumbnail', $thumbnail);
                }
                else $organizer_id = 1;
            }
        }
        
        update_post_meta($post_id, 'mec_organizer_id', $organizer_id);

        // Additional Organizers
        $additional_organizer_ids = isset($mec['additional_organizer_ids']) ? $mec['additional_organizer_ids'] : array();

        foreach($additional_organizer_ids as $additional_organizer_id) wp_set_object_terms($post_id, (int) $additional_organizer_id, 'mec_organizer', true);
        update_post_meta($post_id, 'mec_additional_organizer_ids', $additional_organizer_ids);
        
         // Additional locations
         $additional_location_ids = isset($mec['additional_location_ids']) ? $mec['additional_location_ids'] : array();

         foreach ($additional_location_ids as $additional_location_id) {
             wp_set_object_terms($post_id, (int)$additional_location_id, 'mec_location', true);
         }
         update_post_meta($post_id, 'mec_additional_location_ids', $additional_location_ids);

        // Date Options
        $date = isset($mec['date']) ? $mec['date'] : array();

        $start_date = date('Y-m-d', strtotime($start_date));
        
        // Set the date if it's empty
        if(trim($date['start']['date']) == '') $date['start']['date'] = $start_date;
        
        $start_time_hour = isset($date['start']) ? $date['start']['hour'] : '8';
        $start_time_minutes = isset($date['start']) ? $date['start']['minutes'] : '00';
        $start_time_ampm = (isset($date['start']) and isset($date['start']['ampm'])) ? $date['start']['ampm'] : 'AM';

        $end_date = date('Y-m-d', strtotime($end_date));

        // Fix end_date if it's smaller than start_date
        if(strtotime($end_date) < strtotime($start_date)) $end_date = $start_date;
        
        // Set the date if it's empty
        if(trim($date['end']['date']) == '') $date['end']['date'] = $end_date;
        
        $end_time_hour = isset($date['end']) ? $date['end']['hour'] : '6';
        $end_time_minutes = isset($date['end']) ? $date['end']['minutes'] : '00';
        $end_time_ampm = (isset($date['end']) and isset($date['end']['ampm'])) ? $date['end']['ampm'] : 'PM';
        
        // If 24 hours format is enabled then convert it back to 12 hours
        if(isset($this->settings['time_format']) and $this->settings['time_format'] == 24)
        {
            if($start_time_hour < 12) $start_time_ampm = 'AM';
            elseif($start_time_hour == 12) $start_time_ampm = 'PM';
            elseif($start_time_hour > 12)
            {
                $start_time_hour -= 12;
                $start_time_ampm = 'PM';
            }
            elseif($start_time_hour == 0)
            {
                $start_time_hour = 12;
                $start_time_ampm = 'AM';
            }
            
            if($end_time_hour < 12) $end_time_ampm = 'AM';
            elseif($end_time_hour == 12) $end_time_ampm = 'PM';
            elseif($end_time_hour > 12)
            {
                $end_time_hour -= 12;
                $end_time_ampm = 'PM';
            }
            elseif($end_time_hour == 0)
            {
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
        if($allday == 1)
        {
            $start_time_hour = '8';
            $start_time_minutes = '00';
            $start_time_ampm = 'AM';
            
            $end_time_hour = '6';
            $end_time_minutes = '00';
            $end_time_ampm = 'PM';
        }
        
        $day_start_seconds = $this->main->time_to_seconds($this->main->to_24hours($start_time_hour, $start_time_ampm), $start_time_minutes);
        $day_end_seconds = $this->main->time_to_seconds($this->main->to_24hours($end_time_hour, $end_time_ampm), $end_time_minutes);

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

        update_post_meta($post_id, 'mec_date', $date);

        // Repeat Options
        $repeat = isset($date['repeat']) ? $date['repeat'] : array();
        $certain_weekdays = isset($repeat['certain_weekdays']) ? $repeat['certain_weekdays'] : array();
        
        $repeat_status = isset($repeat['status']) ? 1 : 0;
        $repeat_type = ($repeat_status and isset($repeat['type'])) ? $repeat['type'] : '';
        
        $repeat_interval = ($repeat_status and isset($repeat['interval']) and trim($repeat['interval'])) ? $repeat['interval'] : 1;

        // Advanced Repeat
        $advanced = isset( $repeat['advanced'] ) ? sanitize_text_field($repeat['advanced']) : '';
        
        if(!is_numeric($repeat_interval)) $repeat_interval = NULL;
        
        if($repeat_type == 'weekly') $interval_multiply = 7;
        else $interval_multiply = 1;
        
        // Reset certain weekdays if repeat type is not set to certain weekdays
        if($repeat_type != 'certain_weekdays') $certain_weekdays = array();
        
        if(!is_null($repeat_interval)) $repeat_interval = $repeat_interval*$interval_multiply;
        
        // String To Array
		if($repeat_type == 'advanced' and trim($advanced)) $advanced = explode('-', $advanced);
        else $advanced = array();
        
        $repeat_end = ($repeat_status and isset($repeat['end'])) ? $repeat['end'] : '';
        $repeat_end_at_occurrences = ($repeat_status and isset($repeat['end_at_occurrences'])) ? ($repeat['end_at_occurrences']-1) : '';
        $repeat_end_at_date = ($repeat_status and isset($repeat['end_at_date'])) ? $repeat['end_at_date'] : '';
        if(trim($repeat_end_at_date) != '') $repeat_end_at_date = date('Y-m-d', strtotime($repeat_end_at_date));
        
        update_post_meta($post_id, 'mec_date', $date);
        update_post_meta($post_id, 'mec_repeat', $repeat);
        update_post_meta($post_id, 'mec_certain_weekdays', $certain_weekdays);
        update_post_meta($post_id, 'mec_allday', $allday);
        update_post_meta($post_id, 'mec_hide_time', $hide_time);
        update_post_meta($post_id, 'mec_hide_end_time', $hide_end_time);
        update_post_meta($post_id, 'mec_comment', $comment);
        update_post_meta($post_id, 'mec_repeat_status', $repeat_status);
        update_post_meta($post_id, 'mec_repeat_type', $repeat_type);
        update_post_meta($post_id, 'mec_repeat_interval', $repeat_interval);
        update_post_meta($post_id, 'mec_repeat_end', $repeat_end);
        update_post_meta($post_id, 'mec_repeat_end_at_occurrences', $repeat_end_at_occurrences);
        update_post_meta($post_id, 'mec_repeat_end_at_date', $repeat_end_at_date);
        update_post_meta($post_id, 'mec_advanced_days', $advanced);
        
        // Creating $event array for inserting in mec_events table
        $event = array('post_id'=>$post_id, 'start'=>$start_date, 'repeat'=>$repeat_status, 'rinterval'=>(!in_array($repeat_type, array('daily', 'weekly')) ? NULL : $repeat_interval), 'time_start'=>$day_start_seconds, 'time_end'=>$day_end_seconds);
        
        $year = NULL;
        $month = NULL;
        $day = NULL;
        $week = NULL;
        $weekday = NULL;
        $weekdays = NULL;
        
        // MEC weekdays
        $mec_weekdays = $this->main->get_weekdays();
        
        // MEC weekends
        $mec_weekends = $this->main->get_weekends();

        $plus_date = null;
        if($repeat_type == 'daily')
        {
            $plus_date = '+'.$repeat_end_at_occurrences*$repeat_interval.' Days';
        }
        elseif($repeat_type == 'weekly')
        {
            $plus_date = '+'.$repeat_end_at_occurrences*($repeat_interval).' Days';
        }
        elseif($repeat_type == 'weekday')
        {
            $repeat_interval = 1;
            $plus_date = '+'.$repeat_end_at_occurrences*$repeat_interval.' Weekdays';
            
            $weekdays = ','.implode(',', $mec_weekdays).',';
        }
        elseif($repeat_type == 'weekend')
        {
            $repeat_interval = 1;
            $plus_date = '+'.round($repeat_end_at_occurrences/2)*($repeat_interval*7).' Days';
            
            $weekdays = ','.implode(',', $mec_weekends).',';
        }
        elseif($repeat_type == 'certain_weekdays')
        {
            $repeat_interval = 1;
            $plus_date = '+' . ceil(($repeat_end_at_occurrences * $repeat_interval) * (7/count($certain_weekdays))) . ' days';
            
            $weekdays = ','.implode(',', $certain_weekdays).',';
        }
        elseif($repeat_type == 'monthly')
        {
            $plus_date = '+'.$repeat_end_at_occurrences*$repeat_interval.' Months';
            
            $year = '*';
            $month = '*';
            
            $s = $start_date;
            $e = $end_date;
            
            $_days = array();
            while(strtotime($s) <= strtotime($e))
            {
                $_days[] = date('d', strtotime($s));
                $s = date('Y-m-d', strtotime('+1 Day', strtotime($s)));
            }
            
            $day = ','.implode(',', array_unique($_days)).',';
            
            $week = '*';
            $weekday = '*';
        }
        elseif($repeat_type == 'yearly')
        {
            $plus_date = '+'.$repeat_end_at_occurrences*$repeat_interval.' Years';
            
            $year = '*';
            
            $s = $start_date;
            $e = $end_date;
            
            $_months = array();
            $_days = array();
            while(strtotime($s) <= strtotime($e))
            {
                $_months[] = date('m', strtotime($s));
                $_days[] = date('d', strtotime($s));
                
                $s = date('Y-m-d', strtotime('+1 Day', strtotime($s)));
            }
            
            $month = ','.implode(',', array_unique($_months)).',';
            $day = ','.implode(',', array_unique($_days)).',';
            
            $week = '*';
            $weekday = '*';
        }
        elseif($repeat_type == "advanced")
        {
            // Render class object
            $this->render = $this->getRender();

            // Get finish date
            $event_info = array('start' => $date['start'], 'end' => $date['end']);
            $dates = $this->render->generate_advanced_days($advanced, $event_info, $repeat_end_at_occurrences +1, date( 'Y-m-d', current_time( 'timestamp', 0 )), 'events');
            
            $period_date = $this->main->date_diff($start_date, end($dates)['end']['date']);

            $plus_date = '+' . $period_date->days . ' Days';            
        }

        // "In Days" and "Not In Days"
        $in_days_arr = (isset($mec['in_days']) and is_array($mec['in_days']) and count($mec['in_days'])) ? array_unique($mec['in_days']) : array();
        $not_in_days_arr = (isset($mec['not_in_days']) and is_array($mec['not_in_days']) and count($mec['not_in_days'])) ? array_unique($mec['not_in_days']) : array();

        $in_days = '';
        if(count($in_days_arr)) foreach($in_days_arr as $key=>$in_day_arr) if(is_numeric($key)) $in_days .= $in_day_arr.',';

        $not_in_days = '';
        if(count($not_in_days_arr)) foreach($not_in_days_arr as $key=>$not_in_day_arr) if(is_numeric($key)) $not_in_days .= $not_in_day_arr.',';

        $in_days = trim($in_days, ', ');
        $not_in_days = trim($not_in_days, ', ');

        update_post_meta($post_id, 'mec_in_days', $in_days);
        update_post_meta($post_id, 'mec_not_in_days', $not_in_days);
        
        // Repeat End Date
        if($repeat_end == 'never') $repeat_end_date = '0000-00-00';
        elseif($repeat_end == 'date') $repeat_end_date = $repeat_end_at_date;
        elseif($repeat_end == 'occurrences')
        {
            if($plus_date) $repeat_end_date = date('Y-m-d', strtotime($plus_date, strtotime($end_date)));
            else $repeat_end_date = '0000-00-00';
        }
        else $repeat_end_date = '0000-00-00';

        // If event is not repeating then set the end date of event correctly
        if(!$repeat_status or $repeat_type == 'custom_days') $repeat_end_date = $end_date;

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
        
        if(!$mec_event_id)
        {
            $q1 = "";
            $q2 = "";
            
            foreach($event as $key=>$value)
            {
                $q1 .= "`$key`,";
                
                if(is_null($value)) $q2 .= "NULL,";
                else $q2 .= "'$value',";
            }
            
            $this->db->q("INSERT INTO `#__mec_events` (".trim($q1, ', ').") VALUES (".trim($q2, ', ').")", 'INSERT');
        }
        else
        {
            $q = "";
            
            foreach($event as $key=>$value)
            {
                if(is_null($value)) $q .= "`$key`=NULL,";
                else $q .= "`$key`='$value',";
            }
            
            $this->db->q("UPDATE `#__mec_events` SET ".trim($q, ', ')." WHERE `id`='$mec_event_id'");
        }

        // Update Schedule
        $schedule = $this->getSchedule();
        $schedule->reschedule($post_id, $schedule->get_reschedule_maximum($repeat_type));

        // Hourly Schedule Options
        $raw_hourly_schedules = isset($mec['hourly_schedules']) ? $mec['hourly_schedules'] : array();
        unset($raw_hourly_schedules[':d:']);

        $hourly_schedules = array();
        foreach($raw_hourly_schedules as $raw_hourly_schedule)
        {
            unset($raw_hourly_schedule['schedules'][':i:']);
            $hourly_schedules[] = $raw_hourly_schedule;
        }

        update_post_meta($post_id, 'mec_hourly_schedules', $hourly_schedules);
        
        // Booking and Ticket Options
        $booking = isset($mec['booking']) ? $mec['booking'] : array();
        update_post_meta($post_id, 'mec_booking', $booking);
        
        $tickets = isset($mec['tickets']) ? $mec['tickets'] : array();
        unset($tickets[':i:']);
        
        update_post_meta($post_id, 'mec_tickets', $tickets);
        
        // Fee options
        $fees_global_inheritance = isset($mec['fees_global_inheritance']) ? $mec['fees_global_inheritance'] : 1;
        update_post_meta($post_id, 'mec_fees_global_inheritance', $fees_global_inheritance);
        
        $fees = isset($mec['fees']) ? $mec['fees'] : array();
        update_post_meta($post_id, 'mec_fees', $fees);

        // Ticket Variation options
        $ticket_variations_global_inheritance = isset($mec['ticket_variations_global_inheritance']) ? $mec['ticket_variations_global_inheritance'] : 1;
        update_post_meta($post_id, 'mec_ticket_variations_global_inheritance', $ticket_variations_global_inheritance);

        $ticket_variations = isset($mec['ticket_variations']) ? $mec['ticket_variations'] : array();
        update_post_meta($post_id, 'mec_ticket_variations', $ticket_variations);

        // Registration Fields options
        $reg_fields_global_inheritance = isset($mec['reg_fields_global_inheritance']) ? $mec['reg_fields_global_inheritance'] : 1;
        update_post_meta($post_id, 'mec_reg_fields_global_inheritance', $reg_fields_global_inheritance);

        $reg_fields = isset($mec['reg_fields']) ? $mec['reg_fields'] : array();
        if($reg_fields_global_inheritance) $reg_fields = array();

        update_post_meta($post_id, 'mec_reg_fields', $reg_fields);

        // Organizer Payment Options
        $op = isset($mec['op']) ? $mec['op'] : array();
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

        $message = '';
        if($status == 'pending') $message = __('The event submitted. It will publish as soon as possible.', 'modern-events-calendar-lite');
        elseif($status == 'publish') $message = __('The event published.', 'modern-events-calendar-lite');
        
        // Trigger Event
        if($method == 'updated') do_action('mec_fes_updated', $post_id , 'update');
        else do_action('mec_fes_added', $post_id , '');
        
        $this->main->response(array('success'=>1, 'message'=>$message, 'data'=>array('post_id'=>$post_id)));
    }
    
    public function link_add_event()
    {
        if(isset($this->settings['fes_form_page']) and trim($this->settings['fes_form_page'])) return get_permalink($this->settings['fes_form_page']);
        else return $this->main->add_qs_var('post_id', '-1', $this->main->remove_qs_var('vlist'));
    }
    
    public function link_edit_event($post_id)
    {
        if(isset($this->settings['fes_form_page']) and trim($this->settings['fes_form_page'])) return $this->main->add_qs_var('post_id', $post_id, get_permalink($this->settings['fes_form_page']));
        else return $this->main->add_qs_var('post_id', $post_id, $this->main->remove_qs_var('vlist'));
    }
    
    public function link_list_events()
    {
        if(isset($this->settings['fes_list_page']) and trim($this->settings['fes_list_page'])) return get_permalink($this->settings['fes_list_page']);
        else return $this->main->add_qs_var('vlist', 1, $this->main->remove_qs_var('post_id'));
    }
}

// FES Categories Custom Walker
class FES_Custom_Walker extends Walker_Category 
{
    /**
     * This class is a custom walker for front end event submission hierarchical categories customizing
     */
    private $post_id;

    function __construct($post_id)
    {
        $this->post_id = $post_id;
    }

    function start_lvl(&$output, $depth = 0, $args = array())
    {
        $indent  = str_repeat("\t", $depth);
        $output .= "$indent<div class='mec-fes-category-children'>";
    }

    function end_lvl(&$output, $depth = 0, $args = array())
    {
        $indent  = str_repeat("\t", $depth);
        $output .= "$indent</div>";
    }

    function start_el(&$output, $category, $depth = 0, $args = array(), $id = 0)
    {
        $post_categories = get_the_terms($this->post_id, 'mec_category');

        $categories = array();
        if($post_categories) foreach($post_categories as $post_category) $categories[] = $post_category->term_id;

        $output .= '<label for="mec_fes_categories' . $category->term_id . '">
        <input type="checkbox" name="mec[categories][' . $category->term_id . ']"
        id="mec_fes_categories' . $category->term_id .'" value="1"' . (in_array($category->term_id, $categories) ? 'checked="checked"' : '') . '/>' . $category->name;
    }

    function end_el(&$output, $page, $depth = 0, $args = array())
    {
        $output .= '</label>';
    }
}