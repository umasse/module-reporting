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

$count++;
$sql[$count][0]="1.13" ; // version number
$sql[$count][1]="ALTER TABLE arrCriteria
ADD UNIQUE INDEX `criteriaName` (`subjectID` ASC, `criteriaName` ASC);" ; // sql statements

++$count;
$sql[$count][0] = '1.14';
$sql[$count][1] = "ALTER TABLE arrReport
ADD COLUMN orientation TINYINT(4) UNSIGNED NOT NULL DEFAULT 1 AFTER reportOrder;
INSERT IGNORE INTO gibbonAction
SET gibbonAction.gibbonModuleID = 
(
    SELECT gibbonModule.gibbonModuleID
    FROM gibbonModule
	WHERE gibbonModule.name = 'Reporting'
),
gibbonAction.name = 'PDF Mail',
gibbonAction.precedence = 0,
gibbonAction.category = 'ARR',
gibbonAction.description = 'Email PDF report to parents',
gibbonAction.URLList = 'pdfmail.php',
gibbonAction.entryURL = 'pdfmail.php',
gibbonAction.entrySidebar = 'Y',
gibbonAction.menuShow = 'Y',
gibbonAction.defaultPermissionAdmin = 'Y',
gibbonAction.defaultPermissionTeacher = 'N',
gibbonAction.defaultPermissionStudent = 'N',
gibbonAction.defaultPermissionParent = 'N',
gibbonAction.defaultPermissionSupport = 'Y';

INSERT IGNORE INTO gibbonAction
SET gibbonAction.gibbonModuleID = 
(
    SELECT gibbonModule.gibbonModuleID
    FROM gibbonModule
	WHERE gibbonModule.name = 'Reporting'
),
gibbonAction.name = 'Parent',
gibbonAction.precedence = 0,
gibbonAction.category = 'ARR',
gibbonAction.description = 'Parent login section',
gibbonAction.URLList = 'parent.php',
gibbonAction.entryURL = 'parent.php',
gibbonAction.entrySidebar = 'Y',
gibbonAction.menuShow = 'Y',
gibbonAction.defaultPermissionAdmin = 'N',
gibbonAction.defaultPermissionTeacher = 'Y',
gibbonAction.defaultPermissionStudent = 'N',
gibbonAction.defaultPermissionParent = 'N',
gibbonAction.defaultPermissionSupport = 'N';

ALTER TABLE arrReportGrade
ADD COLUMN mark FLOAT NOT NULL AFTER timestamp,
ADD COLUMN percent FLOAT NOT NULL AFTER mark;
";

$count++;
$sql[$count][0]="1.15" ; // version number
$sql[$count][1]="" ; // sql statements

$count++;
$sql[$count][0]="1.16" ; // version number
$sql[$count][1]="" ; // sql statements

$count++;
$sql[$count][0]="1.17" ; // version number
$sql[$count][1]="" ; // sql statements

$count++;
$sql[$count][0]="1.18" ; // version number
$sql[$count][1]="" ; // sql statements

$count++;
$sql[$count][0]="1.19" ; // version number
$sql[$count][1]="" ; // sql statements

$count++;
$sql[$count][0]="1.20" ; // version number
$sql[$count][1]="" ; // sql statements