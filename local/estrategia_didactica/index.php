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
$PAGE->set_url('/local/estrategia_didactica/index.php', array('id'=>$id));
if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('invalidcourseid');
}
$coursecontext = context_course::instance($id);
require_login($course);
// Setting context for the page.
$PAGE->set_context($coursecontext);
global $COURSE,$USER;
//Obtner el rol del usuario
/*$context = get_context_instance(CONTEXT_COURSE,$COURSE->id);
if ($roles = get_user_roles($context, $USER->id)) {
foreach ($roles as $role) {
  print_object($role);
}
}*/
$video=$DB->get_record('video_components',array('id'=>1));
$actividades = getActivities($USER->id,$COURSE->id);
print_object($actividades);
// URL is created and then set for the page navigation.
// Heading, headers, page layout.
$PAGE->set_title('Flow Diagram');
$PAGE->set_heading('Flow Diagram Prueba');
$PAGE->set_pagelayout('standard');
$PAGE->requires->css(new moodle_url('/local/estrategia_didactica/style/style.css'),true);
$PAGE->requires->css(new moodle_url('/local/estrategia_didactica/style/videojs-transcript.css'),true);
$PAGE->requires->css(new moodle_url('/local/estrategia_didactica/style/prueba.css'),true);
$PAGE->requires->css(new moodle_url('/local/estrategia_didactica/style/video-js.min.css'),true);
$PAGE->requires->js(new moodle_url('/media/player/videojs/amd/build/video-lazy.min.js'),true);
$PAGE->requires->js(new moodle_url('/local/estrategia_didactica/js/videojs-transcript.js'),true);
$PAGE->requires->js(new moodle_url('/local/estrategia_didactica/js/jquery.min.js'),true);
$PAGE->requires->js(new moodle_url('/local/estrategia_didactica/js/app.js'),true);
echo $OUTPUT->header();
// Displaying basic content.
//$OUTPUT->content='<h1>Hola esta es la actividad de formacion</h1>';
echo $OUTPUT->render_from_template('local_estrategia_didactica/actividad_formacion_v1', $video);
// Display the footer.
echo $OUTPUT->footer();
