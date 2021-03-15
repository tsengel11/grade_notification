<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $DB, $CFG;

$link = $CFG->wwwroot;
$PAGE->set_url(new moodle_url('/blocks/block_student_schedule/edit.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Edit Student schedules');
$content='';

echo $OUTPUT->header();

$start_date = 1615530422;
$quizid=952;
$quiz_min =2;

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
and (a.id, a.attempt)not in (SELECT attemptid  as id ,attempt_number as attempt FROM {local_notif_grade_sentlog})';

    $para = ['timestart'=>$start_date,
             'quizid'=>$quizid,
             'quiz_min'=>$quiz_min];

    $table = 'local_notif_grade_quizlist';
    $condition = ['disable_flag'=>0];
         
    //$result = $DB->get_records($table,$condition);
    $result = $DB->get_records_sql($sql,$para);



    print_r($result);

    print(time());

    // foreach($result as $item){

    //      print_r($item->email);
    // }


echo '<hr>';
echo $content;

//echo $content;
echo $OUTPUT->footer();