<?php
/** no direct access **/
defined('MECEXEC') or die();
?>
<div class="mec-calendar-metabox mec-shortcode" id="MECCopyCode" onclick="mec_copy_code();" title="<?php echo esc_html__('Click to copy shortcode', 'modern-events-calendar-lite'); ?>">[MEC id="<?php echo esc_html($post->ID); ?>"]</div>
<span class="mec-copied">Copied!</span>