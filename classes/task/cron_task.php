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
 * A scheduled task for forum cron.
 *
 * @todo MDL-44734 This job will be split up properly.
 *
 * @package    local_grade_notification
 * @copyright  2014 Dan Poltawski <dan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_grade_notification\task;

class cron_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return 'local_grade_notification';
    }

    /**
     * Run forum cron.
     */
    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/local/grade_notification/lib.php');


        $unitlist = get_unitlist();
        foreach ($unitlist as $unit){
            $message_lists = get_attemt_list($unit->quizid,$unit->quizid_mingrade);

            foreach($message_lists as $message){
                $success = send_email_student($message->email,
                $message->firstname,
                $message->lastname,
                $message->fullname,
                $message->name,
                $message->id);
    
                if (!$success) {
                    $errorMessage = error_get_last()['message'];
                    insert_sent_log(
                    $message->id,
                    $message->attempt,
                    $message->userid,
                    $message->email,
                    1,
                    $errorMessage);
                }
                else{
                    $errorMessage='';
                    $errorMessage = error_get_last()['message'];
                    insert_sent_log($message->id,$message->attempt,$message->userid,$message->email,0,$errorMessage);
                }      
            }
        }

    }

}