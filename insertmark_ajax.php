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

    $classID = $_POST['classID'];
    $reportID = $_POST['reportID'];
    $subjectID = $_POST['subjectID'];
    $schoolYearID = $_POST['schoolYearID'];

    $criteriaList = readCriteriaList($connection2, $subjectID);    
    $markSetList = get_markSet($connection2, $classID);

    $res = array(
        'criteriaList' => $criteriaList->fetchAll(),
        'markSetList' => $markSetList->fetchAll()
    );
    echo json_encode($res);
}
    