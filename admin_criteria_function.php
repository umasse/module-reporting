<?php

/*
 * Project:
 * Author:   Andy Statham
 * Date:
 */
class crit {

    var $class;
    var $msg;

    function crit($guid, $connection2) {

        $this->schoolYearID = getSchoolYearID($connection2, $schoolYearName, $currentYear);

        $this->yearGroupID = getYearGroupID();
        $this->subjectID = $this->getSubjectID();
        $this->criteriaID = $this->getCriteriaID();

        // check if add, edit or delete is required
        $this->mode = getMode();

        if (isset($_POST['save'])) {
            $ok = $this->save($connection2);
            setStatus($ok, 'Save', $this->msg, $this->class);
            if ($ok) {
                $this->criteriaID = '';
                $this->mode = '';
            }
        }

        if (isset($_POST['cancel'])) {
            $this->criteriaID = '';
            $this->mode = '';
        }

        if ($this->mode == 'delete') {
            $ok = $this->delete($connection2);
            setStatus($ok, 'Save', $this->msg, $this->class);
            if ($ok) {
                $this->criteriaID = '';
                $this->mode = '';
            }
        }
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function getSubjectID() {
        $subjectID = '';
        if (isset($_POST['subjectID'])) {
            $subjectID = $_POST['subjectID'];
        } else {
            if (isset($_GET['subjectID'])) {
               $subjectID = $_GET['subjectID'];
            }
        }
        return $subjectID;
    }

    function getCriteriaID() {
        $criteriaID = '';
        if (isset($_POST['criteriaID'])) {
            $criteriaID = $_POST['criteriaID'];
        } else {
            if (isset($_GET['criteriaID'])) {
               $criteriaID = $_GET['criteriaID'];
            }
        }
        return $criteriaID;
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function readCriteriaList($connection2) {
        // read list of criteria associated with a course/subject
        $data = array("subjectID" => $this->subjectID);
        $sql = "SELECT *
            FROM arrCriteria
            WHERE subjectID = :subjectID
            ORDER BY criteriaOrder";
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        return $rs;
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function delete($connection2) {
        $data = array('criteriaID' => $this->criteriaID);
        $sql = "DELETE FROM arrCriteria
            WHERE criteriaID = :criteriaID";
        $rs = $connection2->prepare($sql);
        $ok = $rs->execute($data);
        return $ok;
    }

    function save($connection2) {
        $criteriaName = $_POST['criteriaName'];
        $criteriaOrder = $_POST['criteriaOrder'];

        $data = array(
            'criteriaName' => $criteriaName,
            'criteriaOrder' => $criteriaOrder
        );
        $set = "SET criteriaName = :criteriaName,
            criteriaOrder = :criteriaOrder";
        if ($this->criteriaID > 0) {
            $data['criteriaID'] = $this->criteriaID;
            $sql = "UPDATE arrCriteria $set WHERE criteriaID = :criteriaID";
        } else {
            $data['subjectID'] = $this->subjectID;
            $set .= ", subjectID = :subjectID";
            $sql = "INSERT INTO arrCriteria $set";
        }
        $rs = $connection2->prepare($sql);
        $ok = $rs->execute($data);
        return $ok;
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function formCriteria() {
        ?>
        <tr>
            <td>
                <input type='text' name='criteriaName' value='<?php echo $this->criteriaName ?>' size='40' />
            </td>
            <td style='text-align:center'>
                <input type='text' name='criteriaOrder' value='<?php echo $this->criteriaOrder ?>' size='3' />
            </td>
            <td>
                <input type='submit' name='save' value='Save' />
                <input type='submit' name='cancel' value='Cancel' />
            </td>
        </tr>
        <?php
    }

    function mainform($guid, $connection2) {
        if ($this->yearGroupID > 0 && $this->subjectID > 0) {
            $linkPath = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]["module"].'/admin_criteria.php';
            $linkNew = $linkPath.
                    "&amp;subjectID=".$this->subjectID.
                    "&amp;yearGroupID=".$this->yearGroupID.
                    "&amp;schoolYearID=".$this->schoolYearID.
                    "&amp;mode=new";
            $criteriaList = $this->readCriteriaList($connection2);
            ?>
            <div>&nbsp;</div>
            <form name='frm_define' method='post' action=''>
                <input type='hidden' name='criteriaID' value='<?php echo $this->criteriaID ?>' />
                <input type='hidden' name='subjectID' value='<?php echo $this->subjectID ?>' />
                <input type='hidden' name='yearGroupID' value='<?php echo $this->yearGroupID ?>' />
                <input type='hidden' name='schoolYearID' value='<?php echo $this->schoolYearID ?>' />
                <p><a href='<?php echo $linkNew ?>'>Add new</a></p>
                <table class='mini' style='width:100%' id='critTable'>
                    <thead>
                        <tr>
                            <th style='width:55%;'>Criteria</th>
                            <th style='width:20%'>Order</th>
                            <th style='width:25%;'>Action</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                    <?php
                    if ($criteriaList->rowCount() == 0 || $this->mode == 'new') {
                        $this->criteriaName = '';
                        $this->criteriaOrder = '';
                        $this->formCriteria();
                    }
                    while ($row = $criteriaList->fetch()) {
                        if ($this->criteriaID == $row['criteriaID']) {
                            $this->criteriaName = $row['criteriaName'];
                            $this->criteriaOrder = $row['criteriaOrder'];
                            $this->formCriteria();
                        } else {
                            $linkEdit = $linkPath.
                                "&amp;criteriaID=".$row['criteriaID'].
                                "&amp;subjectID=".$this->subjectID.
                                "&amp;yearGroupID=".$this->yearGroupID.
                                "&amp;schoolYearID=".$this->schoolYearID.
                                "&amp;mode=edit";
                            $messageDelete = "WARNING All grades associated with this criterion will be lost.  Delete ".$row['criteriaName']."?";
                            $linkDelete = "window.location = \"$linkPath&amp;criteriaID=".$row['criteriaID'].
                                "&amp;subjectID=".$this->subjectID.
                                "&amp;yearGroupID=".$this->yearGroupID.
                                "&amp;schoolYearID=".$this->schoolYearID.
                                "&amp;mode=delete\"";
                            ?>
                            <tr class='crititem'>
                                <td><?php echo $row['criteriaName'] ?></td>
                                <td style='text-align:center'><?php echo $row['criteriaOrder'] ?></td>
                                <td style='text-align:center'>
                                    <a href='<?php echo $linkEdit ?>'>Edit</a> <a href='#' onclick='if (confirm("<?php echo $messageDelete ?>")) <?php echo $linkDelete ?>'>Delete</a>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </form>
            <?php
        }
        ?>
        <?php
    }
    ////////////////////////////////////////////////////////////////////////////
    
    ////////////////////////////////////////////////////////////////////////////////
    function choose_subject($connection2) {
        // select subject
        ob_start();
        if ($this->yearGroupID > 0) {
            $subjectList = $this->readSubjectlist($connection2);
            ?>
            <div style = "padding:2px;">
                <div style="float:left;width:30%" class = "smalltext">Subject</div>
                <div style="float:left;width:70%">
                    <form name='frm_subject' method='post' action='' style="display:inline">
                        <input type='hidden' name='yearGroupID' value='<?php echo $this->yearGroupID ?>' />
                        <input type='hidden' name='schoolYearID' value='<?php echo $this->schoolYearID ?>' />
                        <select name='subjectID' style="width:95%" onchange="this.form.submit()">
                            <option></option>
                            <?php
                            if ($subjectList->rowCount() > 0) {
                                while ($row = $subjectList->fetch()) {
                                    $subjectName = trimCourseName($row['subjectName']);
                                    ?>
                                    <option value="<?php echo $row['subjectID'] ?>"
                                            <?php if ($this->subjectID == $row['subjectID'])
                                                    echo "selected='selected'" ?>>
                                        <?php echo $subjectName ?>
                                    </option>
                                    <?php
                                }
                            }
                            ?>
                        </select>
                    </form>
                </div>
                <div style="clear:both"></div>
            </div>
            <?php
        }
        return ob_get_clean();
    }
    ////////////////////////////////////////////////////////////////////////////////
    
    ////////////////////////////////////////////////////////////////////////////////
    function readSubjectlist($connection2) {
        $data = array(
            "schoolYearID" => $this->schoolYearID,
            "yearGroupID" => '%'.$this->yearGroupID.'%'
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
}
?>