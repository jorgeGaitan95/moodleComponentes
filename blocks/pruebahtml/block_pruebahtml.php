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
 * Version details
 *
 * @package    block_pruebahtml
 * @copyright  2017 Jorge Gaitán (jorgegaitan903@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
class block_pruebahtml extends block_base {
    public function init() {
        $this->title = get_string('simplehtml', 'block_simplehtml');

    }

    public function get_content() {
      if ($this->content !== null) {
        return $this->content;
      }

      $this->content         =  new stdClass;
      $this->content->text   = 'Prueba para añadir un pligin tipo block dentro de moodle!';
      $this->content->text .='<p><a href="/moodle/local/flowdiagram/index.php">hola</a></p>';
      $this->content->footer = print_object($this->page->course->id);

      return $this->content;
    }
}
