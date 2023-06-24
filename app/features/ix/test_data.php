<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_feature_ix $this */

$categories = get_terms(array(
    'taxonomy' => 'mec_category',
    'hide_empty' => 0,
));
$tags = get_terms(array(
    'taxonomy' => apply_filters('mec_taxonomy_tag', ''),
    'hide_empty' => 0,
));
$locations = get_terms(array(
    'taxonomy' => 'mec_location',
    'hide_empty' => 0,
));
$organizers = get_terms(array(
    'taxonomy' => 'mec_organizer',
    'hide_empty' => 0,
));
?>
<div class="wrap" id="mec-wrap">
    <h1><?php esc_html_e('MEC Import / Export', 'modern-events-calendar-lite'); ?></h1>
    <h2 class="nav-tab-wrapper">
        <a href="<?php echo esc_url($this->main->remove_qs_var('tab')); ?>" class="nav-tab"><?php echo esc_html__('Google Cal. Import', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-g-calendar-export')); ?>" class="nav-tab"><?php echo esc_html__('Google Cal. Export', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-f-calendar-import')); ?>" class="nav-tab"><?php echo esc_html__('Facebook Cal. Import', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-meetup-import')); ?>" class="nav-tab"><?php echo esc_html__('Meetup Import', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-sync')); ?>" class="nav-tab"><?php echo esc_html__('Synchronization', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-export')); ?>" class="nav-tab"><?php echo esc_html__('Export', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-import')); ?>" class="nav-tab"><?php echo esc_html__('Import', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-thirdparty')); ?>" class="nav-tab"><?php echo esc_html__('Third Party Plugins', 'modern-events-calendar-lite'); ?></a>
        <a href="<?php echo esc_url($this->main->add_qs_var('tab', 'MEC-test-data')); ?>" class="nav-tab nav-tab-active"><?php echo esc_html__('Test Data', 'modern-events-calendar-lite'); ?></a>
    </h2>
    <div class="mec-container">
        <div class="import-content w-clearfix extra">
            <h3><?php esc_html_e('Test Data Generator', 'modern-events-calendar-lite'); ?></h3>
            <form id="mec_test_data_generator_form" action="<?php echo esc_url($this->main->get_full_url()); ?>" method="POST" enctype="multipart/form-data">
                <div class="mec-form-row">
                    <p><?php echo esc_html__("You can use following form to generate some test events.", 'modern-events-calendar-lite'); ?></p>
                </div>
                <div class="mec-form-row">
                    <label class="mec-col-2"><?php esc_html_e("Number of Events", 'modern-events-calendar-lite'); ?></label>
                    <input type="number" class="mec-col-3" name="number" value="3" min="1" max="20" step="1">
                </div>
                <div class="mec-form-row">
                    <label class="mec-col-2"><?php echo $this->main->m('taxonomy_categories', esc_html__("Categories", 'modern-events-calendar-lite')); ?></label>
                    <select name="category" class="mec-col-3">
                        <option value="">-----</option>
                        <option value="random"><?php esc_html_e('Random from Existing Items', 'modern-events-calendar-lite'); ?></option>
                        <option value="generate"><?php esc_html_e('Generate and Assign', 'modern-events-calendar-lite'); ?></option>
                        <?php foreach($categories as $category): ?>
                        <option value="<?php echo esc_attr($category->term_id); ?>"><?php echo $category->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mec-form-row">
                    <label class="mec-col-2"><?php echo $this->main->m('taxonomy_tags', esc_html__("Tags", 'modern-events-calendar-lite')); ?></label>
                    <select name="tag" class="mec-col-3">
                        <option value="">-----</option>
                        <option value="random"><?php esc_html_e('Random from Existing Items', 'modern-events-calendar-lite'); ?></option>
                        <option value="generate"><?php esc_html_e('Generate and Assign', 'modern-events-calendar-lite'); ?></option>
                        <?php foreach($tags as $tag): ?>
                        <option value="<?php echo esc_attr($tag->term_id); ?>"><?php echo $tag->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mec-form-row">
                    <label class="mec-col-2"><?php echo $this->main->m('taxonomy_locations', esc_html__("Locations", 'modern-events-calendar-lite')); ?></label>
                    <select name="location" class="mec-col-3">
                        <option value="">-----</option>
                        <option value="random"><?php esc_html_e('Random from Existing Items', 'modern-events-calendar-lite'); ?></option>
                        <option value="generate"><?php esc_html_e('Generate and Assign', 'modern-events-calendar-lite'); ?></option>
                        <?php foreach($locations as $location): ?>
                        <option value="<?php echo esc_attr($location->term_id); ?>"><?php echo $location->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mec-form-row">
                    <label class="mec-col-2"><?php echo $this->main->m('taxonomy_organizers', esc_html__("Organizers", 'modern-events-calendar-lite')); ?></label>
                    <select name="organizer" class="mec-col-3">
                        <option value="">-----</option>
                        <option value="random"><?php esc_html_e('Random from Existing Items', 'modern-events-calendar-lite'); ?></option>
                        <option value="generate"><?php esc_html_e('Generate and Assign', 'modern-events-calendar-lite'); ?></option>
                        <?php foreach($organizers as $organizer): ?>
                        <option value="<?php echo esc_attr($organizer->term_id); ?>"><?php echo $organizer->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mec-form-row">
                    <input type="hidden" name="mec-ix-action" value="test-data-generation-start">
                    <?php wp_nonce_field('mec_test_event'); ?>
                    <button class="button button-primary mec-button-primary mec-btn-2"><?php esc_html_e('Generate Events', 'modern-events-calendar-lite'); ?></button>
                </div>
            </form>

            <?php if($this->action == 'test-data-generation-start'): ?>
                <div class="mec-ix-test-data-started">
                    <?php if($this->response['success'] == 0): ?>
                        <div class="mec-error"><?php echo MEC_kses::element($this->response['message']); ?></div>
                    <?php else: ?>
                        <div class="mec-success"><?php echo MEC_kses::element($this->response['message']); ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php
            $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
            do_action('mec_import_export_page', $tab);
        ?>
    </div>
</div>