<?php
/** no direct access **/
defined('MECEXEC') or die();

// Get layout path
$render_path = $this->get_render_path();

ob_start();
include $render_path;
$items_html = ob_get_clean();

// Inclue OWL Assets
$this->main->load_owl_assets();

// Generating javascript code tpl
$javascript = '<script type="text/javascript">
jQuery(document).ready(function()
{
    jQuery("#mec_skin_'.$this->id.'").mecCarouselView(
    {
        id: "'.$this->id.'",
        start_date: "'.$this->start_date.'",
        items: "'.$this->count.'",
        autoplay: "'.$this->autoplay.'",
        style: "'.$this->style.'",
        atts: "'.http_build_query(array('atts'=>$this->atts), '', '&').'",
        ajax_url: "'.admin_url('admin-ajax.php', NULL).'",
    });
});
</script>';

// Include javascript code into the page
if($this->main->is_ajax()) echo $javascript;
else $this->factory->params('footer', $javascript);
do_action('mec_start_skin' , $this->id);
do_action('mec_carousel_skin_head');
?>
<div class="mec-wrap mec-skin-carousel-container<?php echo $this->html_class; ?>" id="mec_skin_<?php echo $this->id; ?>">
    
    <?php if($this->found): ?>
    <div class="mec-skin-carousel-events-container" id="mec_skin_events_<?php echo $this->id; ?>">
        <?php echo $items_html; ?>
    </div>
    <?php else: ?>
    <div class="mec-skin-carousel-events-container" id="mec_skin_events_<?php echo $this->id; ?>">
        <?php _e('No event found!', 'modern-events-calendar-lite'); ?>
    </div>
    <?php endif; ?>
</div>