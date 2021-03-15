<?php

/**
 * Version details
 *
 * @package    local_grade_notification
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function get_unitlist()
{
    global $DB;

    $table = 'local_notif_grade_quizlist';
    $condition = ['disable_flag'=>0];
         
    $result = $DB->get_records($table,$condition);
    return $result;
}


function send_email_student($email,$firstname,$lastname,$unitname,$assementname,$attemptid)
{
    global $CFG;
    $attempt_url = $CFG->wwwroot.'/mod/quiz/review.php?attempt='.$attemptid;

    $sender = 'academic@libertycollege.edu.au';

    $to= $email;
    $subject= 'Your assessment has marked';
    $text = '
    <html>
    <body>
    <p>Dear <b>'.$firstname.'</b>  <b>'.$lastname.'</b>&nbsp;<span style="font-size: 0.9375rem;">
        <br>
        <br>Your assessment has been graded by traineer. Please follow the link below and access your Moodle account and review trainers comment!</span> 
        <br>
        <a href="'.$attempt_url.'">Click and Review your attempt in the Moodle</a>
        
        </p>
        <br> If you need any support please contact Libety Academic team <b>(academic@libertycollege.edu.au)</b>  
    <p>
    </p>
    <table style= "background-color: rgb(219, 219, 219);">
        <caption style="caption-side: top"></caption>
        <thead>
            <tr>
                <th scope="col">Unit Name :&nbsp;</th>
                <th scope="col"><span style="font-weight: normal;">'.$unitname.'</span></th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><b>Assessment Name :</b>&nbsp;</td>
                <td>'.$assementname.'</td>
                <td></td>
            </tr>

            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        </tbody>
    </table>

    <p><br><b>
    Kind Regard,<br>Liberty Academic</b></p>
    </body>
    </html>';
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers.= 'From: Liberty College <'.$sender.'> \r\n';
    $headers.= 'Cc: hostmaster@libertycollege.edu.au \r\n';

    mail($to,$subject,$text,$headers);
    // $message = 'Say Hello';
    // \core\notification::add($message, \core\output\notification::NOTIFY_SUCCESS);
}

function get_attemt_list($quizid,$quiz_min)
{
    global $DB;
    $start_date = 1615530422;

    $sql = 'SELECT a.id,
                    c.fullname,
                    q.name,
                    a.quiz,
                    a.userid,
                    a.attempt,
                    q.sumgrades as `total_grade`,
                    a.sumgrades,
                    u.firstname,
                    u.lastname,
                    u.email 
            FROM {quiz_attempts} as a
            left join {user} as u on a.userid = u.id
            left join {quiz} as q on a.quiz = q.id
            left join {course} as c on q.course = c.id
                where a.timestart >=:timestart
                and a.quiz = :quizid
                and a.state>="finished"
                and a.sumgrades>:quiz_min
                and (a.id, a.attempt)not in (SELECT attemptid  as id ,attempt_number as attempt FROM {local_notif_grade_sentlog})
                ';


    $para = ['timestart'=>$start_date,
             'quizid'=>$quizid,
             'quiz_min'=>$quiz_min];

    $result = $DB->get_records_sql($sql,$para);
    return $result;
}

function insert_sent_log(
    $attemptid,
    $attempt_number,
    $userid,
    $email,
    $sent_flag,
    $sent_error)
{
    global $DB;

    $table = 'local_notif_grade_sentlog';

    $record = new \stdClass();
    $record->attemptid = $attemptid;
    $record->attempt_number = $attempt_number;
    $record->userid = $userid;
    $record->email = $email;
    $record->sent_flag = $sent_flag;
    $record->sent_error = $sent_error;
    $record->date = time();
    
    $DB->insert_record($table,$record);
}