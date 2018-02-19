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
 * Reboost specific renderers.
 *
 * @package   theme_reboost
 * @copyright 2018 Moodle
 * @author    Bas Brands
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_reboost\output\reboost;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;
use action_menu;
use navigation_node;
use moodle_url;
use action_link;
use pix_icon;

class courseheader implements renderable, templatable {
    private $reboost;
    private $coursemenus;

    public function __construct(\theme_reboost\reboost $reboost) {
        $this->reboost = $reboost;

        $this->coursemenus =
            ['users' =>
                ['review',
                 'manageinstances',
                 'divider',
                 'groups',
                 'divider',
                 'override',
                 'permissions',
                 'divider',
                 'otherusers'
                ]
            ];
    }
    /**
     * Export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $PAGE, $COURSE;
        $data = new stdClass();
        $data->fullname = $COURSE->fullname;

        $words = preg_split("/\s+/", $COURSE->fullname);
        $maxcaps = 2;
        $count = 0;
        $abbr = '';
        foreach ($words as $word) {
            if ($count < $maxcaps) {
                $chr = mb_substr($word, 0, 1, "UTF-8");
                if (mb_strtolower($chr, "UTF-8") != $chr) {
                    $count++;
                    $abbr .= $chr;
                }
            }
        }
        $data->abbr = $abbr;
        $data->iconcolornr = $COURSE->id;
        $data->navbar = $output->navbar();
        $data->navtabs = $this->courseheadermenu($output);
        return $data;
    }

    private function courseheadermenu(renderer_base $output) {
        global $PAGE;
        $menu = new action_menu();
        $settingsnode = $PAGE->settingsnav->find('courseadmin', navigation_node::TYPE_COURSE);
        if ($settingsnode) {
            // Build an action menu based on the visible nodes from this navigation tree.
            return $this->courseheadernav($settingsnode);
        }
    }

    private function courseheadernav(navigation_node $node) {
        $navtabs = new stdClass();
        foreach ($node->children as $menuitem) {
            foreach ($this->coursemenus as $key => $items) {
                if ($menuitem->key === $key) {
                    $navtabs->dropdowns[] = $this->build_courseheader_dropdown($menuitem);
                }
            }
        }
        return $navtabs;
    }

    private function build_courseheader_dropdown(navigation_node $node) {
        $dropdown = new stdClass();
        $dropdown->title = $node->text;
        $items = [];

        // Get the nav items
        foreach ($node->children as $navsub) {
            if (in_array($navsub->key, $this->coursemenus[$node->key])) {
                $dropdownitem = new stdClass();
                $dropdownitem->text = $navsub->text;
                $dropdownitem->action = $navsub->action;
                $dropdownitem->key = $navsub->key;
                $items[$navsub->key] = $dropdownitem;
            }
        }
        // Sort the nav items and add diveders
        foreach ($this->coursemenus[$node->key] as $dropdownitem) {
            if ($dropdownitem === 'divider') {
                $dropdownitem = new stdClass();
                $dropdownitem->isdivider = true;
                $dropdown->items[] = $dropdownitem;
                continue;
            }
            if (isset($items[$dropdownitem])) {
                $dropdown->items[] = $items[$dropdownitem];
            }
        }
        return $dropdown;
    }
}