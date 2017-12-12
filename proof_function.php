<?php

/*
 * Project:
 * Author:   Andy Statham
 * Date:
 */
class proof {

    var $class;
    var $msg;

    var $allClass = 'xxx';
    var $eal = 'eal';
    var $noComment = "No comment written";

    var $classView;
    var $studView;
    var $view;

    var $schoolYearID;
    var $schoolYearName;
    var $classTeacherID;
    var $yearGroupName;

    function __construct($guid, $connection2) {
        // get value of selected year

        $this->classView    = $_SESSION[$guid]['classView'];
        $this->studView     = $_SESSION[$guid]['studView'];
        $this->maxGrade     = $_SESSION[$guid]['maxGrade'];
        $this->repView      = $_SESSION[$guid]['repView'];
        $this->repEdit      = $_SESSION[$guid]['repEdit'];
        
        // check if user is viewing own reports or those of another teacher
        $this->teacherID = getTeacherID($guid);
        
        // check user's role to see if they have access to these reports
        $this->role = $_SESSION[$guid]['gibbonRoleIDCurrent'];
        
        $this->schoolYearID = getSchoolYearID($connection2, $schoolYearName, $this->currentYearID);

        // id of student being viewed
        $this->studentID = getStudentID();

        // check if left students should be shown
        $this->showLeft = getLeft();
        
        $this->rollGroupID = getRollGroupID();

        $this->yearGroupID = getYearGroupID();
        
        if ($this->yearGroupID != '') {
            $data = array('yearGroupID' => $this->yearGroupID);
            $sql = "SELECT name FROM gibbonYearGroup WHERE gibbonYearGroupID = :yearGroupID";
            $rs = $connection2->prepare($sql);
            $rs->execute($data);
            $row = $rs->fetch();
            $this->yearGroupName = $row['name'];
        }

        
        // find maximum length of comment for this class
        //findMaxChar($connection2, $this->classID, $this->courseType, $this->maxChar);
        $this->maxChar = 1000;
        
        // adjust box size for size of comment
        //$this->numRows = intval($this->maxChar/60);
        //$this->numCols = $_SESSION['numCols'];
        $this->numRows = 15;
        $this->numCols = 80;
        
        
        $this->repAccess = 0;
        $this->reportID = getReportID();
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

        // check if a class has been selected
        $this->classID = getClassID();

        if ($this->classID == $this->allClass) {
            $this->view = $this->studView;
        }

        $this->numCols = $_SESSION['numCols'];

        // if class has been selected read the class list
        //$this->classList = $this->readClassList($connection2);

        //$this->rollGroupList = $this->readRollGroupList($connection2);
        $this->rollGroupList = readRollGroupList($connection2, $this->rollGroupID, $this->showLeft);

        if (isset($_POST['subsubmit'])) {
            $ok = $this->save($connection2);
            setStatus($ok, 'Save', $this->msg, $this->class);
        }

    }
    ////////////////////////////////////////////////////////////////////////////
 /*
    // -------------------------------------------------------------------------
    function readRollGroupList($connection2) {
        // return list of students in the selected roll group
        $data = array(
            'rollGroupID' => $this->rollGroupID
        );
        $sql = "SELECT gibbonStudentEnrolment.gibbonPersonID, surname, preferredName
            FROM gibbonStudentEnrolment
            INNER JOIN gibbonPerson
            ON gibbonStudentEnrolment.gibbonPersonID = gibbonPerson.gibbonPersonID
            WHERE gibbonRollGroupID = :rollGroupID ";
            if ($this->showLeft == 0) {
                $sql .= "AND status = 'Full' ";
            }
            $sql .= "ORDER BY surname, firstName";
        //print $sql;
        //print_r($data);
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        return $rs;
    }
    // -------------------------------------------------------------------------
*/

