<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC factory class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_factory extends MEC_base
{
    public $main;
    public $file;
    public $folder;
    public $db;
    public $parser;

    /**
     * @static
     * @var array
     */
    public static $params = array();
    
    /**
     * Constructor method
     * @author Webnus <info@webnus.biz>
     */
    public function __construct()
    {
        if($this->getPRO())
        {
            // Load Vendors
            require_once MEC_ABSPATH.'app/vendor/autoload.php';
        }

        // MEC Main library
        $this->main = $this->getMain();
        
        // MEC File library
        $this->file = $this->getFile();
        
        // MEC Folder library
        $this->folder = $this->getFolder();
        
        // MEC DB library
        $this->db = $this->getDB();
        
        // MEC Parser library
        $this->parser = $this->getParser();
        
        // Import MEC Controller Class
        $this->import('app.controller');
    }
    
    /**
     * Register Webnus MEC actions
     * @author Webnus <info@webnus.biz>
     */
    public function load_actions()
    {
        // Register MEC function to be called in WordPress footer hook
        $this->action('wp_footer', array($this, 'load_footer'), 9999);
        
        // Parse WordPress query
        $this->action('parse_query', array($this->parser, 'WPQ_parse'), 99);
        
        // Add custom styles to header
        $this->action('wp_head', array($this->main, 'include_styles'), 9999);
        
        // MEC iCal export
        $this->action('init', array($this->main, 'ical'), 9999);

        // MEC iCal export in email
        $this->action('init', array($this->main, 'ical_email'), 999);

        // MEC Booking Invoice
        $this->action('init', array($this->main, 'booking_invoice'), 9999);
        
        // Redirect to MEC Dashboard
        $this->action('admin_init', array($this->main, 'mec_redirect_after_activate'));
        
        // Add Events to Tag Archive Page
        $this->action('pre_get_posts', array($this->main, 'add_events_to_tags_archive'));
        
        // MEC booking verification and cancellation
        $this->action('mec_before_main_content', array($this->main, 'do_endpoints'), 9999);
        
        // Add AJAX actions
        $this->action('wp_ajax_mec_save_styles', array($this->main, 'save_options'));
        $this->action('wp_ajax_mec_save_settings', array($this->main, 'save_options'));
        $this->action('wp_ajax_mec_save_reg_form', array($this->main, 'save_options'));
        $this->action('wp_ajax_mec_save_gateways', array($this->main, 'save_options'));
        $this->action('wp_ajax_mec_save_styling', array($this->main, 'save_options'));
        $this->action('wp_ajax_mec_save_notifications', array($this->main, 'save_notifications'));
        $this->action('wp_ajax_mec_save_messages', array($this->main, 'save_options'));
    }
    
    /**
     * Register Webnus MEC hooks such as activate, deactivate and uninstall hooks
     * @author Webnus <info@webnus.biz>
     */
    public function load_hooks()
    {
        register_activation_hook(MEC_ABSPATH.MEC_FILENAME, array($this, 'activate'));
		register_deactivation_hook(MEC_ABSPATH.MEC_FILENAME, array($this, 'deactivate'));
		register_uninstall_hook(MEC_ABSPATH.MEC_FILENAME, array('MEC_factory', 'uninstall'));
    }
    
    /**
     * load MEC filters
     * @author Webnus <info@webnus.biz>
     */
    public function load_filters()
    {
        // Load MEC Plugin links
        $this->filter('plugin_row_meta', array($this, 'load_plugin_links'), 10, 2);
        $this->filter('plugin_action_links_'.plugin_basename(MEC_DIRNAME.DS.MEC_FILENAME), array($this, 'load_plugin_action_links'), 10, 1);
        
        // Add MEC rewrite rules
        $this->filter('generate_rewrite_rules', array($this->parser, 'load_rewrites'));
        $this->filter('query_vars', array($this->parser, 'add_query_vars'));
        
        // Manage MEC templates
        $this->filter('template_include', array($this->parser, 'template'), 99);
        
        // Fetch Googlemap style JSON
        $this->filter('mec_get_googlemap_style', array($this->main, 'fetch_googlemap_style'));
        
        // Filter Request
        $this->filter('request', array($this->main, 'filter_request'));

        // Block Editor Category
        if(function_exists('register_block_type')) $this->filter('block_categories', array($this->main, 'add_custom_block_cateogry'), 9999);

        // Add Taxonomy etc to filters
        $this->filter('mec_vyear_atts', array($this->main, 'add_search_filters'));
        $this->filter('mec_vmonth_atts', array($this->main, 'add_search_filters'));
        $this->filter('mec_vweek_atts', array($this->main, 'add_search_filters'));
        $this->filter('mec_vday_atts', array($this->main, 'add_search_filters'));
        $this->filter('mec_vfull_atts', array($this->main, 'add_search_filters'));
        $this->filter('mec_vmap_atts', array($this->main, 'add_search_filters'));
        $this->filter('mec_vlist_atts', array($this->main, 'add_search_filters'));
        $this->filter('mec_vgrid_atts', array($this->main, 'add_search_filters'));
        $this->filter('mec_vtimetable_atts', array($this->main, 'add_search_filters'));
        $this->filter('mec_vmasonry_atts', array($this->main, 'add_search_filters'));
        $this->filter('mec_vagenda_atts', array($this->main, 'add_search_filters'));
        $this->filter('mce_buttons', array($this->main, 'add_mce_buttons'));
        $this->filter('mce_external_plugins', array($this->main, 'add_mce_external_plugins'));
    }
    
    /**
     * load MEC menus
     * @author Webnus <info@webnus.biz>
     */
    public function load_menus()
    {
        add_menu_page(__('M.E. Calendar', 'modern-events-calendar-lite'), __('M.E. Calendar', 'modern-events-calendar-lite'), 'edit_posts', 'mec-intro', array($this->main, 'dashboard'), plugin_dir_url(__FILE__ ) . '../../assets/img/mec.svg', 26);
    }

    /**
     * load MEC Features
     * @author Webnus <info@webnus.biz>
     */
    public function load_features()
    {
        $path = MEC_ABSPATH.'app'.DS.'features'.DS;
        $files = $this->folder->files($path, '.php$');
        
        foreach($files as $file)
        {
            $name = str_replace('.php', '', $file);
            
            $class = 'MEC_feature_'.$name;
            MEC::getInstance('app.features.'.$name, $class);
            
            if(!class_exists($class)) continue;
            
            $object = new $class();
            $object->init();
        }
    }
    
    /**
     * Inserting MEC plugin links
     * @author Webnus <info@webnus.biz>
     * @param array $links
     * @param string $file
     * @return array
     */
    public function load_plugin_links($links, $file)
    {
        if(strpos($file, MEC_DIRNAME) !== false)
        {
            if(!$this->getPRO())
            {
                $upgrade = '<a href="'.$this->main->get_pro_link().'" target="_blank"><b>'._x('Upgrade to Pro Version', 'plugin link', 'modern-events-calendar-lite').'</b></a>';
                $rate    =  '<a href="https://wordpress.org/support/plugin/modern-events-calendar-lite/reviews/#new-post" target="_blank">'._x('Rate the plugin ★★★★★', 'plugin rate', 'modern-events-calendar-lite').'</a>';
                $links[] = $upgrade;
                $links[] = $rate;
            }
        }
        
        return $links;
    }
    
    /**
     * Load MEC plugin action links
     * @author Webnus <info@webnus.biz>
     * @param array $links
     * @return array
     */
    public function load_plugin_action_links($links)
    {
        $settings = '<a href="'.$this->main->add_qs_vars(array('page'=>'MEC-settings'), $this->main->URL('admin').'admin.php').'">'._x('Settings', 'plugin link', 'modern-events-calendar-lite').'</a>';
        array_unshift($links, $settings);

        if(!$this->getPRO())
        {
            $upgrade = '<a href="'.$this->main->get_pro_link().'" target="_blank"><b>'._x('Upgrade', 'plugin link', 'modern-events-calendar-lite').'</b></a>';
            array_unshift($links, $upgrade);
        }
        
        return $links;
    }
	
    /**
     * Load MEC Backend assets such as CSS or JavaScript files
     * @author Webnus <info@webnus.biz>
     */
    public function load_backend_assets()
    {
        // Include WordPress jQuery
        wp_enqueue_script('jquery');

        // Include WordPress color picker JavaScript file
        wp_enqueue_script('wp-color-picker');

        // Include MEC typekit script file
        wp_enqueue_script('mec-typekit-script', $this->main->asset('js/jquery.typewatch.js'));
        
        //Include the nice-select
        wp_enqueue_script('mec-niceselect-script', $this->main->asset('js/jquery.nice-select.min.js'));

        //Include Select2
        wp_enqueue_script('mec-select2-script', $this->main->asset('packages/select2/select2.full.min.js'));
        wp_enqueue_style('mec-select2-style', $this->main->asset('packages/select2/select2.min.css'));

        // Backend Dependencies
        $dependencies = array('wp-color-picker', 'jquery-ui-datepicker');

        // Get Current Screen
        global $current_screen;
        if(!isset($current_screen)) $current_screen = get_current_screen();

        // Add WP Blocks to the dependencies only when needed!
        if(method_exists($current_screen, 'is_block_editor') and $current_screen->is_block_editor()) $dependencies[] = 'wp-blocks';

        // Include MEC backend script file
        wp_enqueue_script('mec-backend-script', $this->main->asset('js/backend.js'), $dependencies);

        // Register New Block Editor 
        if(function_exists('register_block_type')) register_block_type('mec/blockeditor', array('editor_script' => 'block.editor'));

        wp_localize_script( 'mec-backend-script', 'mec_admin_localize', 
            array( 
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'ajax_nonce' => wp_create_nonce('mec_settings_nonce'),
                'mce_items' => $this->main->mce_get_shortcode_list(),
            )
        );

        wp_enqueue_script('mec-events-script', $this->main->asset('js/events.js'));

        // Thickbox
        wp_enqueue_media();
    
        // Include WordPress color picker CSS file
        wp_enqueue_style('wp-color-picker');
        
        // Include MEC backend CSS files
        wp_enqueue_style('mec-font-icon', $this->main->asset('css/iconfonts.css'));
        wp_enqueue_style('mec-backend-style', $this->main->asset('css/backend.min.css'));
        
        // Include "Right to Left" CSS file
        if(is_rtl()) wp_enqueue_style('mec-backend-rtl-style', $this->main->asset('css/mecrtl.min.css'));
    }
    
    /**
     * Load MEC frontend assets such as CSS or JavaScript files
     * @author Webnus <info@webnus.biz>
     */
    public function load_frontend_assets()
    {
        // Current locale
        $locale = $this->main->get_current_language();

        // Styling
        $styling = $this->main->get_styling();

        // Google Fonts Status
        $gfonts_status = (isset($styling['disable_gfonts']) and $styling['disable_gfonts']) ? false : true;

        
        
        
        // Include WordPress jQuery
        wp_enqueue_script('jquery');
        
        // Include jQuery date picker
        wp_enqueue_script('jquery-ui-datepicker');
        
        // Load Isotope

        if(class_exists('ET_Builder_Element')) $this->main->load_isotope_assets();
        include_once(ABSPATH.'wp-admin/includes/plugin.php');
        if(is_plugin_active( 'elementor/elementor.php' ) && \Elementor\Plugin::$instance->preview->is_preview_mode()) $this->main->load_isotope_assets();
        
        wp_enqueue_script('mec-typekit-script', $this->main->asset('js/jquery.typewatch.js'));
        wp_enqueue_script('mec-featherlight-script', $this->main->asset('packages/featherlight/featherlight.js'));

        //Include Select2
        wp_enqueue_script('mec-select2-script', $this->main->asset('packages/select2/select2.full.min.js'));
        wp_enqueue_style('mec-select2-style', $this->main->asset('packages/select2/select2.min.css'));

        // Include MEC frontend script files
        wp_enqueue_script('mec-frontend-script', $this->main->asset('js/frontend.js'));
        wp_enqueue_script('mec-tooltip-script', $this->main->asset('packages/tooltip/tooltip.js'));

        wp_enqueue_script('mec-events-script', $this->main->asset('js/events.js'));
        
        // Include Lity Lightbox
        wp_enqueue_script('mec-lity-script', $this->main->asset('packages/lity/lity.min.js'));

        // Include color brightness
        wp_enqueue_script('mec-colorbrightness-script', $this->main->asset('packages/colorbrightness/colorbrightness.min.js'));
        
        // Include MEC frontend JS libraries
        wp_enqueue_script('mec-owl-carousel-script', $this->main->asset('packages/owl-carousel/owl.carousel.min.js'));

        if ( did_action( 'elementor/loaded' ) ) {
            $elementor_edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode() == false ? 'no' : 'yes';
        } else {
            $elementor_edit_mode = 'no';
        }
        
         // Settings
         $settings = $this->main->get_settings();
         $grecaptcha_key = isset($settings['google_recaptcha_sitekey']) ? trim($settings['google_recaptcha_sitekey']) : '';

        // Localize Some Strings
        wp_localize_script('mec-frontend-script', 'mecdata', array
        (
            'day'=>__('day', 'modern-events-calendar-lite'),
            'days'=>__('days', 'modern-events-calendar-lite'),
            'hour'=>__('hour', 'modern-events-calendar-lite'),
            'hours'=>__('hours', 'modern-events-calendar-lite'),
            'minute'=>__('minute', 'modern-events-calendar-lite'),
            'minutes'=>__('minutes', 'modern-events-calendar-lite'),
            'second'=>__('second', 'modern-events-calendar-lite'),
            'seconds'=>__('seconds', 'modern-events-calendar-lite'),
            'elementor_edit_mode'=>$elementor_edit_mode,
            'recapcha_key'=>$grecaptcha_key,
            'ajax_url' => admin_url('admin-ajax.php'),
            'fes_nonce' => wp_create_nonce('mec_fes_nonce'),
        ));
        
        // Include Google Recaptcha Javascript API
        $grecaptcha_include = apply_filters('mec_grecaptcha_include', true);
        if($grecaptcha_include) wp_enqueue_script('recaptcha', '//www.google.com/recaptcha/api.js?hl='.str_replace('_', '-', $locale));
        
        // Include MEC frontend CSS files
        wp_enqueue_style('mec-font-icons', $this->main->asset('css/iconfonts.css'));
        wp_enqueue_style('mec-frontend-style', $this->main->asset('css/frontend.min.css'));
        wp_enqueue_style('mec-tooltip-style', $this->main->asset('packages/tooltip/tooltip.css'));
        wp_enqueue_style('mec-tooltip-shadow-style', $this->main->asset('packages/tooltip/tooltipster-sideTip-shadow.min.css'));
        wp_enqueue_style('mec-featherlight-style', $this->main->asset('packages/featherlight/featherlight.css'));
        
        // Include "Right to Left" CSS file
        if(is_rtl()) wp_enqueue_style('mec-frontend-rtl-style', $this->main->asset('css/mecrtl.min.css'));
		
		// Include Google Fonts
		if($gfonts_status) wp_enqueue_style('mec-google-fonts', '//fonts.googleapis.com/css?family=Montserrat:400,700|Roboto:100,300,400,700');
		
		// Include Dynamic CSS
        if(get_option('mec_dyncss') == true)
        {
        	wp_enqueue_style('mec-dynamic-styles', $this->main->asset('css/dyncss.css'));
        	wp_add_inline_style('mec-dynamic-styles', get_option('mec_dyncss'));
        }
        
        // Include Google Font
        if($gfonts_status and get_option('mec_gfont')) wp_enqueue_style('mec-custom-google-font', get_option('mec_gfont'), array(), NULL);
        
        // Include Lity CSS file
        wp_enqueue_style('mec-lity-style', $this->main->asset('packages/lity/lity.min.css'));
    }
   	
    /**
     * Load MEC widget
     * @author Webnus <info@webnus.biz>
     */
    public function load_widgets()
    {
        // register mec side bar
        register_sidebar(array(
            'id' => 'mec-single-sidebar',
            'name' => __('MEC Single Sidebar', 'modern-events-calendar-lite'),
            'description' => __('Custom sidebar for single and modal page of MEC.', 'modern-events-calendar-lite'),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h4 class="widget-title">',
            'after_title' => '</h4>'
        ));

        // Import MEC Widget Class
        $this->import('app.widgets.MEC');
        $this->import('app.widgets.single');

        register_widget('MEC_MEC_widget');
        register_widget('MEC_single_widget');
    }
    
    /**
     * Register MEC shortcode in WordPress
     * @author Webnus <info@webnus.biz>
     */
    public function load_shortcodes()
    {
        // MEC Render library
        $render = $this->getRender();
        
        // Events Archive Page
        $this->shortcode('MEC', array($render, 'shortcode'));
        
        // Event Single Page
        $this->shortcode('MEC_single', array($render, 'vsingle'));

        // MEC Render library
        $book = $this->getBook();

        // Booking Invoice
        $this->shortcode('MEC_invoice_link', array($book, 'invoice_link_shortcode'));
    }
    
    /**
     * Load dynamic css
     * @author Webnus <info@webnus.biz>
     */
    public function mec_dyncss()
    {
        // Import Dynamic CSS codes
        $path = $this->import('app.features.mec.dyncss', true, true);
        
        ob_start();
        include $path;
        echo $output = ob_get_clean();
    }
    
    /**
     * Load MEC skins in WordPress
     * @author Webnus <info@webnus.biz>
     */
    public function load_skins()
    {
        // Import MEC skins Class
        $this->import('app.libraries.skins');
        
        $MEC_skins = new MEC_skins();
        $MEC_skins->load();
    }
    
    /**
     * Register MEC addons in WordPress
     * @author Webnus <info@webnus.biz>
     */
    public function load_addons()
    {
        // Import MEC VC addon Class
        $this->import('app.addons.VC');
        
        $MEC_addon_VC = new MEC_addon_VC();
        $MEC_addon_VC->init();

        // Import MEC KC addon Class
        $this->import('app.addons.KC');

        $MEC_addon_KC = new MEC_addon_KC();
        $MEC_addon_KC->init();

        // Import MEC Elementor addon Class
        $this->import('app.addons.elementor');
        $MEC_addon_elementor = new MEC_addon_elementor();
        $MEC_addon_elementor->init();

        // Import MEC Divi addon Class
        $this->import('app.addons.divi');
        $MEC_addon_divi = new MEC_addon_divi();
        $MEC_addon_divi->init();
    }
    
    /**
     * Initialize MEC Auto Update Feature
     * @author Webnus <info@webnus.biz>
     */
    public function load_auto_update()
    {
        // Import MEC Envato Class
        $envato = MEC::getInstance('app.libraries.envato');

        // Initialize the service
        $envato->init();
    }
    
    /**
     * Add strings (CSS, JavaScript, etc.) to website sections such as footer etc.
     * @author Webnus <info@webnus.biz>
     * @param string $key
     * @param string $string
     * @return boolean
     */
    public function params($key = 'footer', $string)
	{
		$string = (string) $string;
		if(trim($string) == '') return false;
		
        // Register the key for removing PHP notices
        if(!isset(self::$params[$key])) self::$params[$key] = array();
        
        // Add it to the MEC params
        array_push(self::$params[$key], $string);
        return true;
	}
    
    /**
     * Insert MEC assets into the website footer
     * @author Webnus <info@webnus.biz>
     * @return void
     */
    public function load_footer()
    {
		if(!isset(self::$params['footer']) or (isset(self::$params['footer']) and !count(self::$params['footer']))) return;
        
        // Remove duplicate strings
        $strings = array_unique(self::$params['footer']);
        
        // Print the assets in the footer
        foreach($strings as $string) echo PHP_EOL.$string.PHP_EOL;
    }
    
    /**
     * Add MEC actions to WordPress
     * @author Webnus <info@webnus.biz>
     * @param string $hook
     * @param string|array $function
     * @param int $priority
     * @param int $accepted_args
     * @return boolean
     */
    public function action($hook, $function, $priority = 10, $accepted_args = 1)
    {
        // Check Parameters
        if(!trim($hook) or !$function) return false;
        
        // Add it to WordPress actions
        return add_action($hook, $function, $priority, $accepted_args);
    }
    
    /**
     * Add MEC filters to WordPress filters
     * @author Webnus <info@webnus.biz>
     * @param string $tag
     * @param string|array $function
     * @param int $priority
     * @param int $accepted_args
     * @return boolean
     */
    public function filter($tag, $function, $priority = 10, $accepted_args = 1)
    {
        // Check Parameters
        if(!trim($tag) or !$function) return false;
        
        // Add it to WordPress filters
        return add_filter($tag, $function, $priority, $accepted_args);
    }
    
    /**
     * Add MEC shortcodes to WordPress shortcodes
     * @author Webnus <info@webnus.biz>
     * @param string $shortcode
     * @param string|array $function
     * @return boolean
     */
    public function shortcode($shortcode, $function)
    {
        // Check Parameters
        if(!trim($shortcode) or !$function) return false;
        
        // Add it to WordPress shortcodes
        add_shortcode($shortcode, $function);
        return true;
    }
    
    /**
     * Runs on plugin activation
     * @author Webnus <info@webnus.biz>
     * @param boolean $network
     * @return boolean
     */
    public function activate($network = false)
	{
        // Redirect user to MEC Dashboard
        add_option('mec_activation_redirect', true);
        
        $current_blog_id = get_current_blog_id();
        
        // Plugin activated only for one blog
        if(!function_exists('is_multisite') or (function_exists('is_multisite') and !is_multisite())) $network = false;
        if(!$network)
        {
            // Refresh WordPress rewrite rules
            $this->main->flush_rewrite_rules();
            
            return $this->install($current_blog_id);
        }

        // Plugin activated for all blogs
        $blogs = $this->db->select("SELECT `blog_id` FROM `#__blogs`", 'loadColumn');
        foreach($blogs as $blog_id)
        {
            switch_to_blog($blog_id);
            $this->install($blog_id);
        }

        switch_to_blog($current_blog_id);
        
        // Refresh WordPress rewrite rules
        $this->main->flush_rewrite_rules();
        return true;
	}
    
    /**
     * Runs on plugin deactivation
     * @author Webnus <info@webnus.biz>
     * @param boolean $network
     * @return void
     */
    public function deactivate($network = false)
	{
        $this->main->flush_rewrite_rules();

        // Clear Scheduler Cronjob
        wp_clear_scheduled_hook('mec_scheduler');
        wp_clear_scheduled_hook('mec_syncScheduler');
	}
    
    /**
     * Runs on plugin uninstallation
     * @author Webnus <info@webnus.biz>
     * @return boolean
     */
    public static function uninstall()
	{
        // Main Object
        $main = MEC::getInstance('app.libraries.main');
        
        // Database Object
        $db = MEC::getInstance('app.libraries.db');
        
        // Refresh WordPress rewrite rules
        $main->flush_rewrite_rules();
        
        // Getting current blog
        $current_blog_id = get_current_blog_id();
        
        if(!function_exists('is_multisite') or (function_exists('is_multisite') and !is_multisite())) return self::purge($current_blog_id);

        // Plugin activated for all blogs
        $blogs = $db->select("SELECT `blog_id` FROM `#__blogs`", 'loadColumn');
        foreach($blogs as $blog_id)
        {
            switch_to_blog($blog_id);
            self::purge($blog_id);
        }
        
        // Switch back to current blog
        switch_to_blog($current_blog_id);
        return true;
	}
    
    /**
     * Install the plugin on s certain blog
     * @author Webnus <info@webnus.biz>
     * @param int $blog_id
     */
    public function install($blog_id = 1)
    {
        // Plugin installed before
        if(get_option('mec_installed', 0))
        {
            // Create mec_events table if it's removed for any reason
            $this->main->create_mec_tables();
            
            return;
        }
        
        // Run Queries
        $query_file = MEC_ABSPATH. 'assets' .DS. 'sql' .DS. 'install.sql';
		if($this->file->exists($query_file))
		{
			$queries = $this->file->read($query_file);
            $sqls = explode(';', $queries);
			
            foreach($sqls as $sql)
            {
                $sql = trim($sql, '; ');
                if(trim($sql) == '') continue;
                
                $sql .= ';';
                
                try
                {
                    $this->db->q($sql);
                }
                catch (Exception $e){}
            }
		}
        
        // Default Options
        $options = array
        (
            'settings'=>array
            (
                'multiple_day_show_method'=>'first_day_listgrid',
                'google_maps_status'=>1,
                'export_module_status'=>1,
                'sn'=>array('googlecal'=>1, 'ical'=>1, 'facebook'=>1, 'gplus'=>1, 'twitter'=>1, 'linkedin'=>1, 'email'=>1),
                'countdown_status'=>1,
                'social_network_status'=>1,
            ),
            'styles'=>array('CSS'=>''),
            'gateways'=>array(1=>array('status'=>1)),
            'notifications'=>array
            (
                'booking_notification'=>array
                (
                    'subject'=>'Your booking is received.',
                    'recipients'=>'',
                    'content'=>"Hello %%name%%,

                    Your booking is received. We will check and confirm your booking as soon as possible.
                    Thanks for your patience.

                    Regards,
                    %%blog_name%%"
                ),
                'email_verification'=>array
                (
                    'subject'=>'Please verify your booking.',
                    'recipients'=>'',
                    'content'=>"Hi %%name%%,

                    Please verify your booking by clicking on following link:

                    %%verification_link%%

                    Regards,
                    %%blog_name%%"
                ),
                'booking_confirmation'=>array
                (
                    'subject'=>'Your booking is confirmed.',
                    'recipients'=>'',
                    'content'=>"Hi %%name%%,

                    Your booking is confirmed. You should be available at %%book_date%% in %%event_location_address%%.

                    You can contact to event organizer by calling %%event_organizer_tel%%.

                    Regards,
                    %%blog_name%%"
                ),
                'cancellation_notification'=>array
                (
                    'status'=>'0',
                    'subject'=>'Your booking is canceled.',
                    'recipients'=>'',
                    'send_to_admin'=>'1',
                    'send_to_organizer'=>'0',
                    'send_to_user'=>'0',
                    'content'=>"Hi %%name%%,

                    For your information, your booking for %%event_title%% at %%book_date%% is canceled.

                    Regards,
                    %%blog_name%%"
                ),
                'admin_notification'=>array
                (
                    'subject'=>'A new booking is received.',
                    'recipients'=>'',
                    'content'=>"Dear Admin,

                    A new booking is received. Please check and confirm it as soon as possible.

                    %%admin_link%%
                    
                    %%attendees_full_info%%

                    Regards,
                    %%blog_name%%"
                ),
                'new_event'=>array
                (
                    'status'=>'1',
                    'subject'=>'A new event is added.',
                    'recipients'=>'',
                    'content'=>"Hello,

                    A new event just added. The event title is %%event_title%% and its status is %%event_status%%.
                    The new event may need to be published. Please use this link for managing your website events: %%admin_link%%

                    Regards,
                    %%blog_name%%"
                ),
                'user_event_publishing'=>array
                (
                    'status'=>'1',
                    'subject'=>'Your event gets published',
                    'recipients'=>'',
                    'content'=>"Hello %%name%%,

                    Your event gets published. You can check it below:

                    <a href=\"%%event_link%%\">%%event_title%%</a>

                    Regards,
                    %%blog_name%%"
                )
            ),
        );
        
        add_option('mec_options', $options);
        
        // Event Dummy Events or Not
        if(apply_filters('mec_activation_import_events', true))
        {
            // Create Default Events
            $events = array
            (
                array('title'=>'One Time Multiple Day Event', 'start'=>date('Y-m-d', strtotime('+5 days')), 'end'=>date('Y-m-d', strtotime('+7 days')), 'finish'=>date('Y-m-d', strtotime('+7 days')), 'repeat_type'=>'', 'repeat_status'=>0, 'interval'=>NULL, 'meta'=>array('mec_color'=>'dd823b')),
                array('title'=>'Daily each 3 days', 'start'=>date('Y-m-d'), 'end'=>date('Y-m-d'), 'repeat_type'=>'daily', 'repeat_status'=>1, 'interval'=>3, 'meta'=>array('mec_color'=>'a3b745')),
                array('title'=>'Weekly on Mondays', 'start'=>date('Y-m-d', strtotime('Next Monday')), 'end'=>date('Y-m-d', strtotime('Next Monday')), 'repeat_type'=>'weekly', 'repeat_status'=>1, 'interval'=>7, 'meta'=>array('mec_color'=>'e14d43')),
                array('title'=>'Monthly on 27th', 'start'=>date('Y-m-27'), 'end'=>date('Y-m-27'), 'repeat_type'=>'monthly', 'repeat_status'=>1, 'interval'=>NULL, 'year'=>'*', 'month'=>'*', 'day'=>',27,', 'week'=>'*', 'weekday'=>'*', 'meta'=>array('mec_color'=>'00a0d2')),
                array('title'=>'Yearly on August 20th and 21st', 'start'=>date('Y-08-20'), 'end'=>date('Y-08-21'), 'repeat_type'=>'yearly', 'repeat_status'=>1, 'interval'=>NULL, 'year'=>'*', 'month'=>',08,', 'day'=>',20,21,', 'week'=>'*', 'weekday'=>'*', 'meta'=>array('mec_color'=>'fdd700')),
            );

            // Import Events
            $this->main->save_events($events);
        }
        
        // Event Dummy Shortcodes or Not
        if(apply_filters('mec_activation_import_shortcodes', true))
        {
            // Search Form Options
            $sf_options = array('category'=>array('type'=>'dropdown'), 'text_search'=>array('type'=>'text_input'));

            // Create Default Calendars
            $calendars = array
            (
                array('title'=>'Full Calendar', 'meta'=>array('skin'=>'full_calendar', 'show_past_events'=>1, 'sk-options'=>array('full_calendar'=>array('start_date_type'=>'today', 'default_view'=>'list', 'monthly'=>1, 'weekly'=>1, 'daily'=>1, 'list'=>1)), 'sf-options'=>array('full_calendar'=>array('month_filter'=>array('type'=>'dropdown'), 'text_search'=>array('type'=>'text_input'))), 'sf_status'=>1)),
                array('title'=>'Monthly View', 'meta'=>array('skin'=>'monthly_view', 'show_past_events'=>1, 'sk-options'=>array('monthly_view'=>array('start_date_type'=>'start_current_month', 'next_previous_button'=>1)), 'sf-options'=>array('monthly_view'=>$sf_options), 'sf_status'=>1)),
                array('title'=>'Weekly View', 'meta'=>array('skin'=>'weekly_view', 'show_past_events'=>1, 'sk-options'=>array('weekly_view'=>array('start_date_type'=>'start_current_month', 'next_previous_button'=>1)), 'sf-options'=>array('weekly_view'=>$sf_options), 'sf_status'=>1)),
                array('title'=>'Daily View', 'meta'=>array('skin'=>'daily_view', 'show_past_events'=>1, 'sk-options'=>array('daily_view'=>array('start_date_type'=>'start_current_month', 'next_previous_button'=>1)), 'sf-options'=>array('daily_view'=>$sf_options), 'sf_status'=>1)),
                array('title'=>'Map View', 'meta'=>array('skin'=>'map', 'show_past_events'=>1, 'sk-options'=>array('map'=>array('limit'=>200)), 'sf-options'=>array('map'=>$sf_options), 'sf_status'=>1)),
                array('title'=>'Upcoming events (List)', 'meta'=>array('skin'=>'list', 'show_past_events'=>0, 'sk-options'=>array('list'=>array('load_more_button'=>1)), 'sf-options'=>array('list'=>$sf_options), 'sf_status'=>1)),
                array('title'=>'Upcoming events (Grid)', 'meta'=>array('skin'=>'grid', 'show_past_events'=>0, 'sk-options'=>array('grid'=>array('load_more_button'=>1)), 'sf-options'=>array('grid'=>$sf_options), 'sf_status'=>1)),
                array('title'=>'Carousel View', 'meta'=>array('skin'=>'carousel', 'show_past_events'=>0, 'sk-options'=>array('carousel'=>array('count'=>3, 'limit'=>12)), 'sf-options'=>array('carousel'=>$sf_options), 'sf_status'=>0)),
                array('title'=>'Countdown View', 'meta'=>array('skin'=>'countdown', 'show_past_events'=>0, 'sk-options'=>array('countdown'=>array('style'=>'style3', 'event_id'=>'-1')), 'sf-options'=>array('countdown'=>$sf_options), 'sf_status'=>0)),
                array('title'=>'Slider View', 'meta'=>array('skin'=>'slider', 'show_past_events'=>0, 'sk-options'=>array('slider'=>array('style'=>'t1', 'limit'=>6, 'autoplay'=>3000)), 'sf-options'=>array('slider'=>$sf_options), 'sf_status'=>0)),
                array('title'=>'Masonry View', 'meta'=>array('skin'=>'masonry', 'show_past_events'=>0, 'sk-options'=>array('masonry'=>array('limit'=>24, 'filter_by'=>'category')), 'sf-options'=>array('masonry'=>$sf_options), 'sf_status'=>0)),
            );

            foreach($calendars as $calendar)
            {
                // Calendar exists
                if(post_exists($calendar['title'], 'MEC')) continue;

                $post = array('post_title'=>$calendar['title'], 'post_content'=>'MEC', 'post_type'=>'mec_calendars', 'post_status'=>'publish');
                $post_id = wp_insert_post($post);

                update_post_meta($post_id, 'label', '');
                update_post_meta($post_id, 'category', '');
                update_post_meta($post_id, 'location', '');
                update_post_meta($post_id, 'organizer', '');
                update_post_meta($post_id, 'tag', '');
                update_post_meta($post_id, 'author', '');

                foreach($calendar['meta'] as $key=>$value) update_post_meta($post_id, $key, $value);
            }
        }

        // Scheduler Cron job
        if(!wp_next_scheduled('mec_scheduler')) wp_schedule_event(time(), 'hourly', 'mec_scheduler');
        if(!wp_next_scheduled('mec_syncScheduler')) wp_schedule_event(time(), 'daily', 'mec_syncScheduler');
        
        // Mark this blog as installed
        update_option('mec_installed', 1);
        
        // Set the version into the Database
        update_option('mec_version', $this->main->get_version());
    }
    
    /**
     * Remove MEC from a blog
     * @author Webnus <info@webnus.biz>
     * @param int $blog_id
     */
    public static function purge($blog_id = 1)
    {
        // Database Object
        $main = MEC::getInstance('app.libraries.main');
        
        // Settings
        $settings = $main->get_settings();
        
        if(isset($settings['remove_data_on_uninstall']) and $settings['remove_data_on_uninstall'])
        {
            // Database Object
            $db = MEC::getInstance('app.libraries.db');

            // Drop Tables
            $db->q("DROP TABLE `#__mec_events`");
            $db->q("DROP TABLE `#__mec_dates`");

            // MEC Deleted
            delete_option('mec_installed');
            delete_option('mec_options');
            delete_option('mec_version');
        }
    }
}