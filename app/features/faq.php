<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC FAQ class.
 * @author Webnus <info@webnus.net>
 */
class MEC_feature_faq extends MEC_base
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
     * Initialize feature
     * @author Webnus <info@webnus.net>
     */
    public function init()
    {
        // FAQ Status
        $faq_status = isset($this->settings['faq_status']) && $this->settings['faq_status'];

        // Feature is not enabled
        if(!$faq_status) return;

        // Tab
        $this->factory->filter('mec-single-event-meta-title', array($this, 'tab'));

        // Metabox
        $this->factory->action('mec_metabox_details', array($this, 'meta_box_faq'), 18);

        // FAQ for FES
        if(!isset($this->settings['fes_section_faq']) or (isset($this->settings['fes_section_faq']) and $this->settings['fes_section_faq'])) $this->factory->action('mec_fes_metabox_details', array($this, 'meta_box_faq'), 18);

        // Save Data
        $this->factory->action('mec_save_event_data', array($this, 'save'), 10, 2);
    }

    public function tab($tabs)
    {
        $tabs[__('FAQ', 'modern-events-calendar-lite')] = 'mec-faq';
        return $tabs;
    }

    /**
     * Show FAQs of event into the Add/Edit event page
     *
     * @author Webnus <info@webnus.net>
     * @param object $post
     */
    public function meta_box_faq($post)
    {
        $faqs = get_post_meta($post->ID, 'mec_faq', true);
        if(!is_array($faqs)) $faqs = [];
        ?>
        <div class="mec-meta-box-fields mec-event-tab-content" id="mec-faq">
            <h4><?php esc_html_e('FAQ', 'modern-events-calendar-lite'); ?></h4>
            <div class="mec-faq-wrapper mec-form-row">
                <div>
                    <button id="mec_add_faq_button" type="button" class="button mec-button-new"><?php esc_attr_e('Add', 'modern-events-calendar-lite'); ?></button>
                </div>
                <ul class="mec-faq-list" id="mec_faq_list">
                    <?php $i = 0; foreach($faqs as $faq): ?>
                    <li class="mec-box mec_faq_row" id="mec_faq_row<?php echo $i; ?>">
                        <div class="mec-faq-actions">
                            <ul>
                                <li><button class=" button mec-faq-remove" onclick="mec_faq_remove(<?php echo $i; ?>)"><?php esc_html_e("Remove", 'modern-events-calendar-lite'); ?></button></li>
                            </ul>
                        </div>
                        <div class="mec-faq-data">
                            <div class="mec-faq-data-row mec-form-row">
                                <div class="mec-col-2">
                                    <label for="mec_faq<?php echo $i; ?>_title"><?php esc_html_e("FAQ Title", 'modern-events-calendar-lite'); ?></label>
                                </div>
                                <div class="mec-col-8">
                                    <input type="text" class="mec-col-12" name="mec[faq][<?php echo $i; ?>][title]" placeholder="<?php esc_attr_e('Title Here', 'modern-events-calendar-lite'); ?>" value="<?php echo esc_attr($faq['title']); ?>">
                                </div>
                            </div>
                            <div class="mec-faq-data-row mec-form-row">
                                <div class="mec-col-2">
                                    <label for="mec_faq<?php echo $i; ?>_body"><?php esc_html_e("FAQ Content", 'modern-events-calendar-lite'); ?></label>
                                </div>
                                <div class="mec-col-8">
                                    <textarea type="text" class="mec-col-12" name="mec[faq][<?php echo $i; ?>][body]" placeholder="<?php esc_attr_e('FAQ Content Here', 'modern-events-calendar-lite'); ?>"><?php echo esc_textarea($faq['body']); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </li>
                    <?php $i++; endforeach; ?>
                </ul>
            </div>
            <input type="hidden" id="mec_new_faq_key" value="<?php echo ($i + 1); ?>"/>
            <div class="mec-util-hidden" id="mec_new_faq_raw">
                <li class="mec-box mec_faq_row" id="mec_faq_row:i:">
                    <div class="mec-faq-actions">
                        <ul>
                            <li><button class="button mec-faq-remove" onclick="mec_faq_remove(:i:)"><?php esc_html_e("Remove", 'modern-events-calendar-lite'); ?></button></li>
                        </ul>
                    </div>
                    <div class="mec-faq-data">
                        <div class="mec-faq-data-row mec-form-row">
                            <div class="mec-col-2">
                                <label for="mec_faq:i:_title"><?php esc_html_e("FAQ Title", 'modern-events-calendar-lite'); ?></label>
                            </div>
                            <div class="mec-col-8">
                                <input type="text" class="mec-col-12" name="mec[faq][:i:][title]" placeholder="<?php esc_attr_e('Title Here', 'modern-events-calendar-lite'); ?>">
                            </div>
                        </div>
                        <div class="mec-faq-data-row mec-form-row">
                            <div class="mec-col-2">
                                <label for="mec_faq:i:_body"><?php esc_html_e("FAQ Content", 'modern-events-calendar-lite'); ?></label>
                            </div>
                            <div class="mec-col-8">
                                <textarea type="text" class="mec-col-12"     name="mec[faq][:i:][body]" placeholder="<?php esc_attr_e('FAQ Content Here', 'modern-events-calendar-lite'); ?>"></textarea>
                            </div>
                        </div>
                    </div>
                </li>
            </div>
        </div>
        <?php
    }

    /**
     * Store FAQ Data
     *
     * @param int $post_id
     * @param array $data
     * @return void
     */
    public function save($post_id, $data)
    {
        if(!isset($data['faq']) or !is_array($data['faq'])) return;

        $faq = [];
        foreach($data['faq'] as $k => $f)
        {
            if(!is_numeric($k)) continue;

            $faq[$k] = $f;
        }

        update_post_meta($post_id, 'mec_faq', $faq);
    }
}