<?php
session_start();

include  "../../config.php";
include "../../functions.php";
include "../attendance/moduleFunctions.php";

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

    include "./subject_function.php";
    include "./insertmark_function.php";
    include "./function.php";

    $schoolYearID = $_POST['schoolYearID'];
    $reportID = $_POST['reportID'];
    $subjectID = $_POST['subjectID'];
    $classID = $_POST['classID'];
    $formData = $_POST['formData'];
    
    // select marks for this class from markbook
    $sql = "SELECT gibbonMarkbookEntry.gibbonPersonIDStudent,
gibbonMarkbookEntry.attainmentValue,
gibbonMarkbookEntry.attainmentDescriptor,
gibbonMarkbookEntry.effortValue,
gibbonMarkbookEntry.effortDescriptor,
gibbonMarkbookEntry.comment,
gibbonMarkbookEntry.gibbonMarkbookColumnID,
gibbonMarkbookColumn.attainment,
gibbonMarkbookColumn.gibbonScaleIDAttainment,
gibbonMarkbookColumn.gibbonRubricIDAttainment,
gibbonMarkbookColumn.attainmentWeighting,
gibbonMarkbookColumn.effort,
gibbonMarkbookColumn.gibbonScaleIDEffort,
gibbonMarkbookColumn.gibbonRubricIDEffort,
gibbonMarkbookColumn.comment

FROM gibbonCourseClassPerson

INNER JOIN gibbonMarkbookEntry
ON gibbonMarkbookEntry.gibbonPersonIDStudent = gibbonCourseClassPerson.gibbonPersonID 


INNER JOIN gibbonMarkbookColumn
ON 
gibbonMarkbookColumn.gibbonMarkbookColumnID = gibbonMarkbookEntry.gibbonMarkbookColumnID


where gibbonCourseClassPerson.gibbonCourseClassID = 1122";
    $formData = explode('&', $formData);
    foreach ($formData AS $rowData) {
        $row = explode('=', $rowData);
        print_r($row);
    }
}