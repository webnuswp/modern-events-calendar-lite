<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_skin_slider $this */

// Get layout path
$render_path = $this->get_render_path();
$styling = $this->main->get_styling();

$dark_mode = (isset($styling['dark_mode']) ? $styling['dark_mode'] : '');
if($dark_mode == 1) $set_dark = 'mec-dark-mode';
else $set_dark = '';

ob_start();
include $render_path;
$items_html = ob_get_clean();

// Inclue OWL Assets
$this->main->load_owl_assets();

// Generating javascript code tpl
$javascript = '<script>
jQuery(document).ready(function()
{
    jQuery("#mec_skin_'.esc_js($this->id).'").mecSliderView(
    {
        id: "'.esc_js($this->id).'",
        start_date: "'.esc_js($this->start_date).'",
        atts: "'.http_build_query(array('atts' => $this->atts), '', '&').'",
        autoplay: "'.esc_js($this->autoplay).'",
        transition_time: '.esc_js($this->transition_time).',
        ajax_url: "'.admin_url('admin-ajax.php', NULL).'",
    });
});
</script>';

// Include javascript code into the page
if($this->main->is_ajax() or $this->main->preview()) echo MEC_kses::full($javascript);
else $this->factory->params('footer', $javascript);

do_action('mec_start_skin', $this->id);
do_action('mec_slider_skin_head');
?>
<div class="mec-wrap mec-skin-slider-container<?php echo esc_attr($this->html_class . ' ' . $set_dark); ?>" id="mec_skin_<?php echo esc_attr($this->id); ?>">

    <?php if($this->found): ?>
    <div class="mec-skin-slider-events-container" id="mec_skin_events_<?php echo esc_attr($this->id); ?>">
        <?php echo MEC_kses::full($items_html); ?>
    </div>
    <?php else: ?>
    <div class="mec-skin-slider-events-container" id="mec_skin_events_<?php echo esc_attr($this->id); ?>">
        <?php esc_html_e('No event found!', 'modern-events-calendar-lite'); ?>
    </div>
    <?php endif; ?>

</div>