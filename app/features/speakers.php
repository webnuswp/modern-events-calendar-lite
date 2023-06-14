<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC speakers class.
 * @author Webnus <info@webnus.net>
 */
class MEC_feature_speakers extends MEC_base
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
     * Initialize speakers feature
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        // Speakers Feature is Disabled
        if(!isset($this->settings['speakers_status']) or (isset($this->settings['speakers_status']) and !$this->settings['speakers_status'])) return;

        $this->factory->action('init', array($this, 'register_taxonomy'), 25);
        $this->factory->action('mec_speaker_edit_form_fields', array($this, 'edit_form'));
        $this->factory->action('mec_speaker_add_form_fields', array($this, 'add_form'));
        $this->factory->action('edited_mec_speaker', array($this, 'save_metadata'));
        $this->factory->action('created_mec_speaker', array($this, 'save_metadata'));

        $this->factory->action('wp_ajax_speaker_adding', array($this, 'fes_speaker_adding'));
        $this->factory->action('wp_ajax_nopriv_speaker_adding', array($this, 'fes_speaker_adding'));
        $this->factory->action('current_screen', array($this, 'show_notics'));

        $this->factory->filter('manage_edit-mec_speaker_columns', array($this, 'filter_columns'));
        $this->factory->filter('manage_mec_speaker_custom_column', array($this, 'filter_columns_content'), 10, 3);

        $this->factory->action('current_screen', array($this, 'update_speakers_list_admin'));
        $this->factory->action('mec_fes_form_footer', array($this, 'update_speakers_list'));

        $this->factory->action('wp_ajax_update_speakers_list', array($this, 'get_speakers'));
        $this->factory->action('wp_ajax_nopriv_update_speakers_list', array($this, 'get_speakers'));

        $this->factory->filter('post_edit_category_parent_dropdown_args', array($this, 'hide_parent_dropdown'));
    }
    
    /**
     * Registers speaker taxonomy
     * @author Webnus <info@webnus.net>
     */
    public function register_taxonomy()
    {
        $singular_label = $this->main->m('taxonomy_speaker', esc_html__('Speaker', 'modern-events-calendar-lite'));
        $plural_label = $this->main->m('taxonomy_speakers', esc_html__('Speakers', 'modern-events-calendar-lite'));

        register_taxonomy(
            'mec_speaker',
            $this->main->get_main_post_type(),
            array(
                'label'=>$plural_label,
                'labels'=>array(
                    'name'=>$plural_label,
                    'singular_name'=>$singular_label,
                    'all_items'=>sprintf(esc_html__('All %s', 'modern-events-calendar-lite'), $plural_label),
                    'edit_item'=>sprintf(esc_html__('Edit %s', 'modern-events-calendar-lite'), $singular_label),
                    'view_item'=>sprintf(esc_html__('View %s', 'modern-events-calendar-lite'), $singular_label),
                    'update_item'=>sprintf(esc_html__('Update %s', 'modern-events-calendar-lite'), $singular_label),
                    'add_new_item'=>sprintf(esc_html__('Add New %s', 'modern-events-calendar-lite'), $singular_label),
                    'new_item_name'=>sprintf(esc_html__('New %s Name', 'modern-events-calendar-lite'), $singular_label),
                    'popular_items'=>sprintf(esc_html__('Popular %s', 'modern-events-calendar-lite'), $plural_label),
                    'search_items'=>sprintf(esc_html__('Search %s', 'modern-events-calendar-lite'), $plural_label),
                    'back_to_items'=>sprintf(esc_html__('← Back to %s', 'modern-events-calendar-lite'), $plural_label),
                    'not_found'=>sprintf(esc_html__('no %s found.', 'modern-events-calendar-lite'), strtolower($plural_label)),
                ),
                'rewrite'=>array('slug'=>'events-speaker'),
                'public'=>false,
                'show_ui'=>true,
                'show_in_rest'=>true,
                'hierarchical'=>false,
                'meta_box_cb'=>(isset($_POST['_inline_edit']) ? '' : 'post_categories_meta_box'),
            )
        );
        
        register_taxonomy_for_object_type('mec_speaker', $this->main->get_main_post_type());
    }
    
    /**
     * Show edit form of speaker taxonomy
     * @author Webnus <info@webnus.net>
     * @param object $term
     */
    public function edit_form($term)
    {
        $job_title = get_metadata('term', $term->term_id, 'job_title', true);
        $tel = get_metadata('term', $term->term_id, 'tel', true);
        $email = get_metadata('term', $term->term_id, 'email', true);
        $website = get_metadata('term', $term->term_id, 'website', true);
        $facebook = get_metadata('term', $term->term_id, 'facebook', true);
        $instagram = get_metadata('term', $term->term_id, 'instagram', true);
        $linkedin = get_metadata('term', $term->term_id, 'linkedin', true);
        $twitter = get_metadata('term', $term->term_id, 'twitter', true);
        $thumbnail = get_metadata('term', $term->term_id, 'thumbnail', true);
    ?>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_job_title"><?php esc_html_e('Job Title', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <input type="text" placeholder="<?php esc_attr_e('Insert speaker job title.', 'modern-events-calendar-lite'); ?>" name="job_title" id="mec_job_title" value="<?php echo esc_attr($job_title); ?>" />
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_tel"><?php esc_html_e('Tel', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <input type="text" placeholder="<?php esc_attr_e('Insert speaker phone number.', 'modern-events-calendar-lite'); ?>" name="tel" id="mec_tel" value="<?php echo esc_attr($tel); ?>" />
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_email"><?php esc_html_e('Email', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <input type="text"  placeholder="<?php esc_attr_e('Insert speaker email address.', 'modern-events-calendar-lite'); ?>" name="email" id="mec_email" value="<?php echo esc_attr($email); ?>" />
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_website"><?php esc_html_e('Website', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <input type="text" placeholder="<?php esc_attr_e('Insert URL of Website', 'modern-events-calendar-lite'); ?>" name="website" id="mec_website" value="<?php echo esc_attr($website); ?>" />
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_facebook"><?php esc_html_e('Facebook Page', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <input type="text" placeholder="<?php esc_attr_e('Insert URL of Facebook Page', 'modern-events-calendar-lite'); ?>" name="facebook" id="mec_facebook" value="<?php echo esc_attr($facebook); ?>" />
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_instagram"><?php esc_html_e('Instagram', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <input type="text" placeholder="<?php esc_attr_e('Insert URL of Instagram', 'modern-events-calendar-lite'); ?>" name="instagram" id="mec_instagram" value="<?php echo esc_attr($instagram); ?>" />
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_linkedin"><?php esc_html_e('LinkedIn', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <input type="text" placeholder="<?php esc_attr_e('Insert URL of LinkedIn', 'modern-events-calendar-lite'); ?>" name="linkedin" id="mec_linkedin" value="<?php echo esc_attr($linkedin); ?>" />
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_twitter"><?php esc_html_e('Twitter Page', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <input type="text" placeholder="<?php esc_attr_e('Insert URL of Twitter Page', 'modern-events-calendar-lite'); ?>" name="twitter" id="mec_twitter" value="<?php echo esc_attr($twitter); ?>" />
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_thumbnail_button"><?php esc_html_e('Thumbnail', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <div id="mec_thumbnail_img"><?php if(trim($thumbnail) != '') echo '<img src="'.esc_url($thumbnail).'" />'; ?></div>
                <input type="hidden" name="thumbnail" id="mec_thumbnail" value="<?php echo esc_attr($thumbnail); ?>" />
                <button type="button" class="mec_upload_image_button button" id="mec_thumbnail_button"><?php echo esc_html__('Upload/Add image', 'modern-events-calendar-lite'); ?></button>
                <button type="button" class="mec_remove_image_button button <?php echo (!trim($thumbnail) ? 'mec-util-hidden' : ''); ?>"><?php echo esc_html__('Remove image', 'modern-events-calendar-lite'); ?></button>
            </td>
        </tr>
        <?php do_action('mec_edit_speaker_extra_fields', $term); ?>
    <?php
    }
    
    /**
     * Show add form of speaker taxonomy
     * @author Webnus <info@webnus.net>
     */
    public function add_form()
    {
    ?>
        <div class="form-field">
            <label for="mec_job_title"><?php esc_html_e('Job Title', 'modern-events-calendar-lite'); ?></label>
            <input type="text" name="job_title" placeholder="<?php esc_attr_e('Insert speaker job title.', 'modern-events-calendar-lite'); ?>" id="mec_job_title" value="" />
        </div>
        <div class="form-field">
            <label for="mec_tel"><?php esc_html_e('Tel', 'modern-events-calendar-lite'); ?></label>
            <input type="text" name="tel" placeholder="<?php esc_attr_e('Insert speaker phone number.', 'modern-events-calendar-lite'); ?>" id="mec_tel" value="" />
        </div>
        <div class="form-field">
            <label for="mec_email"><?php esc_html_e('Email', 'modern-events-calendar-lite'); ?></label>
            <input type="text" name="email" placeholder="<?php esc_attr_e('Insert speaker email address.', 'modern-events-calendar-lite'); ?>" id="mec_email" value="" />
        </div>
        <div class="form-field">
            <label for="mec_website"><?php esc_html_e('Website', 'modern-events-calendar-lite'); ?></label>
            <input type="text" name="website" placeholder="<?php esc_attr_e('Insert URL of Website', 'modern-events-calendar-lite'); ?>" id="mec_website" value="" />
        </div>
        <div class="form-field">
            <label for="mec_facebook"><?php esc_html_e('Facebook Page', 'modern-events-calendar-lite'); ?></label>
            <input type="text" name="facebook" placeholder="<?php esc_attr_e('Insert URL of Facebook Page', 'modern-events-calendar-lite'); ?>" id="mec_facebook" value="" />
        </div>
        <div class="form-field">
            <label for="mec_instagram"><?php esc_html_e('Instagram', 'modern-events-calendar-lite'); ?></label>
            <input type="text" name="instagram" placeholder="<?php esc_attr_e('Insert URL of Instagram', 'modern-events-calendar-lite'); ?>" id="mec_instagram" value="" />
        </div>
        <div class="form-field">
            <label for="mec_linkedin"><?php esc_html_e('LinkedIn', 'modern-events-calendar-lite'); ?></label>
            <input type="text" name="linkedin" placeholder="<?php esc_attr_e('Insert URL of linkedin', 'modern-events-calendar-lite'); ?>" id="mec_linkedin" value="" />
        </div>
        <div class="form-field">
            <label for="mec_twitter"><?php esc_html_e('Twitter Page', 'modern-events-calendar-lite'); ?></label>
            <input type="text" name="twitter" placeholder="<?php esc_attr_e('Insert URL of Twitter Page', 'modern-events-calendar-lite'); ?>" id="mec_twitter" value="" />
        </div>
        <div class="form-field">
            <label for="mec_thumbnail_button"><?php esc_html_e('Thumbnail', 'modern-events-calendar-lite'); ?></label>
            <div id="mec_thumbnail_img"></div>
            <input type="hidden" name="thumbnail" id="mec_thumbnail" value="" />
            <button type="button" class="mec_upload_image_button button" id="mec_thumbnail_button"><?php echo esc_html__('Upload/Add image', 'modern-events-calendar-lite'); ?></button>
            <button type="button" class="mec_remove_image_button button mec-util-hidden"><?php echo esc_html__('Remove image', 'modern-events-calendar-lite'); ?></button>
        </div>
        <?php do_action('mec_add_speaker_extra_fields'); ?>
    <?php
    }
    
    /**
     * Save meta data of speaker taxonomy
     * @author Webnus <info@webnus.net>
     * @param int $term_id
     */
    public function save_metadata($term_id)
    {
        // Quick Edit
        if(!isset($_POST['job_title'])) return;

        $job_title  = sanitize_text_field($_POST['job_title']);
        $tel        = isset($_POST['tel']) ? sanitize_text_field($_POST['tel']) : '';
        $email      = isset($_POST['email']) ? sanitize_text_field($_POST['email']) : '';
        $website    = (isset($_POST['website']) and trim($_POST['website'])) ? esc_url($_POST['website']) : '';
        $facebook   = (isset($_POST['facebook']) and trim($_POST['facebook'])) ? esc_url($_POST['facebook']) : '';
        $twitter    = (isset($_POST['twitter']) and trim($_POST['twitter'])) ? esc_url($_POST['twitter']) : '';
        $instagram  = (isset($_POST['instagram']) and trim($_POST['instagram'])) ? esc_url($_POST['instagram']) : '';
        $linkedin   = (isset($_POST['linkedin']) and trim($_POST['linkedin'])) ? esc_url($_POST['linkedin']) : '';
        $thumbnail  = isset($_POST['thumbnail']) ? sanitize_text_field($_POST['thumbnail']) : '';
        
        update_term_meta($term_id, 'job_title', $job_title);
        update_term_meta($term_id, 'tel', $tel);
        update_term_meta($term_id, 'email', $email);
        update_term_meta($term_id, 'website', $website);
        update_term_meta($term_id, 'facebook', $facebook);
        update_term_meta($term_id, 'twitter', $twitter);
        update_term_meta($term_id, 'instagram', $instagram);
        update_term_meta($term_id, 'linkedin', $linkedin);
        update_term_meta($term_id, 'thumbnail', $thumbnail);

        do_action('mec_save_speaker_extra_fields', $term_id);
    }
    
    /**
     * Filter columns of speaker taxonomy
     * @author Webnus <info@webnus.net>
     * @param array $columns
     * @return array
     */
    public function filter_columns($columns)
    {
        unset($columns['name']);
        unset($columns['slug']);
        unset($columns['description']);
        unset($columns['posts']);
        
        $columns['id'] = esc_html__('ID', 'modern-events-calendar-lite');
        $columns['name'] = $this->main->m('taxonomy_speaker', esc_html__('Speaker', 'modern-events-calendar-lite'));
        $columns['job_title'] = esc_html__('Job Title', 'modern-events-calendar-lite');
        $columns['tel'] = esc_html__('Tel', 'modern-events-calendar-lite');
        $columns['posts'] = esc_html__('Count', 'modern-events-calendar-lite');

        return apply_filters('speaker_filter_column', $columns);
    }
    
    /**
     * Filter content of speaker taxonomy columns
     * @author Webnus <info@webnus.net>
     * @param string $content
     * @param string $column_name
     * @param int $term_id
     * @return string
     */
    public function filter_columns_content($content, $column_name, $term_id)
    {
        switch($column_name)
        {
            case 'id':
                
                $content = $term_id;
                break;

            case 'tel':

                $content = get_metadata('term', $term_id, 'tel', true);

                break;

            case 'job_title':

                $content = get_metadata('term', $term_id, 'job_title', true);

                break;

            default:
                break;
        }

        return apply_filters('speaker_filter_column_content', $content, $column_name, $term_id);
    }

    /**
     * Adding new speaker
     * @author Webnus <info@webnus.net>
     * @return void
     */
    public function fes_speaker_adding()
    {
        $content = isset($_REQUEST['content']) ? sanitize_text_field($_REQUEST['content']) : NULL;
        $key = isset($_REQUEST['key']) ? sanitize_text_field($_REQUEST['key']) : NULL;

        $content = wp_strip_all_tags($content);
        $content = sanitize_text_field($content);
        $key = intval($key);

        if(!trim($content))
        {
            echo '<p class="mec-error" id="mec-speaker-error-' . esc_attr($key) . '">' . sprintf(esc_html__('Sorry, You must insert %s name!', 'modern-events-calendar-lite'), strtolower(\MEC\Base::get_main()->m('taxonomy_speaker', esc_html__('speaker', 'modern-events-calendar-lite')))) . '</p>';
            exit;
        }

        if(term_exists($content, 'mec_speaker'))
        {
            echo '<p class="mec-error" id="mec-speaker-error-' . esc_attr($key) . '">' . esc_html__("Sorry, {$content} already exists!", 'modern-events-calendar-lite') . '</p>';
            exit;
        }

        wp_insert_term(trim($content), 'mec_speaker');

        $speakers = '';
        $speaker_terms = get_terms(array('taxonomy'=>'mec_speaker', 'hide_empty'=>false));
        foreach($speaker_terms as $speaker_term)
        {
            $speakers .= '<label for="mec_fes_speakers'.esc_attr($speaker_term->term_id).'">
                <input type="checkbox" name="mec[speakers]['.esc_attr($speaker_term->term_id).']" id="mec_fes_speakers'.esc_attr($speaker_term->term_id).'" value="1">
                '.esc_html($speaker_term->name).'
            </label>';
        }

        echo MEC_kses::form($speakers);
        exit;
    }

    public function show_notics($screen)
    {
        if(isset($screen->id) and $screen->id == 'edit-mec_speaker')
        {
            add_action('admin_footer', function()
            {
                echo "<script>
                var xhrObject = window.XMLHttpRequest;
                function ajaxXHR()
                {
                    var xmlHttp = new xhrObject();
                    xmlHttp.addEventListener('readystatechange', function(xhr)
                    {
                        if(xmlHttp.readyState == 4 && xmlHttp.status == 200)
                        {
                            if(xhr.currentTarget.responseText.indexOf('tr') != -1)
                            {
                                jQuery('.form-wrap').find('.warning-msg').remove();
                                jQuery('.form-wrap').append('<div class=\"warning-msg\"><p>" . esc_html__('Note: You can use the speakers in your event edit/add page > hourly schedule section and speaker widget section!', 'modern-events-calendar-lite') . "</p></div>');
                            }
                        }
                    });
                    
                    return xmlHttp;
                }
                window.XMLHttpRequest = ajaxXHR;
                </script>";
            });
        }
    }

    public function update_speakers_list_admin($screen)
    {
        if(isset($screen->id) and $screen->id == 'mec-events' and isset($screen->base) and $screen->base == 'post')
        {
            add_action('admin_footer', array($this, 'update_speakers_list'));
        }
    }

    public function update_speakers_list()
    {
        echo "<script>
        jQuery('body').on('DOMSubtreeModified', 'ul.tagchecklist, #mec-fes-speakers-list', function()
        {
            jQuery.ajax(
            {
                url: '".admin_url('admin-ajax.php', NULL)."',
                type: 'POST',
                data: 'action=update_speakers_list',
                dataType: 'json'
            })
            .done(function(response)
            {
                for(var i = 0; i < response.speakers.length; i++)
                {
                    var speaker = response.speakers[i];
                    jQuery('.mec-hourly-schedule-form-speakers').each(function(index)
                    {
                        var d = jQuery(this).data('d');
                        var key = jQuery(this).data('key');
                        var name_prefix = jQuery(this).data('name-prefix');
                        
                        var name = name_prefix + '[hourly_schedules]['+d+'][schedules]['+key+'][speakers][]';
                        
                        // Add
                        if(!jQuery(this).find('input[value=\"'+speaker[0]+'\"]').length)
                        {
                            jQuery(this).append('<label><input type=\"checkbox\" name=\"'+name+'\" value=\"'+speaker[0]+'\">'+speaker[1]+'</label>');
                        }
                    });
                }
            });
        });
        </script>";
    }

    public function get_speakers()
    {
        $speakers = get_terms('mec_speaker', array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => '0',
        ));

        $sp = array();
        foreach($speakers as $speaker)
        {
            $sp[] = array($speaker->term_id, $speaker->name);
        }

        wp_send_json(array('speakers' => $sp));
    }

    public function hide_parent_dropdown($args)
    {
        if('mec_speaker' == $args['taxonomy']) $args['echo'] = false;
        return $args;
    }
}
