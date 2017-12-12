<?php
session_start();
include  "../../config.php";
include "../../functions.php";

//New PDO DB connection
try {
    $connection2=new PDO("mysql:host=$databaseServer;
            dbname=$databaseName;
            charset=utf8", $databaseUsername, $databasePassword);
    $connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // reset coding
} catch(PDOException $e) {
    echo $e->getMessage();
}
    
$action = $_POST['action'];

switch($action) {
    case 'load':
        $reportID = $_POST['reportID'];
        $data = array(
            'reportID' => $reportID
        );
        $sql = "SELECT arrReportSection.sectionID, arrReportSection.sectionType, arrReportSectionDetail.sectionContent
            FROM arrReportSection
            LEFT JOIN arrReportSectionDetail
            ON arrReportSectionDetail.sectionID = arrReportSection.sectionID
            WHERE reportID = :reportID
            ORDER BY sectionOrder";
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        $section = $rs->fetchAll();
        $res = array(
            'section' => $section
        );
        echo json_encode($res);
        break;


    case 'save':
        // Loop over each item in the form.
        $reportID = $_POST['reportID'];
        $formData = explode('&', $_POST['formData']);
        $idlist = array();
        $ok = true;
        $numcol = 2;
        
        // get the IDs of all existing sections that need to be kept
        for ($i=0; $i<count($formData)/$numcol; $i++) {
            $rowdata = explode('=', $formData[($i*$numcol)]);
            $sectionID = $rowdata[1];
            $idlist[] = $sectionID;
        }
        
        // remove any sections that have not been sent
        // convert $idlist to string
        if (count($idlist) > 0) {
            $idliststring = implode(',', $idlist);
            while (substr($idliststring, strlen($idliststring)-1, 1) === ',') {
                $idliststring = substr($idliststring, 0, strlen($idliststring) - 1);
            }
            if ($idliststring != '') {
                $sql = "DELETE FROM arrReportSection
                    WHERE sectionID NOT IN ($idliststring);
                    DELETE FROM arrReportSectionDetail
                    WHERE sectionID NOT IN ($idliststring)"; 
                $rs = $connection2->prepare($sql);
                $result = $rs->execute();
                if (!$result) {
                    $ok = $result;
                }
            }
        }
        
        // save changes and new sections
        for ($i=0; $i<count($formData)/$numcol; $i++) {
            $rowdata = explode('=', $formData[($i*$numcol)]);
            $sectionID = $rowdata[1];
            $rowdata = explode('=', $formData[($i*$numcol)+1]);
            $sectionType = $rowdata[1];
            $data = array(
                'sectionType' => $sectionType,
                'sectionOrder' => $i+1
            );
            $set = "SET sectionType = :sectionType, sectionOrder = :sectionOrder";
            if ($sectionID > 0) {
                $data['sectionID'] = $sectionID;
                // update
                $sql = "UPDATE arrReportSection ".$set." WHERE sectionID = :sectionID";
            } else {
                // insert new section
                $data['reportID'] = $reportID;
                $set .= ", reportID = :reportID";
                $sql = "INSERT INTO arrReportSection ".$set;
            }
            $rs = $connection2->prepare($sql);
            $result = $rs->execute($data);
            if (!$result) {
                $ok = $result;
            }
        }
        echo $ok;
        break;

    case 'save_detail':
        $ok = true;
        $sectionID = $_POST['sectionID'];
        $sectionContent = $_POST['sectionContent'];
        $data = array(
            'sectionID' => $sectionID,
            'sectionContent' => $sectionContent
        );
        $sql = "INSERT INTO arrReportSectionDetail
            SET sectionContent = :sectionContent,
            sectionID = :sectionID
            ON DUPLICATE KEY update
            sectionContent = :sectionContent";
        $rs = $connection2->prepare($sql);
        $result = $rs->execute($data);
        if (!$result) {
            $ok = $result;
        }
        echo $ok;
        break;

    case 'report_list':
        $yearID = $_POST['yearID'];
        $data = array(
            'schoolYearID' => $yearID
        );
        $sql = "SELECT *
            FROM arrReport
            WHERE schoolYearID = :schoolYearID";
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
        $report = $rs->fetchAll();
        $res = array(
            'report' => $report
        );
        echo json_encode($res);
        break;
}

?>