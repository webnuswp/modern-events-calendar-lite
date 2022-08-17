<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_skin_carousel $this */

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

$sed_method = $this->sed_method;
if($sed_method == 'new') $sed_method = '0';

// Generating javascript code tpl
$loop = ($this->found > 1 ? $this->loop : false);
$javascript = '<script>
jQuery(document).ready(function()
{
    jQuery("#mec_skin_'.esc_js($this->id).'").mecCarouselView(
    {
        id: "'.esc_js($this->id).'",
        start_date: "'.esc_js($this->start_date).'",
        items: "'.esc_js($this->count).'",
        items_tablet: "'.esc_js($this->count_tablet).'",
        items_mobile: "'.esc_js($this->count_mobile).'",
        autoplay_status: "'.esc_js($this->autoplay_status).'",
        autoplay: "'.esc_js($this->autoplay).'",
        loop: '. json_encode($loop) .',
        style: "'.esc_js($this->style).'",
        atts: "'.http_build_query(array('atts' => $this->atts), '', '&').'",
        ajax_url: "'.admin_url('admin-ajax.php', NULL).'",
        sed_method: "'.esc_js($sed_method).'",
        image_popup: "'.esc_js($this->image_popup).'",
    });
});
</script>';

// Include javascript code into the page
if($this->main->is_ajax() or $this->main->preview()) echo MEC_kses::full($javascript);
else $this->factory->params('footer', $javascript);

do_action('mec_start_skin', $this->id);
do_action('mec_carousel_skin_head');
?>
<div class="mec-wrap mec-skin-carousel-container <?php echo esc_attr($this->html_class . ' ' . $set_dark); ?>" id="mec_skin_<?php echo esc_attr($this->id); ?>">
    <?php if($this->found): ?>
    <div class="mec-skin-carousel-events-container" id="mec_skin_events_<?php echo esc_attr($this->id); ?>">
        <?php echo MEC_kses::full($items_html); ?>
    </div>
    <?php else: ?>
    <div class="mec-skin-carousel-events-container" id="mec_skin_events_<?php echo esc_attr($this->id); ?>">
        <?php esc_html_e('No event found!', 'modern-events-calendar-lite'); ?>
    </div>
    <?php endif; ?>
</div>