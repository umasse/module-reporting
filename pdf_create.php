<?php
session_start();
include  "../../config.php";
include "../../functions.php";
include "../Attendance/moduleFunctions.php";

//New PDO DB connection
try {
    $connection2=new PDO("mysql:host=$databaseServer;
            dbname=$databaseName;
            charset=utf8", $databaseUsername, $databasePassword);
    $connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // reset coding
}
catch(PDOException $e) {
    echo $e->getMessage();
}

//if (isActionAccessible($guid, $connection2, "/modules/Reporting/pdf_make.php")==FALSE) {
if (1==2) {
    //Acess denied
    print "<div class='error'>";
    print "You do not have access to this action.";
    print "</div>";
    exit;
} else {

    //include "./pdf_function.php" ;
    include "./function.php";

    //$root = "../..";
    $root = $_SERVER['DOCUMENT_ROOT'];
    require_once $root."/lib/tcpdf-6.2/tcpdf.php";
    include "./pdf_create_function.php";


    setSessionVariables($guid, $connection2);

    $setpdf = new createpdf($guid, $connection2);

    $reportSection = $setpdf->readReportSectionList($connection2);

    // check folder exists
    $setpdf->checkFolder();
    //$path = '../..'.$_SESSION['archivePath'].$setpdf->schoolYearName.'/';
    $path = $_SESSION['archiveFilePath'].$setpdf->schoolYearName.'/';

    // go through class list to see which ones need to be printed
    //$rollGroupList = $setpdf->rollGroupList;
    for ($i=0; $i<count($setpdf->printList); $i++) {
        $setpdf->studentID = $setpdf->printList[$i]['studentID'];
        $studentDetail = $setpdf->readStudentDetail();
        $row = $studentDetail->fetch();
        $setpdf->officialName = $row['officialName'];
        $setpdf->firstName = $row['firstName'];
        $setpdf->preferredName = $row['preferredName'];
        $setpdf->surname = $row['surname'];
        $setpdf->rollOrder = $row['rollOrder'];
        $setpdf->studentEmail = $row['email'];
        if (! empty($row['dateStart']) ) {
            $setpdf->studentDateStart = $row['dateStart'];
        } else {
            $setpdf->studentDateStart = "2000-01-01";
        }
        if (! empty($row['dateEnd']) ) {
            $setpdf->studentDateEnd = $row['dateEnd'];
        } else {
            $setpdf->studentDateEnd = "2099-01-01";
        }
        $setpdf->studentAbrName = str_replace("'", "", $row['surname'].substr($row['firstName'], 0, 1));
        $dob = $row['dob'];
        if ($dob != '' && substr($dob, 0, 4) != '0000') {
            $dob = date('d/m/Y', strtotime($dob));
        } else {
            $dob = '';
        }
        $setpdf->dob = $dob;

        $reportName =
                $setpdf->schoolYearName.'-'.
                $setpdf->yearGroupName.'-'.
                $setpdf->term.'-'.
                $setpdf->studentAbrName.'-'.
                intval($setpdf->studentID).
                ".pdf";
        $fileName = $path.$reportName;

        $debug_dump = false;
        $debug_html = '<html><head></head><body>';

        ////////////////////////////////////////////////////////////////////
        // start pdf file
        ////////////////////////////////////////////////////////////////////
        $pdf = new MYPDF ($setpdf->pageOrientation, 'mm', 'A4', true, 'UTF-8', false);
        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

        // Set PADDING
        $pdf->SetCellPadding(0);
        $tagvs = array(
          'p' => array(
            0 => array('h' => 0.001, 'n' => 0.1),
            1 => array('h' => 0.001, 'n' => 0.001)
          ),
          'li' => array(
            0 => array('h' => 0.01, 'n' => 0.01),
            1 => array('h' => 0.01, 'n' => 0.01)
          )
        );
        $pdf->setHtmlVSpace($tagvs);

        $pdf->SetHeaderMargin($setpdf->topMargin);
        $pdf->SetFooterMargin($setpdf->footerMargin);
        $pdf->loadCustomFonts();
        $pdf->AddPage();


        ////////////////////////////////////////////////////////////////////
        // subject report
        ////////////////////////////////////////////////////////////////////
        $reportSection->execute();
        while ($rowSection = $reportSection->fetch()) {
            $setpdf->sectionID = $rowSection['sectionID'];
            $sectionTypeID = $rowSection['sectionType'];
            $sectionTypeName = $rowSection['sectionTypeName'];
            // Database table arrReportSectionType needs to match this
            // 1	Text
            // 2	Subject (row)
            // 3	Subject (column)
            // 4	Pastoral
            // 5	Page Break
            // 6    Subject (Row-NonEmpty-Att)
            // 7    Attendance (Term)
            //
            // module.js sectionTypeObj needs to match this
            //var sectionTypeObj = {
                //'1': 'Text',
                //'2': 'Subject (row)',
                //'3': 'Subject (column)',
                //'4': 'Pastoral',
                //'5': 'Page Break',
                //'6': 'Subject (Row-NonEmpty-Att)',
                //'7': 'Attendance (Term)'
            //}
            //
            // Methods for each section type need to be created in pdf_create_function.php

            switch ($sectionTypeID) {
                case 1:
                    $debug_html.=$setpdf->textSection($pdf);
                    break;

                case 2:
                    $debug_html.=$setpdf->subjectReportRow($pdf);
                    break;

                case 6:
                    $debug_html.=$setpdf->subjectReportRowNonEmptyAtt($pdf);
                    break;

                case 3:
                    $debug_html.=$setpdf->subjectReportColumn($pdf);
                    break;

                case 4:
                    $debug_html.=$setpdf->pastoralReport($pdf);
                    break;

                case 7:
                    $debug_html.=$setpdf->attendanceTermReport($pdf);
                    break;

                case 5:
                    $pdf->AddPage();
            }
        }

        ////////////////////////////////////////////////////////////////////
        // output to PDF
        ////////////////////////////////////////////////////////////////////
        $pdf->Output($fileName, 'F');

        if ($debug_dump) {
            $debug_html.="</body></html>";
            file_put_contents($fileName.'.html', $debug_html);
        }

        ////////////////////////////////////////////////////////////////////
        // update history
        ////////////////////////////////////////////////////////////////////
        $data = array(
            'studentID' => $setpdf->studentID,
            'reportID' => $setpdf->reportID
        );
        $sql = "SELECT *
            FROM arrArchive
            WHERE studentID = :studentID
            AND reportID = :reportID";
        $rs = $connection2->prepare($sql);
        $rs->execute($data);

        if ($rs->rowCount() > 0) {
            // update record
            $row2 = $rs->fetch();
            $data = array(
                'archiveID' => $row2['archiveID'],
                'reportName' => $reportName,
                'created' => date('Y-m-d H:i:s')
            );
            $sql = "UPDATE arrArchive
                SET reportName = :reportName,
                created = :created
                WHERE archiveID = :archiveID";
        } else {
            $data = array(
                'studentID' => $setpdf->studentID,
                'reportID' => $setpdf->reportID,
                'reportName' => $reportName,
                'created' => date('Y-m-d H:i:s')
            );
            $sql = "INSERT IGNORE INTO arrArchive
                SET studentID = :studentID,
                reportID = :reportID,
                reportName = :reportName,
                created = :created";
        }
        //print $sql."<br>";
        //print_r($data);
        //print "<br>";
        $rs = $connection2->prepare($sql);
        $rs->execute($data);
    } // end rollGroup while loop


    // return to class list page
    $returnPath = $_SESSION[$guid]["absoluteURL"]."/index.php?q=/modules/".$_SESSION[$guid]["module"]."/pdf.php".
            "&yearGroupID=$setpdf->yearGroupID".
            "&schoolYearID=$setpdf->schoolYearID".
            "&rollGroupID=$setpdf->rollGroupID".
            "&reportID=$setpdf->reportID";

    header("location:$returnPath");
}
?>
