<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC labels class.
 * @author Webnus <info@webnus.net>
 */
class MEC_feature_labels extends MEC_base
{
    /**
     * @var MEC_factory
     */
    public $factory;

    /**
     * @var MEC_main
     */
    public $main;

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
    }
    
    /**
     * Initialize label feature
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        $this->factory->action('init', array($this, 'register_taxonomy'), 15);
        $this->factory->action('mec_label_edit_form_fields', array($this, 'edit_form'));
        $this->factory->action('mec_label_add_form_fields', array($this, 'add_form'));
        $this->factory->action('edited_mec_label', array($this, 'save_metadata'));
        $this->factory->action('created_mec_label', array($this, 'save_metadata'));
        
        $this->factory->action('add_meta_boxes', array($this, 'register_meta_boxes'));
        
        $this->factory->filter('manage_edit-mec_label_columns', array($this, 'filter_columns'));
        $this->factory->filter('manage_mec_label_custom_column', array($this, 'filter_columns_content'), 10, 3);
        
        $this->factory->action('save_post', array($this, 'save_event'), 3);
    }
    
    /**
     * Register label taxonomy
     * @author Webnus <info@webnus.net>
     */
    public function register_taxonomy()
    {
        $singular_label = $this->main->m('taxonomy_label', esc_html__('Label', 'modern-events-calendar-lite'));
        $plural_label = $this->main->m('taxonomy_labels', esc_html__('Labels', 'modern-events-calendar-lite'));

        register_taxonomy(
            'mec_label',
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
                'rewrite'=>array('slug'=>'events-label'),
                'public'=>false,
                'show_ui'=>true,
                'hierarchical'=>false,
            )
        );
        
        register_taxonomy_for_object_type('mec_label', $this->main->get_main_post_type());
    }
    
    /**
     * Show edit form of labels
     * @author Webnus <info@webnus.net>
     * @param object $term
     */
    public function edit_form($term)
    {
        $color = get_metadata('term', $term->term_id, 'color', true);
        $style = get_metadata('term', $term->term_id, 'style', true);
    ?>
        <tr class="form-field">
            <th scope="row" >
                <label for="mec_color"><?php esc_html_e('Color', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <input type="text" name="color" id="mec_color" value="<?php echo esc_attr($color); ?>" data-default-color="<?php echo esc_attr($color); ?>" class="mec-color-picker" />
                <p class="description"><?php esc_html_e('Select label color', 'modern-events-calendar-lite'); ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row" >
                <label for="mec_style"><?php esc_html_e('Style', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <select name="style" id="mec_style">
                    <option value=""><?php esc_html_e('Normal', 'modern-events-calendar-lite'); ?></option>
                    <option value="mec-label-featured" <?php echo ($style == 'mec-label-featured' ? 'selected="selected"' : ''); ?>><?php esc_html_e('Featured', 'modern-events-calendar-lite'); ?></option>
                    <option value="mec-label-canceled" <?php echo ($style == 'mec-label-canceled' ? 'selected="selected"' : ''); ?>><?php esc_html_e('Canceled', 'modern-events-calendar-lite'); ?></option>
                    <option value="mec-label-custom" <?php echo ($style == 'mec-label-custom' ? 'selected="selected"' : ''); ?>><?php esc_html_e('Custom', 'modern-events-calendar-lite'); ?></option>
                </select>
                <p class="description"><?php esc_html_e('You can show featured, canceled and custom labels by a different style!', 'modern-events-calendar-lite'); ?></p>
            </td>
        </tr>
    <?php
    }
    
    /**
     * Show add form of labels
     * @author Webnus <info@webnus.net>
     */
    public function add_form()
    {
    ?>
        <div class="form-field">
            <label for="mec_color"><?php esc_html_e('Color', 'modern-events-calendar-lite'); ?></label>
            <input type="text" name="color" id="mec_color" value="" data-default-color="<?php echo esc_attr($this->main->get_default_label_color()); ?>" class="mec-color-picker" />
            <p class="description"><?php esc_html_e('Select label color', 'modern-events-calendar-lite'); ?></p>
        </div>
        <div class="form-field">
            <label for="mec_style"><?php esc_html_e('Style', 'modern-events-calendar-lite'); ?></label>
            <select name="style" id="mec_style">
                <option value=""><?php esc_html_e('Normal', 'modern-events-calendar-lite'); ?></option>
                <option value="mec-label-featured"><?php esc_html_e('Featured', 'modern-events-calendar-lite'); ?></option>
                <option value="mec-label-canceled"><?php esc_html_e('Canceled', 'modern-events-calendar-lite'); ?></option>
                <option value="mec-label-custom"><?php esc_html_e('Custom', 'modern-events-calendar-lite'); ?></option>
            </select>
            <p class="description"><?php esc_html_e('You can show featured, canceled and custom labels by a different style!', 'modern-events-calendar-lite'); ?></p>
        </div>
    <?php
    }
    
    /**
     * Save label meta data
     * @author Webnus <info@webnus.net>
     * @param int $term_id
     */
    public function save_metadata($term_id)
    {
        // Quick Edit
        if(!isset($_POST['color'])) return;

        $color = isset($_POST['color']) ? sanitize_text_field($_POST['color']) : $this->main->get_default_label_color();
        update_term_meta($term_id, 'color', $color);

        $style = isset($_POST['style']) ? sanitize_text_field($_POST['style']) : '';
        update_term_meta($term_id, 'style', $style);
    }
    
    /**
     * Filter label taxonomy columns
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
        $columns['name'] = esc_html__('Name', 'modern-events-calendar-lite');
        $columns['color'] = esc_html__('Color', 'modern-events-calendar-lite');
        $columns['posts'] = esc_html__('Count', 'modern-events-calendar-lite');
        $columns['slug'] = esc_html__('Slug', 'modern-events-calendar-lite');

        return $columns;
    }
    
    /**
     * Filter content of label taxonomy
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

            case 'color':
                
                $content = '<span class="mec-color" style="background-color: '.get_metadata('term', $term_id, 'color', true).';"></span>';
                break;

            default:
                break;
        }

        return $content;
    }
    
    /**
     * Register meta box of labels
     * @author Webnus <info@webnus.net>
     */
    public function register_meta_boxes()
    {
        add_meta_box('mec_metabox_label', sprintf(esc_html__('Event %s', 'modern-events-calendar-lite'), $this->main->m('taxonomy_labels', esc_html__('Labels', 'modern-events-calendar-lite'))), array($this, 'meta_box_labels'), $this->main->get_main_post_type(), 'side');
    }
    
    /**
     * Show meta box of labels
     * @author Webnus <info@webnus.net>
     * @param object $post
     */
    public function meta_box_labels($post)
    {
        $labels = get_terms('mec_label', array('orderby'=>'name', 'order'=>'ASC', 'hide_empty'=>'0'));
        $terms = wp_get_post_terms($post->ID, 'mec_label', array('fields'=>'ids'));
    ?>
        <div class="mec-meta-box-labels-container">
            <div class="mec-form-row">
                <?php foreach($labels as $label): ?>
                <div class="mec-label-row">
                    <input <?php if(in_array($label->term_id, $terms)) echo 'checked="checked"'; ?> name="mec[labels][]" type="checkbox" value="<?php echo esc_attr($label->term_id); ?>" id="mec_label<?php echo esc_attr($label->term_id); ?>" />
                    <?php do_action('mec_label_to_checkbox_backend', $label, $terms); ?>
                    <label for="mec_label<?php echo esc_attr($label->term_id); ?>"><?php echo esc_html($label->name); ?></label>
                    <span class="mec-color" style="background-color: <?php echo get_term_meta($label->term_id, 'color', true); ?>"></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php
    }
    
    /**
     * Save label of event
     * @author Webnus <info@webnus.net>
     * @param int $post_id
     * @return void
     */
    public function save_event($post_id)
    {
        // Check if our nonce is set.
        if(!isset($_POST['mec_event_nonce'])) return;

        // Verify that the nonce is valid.
        if(!wp_verify_nonce(sanitize_text_field($_POST['mec_event_nonce']), 'mec_event_data')) return;

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if(defined('DOING_AUTOSAVE') and DOING_AUTOSAVE) return;

        // Get Modern Events Calendar Data
        $_mec = isset($_POST['mec']) ? $this->main->sanitize_deep_array($_POST['mec']) : array();
        
        $_labels = isset($_mec['labels']) ? (array) $_mec['labels'] : array();
        
        $_labels = array_map('sanitize_text_field', $_labels);
        $_labels = array_map('intval', $_labels);
        $_labels = array_unique($_labels);
        
        wp_set_object_terms($post_id, $_labels, 'mec_label');
    }
}