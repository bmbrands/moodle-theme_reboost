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
 * Settings file.
 *
 * @package   theme_reboost
 * @copyright 2018 Moodle
 * @author    Bas Brands
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
                                                             
defined('MOODLE_INTERNAL') || die();                                                                                                
                                                                                     
if ($ADMIN->fulltree) 
{        
	$settings = new theme_boost_admin_settingspage_tabs('themesettingreboost', get_string('configtitle', 'theme_boost'));

	    // Advanced settings.
    $page = new admin_settingpage('theme_reboost_advanced', get_string('advancedsettings', 'theme_reboost'));

    // Raw SCSS to include before the content.
    $setting = new admin_setting_scsscode('theme_reboost/scsspre',
        get_string('rawscsspre', 'theme_reboost'), get_string('rawscsspre_desc', 'theme_reboost'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Raw SCSS to include after the content.
    $setting = new admin_setting_scsscode('theme_reboost/scss', get_string('rawscss', 'theme_reboost'),
        get_string('rawscss_desc', 'theme_reboost'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);

}