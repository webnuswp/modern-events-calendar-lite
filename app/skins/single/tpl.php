<?php
/** no direct access **/
defined('MECEXEC') or die();

$styling = $this->main->get_styling();
$event = $this->events[0];
$event_colorskin = (isset($styling['mec_colorskin']) || isset($styling['color'])) ? 'colorskin-custom' : '';
$settings = $this->main->get_settings();

$occurrence = (isset($event->date['start']['date']) ? $event->date['start']['date'] : (isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : ''));
$occurrence_end_date = trim($occurrence) ? $this->main->get_end_date_by_occurrence($event->data->ID, (isset($event->date['start']['date']) ? $event->date['start']['date'] : $occurrence)) : '';

if(isset($this->layout) and trim($this->layout)) include MEC::import('app.skins.single.'.$this->layout, true, true);
elseif(!isset($settings['single_single_style']) or (isset($settings['single_single_style']) and $settings['single_single_style'] == 'default')) include MEC::import('app.skins.single.default', true, true);
elseif(!isset($settings['single_single_style']) or (isset($settings['single_single_style']) and $settings['single_single_style'] == 'builder')) include MEC::import('app.skins.single.builder', true, true);
else include MEC::import('app.skins.single.modern', true, true);