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

class renderer extends \theme_reboost\output\renderer {

    public function render_courseicon(\theme_reboost\output\reboost\courseicon $courseicon) {
        // Call the export_for_template function from class reboostui.
        $templatevars = $courseicon->export_for_template($this);

        return $this->render_from_template('theme_reboost/reboost-courseicon', $templatevars);
    }

    public function render_defaultnavicon(\theme_reboost\output\reboost\defaultnavicon $defaultnavicon) {
        // Call the export_for_template function from class reboostui.
        $templatevars = $defaultnavicon->export_for_template($this);

        return $this->render_from_template('theme_reboost/reboost-defaultnavicon', $templatevars);
    }

    public function render_fullheader(\theme_reboost\output\reboost\fullheader $fullheader) {
        $templatevars = $fullheader->export_for_template($this);
        return $this->render_from_template('theme_reboost/reboost-fullheader', $templatevars);
    }
}