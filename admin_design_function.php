<?php

/*
 * Project:
 * Author:   Andy Statham
 * Date:
 */
class des {

    var $schoolYearID;
    var $class;
    var $msg;

    function des($guid, $connection2) {
        // get value of selected year
        $this->schoolYearID = getSchoolYearID($connection2, $schoolYearName, $currentYear);

        // check if reportID has been passed to page
        $this->reportID = getReportID();

    }
    ////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////
    function chooseReport($connection2, $reportID, $schoolYearID) {
        $repList = $this->readReportList($connection2, $schoolYearID);
        $repList->execute();

        ob_start();
        ?>
        <div style = "padding:2px;">
            <?php
            if ($repList->rowCount() > 0) {
                ?>
                <div style = "float:left;width:30%;" class = "smalltext">Report</div>
                <div style = "float:left;">
                    <form name="frm_selectreport" method="post" action="">
                        <input type="hidden" name="schoolYearID" value="<?php echo $schoolYearID ?>" />
                        <select name="reportID" onchange="this.form.submit()">
                            <option></option>
                            <?php
                            while ($row = $repList->fetch()) {
                                $selected = '';
                                if ($reportID == $row['reportID']) {
                                    $selected = 'selected';
                                }
                                echo "<option value='".$row['reportID']."' $selected>";   
                                    echo $row['reportName'];
                                echo "</option>";
                            }
                            ?>
                        </select>
                    </form>
                </div>
                <?php
            } else {
                echo "<div class='smalltext'>No reports created for this year</div>";
            }
            ?>
        </div>
        <?php
        return ob_get_clean();
    }
    ////////////////////////////////////////////////////////////////////////////
    
    ////////////////////////////////////////////////////////////////////////////////
    function readReportList($connection2, $schoolYearID) {
        // read reports available for this year group
        try {
            $data = array(
                'schoolYearID' => $schoolYearID
            );
            $sql = "SELECT arrReport.reportID, reportName
                FROM arrReport
                WHERE arrReport.schoolYearID = :schoolYearID
                ORDER BY reportNum";
            //print $sql;
            //print_r($data);
            $rs  = $connection2->prepare($sql);
            $rs->execute($data);
            return $rs;
        } catch(PDOException $e) {
            print "<div>" . $e->getMessage() . "</div>" ;
        }
    }
    ////////////////////////////////////////////////////////////////////////////
    
    ////////////////////////////////////////////////////////////////////////////
    function mainform($guid, $connection2) {
        ?>
        <input type='hidden' id='reportID' value='<?php echo $this->reportID ?>' />

        <p id='selectReport'></p>

        <p id='sectionTypeList'></p>

        <div id='template'>
            <form id='report_template'>
                <table id='template_table'>
                    <thead></thead>
                    <tbody></tbody>
                </table>
            </form>
        </div>
        <?php
    }
    
    ////////////////////////////////////////////////////////////////////////////
}
?>
