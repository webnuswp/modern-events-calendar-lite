<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_feature_mec extends MEC_base
{
    /**
     * @var MEC_factory
     */
    public $factory;

    /**
     * @var MEC_db
     */
    public $db;

    /**
     * @var MEC_main
     */
    public $main;

    /**
     * @var MEC_notifications
     */
    public $notifications;
    public $settings;
    public $page;
    public $PT;

    /**
     * Constructor method
     * @author Webnus <info@webnus.biz>
     */
    public function __construct()
    {
        // Import MEC Factory
        $this->factory = $this->getFactory();
        
        // Import MEC DB
        $this->db = $this->getDB();
        
        // Import MEC Main
        $this->main = $this->getMain();
        
        // Import MEC Notifications
        $this->notifications = $this->getNotifications();
        
        // MEC Settings
        $this->settings = $this->main->get_settings();
    }
    
    /**
     * Initialize calendars feature
     * @author Webnus <info@webnus.biz>
     */
    public function init()
    {
        $this->factory->action('admin_menu', array($this, 'menus'));
        $this->factory->action('admin_menu', array($this, 'support_menu'), 21);
        $this->factory->action('init', array($this, 'register_post_type'));
        $this->factory->action('add_meta_boxes', array($this, 'register_meta_boxes'), 1);
        
        $this->factory->action('parent_file', array($this, 'mec_parent_menu_highlight'));
        $this->factory->action('submenu_file', array($this, 'mec_sub_menu_highlight'));
        
        // Google recaptcha
        $this->factory->filter('mec_grecaptcha_include', array($this, 'grecaptcha_include'));
        
        // Google Maps API
        $this->factory->filter('mec_gm_include', array($this, 'gm_include'));
        
        $this->factory->filter('manage_mec_calendars_posts_columns', array($this, 'filter_columns'));
        $this->factory->action('manage_mec_calendars_posts_custom_column', array($this, 'filter_columns_content'), 10, 2);
        
        $this->factory->action('save_post', array($this, 'save_calendar'), 10);
        
        // BuddyPress Integration
        $this->factory->action('mec_booking_confirmed', array($this->main, 'bp_add_activity'), 10);
        $this->factory->action('mec_booking_verified', array($this->main, 'bp_add_activity'), 10);
        $this->factory->action('bp_register_activity_actions', array($this->main, 'bp_register_activity_actions'), 10);
        
        // Mailchimp Integration
        $this->factory->action('mec_booking_verified', array($this->main, 'mailchimp_add_subscriber'), 10);
        
        // MEC Notifications
        $this->factory->action('mec_booking_completed', array($this->notifications, 'email_verification'), 10);
        $this->factory->action('mec_booking_completed', array($this->notifications, 'booking_notification'), 11);
        $this->factory->action('mec_booking_completed', array($this->notifications, 'admin_notification'), 12);
        $this->factory->action('mec_booking_confirmed', array($this->notifications, 'booking_confirmation'), 10);
        $this->factory->action('mec_booking_canceled', array($this->notifications, 'booking_cancellation'), 12);
        $this->factory->action('mec_fes_added', array($this->notifications, 'new_event'), 50, 3);
        $this->factory->action('mec_event_published', array($this->notifications, 'user_event_publishing'), 10, 3);

        $this->page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'MEC-settings';
        
        // MEC Post Type Name
        $this->PT = $this->main->get_main_post_type();

        // Disable Block Editor
        $gutenberg_status = (!isset($this->settings['gutenberg']) or (isset($this->settings['gutenberg']) and $this->settings['gutenberg'])) ? true : false;
        if($gutenberg_status):
        $this->factory->filter('gutenberg_can_edit_post_type', array($this, 'gutenberg'), 10, 2);
        $this->factory->filter('use_block_editor_for_post_type', array($this, 'gutenberg'), 10, 2);
        endif; 

        // Export Settings
        $this->factory->action('wp_ajax_download_settings', array($this, 'download_settings'));
        $this->factory->action('wp_ajax_nopriv_download_settings', array($this, 'download_settings'));

        // Import Settings
        $this->factory->action('wp_ajax_import_settings', array($this, 'import_settings'));
        $this->factory->action('wp_ajax_nopriv_import_settings', array($this, 'import_settings'));

        // License Activation
        $this->factory->action('wp_ajax_activate_license', array($this, 'activate_license'));
        $this->factory->action('wp_ajax_nopriv_activate_license', array($this, 'activate_license'));

        // Close Notification
        $this->factory->action('wp_ajax_close_notification', array($this, 'close_notification'));
        $this->factory->action('wp_ajax_nopriv_close_notification', array($this, 'close_notification'));

        // Scheduler Cronjob
        $schedule = $this->getSchedule();
        $this->factory->action('mec_scheduler', array($schedule, 'cron'));

        $syncSchedule = $this->getSyncSchedule();
        $this->factory->action('mec_syncScheduler', array($syncSchedule, 'sync'));
    }

    /* Activate License */
    public function activate_license()
    {
        if(!wp_verify_nonce($_REQUEST['nonce'], 'mec_settings_nonce'))
        {
            exit();
        }

        $options = get_option('mec_options');

        $options['product_name'] = $_REQUEST['content']['LicenseTypeJson'];
        $options['purchase_code'] = $_REQUEST['content']['PurchaseCodeJson'];
        update_option( 'mec_options' , $options);

        $verify = NULL;
        if($this->getPRO())
        {
            $envato = $this->getEnvato();
            $verify = $envato->get_MEC_info('dl');
        }

        if(!is_null($verify))
        {
            $LicenseStatus = 'success';
        }
        else 
        {
            $LicenseStatus = __('Activation faild. Please check your purchase code or license type.<br><b>Note: Your purchase code should match your licesne type.</b>' , 'modern-events-calendar-lite') . '<a style="text-decoration: underline; padding-left: 7px;" href="https://webnus.ticksy.com/article/14445/" target="_blank">'  . __('Troubleshooting' , 'modern-events-calendar-lite') . '</a>';
        }

        echo $LicenseStatus;
        wp_die();
    }
    

    /* Download MEC settings */
    public function download_settings()
    {
        if(!wp_verify_nonce( $_REQUEST['nonce'], 'mec_settings_download'))
        {
            exit();
        }

        $content = get_option('mec_options');
        $content = json_encode($content, true);

        header('Content-type: application/txt');
        header('Content-Description: MEC Settings');
        header('Content-Disposition: attachment; filename="mec_options_backup_' . date( 'd-m-Y' ) . '.json"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
        print_r($content);
        wp_die();
    }

    /* Close addons notification */
    public function close_notification()
    {
        if(!wp_verify_nonce( $_REQUEST['nonce'], 'mec_settings_nonce'))
        {
            exit();
        }
        update_option('mec_addons_notification_option', 'open');
        wp_die();
    }

    /* Download MEC settings */
    public function import_settings()
    {
        if(!wp_verify_nonce( $_REQUEST['nonce'], 'mec_settings_nonce'))
        {
            exit();
        }

        $options = $_REQUEST['content'];
        if($options == 'No-JSON')
        {
            echo '<div class="mec-message-import-error">' . esc_html__('Your options is not in JSON format. Please insert correct options in this field and try again.' , 'modern-events-calendar-lite') . '</div>';
            exit();
        }
        else
        {
            if(empty($options))
            {
                echo '<div class="mec-message-import-error">' . esc_html__('Your options field can not be empty!' , 'modern-events-calendar-lite') . '</div>';
                exit;
            }
            else
            {
                update_option('mec_options' , $options);
                echo '<div class="mec-message-import-success">' . esc_html__('Your options imported successfuly.', 'modern-events-calendar-lite') . '</div>';
            }
        }

        wp_die();
    }

    /**
     * highlighting menu when click on taxonomy
     * @author Webnus <info@webnus.biz>
     * @param string $parent_file
     * @return string
     */
    public function mec_parent_menu_highlight($parent_file)
    {
        global $current_screen;
        
        $taxonomy = $current_screen->taxonomy;
        $post_type = $current_screen->post_type;
        
        // Don't do amything if the post type is not our post type
        if($post_type != $this->PT) return $parent_file;
        
        switch($taxonomy)
        {
            case 'mec_category':
            case 'post_tag':
            case 'mec_label':
            case 'mec_location':
            case 'mec_organizer':
            case 'mec_speaker':

                $parent_file = 'mec-intro';
                break;
            
            default:
                //nothing
                break;
        }
        
        return $parent_file;
    }
    
    public function mec_sub_menu_highlight($submenu_file)
    {
        global $current_screen;
        
        $taxonomy = $current_screen->taxonomy;
        $post_type = $current_screen->post_type;
        
        // Don't do amything if the post type is not our post type
        if($post_type != $this->PT) return $submenu_file;
        
        switch($taxonomy)
        {
            case 'mec_category':
                
                $submenu_file = 'edit-tags.php?taxonomy=mec_category&post_type='.$this->PT;
                break;
            case 'post_tag':
                
                $submenu_file = 'edit-tags.php?taxonomy=post_tag&post_type='.$this->PT;
                break;
            case 'mec_label':
                
                $submenu_file = 'edit-tags.php?taxonomy=mec_label&post_type='.$this->PT;
                break;
            case 'mec_location':
                
                $submenu_file = 'edit-tags.php?taxonomy=mec_location&post_type='.$this->PT;
                break;
            case 'mec_organizer':
                
                $submenu_file = 'edit-tags.php?taxonomy=mec_organizer&post_type='.$this->PT;
                break;
            case 'mec_speaker':

                $submenu_file = 'edit-tags.php?taxonomy=mec_speaker&post_type='.$this->PT;
                break;
            default:
                //nothing
                break;
        }
        
        return $submenu_file;
    }

    /**
     * Add the support menu
     * @author Webnus <info@webnus.biz>
     */
    public function support_menu()
    {
        add_submenu_page('mec-intro', __('MEC - Support', 'modern-events-calendar-lite'), __('Support', 'modern-events-calendar-lite'), 'manage_options', 'MEC-support', array($this, 'support_page'));
    }

    /**
     * Add the calendars menu
     * @author Webnus <info@webnus.biz>
     */
    public function menus()
    {
        global $submenu;
        unset($submenu['mec-intro'][2]);
        
        remove_menu_page('edit.php?post_type=mec-events');
        remove_menu_page('edit.php?post_type=mec_calendars');
        do_action('before_mec_submenu_action');
        
        add_submenu_page('mec-intro', __('Add Event', 'modern-events-calendar-lite'), __('Add Event', 'modern-events-calendar-lite'), 'edit_posts', 'post-new.php?post_type='.$this->PT);
        add_submenu_page('mec-intro', __('Tags', 'modern-events-calendar-lite'), __('Tags', 'modern-events-calendar-lite'), 'edit_others_posts', 'edit-tags.php?taxonomy=post_tag&post_type='.$this->PT);
        add_submenu_page('mec-intro', $this->main->m('taxonomy_categories', __('Categories', 'modern-events-calendar-lite')), $this->main->m('taxonomy_categories', __('Categories', 'modern-events-calendar-lite')), 'edit_others_posts', 'edit-tags.php?taxonomy=mec_category&post_type='.$this->PT);
        add_submenu_page('mec-intro', $this->main->m('taxonomy_labels', __('Labels', 'modern-events-calendar-lite')), $this->main->m('taxonomy_labels', __('Labels', 'modern-events-calendar-lite')), 'edit_others_posts', 'edit-tags.php?taxonomy=mec_label&post_type='.$this->PT);
        add_submenu_page('mec-intro', $this->main->m('taxonomy_locations', __('Locations', 'modern-events-calendar-lite')), $this->main->m('taxonomy_locations', __('Locations', 'modern-events-calendar-lite')), 'edit_others_posts', 'edit-tags.php?taxonomy=mec_location&post_type='.$this->PT);
        add_submenu_page('mec-intro', $this->main->m('taxonomy_organizers', __('Organizers', 'modern-events-calendar-lite')), $this->main->m('taxonomy_organizers', __('Organizers', 'modern-events-calendar-lite')), 'edit_others_posts', 'edit-tags.php?taxonomy=mec_organizer&post_type='.$this->PT);

        // Speakers Menu
        if(isset($this->settings['speakers_status']) and $this->settings['speakers_status'])
        {
            add_submenu_page('mec-intro', $this->main->m('taxonomy_speakers', __('Speakers', 'modern-events-calendar-lite')), $this->main->m('taxonomy_speakers', __('Speakers', 'modern-events-calendar-lite')), 'edit_others_posts', 'edit-tags.php?taxonomy=mec_speaker&post_type='.$this->PT);
        }

        add_submenu_page('mec-intro', __('Shortcodes', 'modern-events-calendar-lite'), __('Shortcodes', 'modern-events-calendar-lite'), 'edit_others_posts', 'edit.php?post_type=mec_calendars');
        add_submenu_page('mec-intro', __('MEC - Settings', 'modern-events-calendar-lite'), __('Settings', 'modern-events-calendar-lite'), 'manage_options', 'MEC-settings', array($this, 'page'));
        add_submenu_page('mec-intro', __('MEC - Addons', 'modern-events-calendar-lite'), __('Addons', 'modern-events-calendar-lite'), 'manage_options', 'MEC-addons', array($this, 'addons'));

        do_action('after_mec_submenu_action');
    }
    
    /**
     * Register post type of calendars/custom shortcodes
     * @author Webnus <info@webnus.biz>
     * 
     */
    public function register_post_type()
    {
        $elementor = class_exists('MEC_Shortcode_Builder') && did_action('elementor/loaded') ? true : false;

        register_post_type('mec_calendars',
            array(
                'labels'=>array
                (
                    'name'=>__('Shortcodes', 'modern-events-calendar-lite'),
                    'singular_name'=>__('Shortcode', 'modern-events-calendar-lite'),
                    'add_new'=>__('Add Shortcode', 'modern-events-calendar-lite'),
                    'add_new_item'=>__('Add New Shortcode', 'modern-events-calendar-lite'),
                    'not_found'=>__('No shortcodes found!', 'modern-events-calendar-lite'),
                    'all_items'=>__('All Shortcodes', 'modern-events-calendar-lite'),
                    'edit_item'=>__('Edit shortcodes', 'modern-events-calendar-lite'),
                    'not_found_in_trash'=>__('No shortcodes found in Trash!', 'modern-events-calendar-lite')
                ),
                'public'=>$elementor,
                'show_in_nav_menus'=>false,
                'show_in_admin_bar'=>$elementor,
                'show_ui'=>true,
                'has_archive'=>false,
                'exclude_from_search'=>true,
                'publicly_queryable'=>$elementor,
                'show_in_menu'=>'mec-intro',
                'supports'=>array('title')
            )
        );

        do_action('mec_register_post_type');
    }
    
    /**
     * Filter columns of calendars/custom shortcodes
     * @author Webnus <info@webnus.biz>
     * @param array $columns
     * @return array
     */
    public function filter_columns($columns)
    {
        $columns['shortcode'] = __('Shortcode', 'modern-events-calendar-lite');
        return $columns;
    }
    
    /**
     * Filter column content of calendars/custom shortcodes
     * @author Webnus <info@webnus.biz>
     * @param string $column_name
     * @param int $post_id
     */
    public function filter_columns_content($column_name, $post_id)
    {
        if($column_name == 'shortcode')
        {
            echo '[MEC id="'.$post_id.'"]';
        }
    }
    
    /**
     * Register meta boxes of calendars/custom shortcodes
     * @author Webnus <info@webnus.biz>
     */
    public function register_meta_boxes()
    {
        // Fix conflict between Ultimate GDPR and niceSelect
        $screen = get_current_screen();
        if ( $screen->id == 'mec_calendars' ) remove_all_actions('acf/input/admin_head');

		add_meta_box('mec_calendar_display_options', __('Display Options', 'modern-events-calendar-lite'), array($this, 'meta_box_display_options'), 'mec_calendars', 'normal', 'high');
        add_meta_box('mec_calendar_filter', __('Filter Options', 'modern-events-calendar-lite'), array($this, 'meta_box_filter'), 'mec_calendars', 'normal', 'high');
        add_meta_box('mec_calendar_shortcode', __('Shortcode', 'modern-events-calendar-lite'), array($this, 'meta_box_shortcode'), 'mec_calendars', 'side');
        add_meta_box('mec_calendar_search_form', __('Search Form', 'modern-events-calendar-lite'), array($this, 'meta_box_search_form'), 'mec_calendars', 'side');
    }
    
    /**
     * Save calendars/custom shortcodes
     * @author Webnus <info@webnus.biz>
     * @param int $post_id
     * @return void
     */
    public function save_calendar($post_id)
    {
        // Check if our nonce is set.
        if(!isset($_POST['mec_calendar_nonce'])) return;

        // Verify that the nonce is valid.
        if(!wp_verify_nonce(sanitize_text_field($_POST['mec_calendar_nonce']), 'mec_calendar_data')) return;

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if(defined('DOING_AUTOSAVE') and DOING_AUTOSAVE) return;
        
        $terms = isset($_POST['mec_tax_input']) ? $_POST['mec_tax_input'] : array();
        
        $categories = (isset($terms['mec_category']) and is_array($terms['mec_category'])) ? implode(',', $terms['mec_category']) : '';
        $locations = (isset($terms['mec_location']) and is_array($terms['mec_location'])) ? implode(',', $terms['mec_location']) : '';
        $organizers = (isset($terms['mec_organizer']) and is_array($terms['mec_organizer'])) ? implode(',', $terms['mec_organizer']) : '';
        $labels = (isset($terms['mec_label']) and is_array($terms['mec_label'])) ? implode(',', $terms['mec_label']) : '';
        $tags = (isset($terms['mec_tag'])) ? explode(',', trim($terms['mec_tag'])) : '';
        $authors = (isset($terms['mec_author']) and is_array($terms['mec_author'])) ? implode(',', $terms['mec_author']) : '';
        
        // Fox tags
        if(is_array($tags) and count($tags) == 1 and trim($tags[0]) == '') $tags = array();
        if(is_array($tags))
        {
            $tags = array_map('trim', $tags);
            $tags = implode(',', $tags);
        }
        
        update_post_meta($post_id, 'label', $labels);
        update_post_meta($post_id, 'category', $categories);
        update_post_meta($post_id, 'location', $locations);
        update_post_meta($post_id, 'organizer', $organizers);
        update_post_meta($post_id, 'tag', $tags);
        update_post_meta($post_id, 'author', $authors);
        
        do_action('mec_shortcode_filters_save' , $post_id , $terms );
        
        $mec = isset($_POST['mec']) ? $_POST['mec'] : array();
        
        foreach($mec as $key=>$value) update_post_meta($post_id, $key, $value);
    }
    
    /**
     * Show content of filter meta box
     * @author Webnus <info@webnus.biz>
     * @param object $post
     */
    public function meta_box_filter($post)
    {
        $path = MEC::import('app.features.mec.meta_boxes.filter', true, true);

        ob_start();
        include $path;
        echo $output = ob_get_clean();
    }
    
    /**
     * Show content of shortcode meta box
     * @author Webnus <info@webnus.biz>
     * @param object $post
     */
    public function meta_box_shortcode($post)
    {
        $path = MEC::import('app.features.mec.meta_boxes.shortcode', true, true);

        ob_start();
        include $path;
        echo $output = ob_get_clean();
    }
    
    /**
     * Show content of search form meta box
     * @author Webnus <info@webnus.biz>
     * @param object $post
     */
    public function meta_box_search_form($post)
    {
        $path = MEC::import('app.features.mec.meta_boxes.search_form', true, true);

        ob_start();
        include $path;
        echo $output = ob_get_clean();
    }
    
    /**
     * Show content of display options meta box
     * @author Webnus <info@webnus.biz>
     * @param object $post
     */
    public function meta_box_display_options($post)
    {
        $path = MEC::import('app.features.mec.meta_boxes.display_options', true, true);

        ob_start();
        include $path;
        echo $output = ob_get_clean();
    }
    
    /**
     * Show content of skin options meta box
     * @author Webnus <info@webnus.biz>
     * @param object $post
     */
    public function meta_box_skin_options($post)
    {
        $path = MEC::import('app.features.mec.meta_boxes.skin_options', true, true);

        ob_start();
        include $path;
        echo $output = ob_get_clean();
    }

    /**
     * Get Addons page
     * @author Webnus <info@webnus.biz>
     * @return void
     */
    public function addons()
    {
        $this->display_addons();
    }

    /**
     * Show Addons page
     * @author Webnus <info@webnus.biz>
     * @return void
     */
    public function display_addons()
    {
        $path = MEC::import('app.features.mec.addons', true, true);
        ob_start();
        include $path;
        echo $output = ob_get_clean();
    }

    /**
     * Show support page
     * @author Webnus <info@webnus.biz>
     * @return void
     */
    public function display_support()
    {
        $path = MEC::import('app.features.mec.support-page', true, true);
        ob_start();
        include $path;
        echo $output = ob_get_clean();
    }

    /**
     * support page
     * @author Webnus <info@webnus.biz>
     * @return void
     */
    public function support_page()
    {
        $this->display_support();
    }
    
    /**
     * Show content settings menu
     * @author Webnus <info@webnus.biz>
     * @return void
     */
    public function page()
    {
        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'MEC-settings';
        
        if($tab == 'MEC-customcss') $this->styles();
        elseif($tab == 'MEC-ie') $this->import_export();
        elseif($tab == 'MEC-notifications') $this->notifications();
        elseif($tab == 'MEC-messages') $this->messages();
        elseif($tab == 'MEC-styling') $this->styling();
        elseif($tab == 'MEC-single') $this->single();
        elseif($tab == 'MEC-booking') $this->booking();
        elseif($tab == 'MEC-modules') $this->modules();
        else $this->settings();
    }
    
    /**
     * Show content of settings tab
     * @author Webnus <info@webnus.biz>
     * @return void
     */
    public function settings()
    {
        $path = MEC::import('app.features.mec.settings', true, true);
        
        ob_start();
        include $path;
        echo $output = ob_get_clean();
    }
    
    /**
     * Show content of styles tab
     * @author Webnus <info@webnus.biz>
     * @return void
     */
    public function styles()
    {
        $path = MEC::import('app.features.mec.styles', true, true);

        ob_start();
        include $path;
        echo $output = ob_get_clean();
    }
    
    /**
     * Show content of styling tab
     * @author Webnus <info@webnus.biz>
     * @return void
     */
    public function styling()
    {
        $path = MEC::import('app.features.mec.styling', true, true);

        ob_start();
        include $path;
        echo $output = ob_get_clean();
    }
    
    /**
     * Show content of single tab
     * @author Webnus <info@webnus.biz>
     * @return void
     */
    public function single()
    {
        $path = MEC::import('app.features.mec.single', true, true);

        ob_start();
        include $path;
        echo $output = ob_get_clean();
    }
    
    /**
     * Show content of booking tab
     * @author Webnus <info@webnus.biz>
     * @return void
     */
    public function booking()
    {
        $path = MEC::import('app.features.mec.booking', true, true);

        ob_start();
        include $path;
        echo $output = ob_get_clean();
    }
    
    /**
     * Show content of modules tab
     * @author Webnus <info@webnus.biz>
     * @return void
     */
    public function modules()
    {
        $path = MEC::import('app.features.mec.modules', true, true);

        ob_start();
        include $path;
        echo $output = ob_get_clean();
    }

    /**
     * Show content of import/export tab
     * @author Webnus <info@webnus.biz>
     * @return void
     */
    public function import_export()
    {
        $path = MEC::import('app.features.mec.ie', true, true);

        ob_start();
        include $path;
        echo $output = ob_get_clean();
    }
    
    /**
     * Show content of notifications tab
     * @author Webnus <info@webnus.biz>
     * @return void
     */
    public function notifications()
    {
        $path = MEC::import('app.features.mec.notifications', true, true);
        
        ob_start();
        include $path;
        echo $output = ob_get_clean();
    }
    
    /**
     * Show content of messages tab
     * @author Webnus <info@webnus.biz>
     * @return void
     */
    public function messages()
    {
        $path = MEC::import('app.features.mec.messages', true, true);
        
        ob_start();
        include $path;
        echo $output = ob_get_clean();
    }
    
    /**
     * Whether to include google recaptcha library
     * @author Webnus <info@webnus.biz>
     * @param boolean $grecaptcha_include
     * @return boolean
     */
    public function grecaptcha_include($grecaptcha_include)
    {
        // Don't include the library if google recaptcha is not enabled
        if(!$this->main->get_recaptcha_status()) return false;
        
        return $grecaptcha_include;
    }
    
    /**
     * Whether to include google map library
     * @author Webnus <info@webnus.biz>
     * @param boolean $gm_include
     * @return boolean
     */
    public function gm_include($gm_include)
    {
        // Don't include the library if google Maps API is set to don't load
        if(isset($this->settings['google_maps_dont_load_api']) and $this->settings['google_maps_dont_load_api']) return false;
        
        return $gm_include;
    }

    /**
     * Single Event Display Method
     * @param string $skin
     * @param int $value
     * @return string
     */
    public function sed_method_field($skin, $value = 0, $image_popup = 0)
    {
        $image_popup_html = '
            <div class="mec-form-row mec-image-popup-wrap mec-switcher">
                <div class="mec-col-4">
                    <label for="mec_skin_'.$skin.'_image_popup">'.__('Display content\'s images as Popup', 'modern-events-calendar-lite').'</label>
                </div>
                <div class="mec-col-4">
                    <input type="hidden" name="mec[sk-options]['.$skin.'][image_popup]" value="0" />
                    <input type="checkbox" name="mec[sk-options]['.$skin.'][image_popup]" id="mec_skin_'.$skin.'_image_popup" value="1"
                ';
                if( $image_popup == 1 ) $image_popup_html .= 'checked="checked"'; 
                $image_popup_html .= '/><label for="mec_skin_'.$skin.'_image_popup"></label>
                </div>
            </div>
        ';
        return '<div class="mec-form-row mec-sed-method-wrap">
            <div class="mec-col-4">
                <label for="mec_skin_'.$skin.'_sed_method">'.__('Single Event Display Method', 'modern-events-calendar-lite').'</label>
            </div>
            <div class="mec-col-4">
                <input type="hidden" name="mec[sk-options]['.$skin.'][sed_method]" value="'.$value.'" id="mec_skin_'.$skin.'_sed_method_field" />
                <ul class="mec-sed-methods" data-for="#mec_skin_'.$skin.'_sed_method_field">
                    <li data-method="0" class="'.(!$value ? 'active' : '').'">'.__('Separate Window', 'modern-events-calendar-lite').'</li>
                    <li data-method="m1" class="'.($value === 'm1' ? 'active' : '').'">'.__('Modal 1', 'modern-events-calendar-lite').'</li>
                </ul>
            </div>
        </div>' . $image_popup_html;
    }

    /**
     * Disable Gutenberg Editor for MEC Post Types
     * @param boolean $status
     * @param string $post_type
     * @return bool
     */
    public function gutenberg($status, $post_type)
    {
        if(in_array($post_type, array($this->PT, $this->main->get_book_post_type(), $this->main->get_shortcode_post_type()))) return false;
        return $status;
    }
}
