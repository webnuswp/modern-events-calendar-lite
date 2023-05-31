<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC sponsors class.
 * @author Webnus <info@webnus.net>
 */
class MEC_feature_sponsors extends MEC_base
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
     * Initialize Sponsors feature
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        // Feature is not included in PRO
        if(!$this->getPRO()) return;

        // Sponsors Feature is Disabled
        if(!isset($this->settings['sponsors_status']) or (isset($this->settings['sponsors_status']) and !$this->settings['sponsors_status'])) return;

        $this->factory->action('init', array($this, 'register_taxonomy'), 25);
        $this->factory->action('mec_sponsor_edit_form_fields', array($this, 'edit_form'));
        $this->factory->action('mec_sponsor_add_form_fields', array($this, 'add_form'));
        $this->factory->action('edited_mec_sponsor', array($this, 'save_metadata'));
        $this->factory->action('created_mec_sponsor', array($this, 'save_metadata'));

        $this->factory->filter('post_edit_category_parent_dropdown_args', array($this, 'hide_parent_dropdown'));
    }
    
    /**
     * Registers Sponsors taxonomy
     * @author Webnus <info@webnus.net>
     */
    public function register_taxonomy()
    {
        $singular_label = $this->main->m('taxonomy_sponsor', esc_html__('Sponsor', 'modern-events-calendar-lite'));
        $plural_label = $this->main->m('taxonomy_sponsors', esc_html__('Sponsors', 'modern-events-calendar-lite'));

        register_taxonomy(
            'mec_sponsor',
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
                'rewrite'=>array('slug'=>'events-sponsor'),
                'public'=>false,
                'show_ui'=>true,
                'show_in_rest'=>true,
                'hierarchical'=>false,
                'meta_box_cb'=>'post_categories_meta_box',
            )
        );
        
        register_taxonomy_for_object_type('mec_sponsor', $this->main->get_main_post_type());
    }
    
    /**
     * Show edit form of Sponsors taxonomy
     * @author Webnus <info@webnus.net>
     * @param object $term
     */
    public function edit_form($term)
    {
        $link = get_metadata('term', $term->term_id, 'link', true);
        $logo = get_metadata('term', $term->term_id, 'logo', true);
    ?>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_link"><?php esc_html_e('Link', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <input type="url" placeholder="<?php esc_attr_e('Insert URL of Sponsor', 'modern-events-calendar-lite'); ?>" name="link" id="mec_link" value="<?php echo esc_attr($link); ?>" />
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_thumbnail_button"><?php esc_html_e('Logo', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <div id="mec_thumbnail_img"><?php if(trim($logo) != '') echo '<img src="'.esc_url($logo).'" />'; ?></div>
                <input type="hidden" name="logo" id="mec_thumbnail" value="<?php echo esc_attr($logo); ?>" />
                <button type="button" class="mec_upload_image_button button" id="mec_thumbnail_button"><?php echo esc_html__('Upload/Add image', 'modern-events-calendar-lite'); ?></button>
                <button type="button" class="mec_remove_image_button button <?php echo (!trim($logo) ? 'mec-util-hidden' : ''); ?>"><?php echo esc_html__('Remove image', 'modern-events-calendar-lite'); ?></button>
            </td>
        </tr>
        <?php do_action('mec_edit_sponsor_extra_fields', $term); ?>
    <?php
    }
    
    /**
     * Show add form of Sponsors taxonomy
     * @author Webnus <info@webnus.net>
     */
    public function add_form()
    {
    ?>
        <div class="form-field">
            <label for="mec_link"><?php esc_html_e('Link', 'modern-events-calendar-lite'); ?></label>
            <input type="url" name="link" placeholder="<?php esc_attr_e('Insert URL of Sponsor.', 'modern-events-calendar-lite'); ?>" id="mec_link" value="" />
        </div>
        <div class="form-field">
            <label for="mec_thumbnail_button"><?php esc_html_e('Logo', 'modern-events-calendar-lite'); ?></label>
            <div id="mec_thumbnail_img"></div>
            <input type="hidden" name="logo" id="mec_thumbnail" value="" />
            <button type="button" class="mec_upload_image_button button" id="mec_thumbnail_button"><?php echo esc_html__('Upload/Add image', 'modern-events-calendar-lite'); ?></button>
            <button type="button" class="mec_remove_image_button button mec-util-hidden"><?php echo esc_html__('Remove image', 'modern-events-calendar-lite'); ?></button>
        </div>
        <?php do_action('mec_add_sponsor_extra_fields'); ?>
    <?php
    }
    
    /**
     * Save meta data of Sponsors taxonomy
     * @author Webnus <info@webnus.net>
     * @param int $term_id
     */
    public function save_metadata($term_id)
    {
        // Quick Edit
        if(!isset($_POST['link'])) return;

        $link = (isset($_POST['link']) and trim($_POST['link'])) ? esc_url($_POST['link']) : '';
        $logo = (isset($_POST['logo']) and trim($_POST['logo'])) ? esc_url($_POST['logo']) : '';
        
        update_term_meta($term_id, 'link', $link);
        update_term_meta($term_id, 'logo', $logo);

        do_action('mec_save_sponsor_extra_fields', $term_id);
    }

    public function hide_parent_dropdown($args)
    {
        if('mec_sponsor' == $args['taxonomy']) $args['echo'] = false;
        return $args;
    }
}
