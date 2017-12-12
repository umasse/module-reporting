<?php

/*
 * Project:
 * Author:   Andy Statham
 * Date:
 */
// in case we need more functions

class MYPDF extends TCPDF {
    //Page header
    public function Header() {
        GLOBAL $setpdf;
        
    }

    // Page footer
    public function Footer() {
        GLOBAL $setpdf;
        //$text = "A Community, Learning for Tomorrow";
        $text = $setpdf->officialName;
        $this->SetFont('helvetica', 'BI', 8);
        $this->Cell(0, 10, $text, 0, 0, 'C');
    }
}

class setpdf {

    var $class;
    var $msg;

    var $schoolYearID;

    var $logo = "images/hly_logo.png";
    var $imgOrchard = "images/orchard.png";
    var $imgIB = "images/ib.png";
    var $imgCIS = "images/cis.png";

    var $topMargin = 18;
    var $pageHeight = 278;

    var $leftMargin = 13;
    var $pageWidth = 180;
    var $critCol1 = 60;
    var $critCol2 = 20;

    var $small = 9;
    var $standard = 10;
    var $heading1 = 12;
    var $gray = 200;
    
    var $insertList = array(
        "Official name", "First name", "Preferred name", "Surname", "Class"
    );

    function setpdf($guid, $connection2) {
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
        $this->findRollGroup($connection2);
        
        $this->reportDetail = readReportDetail($connection2, $this->reportID);
        $reportRow = $this->reportDetail->fetch();
        $this->term = $reportRow['reportNum'];
        $this->gradeScale = $reportRow['gradeScale']; // id for grade scale to be used for assessment
        $this->gradeList = readGradeList($connection2, $this->gradeScale);
        
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
            'studentID' => $this->studentID
        );
        $sql = "SELECT surname, firstName, preferredName, officialName, dob
            FROM gibbonPerson
            WHERE gibbonPersonID = :studentID";
        $rs = $this->connection2->prepare($sql);
        $rs->execute($data);
        return $rs;
    }
    ////////////////////////////////////////////////////////////////////////////
    
    ////////////////////////////////////////////////////////////////////////////
    function checkFolder() {
        // check if archive folder exists

        $path = '../../archive';
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
            echo "<br>";
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
            /*
            switch ($this->yearGroupName) {
                case 'R':
                    $this->reportName = substr($row['arrReportName'], 10);
                    break;

                case 'N':
                    $this->reportName = substr($row['arrReportName'], 8);
                    break;

                default:
                    $this->reportName = substr($row['arrReportName'], 7);
                    break;
            }
             *
             */
            $this->reportDate = $row['arrReportDate'];
            $this->dateStart = $row['dateStart'];
            $this->dateEnd = $row['dateEnd'];
        }
    }
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    function findTerm() {
        // find which term the current report is for
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
        $sql = "SELECT name, CONCAT(firstName, ' ', surname) AS teacherName
            FROM gibbonRollGroup
            INNER JOIN gibbonPerson
            ON gibbonRollGroup.gibbonPersonIDTutor = gibbonPerson.gibbonPersonID
            WHERE gibbonRollGroupID = :rollGroupID";
        //print $sql;
        //print_r($data);
        $rs = $this->connection2->prepare($sql);
        $rs->execute($data);
        $rollGroupName = '';
        $classTeacher = '';
        if ($rs->rowCount() > 0) {
            $row = $rs->fetch();
            $rollGroupName = $row['name'];
            $classTeacher = $row['teacherName'];
        }
        $this->rollGroupName = $rollGroupName;
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
            $pdf->writeHTML($html);
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    // subject section
    ////////////////////////////////////////////////////////////////////////////
    function subjectReport($pdf) {
        $sublist = readStudentClassListNoRepeat($this->connection2, $this->studentID, $this->schoolYearID);
        $this->rowHeight = 12;
        $this->commentHeight = 46;
        $html = '';
        $html .= '<style>';
            $html .= 'body{font-size:12px; font-family: sans-serif;}';
            $html .= '.subjectname {color:#999999; font-weight:bold;}';
            $html .= '.teachername {font-style:italic;}';
            $html .= '.smalltext {font-size:11px;}';
            $html .= '.gradeTable table{width:180mm;}';
            $html .= '.gradeTable th {border: 1px solid #cccccc; background-color: #dddddd; padding:1px;}';
            $html .= '.gradeTable td {border: 1px solid #cccccc; padding:1px;}';
            $html .= '.col1 {text-align:left; width:110mm;}';
            $html .= '.col2 {text-align:center; width:70mm;}';
            $html .= '.commentHead {width:180mm;}';
            /*
            $html .= 'td {border: 1px solid black; padding:1px;}';
            $html .= 'td.col1 {width:50mm; height:'.$this->rowHeight.'mm; font-weight:bold;}';
            $html .= 'td.col2 {width:18mm; height:'.$this->rowHeight.'mm; text-align:center; font-weight:normal;}';
            $html .= 'td.col3 {width:120mm; font-weight:normal;}';
            $html .= 'td.col4 {height:'.$this->commentHeight.'mm; font-weight:normal;}';
            $html .= 'td.topheading {text-align:left; width:188mm; border: none; font-weight:bold;}';
            $html .= 'td.topheading2 {text-align:left; width:188mm; background-color: #cccccc; border: 1px solid black; font-weight:bold;}';
            $html .= 'td.heading {background-color: #cccccc; border: 1px solid black; font-weight:bold;}';
            $html .= 'td.key {font-size:small; font-style:italic; border:none;}';
             * 
             */
        $html .= '</style>';
        
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
                $html .= '<table>';
                    $html .= '<tr><td class="subjectname">'.$subjectName.'</td></tr>';
                    $html .= '<tr><td class="teachername">'.$teacherName.'</td></tr>';
                $html .= '</table>';

                if ($criteriaList->rowCount() > 0) {
                    $html .= '<table class="gradeTable">';
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
                    $html .= '<table class="gradeTable">';
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
        }
        
        //echo $html;
        $pdf->writeHTML($html, true, false, true, false, '');
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
            $html .= 'body{font-size:12px; font-family: sans-serif;}';
            $html .= '.subjectname {color:#999999; font-weight:bold;}';
            $html .= '.teachername {font-style:italic;}';
            $html .= '.smalltext {font-size:11px;}';
            $html .= '.gradeTable table{width:180mm;}';
            $html .= '.gradeTable th {border: 1px solid #cccccc; background-color: #dddddd; padding:1px;}';
            $html .= '.gradeTable td {border: 1px solid #cccccc; padding:1px;}';
            $html .= '.col1 {text-align:left; width:110mm;}';
            $html .= '.col2 {text-align:center; width:70mm;}';
            $html .= '.commentHead {width:180mm;}';
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
            $html .= '<table>';
                $html .= '<tr><td class="subjectname">'.$subjectName.'</td></tr>';
                $html .= '<tr><td class="teachername">'.$teacherName.'</td></tr>';
            $html .= '</table>';

            if ($criteriaList->rowCount() > 0) {
                $html .= '<table class="gradeTable">';
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
                $html .= '<table class="gradeTable">';
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
        //echo $html;
        $pdf->writeHTML($html, true, false, true, false, '');
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

    /*
    function readRollGroupList() {
        // return list of students in the selected roll group
        $data = array(
            'rollGroupID' => $this->rollGroupID,
            'reportID' => $this->reportID
        );
        $sql = "SELECT gibbonStudentEnrolment.gibbonPersonID, surname, firstName, officialName, dob, name
            FROM gibbonStudentEnrolment
            INNER JOIN gibbonPerson
            ON gibbonStudentEnrolment.gibbonPersonID = gibbonPerson.gibbonPersonID
            INNER JOIN gibbonRollGroup
            ON gibbonStudentEnrolment.gibbonRollGroupID = gibbonRollGroup.gibbonRollGroupID

            LEFT JOIN
            (SELECT *
            FROM arrArchive
            WHERE arrArchive.arrReportID = :reportID) AS archive
            ON gibbonStudentEnrolment.gibbonPersonID = archive.arrStudentID

            WHERE gibbonStudentEnrolment.gibbonRollGroupID = :rollGroupID ";

        if ($this->showLeft == 0) {
            $sql .= "AND status = 'Full' ";
        }

        $sql .= "ORDER BY surname, firstName";
        //print $sql;
        //print_r($data);
        $rs = $this->connection2->prepare($sql);
        $rs->execute($data);
        return $rs;
    }
     * 
     */

    function readSubjectList() {
        $data = array(
            'studentID' => $this->studentID,
            'schoolYearID' => $this->schoolYearID
        );
        $sql = "SELECT DISTINCT gibbonCourse.gibbonCourseID, gibbonCourseClassPerson.gibbonCourseClassID,
            gibbonCourse.name, arrCourseType, arrRowHeight, arrCommentHeight
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
        echo "<br>";
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
