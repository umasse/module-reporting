<?php

/*
 * Project:
 * Author:   Andy Statham
 * Date:
 */
// in case we need more functions

class MYPDF extends TCPDF {

    public function loadCustomFonts() {
        // Add PT Sans fonts
        //$tmp=$this->AddFont('pt_sans');
        //$tmp=$this->AddFont('pt_sans_b');
    }
    
    //Page header
    public function Header() {
        GLOBAL $setpdf;
        
    }

    // Page footer
    public function Footer() {
        GLOBAL $setpdf;
        //$text = "A Community, Learning for Tomorrow";
        $text = "Bali Island School - 2017-2018 - Term 1 - ".$setpdf->officialName." - ".$setpdf->yearGroupName;


        //$this->SetFont('helvetica', 'BI', 8);
        $this->SetFont('pt_sans', '', 10);        
        $this->Cell(0, 0, $text, 0, 0, 'C');
    }
}

class createpdf {

    var $class;
    var $msg;

    var $schoolYearID;
/*
    var $logo = "images/hly_logo.png";
    var $imgOrchard = "images/orchard.png";
    var $imgIB = "images/ib.png";
    var $imgCIS = "images/cis.png";
*/
    var $topMargin = 18;
    var $footerMargin = 20;
    var $pageHeight = 278;

    var $leftMargin = 13;
    //var $pageWidth = 180;
    var $critCol1 = 60;
    var $critCol2 = 20;

    var $small = 9;
    var $standard = 10;
    var $heading1 = 12;
    var $gray = 200;
    
    var $insertList = array(
        "Official name", "First name", "Preferred name", "Surname", "Class",
        "School Year", "Year Group", "Roll Group", "Student ID", "Roll Group Teacher", "Student DOB"
    );

    /* function setpdf($guid, $connection2) { */
    function __construct($guid, $connection2) {
        $this->guid = $guid;
        $this->connection2 = $connection2;

        //$this->critCol3 = $this->pageWidth - ($this->critCol1 + $this->critCol2);
        //$this->tick = $_SESSION[$guid]['absoluteURL']."/modules/".$_SESSION[$guid]["module"].'/images/tick.png';

        // get value of selected year
        //$this->repEdit      = $_SESSION[$guid]['repEdit'];

        $this->schoolYearID = $_POST['schoolYearID'];
        $this->schoolYearName = $this->findSchoolYearName();
        $this->yearGroupID = $_POST['yearGroupID'];
        $this->rollGroupID = $_POST['rollGroupID'];
        $this->reportID = $_POST['reportID'];
        $this->showLeft = $_POST['showLeft'];
    
        // get teacher and roll group names
        $this->findRollGroup();
        
        $this->reportDetail = readReportDetail($connection2, $this->reportID);
        $reportRow = $this->reportDetail->fetch();
        $this->term = $reportRow['reportNum'];
        $this->gradeScale = $reportRow['gradeScale']; // id for grade scale to be used for assessment
        $this->gradeList = readGradeList($connection2, $this->gradeScale);
        $this->orientation = $reportRow['orientation'];
        if ($this->orientation == 1) {
            $this->pageWidth = 180;
            $this->pageOrientation = 'P';
        } else {
            $this->pageWidth = 240;
            $this->pageOrientation = 'L';
        }
        
        $_SESSION[$guid]['archivePath'] = $_SESSION[$guid]['absolutePath']."/archive/";
        
        //$this->term = $this->findTerm($connection2, $this->reportID);
        $this->yearGroupName = $this->findYearGroupName($connection2, $this->yearGroupID);

        $this->printList = array();
        foreach ($_POST AS $key => $year) {
            $subdata = array();
            if (substr($key, 0, 5) == 'check') {
                $subdata['studentID'] = substr($key, 5);
                $this->printList[] = $subdata;
            }
        }
        
        // download the file to local computer when button is clicked
        if (isset($_POST['downloadPDF'])) {
            $this->download();
            exit();
        }
    }
    ////////////////////////////////////////////////////////////////////////////

    
    ////////////////////////////////////////////////////////////////////////////
    function download() {

        $files = $this->printList;
        
        //$basepath = "../../archive/reporting/";
        $folderabsolutebasepath = $_SESSION['archiveFilePath'];

        // remove any zip files over 5 minutes old
        $now = strtotime(date("Y-m-d H:i:s"));
        foreach (glob($folderabsolutebasepath."*.zip") AS $filename) {
            if ((filemtime($filename) + 300) < $now) {
                unlink($filename);
            }
        }

        $overwrite = true;
        if (!file_exists($folderabsolutebasepath)) {
            mkdir($folderabsolutebasepath);
        }

        $destination = $folderabsolutebasepath."download_".time()."_".intval($_SESSION[$this->guid]['gibbonPersonID']).".zip";

        //vars
        $valid_files = array();

        //if files were passed in...
        if (is_array($files)) {
            //cycle through each file
            foreach($files as $student_id) {
                //make sure the file exists
                $this->studentID = $student_id['studentID'];
                $archive_name = $this->read_archive_name();
                $file = $folderabsolutebasepath.$this->schoolYearName."/".$archive_name;
                if (file_exists($file)) {
                    $valid_files[] = $file;
                }
            }
        }
        //if we have good files...
        $ok = true;

        if (count($valid_files)) {
            //create the archive
            $zip = new ZipArchive();
            touch($destination);
            
            if ($zip->open($destination, ZipArchive::OVERWRITE) !== true) {
                $msg = "Cannot create zip file";
                $ok = false;
            }

            if ($ok) {
                //add the files
                foreach($valid_files as $file) {
                    $zip->addFile($file, basename($file));
                }
                $zip->close();

                if (!file_exists($destination)) {
                    $msg = "Zip file not found";
                    $ok = false;
                } 

                if ($ok) {
                    $file_name = basename($destination);

                    header("Content-Type: application/zip");
                    header("Content-Disposition: attachment; filename='$file_name'");
                    header("Content-Length: " . filesize($destination));
                    header('HTTP/1.0 200 OK', true, 200);

                    set_time_limit(0);
                    //$file = fopen($destination, "rb");

                    //while(!feof($file)) {
                        //print(fread($file, 1024*8));
                        //ob_flush();
                        //flush();
                    //}
                    //fclose($file);
                    readfile($destination);
                }
            }
        } else {
            $ok = false;
            $msg = "No valid files";
        }
        if (!$ok) {
            echo $msg;
            die();
        } else {
            unset($destination);
            echo "<script>window.close();</script>";
        }
    }
    ////////////////////////////////////////////////////////////////////////////
    
    ////////////////////////////////////////////////////////////////////////////
    function read_archive_name() {
        // read database to see if there is a file recorded for this student
        try {
            $data = array(
                'studentID' => $this->studentID,
                'reportID' => $this->reportID
            );
            $sql = "SELECT *
                FROM arrArchive
                WHERE studentID = :studentID
                AND reportID = :reportID";
            $rs = $this->connection2->prepare($sql);
            $rs->execute($data);
        } catch(Exception $e) {
            print "<div>" . $e->getMessage() . "</div>" ;
            die();
        }
        // if record exists return name and date it was created
        if ($rs->rowCount() > 0) {
            $row = $rs->fetch();
            $reportName = $row['reportName'];
        } else {
            $reportName = '';
        }
        return $reportName;
    }
    ////////////////////////////////////////////////////////////////////////////
    
