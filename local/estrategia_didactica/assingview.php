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
    require_once($CFG->dirroot . '/mod/assign/locallib.php');
    $id = required_param('id', PARAM_INT);
    $activityid= optional_param('activityid', 0, PARAM_INT);
    if($id){
      if (! $assign = $DB->get_record("assign", array("id" => $id))) {
          print_error('invalidforumid', 'forum');
      }
      if (! $course = $DB->get_record("course", array("id" => $assign->course))) {
          print_error('coursemisconf');
      }
      if (! $cm = $DB->get_record("course_modules", array("course" => $course->id,"module"=>1,"instance"=>$id))) {
          print_error('invalidcoursemodule');
      }
      $coursecontext = context_course::instance($course->id);
      require_login($course);
    }else {
      print_error('missingparameter');
    }
    $context = context_module::instance($cm->id);
    require_capability('mod/assign:view', $coursecontext);

    $assign = new assign($context, $cm, $course);
    $urlparams = array('id' => $id,
                      'action' => optional_param('action', '', PARAM_ALPHA),
                      'rownum' => optional_param('rownum', 0, PARAM_INT),
                      'useridlistid' => optional_param('useridlistid', $assign->get_useridlist_key_id(), PARAM_ALPHANUM));
    $url = new moodle_url('/local/estrategia_didactica/assignview.php', $urlparams);
    $PAGE->set_url($url);
    $PAGE->navbar->add('Estrategia Didactica');
    // Update module completion status.
    $assign->set_module_viewed();
    // Apply overrides.
    $assign->update_effective_access($USER->id);
    // Get the assign class to
    // render the page.
    echo $assign->view(optional_param('action', '', PARAM_ALPHA));
    echo "hola desde assignment";
