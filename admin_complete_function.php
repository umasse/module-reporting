<?php

/*
 * Project:
 * Author:   Andy Statham
 * Date:
 */
class comp {

    var $schoolYearID;
    var $class;
    var $msg;

    function comp($guid, $connection2) {
        // get value of selected year
        $this->schoolYearID = getSchoolYearID($connection2, $schoolYearName, $currentYear);

        $this->yearGroupID = getYearGroupID();
        $this->reportID = getReportID();

    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function chooseReport($connection2, $reportID, $schoolYearID, $yearGroupID) {
        // drop down box to select report for selected year group and year
        if ($yearGroupID > 0) {
            $data = array(
                'schoolYearID' => $schoolYearID,
                'yearGroupID' => $yearGroupID
            );
            $sql = "SELECT arrReportAssign.reportID, reportName
                FROM arrReport
                INNER JOIN arrReportAssign
                ON arrReport.reportID = arrReportAssign.reportID
                WHERE assignStatus = 1
                AND yearGroupID = :yearGroupID
                AND arrReportAssign.schoolYearID = :schoolYearID
                ORDER BY reportNum";
            $rs = $connection2->prepare($sql);
            $rs->execute($data);

            ob_start();
            if ($rs->rowCount() > 0) {
                ?>
                <div style = "padding:2px;">
                    <div style="float:left;width:30%" class = "smalltext">Report</div>
                    <div style="float:left;width:70%">
                        <form name="frm_report" method="post" action="" style="display:inline">
                            <input type="hidden" name="yearGroupID" value="<?php echo $yearGroupID ?>" />
                            <input type="hidden" name="schoolYearID" value="<?php echo $schoolYearID ?>" />
                            <select name="reportID" onchange="this.form.submit()">
                                <option></option>
                                <?php
                                while ($row = $rs->fetch()) {
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
                    <div style="clear:both"></div>
                </div>
                <?php
            } else {
                echo "<div class='smalltext' style='margin-top:4px'>No reports assigned for this year group</div>";
            }
            return ob_get_clean();
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    function findStudentCount($connection2, $classID) {
        $data = array(
            "classID" => $classID
        );
        $sql = "SELECT gibbonPersonID
            FROM gibbonCourseClassPerson
            WHERE gibbonCourseClassID = :classID
            AND role = 'Student'";
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        return $rs->rowCount();
    }

    function findReportCount($connection2, $class_id) {
        $data = array(
            'classID' => $class_id,
            'reportID' => $this->reportID
        );
        $sql = "SELECT gibbonCourseClassPerson.gibbonCourseClassPersonID
            FROM gibbonCourseClassPerson
            INNER JOIN gibbonCourseClass
            ON gibbonCourseClass.gibbonCourseClassID = gibbonCourseClassPerson.gibbonCourseClassID
            INNER JOIN arrReportSubject
            ON arrReportSubject.subjectID = gibbonCourseClass.gibbonCourseID
            AND arrReportSubject.studentID = gibbonCourseClassPerson.gibbonPersonID
            WHERE gibbonCourseClassPerson.gibbonCourseClassID = :classID
            AND role = 'Student'
            AND arrReportSubject.reportID = :reportID";
        //print $sql;
        //print_r($data);
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        return $rs->rowCount();
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function readClassList($connection2) {
        $data = array(
            'schoolYearID' => $this->schoolYearID,
            'yearGroupID' => $this->yearGroupID
        );
        $sql = "SELECT DISTINCT gibbonCourseClass.gibbonCourseClassID AS classID, 
            gibbonCourse.nameShort AS courseNameShort, 
            CONCAT(gibbonCourse.nameShort, ' (', gibbonCourseClass.name, ')') AS className
            FROM gibbonCourse
            INNER JOIN gibbonCourseClass
            ON gibbonCourse.gibbonCourseID = gibbonCourseClass.gibbonCourseID
            INNER JOIN gibbonCourseClassPerson
            ON gibbonCourseClassPerson.gibbonCourseClassID = gibbonCourseClass.gibbonCourseClassID
            INNER JOIN gibbonStudentEnrolment
            ON gibbonStudentEnrolment.gibbonPersonID = gibbonCourseClassPerson.gibbonPersonID
            AND gibbonStudentEnrolment.gibbonSchoolYearID = gibbonCourse.gibbonSchoolYearID
            WHERE gibbonCourse.gibbonSchoolYearID = :schoolYearID
            AND gibbonYearGroupID = :yearGroupID
            ORDER BY gibbonCourse.name, gibbonCourseClass.name";
        //print $sql;
        //print_r($data);
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        return $rs;
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function mainform($guid, $connection2) {
        ?>
        <div class='smalltext'>
            <?php
            //chooseYearGroup($connection2, $this->yearGroupID);
            if ($this->yearGroupID > 0) {
                if ($this->reportID > 0) {
                    $classList = $this->readClassList($connection2);
                    ?>
                    <div>&nbsp;</div>
                    <table>
                        <tr>
                            <th>Class</th>
                            <th>Students</th>
                            <th>Reports</th>
                            <th>Status</th>
                        </tr>

                        <?php
                        while ($row = $classList->fetch()) {
                            $countStudent = $this->findStudentCount($connection2, $row['classID']);
                            $countReport = $this->findReportCount($connection2, $row['classID']);
                            if ($countStudent == $countReport) {
                                $status = 'Complete';
                                $color = '#0000ff';
                            } else {
                                if ($countReport == 0) {
                                    $status = 'Not Started';
                                    $color = '#ff0000';
                                } else {
                                    $status = 'Started';
                                    $color = '#00ff00';
                                }
                            }
                            ?>
                            <tr>
                                <td><?php echo $row['className']; ?></td>
                                <td style="text-align:center"><?php echo $countStudent ?></td>
                                <td style="text-align:center"><?php echo $countReport ?></td>
                                <td style="text-align:center;color:<?php echo $color ?>"><?php echo $status ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </table>
                    <?php
                }
            }
            ?>
        </div>
        <?php
    }
    ////////////////////////////////////////////////////////////////////////////
}
?>