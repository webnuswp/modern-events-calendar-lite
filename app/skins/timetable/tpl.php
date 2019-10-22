<?php
/** no direct access **/
defined('MECEXEC') or die();
do_action('mec_start_skin' , $this->id);
do_action('mec_timetable_skin_head');
if($this->style == 'clean') include MEC::import('app.skins.timetable.clean', true, true);
else include MEC::import('app.skins.timetable.modern', true, true);