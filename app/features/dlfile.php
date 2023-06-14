<?php
/** no direct access **/

use MEC\FES\FormBuilder;

defined('MECEXEC') or die();

/**
 * Webnus MEC Downloadable File class.
 * @author Webnus <info@webnus.net>
 */
class MEC_feature_dlfile extends MEC_base
{
    public $factory;
    public $main;
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

        // MEC Settings
        $this->settings = $this->main->get_settings();
    }

    /**
     * Initialize locations feature
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        // Booking Status
        $booking_status = (isset($this->settings['booking_status']) and $this->settings['booking_status']) ? true : false;

        // Booking Download Status
        $booking_download = (isset($this->settings['downloadable_file_status']) and $this->settings['downloadable_file_status']);

        // Public Download Module
        $public_download_module = (isset($this->settings['public_download_module']) and $this->settings['public_download_module']);

        // Booking download is enabled
        if($booking_status and $booking_download)
        {
            // Metabox
            $this->factory->action('mec_metabox_booking', array($this, 'meta_box_downloadable_file'), 17);

            // Downloadable File for FES
            if(!isset($this->settings['fes_section_downloadable_file']) or (isset($this->settings['fes_section_downloadable_file']) and $this->settings['fes_section_downloadable_file'])) $this->factory->action('mec_fes_metabox_details', array($this, 'meta_box_downloadable_file'), 47);

            // AJAX
            $this->factory->action('wp_ajax_mec_downloadable_file_upload', array($this, 'upload'));
        }

        // Public Download Module is enabled
        if($public_download_module)
        {
            // Metabox
            $this->factory->action('mec_metabox_details', array($this, 'meta_box_public_module'), 18);

            // Downloadable File for FES
            if(!isset($this->settings['fes_section_public_download_module']) or (isset($this->settings['fes_section_public_download_module']) and $this->settings['fes_section_public_download_module'])) $this->factory->action('mec_fes_metabox_details', array($this, 'meta_box_public_module'), 32);

            // AJAX
            $this->factory->action('wp_ajax_mec_public_download_module_file_upload', array($this, 'public_download_module_upload'));
        }
    }

    /**
     * Show downloadable file of event into the Add/Edit event page
     *
     * @author Webnus <info@webnus.net>
     * @param object $post
     */
    public function meta_box_downloadable_file($post){

        FormBuilder::downloadable_file( $post );
    }

    public function upload()
    {
        // Check if our nonce is set.
        if(!isset($_POST['_wpnonce'])) $this->main->response(array('success'=>0, 'code'=>'NONCE_MISSING'));

        // Verify that the nonce is valid.
        if(!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'mec_downloadable_file_upload')) $this->main->response(array('success'=>0, 'code'=>'NONCE_IS_INVALID'));

        $uploaded_file = isset($_FILES['file']) ? $_FILES['file'] : NULL;

        // No file
        if(!$uploaded_file) $this->main->response(array('success'=>0, 'code'=>'NO_FILE', 'message'=>esc_html__('Please upload a file.', 'modern-events-calendar-lite')));

        $allowed = array('gif', 'jpeg', 'jpg', 'png', 'pdf', 'zip');

        $ex = explode('.', $uploaded_file['name']);
        $extension = end($ex);

        // Invalid Extension
        if(!in_array($extension, $allowed)) $this->main->response(array('success'=>0, 'code'=>'INVALID_EXTENSION', 'message'=>sprintf(esc_html__('File extension is invalid. You can upload %s files.', 'modern-events-calendar-lite'), implode(', ', $allowed))));

        // Maximum File Size
        $max_file_size = isset($this->settings['fes_max_file_size']) ? (int) ($this->settings['fes_max_file_size'] * 1000) : (5000 * 1000);

        // Invalid Size
        if($uploaded_file['size'] > $max_file_size) $this->main->response(array('success'=>0, 'code'=>'IMAGE_IS_TOO_BIG', 'message'=>sprintf(esc_html__('File is too big. Maximum size is %s KB.', 'modern-events-calendar-lite'), ($max_file_size / 1000))));

        // Include the functions
        if(!function_exists('wp_handle_upload'))
        {
            require_once ABSPATH.'wp-admin/includes/file.php';
            require_once(ABSPATH.'wp-admin/includes/image.php');
        }

        $upload = wp_upload_bits($uploaded_file['name'], NULL, file_get_contents($uploaded_file['tmp_name']));
        $wp_filetype = wp_check_filetype(basename($upload['file']), NULL);

        $wp_upload_dir = wp_upload_dir();
        $attachment = array(
            'guid' => $wp_upload_dir['baseurl'] . _wp_relative_upload_path($upload['file']),
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($upload['file'])),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attach_id = wp_insert_attachment($attachment, $upload['file']);
        wp_update_attachment_metadata($attach_id, wp_generate_attachment_metadata($attach_id, $upload['file']));

        $success = 0;
        $data = array();

        if($attach_id and (!isset($upload['error']) or (isset($upload['error']) and !$upload['error'])))
        {
            $success = 1;
            $message = esc_html__('File uploaded!', 'modern-events-calendar-lite');

            $data['url'] = $upload['url'];
            $data['id'] = $attach_id;
        }
        else
        {
            $message = $upload['error'];
        }

        $this->main->response(array('success'=>$success, 'message'=>$message, 'data'=>$data));
    }

    /**
     * Show public download module of event into the Add/Edit event page
     *
     * @author Webnus <info@webnus.net>
     * @param object $post
     */
    public function meta_box_public_module($post){

        FormBuilder::public_download( $post );
    }

    public function public_download_module_upload()
    {
        // Check if our nonce is set.
        if(!isset($_POST['_wpnonce'])) $this->main->response(array('success'=>0, 'code'=>'NONCE_MISSING'));

        // Verify that the nonce is valid.
        if(!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'mec_public_download_module_file_upload')) $this->main->response(array('success'=>0, 'code'=>'NONCE_IS_INVALID'));

        $uploaded_file = isset($_FILES['file']) ? $_FILES['file'] : NULL;

        // No file
        if(!$uploaded_file) $this->main->response(array('success'=>0, 'code'=>'NO_FILE', 'message'=>esc_html__('Please upload a file.', 'modern-events-calendar-lite')));

        $allowed = array('docx', 'jpeg', 'jpg', 'png', 'pdf', 'zip');

        $ex = explode('.', $uploaded_file['name']);
        $extension = end($ex);

        // Invalid Extension
        if(!in_array($extension, $allowed)) $this->main->response(array('success'=>0, 'code'=>'INVALID_EXTENSION', 'message'=>sprintf(esc_html__('File extension is invalid. You can upload %s files.', 'modern-events-calendar-lite'), implode(', ', $allowed))));

        // Maximum File Size
        $max_file_size = isset($this->settings['fes_max_file_size']) ? (int) ($this->settings['fes_max_file_size'] * 1000) : (5000 * 1000);

        // Invalid Size
        if($uploaded_file['size'] > $max_file_size) $this->main->response(array('success'=>0, 'code'=>'IMAGE_IS_TOO_BIG', 'message'=>sprintf(esc_html__('File is too big. Maximum size is %s KB.', 'modern-events-calendar-lite'), ($max_file_size / 1000))));

        // Include the functions
        if(!function_exists('wp_handle_upload'))
        {
            require_once ABSPATH.'wp-admin/includes/file.php';
            require_once(ABSPATH.'wp-admin/includes/image.php');
        }

        $upload = wp_upload_bits($uploaded_file['name'], NULL, file_get_contents($uploaded_file['tmp_name']));
        $wp_filetype = wp_check_filetype(basename($upload['file']), NULL);

        $wp_upload_dir = wp_upload_dir();
        $attachment = array(
            'guid' => $wp_upload_dir['baseurl'] . _wp_relative_upload_path($upload['file']),
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($upload['file'])),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attach_id = wp_insert_attachment($attachment, $upload['file']);
        wp_update_attachment_metadata($attach_id, wp_generate_attachment_metadata($attach_id, $upload['file']));

        $success = 0;
        $data = array();

        if($attach_id and (!isset($upload['error']) or (isset($upload['error']) and !$upload['error'])))
        {
            $success = 1;
            $message = esc_html__('File uploaded!', 'modern-events-calendar-lite');

            $data['url'] = $upload['url'];
            $data['id'] = $attach_id;
        }
        else
        {
            $message = $upload['error'];
        }

        $this->main->response(array('success'=>$success, 'message'=>$message, 'data'=>$data));
    }
}