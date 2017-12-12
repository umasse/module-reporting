SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE arrArchive (
  archiveID int(10) unsigned NOT NULL,
  studentID int(10) unsigned NOT NULL,
  reportID int(10) unsigned NOT NULL,
  reportName varchar(255) NOT NULL,
  created datetime NOT NULL,
  firstDate datetime NOT NULL,
  lastDate datetime NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE arrCriteria (
  criteriaID int(10) unsigned NOT NULL,
  subjectID int(10) unsigned zerofill NOT NULL,
  criteriaName varchar(255) NOT NULL,
  criteriaOrder tinyint(3) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE arrReport (
  reportID int(10) unsigned NOT NULL,
  schoolYearID int(3) unsigned zerofill NOT NULL,
  reportName varchar(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  reportNum tinyint(3) unsigned NOT NULL DEFAULT '1',
  reportOrder tinyint(4) unsigned NOT NULL,
  orientation tinyint(4) unsigned NOT NULL DEFAULT '1',
  gradeScale int(10) unsigned DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  gradeScale2 int(10) unsigned NOT NULL,
  gradeScale3 int(10) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE arrReportAssign (
  reportAssignID int(10) unsigned NOT NULL,
  schoolYearID int(3) unsigned zerofill NOT NULL,
  yearGroupID int(3) unsigned zerofill NOT NULL,
  reportID int(10) NOT NULL,
  assignStatus tinyint(1) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE arrReportGrade (
  reportGradeID int(10) unsigned NOT NULL,
  reportID int(10) unsigned DEFAULT NULL,
  criteriaID int(10) unsigned NOT NULL,
  studentID int(10) unsigned NOT NULL,
  gradeID int(10) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  mark float NOT NULL,
  percent float NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE arrReportSection (
  sectionID int(10) unsigned NOT NULL,
  reportID int(10) unsigned DEFAULT NULL,
  sectionType int(10) unsigned DEFAULT NULL,
  sectionOrder int(10) unsigned DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE arrReportSectionDetail (
  reportSectionDetailID int(10) unsigned NOT NULL,
  sectionID int(10) unsigned DEFAULT NULL,
  sectionContent text,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE arrReportSectionType (
  reportSectionTypeID int(11) NOT NULL,
  sectionTypeName varchar(45) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE arrReportStatus (
  reportAssignID int(10) unsigned NOT NULL,
  reportID int(10) NOT NULL,
  roleID int(10) unsigned NOT NULL,
  assignStatus tinyint(1) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE arrReportSubject (
  reportSubjectID int(10) unsigned NOT NULL,
  studentID int(10) unsigned zerofill NOT NULL,
  subjectID int(10) unsigned NOT NULL,
  classID int(8) unsigned zerofill NOT NULL,
  reportID int(10) unsigned NOT NULL,
  subjectComment text,
  teacherID int(10) unsigned DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE arrStatus (
  statusID int(10) unsigned NOT NULL,
  reportID int(10) NOT NULL,
  roleID int(3) unsigned zerofill NOT NULL,
  reportStatus tinyint(4) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


ALTER TABLE arrArchive
  ADD PRIMARY KEY (archiveID),
  ADD UNIQUE KEY studentID (studentID,reportID);

ALTER TABLE arrCriteria
  ADD PRIMARY KEY (criteriaID),
  ADD UNIQUE KEY criteriaName (subjectID,criteriaName);

ALTER TABLE arrReport
  ADD PRIMARY KEY (reportID),
  ADD UNIQUE KEY schoolYearID (schoolYearID,reportName),
  ADD KEY reportNum (reportNum);

ALTER TABLE arrReportAssign
  ADD PRIMARY KEY (reportAssignID),
  ADD UNIQUE KEY yearGroupID (yearGroupID,reportID,schoolYearID);

ALTER TABLE arrReportGrade
  ADD PRIMARY KEY (reportGradeID),
  ADD UNIQUE KEY criteriaID (studentID,criteriaID);

ALTER TABLE arrReportSection
  ADD PRIMARY KEY (sectionID);

ALTER TABLE arrReportSectionDetail
  ADD PRIMARY KEY (reportSectionDetailID),
  ADD UNIQUE KEY sectionID (sectionID);

ALTER TABLE arrReportSectionType
  ADD PRIMARY KEY (reportSectionTypeID),
  ADD UNIQUE KEY sectionTypeName (sectionTypeName);

ALTER TABLE arrReportStatus
  ADD PRIMARY KEY (reportAssignID),
  ADD UNIQUE KEY reportID (reportID,roleID);

ALTER TABLE arrReportSubject
  ADD PRIMARY KEY (reportSubjectID),
  ADD UNIQUE KEY arrPersonID (studentID,reportID,subjectID);

ALTER TABLE arrStatus
  ADD PRIMARY KEY (statusID),
  ADD UNIQUE KEY reportID (reportID,roleID);


ALTER TABLE arrArchive
  MODIFY archiveID int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE arrCriteria
  MODIFY criteriaID int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE arrReport
  MODIFY reportID int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE arrReportAssign
  MODIFY reportAssignID int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE arrReportGrade
  MODIFY reportGradeID int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE arrReportSection
  MODIFY sectionID int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE arrReportSectionDetail
  MODIFY reportSectionDetailID int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE arrReportSectionType
  MODIFY reportSectionTypeID int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE arrReportStatus
  MODIFY reportAssignID int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE arrReportSubject
  MODIFY reportSubjectID int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE arrStatus
  MODIFY statusID int(10) unsigned NOT NULL AUTO_INCREMENT;

INSERT INTO `arrReportSectionType` (`reportSectionTypeID`, `sectionTypeName`) VALUES
        (1, 'Text'),
        (2, 'Subject (row)'),
        (3, 'Subject (column)'),
        (4, 'Pastoral'),
        (5, 'Page Break');