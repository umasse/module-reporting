<?php

/*
 * Project:
 * Author:   Andy Statham
 * Date:
 */
class acc {

    var $class;
    var $msg;

    function acc($guid, $connection2) {
        // get value of selected year
        $this->schoolYearID = getSchoolYearID($connection2, $schoolYearName, $currentYear);

        $this->yearGroupList = readYeargroup($connection2);
        $this->reportList = readReport($connection2, $this->schoolYearID);
        $this->roleList = $this->read_roleList($connection2);

        // make sure each role has an entry in the assign list
        while ($row = $this->reportList->fetch()) {
            $this->roleList->execute();
            while ($row2 = $this->roleList->fetch()) {
                // set all roles to false for each report
                $data = array(
                    'reportID' => $row['reportID'],
                    'roleID' => $row2['roleID']
                );
                $sql = "INSERT IGNORE INTO arrStatus
                    SET reportID = :reportID,
                    roleID = :roleID,
                    reportStatus = 0";
                $rs = $connection2->prepare($sql);
                $rs->execute($data);
            }
        }

        // save any changes
        if (isset($_POST['save'])) {
            $ok = $this->save($connection2);
            setStatus($ok, 'Save', $this->msg, $this->class);
        }
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function readReport($connection2, $yearGroupID, $term) {
        $data = array(
            'yearGroupID' => $yearGroupID,
            'schoolYearID' => $this->schoolYearID,
            'reportNum' => $term
        );
        $sql = "SELECT *
            FROM arrReportAssign
            INNER JOIN arrReport
            ON arrReportAssign.reportID = arrReport.reportID
            WHERE arrReportAssign.schoolYearID = :schoolYearID
            AND yearGroupID = :yearGroupID
            AND reportNum = :reportNum
            AND assignStatus = 1";
        //print $sql."<br>";
        //print_r($data);
        //print"<br>";
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        if ($rs->rowCount() > 0) {
            $row = $rs->fetch();
            $staff = $row['teacherOpen'];
        } else {
            $staff = 'X';
        }
        return $staff;
    }

    function readReportID($connection2, $yearGroupID, $term) {
        $data = array(
            'yearGroupID' => $yearGroupID,
            'schoolYearID' => $this->schoolYearID,
            'reportNum' => $term
        );
        $sql = "SELECT *
            FROM arrReportAssign
            INNER JOIN arrReport
            ON arrReportAssign.reportID = arrReport.reportID
            WHERE arrReportAssign.schoolYearID = :schoolYearID
            AND yearGroupID = :yearGroupID
            AND reportNum = :reportNum
            AND assignStatus = 1";
        //print $sql."<br>";
        //print_r($data);
        //print"<br>";
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        $reportAssignID = 0;
        if ($rs->rowCount() > 0) {
            $row = $rs->fetch();
            $reportAssignID = $row['reportAssignID'];
        }
        return $reportAssignID;
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function read_roleList($connection2) {
        $sql = "SELECT gibbonRoleID AS roleID, name AS roleName
            FROM gibbonRole
            WHERE category = 'Staff'
            ORDER BY name";
        $rs = $connection2->prepare($sql);
        $rs->execute();
        return $rs;
    }
    ////////////////////////////////////////////////////////////////////////////
    
    ////////////////////////////////////////////////////////////////////////////
    function save($connection2) {
        // save status for each report for each role
        $reportList = $this->reportList;
        $reportList->execute();
        
        // set everyone to 0 for this report
        while ($row = $reportList->fetch()) {
            $data = array(
                'reportID' => $row['reportID']
            );
            $sql = "UPDATE arrStatus
                SET reportStatus = 0
                WHERE reportID = :reportID";
            $rs = $connection2->prepare($sql);
            $rs->execute($data);
        }
        
        $ok = true;
        foreach($_POST AS $key => $value) {
            $pos = strpos($key, "_");
            $reportID = intval(substr($key, 6, ($pos-6)));
            $roleID = intval(substr($key, $pos+1));
            $data = array(
                'reportID' => $reportID,
                'roleID' => $roleID
            );
            $sql = "UPDATE arrStatus
                SET reportStatus = 1
                WHERE reportID = :reportID
                AND roleID = :roleID";
            $rs = $connection2->prepare($sql);
            $result = $rs->execute($data);
            if (!$result) {
                $ok = $result;
            }
        }
        return $ok;
    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function mainform($guid, $connection2) {
        
        $roleList = $this->roleList;
        $roleList->execute();
        $reportList = $this->reportList;
        $reportList->execute();
        ?>
        <form name="frm_access" method="post" action="">
            <table class='mini'>
                <tr>
                    <th style='width:100px;'>Report</th>
                    <?php
                    while ($row = $roleList->fetch()) {
                        echo "<th>".substr($row['roleName'], 0, 8)."</th>";
                    }
                    ?>
                </tr>
                <?php
                while ($row = $reportList->fetch()) {
                    // read status for each role for this report
                    echo "<tr>";
                        echo "<td style='width:150px;'>".substr($row['reportName'], 0, 20)."</td>";
                        $roleList->execute();
                        while ($row2 = $roleList->fetch()) {
                            $status = $this->read_status($connection2, $row['reportID'], intval($row2['roleID']));
                            $id = "status".$row['reportID'].'_'.$row2['roleID'];
                            $checked = '';
                            if ($status) {
                                $checked = 'checked';
                            }
                            echo "<td>";
                                echo "<input type='checkbox' name='$id' id='$id' $checked />";
                            echo "</td>";
                        }
                    echo "</tr>";
                }
                /*
                while ($row = $yearGroupList->fetch()) {
                    $staff1 = $this->readReport($connection2, $row['gibbonYearGroupID'], 1);
                    $staff2 = $this->readReport($connection2, $row['gibbonYearGroupID'], 2);

                    $staffID1 = "staff1".$row['gibbonYearGroupID'];
                    $staffID2 = "staff2".$row['gibbonYearGroupID'];

                    $enabled1 = '';
                    if ($staff1 == 'X') {
                        $enabled1 = "disabled='disabled'";
                    }
                    $staffchecked1 = '';
                    if ($staff1 == 'Y') {
                        $staffchecked1 = "checked='checked'";
                    }
                    $enabled2 = '';
                    if ($staff2 == 'X') {
                        $enabled2 = "disabled='disabled'";
                    }
                    $staffchecked2 = '';
                    if ($staff2 == 'Y') {
                        $staffchecked2 = "checked='checked'";
                    }
                    ?>
                    <tr>
                        <td style="text-align:center"><?php echo $row['nameShort']; ?></td>
                        <td style="text-align:center">
                            <input type="checkbox" name="<?php echo $staffID1 ?>" value="Y" <?php echo $enabled1 ?> <?php echo $staffchecked1 ?> />
                        </td>
                        <td style="text-align:center">
                            <input type="checkbox" name="<?php echo $staffID2 ?>" value="Y" <?php echo $enabled2 ?> <?php echo $staffchecked2 ?> />
                        </td>
                    </tr>
                    <?php
                }
                 * 
                 */
                ?>
            </table>
            <input type="submit" name="save" value="Save" />
        </form>
        <?php
    }
    ////////////////////////////////////////////////////////////////////////////
    
    ////////////////////////////////////////////////////////////////////////////////
    function read_status($connection2, $reportID, $roleID) {
        // check if report is assigned to year group
        $data = array(
            'reportID' => $reportID,
            'roleID' => $roleID
        );
        $sql = "SELECT reportStatus
            FROM arrStatus
            WHERE reportID = :reportID
            AND roleID = :roleID";
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        $reportStatus = false;
        if ($rs->rowCount() > 0) {
            $row = $rs->fetch();
            $reportStatus = $row['reportStatus'];
        }
        return $reportStatus;
    }
////////////////////////////////////////////////////////////////////////////////

}
?>
