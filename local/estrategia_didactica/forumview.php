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
    $id= optional_param('id', 0, PARAM_INT);
    $mode        = optional_param('mode', 0, PARAM_INT);     // Forum ID
    if($id){
      if (! $forum = $DB->get_record("forum", array("id" => $id))) {
          print_error('invalidforumid', 'forum');
      }
      if (! $course = $DB->get_record("course", array("id" => $forum->course))) {
          print_error('coursemisconf');
      }
      $coursecontext = context_course::instance($course->id);
      require_login($course);
    }else {
      print_error('missingparameter');
    }
    global $USER;
    $PAGE->set_title($forum->name);
    $PAGE->add_body_class('forumtype-'.$forum->type);
    $PAGE->add_body_class('path-mod-forum');
    $PAGE->set_pagelayout('base');
    $PAGE->requires->css(new moodle_url('/local/estrategia_didactica/style/style.css'),true);
    $actividades = getActivities($USER->id,$course->id);
    $data=(object)array();
    $data->activities=$actividades;
    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('local_estrategia_didactica/tabs', $data);
    echo $OUTPUT->heading(format_string($forum->name), 2);
    if ($forum->type == 'single') {
        $discussion = NULL;
        $discussions = $DB->get_records('forum_discussions', array('forum'=>$forum->id), 'timemodified ASC');
        if (!empty($discussions)) {
            $discussion = array_pop($discussions);
        }
        if ($discussion) {
            //Muestra las discuciones anidadas por defecto
            if($mode===0){
              $mode=3;
            }//
            if ($mode) {
                set_user_preference("forum_displaymode", $mode);
            }
            $displaymode = get_user_preferences("forum_displaymode", $CFG->forum_displaymode);
            print_mode_form($forum->id, $displaymode, $forum->type);
        }
        if (! $post = get_post_full($discussion->firstpost)) {
            print_error('cannotfindfirstpost', 'forum');
        }
        if ($mode) {
            set_user_preference("forum_displaymode", $mode);
        }

        $canreply    = true;
        $canrate     = false;
        $displaymode = get_user_preferences("forum_displaymode", $CFG->forum_displaymode);
        echo '&nbsp;'; // this should fix the floating in FF
        print_discussion($course, $forum, $discussion, $post, $displaymode, $canreply, $canrate);
    }
    // Add the subscription toggle JS.
    //$PAGE->requires->yui_module('moodle-mod_forum-subscriptiontoggle', 'Y.M.mod_forum.subscriptiontoggle.init');

    echo $OUTPUT->footer($course);
