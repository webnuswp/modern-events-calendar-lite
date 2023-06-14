<?php
/** no direct access **/
defined('MECEXEC') or die();

// Not enabled in lite version
if(!$this->getPRO()) return;

// MEC Settings
$settings = $this->get_settings();

// The module is disabled
if(!isset($settings['sponsors_status']) or (isset($settings['sponsors_status']) and !$settings['sponsors_status'])) return;

// Event Sponsors
$sponsors = (isset($event->data->sponsors) and is_array($event->data->sponsors)) ? $event->data->sponsors : array();

// No Sponsor
if(!count($sponsors)) return false;
?>
<div class="mec-sponsors-details mec-single-event-sponsor mec-frontbox" id="mec_sponsor_details">
    <h3 class="mec-events-single-section-title"><?php if(count($sponsors) == 1): echo esc_html($this->m('taxonomy_sponsor', esc_html__('Sponsor', 'modern-events-calendar-lite'))); else: echo esc_html($this->m('taxonomy_sponsors', esc_html__('Sponsors', 'modern-events-calendar-lite'))); endif; ?></h3>
    <ul>
        <?php foreach($sponsors as $sponsor): ?>
        <li>
            <div class="mec-sponsor">
                <?php if(trim($sponsor['link'])): ?>
                <a class="mec-color-hover" href="<?php echo esc_url($sponsor['link']); ?>">
                    <?php if(isset($sponsor['logo']) and trim($sponsor['logo'])): ?>
                    <img class="mec-border-color-hover mec-sponsor-logo" src="<?php echo esc_url($sponsor['logo']); ?>" alt="<?php echo esc_attr($sponsor['name']); ?>">
                    <?php endif; ?>
                    <span class="mec-sponsor-name"><?php echo esc_html($sponsor['name']); ?></span>
                </a>
                <?php else: ?>
                    <?php if(isset($sponsor['logo']) and trim($sponsor['logo'])): ?>
                    <img class="mec-border-color-hover mec-sponsor-logo" src="<?php echo esc_url($sponsor['logo']); ?>" alt="<?php echo esc_attr($sponsor['name']); ?>">
                    <?php endif; ?>
                    <span class="mec-sponsor-name"><?php echo esc_html($sponsor['name']); ?></span>
                <?php endif; ?>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
</div>