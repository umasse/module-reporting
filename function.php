<?php
//$effortLabel = "Approach to learning";
$_SESSION['max_term'] = 3;
$_SESSION['numCols'] = 60;
$_SESSION['archivePath'] = '/archive/reporting/';

ini_set('error_log', 'logfile.txt');

////////////////////////////////////////////////////////////////////////////////
function chooseReport($connection2, $classID, $reportID, $rollGroupID, $schoolYearID, $teacherID, $yearGroupID) {
    $repList = readReportList($connection2, $schoolYearID, $yearGroupID);
    $repList->execute();
    
    ob_start();
    ?>
    <div style = "padding:2px;">
        <?php
        if ($repList->rowCount() > 0) {
            ?>
            <div style = "float:left;width:30%;" class = "smalltext">Report</div>
            <div style = "float:left;">
                <form name="frm_selectreport" method="post" action="">
                    <input type="hidden" name="schoolYearID" value="<?php echo $schoolYearID ?>" />
                    <input type="hidden" name="yearGroupID" value="<?php echo $yearGroupID ?>" />
                    <input type="hidden" name="rollGroupID" value="<?php echo $rollGroupID ?>" />
                    <input type="hidden" name="teacherID" value="<?php echo $teacherID ?>" />
                    <input type="hidden" name="classID" value="<?php echo $classID ?>" />
                    <select name="reportID" onchange="this.form.submit()">
                        <option></option>
                        <?php
                        while ($row = $repList->fetch()) {
                            ?>
                            <option value="<?php echo $row['reportID'] ?>"
                                   <?php if ($reportID == $row['reportID'])
                                       echo "selected='selected'" ?>>
                                <?php echo $row['reportName'] ?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                </form>
            </div>
            <?php
        } else {
            echo "<div class='smalltext'>No reports assigned to this year group</div>";
        }
        ?>
    </div>
    <?php
    return ob_get_clean();
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function chooseRollGroup($connection2, $rollGroupID, $schoolYearID, $yearGroupID) {
    // drop down box to select roll group
    $data = array(
            'schoolYearID' => $schoolYearID,
            'yearGroupID' => $yearGroupID
    );
    $sql = "SELECT DISTINCT gibbonStudentEnrolment.gibbonRollGroupID, gibbonRollGroup.nameShort
        FROM gibbonRollGroup
        INNER JOIN gibbonStudentEnrolment
        ON gibbonRollGroup.gibbonRollGroupID = gibbonStudentEnrolment.gibbonRollGroupID
        WHERE gibbonYearGroupID = :yearGroupID
        AND gibbonStudentEnrolment.gibbonSchoolYearID = :schoolYearID
        ORDER BY nameShort";
    //print $sql;
    //print_r($data);
    $rs = $connection2->prepare($sql);
    $rs->execute($data);

    ob_start();
    ?>
    <div style = "padding:2px;">
        <div style = "float:left;width:30%;" class = "smalltext">Home Room</div>
        <div style = "float:left;">
            <form name="frm_class" method="post" action="">
                <input type="hidden" name="yearGroupID" value="<?php echo $yearGroupID ?>" />
                <input type="hidden" name="schoolYearID" value="<?php echo $schoolYearID ?>" />
                <input type="hidden" name="studentID" value="<?php echo $studentID ?>" />
                <select name="rollGroupID" onchange="this.form.submit();">
                    <option></option>
                    <?php
                    while ($row = $rs->fetch()) {
                        ?>
                        <option value="<?php echo $row['gibbonRollGroupID'] ?>"
                                <?php if ($rollGroupID == $row['gibbonRollGroupID'])
                                    echo "selected='selected'" ?>>
                            <?php echo $row['nameShort'] ?>
                        </option>
                        <?php
                    }
                    ?>
                </select>
            </form>
        </div>
        <div style="clear:both"></div>
    </div>
    <?php
    return ob_get_clean();
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function chooseSchoolYear($connection2, $studentID, $reportID, $schoolYearID) {
    // drop down box for selecting year
    $schoolYearList = readSchoolYearList($connection2);
    ob_start();
    ?>
    <div style = "padding:2px;">
        <div style = "float:left;width:30%;" class = "smalltext">Year</div>
        <div style = "float:left;">
            <form name = "frm_schoolyear" method = "post" action = "">
                <input type = "hidden" name = "classID" value = "" />
                <input type = "hidden" name = "reportID" value = "" />
                <input type = "hidden" name = "studentID" value = "" />
                <input type = "hidden" name = "yearGroupID" value = "" />
                <select name = "schoolYearID" onchange = "this.form.submit();" style = 'width:95%;'>
                    <option></option>
                    <?php
                    $schoolYearList->execute();
                    while ($row_schoolYearList = $schoolYearList->fetch()) { ?>
                        <option value = "<?php echo $row_schoolYearList['gibbonSchoolYearID'] ?>"
                            <?php if ($schoolYearID == $row_schoolYearList['gibbonSchoolYearID']) echo "selected='selected'"; ?>>
                            <?php echo $row_schoolYearList['name'] ?>
                         </option>
                    <?php } ?>
                </select>
            </form>
        </div>
        <div style = "clear:both;"></div>
    </div>
    <?php
    return ob_get_clean();
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function chooseYearGroup($connection2, $yearGroupID, $schoolYearID) {
    $yearGroupList = readYeargroup($connection2);
    ob_start();
    ?>
    <div style = "padding:2px;">
        <div style = "float:left;width:30%;" class = "smalltext">Year Group</div>
        <div style = "float:left;">
            <form name='frm_yeargroup' method='post' action=''>
                <input type='hidden' name='courseID' value='' />
                <input type='hidden' name='classID' value='' />
                <input type='hidden' name='rollGroupID' value='' />
                <input type='hidden' name='studentID' value='' />
                <input type='hidden' name='reportID' value='' />
                <input type='hidden' name='schoolYearID' value='<?php echo $schoolYearID ?>' />
                <select name='yearGroupID' onchange="this.form.submit();">
                    <option></option>
                    <?php
                    while ($row = $yearGroupList->fetch()) {
                        ?>
                        <option value="<?php echo $row['gibbonYearGroupID'] ?>"
                                <?php if ($yearGroupID == $row['gibbonYearGroupID'])
                                        echo "selected='selected'" ?>>
                            <?php echo $row['nameShort'] ?>
                        </option>
                        <?php
                    }
                    ?>
                </select>
            </form>
        </div>
        <div style="clear:both"></div>
    </div>
    <?php
    return ob_get_clean();
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function getClassID() {
    $classID = '';
    if (isset($_POST['classID'])) {
        $classID = $_POST['classID'];
    } else {
        if (isset($_GET['classID'])) {
            $classID = $_GET['classID'];
        }
    }
    return $classID;
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function getLeft() {
    $showLeft = 0;
    if (isset($_POST['showLeft'])) {
        $showLeft = $_POST['showLeft'];
    } else {
        if (isset($_GET['showLeft'])) {
            $showLeft = $_GET['showLeft'];
        }
    }
    return $showLeft;
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function getReportID() {
    // check if parameter has been passed to current page
    $reportID = '';
    if (isset($_POST['reportID'])) {
        $reportID = $_POST['reportID'];
    } else {
        if (isset($_GET['reportID'])) {
            $reportID = $_GET['reportID'];
        }
    }
    return $reportID;
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function getRollGroupID() {
    $rollGroupID = '';
    if (isset($_POST['rollGroupID'])) {
        $rollGroupID = $_POST['rollGroupID'];
    } else {
        if (isset($_GET['rollGroupID'])) {
            $rollGroupID = $_GET['rollGroupID'];
        }
    }
    return $rollGroupID;
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function getSchoolYearCurrent($connection2) {
    try {
	// return details of current year
	$sql = "SELECT gibbonSchoolYearID
            FROM gibbonSchoolYear
            WHERE status = 'current'";
        $rs = $connection2->prepare($sql);
        $rs->execute();
        $schoolYearID = 0;
	if ($rs->rowCount() > 0) {
            $row = $rs->fetch();
            $schoolYearID = $row['gibbonSchoolYearID'];
	}
	return $schoolYearID;
    } catch(PDOException $e) {
        print "<div>" . $e->getMessage() . "</div>" ;
    }
}
////////////////////////////////////////////////////////////////////////////////
 
////////////////////////////////////////////////////////////////////////////////
function getSchoolYearID($connection2, &$schoolYearName, &$currentYearID) {
    // find selected year
    $currentYearID = getSchoolYearCurrent($connection2);
    if (isset($_POST['schoolYearID'])) {
        $schoolYearID = $_POST['schoolYearID'];
    } else {
        if (isset($_GET['schoolYearID'])) {
            $schoolYearID = $_GET['schoolYearID'];
        } else {
            $schoolYearID = $currentYearID;
        }
    }
    try {
	// get the name
	$data = array(":schoolYearID"=>$schoolYearID);
        $sql = "SELECT name
            FROM gibbonSchoolYear
            WHERE gibbonSchoolYearID = :schoolYearID";
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
	$row_select = $rs->fetch();

    } catch(PDOException $e) {
        print "<div>" . $e->getMessage() . "</div>" ;
    }
    $schoolYearName = $row_select['name'];
    return $schoolYearID;
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function getStudentID() {
    // see if a student has been selected
    $studentID = '';
    if (isset($_POST['studentID'])) {
        $studentID = $_POST['studentID'];
    } else {
        if (isset($_GET['studentID'])) {
            $studentID = $_GET['studentID'];
        }
    }
    return $studentID;
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function getTeacherID($guid) {
    // see if teacher has been selected who is different from that logged in
    if (isset($_REQUEST['teacherID']) && $_REQUEST['teacherID'] != '') {
        $teacherID = $_REQUEST['teacherID'];
        if ($teacherID != $_SESSION[$guid]['teacherID'])
            $_SESSION[$guid]['classID'] = '';
    } else {
        if (isset($_SESSION[$guid]['teacherID'])) {
            $teacherID = $_SESSION[$guid]['teacherID'];
        } else {
            $teacherID = $_SESSION[$guid]['gibbonPersonID'];
        }
    }
    $_SESSION[$guid]['teacherID'] = $teacherID;
    return $teacherID;
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function getYearGroupID() {
    $yearGroupID = '';
    if (isset($_POST['yearGroupID'])) {
        $yearGroupID = $_POST['yearGroupID'];
    } else {
        if (isset($_GET['yearGroupID'])) {
            $yearGroupID = $_GET['yearGroupID'];
        }
    }
    return $yearGroupID;
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function getView() {
    $view = '';
    if (isset($_POST['view'])) {
        $view = $_POST['view'];
    } else {
        if (isset($_GET['view'])) {
            $view = $_GET['view'];
        }
    }
    return $view;
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function oddEven($num) {
    if (($num-1)%2==0) {
        $rowNum="arreven" ;
    } else {
        $rowNum="arrodd" ;
    }
    return $rowNum;
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function pageTitle($title) {
    // display title
    echo "<div class='trail'>";
	echo "<div class='trailEnd'>$title</div>";
    echo "</div>";
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function archiveNavbar($guid, $page) {
    $path = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]["module"];
    $pageList = array('Current', 'archive.php', 'Search', 'archive_search.php');
    ?>
    <div class='smalltext'>
        <?php
        for ($p=0; $p<count($pageList)/2; $p++) {
            if ($page == strtolower($pageList[$p*2])) {
                echo "<span>".$pageList[$p*2]."</span>";
            } else {
                $link = $path.'/'.$pageList[$p*2+1];
                echo "<a href='$link'>".$pageList[$p*2]."</a>";
            }
            if ($p < (count($pageList)/2)-1) {
                echo "<span style='padding:2px;'>&nbsp;&nbsp;|&nbsp;&nbsp;</span>";
            }
        }
        ?>
    </div>
    <?php
}////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function navbar($guid, $connection2, $page, $studentID, &$reportID, $classID, $rollGroupID, $schoolYearID, $yearGroupID) {
    // subject navigation
    // shows title aand list of reports from which to select
    $path = $_SESSION[$guid]['absoluteURL'];
    $pathroot = $path."/index.php?q=/modules/".$_SESSION[$guid]['module']."/".strtolower($page).".php&amp;studentID=".$studentID;

    // see how many reports are available
    $repList = readReportList($connection2, $schoolYearID, $yearGroupID);

    // display list of report numbers and links
    echo "<div style = 'font-size:smaller;' class = 'smalltext'>";

    // counter used for deciding whether to place spacer between items.
    // do it for all but last item
    $c = 0;

    // if no reports match the current one it must be for a differeny
    // year/yeargroup so may need to reset it
    $match = 0;

    while ($row = $repList->fetch()) {
        $c++;
        if ($reportID == $row['reportID']) {
            // found a match so flag that there is no need to reset reportID
            $match = 1;
            echo "<span>".$row['reportName']."</span>";
        } else {
            if ($rollGroupID > 0) {
                $link = $pathroot."&amp;reportID=".$row['reportID'].
                    "&amp;schoolYearID=".$schoolYearID.
                    "&amp;yearGroupID=".$yearGroupID.
                    "&amp;rollGroupID=".$rollGroupID.
                    "&amp;classID=".$classID;
            } else {
                $link = $pathroot."&amp;reportID=".$row['reportID'].
                    "&amp;schoolYearID=".$schoolYearID.
                    "&amp;classID=".$classID;
            }
            echo "<span style='padding:2px'>";
            echo "<a href='$link'>";
                echo $row['reportName'];
            echo "</a>";
            echo "</span>";
        }
        if ($c < $repList->rowCount()) {
            echo "<span style='padding:2px;'>&nbsp;&nbsp;|&nbsp;&nbsp;</span>";
        }
    }
    if ($match == 0) {
        $reportID = 0;
    }
    echo "</div>";
    echo "<div class = 'header' style = 'clear:both;width:100%;'>&nbsp;</div>";
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function findMaxChar($connection2, $classID, &$courseType, &$maxChar) {
    $data = array('classID' => $classID);
    $sql = "SELECT *, arrCourseType, arrMaxChar
        FROM arrSubjectDetail
        INNER JOIN gibbonCourse
        ON gibbonCourse.gibbonCourseID = arrSubjectDetail.arrCourseID
        INNER JOIN gibbonCourseClass
        ON gibbonCourseClass.gibbonCourseID = gibbonCourse.gibbonCourseID
        WHERE gibbonCourseClassID = :classID";
    //print $sql;
    //print_r($data);
    $rs = $connection2->prepare($sql);
    $rs->execute($data);
    $row = $rs->fetch();
    $courseType = $row['arrCourseType'];
    $maxChar = $row['arrMaxChar'];
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function findReportstatus($connection2, $reportID, $roleID) {
    $data = array(
        "reportID" => $reportID,
        "roleID" => $roleID
    );
    $sql = "SELECT reportStatus
        FROM arrStatus
        WHERE reportID = :reportID
        AND roleID = :roleID";
    //print $sql;
    //print_r($data);
    $rs = $connection2->prepare($sql);
    $rs->execute($data);
    $reportStatus = false;
    if ($rs->rowCount() > 0) {
        $row = $rs->fetch();
        $reportStatus = $row['reportStatus'];
    }
    return $reportStatus;
}
////////////////////////////////////////////////////////////////////////////////
/*
////////////////////////////////////////////////////////////////////////////////
function find_student_name($connection2, $studentID) {
    // return name of a an individual student
    $data = array('studentID' => $studentID);
    $sql = "SELECT CONCAT(firstName, ' ', surname) AS studentName
        FROM gibbonPerson
        WHERE gibbonPerson.gibbonPersonID = :studentID";
    $rs = $connection2->prepare($sql);
    $rs->execute($data);
    $row = $rs->fetch();
    return $row['studentName'];
}
 * 
 */
/*
function findReportNum($connection2, $reportID) {
    $data = array(
        'reportID' => $reportID
    );
    $sql = "SELECT arrReportNum
        FROM arrReport
        WHERE arrReportID = :reportID";
    $rs = $connection2->prepare($sql);
    $rs->execute($data);
    $row = $rs->fetch();
    return $row['arrReportNum'];
}
 * 
 */
////////////////////////////////////////////////////////////////////////////////
function findGrade($gradeList, $gradeID) {
    // return grade value given its ID
    $grade = "-";
    $gradeList->execute();
    while ($row = $gradeList->fetch()) {
        if ($row['gibbonScaleGradeID'] == $gradeID) {
            $grade = $row['value'];
        }
    }
    return $grade;
}
////////////////////////////////////////////////////////////////////////////////
/*
////////////////////////////////////////////////////////////////////////////////
function read_access($connection2, $role, $teacherID) {
    // check if the current user has the role requested
    $data = array(
        'teacherID' => $teacherID,
        'roleName' => '%'.strtolower($role).'%'
    );
    $sql = "SELECT arrRole.arrRoleID
        FROM arrRole
        INNER JOIN arrRoleStaff
        ON arrRole.arrRoleID = arrRoleStaff.arrRoleID
        WHERE LOWER(arrRoleName) LIKE :roleName
        AND arrTeacherID = :teacherID";
    //print $sql;
    //print_r($data);
    $rs = $connection2->prepare($sql);
    $rs->execute($data);
    $ok = false;
    if ($rs->rowCount() > 0) {
        $ok = true;
    }
    return $ok;
}
 * 
 */


/*
// admin_criteria_function
function read_criterialist($connection2, $courseID) {
    // read list of criteria associated with a course/subject
    $data = array("courseID" => $courseID);
    $sql = "SELECT *
        FROM arrCriteria
        WHERE arrCourseID = :arrCourseID
        ORDER BY arrCriteriaOrder";
    $rs = $connection2->prepare($sql);
    $rs->execute($data);
    return $rs;
}
*/
////////////////////////////////////////////////////////////////////////////////
function getStudentName($connection2, $studentID) {
    // return name
    try {
        $data = array(":studentID"=>$studentID);
        $sql = "SELECT CONCAT(preferredName, ' ', surname) AS student_name
            FROM gibbonPerson
            WHERE gibbonPersonID = :studentID";
        //$connection2->query("SET NAMES 'utf8'");
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        $row = $rs->fetch();
        return $row['student_name'];
    } catch(PDOException $e) {
        print "<div>" . $e->getMessage() . "</div>" ;
    }
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function getTeacherName($connection2, $classID) {
    // return name
    try {
        $data = array("classID"=>$classID);
        $sql = "SELECT CONCAT(preferredName, ' ', surname) AS teacherName
            FROM gibbonPerson
            INNER JOIN gibbonCourseClassPerson
            ON gibbonCourseClassPerson.gibbonPersonID = gibbonPerson.gibbonPersonID
            WHERE gibbonCourseClassPerson.gibbonCourseClassID = :classID
            AND gibbonCourseClassPerson.role = 'Teacher'";
        //$connection2->query("SET NAMES 'utf8'");
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        $row = $rs->fetch();
        return $row['teacherName'];
    } catch(PDOException $e) {
        print "<div>" . $e->getMessage() . "</div>" ;
    }
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function readCriteriaGrade($connection2, $studentID, $subjectID, $reportID) {
    // read criteria for this subject
    // together with any associated grades that have been stored for this student
    try {
        $data = array(
            "studentID" => $studentID,
            "subjectID" => $subjectID,
            "reportID" => $reportID
        );
        $sql = "SELECT arrCriteria.criteriaID, arrCriteria.criteriaName, 
            (
                SELECT arrReportGrade.gradeID
                FROM arrReportGrade
                WHERE arrReportGrade.criteriaID = arrCriteria.criteriaID
                AND reportID = :reportID
                AND studentID = :studentID
            ) AS gradeID
            FROM arrCriteria
            WHERE subjectID = :subjectID
            ORDER BY criteriaOrder";
        //print $sql;
        //print_r($data);
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        return $rs;
    } catch(PDOException $e) {
        print "<div>" . $e->getMessage() . "</div>" ;
    }
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function readReportDetail($connection2, $reportID) {
    $data = array(
        'reportID' => $reportID
    );
    $sql = "SELECT *
        FROM arrReport
        WHERE reportID = :reportID";
    $rs = $connection2->prepare($sql);
    $rs->execute($data);
    return $rs;
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function readReportList($connection2, $schoolYearID, $yearGroupID) {
    // read reports available for this year group
    try {
        $data = array(
            'schoolYearID' => $schoolYearID,
            'yearGroupID' => $yearGroupID
        );
        $sql = "SELECT arrReport.reportID, reportName
            FROM arrReport
            INNER JOIN arrReportAssign
            ON arrReport.reportID = arrReportAssign.reportID
            WHERE arrReportAssign.schoolYearID = :schoolYearID
            AND yearGroupID = :yearGroupID
            AND assignStatus = 1
            ORDER BY reportNum";
        //print $sql;
        //print_r($data);
        $rs  = $connection2->prepare($sql);
        $rs->execute($data);
        return $rs;
    } catch(PDOException $e) {
        print "<div>" . $e->getMessage() . "</div>" ;
    }
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function readRollGroupList($connection2, $rollGroupID, $showLeft) {
    // return list of students in the selected roll group
    $data = array(
        'rollGroupID' => $rollGroupID
    );
    $sql = "SELECT *
        FROM gibbonStudentEnrolment
        INNER JOIN gibbonPerson
        ON gibbonStudentEnrolment.gibbonPersonID = gibbonPerson.gibbonPersonID
        WHERE gibbonRollGroupID = :rollGroupID 
        AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "')";
    if ($showLeft == 0) {
        $sql .= "AND status = 'Full' ";
    }
    $sql .= "ORDER BY surname, firstName";
    $rs = $connection2->prepare($sql);
    $rs->execute($data);
    return $rs;
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function readSchoolYearList($connection2) {
    try {
	// read list of academic years
	$sql = "SELECT gibbonSchoolYearID, name, status
            FROM gibbonSchoolYear
            ORDER BY sequenceNumber";
        $rs = $connection2->prepare($sql);
        $rs->execute();
        return $rs;
    } catch(PDOException $e) {
        print "<div>" . $e->getMessage() . "</div>" ;
    }
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function readGradeScaleList($connection2) {
    try {
        $sql = "SELECT gibbonScaleID, name, nameShort, gibbonScale.usage
            FROM gibbonScale
            ORDER BY name";
        $rs = $connection2->prepare($sql);
        $rs->execute();
        return $rs;
    } catch(PDOException $e) {
        print "<div>" . $e->getMessage() . "</div>" ;
    }    
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function readGradeList($connection2, $gradeScale) {
    try {
        $data = array(
            'gradeScaleID' => $gradeScale
        );
        $sql = "SELECT gibbonScaleGradeID, 
            gibbonScaleGrade.value, descriptor, sequenceNumber
            FROM gibbonScaleGrade
            WHERE gibbonScaleID = :gradeScaleID
            ORDER BY sequenceNumber";
        //print $sql;
        //print_r($data);
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        return $rs;
    } catch(PDOException $e) {
        print "<div>" . $e->getMessage() . "</div>" ;
    }   
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function readStudentClassList($connection2, $studentID, $schoolYearID) {
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
    return $rs;
}
////////////////////////////////////////////////////////////////////////////////
/*   
////////////////////////////////////////////////////////////////////////////////
function read_pasReport($connection2, $studentID, $reportID) {
    // get report for selected student
    try {
        $data = array(
            ":studentID"=>$studentID,
            ":reportID"=>$reportID
        );
        $sql = "SELECT pastoralID, pastoralComment
            FROM arrReportPastoral
            WHERE studentID = :studentID
            AND reportID = :reportID";
        //print $sql;
        //print_r($data);
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        return $rs;
    } catch(PDOException $e) {
        print "<div>" . $e->getMessage() . "</div>" ;
    }
}
////////////////////////////////////////////////////////////////////////////////
 * 
 */

////////////////////////////////////////////////////////////////////////////////
function readSubReport($connection2, $studentID, $subjectID, $reportID) {
    // get report for selected student
    try {
        $data = array(
            ":studentID"=>$studentID,
            ":subjectID"=>$subjectID,
            ":reportID"=>$reportID
        );
        $sql = "SELECT *
            FROM arrReportSubject
            WHERE studentID = :studentID
            AND subjectID = :subjectID
            AND reportID = :reportID";
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        return $rs;
    } catch(PDOException $e) {
        print "<div>" . $e->getMessage() . "</div>" ;
    }
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function readCriteriaList($connection2, $subjectID) {
    // read any criteria assocated with this class/subject
    // not UOI
    $data = array(
        "subjectID" => $subjectID
    );
    $sql = "SELECT criteriaID, criteriaName
        FROM arrCriteria
        WHERE subjectID = :subjectID
        ORDER BY criteriaOrder";
    //print $sql;
    //print_r($data);
    $rs = $connection2->prepare($sql);
    $rs->execute($data);
    return $rs;
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function readYeargroup($connection2) {
    $sql = "SELECT *
        FROM gibbonYearGroup
        ORDER BY sequenceNumber";
    $rs = $connection2->prepare($sql);
    $rs->execute();
    return $rs;
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function setSessionVariables($guid, $connection2) {
    $_SESSION[$guid]['schoolYearID'] = getSchoolYearCurrent($connection2);

    $_SESSION[$guid]['classView']    = 1;
    $_SESSION[$guid]['studView']     = 0;
    $_SESSION[$guid]['maxGrade']     = 7;

    $_SESSION[$guid]['minYear'] = 1;
    $_SESSION[$guid]['maxYear'] = 13;

    $_SESSION[$guid]['repEdit'] = 2;
    $_SESSION[$guid]['repView'] = 1;

    $uploadpath = "../../uploads/documents/reports";
    @mkdir($uploadpath);
    $_SESSION[$guid]['uploadpath'] = $uploadpath;
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function setStatus($ok, $action, &$msg, &$class) {
    // set values for displaying message after save
    if ($ok) {
        $msg = $action." successful";
        $class = "success";

    } else {
        $msg = $action." failed";
        $class = "warning";
    }
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function showPhoto($guid, $connection2, $studentID) {
    // display student photo
    $size = 75;
    try {
        $data = array(":student_id"=>$studentID);
        $sql = "SELECT image_240, image_240
            FROM gibbonPerson
            WHERE gibbonPersonID=:student_id";
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        if ($rs->rowCount() > 0) {
            $row_select = $rs->fetch();
            if ($size == 75)
                $image = $row_select['image_240'];
            else
                $image = $row_select['image_240'];
        } else {
            $image = '';
        }
    } catch(PDOException $e) {
        print "<div>" . $e->getMessage() . "</div>" ;
    }
    echo "<div class = 'photobox'>";
        //$image = get_photoPath($connection2, $studentID, 75);
        echo getUserPhoto($guid, $image, 75);
    echo "</div>";
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function showRepLength($comment, $maxChar, $charBarID, $numCharID) {
    // show length of comment
    echo "<div id='$charBarID' class='smalltext replenbar'>";
    echo "<span id='$numCharID'>";
    echo strlen($comment);
    echo "</span> characters $maxChar maximum";
    echo "</div>";
}
////////////////////////////////////////////////////////////////////////////////
/*
function show_repLine($comment, $numLine, $maxLine, $lineBarID, $numLineID, $width) {
    // show length of comment
    $ok = true;
    $bgcolor = '#dddddd';
    if ($numLine > $maxLine) {
        $ok = false;
        $bgcolor = '#ff0000';
    }
    $width .= 'px';
    echo "<div id='$lineBarID' class='replenbar' style='font-size:10px;background-color:$bgcolor; width:$width;'>";
    echo "<span id='$numLineID'>$numLine</span> lines $maxLine maximum</span>";
    echo "</div>";
    return $ok;
}
 * 
 */
/*
function numLine($pdf, $comment, $fontSize, $width) {
    // show length of comment
    $length = 0;
    if (strlen($comment) > 0) {
        $pdf->setFont('Helvetica', '', $fontSize);
        $length = $pdf->getNumlines($comment, $width);
    }
    return $length;
}
 *
 */
/*
function showStatus($status, $title) {
    // show status of report in statusbar at top of reports
    switch ($status) {
        case 0:
            $col = "#999";
            $class = "standard";
            $msg = $title.' reports';
            break;

        case 1:
            $col = "#F00";
            $class = "warning";
            $msg = "FAILED - some items did not save";
            break;

        case 2:
            $col = "#0F0";
            $class = "success";
            $msg = "SUCCESS - your record(s) have been saved";
            break;
    }

    echo "<div class = '$class' id = 'status'>$msg</div>";
}
////////////////////////////////////////////////////////////////////////////////
*/
////////////////////////////////////////////////////////////////////////////////
function thisPage($guid, $page) {
    // path to current page
    $path = $_SESSION[$guid]['absoluteURL'];
    $thisPage = $path."/index.php?q=/modules/".$_SESSION[$guid]['module']."/".$page;
    return $thisPage;
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function trimCourseName($courseName) {
    switch (substr($courseName, 0, 4)) {
        case 'Year':
            $courseName = substr($courseName, 6);
            break;

        case 'Nurs':
            $courseName = substr($courseName, 8);
            break;

        case 'Rece':
            $courseName = substr($courseName, 9);
            break;

        case 'Default':
            $courseName = $courseName;
            break;
    }
    return $courseName;
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function showComment($fldComment, $comment, $charBarID, $maxChar, $numCharID, $numRows, $enabledState) {
    // show comment for edit or display
    // show number of characters entered and disable save if too long        
    showRepLength($comment, $maxChar, $charBarID, $numCharID);
    ?>
    <div>
        <textarea
            name = "<?php echo $fldComment ?>"
            rows = "<?php echo $numRows ?>"
            cols = "<?php echo $_SESSION['cols'] ?>"
            onkeyup = "checkEnter(this.value, <?php echo $maxChar ?>, 'submit', '<?php echo $numCharID ?>', '<?php echo $charBarID ?>');"
            class = "subtextbox"
            onclick = "notSaved('status');"
            <?php echo $enabledState ?>
            ><?php echo $comment; ?></textarea>
    </div>
    <?php
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function freemium($modpath) {
    $path = $modpath.'/documents/gibbon_reporting_user_guide.pdf';
    $freemium = "<div id='freemium'>";
        $freemium .= "<table class='tableNoBorder'>";
            $freemium .= "<tr>";
                $freemium .= "<td>";
                    $freemium .= "Want extra features - contact:";
                $freemium .= "</td>";
                $freemium .= "<td>";
                    $freemium .= "<a href='mailto:info@rapid36.com'>info@rapid36.com</a>";
                $freemium .= "</td>";
            $freemium .= "</tr>";
            $freemium .= "<tr>";
                $freemium .= "<td>";
                    $freemium .= "User guide:";
                $freemium .= "</td>";
                $freemium .= "<td>";
                    $freemium .= "<a href='$path' target='_blank'>download PDF</a>";
                $freemium .= "</td>";
            $freemium .= "</tr>";
            $freemium .= "<tr>";
                $freemium .= "<td colspan='2'>";
                    $freemium .= "<a href='#' onclick='$(\"#freemium\").hide();'>Hide me</a>";
                $freemium .= "</td>";
            $freemium .= "</tr>";
        $freemium .= "</table>";
    $freemium .= "</div>";
    return $freemium;
}
////////////////////////////////////////////////////////////////////////////////
?>