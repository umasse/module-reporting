<?php
if (isActionAccessible($guid, $connection2,"/modules/Reporting/insertmark.php")==FALSE) {
    //Acess denied
    print "<div class='error'>" ;
            print "You do not have access to this action." ;
    print "</div>" ;
} else {
    // proceed

    // include function pages
    $modpath =  "./modules/".$_SESSION[$guid]["module"];
    include $modpath."/subject_function.php";
    include $modpath."/insertmark_function.php";
    include $modpath."/function.php";
    
    $schoolYearID = $_POST['schoolYearID'];
    $reportID = $_POST['reportID'];
    $subjectID = $_POST['subjectID'];
    $classID = $_POST['classID'];
    
    $criteriaList = readCriteriaList($connection2, $subjectID);    
    $markSetList = get_markSet($connection2, $classID);
 
    
    
}