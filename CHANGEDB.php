<?php
$sql=array();
$count=0;
$sql[$count][0]="1.00" ; // version number
$sql[$count][1]="" ; // sql statements
$count++;
$sql[$count][0]="1.01" ; // version number
$sql[$count][1]="" ; // sql statements
$count++;
$sql[$count][0]="1.02" ; // version number
$sql[$count][1]="" ; // sql statements
$count++;
$sql[$count][0]="1.03" ; // version number
$sql[$count][1]="" ; // sql statements
$count++;
$sql[$count][0]="1.04" ; // version number
$sql[$count][1]="" ; // sql statements
$count++;
$sql[$count][0]="1.05" ; // version number
$sql[$count][1]="
    ALTER TABLE arrReport DROP INDEX schoolYearID;
    ALTER TABLE arrReport ADD UNIQUE( schoolYearID, reportName);
";
$count++;
$sql[$count][0]="1.06" ; // version number
$sql[$count][1]="
    INSERT INTO `arrReportSectionType` (`reportSectionTypeID`, `sectionTypeName`) VALUES
        (1, 'Text'),
        (2, 'Subject'),
        (3, 'Pastoral'),
        (4, 'Page Break');";
$count++;
$sql[$count][0]="1.08" ; // version number
$sql[$count][1]="" ; // sql statements

$count++;
$sql[$count][0]="1.09" ; // version number
$sql[$count][1]="" ; // sql statements

$count++;
$sql[$count][0]="1.10" ; // version number
$sql[$count][1]="" ; // sql statements

$count++;
$sql[$count][0]="1.11" ; // version number
$sql[$count][1]="" ; // sql statements

$count++;
$sql[$count][0]="1.12" ; // version number
$sql[$count][1]="" ; // sql statements
?>