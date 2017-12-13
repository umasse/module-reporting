<?php
session_start();
include  "../../config.php";
include "../../functions.php";

//New PDO DB connection
try {
    $connection2=new PDO("mysql:host=$databaseServer;
            dbname=$databaseName;
            charset=utf8", $databaseUsername, $databasePassword);
    $connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // reset coding
}
catch(PDOException $e) {
    echo $e->getMessage();
}

//if (isActionAccessible($guid, $connection2, "/modules/Reporting/pdf_make.php")==FALSE) {
if (1==2) {
    //Acess denied
    print "<div class='error'>";
    print "You do not have access to this action.";
    print "</div>";
    exit;
} else {

    //include "./pdf_function.php" ;
    include "./function.php";
    include "./pdfmail_send_function.php";
    //include "../../lib/PHPMailer/class.phpmailer.php";
    include $_SERVER['DOCUMENT_ROOT']."/lib/PHPMailer/PHPMailerAutoload.php";

    setSessionVariables($guid, $connection2);

    $setpdf = new setpdf($guid, $connection2);

    // check folder exists
    $setpdf->checkFolder();
    $archiveFolderPath = $_SESSION['archiveFilePath'].$setpdf->schoolYearName.'/';

    // go through class list to see which ones need to be printed
    //$rollGroupList = $setpdf->rollGroupList;
    $text = ''; // variable for holding output for email sending log page

    for ($i=0; $i<count($setpdf->printList); $i++) {
        $setpdf->studentID = $setpdf->printList[$i]['studentID'];
        $studentDetail = $setpdf->readStudentDetail();
        $row = $studentDetail->fetch();
        $setpdf->officialName = $row['officialName'];
        $setpdf->firstName = $row['firstName'];
        $setpdf->preferredName = $row['preferredName'];
        $setpdf->surname = $row['surname'];
        $setpdf->rollOrder = $row['rollOrder'];
        $setpdf->studentEmail = $row['email'];
        if (! empty($row['dateStart']) ) {
            $setpdf->studentDateStart = $row['dateStart'];
        } else {
            $setpdf->studentDateStart = "2000-01-01";
        }
        if (! empty($row['dateEnd']) ) {
            $setpdf->studentDateEnd = $row['dateEnd'];
        } else {
            $setpdf->studentDateEnd = "2099-01-01";
        }
        $setpdf->studentAbrName = str_replace("'", "", $row['surname'].substr($row['firstName'], 0, 1));
        $dob = $row['dob'];
        if ($dob != '' && substr($dob, 0, 4) != '0000') {
            $dob = date('d/m/Y', strtotime($dob));
        } else {
            $dob = '';
        }
        $setpdf->dob = $dob;

        // Retrieve filename from DB?
        /* $reportName =
                $setpdf->schoolYearName.'-'.
                $setpdf->yearGroupName.'-'.
                $setpdf->term.'-'.
                intval($setpdf->studentID).'-'.
                $setpdf->studentAbrName.".pdf";
        */

        $reportName = '';
        $fileName = '';

        $reportName = $setpdf->readLastStudentReportFilename();
        if (!empty($reportName)) {
            $fileName = $archiveFolderPath.$reportName;
        }

        // Check if report file exists and is readable, only send emails if it does!
        if (!empty($fileName) && is_readable($fileName)) {
            // Check student grade hack, just look at email address
            $splitStudentEmail = explode('@', $setpdf->studentEmail, 2);
            //$text .= $setpdf->studentEmail."<br />";
            if (!empty($splitStudentEmail[0])) {
                $gradYear = substr($splitStudentEmail[0],-2);
                //$text .= $gradYear."<br />";

                if (ctype_digit($gradYear) ) {
                    // echo "is digit\n";
                    $gradYear = intval($gradYear);

                    // BIS UGLY QUICK HACK TO SEND REPORT CARDS TO gr6 or higher
                    // Current Year is 2017-2018, grade 12 graduates in 2018
                    // 2018 + 6 = 2024, so grade 6 usernames end in 24
                    if ($gradYear <= 24){
                        // echo "student is in grade 6 or higher";
                        /* sendmail($guid,
                            $to,
                            $toName=null,
                            $from=null,
                            $fromName=null,
                            $emailSubject=null,
                            $body=null,
                            $reportFilename=null,
                            $replyTo=null,
                            $replyToName=null */

                        if (!empty($setpdf->studentEmail)) {
                            // from is left default
                            $ok = sendmail($guid, $setpdf->studentEmail, $setpdf->officialName, null, null, "Report Card for ".$setpdf->officialName, null, $fileName);
                            if ($ok) {
                                $text .= "Mail sent to STUDENT: ".$setpdf->officialName.' ('.$setpdf->studentEmail.')<br />';
                            } else {
                                $text .= "<b>ERROR:</b> Failed to send to STUDENT: ".$setpdf->officialName.' ('.$setpdf->studentEmail.')<br />';
                            }
                        } else {
                            $text .= "<b>WARNING:</b> No email address for STUDENT ".$setpdf->officialName.'<br />';
                        }
                    }
                }
            }


            // Send email to parents
            $rs = $setpdf->readParentDetail();
            while ($row = $rs->fetch()) {
                $to = $row['email'];
                $parentName = $row['title'].' '.$row['surname'];
                if (!empty($to)) {
                    // from is left default
                    $ok = sendmail($guid, $to, $parentName, null, null, "Report Card for ".$setpdf->officialName, null, $fileName);
                    if ($ok) {
                        $text .= "Mail sent to PARENT: ".$parentName.' - '.$setpdf->officialName.' ('.$to.')<br />';
                    } else {
                        $text .= "<b>ERROR:</b> Failed to send to PARENT: ".$parentName.' - '.$setpdf->officialName.' ('.$to.')<br />';
                    }
                } else {
                    $text .= "<b>WARNING:</b> No email address for parent of ".$parentName.' - '.$setpdf->officialName.'<br />';
                }
            }
        } else {
            // Report file is not readable, don't send emails and just output error on next page
            $fileName = empty($fileName) ? "NO FILENAME FOUND" : $fileName;
            $text .= "ERROR: Report file for ".$setpdf->officialName.' does not exist: '.$fileName.'<br />';
        }
    } // end rollGroup while loop
    // DEBUG
    //$text.= nl2br(print_r($setpdf, True));

    /*
    $returnPath = $_SESSION[$guid]["absoluteURL"]."/index.php?q=/modules/".$_SESSION[$guid]["module"]."/pdfmail.php";
    $text .= "<p>&nbsp;</p>";
    $text .= "<p>";
        $text .= "<a href=\"".$returnPath."\">Return to previous page</a>";
    $text .= "</p>";
    */
    /*
    // return to class list page
    $returnPath = $_SESSION[$guid]["absoluteURL"]."/index.php?q=/modules/".$_SESSION[$guid]["module"]."/pdfmail_sent.php".
            "&yearGroupID=$setpdf->yearGroupID".
            "&schoolYearID=$setpdf->schoolYearID".
            "&rollGroupID=$setpdf->rollGroupID".
            "&reportID=$setpdf->reportID".
            "&text=$text";

    header("location:$returnPath");
    */
    $returnPath = $_SESSION[$guid]["absoluteURL"]."/index.php?q=/modules/".$_SESSION[$guid]["module"]."/pdfmail_sent.php";
    echo "<form name='sentForm' method='post' action='$returnPath'>";
        echo "<input type='hidden' name='yearGroupID' value='".$setpdf->yearGroupID."' />";
        echo "<input type='hidden' name='schoolYearID' value='".$setpdf->schoolYearID."' />";
        echo "<input type='hidden' name='rollGroupID' value='".$setpdf->rollGroupID."' />";
        echo "<input type='hidden' name='reportID' value='".$setpdf->reportID."' />";
        echo "<input type='hidden' name='text' value='$text' />";
    echo "</form>";

    ?>
    <script>
        document.forms['sentForm'].submit();
    </script>
    <?php
}


function sendmail($guid,
    $to,
    $toName=null,
    $from=null,
    $fromName=null,
    $emailSubject=null,
    $body=null,
    $reportFilename=null,
    $replyTo=null,
    $replyToName=null
    ) {

    // APPLY DEFAULT VALUES ///////////////////////
    $toName = is_null($toName) ? $to : $toName; // Use email address of $to as name if not specified
    $from = is_null($from) ? "noreply@baliis.net" : $from;
    $fromName = is_null($fromName) ? "BIS" : $fromName;
    $emailSubject = is_null($emailSubject) ? "Message from Bali Island School" : $emailSubject;
    $defaultBody = <<<EOD
Dear $toName<br><br>
Please find attached the progress report for this semester. <br><br>
If you have any questions concerning the report please contact the secretary.<br><br>
Kind regards.<br><br>
Bali Island School<br>
Jalan Danau Buyan IV no.15, Sanur, Denpasar, Bali, Indonesia<br>
Phone: (+62/0)361 288770 / Website: http://baliinternationalschool.com<br>
EOD;

    $body = is_null($body) ? $defaultBody : $body;
    // $reportFilename can stay as null, will try to attach below only if not null
    $replyTo = is_null($replyTo) ? $from : $replyTo;
    $replyToName = is_null($replyToName) ? $fromName : $replyToName;
    /////////// END DEFAULT VALUES ////////////////

    //$mail = new PHPMailer(); // defaults to using php "mail()"
    $mail = getGibbonMailer($guid); // defaults to using php "mail()"

    // DEBUG
    //$mail->SMTPDebug = 2; // Set to debug client and server messages
    //$mail->Debugoutput = "error_log"; // send debug to the php log file

    $mail->setFrom($from, $fromName);
    $mail->addReplyTo($replyTo, $replyToName);
    $mail->addAddress($to, $toName);

    $mail->Subject = $emailSubject;

    $mail->msgHTML($body);
    // Using core Gibbon function
    $mail->AltBody = emailBodyConvert($body);

    //$mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

    if (!is_null($reportFilename)) {
        $mail->addAttachment($reportFilename);      // attachment
    }

    return $mail->Send();
}
