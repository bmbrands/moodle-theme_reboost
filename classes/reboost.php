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
 * Reboost stuff.
 *
 * @package   theme_reboost
 * @copyright 2018 Moodle
 * @author    Bas Brands
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_reboost;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use moodle_url;
use Exception;
use moodle_exception;
use navigation_node;
use flat_navigation_node;

class reboost {

    private $renderer;
    /**
     * Constructor.
     *
     * Make sure no renderer functions are being called in the constructor
     *
     */
    public function __construct() {
        global $PAGE, $USER;
        $this->renderer = $PAGE->get_renderer('theme_reboost', 'reboost');
    }

    /**
     * Magic Getter function
     * @param string partial function name after _get
     * @return result of function.
     */
    public function __get($name) {
        if (method_exists($this, "get_{$name}")) {
            return $this->{"get_{$name}"}();
        }
        if (!isset($this->{$name})) {
            throw new Exception('Undefined property ' .$name. ' requested');
        }
        return $this->{$name};
    }

    public function removenav() {
        global $PAGE;
        $flatnav = $PAGE->flatnav;
        foreach ($flatnav as $action) {
            $flatnav->remove($action->key);
        }
    }

    public function defaultnav() {
        global $PAGE;
        $url = new moodle_url('/index.php', ['redirect' => 0]);
        $this->adddefaultnav($url, get_string('home'), 'home');
        
        $url = new moodle_url('/my');
        $this->adddefaultnav($url, get_string('myhome'), 'tachometer');

        $url = new moodle_url('/calendar');
        $this->adddefaultnav($url, get_string('calendar', 'calendar'), 'calendar');

        $url = new moodle_url('/files', ['redirect' => 0]);
        $this->adddefaultnav($url, get_string('privatefiles'), 'home');
    }

    private function adddefaultnav($url, $string, $icon) {
        global $PAGE;
        $navitem = new stdClass;
        $navitem->name = $string;
        $navitem->icon = $icon;
        $nameicon = $this->defaultnavicon($navitem);
        $node = navigation_node::create($nameicon, $url);
        $flat = new flat_navigation_node($node, 0);
        $flat->key = $navitem->name;
        $PAGE->flatnav->add($flat);
    }

    public function addmycourses() {
        global $PAGE;
        $mycourses = enrol_get_my_courses();
        foreach ($mycourses as $mycourse) {
            $url = new moodle_url('/course/view.php', ['id' => $mycourse->id]);
            $coursenameicon = $this->courseicon($mycourse);
            $addacourse = navigation_node::create($coursenameicon, $url);
            $flat = new flat_navigation_node($addacourse, 0);
            $flat->key = 'course-' . $mycourse->id;
            $PAGE->flatnav->add($flat);
        }
    }

    private function courseicon($mycourse) {
        $courseicon = new \theme_reboost\output\reboost\courseicon($mycourse);
        return $this->renderer->render($courseicon);
    }

    private function defaultnavicon($navitem) {
        $defaultnavicon = new \theme_reboost\output\reboost\defaultnavicon($navitem);
        return $this->renderer->render($defaultnavicon);
    }

    public function reboostheader() {
        $fullheader = new \theme_reboost\output\reboost\fullheader($this);
        return $this->renderer->render($fullheader);
    }
}