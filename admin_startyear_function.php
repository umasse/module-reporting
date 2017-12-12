<?php

/*
 * Project:
 * Author:   Andy Statham
 * Date:
 */
class startyear {
    var $msg;
    var $class;

    function startyear($guid, $connection2) {
        $this->previousYear = $this->get_schoolYearPrevious($connection2);
        $this->schoolYearID = getSchoolYearID($connection2, $schoolYearName, $currentYear);
        if (isset($_POST['copyreportsubmit'])) {
            $this->copyData($connection2);
        }
    }

    function get_schoolYearPrevious($connection2) {
        $currentYear = getSchoolYearCurrent($connection2);
        $sql = "SELECT * FROM gibbonSchoolYear ORDER BY name DESC";
        $rs = $connection2->prepare($sql);
        $rs->execute();
        $previousYear = 0;
        while ($row = $rs->fetch()) {
            if ($row['gibbonSchoolYearID'] == $currentYear) {
                $row = $rs->fetch();
                $previousYear = $row['gibbonSchoolYearID'];
            }
        }
        return $previousYear;
    }

    function mainform($guid, $connection2) {
        echo "<form name='copyreport' method='post' action=''>";
            echo "<input type='hidden' name='schoolYearID' value='$this->schoolYearID' />";
            echo "<p>This will copy data from last year and will include:</p>";
            echo "<ul>";
            echo "<li>Report templates</li>";
            echo "<li>Assign reports to current year groups</li>";
            echo "<li>Setup details for each template</li>";
            echo "<li>Subject criteria</li>";
            echo "</ul>";
            echo "<p>If you run this feature it will not overwrite existing data</p>";
            echo "<p>When you are ready click on the button below</p>";
            echo "<p class='highlight'>Not yet ready to use</p>";
            //echo "<input type='submit' name='copyreportsubmit' value='Copy' />";
        echo "</form>";
    }



    function copyData($connection2) {
        $ok = $this->copyReport($connection2);
        $msg = '';
        if ($ok) {
            $ok = $this->copyCriteria($connection2);
        }
        if ($ok) {
            //$ok = $this->copyUoi($connection2);
        }
        if ($ok) {
            $msg = "<p>Operation complete</p>";
        } else {
            $msg = "<p>Possible problem.  Please contact the administrator</p>";
        }

        setStatus($ok, 'Save', $this->msg, $this->class);
    }

    function copyReport($connection2) {
        // get last year's reports
        $ok = true;
        $data = array(
            'previousYearID' => $this->previousYear
        );
        $sql = "SELECT *
            FROM arrReport
            INNER JOIN arrReportAssign
            ON arrReportAssign.arrReportID = arrReport.arrReportID
            WHERE arrReport.arrSchoolYearID = :previousYearID";
        $rs = $connection2->prepare($sql);
        $rs->execute($data);

        // for each report
        while ($row = $rs->fetch()) {
            // create report for current year
            $data = array(
                'schoolYearID' => $this->schoolYearID,
                'arrReportNum' => $row['arrReportNum'],
                'arrReportOrder' => $row['arrReportOrder'],
                'arrReportType' => $row['arrReportType'],
                'arrReportName' => $row['arrReportName'],
                'arrReportDate' => $row['arrReportDate'],
                'dateStart' => $row['dateStart'],
                'dateEnd' => $row['dateEnd']
            );
            $sql = "INSERT IGNORE INTO arrReport
                SET arrSchoolYearID = :schoolYearID,
                arrReportNum = :arrReportNum,
                arrReportOrder = :arrReportOrder,
                arrReportType = :arrReportType,
                arrReportName = :arrReportName,
                arrReportDate = :arrReportDate,
                dateStart = :dateStart,
                dateEnd = :dateEnd";
            $rs2 = $connection2->prepare($sql);
            $result = $rs2->execute($data);
            if (!$result) {
                $ok = $result;
            }

            if ($ok) {
                // get id of new report
                $data = array(
                    'arrReportName' => $row['arrReportName'],
                    'schoolYearID' => $this->schoolYearID
                );
                $sql = "SELECT arrReportID
                    FROM arrReport
                    WHERE arrReportName = :arrReportName
                    AND arrSchoolYearID = :schoolYearID";
                $rs3 = $connection2->prepare($sql);
                $rs3->execute($data);
                $row3 = $rs3->fetch();
                $arrReportID = $row3['arrReportID'];

                // assign to year groups
                $data = array(
                    'schoolYearID' => $this->schoolYearID,
                    'arrYearGroupID' => $row['arrYearGroupID'],
                    'arrReportID' => $arrReportID,
                    'arrAssignStatus' => $row['arrAssignStatus'],
                    'teacherOpen' => $row['teacherOpen'],
                    'parentOpen' => $row['parentOpen']
                );
                $sql = "INSERT IGNORE INTO arrReportAssign
                    SET arrSchoolYearID = :schoolYearID,
                    arrYearGroupID = :arrYearGroupID,
                    arrReportID = :arrReportID,
                    arrAssignStatus = :arrAssignStatus,
                    teacherOpen = :teacherOpen,
                    parentOpen = :parentOpen";
                $rs4 = $connection2->prepare($sql);
                $result = $rs4->execute($data);
                if (!$result) {
                    $ok = $result;
                }
            }
        }
        return $ok;
    }

