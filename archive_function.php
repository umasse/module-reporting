<?php

/*
 * Project:
 * Author:   Andy Statham
 * Date:
 */
class arc {

    var $class;
    var $msg;

    var $classView;
    var $studView;
    var $view;

    var $schoolYearID;

    function __construct($guid, $connection2) {
        // get value of selected year
        $this->schoolYearName = '';
        $this->schoolYearID = getSchoolYearID($connection2, $this->schoolYearName, $this->currentYearID);

        $this->rollGroupID = getRollGroupID();

        $this->yearGroupID = getYearGroupID();

        $this->reportID = getReportID();

        // check if left students should be shown
        $this->showLeft = getLeft();

        if ($this->rollGroupID > 0) {
            // list of students in roll group
            $this->rollGroupList = readRollGroupList($connection2, $this->rollGroupID, $this->showLeft);
        }
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function readArchiveList($connection2, $studentID) {
        $data = array('studentID' => $studentID);
        $sql = "SELECT arrReport.reportName AS reportName, 
            arrArchive.reportName AS reportLink
            FROM arrArchive
            INNER JOIN arrReport
            ON arrArchive.reportID = arrReport.reportID
            WHERE studentID = :studentID
            ORDER BY reportName";
        //print $sql;
        //print_r($data);
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        return $rs;
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function chooseLeft() {
        // choose whether to show students who have left
        ob_start();
        echo "<div>&nbsp;</div>";
        echo "<div class = 'smalltext'>";
            echo "<form name = 'frm_showleft' method = 'post' action = ''>";
                echo "<input type = 'hidden' name = 'rollGroupID' value = '$this->rollGroupID' />";
                echo "<input type = 'hidden' name = 'reportID' value = '$this->reportID' />";
                echo "<input type = 'hidden' name = 'schoolYearID' value = '$this->schoolYearID' />";
                echo "<input type = 'hidden' name = 'yearGroupID' value = '$this->yearGroupID' />";
                echo "<input type = 'hidden' name = 'studentID' value = '' />";
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
        return ob_get_clean();
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
            <div style = "float:left;width:30%;" class = "smalltext">Class</div>
            <div style = "float:left;">
                <form name="frm_class" method="post" action="">
                    <input type="hidden" name="yearGroupID" value="<?php echo $this->yearGroupID ?>" />
                    <input type="hidden" name="studentID" value="<?php echo $this->studentID ?>" />
                    <select name="rollGroupID" onchange="this.form.submit();">
                        <option></option>
                        <?php
                        while ($row = $rs->fetch()) {
                            ?>
                            <option value="<?php echo $row['gibbonRollGroupID'] ?>"
                                    <?php if ($this->rollGroupID == $row['gibbonRollGroupID'])
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
    ////////////////////////////////////////////////////////////////////////////


    // -------------------------------------------------------------------------
    function showRollGroupList($guid, $connection2) {
        // show classlist with appropriate links and colours
        // this is going in session sidebar so use output buffer
        $rollGroupList = $this->rollGroupList;
        $rollGroupList->execute();
        ob_start();
        if ($this->rollGroupID > 0) { // only do something if a class has been selected
            if ($rollGroupList->rowCount() > 0) { // only worry if there are students in the class
                if ($this->classID != $this->allClass) {
                    $this->changeView($guid); // show link to change between student and class view
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
            $this->chooseLeft($this->showLeft); // show students who have left the class
        }
        return ob_get_clean();
    }
    ////////////////////////////////////////////////////////////////////////////


    ////////////////////////////////////////////////////////////////////////////
    function mainform($guid, $connection2) {

        if ($this->rollGroupID > 0) {
            $processPath = $this->modpath."/pdf_create.php";
            $rollGroupList = $this->rollGroupList;
            $path = 'archive/'.$this->schoolYearName.'/';

            ?>
            <table class='mini'>
                <tr>
                    <th style='width:200px;'>Student</th>
                    <th style='width:400px;'>Files</th>
                </tr>
                <?php
                $c = 0;
                while ($row = $rollGroupList->fetch()) {
                    //$c++;
                    $rowcol = oddEven($c++);
                    ?>
                    <tr class='<?php echo $rowcol ?>'>
                        <td><?php echo $row['surname'],', '.$row['preferredName'] ?></td>
                        <td>
                            <?php
                            $list = $this->readArchiveList($connection2, $row['gibbonPersonID']);
                            $count = 0;
                            while ($row2 = $list->fetch()) {
                                $pos = strpos($row2['reportLink'], '_');
                                $link = $_SESSION[$guid]['absoluteURL'].$_SESSION['archivePath'].
                                        $this->schoolYearName.'/'.
                                        $row2['reportLink'];
                                ?>
                                <a href='<?php echo $link ?>' target='_blank'><?php echo $row2['reportName'] ?></a>
                                <?php
                                $count++;
                                if ($count < $list->rowCount()) {
                                    echo "<br />";
                                }
                            }
                            ?>
                        </td>
                    </tr>

                    <?php
                }
                ?>
            </table>
            <?php
        }
    }
    ////////////////////////////////////////////////////////////////////////////
}
?>
