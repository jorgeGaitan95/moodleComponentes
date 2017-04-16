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
    require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
    require_once(dirname(__FILE__).'/lib.php');

    $id = required_param('id', PARAM_INT);

    if($id){
      if (! $chat = $DB->get_record("chat", array("id" => $id))) {
          print_error('invalidforumid', 'forum');
      }
      if (! $course = $DB->get_record("course", array("id" => $chat->course))) {
          print_error('coursemisconf');
      }
      $coursecontext = context_course::instance($course->id);
      require_login($course);
    }else {
      print_error('missingparameter');
    }
    global $USER;
    $PAGE->set_title($chat->name);
    $PAGE->set_pagelayout('base');
    $PAGE->requires->css(new moodle_url('/local/estrategia_didactica/style/style.css'),true);
    $actividades = getActivities($USER->id,$course->id);
    $data=(object)array();
    $data->activities=$actividades;
    $data->chatid=$chat->id;
    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('local_estrategia_didactica/chatview', $data);
    echo $OUTPUT->footer($course);
