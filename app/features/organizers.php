<?php
/** no direct access **/

use MEC\FES\FormBuilder;

defined('MECEXEC') or die();

/**
 * Webnus MEC organizers class.
 * @author Webnus <info@webnus.net>
 */
class MEC_feature_organizers extends MEC_base
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
     * Initialize organizers feature
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        $this->factory->action('init', array($this, 'register_taxonomy'), 25);
        $this->factory->action('mec_organizer_edit_form_fields', array($this, 'edit_form'));
        $this->factory->action('mec_organizer_add_form_fields', array($this, 'add_form'));
        $this->factory->action('edited_mec_organizer', array($this, 'save_metadata'));
        $this->factory->action('created_mec_organizer', array($this, 'save_metadata'));

        $this->factory->action('mec_metabox_details', array($this, 'meta_box_organizer'), 40);
        if(!isset($this->settings['fes_section_organizer']) or (isset($this->settings['fes_section_organizer']) and $this->settings['fes_section_organizer'])) $this->factory->action('mec_fes_metabox_details', array($this, 'meta_box_organizer'), 31);

        $this->factory->filter('manage_edit-mec_organizer_columns', array($this, 'filter_columns'));
        $this->factory->filter('manage_mec_organizer_custom_column', array($this, 'filter_columns_content'), 10, 3);

        $this->factory->action('save_post', array($this, 'save_event'), 2);
    }

    /**
     * Registers organizer taxonomy
     * @author Webnus <info@webnus.net>
     */
    public function register_taxonomy()
    {
        $singular_label = $this->main->m('taxonomy_organizer', esc_html__('Organizer', 'modern-events-calendar-lite'));
        $plural_label = $this->main->m('taxonomy_organizers', esc_html__('Organizers', 'modern-events-calendar-lite'));

        register_taxonomy(
            'mec_organizer',
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
                'rewrite'=>array('slug'=>'events-organizer'),
                'public'=>false,
                'show_ui'=>true,
                'hierarchical'=>false,
            )
        );

        register_taxonomy_for_object_type('mec_organizer', $this->main->get_main_post_type());
    }

    /**
     * Show edit form of organizer taxonomy
     * @author Webnus <info@webnus.net>
     * @param object $term
     */
    public function edit_form($term)
    {
        $tel = get_metadata('term', $term->term_id, 'tel', true);
        $email = get_metadata('term', $term->term_id, 'email', true);
        $url = get_metadata('term', $term->term_id, 'url', true);
        $page_label = get_metadata('term', $term->term_id, 'page_label', true);
        $facebook = get_metadata('term', $term->term_id, 'facebook', true);
        $instagram = get_metadata('term', $term->term_id, 'instagram', true);
        $linkedin = get_metadata('term', $term->term_id, 'linkedin', true);
        $twitter = get_metadata('term', $term->term_id, 'twitter', true);
        $thumbnail = get_metadata('term', $term->term_id, 'thumbnail', true);
    ?>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_tel"><?php esc_html_e('Tel', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <input type="text" placeholder="<?php esc_attr_e('Insert organizer phone number.', 'modern-events-calendar-lite'); ?>" name="tel" id="mec_tel" value="<?php echo esc_attr($tel); ?>" />
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_email"><?php esc_html_e('Email', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <input type="text"  placeholder="<?php esc_attr_e('Insert organizer email address.', 'modern-events-calendar-lite'); ?>" name="email" id="mec_email" value="<?php echo esc_attr($email); ?>" />
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_url"><?php esc_html_e('Page URL', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <input type="url" placeholder="<?php esc_attr_e('Use this field to link organizer to other user profile pages', 'modern-events-calendar-lite'); ?>" name="url" id="mec_url" value="<?php echo esc_attr($url); ?>" />
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_page_label"><?php esc_html_e('Page Label', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <input type="text" placeholder="<?php esc_attr_e('Site name or any text', 'modern-events-calendar-lite'); ?>" name="page_label" id="mec_page_label" value="<?php echo esc_attr($page_label); ?>" />
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
        <?php do_action('mec_edit_organizer_extra_fields', $term); ?>
    <?php
    }

    /**
     * Show add form of organizer taxonomy
     * @author Webnus <info@webnus.net>
     */
    public function add_form()
    {
    ?>
        <div class="form-field">
            <label for="mec_tel"><?php esc_html_e('Tel', 'modern-events-calendar-lite'); ?></label>
            <input type="text" name="tel" placeholder="<?php esc_attr_e('Insert organizer phone number.', 'modern-events-calendar-lite'); ?>" id="mec_tel" value="" />
        </div>
        <div class="form-field">
            <label for="mec_email"><?php esc_html_e('Email', 'modern-events-calendar-lite'); ?></label>
            <input type="text" name="email" placeholder="<?php esc_attr_e('Insert organizer email address.', 'modern-events-calendar-lite'); ?>" id="mec_email" value="" />
        </div>
        <div class="form-field">
            <label for="mec_url"><?php esc_html_e('Page URL', 'modern-events-calendar-lite'); ?></label>
            <input type="url" name="url" placeholder="<?php esc_attr_e('Use this field to link organizer to other user profile pages', 'modern-events-calendar-lite'); ?>" id="mec_url" value="" />
        </div>
        <div class="form-field">
            <label for="mec_page_label"><?php esc_html_e('Page Label', 'modern-events-calendar-lite'); ?></label>
            <input type="text" name="page_label" placeholder="<?php esc_attr_e('Site name or any text', 'modern-events-calendar-lite'); ?>" id="mec_page_label" value="" />
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
        <?php do_action('mec_add_organizer_extra_fields'); ?>
    <?php
    }

    /**
     * Save meta data of organizer taxonomy
     * @author Webnus <info@webnus.net>
     * @param int $term_id
     */
    public function save_metadata($term_id)
    {
        // Quick Edit
        if(!isset($_POST['tel'])) return;

        $tel = isset($_POST['tel']) ? sanitize_text_field($_POST['tel']) : '';
        $email = isset($_POST['email']) ? sanitize_text_field($_POST['email']) : '';
        $url = (isset($_POST['url']) and trim($_POST['url'])) ? sanitize_url($_POST['url']) : '';
        $page_label = (isset($_POST['page_label']) and trim($_POST['page_label'])) ? sanitize_text_field($_POST['page_label']) : '';
        $facebook   = (isset($_POST['facebook']) and trim($_POST['facebook'])) ? esc_url($_POST['facebook']) : '';
        $twitter    = (isset($_POST['twitter']) and trim($_POST['twitter'])) ? esc_url($_POST['twitter']) : '';
        $instagram  = (isset($_POST['instagram']) and trim($_POST['instagram'])) ? esc_url($_POST['instagram']) : '';
        $linkedin   = (isset($_POST['linkedin']) and trim($_POST['linkedin'])) ? esc_url($_POST['linkedin']) : '';
        $thumbnail = isset($_POST['thumbnail']) ? sanitize_text_field($_POST['thumbnail']) : '';

        update_term_meta($term_id, 'tel', $tel);
        update_term_meta($term_id, 'email', $email);
        update_term_meta($term_id, 'url', $url);
        update_term_meta($term_id, 'page_label', $page_label);
        update_term_meta($term_id, 'facebook', $facebook);
        update_term_meta($term_id, 'twitter', $twitter);
        update_term_meta($term_id, 'instagram', $instagram);
        update_term_meta($term_id, 'linkedin', $linkedin);
        update_term_meta($term_id, 'thumbnail', $thumbnail);

        do_action('mec_save_organizer_extra_fields', $term_id);
    }

    /**
     * Filter columns of organizer taxonomy
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
        $columns['name'] = $this->main->m('taxonomy_organizer', esc_html__('Organizer', 'modern-events-calendar-lite'));
        $columns['contact'] = esc_html__('Contact info', 'modern-events-calendar-lite');
        $columns['posts'] = esc_html__('Count', 'modern-events-calendar-lite');
        $columns['slug'] = esc_html__('Slug', 'modern-events-calendar-lite');

        return apply_filters('organizer_filter_column', $columns);
    }

    /**
     * Filter content of organizer taxonomy columns
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

            case 'contact':

                $tel = get_metadata('term', $term_id, 'tel', true);
                $email = get_metadata('term', $term_id, 'email', true);

                $content = $email.(trim($tel) ? '<br />'.$tel : '');
                break;

            default:
                break;
        }

        return apply_filters('organizer_filter_column_content', $content, $column_name, $term_id);
    }

    /**
     * Show organizer meta box
     * @author Webnus <info@webnus.net>
     * @param object $post
     */
    public function meta_box_organizer($post){

        FormBuilder::organizers( $post );
    }

    /**
     * Save event organizer data
     * @author Webnus <info@webnus.net>
     * @param int $post_id
     * @return boolean
     */
    public function save_event($post_id)
    {
        // Check if our nonce is set.
        if(!isset($_POST['mec_event_nonce'])) return false;

        // Verify that the nonce is valid.
        if(!wp_verify_nonce(sanitize_text_field($_POST['mec_event_nonce']), 'mec_event_data')) return false;

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if(defined('DOING_AUTOSAVE') and DOING_AUTOSAVE) return false;

        $action = (isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '');
        if($action === 'mec_fes_form') return false;

        // Get Modern Events Calendar Data
        $_mec = isset($_POST['mec']) ? $this->main->sanitize_deep_array($_POST['mec']) : array();

        // Selected a saved organizer
        if(isset($_mec['organizer_id']) and $_mec['organizer_id'])
        {
            // Set term to the post
            wp_set_object_terms($post_id, (int) sanitize_text_field($_mec['organizer_id']), 'mec_organizer');

            return true;
        }

        $name = (isset($_mec['organizer']['name']) and trim($_mec['organizer']['name'])) ? sanitize_text_field($_mec['organizer']['name']) : esc_html__('Organizer Name', 'modern-events-calendar-lite');

        $term = get_term_by('name', $name, 'mec_organizer');

        // Term already exists
        if(is_object($term) and isset($term->term_id))
        {
            // Set term to the post
            wp_set_object_terms($post_id, (int) $term->term_id, 'mec_organizer');

            return true;
        }

        $term = wp_insert_term($name, 'mec_organizer');

        // An error ocurred
        if(is_wp_error($term)) return false;

        $organizer_id = $term['term_id'];

        if(!$organizer_id) return false;

        // Set Organizer ID to the parameters
        $_POST['mec']['organizer_id'] = $organizer_id;

        // Set term to the post
        wp_set_object_terms($post_id, (int) $organizer_id, 'mec_organizer');

        $tel = (isset($_mec['organizer']['tel']) and trim($_mec['organizer']['tel'])) ? sanitize_text_field($_mec['organizer']['tel']) : '';
        $email = (isset($_mec['organizer']['email']) and trim($_mec['organizer']['email'])) ? sanitize_text_field($_mec['organizer']['email']) : '';
        $url = (isset($_mec['organizer']['url']) and trim($_mec['organizer']['url'])) ? sanitize_url($_mec['organizer']['url']) : '';
        $page_label = (isset($_mec['organizer']['page_label']) and trim($_mec['organizer']['page_label'])) ? sanitize_text_field($_mec['organizer']['page_label']) : '';
        $thumbnail = (isset($_mec['organizer']['thumbnail']) and trim($_mec['organizer']['thumbnail'])) ? sanitize_text_field($_mec['organizer']['thumbnail']) : '';

        update_term_meta($organizer_id, 'tel', $tel);
        update_term_meta($organizer_id, 'email', $email);
        update_term_meta($organizer_id, 'url', $url);
        update_term_meta($organizer_id, 'page_label', $page_label);
        update_term_meta($organizer_id, 'thumbnail', $thumbnail);

        return true;
    }
}