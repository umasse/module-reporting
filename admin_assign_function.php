<?php

/*
 * Project:
 * Author:   Andy Statham
 * Date:
 */
class ass {

    var $class;
    var $msg;

    function ass($guid, $connection2) {
        // get value of selected year
        $this->schoolYearID = getSchoolYearID($connection2, $schoolYearName, $currentYear);

        $this->replist = readReport($connection2, $this->schoolYearID);
        $this->yearGroupList = readYeargroup($connection2);

        if (isset($_POST['save'])) {
            $ok = $this->save($connection2);
            setStatus($ok, 'Save', $this->msg, $this->class);
        }

    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function save($connection2) {
        $repList = $this->replist;
        $yearGroupList = $this->yearGroupList;
        $ok = true;
        $repList->execute();
        while ($row_report = $repList->fetch()) {
            $yearGroupList->execute();
            while ($row_yeargroup = $yearGroupList->fetch()) {
                $fld_id = "yg".$row_yeargroup['gibbonYearGroupID'].'rep'.$row_report['reportID'];
                $status = 0;
                if (isset($_POST[$fld_id])) {
                    $status = $_POST[$fld_id];
                }

                // check if there is an entry
                $data = array(
                    "yearGroupID" => $row_yeargroup['gibbonYearGroupID'],
                    "reportID" => $row_report['reportID'],
                    "schoolYearID" => $this->schoolYearID
                );
                $sql = "SELECT reportAssignID
                    FROM arrReportAssign
                    WHERE yearGroupID = :yearGroupID
                    AND reportID = :reportID
                    AND schoolYearID = :schoolYearID";
                $rs = $connection2->prepare($sql);
                $rs->execute($data);

                if ($rs->rowCount() > 0) {
                    // already exists so update
                    $row = $rs->fetch();
                    //$reportAssignID = $row['arrReportAssignID'];
                    $data = array(
                        "reportAssignID" => $row['reportAssignID'],
                        "assignStatus" => $status
                    );
                    $sql = "UPDATE arrReportAssign
                        SET assignStatus = :assignStatus
                        WHERE reportAssignID = :reportAssignID";
                } else {
                    // new record
                    $data = array(
                        "yearGroupID" => $row_yeargroup['gibbonYearGroupID'],
                        "reportID" => $row_report['reportID'],
                        "assignStatus" => $status,
                        "schoolYearID" => $this->schoolYearID
                    );
                    $sql = "INSERT IGNORE INTO arrReportAssign
                        SET yearGroupID = :yearGroupID,
                        reportID = :reportID,
                        assignStatus = :assignStatus,
                        schoolYearID = :schoolYearID";
                }
                $rs = $connection2->prepare($sql);
                $result = $rs->execute($data);
                if (!$result) {
                    $ok = false;
                }
            }
        }
        return $ok;
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function mainform($guid, $connection2) {
        $repList = $this->replist;
        $yearGroupList = $this->yearGroupList;
        $repList->execute();
        $yearGroupList->execute();
        ?>
        <form name='frm_status' method='post' action=''>
            <table class='mini'>
                <tr>
                    <th style='width:150px;'>Report</th>
                    <?php
                    while ($row_yeargroup = $yearGroupList->fetch()) {
                        ?>
                        <th style='width:50px;'><?php echo $row_yeargroup['nameShort'] ?></td>
                        <?php
                    }
                    ?>
                </tr>
                <?php
                while ($row_report = $repList->fetch()) {
                    ?>
                    <tr>
                        <td><?php echo $row_report['reportName'] ?></td>
                        <?php
                        $yearGroupList->execute();
                        while ($row_yeargroup = $yearGroupList->fetch()) {
                            $fldID = "yg".$row_yeargroup['gibbonYearGroupID'].'rep'.$row_report['reportID'];
                            $status = $this->readAssignStatus($connection2, $row_report['reportID'], $row_yeargroup['gibbonYearGroupID']);
                            if ($status == 1) {
                                $checked = "checked='checked'";
                            } else {
                                $checked = '';
                            }
                            ?>
                            <td style='width:50px;text-align:center'>
                                <input type='checkbox' name='<?php echo $fldID ?>' value='1' <?php echo $checked ?> />
                            </td>
                            <?php
                        }
                        ?>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <input type='submit' name='save' value='Save' />
        </form>
        <?php
    }
    ////////////////////////////////////////////////////////////////////////////
    
    ////////////////////////////////////////////////////////////////////////////////
    function readAssignStatus($connection2, $reportID, $yearGroupID) {
        // check if report has been assign to yeargroup
        $data = array(
            'reportID' => $reportID,
            'yearGroupID' => $yearGroupID
        );
        $sql = "SELECT *
            FROM arrReportAssign
            WHERE reportID = :reportID
            AND yearGroupID = :yearGroupID";
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        $assignStatus = false;
        if ($rs->rowCount() > 0) {
            $row = $rs->fetch();
            $assignStatus = $row['assignStatus'];
        }
        return $assignStatus;
    }
    ////////////////////////////////////////////////////////////////////////////////

}
?>
