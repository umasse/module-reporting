<?php
function get_reportName($connection2, $schoolYearID, $reportNum) {
    // return name of selected report
    try {
        $query_select = "SELECT arrReportName
            FROM arrSettings
            WHERE arrSchoolYearID = :schoolYearID
            AND arrReportNum = :reportNum";
        $select = $connection2->prepare($query_select);
        $data = array(
            ":schoolYearID"=>$schoolYearID,
            ":reportNum"=>$reportNum
            );
        $select->execute($data);
        if ($select->rowCount() > 0) {
            $row_select = $select->fetch();
            $reportName = $row_select['arrReportName'];
        } else {
            $reportName = '';
        }
        return $reportName;
    } catch(PDOException $e) {
        print "<div>" . $e->getMessage() . "</div>" ;
    }
}

function get_className($connection2, $classCode) {
    // return name of selected class
    try {
        $query_select = "SELECT gibbonCourse.name AS course, gibbonCourseClass.name AS class
            FROM gibbonCourseClass
            INNER JOIN gibbonCourse
            ON gibbonCourseClass.gibbonCourseID = gibbonCourse.gibbonCourseID
            WHERE gibbonCourseClassID = :classCode";
        $select = $connection2->prepare($query_select);
        $data = array(":classCode"=>$classCode);
        $select->execute($data);
        if ($select->rowCount() > 0) {
            $row_select = $select->fetch();
            $className = $row_select['course'].'.'.$row_select['class'];
        } else {
            $className = '';
        }
        return $className;
    } catch(PDOException $e) {
        print "<div>" . $e->getMessage() . "</div>" ;
    }
}

function get_markSet($connection2, $classID) {
    // find latest relevant markbook entry
    try {
        $data = array("classID"=>$classID);
        $sql = "SELECT gibbonMarkbookColumn.gibbonMarkbookColumnID,
            gibbonMarkbookColumn.name,
            gibbonMarkbookColumn.type,
            gibbonMarkbookColumn.attainment,
            gibbonMarkbookColumn.gibbonScaleIDAttainment,
            gibbonMarkbookColumn.gibbonRubricIDAttainment,
            gibbonMarkbookColumn.attainmentWeighting,
            gibbonMarkbookColumn.effort,
            gibbonMarkbookColumn.gibbonScaleIDEffort,
            gibbonMarkbookColumn.gibbonRubricIDEffort,
            gibbonMarkbookColumn.comment,
            gibbonMarkbookColumn.complete
            FROM gibbonMarkbookColumn
            WHERE gibbonCourseClassID = :classID
            ORDER BY completeDate DESC";
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        return $rs;
    } catch(PDOException $e) {
        print "<div>" . $e->getMessage() . "</div>" ;
    }
}

function get_markSetName($connection2, $markSetID) {
    // find latest relevant markbook entry
    try {
        $query_select = "SELECT name
            FROM gibbonMarkbookColumn
            WHERE gibbonMarkbookColumnID = :markSetID";
        $select = $connection2->prepare($query_select);
        $data = array(":markSetID"=>$markSetID);
        $select->execute($data);
        $row_select = $select->fetch();
        return $row_select['name'];
    } catch(PDOException $e) {
        print "<div>" . $e->getMessage() . "</div>" ;
    }
}

function insert_grades($connection2, $schoolYearID, $yearGroupID, $reportNum, $classCode,
        $markList, $studentID, $insertAttain, $insertEffort, $insertComment, $overwrite) {
    GLOBAL $gradesset;
    
    // read through list of marks.  if nothing in the report copy the grades from the mark list
    // make sure the user wants some data
    try {
        $result = 1;
        if ($markList != '') {
            $result = 2;
            $markList->execute();
            while ($row_markList = $markList->fetch()) {
                $data = array(
                    "schoolYearID"=>$schoolYearID,
                    "reportNum"=>$reportNum,
                    "classCode"=>$classCode,
                    "personID"=>$row_markList['gibbonPersonIDStudent']
                    );
                // see which fields need to be copied
                if ($insertAttain == 1 || $insertEffort == 1 || $insertComment == 1) {
                    // check if there is a report
                    $query_select = "SELECT arrPersonID
                        FROM arrReportSubject
                        WHERE arrSchoolYearID = :schoolYearID
                        AND arrReportNum = :reportNum
                        AND arrCourseClassID = :classCode
                        AND arrPersonID = :personID";
                    $select = $connection2->prepare($query_select);
                    $select->execute($data);

                    if ($select->rowCount() > 0) {
                        if ($overwrite == 1) {
                            $query_action = "UPDATE arrReportSubject
                                SET ";
                            $set = 0;
                            if ($insertAttain == 1) {
                                $attainmentValue = $row_markList['attainmentValue'];
                                $attainmentValue = gradeToNumber($attainmentValue, $yearGroup, $schoolYearID); 
                                $query_action .= " arrAttainment = :attainmentValue";
                                $data[":attainmentValue"] = $attainmentValue;
                                $set++;
                            }
                            if ($insertEffort == 1) {
                                if ($set > 0) {
                                    $query_action .= ", ";
                                }
                                $query_action .= " arrEffort = :effortValue";
                                $data["effortValue"] = $row_markList['effortValue'];
                                $set++;
                            }
                            if ($insertComment == 1) {
                                if ($set > 0) {
                                    $query_action .= ", ";
                                }
                                $query_action .= " arrSubjectComment = :comment";
                                $data['comment'] = $row_markList['comment'];
                            }
                            $query_action .= " WHERE arrSchoolYearID = :schoolYearID
                                AND arrReportNum = :reportNum
                                AND arrCourseClassID = :classCode
                                AND arrPersonID = :personID";
                        } else {
                            // SET :set,
                            $query_action = "INSERT IGNORE INTO arrReportSubject
                                SET
                                arrSchoolYearID = :schoolYearID,
                                arrReportNum = :reportNum,
                                arrCourseClassID = :classCode,
                                arrPersonID = :personID";
                                if ($insertAttain == 1) {
                                    $attainmentValue = $row_markList['attainmentValue'];
                                    $attainmentValue = gradeToNumber($attainmentValue, $yearGroup, $schoolYearID); 
                                    $query_action .= " arrAttainment = :attainmentValue";
                                    $data[":attainmentValue"] = $attainmentValue;
                                }
                                if ($insertEffort == 1) {
                                    $query_action .= ", arrEffort = :effortValue";
                                    $data["effortValue"] = $row_markList['effortValue'];
                                }
                                if ($insertComment == 1) {
                                    $query_action .= ", arrSubjectComment = :comment";
                                    $data['comment'] = $row_markList['comment'];
                                }
                        }
                        $action = $connection2->prepare($query_action);
                        $ok = $action->execute($data);
                        if ($ok == FALSE)
                            $result = 1;
                        
                    } else {
                        $result = 3;
                    }
                } else {
                    $result = 1;
                }
            }
        }
        return $result;
    } catch(PDOException $e) {
        print "<div>" . $e->getMessage() . "</div>" ;
    }
}

