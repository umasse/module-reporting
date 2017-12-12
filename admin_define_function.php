<?php

/*
 * Project:
 * Author:   Andy Statham
 * Date:
 */
class def {

    var $schoolYearID;
    var $class;
    var $msg;

    function def($guid, $connection2) {
        // get value of selected year
        $this->schoolYearID = getSchoolYearID($connection2, $schoolYearName, $currentYear);

        // check if reportID has been passed to page
        $this->reportID = getReportID();

        // check if add, edit or delete is required
        $this->mode = getMode();

        if (isset($_POST['save'])) {
            $ok = $this->save($connection2);
            setStatus($ok, 'Save', $this->msg, $this->class);
            if ($ok) {
                $this->reportID = '';
                $this->mode = '';
            }
        }

        if (isset($_POST['cancel'])) {
            $this->reportID = '';
            $this->mode = '';
        }

        if ($this->mode == 'delete') {
            $ok = $this->delete($connection2);
            setStatus($ok, 'Save', $this->msg, $this->class);
            if ($ok) {
                $this->reportID = '';
                $this->mode = '';
            }
        }
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function delete($connection2) {
        $data = array("reportID" => $this->reportID);
        $sql = "DELETE FROM arrReport
            WHERE reportID = :reportID";
        $rs = $connection2->prepare($sql);
        $ok = $rs->execute($data);
        return $rs;
    }

    function save($connection2) {
        $reportName = $_POST['reportName'];
        $reportNum = $_POST['reportNum'];
        $gradeScale = $_POST['gradeScale'];
        
        // check values are valid
        $ok = true;

        // report name can't be blank
        if ($reportName == '') {
            $ok = false;
        }

        if ($ok) {
            $data = array(
                "reportName" => $reportName,
                "reportNum" => $reportNum,
                "gradeScale" => $gradeScale,
            );

            // values to update
            $set = "SET reportName = :reportName,
                reportNum = :reportNum,
                gradeScale = :gradeScale";

            if ($this->reportID > 0) {
                // already exists so update
                $data['reportID'] = $this->reportID;
                $sql = "UPDATE arrReport $set WHERE reportID = :reportID";
            } else {
                // new one so insert it
                $data['schoolYearID'] = $this->schoolYearID;
                $set .= ", schoolYearID = :schoolYearID";
                $sql = "INSERT IGNORE INTO arrReport $set";
            }
            //print $sql;
            //print_r($data);
            $rs = $connection2->prepare($sql);
            $ok = $rs->execute($data);
        }
        return $ok;
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function formDefine() {        
        ?>
        <tr>
            <td style='text-align:center'>
                <input type='text' name='reportName' value='<?php echo $this->reportName ?>' size='30' style="width:90%;" />
            </td>
            <td style='text-align:center'>
                <select name='reportNum'>
                    <?php
                    for ($term=1; $term<=$_SESSION['max_term']; $term++) {
                        ?>
                        <option value='<?php echo $term ?>'
                                <?php if ($term == $this->reportNum)
                                    echo "selected='selected'"; ?>>
                            <?php echo $term ?>
                        </option>
                        <?php
                    }
                    ?>
                </select>
            </td>
            <td>
                <select name='gradeScale'>
                    <?php
                    while ($row = $this->gradeScaleList->fetch()) {
                        ?>
                        <option value='<?php echo $row['gibbonScaleID'] ?>'
                            <?php if ($row['gibbonScaleID'] == $this->gradeScale)
                                echo "selected='selected'"; ?>>
                            <?php echo $row['nameShort'].' ('.$row['usage'].')' ?>
                        </option>
                        <?php
                    }
                    ?>
                </select>
            </td>
            <td style='text-align:center'>
                <input type='submit' name='save' value='Save' />
                <input type='submit' name='cancel' value='Cancel' />
            </td>
        </tr>
        <?php
    }

    function mainform($guid, $connection2) {
        $linkPath = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]["module"].'/admin_define.php';
        $linkNew = $linkPath."&amp;mode=new";
        $this->gradeScaleList = readGradeScaleList($connection2, $this->schoolYearID);
        ?>
        <form name='frm_define' method='post' action=''>
            <input type='hidden' name='reportID' value='<?php echo $this->reportID ?>' />
            <p><a href='<?php echo $linkNew ?>'>Add new</a></p>
            <table class='mini' style='width:100%'>
                <tr>
                    <th style='width:25%;'>Report Name</th>
                    <th style='width:10%'>Term</th>
                    <th style='width:35%'>Grade Scale</th>
                    <th style='width:20%'>Action</th>
                </tr>

                <?php
                // read list of reports for selected year
                $rs = readReport($connection2, $this->schoolYearID);
                if ($rs->rowCount() == 0 || $this->mode == 'new') {
                    $this->reportID = '';
                    $this->reportName = '';
                    $this->reportNum = '';
                    $this->gradeScale = '';
                    $this->formDefine();
                }

                while ($row = $rs->fetch()) {
                    if ($this->reportID == $row['reportID']) {
                        $this->reportName = $row['reportName'];
                        $this->reportNum = $row['reportNum'];
                        $this->gradeScale = $row['gradeScale'];
                        $this->formDefine();
                    } else {
                        $linkEdit = $linkPath.
                                "&amp;reportID=".$row['reportID'].
                                "&amp;mode=edit";
                        $messageDelete = "WARNING All reports associated with this will be lost.  Delete ".$row['reportName']."?";
                        $linkDelete = "window.location = \"$linkPath&amp;reportID=".$row['reportID'].
                                "&amp;mode=delete\"";
                        ?>
                        <tr>
                            <td><?php echo $row['reportName'] ?></td>
                            <td style='text-align:center'><?php echo $row['reportNum'] ?></td>
                            <td><?php echo $row['nameShort'].' ('.$row['usage'].')' ?></td>
                            <td style='text-align:center'>
                                <a href='<?php echo $linkEdit ?>'>Edit</a> <a href='#' onclick='if (confirm("<?php echo $messageDelete ?>")) <?php echo $linkDelete ?>'>Delete</a>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </table>
        </form>
        <?php
    }
    ////////////////////////////////////////////////////////////////////////////
}
?>
