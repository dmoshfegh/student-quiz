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
 * Container class for comment area.
 *
 * @package mod_studentquiz
 * @copyright 2020 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_studentquiz\commentarea;

defined('MOODLE_INTERNAL') || die();

use mod_studentquiz\utils;
use stdClass;

/**
 * Container class for comment area.
 *
 * @package mod_studentquiz
 * @copyright 2020 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class container {

    /** @var int - Number of comments to show by default. */
    const NUMBER_COMMENT_TO_SHOW_BY_DEFAULT = 5;

    /** @var int - Comment root parent id. */
    const PARENTID = 0;

    /** @var string - Created comment event name. */
    const COMMENT_CREATED = 'comment_created';

    /** @var string - Deleted comment event name. */
    const COMMENT_DELETED = 'comment_deleted';

    /** @var \question_definition $question - Question class. */
    private $question;

    /** @var stdClass $cm - Module. */
    private $cm;

    /** @var stdClass $context - Context. */
    private $context;

    /** @var array - Array of stored comments. */
    private $storedcomments;

    /** @var object|stdClass - Studentquiz data. */
    private $studentquiz;

    /** @var string - Basic order to get comments. */
    private $basicorder = 'c.created ASC';

    /** @var object|stdClass - Current user of Moodle. Only call it once when __construct */
    private $user;

    /** @var object|stdClass - Current course of Moodle. Only call it once when __construct */
    private $course;

    /** @var int - Current set limit. */
    private $currentlimit = 0;

    /** @var int - Current offset. */
    private $currentoffset = 0;

    /** @var bool - Flag current user has commented. */
    private $checkhascomment = false;

    /**
     * @var string - Sort feature.
     */
    private $sortfeature;

    /**
     * @var string - Sort field.
     */
    private $sortfield;

    /** @var string - Sort by (ASC/DESC). */
    private $sortby;

    /**
     * @var array List of users has comments.
     */
    private $userlist = [];

    /** @var bool - Check if user is moderator in current context. */
    public $ismoderator = false;

    /** @var bool - Can view deleted. */
    public $canviewdeleted = false;

    /** @var int - Comment/reply deletion period default - 10 minutes. */
    const DELETION_PERIOD_DEFAULT = 10;

    /** @var int - Comment/reply deletion period min. */
    const DELETION_PERIOD_MIN = 0;

    /** @var int - Comment/reply deletion period max. */
    const DELETION_PERIOD_MAX = 60;

    /**
     * @var array - Reporting Emails.
     */
    private $reportemails = [];

    /** @var string - Define sort type date. */
    const SORT_DATE = 'date';
    /** @var string - Define sort type forename. */
    const SORT_FIRSTNAME = 'forename';
    /** @var string - Define sort type lastname. */
    const SORT_LASTNAME = 'surname';

    /** @var array - Mapping db fields with sort define. */
    const SORT_DB_FIELDS = [
            self::SORT_DATE => 'c.created',
            self::SORT_FIRSTNAME => 'u.firstname',
            self::SORT_LASTNAME => 'u.lastname',
    ];

    /** @var array - Default sort. */
    const SORT_FIELDS = [
            self::SORT_DATE
    ];

    /** @var array - Special fields. */
    const USER_SORT_FIELDS = [
            self::SORT_FIRSTNAME,
            self::SORT_LASTNAME
    ];

    /** @var string - Define sort by date ascending. */
    const SORT_DATE_ASC = 'date_asc';
    /** @var string - Define sort by date descending. */
    const SORT_DATE_DESC = 'date_desc';
    /** @var string - Define sort by user forename ascending. */
    const SORT_FIRSTNAME_ASC = 'forename_asc';
    /** @var string - Define sort by user forename descending. */
    const SORT_FIRSTNAME_DESC = 'forename_desc';
    /** @var string - Define sort by user surname ascending. */
    const SORT_LASTNAME_ASC = 'surname_asc';
    /** @var string - Define sort by user forename descending. */
    const SORT_LASTNAME_DESC = 'surname_desc';

    /** @var array - Per sort field has multiple sort features. */
    const SORT_FEATURES = [
            self::SORT_DATE => [
                    self::SORT_DATE_ASC,
                    self::SORT_DATE_DESC
            ],
            self::SORT_FIRSTNAME => [
                    self::SORT_FIRSTNAME_ASC,
                    self::SORT_FIRSTNAME_DESC,
            ],
            self::SORT_LASTNAME => [
                    self::SORT_LASTNAME_ASC,
                    self::SORT_LASTNAME_DESC,
            ]
    ];

    /** @var string - Define name for user preference sort. */
    const USER_PREFERENCE_SORT = 'mod_studentquiz_comment_sort';

    /**
     * mod_studentquiz_commentarea_list constructor.
     *
     * @param mixed $studentquiz - Student Quiz instance.
     * @param \question_definition $question - Question instance.
     * @param mixed $cm - Course Module instance.
     * @param mixed $context - Context instance.
     * @param stdClass $user - User instance.
     * @param string $sort - Sort type.
     */
    public function __construct($studentquiz, \question_definition $question, $cm, $context, $user = null, $sort = '') {
        global $USER, $COURSE;
        $this->studentquiz = $studentquiz;
        $this->question = $question;
        $this->cm = $cm;
        $this->context = $context;
        $this->storedcomments = null;
        $this->user = $user === null ? clone $USER : $user;
        $this->course = clone $COURSE;
        $this->ismoderator = has_capability('mod/studentquiz:previewothers', $context);
        $this->canviewdeleted = $this->ismoderator;

        // If not force commenting, always true.
        $this->refresh_has_comment();
        $this->reportemails = utils::extract_reporting_emails_from_string($studentquiz->reportingemail);
        $this->set_sort_user_preference($sort);
        $this->setup_sort();
    }

    /**
     * Get current user
     *
     * @return mixed
     */
    public function get_user() {
        return $this->user;
    }

    /**
     * Get module.
     *
     * @return mixed
     */
    public function get_cm() {
        return $this->cm;
    }

    /**
     * Get course.
     *
     * @return mixed
     */
    public function get_course() {
        return $this->course;
    }

    /**
     * Get question.
     *
     * @return \question_definition
     */
    public function get_question() {
        return $this->question;
    }

    /**
     * Get context.
     *
     * @return mixed
     */
    public function get_context() {
        return $this->context;
    }

    /**
     * Get module id
     *
     * @return mixed
     */
    public function get_cmid() {
        return $this->get_cm()->id;
    }

    /**
     * Get studentquiz.
     *
     * @return object|stdClass
     */
    public function get_studentquiz() {
        return $this->studentquiz;
    }

    /**
     * Fetch all comments.
     *
     * @param int $numbertoshow - Number of top-level comments to show.
     * @return comment[]
     */
    public function fetch_all($numbertoshow = 0) {
        return $this->fetch($numbertoshow, ['parentid' => self::PARENTID]);
    }

    /**
     * Fetch comments.
     *
     * @param int $numbertoshow - Number of top-level comments to show.
     * @param array $params - Query conditions.
     * @return array
     */
    public function fetch($numbertoshow, $params) {
        $this->storedcomments = $this->query_comments($numbertoshow, $params);
        $comments = $this->storedcomments;
        $list = [];
        // Check if we have any comments.
        if ($comments) {
            // We need to get users.
            $this->set_user_list($comments);
            // Obtain comments relationships.
            $tree = $this->build_tree($comments);
            foreach ($tree as $rootid => $children) {
                $comment = $this->build_comment($comments[$rootid]);
                if (!empty($children)) {
                    foreach ($children as $childid) {
                        $reply = $this->build_comment($comments[$childid], $comment);
                        $comment->add_child($reply);
                    }
                }
                $list[] = $comment;
            }
        }
        return $list;
    }

    /**
     * Build tree comment.
     *
     * @param array $comments - Array of comments.
     * @return array
     */
    public function build_tree($comments) {
        $tree = [];
        foreach ($comments as $id => $comment) {
            $parentid = $comment->parentid;
            // Add root comments.
            if ($parentid == self::PARENTID) {
                if (!isset($tree[$id])) {
                    $tree[$id] = [];
                }
                continue;
            }
            if (!isset($tree[$parentid])) {
                $tree[$parentid] = [];
            }
            $tree[$parentid][] = $id;
        }
        return $tree;
    }

    /**
     * Count all comments.
     *
     * @return int
     */
    public function get_num_comments() {
        global $DB;
        return $DB->count_records_select('studentquiz_comment', 'questionid = :questionid AND status <> :status',
                ['questionid' => $this->get_question()->id, 'status' => utils::COMMENT_HISTORY_DELETE]);
    }

    /**
     * Query for comments
     *
     * @param string $numbertoshow - Number to show.
     * @param array $params - Params for comment conditions.
     * @return array - Array of comment.
     */
    public function query_comments($numbertoshow, $params) {
        global $DB;

        $params['questionid'] = $this->get_question()->id;

        // Set limit.
        if (is_numeric($numbertoshow) && $numbertoshow > 0) {
            $this->currentlimit = $numbertoshow;
        }
        // Check has limit or get all.
        $haslimit = $this->currentlimit > 0;

        // Build join.
        $join = '';
        if ($this->is_user_table_sort()) {
            $join = 'JOIN {user} u ON u.id = c.userid';
        }

        $userpreferencesort = $this->get_sort();

        // If have limit, always get latest.
        if ($haslimit) {
            $order = 'c.created DESC';
            if ($this->is_user_table_sort()) {
                $order = $userpreferencesort . ', ' . $order;
            }
        } else {
            $order = $this->get_sort();
            if ($this->is_user_table_sort()) {
                $order = $userpreferencesort . ', ' . $this->basicorder;
            }
        }

        // Build a where string a = :a AND b = :b.
        $where = '';
        foreach (array_keys($params) as $v) {
            if (!$where) {
                $where .= "c.$v = :$v";
            } else {
                $where .= " AND c.$v = :$v";
            }
        }

        // Build limit.
        $limit = $haslimit ? "LIMIT $this->currentlimit" : '';

        $sql = "SELECT c.*
                  FROM {studentquiz_comment} c
                       $join
                 WHERE $where
              ORDER BY $order
                       $limit";
        // Retrieve comments from question.
        $roots = $DB->get_records_sql($sql, $params);

        $data = [];
        if (!empty($roots)) {
            if ($haslimit) {
                $roots = $this->resort($roots);
            }
            list($ids, $listids) = $DB->get_in_or_equal(array_column($roots, 'id'));
            $query = "SELECT *
                        FROM {studentquiz_comment}
                       WHERE parentid $ids
                    ORDER BY created ASC";
            $comments = $DB->get_records_sql($query, $listids);

            $data = $roots + $comments;

            $rownumber = 1;
            foreach ($data as &$comment) {
                $comment->rownumber = $rownumber;
                $rownumber++;
            }
        }

        return $data;
    }

    /**
     * Get a comment and its replies by comment id. Null if not found.
     *
     * @param int $id
     * @return comment|null
     */
    public function query_comment_by_id($id) {
        global $DB;

        // First fetch to check it's a comment or reply.
        $record = $DB->get_record('studentquiz_comment', ['id' => $id]);
        if (!$record) {
            return null;
        }

        // It is a reply.
        if ($record->parentid != self::PARENTID) {
            $parentdata = $DB->get_record('studentquiz_comment', ['parentid' => $id]);
            $this->set_user_list([$record]);
            return $this->build_comment($record, $parentdata);
        }

        // It's a comment.
        $comments = $this->fetch(1, ['parentid' => self::PARENTID, 'id' => $record->id]);
        if (!isset($comments[0])) {
            return null;
        }
        return $comments[0];
    }

    /**
     * Build data comment into comment class.
     *
     * @param stdClass $commentdata - Comment data.
     * @param null $parentdata - Parent comment data, null if top level comment.
     * @return comment
     */
    private function build_comment($commentdata, $parentdata = null) {
        return new comment($this, $commentdata, $parentdata);
    }

    /**
     * Create new comment.
     *
     * @param stdClass $data - Data of comment will be created.
     * @return int - ID of created comment.
     */
    public function create_comment($data) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $comment = new stdClass();
        $comment->comment = $data->message['text'];
        $comment->questionid = $this->question->id;
        $comment->userid = $this->get_user()->id;
        $comment->parentid = $data->replyto != self::PARENTID ? $data->replyto : self::PARENTID;
        $comment->timemodified = time();
        $comment->usermodified = $this->get_user()->id;
        $comment->status = utils::COMMENT_HISTORY_CREATE;
        $comment->created = time();
        $id = $DB->insert_record('studentquiz_comment', $comment);
        // Write log.
        $this->log(self::COMMENT_CREATED, $comment);
        $transaction->allow_commit();
        return $id;
    }

    /**
     * Writing log.
     *
     * @param string $action - Action name.
     * @param stdClass $data - data of comment.
     */
    public function log($action, $data) {
        $coursemodule = $this->get_cm();
        if ($action == self::COMMENT_CREATED) {
            mod_studentquiz_notify_comment_added($data, $this->get_course(), $coursemodule);
        } else if ($action == self::COMMENT_DELETED) {
            mod_studentquiz_notify_comment_deleted($data, $this->get_course(), $coursemodule);
        }
    }

    /**
     * Set users list.
     *
     * @param array $comments
     */
    public function set_user_list($comments) {
        global $CFG, $DB;
        $userids = [];
        foreach ($comments as $comment) {
            if (!in_array($comment->userid, $userids)) {
                $userids[] = $comment->userid;
            }
            if (!in_array($comment->userid, $userids) && $comment->status == utils::COMMENT_HISTORY_DELETE) {
                $userids[] = $comment->userid;
            }
        }
        // Retrieve users from db.
        if (!empty($userids)) {
            list($idsql, $params) = $DB->get_in_or_equal($userids);
            // Adding the standard "intro" and "introformat" fields.
            if ($CFG->branch >= 311) {
                $fields = implode(',', \core_user\fields::get_name_fields());
            } else {
                $fields = get_all_user_name_fields(true);
            }
            $query = "SELECT id, $fields
                        FROM {user}
                       WHERE id $idsql";
            $users = $DB->get_records_sql($query, $params);
            foreach ($users as $user) {
                $user->fullname = fullname($user);
                $this->userlist[$user->id] = $user;
            }
        }
    }

    /**
     * Add given user to the user list.
     *
     * @param int $userid
     */
    public function add_user_to_user_list($userid) {
        $user = \core_user::get_user($userid);
        $user->fullname = fullname($user);
        $this->userlist[$user->id] = $user;
    }

    /**
     * Get user from users list.
     *
     * @param int $id - Id of user.
     * @return mixed|null
     */
    public function get_user_from_user_list(int $id) {
        if (!array_key_exists($id, $this->userlist)) {
            return null;
        }
        return $this->userlist[$id];
    }

    /**
     * Users can't see other comment authors user names except ismoderator.
     * Should use in construct only.
     *
     * @return bool
     * @throws \coding_exception
     */
    public function can_view_username() {
        if ($this->ismoderator) {
            return true;
        }
        if (has_capability('mod/studentquiz:unhideanonymous', $this->get_context())) {
            return true;
        }
        return !$this->get_studentquiz()->anonymrank;
    }

    /**
     * View deleted permission.
     *
     * @return bool
     */
    public function can_view_deleted() {
        return $this->canviewdeleted;
    }

    /**
     * Get deletion period select list.
     *
     * @return array
     */
    public static function get_deletion_period_options() {
        return range(self::DELETION_PERIOD_MIN, self::DELETION_PERIOD_MAX);
    }

    /**
     * Check if user already commented.
     *
     * @param int $questionid
     * @param int $userid
     * @return bool
     */
    public static function has_comment(int $questionid, $userid) {
        global $DB;
        return $DB->record_exists_select('studentquiz_comment',
                'questionid = :questionid AND userid = :userid AND status <> :status', [
                        'questionid' => $questionid,
                        'userid' => $userid,
                        'status' => utils::COMMENT_HISTORY_DELETE
                ]);
    }

    /**
     * In case if any comments in area change (insert/delete).
     * Use to refresh flag user has commented.
     *
     * @return $this
     */
    public function refresh_has_comment() {
        if (!$this->get_studentquiz()->forcecommenting) {
            $this->checkhascomment = true;
        } else {
            $this->checkhascomment = self::has_comment($this->get_question()->id, $this->get_user()->id);
        }
        return $this;
    }

    /**
     * Check user has commented.
     *
     * @return bool
     */
    public function check_has_comment() {
        return $this->checkhascomment;
    }

    /**
     * Get reporting emails list.
     *
     * @return array
     */
    public function get_reporting_emails() {
        return $this->reportemails;
    }

    /**
     * Get anonymous mode.
     *
     * @return bool
     */
    public function anonymous_mode() {
        $context = $this->get_context();
        $studentquiz = $this->get_studentquiz();
        if ($this->ismoderator || has_capability('mod/studentquiz:unhideanonymous', $context)) {
            return false;
        }
        return $studentquiz->anonymrank;
    }

    /**
     * Get fields.
     *
     * @return array
     */
    public function get_fields() {
        $fields = [];
        // In anonymous mode, those features is not available.
        if (!$this->anonymous_mode()) {
            $fields = array_merge($fields, self::USER_SORT_FIELDS);
        }
        return array_merge($fields, self::SORT_FIELDS);
    }

    /**
     * Get array of sortable in current context.
     *
     * @return array
     */
    public function get_sortable() {
        return self::extract_sort_features_from_sort_fields($this->get_fields());
    }

    /**
     * Check if current sort feature can be used to sort.
     *
     * @param string $field
     * @return bool
     */
    public function is_sortable($field) {
        return in_array($field, $this->get_sortable());
    }

    /**
     * Setup sort.
     */
    public function setup_sort() {
        $currentsortfeature = $this->get_sort_from_user_preference();
        // In case we are in anonymous mode, and current sort is not supported, return default sort.
        if ($this->anonymous_mode() && !$this->is_sortable($currentsortfeature)) {
            $currentsortfeature = self::SORT_DATE_ASC;
        }
        $this->sortfeature = $currentsortfeature;
    }

    /**
     * Get sort from user preference. If not set then create one.
     *
     * @return string
     */
    public function get_sort_from_user_preference() {
        $sort = get_user_preferences(self::USER_PREFERENCE_SORT);
        // In case db row is not found.
        if (is_null($sort)) {
            set_user_preference(self::USER_PREFERENCE_SORT, self::SORT_DATE_ASC);
            $sort = get_user_preferences(self::USER_PREFERENCE_SORT);
        }
        return $sort;
    }

    /**
     * Check if current sort needs to join user table for sort.
     *
     * @return bool
     */
    public function is_user_table_sort() {
        return in_array($this->sortfeature, self::extract_sort_features_from_sort_fields(self::USER_SORT_FIELDS));
    }

    /**
     * Get all sort features by sort field (date, forename, surname).
     *
     * @param array $fields
     * @return array
     */
    public static function extract_sort_features_from_sort_fields($fields) {
        $sortable = [];
        if (count($fields) > 0) {
            foreach ($fields as $field) {
                if (!isset(self::SORT_FEATURES[$field])) {
                    continue;
                }
                $sortdata = self::SORT_FEATURES[$field];
                $sortable = array_merge($sortable, $sortdata);
            }
        }
        return $sortable;
    }

    /**
     * Convert sort feature to database order.
     *
     * @return array
     */
    public function extract_user_preference_sort() {
        $sort = explode('_', $this->sortfeature);
        $sortfield = self::SORT_DB_FIELDS[$sort[0]];
        $sortby = $sort[1];
        // Build into order query. Example: created_at => 'created asc'.
        $dbsort = $sortfield . ' ' . $sortby;
        return [$dbsort, $sortfield, $sortby];
    }

    /**
     * Build query order by.
     *
     * @return string
     */
    public function get_sort() {
        list($dbsort, $sortfield, $sortby) = $this->extract_user_preference_sort();
        $this->sortfield = $sortfield;
        $this->sortby = $sortby;
        return $dbsort;
    }

    /**
     * Set user preference sort.
     *
     * @param string $string
     */
    public function set_sort_user_preference($string) {
        if ($this->is_sortable($string)) {
            $currentsort = $this->get_sort_from_user_preference();
            // If current sort is different, then update. Otherwise no need to call DB.
            if ($string !== $currentsort) {
                set_user_preference(self::USER_PREFERENCE_SORT, $string);
            }
        }
    }

    /**
     * Get current sort feature of comment area.
     *
     * @return string
     */
    public function get_sort_feature() {
        return $this->sortfeature;
    }

    /**
     * Render sort select filters.
     *
     * @return array
     */
    public function get_sort_select() {
        $data = [];
        foreach ($this->get_fields() as $field) {
            $type = 'desc';
            $features = self::SORT_FEATURES[$field];
            if (in_array($this->sortfeature, $features) && $this->sortby === 'asc') {
                $type = 'asc';
            }
            $classes = $type === 'desc' ? 'filter-desc' : 'filter-asc';
            // Add current class to current sort href link in fe.
            if (in_array($this->sortfeature, $features)) {
                $classes .= ' current';
            }
            $asc = \get_string('asc');
            $desc = \get_string('desc');
            $typename = \get_string("filter_comment_label_$field", 'studentquiz');
            $sortbydesc = \get_string('filter_comment_label_sort_toggle', 'studentquiz', [
                    'field' => $typename,
                    'type' => $desc
            ]);
            $sortbyasc = \get_string('filter_comment_label_sort_toggle', 'studentquiz', [
                    'field' => $typename,
                    'type' => $asc
            ]);
            $data[] = [
                    'sortkey' => $field,
                    'typename' => $typename,
                    'togglestring' => $type === 'desc' ? $sortbyasc : $sortbydesc,
                    'orderclass' => $classes,
                    'ordertype' => $type,
                    'iconsortname' => ${$type},
                    'ascstring' => $sortbyasc,
                    'descstring' => $sortbydesc
            ];
        }
        return $data;
    }

    /**
     * Re-sort data when get limit (limit always get latest).
     *
     * @param array $data
     * @return array
     */
    private function resort($data) {
        // If sort by date desc, do not need re-sort.
        if ($this->sortfeature === self::SORT_DATE_DESC) {
            return $data;
        }
        // If sort by user name, keep name as it is. But sort time created DESC => ASC.
        if ($this->is_user_table_sort()) {
            $orders = [];
            foreach ($data as $k => $v) {
                $orders[$v->userid][] = $k;
            }
            foreach ($orders as $k => $v) {
                $orders[$k] = array_reverse($v);
            }
            $res = [];
            foreach ($orders as $v) {
                foreach ($v as $commentid) {
                    $res["$commentid"] = $data[$commentid];
                }
            }
            return $res;
        }
        // Otherwise just reverse data.
        return array_reverse($data, true);
    }

    /**
     * Get comment history by given comment id
     *
     * @param int $commentid Comment id for filter data
     * @return array comment's history
     */
    public function get_history($commentid): array {
        global $DB;

        list($insql, $inparams) = $DB->get_in_or_equal([utils::COMMENT_HISTORY_EDIT, utils::COMMENT_HISTORY_CREATE]);
        return $DB->get_records_select('studentquiz_comment_history', "commentid = $commentid AND action $insql", $inparams,
                'timemodified DESC');
    }

    /**
     * Return custom data for render comment history
     *
     * @param stdClass $commenthistories Content for renderer
     * @return array
     */
    public function extract_comment_history_to_render($commenthistories): array {
        $outputresults = [];
        $userinfocacheset = [];
        foreach ($commenthistories as $commenthistory) {
            $instance = new stdClass();
            $instance->id = $commenthistory->id;
            $instance->posttime = userdate($commenthistory->timemodified, get_string('strftimedatetime', 'langconfig'));
            $instance->content = $commenthistory->content;
            $instance->rownumber = isset($commenthistory->rownumber) ? $commenthistory->rownumber : $commenthistory->id;
            if (!array_key_exists($commenthistory->userid, $userinfocacheset)) {
                if ($this->can_view_username() || $this->get_user()->id == $commenthistory->userid) {
                    $user = \core_user::get_user($commenthistory->userid);
                    $instance->authorname = fullname($user, true);
                } else {
                    $instance->authorname = get_string('anonymous_user_name', 'mod_studentquiz', $instance->rownumber);
                }
                $userinfocacheset[$commenthistory->userid] = $instance->authorname;
            } else {
                $instance->authorname = $userinfocacheset[$commenthistory->userid];
            }

            $outputresults[] = $instance;
        }

        return $outputresults;
    }
}