    ////////////////////////////////////////////////////////////////////////////
    function save($connection2) {
        // single student only
        $ok = true;
        
        $subjectID = $_POST['subjectID'];
        $studentID = $_POST['studentID'];
        $reportID = $_POST['reportID'];
        $idtext = 'comtext'.$subjectID.'_'.$studentID;
        $comment = $_POST[$idtext];
            /*
        foreach ($_POST AS $key => $value) {
            if (substr($key,0 , 7) == "comtext") {
                $comment = $value;
            }
        }
        */
        //$comment = $_POST['comment'];
        $data = array(
            'subjectID' => $subjectID,
            'studentID' => $studentID,
            'reportID' => $reportID,
            'comment' => $comment
        );
        $sql = "INSERT INTO arrReportSubject
            SET studentID = :studentID,
            subjectID = :subjectID,
            reportID = :reportID,
            subjectComment = :comment
            ON DUPLICATE KEY UPDATE
            subjectComment = :comment";
        $rs = $connection2->prepare($sql);
        return $rs->execute($data);
    }
    // -------------------------------------------------------------------------

    ////////////////////////////////////////////////////////////////////////////
    function studentLink($guid, $connection2, $name, $studentID) {
        // set links in class list
        $page = $_SESSION[$guid]['address'];
        if ($this->view == $this->studView) {
            $link = $_SESSION[$guid]['absoluteURL']."/index.php?q=".$page.
            "&amp;studentID=".$studentID.
            "&amp;view=".$this->studView.
            "&amp;showLeft=".$this->showLeft.
            "&amp;rollGroupID=".$this->rollGroupID.
            "&amp;reportID=".$this->reportID.
            "&amp;classID=".$this->classID.
            "&amp;yearGroupID=".$this->yearGroupID.
            "&amp;schoolYearID=".$this->schoolYearID;
            $click = "if (checkForEdit('status') == true) location.href ='$link'";
        } else {
            $link = "#".$studentID;
            $click = '';
        }

        // check completeness of report
        //$complete = $thisreportComplete($connection2, $studentID);

        // show link
        echo "<div class = 'studlist'>";
            ?>
            <a href = "#" style = "" onclick = "<?php echo $click ?>">
            <?php
            
            echo $name;
            echo "</a>";
        echo "</div>";
    }
    ////////////////////////////////////////////////////////////////////////////

    
    ////////////////////////////////////////////////////////////////////////////
    function changeView($guid) {
        // change between class and student view
        $path = $_SESSION[$guid]['absoluteURL'];
        $page = $_SESSION[$guid]['address'];
        $link = $path."/index.php?q=".$page;
        if ($this->view == $this->studView) {
            $viewlink = $link."&amp;view=".$this->classView.
                    "&amp;classID=".$this->classID.
                    "&amp;rollGroupID=".$this->rollGroupID.
                    "&amp;reportID=".$this->reportID.
                    "&amp;yearGroupID=".$this->yearGroupID.
                    "&amp;schoolYearID=".$this->schoolYearID;
            $text = "Change to class view";
        } else {
            $viewlink = $link."&amp;view=".$this->studView.
                    "&amp;classID=".$this->classID.
                    "&amp;rollGroupID=".$this->rollGroupID.
                    "&amp;reportID=".$this->reportID.
                    "&amp;yearGroupID=".$this->yearGroupID.
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
    ////////////////////////////////////////////////////////////////////////////

    /*
    ////////////////////////////////////////////////////////////////////////////
    function chooseClass($connection2) {
        // select class from drop down list
        $classList = $this->classList;
        $classList->execute();
        ob_start();
        ?>
        <div style = "padding:2px;">
            <div style = "float:left;width:30%;" class = "smalltext">Subject</div>
            <div style = "float:left;width:70%">
                <form name="frm_subject" method="post" action="">
                    <input type="hidden" name="rollGroupID" value="<?php echo $this->rollGroupID ?>" />
                    <input type="hidden" name="yearGroupID" value="<?php echo $this->yearGroupID ?>" />
                    <input type="hidden" name="schoolYearID" value="<?php echo $this->schoolYearID ?>" />
                    <input type="hidden" name="reportID" value="<?php echo $this->reportID ?>" />
                    <input type="hidden" name="studentID" value="<?php echo $this->studentID ?>" />
                    <input type="hidden" name="view" value="<?php echo $this->view ?>" />
                    <select name="classID" onchange="this.form.submit()" style="width:75%">
                        <option></option>
                        <?php
                        while ($row = $classList->fetch()) {
                            $courseName = trimCourseName($row['courseName']);
                            ?>
                            <option value="<?php echo $row['gibbonCourseClassID'] ?>"
                                    <?php if ($this->classID == $row['gibbonCourseClassID'])
                                        echo "selected='selected'" ?>>
                                <?php echo $courseName.' ('.$row['className'].')' ?>
                            </option>
                            <?php
                        }
                        ?>
                        <option value="eal" <?php if ($this->classID == $this->eal) echo "selected='selected'" ?>>EAL</option>
                        <option value="<?php echo $this->allClass ?>" <?php if ($this->classID == $this->allClass) echo "selected='selected'" ?>>All Subjects</option>
                    </select>
                </form>
            </div>
            <div style="clear:both"></div>
        </div>
        <?php
        return ob_get_clean();
    }
    // -------------------------------------------------------------------------
*/
   
    // -------------------------------------------------------------------------
    function chooseLeft() {
        // choose whether to show students who have left
        echo "<div>&nbsp;</div>";
        echo "<div class = 'smalltext'>";
            echo "<form name = 'frm_showleft' method = 'post' action = ''>";
                echo "<input type = 'hidden' name = 'rollGroupID' value = '$this->rollGroupID' />";
                echo "<input type = 'hidden' name = 'classID' value = '$this->classID' />";
                echo "<input type = 'hidden' name = 'reportID' value = '$this->reportID' />";
                echo "<input type = 'hidden' name = 'schoolYearID' value = '$this->schoolYearID' />";
                echo "<input type = 'hidden' name = 'yearGroupID' value = '$this->yearGroupID' />";
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
    // -------------------------------------------------------------------------

    
    // -------------------------------------------------------------------------
    function chooseRollGroup($connection2) {
        // drop down box to select roll group
        $data = array(
                'schoolYearID' => $this->schoolYearID,
                'yearGroupID' => $this->yearGroupID
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
                    <input type="hidden" name="schoolYearID" value="<?php echo $this->schoolYearID ?>" />
                    <input type="hidden" name="yearGroupID" value="<?php echo $this->yearGroupID ?>" />
                    <input type="hidden" name="classID" value="" />
                    <input type="hidden" name="reportID" value="" />
                    <input type="hidden" name="studentID" value="" />
                    <select name="rollGroupID" onchange="this.form.submit();">
                        <option></option>
                        <?php
                        while ($row = $rs->fetch()) {
                            $selected = '';
                            if ($this->rollGroupID == $row['gibbonRollGroupID']) {
                                $selected = 'selected';
                            }
                            echo "<option value='".$row['gibbonRollGroupID']."' $selected>";
                                echo $row['nameShort'];
                            echo "</option>";
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
    ////////////////////////////////////////////////////////////////////////////
    
    function showComment($fld_comment, $comment, $charBarID, $numCharID) {
	// show comment for edit or display

	//if ($this->repAccess == $this->repEdit) {
            // show number of characters entered and disable save if too long
            show_replength($comment, $this->maxChar, $charBarID, $numCharID);
            ?>
            <div>
            <textarea
                name = "<?php echo $fld_comment ?>"
                rows = "<?php echo $this->numRows ?>"
                cols = "<?php echo $this->numCols ?>"
                onkeyup = "checkEnter(this.value, <?php echo $this->maxChar ?>, 'submit', '<?php echo $numCharID ?>', '<?php echo $charBarID ?>');"
                class = "subtextbox"
                onclick = "not_saved('status');"
                <?php echo $this->enabledState ?>
                ><?php echo $comment; ?></textarea>
            </div>
            <?php
	//} else {
        //    echo "<div class = 'subtextboxnoedit'>".nl2br($comment)."</div>";  // read only so display comment
	//}
    }
    
    // -------------------------------------------------------------------------
    function showRollGroupList($guid, $connection2) {
        // show classlist with appropriate links and colours
        // this is going in session sidebar so use output buffer
        if ($this->reportID > 0) {
            $rollGroupList = $this->rollGroupList;
            $rollGroupList->execute();
            ob_start();
            if ($this->rollGroupID > 0) { // only do something if a class has been selected
                if ($rollGroupList->rowCount() > 0) { // only worry if there are students in the class
                    if ($this->classID != $this->allClass) {
                        //$this->changeView($guid); // show link to change between student and class view
                    }
                    while ($row = $rollGroupList->fetch()) { // for each student in the class
                        $classStudentID = $row['gibbonPersonID']; // read their ID
                        $name = $row['surname'].', '.$row['preferredName']; // name to be shown in list
                        $this->studentLink($guid, $connection2, $name, $classStudentID); // create link and display

                    }
                } else { // no one in the class
                    echo "<div>&nbsp;</div>";
                    echo "<div class = 'smalltext'>No students listed</div>";
                }
                echo "<div>&nbsp;</div>";
                $this->chooseLeft($this->showLeft); // show students who have left the class
            }
            return ob_get_clean();
        }
    }
    ////////////////////////////////////////////////////////////////////////////


    ////////////////////////////////////////////////////////////////////////////
    function showReport($guid, $connection2, $studentID, $schoolYearID) {
        $numRows = intval($this->maxChar/60);
        
        $studentName = getStudentName($connection2, $studentID); // id has already been passed to this function so just make the name
        $fldStudentID    = "student".$studentID; // store the student's ID
        
        echo "<div class='studentname'>$studentName</div>";
        
        // read report
        // 
        
        //$report = readPasReport($connection2, $studentID, $this->reportID); // get the student's report
        $report = readSubReport($connection2, $studentID, 0, $this->reportID);
        $row = $report->fetch(); // read the report
        $reportPastoralID = $row['reportSubjectID'];
        $comment = $row['subjectComment']; // get the comment
        
        echo "<div style = 'float:left;'>"; // the report
            //$this->showComment($fldComment, $comment, $charBarID, $numCharID);
            //showComment($fldComment, $comment, $charBarID, $this->maxChar, $numCharID, $this->numRows, $this->enabledState);
        echo "</div>";
        
        echo "<div style = 'float:left;width:10px;'>&nbsp;</div>"; // spacer between report and photo
            showPhoto($guid, $connection2, $studentID);
        echo "<div style = 'clear:both;'></div>";
        
        // get list of subjects/classes for student
        $sublist = readStudentClassList($connection2, $studentID, $schoolYearID);
        while ($row = $sublist->fetch()) {
            $subjectID = $row['subjectID'];
            $teacherName = $row['teacherName'];
            $subreport = readSubReport($connection2, $studentID, $subjectID, $this->reportID);
            //$criterialist = readCriteriaList($connection2, $subjectID);
            $criterialist = readCriteriaGrade($connection2, $studentID, $subjectID, $this->reportID);
            $row_subject = $subreport->fetch();
            $comment = $row_subject['subjectComment'];
            
            $idedit = 'comedit'.$subjectID.'_'.$studentID;
            $idanchor = 'anchor'.$subjectID.'_'.$studentID;
            $idtext = 'comtext'.$subjectID.'_'.$studentID;
            $idtext2 = 'comtext2'.$subjectID.'_'.$studentID;
            $idshow = 'comshow'.$subjectID.'_'.$studentID;
            $numCharID = 'numCharID'.$subjectID.'_'.$studentID;
            $charBarID = 'charBarID'.$subjectID.'_'.$studentID;
            
            echo "<div class='subjectname'><a name='$idanchor'>".$row['subjectName']."</a></div>";
            echo "<div class='teachername'>$teacherName</div>";
            
            if ($criterialist->rowCount() > 0) {
                echo "<table>";
                    echo "<tr>";
                        echo "<th style='width:300px;'>Criteria</th>";
                        echo "<th style='width:150px;'>Grade</th>";
                    echo "</tr>";

                    while ($row_criteria = $criterialist->fetch()) {
                        echo "<tr>";
                            echo "<td>".$row_criteria['criteriaName']."</td>";
                            echo "<td>";
                                echo findGrade($this->gradeList, $row_criteria['gradeID']);
                            echo "</td>";
                        echo "</tr>";
                    }
                echo "</table>";
            }
                
            echo "<div name='$idedit' id='$idedit' style='display:none' class='idedit'>";
                showRepLength($comment, $this->maxChar, $charBarID, $numCharID);
                echo "<form name='frm_editcom' method='post'>";
                    echo "<input type='hidden' name='reportID' value='$this->reportID' />";
                    echo "<input type='hidden' name='subjectID' value='$subjectID' />";
                    echo "<input type='hidden' name='rollGroupID' value='$this->rollGroupID' />";
                    echo "<input type='hidden' name='schoolYearID' value='$schoolYearID' />";
                    echo "<input type='hidden' name='studentID' value='$studentID' />";
                    echo "<input type='hidden' name='teacherID' value='$this->teacherID' />";
                    echo "<input type='hidden' name='yearGroupID' value='$this->yearGroupID' />";
                    echo "<div>";
                        echo "<textarea
                           name='$idtext'
                           id='$idtext'
                           rows='$numRows'
                           cols='$this->numCols'
                           class='subtextbox'
                           onclick = 'notSaved(\"status\")';
                           onkeyup='setTextValue(\"$idtext\", \"$idtext2\");
                           checkEnter(this.value, \"$this->maxChar\", \"submit\", \"$numCharID\", \"$charBarID\");'>$comment</textarea>";
                    echo "</div>";

                    echo "<div>";
                        echo "<input type='submit' name='subsubmit' class='submit' value='Save' />";
                        echo "<input type='button' name='subcancel' value='Cancel' onclick='showEdit(\"$idshow\", \"$idedit\", \"\");' />";
                    echo "</div>";
                echo "</form>";
            echo "</div>";

            if ($comment == '') {
                $comment = "No comment entered";
            }
            echo "<div class='reportbox idshow smalltext' name='$idshow' id='$idshow'>";
                if ($this->repAccess) {
                    echo "<a href='#' id='$idtext2' onclick='showEdit(\"$idedit\", \"$idshow\", \"$idanchor\");return false;'>";
                        echo nl2br($comment);
                    echo "</a>";
                } else {
                    echo nl2br($comment);
                }
            echo "</div>";
            echo "<div class='reportend'>&nbsp;</div>";
        } // end of subjects
    }
    ////////////////////////////////////////////////////////////////////////////
    
    ////////////////////////////////////////////////////////////////////////////
    function mainform($guid, $connection2) {
        if ($this->rollGroupID != '' && $this->reportID > 0) {
            if ($this->view == $this->studView) {
                if ($this->studentID > 0) {
                    // single student selected
                    $this->showReport($guid, $connection2, $this->studentID, $this->schoolYearID);
                }
            } else {
                $rollGroupList = $this->rollGroupList;
                $rollGroupList->execute();
                while ($row = $rollGroupList->fetch()) {
                    $this->showReport($guid, $connection2, $row['gibbonPersonID'], $this->schoolYearID);
                }
            }
        }
    }
    ////////////////////////////////////////////////////////////////////////////
}
?>
