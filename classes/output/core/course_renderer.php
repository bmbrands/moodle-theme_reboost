<?php

namespace theme_reboost\output\core;

use coursecat;
use coursecat_helper;
use context_course;
use context_system;
use core_course_renderer;
use html_writer;
use single_select;
use lang_string;
use moodle_url;
use stdClass;
use completion_info;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/renderer.php');

class course_renderer extends \core_course_renderer {

    private $catcourses;
    private $categories;
    private $firstcat;
	// Course search form

    public function course_search_form($value = '', $format = 'plain') {
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

        $data = (object) [
            'searchurl' => (new moodle_url('/course/search.php'))->out(false),
            'id' => $formid,
            'inputid' => $inputid,
            'inputsize' => $inputsize,
            'value' => $value
        ];

        return $this->render_from_template('theme_reboost/course_search_form', $data);
    }

        /**
     * Returns HTML to print list of available courses for the frontpage
     *
     * @return string
     */
    public function frontpage_available_courses() {
        global $CFG;
        require_once($CFG->libdir. '/coursecatlib.php');

        $chelper = new coursecat_helper();
        $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED)->
                set_courses_display_options(array(
                    'recursive' => true,
                    'limit' => $CFG->frontpagecourselimit,
                    'viewmoreurl' => new moodle_url('/course/index.php'),
                    'viewmoretext' => new lang_string('fulllistofcourses')));

        $chelper->set_attributes(array('class' => 'frontpage-course-list-all'));
        $courses = coursecat::get(0)->get_courses($chelper->get_courses_display_options());
        $totalcount = coursecat::get(0)->get_courses_count($chelper->get_courses_display_options());
        if (!$totalcount && !$this->page->user_is_editing() && has_capability('moodle/course:create', context_system::instance())) {
            // Print link to create a new course, for the 1st available category.
            return $this->add_new_course_button();
        }
        return $this->frontpage_courseboxes($chelper, $courses);
    }

    public function frontpage_courseboxes(coursecat_helper $chelper, $courses) {
        global $CFG, $OUTPUT, $DB;

        if (empty($this->categories)) {
            $this->categories = $DB->get_records('course_categories', array('visible' => 1));
        }

        $template = new stdClass();
        $template->coursecount = 0;
        $template->courses = array();

        $mycourses = enrol_get_my_courses();

        $allcourses = optional_param('allcourses', 0, PARAM_INT);
        $showdefault = 6;

        foreach ($courses as $course) {
            if (!$allcourses && $template->coursecount >= $showdefault) {
                continue;
            }
            $rendercourse = new stdClass();
            if ($course instanceof stdClass) {
                require_once($CFG->libdir. '/coursecatlib.php');
                $course = new course_in_list($course);
            }
            // Get course name.
            $rendercourse->coursename = $chelper->get_course_formatted_name($course);
            // Get course link.
            $rendercourse->courselink = new moodle_url('/course/view.php', array('id' => $course->id));
            // Get course image
            $rendercourse->courseimage = $OUTPUT->image_url('default-course-image', 'theme');
            foreach ($course->get_course_overviewfiles() as $file) {
                $isimage = $file->is_valid_image();
                if ($isimage) {
                    $rendercourse->courseimage = $CFG->wwwroot. '/pluginfile.php/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                        $file->get_filearea(). $file->get_filepath(). $file->get_filename();
                }
            }
            // Get course description.
            if ($course->has_summary()) {
                $rendercourse->coursedescription = strip_tags($chelper->get_course_formatted_summary($course));
            }
            // Get course dates.
            if ($course->startdate) {
                $rendercourse->startdate = userdate($course->startdate, get_string('strftimedate'));
            }
            // Get course category name
            if ($catid = $course->__get('category')) {
                if (array_key_exists($catid, $this->categories)) {
                    $category = $this->categories[$catid];
                    $rendercourse->category = $category->name;
                } 
            }

            // Get the course progress
            $rendercourse->hasprogress = false;
            $completion = new completion_info($course);
            if (array_key_exists($course->id, $mycourses) && $completion->is_enabled()) {
                $rendercourse->hasprogress = true;
            }

            if (!isloggedin() || isguestuser()) {
                $rendercourse->hasprogress = false;
            }

            $rendercourse->progress = $this->course_progress($course->id);

            // display course contacts. See course_in_list::get_course_contacts()
            if ($course->has_course_contacts()) {
                $rendercourse->contacts = array();
                foreach ($course->get_course_contacts() as $userid => $coursecontact) {
                    $contact = new stdClass();
                    $contact->role = $coursecontact['rolename'];
                    $contact->name = $coursecontact['username'];
                    $contact->url = new moodle_url('/user/view.php', array('id' => $userid, 'course' => SITEID));
                    $rendercourse->contacts[] = $contact;
                }
            }
            $rendercourses[] = $rendercourse;
            $template->coursecount++;
        }

        $template->courses = $rendercourses;
        $template->allcourses = $allcourses;

        $template->hasmorecourses = !$allcourses && count($courses) > $showdefault;
        $template->showallcoursesurl = new moodle_url('/course/');
        return $OUTPUT->render_from_template('theme_reboost/reboost-availablecourses', $template);
    }

    public function course_progress($courseid, $user = null, $method = 'count') {
        global $DB, $USER;

        if (!$user) {
            $user = $USER;
        }

        $coursemodules = $DB->get_records('course_modules', array('completion' => 1, 'course' => $courseid, 'visible' =>1));
        
        $total = count($coursemodules);

        $done = 0;
        if ($total > 0 ) {
            $completed = $DB->get_records('course_modules_completion', array('userid' => $user->id, 'completionstate' => 1));
            if (count($completed) > 0) {
                foreach ($completed as $complete) {
                    if (array_key_exists($complete->coursemoduleid, $coursemodules)) {
                        $done++;
                    }
                }
            } else {
                return 0;
            }
        } else {
            return 0;
        }

        if ($done) {
            return round(($done / $total) * 100);
        } else {
            return 0;
        }
    }

}
