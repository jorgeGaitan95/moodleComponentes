<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Defines the version and other meta-info about the plugin
 *
 * Setting the $plugin->version to 0 prevents the plugin from being installed.
 * See https://docs.moodle.org/dev/version.php for more info.
 *
 * @package    local_flowdiagram
 * @copyright  2017 Jorge Gaitán <jorgegaitan903@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
function local_estrategia_didactica_extend_navigation(global_navigation $navigation) {
$nodeFoo = $navigation->add('Estrategia Didáctica');
$nodeBar = $nodeFoo->add('Actividad Formación');
$nodeVaribilidaddes=$nodeBar->add('Variabilidad1',new moodle_url('/local/estrategia_didactica/index.php'));
$nodeVaribilidaddes=$nodeBar->add('Variabilidad2',new moodle_url('/local/estrategia_didactica/variabilidad2.php'));
$nodeVaribilidaddes=$nodeBar->add('PruebaQuiz',new moodle_url('/local/estrategia_didactica/variabilidad2.php'));
}
function getEstrategiaDidactica($userid, $courseid){
  global $DB;
  return $DB->get_record('assigneducational_strategy', array('userid'=>$userid,'courseid'=>$courseid));
}
function getActivities($userid, $courseid){
  global $DB;
  $activities=array();
  $estrategia_didactica = getEstrategiaDidactica($userid,$courseid);
  $result= $DB->get_records('activities', array('educational_strategy_id'=>$estrategia_didactica->educational_strategy_id));
  foreach ($result as $activity) {
    $url= new moodle_url('/local/estrategia_didactica/index.php',array('id' => 3,'activityid'=>$activity->id));
    echo html_writer::link($url,$activity->id);
    $aux= array('id' => $activity->id ,'url' =>$url,'name' => $activity->name,'description'=>$activity->description);
    array_push($activities,$aux);
    //print_object($aux);
  }
  return $activities;
}
function getTemplateName($activityid){
  global $DB;
  $template=$DB->get_record_sql('SELECT tmp.templatename FROM {template} as tmp
    INNER JOIN {template_activities} as tmp_act on tmp.id = tmp_act.activitiesid
    where tmp_act.active=1 and tmp_act.activitiesid=?',array($activityid));
  return $template->templatename;
}

function getComponents($activitiesid){
  global $DB;
  $data=(object)array();
  $components =$DB->get_records_sql('SELECT * FROM {template_activities} as template_act
    INNER JOIN {components} as comp on template_act.id=comp.template_activities_id
    WHERE template_act.activitiesid=?',array($activitiesid));
  foreach ($components as $component) {
    if($component->typecomponents_id==1){
      $video=$DB->get_record('video', array('idcomponent'=>$component->id));
      $data->video=$video;
    }
    if($component->typecomponents_id==2){
      $presentacion=$DB->get_record('viewer', array('idcomponent'=>$component->id));
      $data->presentacion=$presentacion;
    }
    //TODO: completar con los demás elementos
  }
  return $data;
}
