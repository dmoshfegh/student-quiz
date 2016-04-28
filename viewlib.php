<?php

require_once($CFG->dirroot . '/question/editlib.php');
require_once(dirname(__FILE__) . '/locallib.php');

require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/attemptlib.php');

class studentquiz_view {
    /** @var string generated student quiz placeholder */
    const GENERATE_QUIZ_PLACEHOLDER = 'quiz';
    /** @var string generated student quiz intro */
    const GENERATE_QUIZ_INTRO = 'Studentquiz';
    /** @var string generated student quiz overduehandling */
    const GENERATE_QUIZ_OVERDUEHANDLING = 'autosubmit';
    /** @var int default course section id for the orphaned activities */
    const COURSE_SECTION_ID = 999;
    /** @var string default course section name for the orphaned activities */
    const COURSE_SECTION_NAME = 'studentquiz quizzes';
    /** @var string default course section summary for the orphaned activities */
    const COURSE_SECTION_SUMMARY = 'all student quizzes';
    /** @var string default course section summaryformat for the orphaned activities */
    const COURSE_SECTION_SUMMARYFORMAT = 1;
    /** @var string default course section visible for the orphaned activities */
    const COURSE_SECTION_VISIBLE = false;
    /** @var stdClass the course_module settings from the database. */
    protected $cm;
    /** @var stdClass the course settings from the database. */
    protected $course;
    /** @var context the quiz context. */
    protected $context;
    /** @var category the default category */
    protected $category;
    /** @var  bool has question ids found */
    protected $hasquestionids;
    /** @var object pagevars */
    protected $qbpagevar;


    public function __construct($cmid) {
        global $DB;
        if (!$this->cm = get_coursemodule_from_id('studentquiz', $cmid)) {
            throw new moodle_studentquiz_view_exception($this, 'invalidcoursemodule');
        }
        if (!$this->course = $DB->get_record('course', array('id' => $this->cm->course))) {
            throw new moodle_studentquiz_view_exception($this, 'coursemisconf');
        }

        $this->context = context_module::instance($this->cm->id);
        $this->category = question_get_default_category($this->context->id);
    }

    private function start_quiz($ids) {
        if($ids) {
            $this->hasquestionids = true;
            return $this->generate_quiz_activity($ids);
        } else {
            $this->hasquestionids = false;
        }
    }

    private function generate_quiz_activity($ids) {
        $quiz = $this->get_standard_quiz_setup();
        $quiz->coursemodule = $this->create_quiz_course_module($quiz->course);

        $this->set_course_section_information($quiz->course, $quiz->coursemodule);

        $quiz->instance =  $this->quiz_add_instance($quiz);

        foreach($ids as $key){
            quiz_add_quiz_question($key, $quiz, 0);
            quiz_update_sumgrades($quiz);
            quiz_set_grade($quiz->sumgrades, $quiz);
        }

        rebuild_course_cache($quiz->course, true);
        return $quiz->coursemodule;
    }

    private function set_course_section_information($courseid, $coursemoudleid){
        global $DB;
        $coursesection = $this->get_course_section();

        if(!$coursesection) {
            $coursesectionid = $this->create_course_section($courseid);
            $sequence = array();
        } else {
            $coursesectionid = $coursesection->id;
            $sequence = explode(',', $coursesection->sequence);
        }

        $sequence[] = $coursemoudleid;
        sort($sequence);


        $DB->set_field('course_modules', 'section', $coursesectionid, array('id' => $coursemoudleid));
        $DB->set_field('course_sections', 'sequence', implode(',', $sequence), array('id' => $coursesectionid));
    }

    private function create_course_section($courseid) {
        global $DB;
        $coursesection = new stdClass();
        $coursesection->course = $courseid;
        $coursesection->section = studentquiz_view::COURSE_SECTION_ID;
        $coursesection->name = studentquiz_view::COURSE_SECTION_NAME;
        $coursesection->summary = studentquiz_view::COURSE_SECTION_SUMMARY;
        $coursesection->summaryformat = studentquiz_view::COURSE_SECTION_SUMMARYFORMAT;
        $coursesection->visible = studentquiz_view::COURSE_SECTION_VISIBLE;

        return $DB->insert_record('course_sections',$coursesection);
    }

    private function get_course_section() {
        global $DB;
        return $DB->get_record('course_sections', array('section' => 999));
    }

    private function create_quiz_course_module($courseid){
        global $DB;
        $moduleid = $this->get_quiz_module_id();
        $qcm = new stdClass();
        $qcm->course = $courseid;
        $qcm->module = $moduleid;
        $qcm->instance = 0;

        return $DB->insert_record('course_modules',$qcm);
    }

    private function get_quiz_module_id() {
        global $DB;
        return $DB->get_field('modules', 'id', array('name'=>'quiz'));
    }