function read_markList($connection2, $markSetID, $studentID) {
    // read marks from selected unit
    try {
        $data = array(":markSetID"=>$markSetID);
        $query_select = "SELECT gibbonPersonIDStudent, attainmentValue, effortValue,
            comment, CONCAT(preferredName, ' ', surname) AS name
            FROM gibbonMarkbookEntry
            INNER JOIN gibbonPerson
            ON gibbonMarkbookEntry.gibbonPersonIDStudent = gibbonPerson.gibbonPersonID
            WHERE gibbonMarkbookColumnID = :markSetID";
        if ($studentID > 0) {
            $query_select .= " AND gibbonPersonID = :studentID";
            $data[":studentID"] = $studentID;
        }
        $query_select .= " ORDER BY surname, firstName";
        $select = $connection2->prepare($query_select);
        $select->execute($data);
        return $select;
    } catch(PDOException $e) {
        print "<div>" . $e->getMessage() . "</div>" ;
    }
}

function show_marks($markList) {
    if ($markList->rowCount() > 0) {
        echo "<div class='smalltext'>This is the data you can import:</div>";
        echo "<table class = 'smalltext'>";
        echo "<tr class='head'>";
        echo "<th>Name</th>";
        echo "<th>Attain</th>";
        echo "<th>Effort</th>";
        echo "<th>Comment</th>";
        echo "</tr>";
        $markList->execute();
        while ($row_markList = $markList->fetch()) {
            echo "<tr>";
            //echo "<td>".$row_select['gibbonPersonIDStudent']."</td>";
            echo "<td>".$row_markList['name']."</td>";
            echo "<td>".$row_markList['attainmentValue']."</td>";
            echo "<td>".$row_markList['effortValue']."</td>";
            echo "<td>".$row_markList['comment']."</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='smalltext'>No marks to insert</div>";
    }
}

function read_yearGroupList($connection, $classCode) {
    $data = array(
        'classCode' => $classCode
    );
    $sql = "SELECT gibbonYearGroupIDList AS yearGroup
        FROM gibbonCourse
        INNER JOIN gibbonCourseClass
        ON gibbonCourseClass.gibbonCourseID = gibbonCourse.gibbonCourseID
        WHERE gibbonCourseClass.gibbonCourseClassID = :classCode";
    $rs = $connection->prepare($sql);
    $rs->execute($data);
    return $rs;
}

function yearGroupListToYearGroup($yearGroupIDList) {
    $yearGroupIDList->execute;
    $row = $yearGroupIDList->fetch();
    $yearGroupID = $row['yearGroupIDLIst'];
    if (strpos($yearGroupID, ",") > 0) {
        $pos = strpos($yearGroupID, ",");
        $yearGroupID = substr($yearGroupID, 0, $pos-1);
    }
    $yearGroupID = intval($yearGroupID);
    return $yearGroupID;
}

function gradeToNumber($grade, $yearGroup, $schoolYearID) {
    GLOBAL $gradeset;
    print $yearGroup;
    if ($schoolYearID >= 20) {
        if ($yearGroup >= 12) {
            $gradesetID = 1;
        } elseif ($yearGroup >= 10) {
            $gradesetID = 3;
        } else {
            $gradesetID = 15;
        }
        
        $gradeset->execute();
        $gradeID = 0;
        while ($row = $gradeset->fetch()) {
            if ($grade == $row['value']) {
                $gradeID = $row['gibbonScaleGradeID'];
            }
        }
    } else {
        $gradeID = $grade;
    }
    return $gradeID;
}

// show status of report in statusbar at top of reports
function show_insert_status($status) {
    switch ($status) {
        case 3:
            $col = "#F00";
            $class = "warning";
            $msg = "WARNING - you must save reports before you can insert marks";
            break;

        case 1:
            $col = "#F00";
            $class = "warning";
            $msg = "FAILED - some items did not save";
            break;

        case 2:
            $col = "#0F0";
            $class = "success";
            $msg = "SUCCESS - your marks have been inserted";
            break;
    }

    echo "<div class = '$class' id = 'status'>$msg</div>";
}
?>
