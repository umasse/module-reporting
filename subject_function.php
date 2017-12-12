<?php
// ARR Gibbon module
// functions for use with subject reports

class subrep {

    var $class;
    var $msg;
    var $view;
    var $repStatus;
    var $yearGroupID;


    function __construct($guid, $connection2) {

        $this->classView    = $_SESSION[$guid]['classView'];
        $this->studView     = $_SESSION[$guid]['studView'];
        $this->maxGrade     = $_SESSION[$guid]['maxGrade'];
        $this->repView      = $_SESSION[$guid]['repView'];
        $this->repEdit      = $_SESSION[$guid]['repEdit'];

        // get ID of selected teacher
        $this->teacherID = getTeacherID($guid);

        // get role of current user
        $this->role = $_SESSION[$guid]['gibbonRoleIDCurrent'];
        
        // get selected school year
        $this->schoolYearID = getSchoolYearID($connection2, $this->schoolYearName, $this->currentYearID);

        // id of student being viewed
        $this->studentID = getStudentID();

        // check if left students should be shown
        $this->showLeft = getLeft();

        // if class has been selected read the class list
        $this->classID = getClassID();
        if ($this->classID > 0) {
            // find subject for selected class
            $this->subjectID = $this->findSubjectID($connection2);
            
            // find any criteria associated with this subject
            $this->criteriaList = readCriteriaList($connection2, $this->subjectID);

            // read classlist of selected class
            $this->classList = $this->readClassList($connection2);

            // find the yeargroup for this class
            $this->yearGroupID = $this->findYearGroup($connection2);
        }
        

        // find maximum length of comment for this class
        //findMaxChar($connection2, $this->classID, $this->courseType, $this->maxChar);
        $this->maxChar = 1000;
        
        // adjust box size for size of comment
        //$this->numRows = intval($this->maxChar/60);
        //$this->numCols = $_SESSION['numCols'];
        $this->numRows = 15;
        $this->numCols = 80;

        // get ID parameter for this report
        $this->reportID = getReportID();
        // see if user has access to the reports
        $this->repAccess = 0;
        if ($this->reportID > 0) {
            $this->repAccess = findReportstatus($connection2, $this->reportID, $this->role);
            $this->reportDetail = readReportDetail($connection2, $this->reportID);
            $reportRow = $this->reportDetail->fetch();
            $this->gradeScale = $reportRow['gradeScale']; // id for grade scale to be used for assessment
            $this->gradeList = readGradeList($connection2, $this->gradeScale);
        }

        // if view only use enabledState to disable controls
        if ($this->repAccess) {
            $this->enabledState = "";
        } else {
            $this->enabledState = "disabled='disabled'";
        }

        // check whether to view individual student or whole class
        $this->view = getView();

        // submit has been pressed so save
        if (isset($_POST['subsubmit'])) {
            $ok = $this->save($connection2);
            setStatus($ok, 'Save', $this->msg, $this->class);
        }
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function findSubjectID($connection2) {
        try {
            $data = array(
                'classID' => $this->classID
            );
            $sql = "SELECT gibbonCourseID AS subjectID
                FROM gibbonCourseClass
                WHERE gibbonCourseClassID = :classID";
            $rs = $connection2->prepare($sql);
            $rs->execute($data);
            $row = $rs->fetch();
            $subjectID = $row['subjectID'];
            return $subjectID;
        } catch(PDOException $e) {
            print "<div>" . $e->getMessage() . "</div>" ;
        }
            
    }
    ////////////////////////////////////////////////////////////////////////////
    
    ////////////////////////////////////////////////////////////////////////////
    function getStatus() {
        $status = '';
        if (isset($_POST['status'])) {
            $status = $_POST['status'];
        } else {
            if (isset($_GET['status'])) {
                $status = $_GET['status'];
            }
        }
        return $status;
    }

    /*
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
     * 
     */
    ////////////////////////////////////////////////////////////////////////////

    // -------------------------------------------------------------------------
    function findYearGroup($connection2) {
        // find year group of selected class
        $data = array(
            'classID' => $this->classID
        );
        $sql = "SELECT gibbonYearGroupIDList
                FROM gibbonCourseClass
                INNER JOIN gibbonCourse
                ON gibbonCourseClass.gibbonCourseID = gibbonCourse.gibbonCourseID
                WHERE gibbonCourseClassID = :classID";
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        $row = $rs->fetch();
        $yearGroupID = $row['gibbonYearGroupIDList'];
        return $yearGroupID;
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    // read recordsets
    ////////////////////////////////////////////////////////////////////////////
    
    
    function readClassesList($connection2) {
        // read list of classes assigned to selected teacher
        try {
            $data = array(
                "teacherID"=>$this->teacherID,
                "schoolYearID"=>$this->schoolYearID);
            $sql = "SELECT gibbonCourseClassPerson.gibbonCourseClassID, surname, preferredName, gibbonCourseClass.nameShort AS class, gibbonCourse.nameShort AS course
                FROM gibbonCourseClassPerson
                INNER JOIN gibbonPerson
                ON gibbonCourseClassPerson.gibbonPersonID = gibbonPerson.gibbonPersonID
                INNER JOIN gibbonCourseClass
                ON gibbonCourseClass.gibbonCourseClassID = gibbonCourseClassPerson.gibbonCourseClassID
                INNER JOIN gibbonCourse
                ON gibbonCourseClass.gibbonCourseID = gibbonCourse.gibbonCourseID
                WHERE gibbonCourseClassPerson.gibbonPersonID = :teacherID
                AND gibbonSchoolYearID = :schoolYearID
    	        AND gibbonCourseClass.reportable = 'Y'";
            //print $sql;
            //print_r($data);
            $rs = $connection2->prepare($sql);
            $rs->execute($data);
            if ($rs->rowCount() == 0) {
                $this->classID = '';
            }
            return $rs;
        } catch(PDOException $e) {
            print "<div>" . $e->getMessage() . "</div>" ;
        }
    }
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    function readClassList($connection2) {
        // read list of students in selected class
        try {
            $data = array(
                ":classID"=>$this->classID,
                ":schoolYearID"=>$this->schoolYearID);
            $sql = "SELECT gibbonCourseClassPerson.gibbonPersonID, surname,
                    preferredName, gibbonRollGroup.nameShort
                    FROM gibbonCourseClassPerson
                    INNER JOIN gibbonPerson
                    ON gibbonCourseClassPerson.gibbonPersonID = gibbonPerson.gibbonPersonID
                    INNER JOIN gibbonStudentEnrolment
                    ON gibbonPerson.gibbonPersonID = gibbonStudentEnrolment.gibbonPersonID
                    INNER JOIN gibbonRollGroup
                    ON gibbonStudentEnrolment.gibbonRollGroupID = gibbonRollGroup.gibbonRollGroupID
                    WHERE gibbonCourseClassID = :classID
                    AND gibbonCourseClassPerson.role LIKE '%Student%'
                    AND gibbonStudentEnrolment.gibbonSchoolYearID = :schoolYearID
                    AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "')";
            if ($this->showLeft == 0) {
                $sql .= " AND gibbonCourseClassPerson.role = 'Student'
                    AND gibbonPerson.status = 'Full'";
            } else {
                $sql .= " AND (gibbonCourseClassPerson.role LIKE '%Student%' OR gibbonCourseClassPerson.role LIKE '%Left%')";
            }
            $sql .= " ORDER BY surname, preferredName";
            //print $sql;
            //print_r($data);
            $rs = $connection2->prepare($sql);
            $rs->execute($data);
            return $rs;
        } catch(PDOException $e) {
            print "<div>" . $e->getMessage() . "</div>" ;
        }
    }
    // -------------------------------------------------------------------------
/*
    // -------------------------------------------------------------------------
    function readCriteriaGrade($connection2, $studentID) {
        // read criteria for this subject
        // together with any associated grades that have been stored for this student
        try {
            $data = array(
                "studentID" => $studentID,
                "subjectID" => $this->subjectID,
                "reportID" => $this->reportID
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
    // -------------------------------------------------------------------------
*/
    // -------------------------------------------------------------------------
    function changeView($guid) {
        // change between class and student view
        $path = $_SESSION[$guid]['absoluteURL'];
        $page = $_SESSION[$guid]['address'];
        $link = $path."/index.php?q=".$page;
        if ($this->view == $this->studView) {
            $viewlink = $link."&amp;view=".$this->classView.
                    "&amp;classID=".$this->classID.
                    "&amp;reportID=".$this->reportID.
                    "&amp;schoolYearID=".$this->schoolYearID;
            $text = "Change to class view";
        } else {
            $viewlink = $link."&amp;view=".$this->studView.
                    "&amp;classID=".$this->classID.
                    "&amp;reportID=".$this->reportID.
                    "&amp;schoolYearID=".$this->schoolYearID;
            $text = "Change to student view";
        }
        $click = "if (checkForEdit('status') == true) location.href ='$viewlink'";
        ?>

        <div class = "smalltext">
        <a href = "#" onclick = "<?php echo $click ?>"><?php echo $text ?></a>
        </div>
        <div>&nbsp;</div>
        <?php
    }
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    function chooseLeft() {
        // choose whether to show students who have left
        echo "<div>&nbsp;</div>";
        echo "<div class = 'smalltext'>";
            echo "<form name = 'frm_showleft' method = 'post' action = ''>";
                echo "<input type = 'hidden' name = 'classID' value = '$this->classID' />";
                echo "<input type = 'hidden' name = 'reportID' value = '$this->reportID' />";
                echo "<input type = 'hidden' name = 'schoolYearID' value = '$this->schoolYearID' />";
                echo "<input type = 'hidden' name = 'studentID' value = '' />";
                echo "<input type = 'hidden' name = 'view' value = '$this->view' />";
                echo "<input type = 'hidden' name = 'showLeft' value = '$this->showLeft' />";
                echo "<input type = 'checkbox' name = 'setShowLeft' value = '1' ";
                    if ($this->showLeft == 1) {
                        echo "checked='checked'";
                    }
                    echo "onclick = 'if (this.checked) this.form.showLeft.value = 1; else this.form.showLeft.value = 0;this.form.submit();' ";
                echo "/>";
                echo " show left";
            echo "</form>";
        echo "</div>";
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function save($connection2) {
        // begin save process
        $ok = true;
        if ($this->view == $this->studView) {
            // single student only
            //$idtext = 'comtext'.$this->classID.'_'.$this->studentID;
            $ok = $this->saveReportDetail($connection2, $this->studentID);
        } else {
            // class view
            $classList = $this->classList;
            $classList->execute();
            while ($row = $classList->fetch()) {
                $result = $this->saveReportDetail($connection2, $row['gibbonPersonID']);
            }
        }
        return $ok;
    }
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    function saveReportDetail($connection2, $studentID) {
        // save report for individual student
        $fldComment = "comment".$studentID;
        $comment = $_POST[$fldComment];
        $comment = preg_replace( "/\r|\n/", "", $comment );

        // save report
        $ok = true;
        try {
            // save comment
            $data = array(
                "studentID"=>$studentID,
                "subjectID"=>$this->subjectID,
                "reportID"=>$this->reportID,
                "comment"=>$comment
            );
            $sql = "INSERT INTO arrReportSubject
                SET studentID = :studentID,
                subjectID = :subjectID,
                reportID = :reportID,
                subjectComment = :comment
                ON DUPLICATE KEY UPDATE
                subjectComment = :comment";
            $rs = $connection2->prepare($sql);
            $result = $rs->execute($data);
            if (!$result) {
                $ok = $result;
            }
            
            if ($ok) {
                // save grades
                $criteriaList = $this->criteriaList;
                $criteriaList->execute();
                while ($row = $criteriaList->fetch()) {
                    $fldID = "crit".$studentID.'_'.$row['criteriaID'];
                    $markID = "mark".$studentID.'_'.$row['criteriaID'];
                    $percentID = "percent".$studentID.'_'.$row['criteriaID'];
                    $gradeID = $_POST[$fldID];
                    $mark = $_POST[$markID];
                    $percent = $_POST[$percentID];
                    $data = array(
                        'reportID' => $this->reportID,
                        'criteriaID' => $row['criteriaID'],
                        'studentID' => $studentID,
                        'gradeID' => $gradeID,
                        'mark' => $mark,
                        'percent' => $percent
                    );
                    $sql = "INSERT INTO arrReportGrade
                        SET reportID = :reportID,
                        studentID = :studentID,
                        criteriaID = :criteriaID,
                        gradeID = :gradeID,
                        mark = :mark,
                        percent = :percent
                        ON DUPLICATE KEY UPDATE
                        gradeID = :gradeID,
                        mark = :mark,
                        percent = :percent";
                    $rs = $connection2->prepare($sql);
                    $result = $rs->execute($data);
                    if (!$result) {
                        $ok = $result;
                    }
                }
            }
            return $ok;
        } catch(PDOException $e) {
            print "<div>" . $e->getMessage() . "</div>" ;
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    // display
    ////////////////////////////////////////////////////////////////////////////
    function showClassesList($connection2) {
        // show list of classes in drop down box
        $this->classesList = $this->readClassesList($connection2);
	ob_start();
	echo "<div style = 'padding:2px;'>";
	if ($this->teacherID > 0) { // if a teacher has been selected
            if ($this->classesList->RowCount() > 0) { // and the teacher has some classes
                $classesList = $this->classesList;
                ?>
                <div style = "float:left;width:30%;" class = "smalltext">Class</div>
                <div style = "float:left;width:70%;">
                    <form name = "classid" method = "post" action = "">
                        <input type = "hidden" name = "rep" value = "subject" />
                        <input type = "hidden" name = "schoolYearID" value = "<?php echo $this->schoolYearID ?>" />
                        <input type = "hidden" name = "teacherID" value = "<?php echo $this->teacherID ?>" />
                        <input type = "hidden" name = "reportID" value = "" />
                        <input type = "hidden" name = "status" value = "0" />
                        <input type = "hidden" name = "view" value = "<?php echo $this->view ?>" />
                        <select name = "classID" style = "float:left;width:60%;" onchange = "if (checkForEdit('status')) this.form.submit();">
                            <option></option>
                            <?php
                            while ($row = $classesList->fetch()) { // for each class
                                $selected = ($this->classID == $row['gibbonCourseClassID']) ? 'selected' : '';
                                echo "<option value='".$row['gibbonCourseClassID']."' $selected>";
                                    echo $row['course'].'.'.$row['class'];
                                echo "</option>";
                            }
                            ?>
                         </select>
                    </form>
                </div>
                <div style = "clear:both;"></div>
                <?php
            } else {
                echo "<div class = 'smalltext'>No classes</div>";
            }
	}
	echo "</div>";
	return ob_get_clean();
    }
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    function showClassList($guid, $connection2) {
        // show classlist with appropriate links and colours
        // this is going in session sidebar so use output buffer
        if ($this->reportID > 0) {
            $classList = $this->classList;
            $classList->execute();
            ob_start();
            if ($this->classID > 0) { // only do something if a class has been selected
                if ($classList->rowCount() > 0) { // only worry if there are students in the class
                    $this->changeView($guid); // show link to change between student and class view
                    while ($row = $classList->fetch()) { // for each student in the class
                        $classStudentID = $row['gibbonPersonID']; // read their ID;
                        $name = $row['surname'].', '.$row['preferredName'].' ('.$row['nameShort'].')'; // name to be shown in list
                        $this->studentLink($guid, $connection2, $name, $classStudentID); // create link and display
                    }
                } else { // no one in the class
                    echo "<div>&nbsp;</div>";
                    echo "<div class = 'smalltext'>No students listed</div>";
                }

                $this->chooseLeft($this->showLeft); // set value to show students who have left the class
            }
            return ob_get_clean();
        }
    }
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    function showTeacherList($connection2) {
        // display list of teachers in drop down box
        $this->teacherList = $this->readTeacherList($connection2);
        ob_start(); //Turn on output buffering
        ?>
        <div style = "padding:2px;">
            <div style = "float:left;width:30%;" class = "smalltext">Teacher</div>
            <div style = "float:left;width:70%;">
                <form name = "teacher" method = "post" action="">
                    <input type = "hidden" name = "rep" value = "subject" />
                    <input type = "hidden" name = "classID" value = "" />
                    <input type = "hidden" name = "reportID" value = "" />
                    <input type = "hidden" name = "status" value = "0" />
                    <input type = "hidden" name = "schoolYearID" value = "<?php echo $this->schoolYearID ?>" />
                    <input type = "hidden" name = "view" value = "<?php echo $this->view ?>" />
                    <select name = "teacherID" style = "float:left;width:95%;" onchange = "if (checkForEdit('status')) this.form.submit();">
                        <option></option>
                        <?php
                        $teacherList = $this->teacherList;
                        while ($row = $teacherList->fetch()) {
                            $selected = ($this->teacherID == $row['gibbonPersonID']) ? 'selected' : '';
                            echo "<option value='".$row['gibbonPersonID']."' $selected>";
                                echo $row['surname'].', '.$row['preferredName'];
                            echo "</option>";
                        }
                        ?>
                    </select>
                </form>
            </div>
            <div style = "clear:both;"></div>
        </div>
        <?php
        return ob_get_clean();
    }
    // -------------------------------------------------------------------------


    // -------------------------------------------------------------------------
    function selectGrade($fldID, $gradeID) {
        // drop down box for selecting grades
        ?>
        <select name="<?php echo $fldID ?>" <?php echo $this->enabledState ?>>
            <option> </option>
            <?php
            $gradeset = $this->gradeList;
            $gradeset->execute();
            while ($row = $gradeset->fetch()) {
                $selected = '';
                if ($gradeID == $row['gibbonScaleGradeID']) {
                    $selected = 'selected';
                }
                echo "<option value='".$row['gibbonScaleGradeID']."' $selected >";
                    echo $row['value'];
                echo "</option>";
            }
            ?>
        </select>
        <?php
    }
    // -------------------------------------------------------------------------


    ////////////////////////////////////////////////////////////////////////////
    // miscellanous functions
    ////////////////////////////////////////////////////////////////////////////
    function reportComplete($connection2, $studentID) {
        // check completeness of report
        $count = 0;
        $numcrit = 0;

        // check how many criteria are required
        $numcrit = $this->criteriaList->rowCount() + 1;

        // read report
        $report = readSubReport($connection2, $studentID, $this->subjectID, $this->reportID, $this->schoolYearID);

        if ($report->rowCount() > 0) {
            // report exists so check contents
            $row = $report->fetch();
            $subjectID = $row['subjectID']; // find ID of this student's report
            $comment = $row['subjectComment'];
            if (strlen($comment) > 0) {
                $count = 1; // this only shows something has been written
            }
            // check number of grades
            $data = array(
                'studentID' => $studentID,
                'subjectID' => $subjectID,
                'reportID' => $this->reportID
            );
            $sql = "SELECT gradeID
                FROM arrReportGrade
                LEFT JOIN arrCriteria
                ON arrCriteria.criteriaID = arrReportGrade.criteriaID
                WHERE subjectID = :subjectID
                AND studentID = :studentID
                AND reportID = :reportID
                AND gradeID > 0";
            //print $sql;
            //print_r($data);
            $rs = $connection2->prepare($sql);
            $rs->execute($data);
            $count = $count + $rs->rowCount();
        }

        // return colour to show for name
        if ($count == $numcrit) {
            // everything present
            $complete = "blue";
        } else {
            if ($count > 0) {
                // partially complete
                $complete = "green";
            } else {
                // nothing started
                $complete = "red";
            }
        }
        return 'color:'.$complete;
    }
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    function studentLink($guid, $connection2, $name, $studentID) {
        // links on class list to select different student
        $page = $_SESSION[$guid]['address'];
        if ($this->view == $this->studView) {
            $link = $_SESSION[$guid]['absoluteURL']."/index.php?q=".$page.
            "&amp;studentID=".$studentID.
            "&amp;view=".$this->studView.
            "&amp;showLeft=".$this->showLeft.
            "&amp;classID=".$this->classID.
            "&amp;reportID=".$this->reportID.
            "&amp;schoolYearID=".$this->schoolYearID.
            "&amp;teacherID=".$this->teacherID;
            $click = "if (checkForEdit('status') == true) location.href ='$link'";
        } else {
            $link = "#".$studentID;
            $click = '';
        }

        // check completeness of report
        $complete = $this->reportComplete($connection2, $studentID);

        // show link
        echo "<div class = 'studlist'>";
            if ($this->view == $this->studView) {
                // student view.  check that no changes have been made before moving to a new student
                ?>
                <a href = "#" style = "<?php echo $complete ?>;" onclick = "<?php echo $click ?>">
                <?php
            } else {
                // class view, just move to selected student
                ?>
                <a href = "<?php echo $link ?>" style="<?php echo $complete ?>">
                <?php
            }
            echo $name;
            echo "</a>";
        echo "</div>";
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function reportForm($guid, $connection2, $studentID) {
        // the actual form to fill in when writing reports
        $studentName = getStudentName($connection2, $studentID); // id has already been passed to this function so just make the name

        $fldStudentID    = "student".$studentID; // store the student's ID

        echo "<div class = 'studentname'>"; // show the name
            echo "&nbsp;<a name = '$studentID'>".$studentName."</a>";
            echo "<input type = 'hidden' name = '$fldStudentID' value = '$studentID' />"; // pass the student id
        echo "</div>";

        // all other subject reports
        // set field names
        $fldStudentID    = "student".$studentID;
        $fldComment      = "comment".$studentID;
        $charBarID        = "charBar".$studentID; // used for displaying character count
        $numCharID        = "numChar".$studentID; // used for displaying character count

        // read report
        $report = readSubReport($connection2, $studentID, $this->subjectID, $this->reportID); // get the student's report
        $row = $report->fetch(); // read the report
        $reportSubjectID = $row['reportSubjectID'];
        $comment = $row['subjectComment']; // get the comment

        echo "<div style = 'float:left;'>"; // the report
            echo "<div class='reportbox'>";
                if ($this->criteriaList->rowCount() > 0) {

                    // there are criteria for this class
                    $criteriagradelist = readCriteriaGrade($connection2, $studentID, $this->subjectID, $this->reportID);
                    echo "<table>";
                        echo "<tr>";
                            echo "<th style='width:300px;'>Criteria</th>";
                            echo "<th style='width:50px;'>Mark</th>";
                            echo "<th style='width:50px;'>Percent</th>";
                            echo "<th style='width:50px;'>Grade</th>";
                        echo "</tr>";

                        while ($row = $criteriagradelist->fetch()) {
                            $fldID = "crit".$studentID.'_'.$row['criteriaID'];
                            $markID = "mark".$studentID.'_'.$row['criteriaID'];
                            $percentID = "percent".$studentID.'_'.$row['criteriaID'];
                            echo "<tr>";
                                echo "<td>".$row['criteriaName']."</td>";
                                echo "<td>";
                                    echo "<input type='text' name='$markID' value='".$row['mark']."' size='5' />";
                                echo "</td>";
                                echo "<td>";
                                    echo "<input type='text' name='$percentID' value='".$row['percent']."' size='5' />";
                                echo "</td>";
                                echo "<td>";
                                    $this->selectGrade($fldID, $row['gradeID']);
                                echo "</td>";
                            echo "</tr>";
                        }

                    echo "</table>";
                }
            echo "</div>";
            //showComment($fldComment, $comment, $charBarID, $maxChar, $numCharID, $enabledState)
            
            showComment($fldComment, $comment, $charBarID, $this->maxChar, $numCharID, $this->numRows, $this->numCols, $this->enabledState);
        echo "</div>";

        echo "<div style = 'float:left;width:10px;'>&nbsp;</div>"; // spacer between report and photo
        showPhoto($guid, $connection2, $studentID);
        echo "<div style = 'clear:both;'></div>";

        echo "<div class = 'smalltext'>";
            if ($this->repAccess) {
                echo "<input type = 'submit' name = 'subsubmit' class='submit' value = 'Save' />";
            }
            if ($this->view == $this->classView) {
                echo "&nbsp;<a href = '#top'>Top</a>&nbsp;|&nbsp;<a href = '#bottom'>Bottom</a>";
            }
        echo "</div>";
        echo "<div>&nbsp;</div>";

        if ($this->view == $this->classView) echo "<hr />";
    }
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    function showReport($guid, $connection2) {
        // show all selected reports
        $status = $this->getStatus();
        $this->showStatus($status, "Subject"); // heading with status to show state of report - editing, saved

        $class = "studbox";

        echo "<div class = ''>";
            echo "<div><a name = 'top' style = 'color:white;'>&nbsp;</a></div>";

            echo "<form name = 'subreport' method = 'post' action = ''>";
                echo "<input type = 'hidden' name = 'address'      value = '".$_SESSION[$guid]['address']."' />";
                echo "<input type = 'hidden' name = 'teacherID'    value = '$this->teacherID'    />";
                echo "<input type = 'hidden' name = 'classID'      value = '$this->classID'    />";
                echo "<input type = 'hidden' name = 'reportID'     value = '$this->reportID'    />";
                echo "<input type = 'hidden' name = 'schoolYearID' value = '$this->schoolYearID' />";
                echo "<input type = 'hidden' name = 'view'         value = '$this->view'         />";
                echo "<input type = 'hidden' name = 'maxGrade'     value = '$this->maxGrade'     />";

                if ($this->view == $this->classView) { // class view so go through each student in turn
                    //$i = 1; // counter for making field names
                    $classList = $this->classList;
                    $classList->execute();
                    while ($row = $classList->fetch()) { // for each student
                        // create form for data input
                        $this->reportForm($guid, $connection2, $row['gibbonPersonID']);
                    }
                } else {
                    // student view so only show record for one student
                    if ($this->studentID > 0) {
                        // create form for data input
                        $this->reportForm($guid, $connection2, $this->studentID);
                    }
                }
            echo "</form>";

            if ($this->view == $this->classView) {
                // in class view put a marker so user can link to bottom of page
                echo "<div><a name = 'bottom' style = 'color:white;'>&nbsp;</a></div>";
            }
        echo "</div>";
    }
    ////////////////////////////////////////////////////////////////////////////
    
    ////////////////////////////////////////////////////////////////////////////////
    function readTeacherList($connection2) {
        // read list of staff to be displayed
        try {
            $sql = "SELECT gibbonStaff.gibbonPersonID, surname, preferredName
                FROM gibbonStaff
                INNER JOIN gibbonPerson
                ON gibbonStaff.gibbonPersonID = gibbonPerson.gibbonPersonID
                WHERE status = 'Full'
                ORDER BY surname, preferredName";
            //print $sql;
            //print_r($data);
            $rs = $connection2->prepare($sql);
            $rs->execute();
            return $rs;
        } catch(PDOException $e) {
            print "<div>" . $e->getMessage() . "</div>" ;
        }
    }
    ////////////////////////////////////////////////////////////////////////////////
    
    ////////////////////////////////////////////////////////////////////////////////
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
}
?>