    ////////////////////////////////////////////////////////////////////////////
    function findSchoolYearName() {
        $data = array(
            'schoolYearID' => $this->schoolYearID
        );
        $sql = "SELECT name AS schoolYearName
            FROM gibbonSchoolYear
            WHERE gibbonSchoolYearID = :schoolYearID";
        $rs = $this->connection2->prepare($sql);
        $rs->execute($data);
        $row = $rs->fetch();
        return $row['schoolYearName'];
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function readStudentDetail() {
        $data = array(
            'studentID' => $this->studentID,
            'schoolYearID' => $this->schoolYearID
        );
        $sql = "SELECT surname, firstName, preferredName, officialName, dob, rollOrder, email, dateStart, dateEnd
            FROM gibbonPerson
            INNER JOIN gibbonStudentEnrolment
            ON gibbonStudentEnrolment.gibbonPersonID = gibbonPerson.gibbonPersonID
            WHERE gibbonPerson.gibbonPersonID = :studentID
            AND gibbonStudentEnrolment.gibbonSchoolYearID = :schoolYearID";
        $rs = $this->connection2->prepare($sql);
        $rs->execute($data);
        return $rs;
    }
    ////////////////////////////////////////////////////////////////////////////
    
    ////////////////////////////////////////////////////////////////////////////
    function checkFolder() {
        // check if archive folder exists
        //echo $_SESSION[$this->guid]['archivePath'];
        
        $path = $_SESSION[$this->guid]['archivePath'];
        
        if (!is_dir($path)) {
            mkdir($path);
        }
        makeIndex($path);

        $path = $path.'/reporting';
        if (!is_dir($path)) {
            mkdir($path);
        }
        makeIndex($path);

        $path = $path.'/'.$this->schoolYearName;
        if (!is_dir($path)) {
            mkdir($path);
        }
        makeIndex($path);
    }

    function checkLanguage($text) {
        $language = 'english';
        if (preg_match("/\p{Han}+/u", $text)) {
            $language = 'chinese';
        }
        return strtolower($language);
    }

    function makeIndex($path) {
       // check there is an index file
        $index = 'index.html';
        $pathIndex = $path.'/'.$index;
        if (!file_exists($pathIndex)) {
            echo $pathIndex;
            echo "<br />";
            $handle = fopen($pathIndex, 'w') or die('Cannot open file:  '.$pathIndex); //implicitly creates file
            fclose($handle);
        }
    }

    ////////////////////////////////////////////////////////////////////////////////
    function findReportName() {
        $data = array('reportID' => $this->reportID);
        $sql = "SELECT *
            FROM arrReport
            WHERE arrReportID = :reportID";
        $rs = $this->connection2->prepare($sql);
        $rs->execute($data);
        $this->reportName = '';
        $this->reportDate = '';
        if ($rs->rowCount() > 0) {
            $row = $rs->fetch();
            $this->reportName = substr($row['arrReportName'], strlen($this->yearGroupName));
            $this->reportDate = $row['arrReportDate'];
            $this->dateStart = $row['dateStart'];
            $this->dateEnd = $row['dateEnd'];
        }
    }
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    function findTerm() {
        // find which term the current report is for
        // NOT AT ALL THE TERM ID WE SHOULD EXPECT!!!!
        $data = array('reportID' => $this->reportID);
        $sql = "SELECT reportNum
            FROM arrReport
            WHERE arrReportID = :reportID";
        $rs = $this->connection2->prepare($sql);
        $rs->execute($data);
        $row = $rs->fetch();
        return $row['arrReportNum'];
    }
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    function findRepAccess() {
        // check if use should have editing access to the reports

        // check if administrator
        $admin = read_access($this->connection2, 'admin', $_SESSION[$this->guid]["gibbonPersonID"]);

        //   or slt
        $slt = read_access($this->connection2, 'senior', $_SESSION[$this->guid]["gibbonPersonID"]);

        $access = 1;
        if ($admin || $slt) {
            $access = 2;
        }

        return $access;
    }
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    function findRollGroup() {
        $data = array(
            'rollGroupID' => $this->rollGroupID
        );
        $sql = "SELECT name, nameShort, CONCAT(firstName, ' ', surname) AS teacherName
            FROM gibbonRollGroup
            INNER JOIN gibbonPerson
            ON gibbonRollGroup.gibbonPersonIDTutor = gibbonPerson.gibbonPersonID
            WHERE gibbonRollGroupID = :rollGroupID";
        //print $sql;
        //print_r($data);
        $rs = $this->connection2->prepare($sql);
        $rs->execute($data);
        $rollGroupName = '';
        $rollGroupNameShort = '';
        $classTeacher = '';
        if ($rs->rowCount() > 0) {
            $row = $rs->fetch();
            $rollGroupName = $row['name'];
            $rollGroupNameShort = $row['nameShort'];
            $classTeacher = $row['teacherName'];
        }
        $this->rollGroupName = $rollGroupName;
        $this->rollGroupNameShort = $rollGroupNameShort;
        $this->classTeacher = $classTeacher;
    }
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    function findYearGroupName() {
        // find which term the current report is for
        $data = array('yearGroupID' => $this->yearGroupID);
        $sql = "SELECT name
            FROM gibbonYearGroup
            WHERE gibbonYearGroupID = :yearGroupID";
        $rs = $this->connection2->prepare($sql);
        $rs->execute($data);
        $row = $rs->fetch();
        return $row['name'];
    }
    ////////////////////////////////////////////////////////////////////////////////


    ////////////////////////////////////////////////////////////////////////////
    // text section
    ////////////////////////////////////////////////////////////////////////////
    function textSection($pdf) {
        $html = '';
        // read details
        $data = array(
            'sectionID' => $this->sectionID
        );
        $sql = "SELECT *
            FROM arrReportSectionDetail
            WHERE sectionID = :sectionID";
        $rs = $this->connection2->prepare($sql);
        $rs->execute($data);
        if ($rs->rowCount() > 0) {
            $row = $rs->fetch();
            $html = $row['sectionContent'];
            // replace codes
            $html = str_replace("[Preferred name]", $this->preferredName, $html);
            $html = str_replace("[First name]", $this->firstName, $html);
            $html = str_replace("[Surname]", $this->surname, $html);
            $html = str_replace("[Official name]", $this->officialName, $html);
            $html = str_replace("[Class]", $this->rollGroupName, $html);
            $html = str_replace("[Roll Number]", $this->rollOrder, $html);

            ///////////////////////////////
            // EXTRA FIELDS ADDED AT BIS
            $html = str_replace("[School Year]", $this->schoolYearName, $html);
            $html = str_replace("[Year Group]", $this->yearGroupName, $html);
            $html = str_replace("[Classname Short]", $this->rollGroupNameShort, $html);
            $html = str_replace("[Student ID]", $this->studentID, $html);
            $html = str_replace("[Roll Group Teacher]", $this->classTeacher, $html);
            $html = str_replace("[Student DOB]", $this->dob, $html);
            $html = str_replace("[Student email]", $this->studentEmail, $html);
            ///////////////////////////////

            ///////////////////////////////
            // FIX FOR ALLOWING CARRIAGE RETURNS
            // https://stackoverflow.com/questions/28835690/tcpdf-writehtmlcell-new-line-issue
            $needles = array("<br>", "&#13;", "<br/>", "\n");
            $replacement = "<br />";
            $html = str_replace($needles, $replacement, $html);
            
            $pdf->writeHTML($html);
        }
        return $html;
    }

    ////////////////////////////////////////////////////////////////////////////
    // subject section
    ////////////////////////////////////////////////////////////////////////////
    function subjectReportRow($pdf) {
        $debug_html = '';
        $sublist = readStudentClassListNoRepeat($this->connection2, $this->studentID, $this->schoolYearID);
        $this->rowHeight = 12;
        $this->commentHeight = 46;
        foreach ($sublist AS $row) {
            $subjectID = $row['subjectID'];
            $subjectName = $row['subjectName'];
            $teacherName = $row['teacherName'];
            //$teacherName = getTeacherName($this->connection2, $row['classID']);
            $subreport = readSubReport($this->connection2, $this->studentID, $subjectID, $this->reportID);
            $criteriaList = readCriteriaGrade($this->connection2, $this->studentID, $subjectID, $this->reportID);
            $row_subject = $subreport->fetch();
            $comment = $row_subject['subjectComment'];
            $html = '';
            $html .= <<<EOD
<style>
body {
    font-size: 12px;
    font-family: pt_sans, PT Sans, helvetica, sans-serif;
}
.subjectname {
    color: #999999;
    font-weight: bold;
}
.teachername {
    font-style: italic;
}
.gradeTable table {
    width: 100%;
}
td {
    font-size: 10px;
}
.gradeTable th {
    border: 1px solid #cccccc;
    background-color: #dddddd;
    padding: 1px;
}
.gradeTable td {
    border: 1px solid #cccccc;
    padding: 1px;
}
.col1 {
    text-align: left;
    width: 70%;
}
.col2 {
    text-align: center;
    width: 30%;
}
.commentHead {
    width: $this->pageWidth;
}
</style>
EOD;
            // $html .= '.commentHead {\n	width:'. $this->pageWidth .'mm}';

            if ($criteriaList->rowCount() > 0 || $comment != '') {
                $html .= '<table cellpadding="4">';
                    $html .= '<tr><td class="subjectname">'.$subjectName.'</td></tr>';
                    $html .= '<tr><td class="teachername">'.$teacherName.'</td></tr>';
                $html .= '</table>';

                if ($criteriaList->rowCount() > 0) {
                    $html .= '<table class="gradeTable" cellpadding="4">';
                        $html .= '<tr>';
                            $html .= '<th class="col1">Criteria</th>';
                            $html .= '<th class="col2">Mark</th>';
                            $html .= '<th class="col2">Percent</th>';
                            $html .= '<th class="col2">Grade</th>';
                        $html .= '</tr>';

                        while ($row_criteria = $criteriaList->fetch()) {
                            $criteriaName = $row_criteria['criteriaName'];
                            $grade = findGrade($this->gradeList, $row_criteria['gradeID']);
                            $mark = $row_criteria['mark'];
                            $percent = $row_criteria['percent'];
                            $html .= '<tr>';
                                $html .= '<td class="col1">'.$criteriaName.'</td>';
                                $html .= '<td class="col2">';
                                    $html .= $mark;
                                $html .= "</td>";
                                $html .= '<td class="col2">';
                                    $html .= $percent;
                                $html .= "</td>";
                                $html .= '<td class="col2">';
                                    $html .= $grade;
                                $html .= "</td>";                                
                            $html .= "</tr>";
                        }
                    $html .= '</table>';
                }

                if ($comment != '') {
                    $html .= '<table class="gradeTable" cellpadding="4">';
                        $html .= '<tr>';
                            $html .= '<th colspan="2">Comment</th>';
                        $html .= '</tr>';
                        $html .= '<tr>';
                            $html .= '<td colspan="2">';
                                $html .= nl2br($comment);
                            $html .= '</td>';
                        $html .= '</tr>';
                    $html .= '</table>';
                }
                $html .= '<div>&nbsp;</div>';

                $cp =  $pdf->getPage(); // current page number
                $pdf->startTransaction();
                $pdf->writeHTML($html, true, false, true, false, '');
                if ($pdf->getPage() != $cp) {
                    $pdf->rollBackTransaction(true);
                    $pdf->addPage();
                    $pdf->setY($this->topMargin);
                    $pdf->writeHTML($html, true, false, true, false, '');
                } else {
                    $pdf->commitTransaction();
                }
            }
            $debug_html.=$html;
        }

        return $debug_html;
    }
    ////////////////////////////////////////////////////////////////////////////

    function subjectReportRowNonEmptyAtt($pdf) {
        // Prints only Criteria that have valid Grades
        // DOES NOT PRINT MARK AND PERCENT!!
        $debug_html='';
        $sublist = readStudentClassListNoRepeat($this->connection2, $this->studentID, $this->schoolYearID);
        $this->rowHeight = 12;
        $this->commentHeight = 46;
        foreach ($sublist AS $row) {
            $subjectID = $row['subjectID'];
            $subjectDescription = $row['subjectDescription'];
            $subjectName = $row['subjectName'];
            $teacherName = $row['teacherName'];
            //$teacherName = getTeacherName($this->connection2, $row['classID']);
            $subreport = readSubReport($this->connection2, $this->studentID, $subjectID, $this->reportID);
            $criteriaList = readCriteriaGrade($this->connection2, $this->studentID, $subjectID, $this->reportID);
            $row_subject = $subreport->fetch();
            $comment = $row_subject['subjectComment'];
            $html = '';
            $html .= <<<EOD
<style>
body {
    font-size: 12px;
    font-family: pt_sans, PT Sans, helvetica, sans-serif;
}
.subjectname {
    color: #999999;
    font-weight: bold;
    font-family: pt_sans_b, PT Sans, helvetica, sans-serif;
}
.teachername {
    font-style: italic;
    font-family: pt_sans_i, PT Sans, helvetica, sans-serif;
}
.gradeTable table {
    width: 100%;
    font-family: pt_sans, PT Sans, helvetica, sans-serif;
}
td {
    font-size: 10px;
    font-family: pt_sans, PT Sans, helvetica, sans-serif;
}
.gradeTable th {
    border: 1px solid #cccccc;
    background-color: #dddddd;
    padding: 1px;
    font-family: pt_sans_b, PT Sans, helvetica, sans-serif;
}
.gradeTable td {
    border: 1px solid #cccccc;
    padding: 1px;
    font-family: pt_sans, PT Sans, helvetica, sans-serif;
}
.col1 {
    text-align: left;
    font-family: pt_sans, PT Sans, helvetica, sans-serif;
    width: 70%;
}
.col2 {
    text-align: center;
    font-family: pt_sans, PT Sans, helvetica, sans-serif;
    width: 30%;
}
.commentHead {
    width: $this->pageWidth;
    font-family: pt_sans, PT Sans, helvetica, sans-serif;
}
</style>
EOD;

            if ($criteriaList->rowCount() > 0 || !empty($comment)) {
                $html .= '<table cellpadding="4">';
                    $html .= '<tr><td class="subjectname">'.$subjectName.'</td></tr>';
                    $html .= '<tr><td class="teachername">'.$teacherName.'</td></tr>';
                $html .= '</table>';

                if (!empty($subjectDescription)) {
                    $html .= '<table class="gradeTable" cellpadding="4">';
                        $html .= '<tr>';
                            $html .= '<th colspan="2">Course Description</th>';
                        $html .= '</tr>';
                        $html .= '<tr>';
                            $html .= '<td colspan="2">';
                                $html .= nl2br($subjectDescription);
                            $html .= '</td>';
                        $html .= '</tr>';
                    $html .= '</table>';
                }


                if ($criteriaList->rowCount() > 0) {
                    $html .= '<table class="gradeTable" cellpadding="4">';
                        $html .= '<tr>';
                            $html .= '<th class="col1">Criteria</th>';
                            $html .= '<th class="col2">Grade</th>';
                        $html .= '</tr>';

                        while ($row_criteria = $criteriaList->fetch()) {
                            $criteriaName = $row_criteria['criteriaName'];
                            $grade = findGrade($this->gradeList, $row_criteria['gradeID']);
                            $mark = $row_criteria['mark'];
                            $percent = $row_criteria['percent'];
                            if ($grade != "-") {
                                $html .= '<tr>';
                                    $html .= '<td class="col1">'.$criteriaName.'</td>';
                                    $html .= '<td class="col2">';
                                        $html .= $grade;
                                    $html .= "</td>";                                
                                $html .= "</tr>";
                            }
                        }
                    $html .= '</table>';
                }

                if (!empty($comment)) {
                    $html .= '<table class="gradeTable" cellpadding="4">';
                        $html .= '<tr>';
                            $html .= '<th colspan="2">Comment</th>';
                        $html .= '</tr>';
                        $html .= '<tr>';
                            $html .= '<td colspan="2">';
                                $html .= nl2br($comment);
                            $html .= '</td>';
                        $html .= '</tr>';
                    $html .= '</table>';
                }
                $html .= '<div>&nbsp;</div>';

                $cp =  $pdf->getPage(); // current page number
                $pdf->startTransaction();
                $pdf->writeHTML($html, true, false, true, false, '');
                if ($pdf->getPage() != $cp) {
                    $pdf->rollBackTransaction(true);
                    $pdf->addPage();
                    $pdf->setY($this->topMargin);
                    $pdf->writeHTML($html, true, false, true, false, '');
                } else {
                    $pdf->commitTransaction();
                }
            }
            $debug_html.=$html;
        }
        
        return $debug_html;
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function subjectReportColumn($pdf) {
        $sublist = readStudentClassListNoRepeat($this->connection2, $this->studentID, $this->schoolYearID);
        $this->rowHeight = 12;
        $this->commentHeight = 46;
        $html = '';
        //$col1Width = $this->pageWidth * 60 /100;
        //$col2Width = $this->pageWidth * 40 /100;
        $numcol = 0;
        $colhead = array();
        foreach ($sublist AS $row) {
            $subjectID = $row['subjectID'];
            $criteriaList = readCriteriaGrade($this->connection2, $this->studentID, $subjectID, $this->reportID);
            if (count($criteriaList) > $numcol && count($colhead) == 0) {
                while($row = $criteriaList->fetch()) {
                    $colhead[] = $row['criteriaName'];
                }
            }
        }
        if (count($colhead) > 0) {
            $gradeWidth = (50 / count($colhead));
        } else {
            $gradeWidth = 0;
        }

        $html .= '<style>';
            $html .= 'body{font-size:12px; font-family: pt_sans, PT Sans, helvetica, sans-serif;}';
            $html .= '.subjectname {color:#999999; font-weight:bold;}';
            $html .= '.teachername {font-style:italic;}';
            $html .= '.smalltext {font-size:11px;}';
            $html .= '.gradeTable table{width:100%;}';
            $html .= '.gradeTable th {border: 1px solid #cccccc; background-color: #dddddd; padding:1px;}';
            $html .= '.gradeTable td {border: 1px solid #cccccc; padding:1px;}';
            //$html .= '.col1 {text-align:left; width:110mm;}';
            //$html .= '.col2 {text-align:center; width:70mm;}';
            $html .= '.col1 {text-align:left; width:30%}';
            $html .= '.col2 {text-align:left; width:20%}';
            $html .= '.col3 {text-align:center; width:'.$gradeWidth.'%}';
            $html .= '.commentHead {width:100%;}';
        $html .= '</style>';

        $html .= '<table class="gradeTable" cellpadding="4">';
            $html .= '<tr>';
                $html .= '<th class="col1">Subject</th>';
                $html .= '<th class="col2">Teacher</th>';
                for ($i=0; $i<count($colhead); $i++) {
                    $html .= '<th class="col3">'.$colhead[$i].'</th>';
                }
            $html .= '</tr>';

            foreach ($sublist AS $row) {
                $subjectID = $row['subjectID'];
                $subjectName = $row['subjectName'];
                $teacherName = $row['teacherName'];
                //$teacherName = getTeacherName($this->connection2, $row['classID']);
                $subreport = readSubReport($this->connection2, $this->studentID, $subjectID, $this->reportID);
                $criteriaList = readCriteriaGrade($this->connection2, $this->studentID, $subjectID, $this->reportID);
                $row_subject = $subreport->fetch();
                $comment = $row_subject['subjectComment'];

                if ($criteriaList->rowCount() > 0 || $comment != '') {
                    if ($criteriaList->rowCount() > 0) {
                        $html .= '<tr>';
                            $html .= '<td class="col1">'. $row['subjectName'].'</td>';
                            $html .= '<td class="col2">'.$row['teacherName'].'</td>';

                            while ($row_criteria = $criteriaList->fetch()) {
                                $criteriaName = $row_criteria['criteriaName'];
                                $grade = findGrade($this->gradeList, $row_criteria['gradeID']);
                                $html .= '<td class="col3">';
                                    $html .= $grade;
                                $html .= "</td>";
                            }
                        $html .= '</tr>';
                    }
                }
            }
        $html .= '</table>';

        $cp =  $pdf->getPage(); // current page number
        $pdf->startTransaction();
        $pdf->writeHTML($html, true, false, true, false, '');
        if ($pdf->getPage() != $cp) {
            $pdf->rollBackTransaction(true);
            $pdf->addPage();
            $pdf->setY($this->topMargin);
            $pdf->writeHTML($html, true, false, true, false, '');
        } else {
            $pdf->commitTransaction();
        }
        
        return $html;
    }
    ////////////////////////////////////////////////////////////////////////////
    
    
    ////////////////////////////////////////////////////////////////////////////
    // pastoral section
    ////////////////////////////////////////////////////////////////////////////
    function pastoralReport($pdf) { 
        //$sublist = readStudentClassList($this->connection2, $this->studentID, $this->schoolYearID);
        $this->rowHeight = 12;
        $this->commentHeight = 46;
        $html = '';
        $html .= '<style>';
            $html .= 'body{font-size:12px; font-family: pt_sans, PT Sans, helvetica, sans-serif;}';
            $html .= '.subjectname {color:#999999; font-weight:bold;}';
            $html .= '.teachername {font-style:italic;}';
            $html .= '.smalltext {font-size:11px;}';
            $html .= '.gradeTable table{width:100%;}';
            $html .= '.gradeTable th {border: 1px solid #cccccc; background-color: #dddddd; padding:1px;}';
            $html .= '.gradeTable td {border: 1px solid #cccccc; padding:1px;}';
            $html .= '.col1 {text-align:left; width:60%}';
            $html .= '.col2 {text-align:center; width:40%;}';
            $html .= '.commentHead {width:100%;}';
        $html .= '</style>';
        
        $subjectID = 0;
        $subjectName = "Pastoral";
        $teacherName = $this->classTeacher;
        $subreport = readSubReport($this->connection2, $this->studentID, 0, $this->reportID);
        //$criterialist = readCriteriaList($connection2, $subjectID);
        $criteriaList = readCriteriaGrade($this->connection2, $this->studentID, 0, $this->reportID);
        $row_subject = $subreport->fetch();
        $comment = $row_subject['subjectComment'];

        if ($criteriaList->rowCount() > 0 || $comment != '') {
            $html .= '<table cellpadding="4">';
                $html .= '<tr><td class="subjectname">'.$subjectName.'</td></tr>';
                $html .= '<tr><td class="teachername">'.$teacherName.'</td></tr>';
            $html .= '</table>';

            if ($criteriaList->rowCount() > 0) {
                $html .= '<table class="gradeTable" cellpadding="4">';
                    $html .= '<tr>';
                        $html .= '<th class="col1">Criteria</th>';
                        $html .= '<th class="col2">Grade</th>';
                    $html .= '</tr>';

                    while ($row_criteria = $criteriaList->fetch()) {
                        $criteriaName = $row_criteria['criteriaName'];
                        $grade = findGrade($this->gradeList, $row_criteria['gradeID']);
                        $html .= '<tr>';
                            $html .= '<td class="col1">'.$criteriaName.'</td>';
                            $html .= '<td class="col2">';
                                $html .= $grade;
                            $html .= "</td>";
                        $html .= "</tr>";
                    }
                $html .= '</table>';
            }

            if ($comment != '') {
                $html .= '<table class="gradeTable" cellpadding="4">';
                    $html .= '<tr>';
                        $html .= '<th colspan="2" class="commentHead">Comment</th>';
                    $html .= '</tr>';
                    $html .= '<tr>';
                        $html .= '<td colspan="2">';
                            $html .= nl2br($comment);
                        $html .= '</td>';
                    $html .= '</tr>';
                $html .= '</table>';
            }
            $html .= '<div>&nbsp;</div>';

        }
        
        $cp =  $pdf->getPage(); // current page number
        $pdf->startTransaction();
        $pdf->writeHTML($html, true, false, true, false, '');
        if ($pdf->getPage() != $cp) {
            $pdf->rollBackTransaction(true);
            $pdf->addPage();
            $pdf->setY($this->topMargin);
            $pdf->writeHTML($html, true, false, true, false, '');
        } else {
            $pdf->commitTransaction();
        }

        return $html;
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    // attendance section
    ////////////////////////////////////////////////////////////////////////////
    //$dateStart and $dateEnd refer to the students' first and last day at the school, not the range of dates for the report
    function readStudentAttendanceHistory()
    {
        global $gibbon, $session, $pdo;
        require_once $_SESSION[$this->guid]['absolutePath'].'/modules/Attendance/src/attendanceView.php';
        $attendance = new Module\Attendance\attendanceView($gibbon, $pdo);

        $dateStart = $this->studentDateStart;
        $dateEnd = $this->studentDateEnd;

        $output = '';

        // Find Term for report. Reporting should allow us to link a Report to a specific Term.
        // Right now it doesn't, and organizes reports by increasing number per school year, not
        // good enough. Either fix that, or extend this block to have some sort of configuration.

        // For now, hardcoding current term IDs!!
        //$termName = 'Semester 1';
        // Restructure it all to have an array of Term IDs
        $attendanceterms = [];
        $attendanceterms[0] = [
            'id' => 9,
            'name' => 'Term 1',
            'firstday' => '2017-08-07',
            'lastday' => '2017-10-06'
        ];
        $attendanceterms[1] = [
            'id' => 10,
            'name' => 'Term 2',
            'firstday' => '2017-10-16',
            'lastday' => '2017-12-15'
        ];

        $countSchoolDays = 0;
        $countAbsent = 0;
        $countPresent = 0;
        $countTypes = array();
        $countReasons = array();

        // Check which days are school days
        $days = array();
        $days['Mon'] = 'Y';
        $days['Tue'] = 'Y';
        $days['Wed'] = 'Y';
        $days['Thu'] = 'Y';
        $days['Fri'] = 'Y';
        $days['Sat'] = 'Y';
        $days['Sun'] = 'Y';
        $days['count'] = 7;
        try {
            $dataDays = array();
            $sqlDays = "SELECT nameShort FROM gibbonDaysOfWeek WHERE schoolDay='N'";
            $resultDays = $this->connection2->prepare($sqlDays);
            $resultDays->execute($dataDays);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        // Mark non-school days as N
        while ($rowDays = $resultDays->fetch()) {
            $day = $rowDays['nameShort'];
            if ( isset($days[$day]) ) {
                $days[$day] = 'N';
                --$days['count'];
            }
        }

        // Start looping per term here

        foreach($attendanceterms as $current_term) {

            list($termFirstDayYear, $termFirstDayMonth, $termFirstDayDay) = explode('-', $current_term['firstday']);
            $termFirstDayStamp = mktime(0, 0, 0, $termFirstDayMonth, $termFirstDayDay, $termFirstDayYear);
            list($termLastDayYear, $termLastDayMonth, $termLastDayDay) = explode('-', $current_term['lastday']);
            $termLastDayStamp = mktime(0, 0, 0, $termLastDayMonth, $termLastDayDay, $termLastDayYear);

            $output .= "<table class=\"attendanceHeader\"><tr><td>".$current_term['name']."</td></tr></table>";

            //Count back to first Monday before first day
            $startDayStamp = $termFirstDayStamp;
            while (date('D', $startDayStamp) != 'Mon') {
                $startDayStamp = $startDayStamp - 86400;
            }

            //Count forward to first Sunday after last day
            $endDayStamp = $termLastDayStamp;
            while (date('D', $endDayStamp) != 'Sun') {
                $endDayStamp = $endDayStamp + 86400;
            }

            //Get the special days
            try {
                $dataSpecial = array('gibbonSchoolYearTermID' => $current_term['id']);
                $sqlSpecial = "SELECT name, date FROM gibbonSchoolYearSpecialDay WHERE gibbonSchoolYearTermID=:gibbonSchoolYearTermID AND type='School Closure' ORDER BY date";
                $resultSpecial = $this->connection2->prepare($sqlSpecial);
                $resultSpecial->execute($dataSpecial);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }

            $rowSpecial = null;
            if ($resultSpecial->rowCount() > 0) {
                $rowSpecial = $resultSpecial->fetch();
            }

            $count = 0;
            $weeks = 2;

            $output .= "<table class=\"minihistoryCalendar\" cellspacing=\"0\" style=\"width: 100%\">";
            $output .= "<tr class=\"minihistoryCalendarHead\">";
            for ($w = 0; $w < $weeks; ++$w) {
                if ($days['Mon'] == 'Y') {
                    $output .= "<th>Mon</th>";
                }
                if ($days['Tue'] == 'Y') {
                    $output .= "<th>Tue</th>";
                }
                if ($days['Wed'] == 'Y') {
                    $output .= "<th>Wed</th>";
                }
                if ($days['Thu'] == 'Y') {
                    $output .= "<th>Thu</th>";
                }
                if ($days['Fri'] == 'Y') {
                    $output .= "<th>Fri</th>";
                }
                if ($days['Sat'] == 'Y') {
                    $output .= "<th>Sat</th>";
                }
                if ($days['Sun'] == 'Y') {
                    $output .= "<th>Sun</th>";
                }
            }
            $output .= '</tr>';

            //Make sure we are not showing future dates
            $now = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $end = $endDayStamp;
            if ($now < $endDayStamp) {
                $end = $now;
            }
            //Display grid
            // Changing regional date format to "d-m-Y" e.g. 17-03-2017
            // Original was $_SESSION[$guid]['i18n']['dateFormatPHP']
            for ($i = $startDayStamp;$i <= $end;$i = $i + 86400) {
                if ($days[date('D', $i)] == 'Y') {
                    if (($count % ($days['count'] * $weeks)) == 0 and $days[date('D', $i)] == 'Y') {
                        $output .= "<tr>";
                    }

                    //Before student started at school
                    if ($dateStart != '' and date('Y-m-d', $i) < $dateStart) {
                        $output .= "<td class=\"dayClosed\">".date("d-m-Y", $i)."<br />";
                        $output .= "Before Start Date";
                        $output .= "</td>";
                        ++$count;
                    }
                    //After student left school
                    elseif ($dateEnd != '' and date('Y-m-d', $i) > $dateEnd) {
                        $output .= "<td class=\"dayClosed\">".date("d-m-Y", $i)."<br />";
                        $output .= "After End Date";
                        $output .= "</td>";
                        ++$count;
                    }
                    //Student attending school
                    else {
                        $specialDayStamp = null;
                        if ($rowSpecial != null) {
                            if ($rowSpecial == true) {
                                list($specialDayYear, $specialDayMonth, $specialDayDay) = explode('-', $rowSpecial['date']);
                                $specialDayStamp = mktime(0, 0, 0, $specialDayMonth, $specialDayDay, $specialDayYear);
                            }
                        }

                        if ($i < $termFirstDayStamp or $i > $termLastDayStamp) {
                            $output .= "<td class=\"dayClosed\"></td>";
                            ++$count;

                            if ($i == $specialDayStamp) {
                                $rowSpecial = $resultSpecial->fetch();
                            }
                        } else {
                            if ($i == $specialDayStamp) {
                                $output .= "<td class=\"dayClosed\">";
                                $output .= $rowSpecial['name'];
                                $output .= '</td>';
                                ++$count;
                                $rowSpecial = $resultSpecial->fetch();
                            } else {
                                if ($days[date('D', $i)] == 'Y') {
                                    ++$countSchoolDays;

                                    $log = array();
                                    $logCount = 0;
                                    try {
                                        $dataLog = array('date' => date('Y-m-d', $i), 'gibbonPersonID' => $this->studentID);
                                        $sqlLog = 'SELECT gibbonAttendanceLogPerson.type, gibbonAttendanceLogPerson.reason FROM gibbonAttendanceLogPerson, gibbonAttendanceCode WHERE gibbonAttendanceLogPerson.type=gibbonAttendanceCode.name AND date=:date AND gibbonPersonID=:gibbonPersonID ORDER BY timestampTaken DESC';
                                        $resultLog = $this->connection2->prepare($sqlLog);
                                        $resultLog->execute($dataLog);
                                    } catch (PDOException $e) {
                                        echo "<div class=\"error\">".$e->getMessage().'</div>';
                                    }

                                    if ($resultLog->rowCount() < 1) {
                                        $class = 'dayNoData';
                                    } else {
                                        while ($rowLog = $resultLog->fetch()) {
                                            $log[$logCount][0] = $rowLog['type'];
                                            $log[$logCount][1] = $rowLog['reason'];

                                            if ($rowLog['type'] != 'Present') @$countTypes[ $rowLog['type'] ]++;
                                            if ($rowLog['reason'] != '') @$countReasons[ $rowLog['reason'] ]++;

                                            ++$logCount;
                                        }

                                        if ( $attendance->isTypeAbsent($log[0][0])) {
                                            ++$countAbsent;
                                            $class = 'dayAbsent';
                                            $textClass = 'highlightAbsent';
                                        } else {
                                            ++$countPresent;
                                            $class = 'dayPresent';
                                            $textClass = 'highlightPresent';
                                        }
                                        if ($log[0][1] != '') {
                                            $title = "title=\"".$log[0][1]."\"";
                                        } else {
                                            $title = '';
                                        }
                                    }
                                    $output .= "<td class=\"".$class."\">".date("d-m-Y", $i)."<br />";
                                    if (count($log) > 0) {
                                        $output .= "<span class=\"".$textClass."\" $title><b>".$log[0][0]."</b></span><br />";

                                        for ($x = count($log); $x >= 0; --$x) {
                                            if (isset($log[$x][0])) {
                                                $textClass = $attendance->isTypeAbsent($log[$x][0])? 'highlightAbsent' : 'highlightPresent';
                                                $output .= "<span class=\"".$textClass."\">";
                                                $output .= $attendance->getAttendanceCodeByType( $log[$x][0] )['nameShort'];
                                                $output .= "</span>";
                                            }
                                            if ($x != 0 and $x != count($log)) {
                                                $output .= ' : ';
                                            }
                                        }
                                    }
                                    $output .= "</td>";
                                    ++$count;
                                }
                            }
                        }
                    }

                    if (($count % ($days['count'] * $weeks)) == 0 and $days[date('D', $i)] == 'Y') {
                        $output .= "</tr>";
                    }
                }
            }

            if ($count % ($days['count'] * $weeks) > 0) {
                // Previous loop finished before creating enough <td></td>
                while ($count % ($days['count'] * $weeks) > 0) {
                    $output .= "<td class=\"dayClosed\">";
                    $output .= "<!-- PADDING -->";
                    $output .= "</td>";
                    ++$count;
                }
                $output .= "</tr>";
            }

            $output .= "</table>";
                
        }

        // Looping per term finishes here

        // $output now has the table, and the variables for summary data have been calculated
        $html = <<<EOD
<style>
.sectionName {
    font-size: 12px;
    color: #999999;
    font-weight: bold;
    font-family: pt_sans_b, PT Sans, helvetica, sans-serif;
}
.tableSummary {
    font-size: 8px;
    font-family: pt_sans, PT Sans, helvetica, sans-serif;
    cellpadding: 4px;
}
.tableSummary table {
    width: 100%;
}
.tableSummary th {
    border: 1px solid #cccccc;
    background-color: #dddddd;
    font-size: 12px;
    font-weight: bold;
    font-family: pt_sans_b, PT Sans, helvetica, sans-serif;
    height: 18px:
    vertical-align: top;
}
.tableSummary td {
    border: 1px solid #cccccc;
}
.tableSummaryBold td {
    font-family: pt_sans_b, PT Sans, helvetica, sans-serif;
}
.attendanceKey {
    height: 124px;
}
.attendanceHeader td {
    border: 1px solid #cccccc;
    background-color: #dddddd;
    font-size: 12px;
    font-weight: bold;
    font-family: pt_sans_b, PT Sans, helvetica, sans-serif;
    height: 18px:
    vertical-align: top;
}
.miniHistoryCalendar {
    border: 1px solid #cccccc;
    text-align: center;
}
.miniHistoryCalendar th {
    font-weight: bold;
    font-size: 8px;
    font-weight: bold;
    font-family: pt_sans_b, PT Sans, helvetica, sans-serif;
    background-color: #e2e2e2;
}
.miniHistoryCalendar td {
    font-size: 7px;
    font-family: pt_sans_b, PT Sans, helvetica, sans-serif;
    border: 1px solid #cccccc;
}
.dayClosed {
	color: #aaaaaa;
	background-color: #cccccc;
}
.highlightPresent
 {
	color: #390;
	background-color: #D4F6DC;
}
.dayPresent {
	background-color: #D4F6DC;
}
.highlightAbsent {
	color: #c00;
	background-color: #F6CECB;
}
.dayAbsent {
	background-color: #F6CECB;
}
.highlightNoData {
	color: #555;
	background-color: #eeeeee;
}
.dayNoData {
	background-color: #eeeeee;
}
</style>
<table cellpadding="4" class="sectionAttendance">
    <tr>
        <td class="sectionName">Attendance</td>
    </tr>
</table>
<table class="tableSummary">
    <tr>
        <th>Summary</th>
        <th>Key</th>
    </tr>
    <tr>
EOD;

        //$html.= '<p>';
        if (isset($countSchoolDays) and isset($countPresent) and isset($countAbsent)) {

            $html.= "<td><span class=\"tableSummaryBold\">Total number of school days recorded: ".$countSchoolDays."</span><br />- Present: ".$countPresent."<br />- Absent: $countAbsent<br />";

            if ( count($countTypes) > 0 ) {
                $html.= '<br /><b>Individual class absences/lates recorded:</b>';
                $html.= '<br />';
                //$html.= '<ul>';
                foreach ($countTypes as $typeName => $count ) {
                    //$html.= "<li>".$typeName.": ".$count."</li>";
                    $html.= "- ".$typeName.": ".$count."<br />";
                }
                //$html.= '</ul>';
            }

            if ( count($countReasons) > 0 ) {
                $html.= '<br /><b>Reasons for individual class absences:</b>';
                $html.= '<br />';
                //$html.= '<ul>';
                foreach ($countReasons as $reasonName => $count ) {
                    //$html.= "<li>".$reasonName.": ".$count."</li>";
                    $html.= "- ".$reasonName.": ".$count."<br />";
                }
                //$html.= '</ul>';
            }
        } else {
            $html.= 'Information not available';
        }
        $html.= '</td>';
        $html.= "<td class=\"attendanceKey\">";
        //$html.= "<img style='border: 1px solid #eee' alt='Data Key' src='".$_SESSION[$this->guid]['absoluteURL']."/modules/Attendance/img/dataKey.png'>";
        $html.= "<table><tr><td style=\"font-size: 2px\">&nbsp;</td></tr><tr><td><img height=\"120\" src=\"http://webhost.baliis.net/bisfiles/attendance-data-key.png\"></td></tr></table>";
        $html.= '</td></tr></table>';
        
        // Append the detailed table
        $html.= $output;

        //echo $html;
        return $html;
    }

    function attendanceTermReport($pdf) { 
        $this->rowHeight = 12;
        $this->commentHeight = 46;
        $html = $this->readStudentAttendanceHistory();

        //file_put_contents("/tmp/html_debug.html", $html);
        
        $cp =  $pdf->getPage(); // current page number
        $pdf->startTransaction();
        $pdf->writeHTML($html, true, false, true, false, '');
        if ($pdf->getPage() != $cp) {
            $pdf->rollBackTransaction(true);
            $pdf->addPage();
            $pdf->setY($this->topMargin);
            $pdf->writeHTML($html, true, false, true, false, '');
        } else {
            $pdf->commitTransaction();
        }

        return $html;
    }
    ////////////////////////////////////////////////////////////////////////////
    
    ////////////////////////////////////////////////////////////////////////////
    function readCriteriaList() {
        $data = array(
                "classID" => $this->classID,
                "gradesetID" => 1,
                "reportSubjectID" => $this->reportSubjectID
            );
        $sql = "SELECT arrCriteria.arrCriteriaID, arrCriteriaName, arrGradesetDetailID, arrGrade
                FROM gibbonCourseClass
                INNER JOIN arrCriteria
                ON gibbonCourseClass.gibbonCourseID = arrCriteria.arrCourseID

                LEFT JOIN
                (
                SELECT arrCriteriaID, arrGradesetDetail.arrGradesetDetailID, arrGrade
                FROM arrReportGrade
                LEFT JOIN arrGradesetDetail
                ON arrReportGrade.arrGradesetDetailID = arrGradesetDetail.arrGradesetDetailID
                WHERE arrReportSubjectID = :reportSubjectID
                AND arrGradesetID = :gradesetID
                ) AS grade

                ON grade.arrCriteriaID = arrCriteria.arrCriteriaID

                WHERE gibbonCourseClassID = :classID
                ORDER BY arrCriteriaOrder";
        //print $sql;
        //print_r($data);
        $rs = $this->connection2->prepare($sql);
        $rs->execute($data);
        return $rs;
    }

    function readReport() {
        $data = array(
            'classID' => $this->classID,
            'reportID' => $this->reportID,
            'studentID' => $this->studentID
        );
        $sql = "SELECT arrReportSubjectID, CONCAT(title, ' ', LEFT(preferredName, 1), '.', surname) AS teacherName, arrSubjectComment
            FROM arrReportSubject
            LEFT JOIN gibbonPerson
            ON arrReportSubject.arrTeacherID = gibbonPerson.gibbonPersonID
            WHERE arrStudentID = :studentID
            AND arrClassID = :classID
            AND arrReportID = :reportID";
        //print $sql;
        //print_r($data);
        $rs = $this->connection2->prepare($sql);
        $rs->execute($data);
        return $rs;
    }


    function readSubjectList() {
        $data = array(
            'studentID' => $this->studentID,
            'schoolYearID' => $this->schoolYearID
        );
        $sql = "SELECT DISTINCT gibbonCourse.gibbonCourseID, gibbonCourseClassPerson.gibbonCourseClassID,
            gibbonCourse.name, gibbonCourse.description, arrCourseType, arrRowHeight, arrCommentHeight
            FROM gibbonCourseClass
            INNER JOIN gibbonCourseClassPerson
            ON gibbonCourseClass.gibbonCourseClassID = gibbonCourseClassPerson.gibbonCourseClassID
            INNER JOIN gibbonCourse
            ON gibbonCourse.gibbonCourseID = gibbonCourseClass.gibbonCourseID
            INNER JOIN arrSubjectDetail
            ON arrSubjectDetail.arrCourseID = gibbonCourseClass.gibbonCourseID
            WHERE gibbonCourse.gibbonSchoolYearID = :schoolYearID
            AND gibbonCourseClassPerson.gibbonPersonID = :studentID
            AND gibbonCourseClass.reportable = 'Y'
            
            AND arrSubjectDetail.arrCourseType <> '-'
            ORDER BY arrSubjectPosition";
        //print $sql;
        //print_r($data);
        $rs = $this->connection2->prepare($sql);
        $rs->execute($data);
        return $rs;
    }
    
    function readReportSetting() {
        
        $rs = $this->connection2->prepare($sql);
        $rs->execute($data);
        return $rs;
    }
    
    ////////////////////////////////////////////////////////////////////////////////
    function readReportSectionList($connection2) {
        // read list of sections that make up the report
        $data = array(
            'reportID' => $this->reportID
        );
        $sql = "SELECT *
            FROM arrReportSection
            INNER JOIN arrReportSectionType
            ON arrReportSectionType.reportSectionTypeID = arrReportSection.sectionType
            WHERE reportID = :reportID
            ORDER BY sectionOrder";
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        return $rs;
    }
    ////////////////////////////////////////////////////////////////////////////////
}

function makeIndex($path) {
   // check there is an index file
    $index = 'index.html';
    $pathIndex = $path.'/'.$index;
    if (!file_exists($pathIndex)) {
        echo $pathIndex;
        echo "<br />";
        $handle = fopen($pathIndex, 'w') or die('Cannot open file:  '.$pathIndex); //implicitly creates file
        fclose($handle);
    }
}

////////////////////////////////////////////////////////////////////////////////
function findTerm($reportID) {
    // find which term the current report is for
    $data = array('reportID' => $reportID);
    $sql = "SELECT arrReportNum
        FROM arrReport
        WHERE arrReportID = :reportID";
    $rs = $this->connection2->prepare($sql);
    $rs->execute($data);
    $row = $rs->fetch();
    return $row['arrReportNum'];
}
// -------------------------------------------------------------------------

// -------------------------------------------------------------------------
function findYearGroupName($yearGroupID) {
    // find which term the current report is for
    $data = array('yearGroupID' => $yearGroupID);
    $sql = "SELECT nameShort
        FROM gibbonYearGroup
        WHERE gibbonYearGroupID = :yearGroupID";
    $rs = $this->connection2->prepare($sql);
    $rs->execute($data);
    $row = $rs->fetch();
    return $row['nameShort'];
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function readStudentClassListNoRepeat($connection2, $studentID, $schoolYearID) {
    // read classes/subjects attended by selected student
    $data = array(
        'studentID' => $studentID,
        'schoolYearID' => $schoolYearID
    );
    $sql = "SELECT gibbonCourseClassPerson.gibbonCourseClassID AS classID, 
        gibbonCourseClass.name AS subjectClassName, 
        gibbonCourseClass.nameShort AS subjectClassNameShort, 
        gibbonCourse.gibbonCourseID AS subjectID,
        gibbonCourse.description, 
        gibbonCourse.name AS subjectName,
        teacher.gibbonPersonID,
        CONCAT(gibbonPerson.preferredName,' ',gibbonPerson.surname) AS teacherName

        FROM gibbonCourseClassPerson 
        INNER JOIN gibbonCourseClass 
        ON gibbonCourseClass.gibbonCourseClassID = gibbonCourseClassPerson.gibbonCourseClassID INNER JOIN gibbonCourse 
        ON gibbonCourse.gibbonCourseID = gibbonCourseClass.gibbonCourseID 
        INNER JOIN gibbonCourseClassPerson AS teacher 
        ON teacher.gibbonCourseClassID = gibbonCourseClass.gibbonCourseClassID 

        LEFT JOIN gibbonPerson
        ON gibbonPerson.gibbonPersonID = teacher.gibbonPersonID
        WHERE gibbonCourseClassPerson.gibbonPersonID = :studentID
        AND gibbonCourse.gibbonSchoolYearID = :schoolYearID
        AND gibbonCourseClass.reportable = 'Y'
        AND teacher.role = 'Teacher'
        ORDER BY gibbonCourse.name";
    $rs = $connection2->prepare($sql);
    $rs->execute($data);
    
    // there maybe multiple teachers so reduce this to one row per class
    $classList = array();
    $rowdata = array();
    $lastClass = 0;
    $teacherName = '';
    while ($row = $rs->fetch()) {
        if ($row['classID'] != $lastClass) {
            if ($lastClass > 0) {
                $rowdata['teacherName'] = $teacherName;
                $classList[] = $rowdata;
            }
            $lastClass = $row['classID'];
            $rowdata = [];
            $rowdata['classID'] = $row['classID'];
            $rowdata['subjectClassName'] = $row['subjectClassName'];
            $rowdata['subjectDescription'] = $row['description'];
            $rowdata['subjectClassNameShort'] = $row['subjectClassNameShort'];
            $rowdata['subjectID'] = $row['subjectID'];
            $rowdata['subjectName'] = $row['subjectName'];
            $teacherName = '';
            $comma = "";
        }
        $teacherName .= $comma.$row['teacherName'];
        $comma = ", ";
    }
    $rowdata['teacherName'] = $teacherName;
    $classList[] = $rowdata;
    return $classList;
}
////////////////////////////////////////////////////////////////////////////////
?>
