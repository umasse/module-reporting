<?php

/*
 * Project:
 * Author:   Andy Statham
 * Date:
 */
function admin_navbar($guid, $connection2, $title) {
    // admin suite navigation
    $path = $_SESSION[$guid]['absoluteURL'];
    $pathroot = $path."/index.php?q=/modules/".$_SESSION[$guid]['module']."/";
    $option = array(
        'Create', 'admin_define.php',
        'Assign', 'admin_assign.php',
        'Design', 'admin_design.php',
        'Access', 'admin_access.php',
        'Criteria', 'admin_criteria.php',
        'Complete', 'admin_complete.php',
        'Start of Year', 'admin_startyear.php'
    );
    /*
    $option = array(
        'Complete', 'admin_complete.php',
        'Roles', 'admin_role.php',
        'Define', 'admin_define.php',
        'Assign', 'admin_assign.php',
        'Access', 'admin_access.php',
        'Criteria', 'admin_criteria.php',
        'Subject', 'admin_subject.php',
        'UOI', 'admin_uoi.php',
        'EAL', 'admin_eal.php',
        'Start of Year', 'admin_startyear.php'
    );
     * 
     */

    echo "<div style = 'float:left; width:20px;'>&nbsp;</div>";
    echo "<div style = 'float:left;' class = 'smalltext'>";
        for ($i=0; $i<count($option)/2; $i++) {
            if (strtolower($title) == strtolower($option[$i*2])) {
                echo $option[$i*2];
            } else {
                $link = $pathroot.$option[$i*2+1];
                echo "<a href='$link'>".$option[$i*2]."</a>";
            }
            // show separator after each option apart from last one
            if ($i<count($option)/2-1) {
                echo "&nbsp;|&nbsp;";
            }
        }
    echo "</div>";
    echo "<div style = 'clear:both;'>&nbsp;</div>";
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function getMode() {
    $mode = '';
    if (isset($_POST['mode'])) {
        $mode = $_POST['mode'];
    } else {
        if (isset($_GET['mode'])) {
            $mode = $_GET['mode'];
        }
    }
    return $mode;
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function readReport($connection2, $schoolYearID) {
    // read list of all reports for selected year
    $data = array('schoolYearID' => $schoolYearID);
    $sql = "SELECT reportID, schoolYearID, reportName, reportNum, reportOrder, 
        gradeScale, gibbonScale.nameShort, gibbonScale.usage
        FROM arrReport
        LEFT JOIN gibbonScale
        ON gibbonScale.gibbonScaleID = arrReport.gradeScale
        WHERE schoolYearID = :schoolYearID
        ORDER BY reportNum, reportName";
    $rs = $connection2->prepare($sql);
    $rs->execute($data);
    return $rs;
}
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
function read_subjectlist($connection2, $yearGroupID, $schoolYearID) {
    $data = array(
        "schoolYearID" => $schoolYearID,
        "yearGroupID" => '%'.$yearGroupID.'%'
    );
    $sql = "SELECT DISTINCT gibbonCourse.gibbonCourseID AS subjectID, 
        gibbonCourse.name AS subjectName
        FROM gibbonCourse
        INNER JOIN gibbonCourseClass
        ON gibbonCourse.gibbonCourseID = gibbonCourseClass.gibbonCourseID
        WHERE gibbonSchoolYearID = :schoolYearID
        AND gibbonYearGroupIDList LIKE :yearGroupID
        AND reportable = 'Y'";
    //print $sql;
    //print_r($data);
    $rs = $connection2->prepare($sql);
    $rs->execute($data);
    return $rs;
}
////////////////////////////////////////////////////////////////////////////////
?>