    function copyCriteria($connection2) {
        $ok = true;
        // select criteria and subject report setup details from previous year
        $data = array(
            'previousYearID' => $this->previousYear
        );
        $sql = "SELECT *
            FROM gibbonCourse
            LEFT JOIN arrCriteria
            ON arrCriteria.arrCourseID = gibbonCourse.gibbonCourseID
            LEFT JOIN arrSubjectDetail
            ON arrSubjectDetail.arrCourseID = gibbonCourse.gibbonCourseID
            WHERE gibbonCourse.gibbonSchoolYearID = :previousYearID";
        $rs = $connection2->prepare($sql);
        $rs->execute($data);

        while ($row = $rs->fetch()) {
            // create record for new year
            // find courseID for course in current year
            $data = array(
                'name' => $row['name'],
                'schoolYearID' => $this->schoolYearID
            );
            $sql = "SELECT gibbonCourseID
                FROM gibbonCourse
                WHERE name = :name
                AND gibbonSchoolYearID = :schoolYearID";
            $rs2 = $connection2->prepare($sql);
            $ok = $rs2->execute($data);
            $row2 = $rs2->fetch();
            $courseID = $row2['gibbonCourseID'];

            // now insert new criteria record
            $data = array(
                'courseID' => $courseID,
                'criteriaName' => $row['arrCriteriaName'],
                'criteriaOrder' => $row['arrCriteriaOrder']
            );
            $sql = "INSERT IGNORE INTO arrCriteria
                SET arrCourseID = :courseID,
                arrCriteriaName = :criteriaName,
                arrCriteriaOrder = :criteriaOrder";
            $rs3 = $connection2->prepare($sql);
            $result = $rs3->execute($data);
            if (!$result) {
                $ok = $result;
            }


            // and insert new subject detail record
            $data = array(
                'schoolYearID' => $this->schoolYearID,
                'courseID' => $courseID,
                'yearGroupID' => intval($row['gibbonYearGroupIDList']),
                'courseType' => $row['arrCourseType'],
                'maxChar' => $row['arrMaxChar'],
                'rowHeight' => $row['arrRowHeight'],
                'commentHeight' => $row['arrCommentHeight'],
                'subjectPosition' => $row['arrSubjectPosition']
            );
            $sql = "INSERT IGNORE INTO arrSubjectDetail
                SET arrSchoolYearID = :schoolYearID,
                arrCourseID = :courseID,
                arrYearGroupID = :yearGroupID,
                arrCourseType = :courseType,
                arrMaxChar = :maxChar,
                arrRowHeight = :rowHeight,
                arrCommentHeight = :commentHeight,
                arrSubjectPosition = :subjectPosition";
            //print $sql;
            //print_r($data);
            $rs4 = $connection2->prepare($sql);
            $result = $rs4->execute($data);
            if (!$result) {
                $ok = $result;
            }
        }
        return $ok;
    }
}
?>