    private function get_standard_quiz_setup() {
        global $USER;
        $quiz = new stdClass();
        $quiz->course = $this->get_course()->id;
        $quiz->name = $this->cm->name . ' - ' . $USER->username . ' '. studentquiz_view::GENERATE_QUIZ_PLACEHOLDER;
        $quiz->intro = studentquiz_view::GENERATE_QUIZ_INTRO;
        $quiz->introformat = 1;
        $quiz->timeopen = 0;
        $quiz->timeclose = 0;
        $quiz->timelimit = 0;
        $quiz->overduehandling = studentquiz_view::GENERATE_QUIZ_OVERDUEHANDLING;
        $quiz->graceperiod = 0;
        $quiz->preferredbehaviour = get_current_behaviour($this->cm);
        $quiz->canredoquestions = 0;
        $quiz->attempts = 0;
        $quiz->attemptonlast = 0;
        $quiz->grademethod = 1;
        $quiz->decimalpoints = 2;
        $quiz->questiondecimalpoints = -1;

        //reviewattempt
        $quiz->attemptduring = 1;
        $quiz->attemptimmediately = 1;
        $quiz->attemptopen = 1;
        $quiz->attemptclosed =1;

        //reviewcorrectness
        $quiz->correctnessduring = 1;
        $quiz->correctnessimmediately = 1;
        $quiz->correctnessopen = 1;
        $quiz->correctnessclosed = 1;

        //reviewmarks
        $quiz->marksduring = 1;
        $quiz->marksimmediately = 1;
        $quiz->marksopen = 1;
        $quiz->marksclosed = 1;

        //reviewspecificfeedback
        $quiz->specificfeedbackduring = 1;
        $quiz->specificfeedbackimmediately = 1;
        $quiz->specificfeedbackopen = 1;
        $quiz->specificfeedbackclosed = 1;

        //reviewgeneralfeedback
        $quiz->generalfeedbackduring = 1;
        $quiz->generalfeedbackimmediately = 1;
        $quiz->generalfeedbackopen = 1;
        $quiz->generalfeedbackclosed = 1;

        //reviewrightanswer
        $quiz->rightanswerduring = 1;
        $quiz->rightanswerimmediately = 1;
        $quiz->rightansweropen = 1;
        $quiz-> rightanswerclosed = 1;

        //reviewoverallfeedback
        $quiz->overallfeedbackimmediately = 1;
        $quiz->overallfeedbackopen = 1;
        $quiz->overallfeedbackclosed = 1;

        $quiz->questionsperpage = 1;
        $quiz->navmethod = 'free';
        $quiz->shuffleanswers = 1;
        $quiz->sumgrades = 0.0;
        $quiz->grade = 0.0;
        $quiz->timecreated = time();
        $quiz->quizpassword = '';
        $quiz->subnet = '';
        $quiz->browsersecurity = '-';
        $quiz->delay1 = 0;
        $quiz->delay2 = 0;
        $quiz->showuserpicture = 0;
        $quiz->showblocks = 0;
        $quiz->completionattemptsexhausted  = 0;
        $quiz->completionpass = 0;
        return $quiz;
    }

    /***
     * Override quiz_add_instance method from quiz lib to call custom quiz_after_add_or_update method,
     * because the user has no permission to call this method.
     * @param $quiz
     */
    private function  quiz_add_instance($quiz) {
        global $DB;
        $cmid = $quiz->coursemodule;

        // Process the options from the form.
        $quiz->created = time();
        $result = quiz_process_options($quiz);
        if ($result && is_string($result)) {
            return $result;
        }

        // Try to store it in the database.
        $quiz->id = $DB->insert_record('quiz', $quiz);

        // Create the first section for this quiz.
        $DB->insert_record('quiz_sections', array('quizid' => $quiz->id,
            'firstslot' => 1, 'heading' => '', 'shufflequestions' => 0));

        // Do the processing required after an add or an update.
        $this->quiz_after_add_or_update($quiz);

        return $quiz->id;
    }

    /***
     * Override quiz_after_add_or_update method from quiz lib to prevent quiz_update_events,
     * because the user has no permission to do this.
     * @param $quiz
     */
    private function quiz_after_add_or_update($quiz) {
        global $DB;
        $cmid = $quiz->coursemodule;

        // We need to use context now, so we need to make sure all needed info is already in db.
        $DB->set_field('course_modules', 'instance', $quiz->id, array('id'=>$cmid));
        $context = context_module::instance($cmid);

        // Save the feedback.
        $DB->delete_records('quiz_feedback', array('quizid' => $quiz->id));

        for ($i = 0; $i <= $quiz->feedbackboundarycount; $i++) {
            $feedback = new stdClass();
            $feedback->quizid = $quiz->id;
            $feedback->feedbacktext = $quiz->feedbacktext[$i]['text'];
            $feedback->feedbacktextformat = $quiz->feedbacktext[$i]['format'];
            $feedback->mingrade = $quiz->feedbackboundaries[$i];
            $feedback->maxgrade = $quiz->feedbackboundaries[$i - 1];
            $feedback->id = $DB->insert_record('quiz_feedback', $feedback);
            $feedbacktext = file_save_draft_area_files((int)$quiz->feedbacktext[$i]['itemid'],
                $context->id, 'mod_quiz', 'feedback', $feedback->id,
                array('subdirs' => false, 'maxfiles' => -1, 'maxbytes' => 0),
                $quiz->feedbacktext[$i]['text']);
            $DB->set_field('quiz_feedback', 'feedbacktext', $feedbacktext,
                array('id' => $feedback->id));
        }

        // Store any settings belonging to the access rules.
        quiz_access_manager::save_settings($quiz);

        // Update the events relating to this quiz.
        //quiz_update_events($quiz); no permission

        // Update related grade item.
        quiz_grade_item_update($quiz);
    }

