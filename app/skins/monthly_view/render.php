<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_skin_monthly_view $this */

if(in_array($this->style, array('clean', 'modern'))) $calendar_type = 'calendar_clean';
elseif(in_array($this->style, array('novel'))) $calendar_type = 'calendar_novel';
elseif(in_array($this->style, array('simple'))) $calendar_type = 'calendar_simple';
elseif(in_array($this->style, array('admin'))) $calendar_type = 'calendar_admin';
else $calendar_type = 'calendar';

echo MEC_kses::full($this->draw_monthly_calendar($this->year, $this->month, $this->events, $calendar_type));