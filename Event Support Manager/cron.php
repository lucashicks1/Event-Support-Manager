<?php

require_once('mail/SMTP.php');
require_once('mail/PHPMailer.php');
require_once('mail/Exception.php');

use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\Exception;


date_default_timezone_set('Australia/Brisbane');
$date = new DateTime();

$date->add(new DateInterval('P1D'));

require('connect.php');


// Find details of students with alerts to send
$details = DB::query(
    '
      select e.etype, e.starttime, e.endtime, e.ename, u.fname, u.lname, u.email, u.uid, e.eid
      from events e, userevents a, users u
      where a.alerts = 1
      AND a.sent = 0
      AND a.uid = u.uid
      AND a.eid = e.eid',
);

// Iterate through details
for ($i = 0; $i < count($details); $i++) {
    // Format dates to place in email
    $sdate = (new DateTime($details[$i]['starttime']));
    $edate = (new DateTime($details[$i]['endtime']));
    if (($sdate < $date) && ($edate > $date)) {


        $mail = new PHPMailer(true);
        // send email
        try {
            //settings
            $mail->SMTPDebug = 0; // Enable verbose debug output
            $mail->isSMTP(); // Set mailer to use SMTP
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true; // Enable SMTP authentication
            $mail->Username = 'gtdigisol2020@gmail.com'; // SMTP username
            $mail->Password = 'securepassword'; // SMTP password
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            $mail->setFrom('gtdigisol2020@gmail.com', 'Terrace Support Program');

            //recipient
            $mail->addAddress($details[$i]['email'], $details[$i]['fname'] . ' ' . $details[$i]['lname']);
            // Add a recipient

            //content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'Upcoming event for ' . $details[$i]['etype'];
            $mail->Body = $details[$i]['ename'] . ' is upcoming so get ready to support your school! The event starts at ' . $sdate->format('h:i a') . ' on ' . strtolower($sdate->format('l')) . '.';
            $mail->AltBody =
                $details[$i]['ename'] . ' is upcoming so get ready to support your school! The event starts at ' . $sdate->format('h:i a') . ' on ' . strtolower($sdate->format('l')) . '.';

            $mail->send();
        } catch (Exception $e) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }
        // update database
        DB::update(
            'userevents',
            ['sent' => 1],
            "uid=%s AND eid=%s",
            $details[$i]['uid'],
            $details[$i]['eid']
        );
    }
}