    /**
     * start the quiz activity with the filtered quiz ids
     * @param $submitdata
     * @return bool|int
     */
    public function start_filtered_quiz($ids) {
        $tmp = explode(',', $ids);
        $ids = array();
        foreach($tmp as $id) {
            $ids[$id] = 1;
        }

        return $this->start_quiz($this->quiz_practice_get_question_ids($ids));
    }

    /**
     * start the quiz activity with the selected quiz ids
     * @param $submitdata
     * @return bool|int
     */
    public function start_selected_quiz($submitdata) {
        return $this->start_quiz($this->quiz_practice_get_question_ids($submitdata));
    }

    /**
     * create the questio bank view
     */
    public function create_questionbank() {
        $_GET['cmid'] = $this->get_cm_id();
        $_POST['cat'] = $this->get_category_id() . ',' . $this->get_context_id();

        list($thispageurl, $contexts, $cmid, $cm, $module, $pagevars) =
            question_edit_setup('questions', '/mod/studentquiz/view.php', true, false);

        $this->pageurl = new moodle_url($thispageurl);
        if (($lastchanged = optional_param('lastchanged', 0, PARAM_INT)) !== 0) {
            $this->pageurl->param('lastchanged', $lastchanged);
        }
        $this->qbpagevar = $pagevars;

        $this->questionbank = new \mod_studentquiz\question\bank\studentquiz_bank_view($contexts, $thispageurl, $this->course, $this->cm);
        $this->questionbank->process_actions();
    }

    /**
     * get the quiz ids from the submit data
     * @param $rawdata
     * @return array
     */
    private function get_quiz_ids($rawdata) {
        $ids = array();
        foreach ($rawdata as $key => $value) { // Parse input for question ids.
            if (preg_match('!^q([0-9]+)$!', $key, $matches)) {
                $ids[] = $matches[1];
            }
        }
        return $ids;
    }

    /**
     * get the question ids
     * @param $rawdata
     * @return array|bool
     */
    private function quiz_practice_get_question_ids($rawdata) {
        if(!isset($rawdata)&& empty($rawdata)) return false;

        $ids = $this->get_quiz_ids($rawdata);

        if(!count($ids)) {
            return false;
        }

        return $ids;
    }

    /**
     * has question ids set
     * @return bool
     */
    public function has_questiond_ids(){
        return $this->hasquestionids;
    }


    /**
     * get the question bank page url
     * @return moodle_url
     */
    public function get_pageurl() {
        return new moodle_url($this->pageurl, $this->get_urlview_data());
    }

    /**
     * get actual view url
     * @return moodle_url
     */
    public function get_viewurl() {
        return new moodle_url('/mod/studentquiz/view.php', $this->get_urlview_data());
    }

    /**
     * get the question pagevar
     * @return object
     */
    public function get_qb_pagevar() {
        return $this->qbpagevar;
    }

    /**
     * get the urlview data (includes cmid)
     * @return array
     */
    public function get_urlview_data() {
        return array('cmid' => $this->cm->id);
    }

    /**
     * get activity course
     * @return mixed|stdClass
     */
    public function get_course() {
        return $this->course;
    }


    /**
     * get activity course module
     * @return stdClass
     */
    public function get_coursemodule() {
        return $this->cm;
    }

    /**
     * get activity course module id
     * @return mixed
     */
    public function get_cm_id() {
        return $this->cm->id;
    }

    /*
     * get activity category id
     * @return int
     */
    public function get_category_id() {
        return $this->category->id;
    }

    /**
     * get activity context id
     * @return int
     */
    public function get_context_id() {
        return $this->context->id;
    }

    /**
     * get the view title
     * @return string
     */
    public function get_title() {
        return get_string('editquestions', 'question');
    }

    /**
     * get the question view
     * @return mixed
     */
    public function get_questionbank() {
        return $this->questionbank;
    }
}

class moodle_studentquiz_view_exception extends moodle_exception {
    public function __construct($view, $errorCode, $a = null, $link = '', $debuginfo = null) {
        if (!$link) {
            $link = $view->get_viewurl();
        }
        parent::__construct($errorCode, 'studentquiz', $link, $a, $debuginfo);
    }
}