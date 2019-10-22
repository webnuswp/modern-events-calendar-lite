<?php
/** no direct access **/
defined('MECEXEC') or die();

$calendar_type = 'calendar';
if(in_array($this->style, array('clean', 'modern'))) $calendar_type = 'calendar_clean';
if(in_array($this->style, array('novel'))) $calendar_type = 'calendar_novel';
if(in_array($this->style, array('simple'))) $calendar_type = 'calendar_simple';

echo $this->draw_monthly_calendar($this->year, $this->month, $this->events, $calendar_type);