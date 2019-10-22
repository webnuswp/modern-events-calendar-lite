<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC colors class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_feature_colors extends MEC_base
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
     * @author Webnus <info@webnus.biz>
     */
    public function __construct()
    {
        // Import MEC Factory
        $this->factory = $this->getFactory();
        
        // Import MEC Main
        $this->main = $this->getMain();
    }
    
    /**
     * Initialize colors feature
     * @author Webnus <info@webnus.biz>
     */
    public function init()
    {
        $this->factory->action('add_meta_boxes', array($this, 'register_meta_boxes'));
        $this->factory->action('save_post', array($this, 'save_event'), 3);
    }
    
    /**
     * Registers color meta box
     * @author Webnus <info@webnus.biz>
     */
    public function register_meta_boxes()
    {
        add_meta_box('mec_metabox_color', __('Event Color', 'modern-events-calendar-lite'), array($this, 'meta_box_colors'), $this->main->get_main_post_type(), 'side');
    }
    
    /**
     * Show color meta box content
     * @author Webnus <info@webnus.biz>
     * @param object $post
     */
    public function meta_box_colors($post)
    {
        $color = get_post_meta($post->ID, 'mec_color', true);
        $available_colors = $this->main->get_available_colors();
        
        if(!trim($color)) $color = $available_colors[0];
    ?>
        <div class="mec-meta-box-colors-container">
            <div class="mec-form-row">
                <input type="text" id="mec_event_color" name="mec[color]" value="#<?php echo $color; ?>" data-default-color="#<?php echo $color; ?>" class="mec-color-picker" />
            </div>
            <div class="mec-form-row mec-available-color-row">
                <?php foreach($available_colors as $available_color): ?>
                <span class="mec-color" onclick="mec_set_event_color('<?php echo $available_color; ?>');" style="background-color: #<?php echo $available_color; ?>"></span>
                <?php endforeach; ?>
            </div>
        </div>
    <?php
    }
    
    /**
     * Save color of event
     * @author Webnus <info@webnus.biz>
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
        $_mec = isset($_POST['mec']) ? $_POST['mec'] : array();
        
        $color = isset($_mec['color']) ? trim(sanitize_text_field($_mec['color']), '# ') : '';
        update_post_meta($post_id, 'mec_color', $color);
        
        // Add the new color to available colors
        if(trim($color)) $this->main->add_to_available_colors($color);
    }
}