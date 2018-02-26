<?php
// This file is part of The Bootstrap Moodle theme
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
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_reboost
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_reboost\output\core_course\management;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/course/classes/management_renderer.php");

use html_writer;
use coursecat;
use moodle_url;
use course_in_list;
use lang_string;
use context_system;
use stdClass;
use action_menu;
use action_menu_link_secondary;

class renderer extends \core_course_management_renderer {

    public function grid_start($id = null, $class = null) {
        $gridclass = 'grid-start grid-row-r d-flex flex-wrap row';
        if (is_null($class)) {
            $class = $gridclass;
        } else {
            $class .= ' ' . $gridclass;
        }
        $attributes = array();
        if (!is_null($id)) {
            $attributes['id'] = $id;
        }
        return html_writer::start_div($class, $attributes);
    }

    public function grid_column_start($size, $id = null, $class = null) {

        if ($id == 'course-detail') {
            $size = 12;
            $bootstrapclass = 'col-md-'.$size;
        } else {
            $bootstrapclass = 'd-flex flex-wrap px-3 mb-3';
        }

        $yuigridclass =  "col-sm";

        if (is_null($class)) {
            $class = $yuigridclass . ' ' . $bootstrapclass;
        } else {
            $class .= ' ' . $yuigridclass . ' ' . $bootstrapclass;
        }
        $attributes = array();
        if (!is_null($id)) {
            $attributes['id'] = $id;
        }
        return html_writer::start_div($class . " grid_column_start", $attributes);
    }

    public function course_detail(course_in_list $course) {
        $details = \core_course\management\helper::get_course_detail_array($course);
        $fullname = $details['fullname']['value'];

        $html  = html_writer::start_div('course-detail card');
        $html .= html_writer::start_div('card-header');
        $html .= html_writer::tag('h3', $fullname, array('id' => 'course-detail-title', 'class'=>'card-title', 'tabindex' => '0'));
        $html .= html_writer::end_div();
        $html .= html_writer::start_div('card-body');
        $html .= $this->course_detail_actions($course);
        foreach ($details as $class => $data) {
            $html .= $this->detail_pair($data['key'], $data['value'], $class);
        }
        $html .= html_writer::end_div();
        $html .= html_writer::end_div();
        return $html;
    }

    public function course_search_forms($value = '', $format = 'plain') {
        static $count = 0;
        $formid = 'coursesearch';
        if ((++$count) > 1) {
            $formid .= $count;
        }

        switch ($format) {
            case 'navbar' :
                $formid = 'coursesearchnavbar';
                $inputid = 'navsearchbox';
                $inputsize = 20;
                break;
            case 'short' :
                $inputid = 'shortsearchbox';
                $inputsize = 12;
                break;
            default :
                $inputid = 'coursesearchbox';
                $inputsize = 30;
        }

        $strsearchcourses = get_string("searchcourses");
        $searchurl = new moodle_url('/course/management.php');

        $output = html_writer::start_div('row');
        $output .= html_writer::start_div('col-md-12');
        $output .= html_writer::start_tag('form', array('class'=>'card', 'id' => $formid, 'action' => $searchurl, 'method' => 'get'));
        $output .= html_writer::start_tag('fieldset', array('class' => 'coursesearchbox invisiblefieldset'));
        $output .= html_writer::tag('div', $this->output->heading($strsearchcourses.': ', 2, 'm-0'), array('class'=>'card-header'));
        $output .= html_writer::start_div('card-body');
        $output .= html_writer::start_div('input-group');
        $output .= html_writer::empty_tag('input', array('class'=>'form-control', 'type' => 'text', 'id' => $inputid,
            'size' => $inputsize, 'name' => 'search', 'value' => s($value)));
        $output .= html_writer::start_tag('span', array('class' =>'input-group-btn'));
        $output .= html_writer::tag('button', get_string('go'), array('class'=>'btn btn-primary', 'type' => 'submit'));
        $output .= html_writer::end_tag('span');
        $output .= html_writer::end_div();
        $output .= html_writer::end_div();
        $output .= html_writer::end_tag('fieldset');
        $output .= html_writer::end_tag('form');
        $output .= html_writer::end_div();
        $output .= html_writer::end_div();

        return $output;
    }

    public function category_listing(coursecat $category = null) {

        if ($category === null) {
            $selectedparents = array();
            $selectedcategory = null;
        } else {
            $selectedparents = $category->get_parents();
            $selectedparents[] = $category->id;
            $selectedcategory = $category->id;
        }
        $catatlevel = \core_course\management\helper::get_expanded_categories('');
        $catatlevel[] = array_shift($selectedparents);
        $catatlevel = array_unique($catatlevel);

        $listing = coursecat::get(0)->get_children();

        $attributes = array(
            'class' => 'ml-1',
            'role' => 'tree',
            'aria-labelledby' => 'category-listing-title'
        );

        $html  = html_writer::start_div('category-listing card w-100');
        $html .= html_writer::tag('h3', get_string('categories'), array('class'=>'card-header', 'id' => 'category-listing-title'));
        $html .= html_writer::start_div('card-body');
        $html .= $this->category_listing_actions($category);
        $html .= html_writer::start_tag('ul', $attributes);
        foreach ($listing as $listitem) {
            // Render each category in the listing.
            $subcategories = array();
            if (in_array($listitem->id, $catatlevel)) {
                $subcategories = $listitem->get_children();
            }
            $html .= $this->category_listitem(
                $listitem,
                $subcategories,
                $listitem->get_children_count(),
                $selectedcategory,
                $selectedparents
            );
        }
        $html .= html_writer::end_tag('ul');
        $html .= $this->category_bulk_actions($category);
        $html .= html_writer::end_div();
        $html .= html_writer::end_div();
        return $html;
    }

