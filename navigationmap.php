<?php
// This file is part of the Arup Course Management system
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
 * Navigation mapping.
 *
 * @package   theme_reboost
 * @copyright 2018 Moodle
 * @author    Bas Brands
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../config.php';

require_login();

$PAGE->set_context(context_course::instance('3'));
$PAGE->set_url('/theme/reboost/navigationmap.php');
$PAGE->set_pagelayout('course');
$PAGE->navbar->add("Moodle navigation", new moodle_url('/theme/reboost/navigationmap.php'));
$PAGE->blocks->show_only_fake_blocks();

$PAGE->set_title("Moodle navigation structure");
$PAGE->set_heading("List of navigation nodes");

echo $OUTPUT->header();

echo $OUTPUT->region_main_settings_menu();

$settingsnode = $PAGE->settingsnav->find('courseadmin', navigation_node::TYPE_COURSE);

//$items = $PAGE->navbar->get_items()

$node = $PAGE->navigation->find_active_node();

echo "Node";

echo $node->type;

// echo "<ul>";
// foreach ($node->children as $menuitem) {
//     echo "<li>" . $menuitem->key . "</li>";
// }
// echo "</ul>";

echo "Hello Bas";

echo $OUTPUT->footer();