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
// Setting context for the page.
$PAGE->set_context(context_system::instance());
// URL is created and then set for the page navigation.
// Heading, headers, page layout.
$PAGE->set_title('Variabilidad2');
$PAGE->set_heading('Actividad Formacion');
$PAGE->set_pagelayout('standard');
$PAGE->requires->css(new moodle_url('/local/estrategia_didactica/videojs-transcript.css'),true);
$PAGE->requires->css(new moodle_url('/local/estrategia_didactica/prueba.css'),true);
$PAGE->requires->css(new moodle_url('/local/estrategia_didactica/video-js.min.css'),true);
$PAGE->requires->js(new moodle_url('/media/player/videojs/amd/build/video-lazy.min.js'),true);
$PAGE->requires->js(new moodle_url('/local/estrategia_didactica/videojs-transcript.js'),true);
$PAGE->requires->js(new moodle_url('/media/player/videojs/amd/build/video-lazy.min.js'),true);
$PAGE->requires->js(new moodle_url('/local/estrategia_didactica/pdf.js'),true);
$PAGE->requires->js(new moodle_url('/local/estrategia_didactica/app.js'),true);
echo $OUTPUT->header();
// Displaying basic content.
//$OUTPUT->content='<h1>Hola esta es la actividad de formacion</h1>';

echo $OUTPUT->render_from_template('local_estrategia_didactica/actividad_formacion_v2', context_system::instance());
// Display the footer.
echo $OUTPUT->footer();
