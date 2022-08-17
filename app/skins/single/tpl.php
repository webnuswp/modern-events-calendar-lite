<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_skin_single $this */

$styling = $this->main->get_styling();
$event = $this->events[0];
$event_colorskin = (isset($styling['mec_colorskin']) || isset($styling['color'])) ? 'colorskin-custom' : '';
$settings = $this->main->get_settings();

$occurrence = (isset($event->date['start']['date']) ? $event->date['start']['date'] : (isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : ''));
$occurrence_end_date = (isset($event->date['end']['date']) ? $event->date['end']['date'] : (trim($occurrence) ? $this->main->get_end_date_by_occurrence($event->data->ID, (isset($event->date['start']['date']) ? $event->date['start']['date'] : $occurrence)) : ''));

// Event Object
$GLOBALS['mec-event'] = $event;

$show_event_details_page = apply_filters('mec_show_event_details_page', true, $event->data->ID);
if($show_event_details_page !== true)
{
    echo MEC_kses::full($show_event_details_page);
    return;
}

if(post_password_required($event->data->post))
{
    echo get_the_password_form($event->data->post);
    return;
}

// Created by FES?
$fes = ($event and isset($event->data, $event->data->meta, $event->data->meta['mec_created_by_fes']));

if(isset($this->layout) and trim($this->layout)) $layout = $this->layout;
elseif($fes and isset($settings['fes_single_event_style']) and trim($settings['fes_single_event_style'])) $layout = $settings['fes_single_event_style'];
elseif(!isset($settings['single_single_style']) or (isset($settings['single_single_style']) and $settings['single_single_style'] == 'default')) $layout = 'default';
elseif(isset($settings['single_single_style']) and $settings['single_single_style'] == 'builder') $layout = 'builder';
elseif(isset($settings['single_single_style']) and $settings['single_single_style'] == 'divi-builder') $layout = 'divi-builder';
else $layout = 'modern';

include MEC::import('app.skins.single.'.$layout, true, true);