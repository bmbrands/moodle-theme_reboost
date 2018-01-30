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

class courseheader implements renderable, templatable {
    private $reboost;

    public function __construct(\theme_reboost\reboost $reboost) {
        $this->reboost = $reboost;
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
        return $data;
    }
}