    public function course_listing(coursecat $category = null, course_in_list $course = null, $page = 0, $perpage = 20) {

        if ($category === null) {
            $html = html_writer::start_div('select-a-category');
            $html .= html_writer::tag('h3', get_string('courses'),
                array('id' => 'course-listing-title', 'tabindex' => '0'));
            $html .= $this->output->notification(get_string('selectacategory'), 'notifymessage');
            $html .= html_writer::end_div();
            return $html;
        }

        $page = max($page, 0);
        $perpage = max($perpage, 2);
        $totalcourses = $category->coursecount;
        $totalpages = ceil($totalcourses / $perpage);
        if ($page > $totalpages - 1) {
            $page = $totalpages - 1;
        }
        $options = array(
            'offset' => $page * $perpage,
            'limit' => $perpage
        );
        $courseid = isset($course) ? $course->id : null;
        $class = '';
        if ($page === 0) {
            $class .= ' firstpage';
        }
        if ($page + 1 === (int)$totalpages) {
            $class .= ' lastpage';
        }

        $html  = html_writer::start_div('card course-listing w-100'.$class, array(
            'data-category' => $category->id,
            'data-page' => $page,
            'data-totalpages' => $totalpages,
            'data-totalcourses' => $totalcourses,
            'data-canmoveoutof' => $category->can_move_courses_out_of() && $category->can_move_courses_into()
        ));
        $html .= html_writer::tag('h3', $category->get_formatted_name(),
            array('id' => 'course-listing-title', 'tabindex' => '0', 'class'=>'card-header'));
        $html .= html_writer::start_div('card-body');
        $html .= $this->course_listing_actions($category, $course, $perpage);
        $html .= $this->listing_pagination($category, $page, $perpage);
        $html .= html_writer::start_tag('ul', array('class' => 'ml', 'role' => 'group'));
        foreach ($category->get_courses($options) as $listitem) {
            $html .= $this->course_listitem($category, $listitem, $courseid);
        }
        $html .= html_writer::end_tag('ul');
        $html .= $this->listing_pagination($category, $page, $perpage, true);
        $html .= $this->course_bulk_actions($category);
        $html .= html_writer::end_div();
        $html .= html_writer::end_div();
        return $html;
    }

    /**
     * Displays a search result listing.
     *
     * @param array $courses The courses to display.
     * @param int $totalcourses The total number of courses to display.
     * @param course_in_list $course The currently selected course if there is one.
     * @param int $page The current page, starting at 0.
     * @param int $perpage The number of courses to display per page.
     * @param string $search The string we are searching for.
     * @return string
     */
    public function search_listing(array $courses, $totalcourses, course_in_list $course = null, $page = 0, $perpage = 20,
        $search = '') {
        $page = max($page, 0);
        $perpage = max($perpage, 2);
        $totalpages = ceil($totalcourses / $perpage);
        if ($page > $totalpages - 1) {
            $page = $totalpages - 1;
        }
        $courseid = isset($course) ? $course->id : null;
        $first = true;
        $last = false;
        $i = $page * $perpage;

        $html  = html_writer::start_div('course-listing w-100', array(
            'data-category' => 'search',
            'data-page' => $page,
            'data-totalpages' => $totalpages,
            'data-totalcourses' => $totalcourses
        ));
        $html .= html_writer::tag('h3', get_string('courses'));
        $html .= $this->search_pagination($totalcourses, $page, $perpage);
        $html .= html_writer::start_tag('ul', array('class' => 'ml'));
        foreach ($courses as $listitem) {
            $i++;
            if ($i == $totalcourses) {
                $last = true;
            }
            $html .= $this->search_listitem($listitem, $courseid, $first, $last);
            $first = false;
        }
        $html .= html_writer::end_tag('ul');
        $html .= $this->search_pagination($totalcourses, $page, $perpage, true, $search);
        $html .= $this->course_search_bulk_actions();
        $html .= html_writer::end_div();
        return $html;
    }

    protected function detail_pair($key, $value, $class ='') {
        $html = html_writer::start_div('detail-pair row yui3-g '.preg_replace('#[^a-zA-Z0-9_\-]#', '-', $class));
        $html .= html_writer::div(html_writer::span($key), 'pair-key col-md-4 yui3-u-1-4 font-weight-bold');
        $html .= html_writer::div(html_writer::span($value), 'pair-value col-md-8 yui3-u-3-4');
        $html .= html_writer::end_div();
        return $html;
    }

    public function course_detail_actions(course_in_list $course) {
        $actions = \core_course\management\helper::get_course_detail_actions($course);
        if (empty($actions)) {
            return '';
        }
        $options = array();
        foreach ($actions as $action) {
            $options[] = $this->action_link($action['url'], $action['string'], null, array('class'=>'btn btn-sm btn-secondary mr-1 mb-3'));
        }
        return html_writer::div(join('', $options), 'listing-actions course-detail-listing-actions');
    }

}
