-- phpMyAdmin SQL Dump
-- version 4.0.10.14
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Aug 23, 2017 at 06:49 AM
-- Server version: 5.5.54-38.6-log
-- PHP Version: 5.6.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `getdemo_pd_astute`
--

-- --------------------------------------------------------

--
-- Table structure for table `companyformmaster`
--

CREATE TABLE IF NOT EXISTS `companyformmaster` (
  `CFMId` int(11) NOT NULL AUTO_INCREMENT,
  `clientId` int(11) NOT NULL DEFAULT '0',
  `categoryId` int(11) NOT NULL DEFAULT '0',
  `dynamicFormMasterId` int(11) NOT NULL DEFAULT '0',
  `strEntryDate` varchar(50) DEFAULT NULL,
  `strIP` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`CFMId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=64 ;

--
-- Dumping data for table `companyformmaster`
--

INSERT INTO `companyformmaster` (`CFMId`, `clientId`, `categoryId`, `dynamicFormMasterId`, `strEntryDate`, `strIP`) VALUES
(24, 3, 3, 2, '23-08-2017 10:54:23', '49.213.43.2'),
(23, 3, 3, 1, '23-08-2017 10:54:23', '49.213.43.2'),
(28, 3, 2, 1, '23-08-2017 10:54:37', '49.213.43.2'),
(33, 3, 1, 1, '23-08-2017 10:54:49', '49.213.43.2'),
(7, 2, 6, 1, '18-08-2017 08:48:44', '::1'),
(8, 2, 6, 2, '18-08-2017 08:48:44', '::1'),
(9, 2, 6, 3, '18-08-2017 08:48:44', '::1'),
(10, 2, 6, 4, '18-08-2017 08:48:44', '::1'),
(11, 2, 5, 1, '18-08-2017 08:48:53', '::1'),
(12, 2, 5, 2, '18-08-2017 08:48:53', '::1'),
(13, 2, 4, 1, '18-08-2017 08:49:01', '::1'),
(62, 1, 7, 15, '23-08-2017 11:40:05', '49.213.43.2'),
(63, 1, 7, 16, '23-08-2017 11:40:05', '49.213.43.2'),
(17, 1, 10, 1, '18-08-2017 08:49:28', '::1'),
(18, 1, 9, 1, '18-08-2017 08:49:37', '::1'),
(19, 1, 9, 2, '18-08-2017 08:49:37', '::1'),
(20, 1, 8, 1, '18-08-2017 08:49:48', '::1'),
(21, 1, 8, 2, '18-08-2017 08:49:48', '::1'),
(22, 1, 8, 3, '18-08-2017 08:49:48', '::1'),
(25, 3, 3, 3, '23-08-2017 10:54:23', '49.213.43.2'),
(26, 3, 3, 4, '23-08-2017 10:54:23', '49.213.43.2'),
(27, 3, 3, 5, '23-08-2017 10:54:23', '49.213.43.2'),
(29, 3, 2, 2, '23-08-2017 10:54:37', '49.213.43.2'),
(30, 3, 2, 3, '23-08-2017 10:54:37', '49.213.43.2'),
(31, 3, 2, 4, '23-08-2017 10:54:37', '49.213.43.2'),
(32, 3, 2, 5, '23-08-2017 10:54:37', '49.213.43.2'),
(34, 3, 1, 2, '23-08-2017 10:54:49', '49.213.43.2'),
(35, 3, 1, 3, '23-08-2017 10:54:49', '49.213.43.2'),
(36, 3, 1, 4, '23-08-2017 10:54:49', '49.213.43.2'),
(37, 3, 1, 5, '23-08-2017 10:54:49', '49.213.43.2'),
(61, 1, 7, 14, '23-08-2017 11:40:05', '49.213.43.2'),
(60, 1, 7, 7, '23-08-2017 11:40:05', '49.213.43.2'),
(59, 1, 7, 6, '23-08-2017 11:40:05', '49.213.43.2'),
(58, 1, 7, 5, '23-08-2017 11:40:05', '49.213.43.2'),
(57, 1, 7, 4, '23-08-2017 11:40:05', '49.213.43.2'),
(56, 1, 7, 3, '23-08-2017 11:40:05', '49.213.43.2'),
(55, 1, 7, 2, '23-08-2017 11:40:05', '49.213.43.2'),
(54, 1, 7, 1, '23-08-2017 11:40:05', '49.213.43.2');

-- --------------------------------------------------------

--
-- Table structure for table `dropdrowndetails`
--

CREATE TABLE IF NOT EXISTS `dropdrowndetails` (
  `dropDrownDetailId` int(11) NOT NULL AUTO_INCREMENT,
  `dropDrownMId` int(11) NOT NULL DEFAULT '0',
  `Name` varchar(1000) DEFAULT NULL,
  `strEntryDate` varchar(50) DEFAULT NULL,
  `strIP` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`dropDrownDetailId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

--
-- Dumping data for table `dropdrowndetails`
--

INSERT INTO `dropdrowndetails` (`dropDrownDetailId`, `dropDrownMId`, `Name`, `strEntryDate`, `strIP`) VALUES
(1, 1, 'Credit Balance', '16-08-2017 03:41:27', '::1'),
(2, 1, 'Debit Balance', '16-08-2017 03:41:34', '::1'),
(3, 1, 'Opening Balance', '16-08-2017 03:41:59', '::1'),
(4, 2, 'Self Employed', '23-08-2017 10:05:47', '49.213.43.2'),
(5, 2, 'Salaried', '23-08-2017 10:06:08', '49.213.43.2'),
(6, 3, 'Residential', '23-08-2017 10:06:46', '49.213.43.2'),
(7, 3, 'Commercial', '23-08-2017 10:06:57', '49.213.43.2'),
(8, 3, 'Self Occupied', '23-08-2017 10:07:08', '49.213.43.2'),
(9, 3, 'Rented', '23-08-2017 10:07:16', '49.213.43.2'),
(10, 3, 'Vacant', '23-08-2017 10:07:23', '49.213.43.2'),
(11, 4, 'Proprietorship Firm', '23-08-2017 10:32:59', '49.213.43.2'),
(12, 4, 'Partnership Firm', '23-08-2017 10:33:11', '49.213.43.2'),
(13, 4, 'Pvt. Ltd.', '23-08-2017 10:33:23', '49.213.43.2'),
(14, 4, 'Ltd. Co.', '23-08-2017 10:33:34', '49.213.43.2');

-- --------------------------------------------------------

--
-- Table structure for table `dropdrownmaster`
--

CREATE TABLE IF NOT EXISTS `dropdrownmaster` (
  `DropDrownMId` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(1000) DEFAULT NULL,
  `strEntryDate` varchar(50) DEFAULT NULL,
  `strIP` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`DropDrownMId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `dropdrownmaster`
--

INSERT INTO `dropdrownmaster` (`DropDrownMId`, `Name`, `strEntryDate`, `strIP`) VALUES
(1, 'Account', '16-08-2017 03:41:21', '::1'),
(2, 'Employeement Type', '23-08-2017 10:05:26', '49.213.43.2'),
(3, 'Type of Property', '23-08-2017 10:06:29', '49.213.43.2'),
(4, 'Nature of Business Entiry', '23-08-2017 10:32:20', '49.213.43.2');

-- --------------------------------------------------------

--
-- Table structure for table `dynamicformdetails`
--

CREATE TABLE IF NOT EXISTS `dynamicformdetails` (
  `dynamicFormDId` int(11) NOT NULL AUTO_INCREMENT,
  `dynamicFMId` int(11) NOT NULL DEFAULT '0',
  `labelText` varchar(1000) DEFAULT NULL,
  `type` varchar(1000) DEFAULT NULL,
  `dropdrownMasterId` int(11) NOT NULL DEFAULT '0',
  `isRequired` varchar(1000) DEFAULT NULL,
  `patternId` int(11) NOT NULL DEFAULT '0',
  `strEntryDate` varchar(50) DEFAULT NULL,
  `strIP` varchar(20) DEFAULT NULL,
  `isDelete` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`dynamicFormDId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=101 ;

--
-- Dumping data for table `dynamicformdetails`
--

INSERT INTO `dynamicformdetails` (`dynamicFormDId`, `dynamicFMId`, `labelText`, `type`, `dropdrownMasterId`, `isRequired`, `patternId`, `strEntryDate`, `strIP`, `isDelete`) VALUES
(3, 1, 'Mobile No :', 'Text Box', 0, 'Yes', 3, '23-08-2017 10:08:19', '49.213.43.2', 0),
(2, 1, 'Employeement Type', 'Drop Drown', 2, 'Yes', 0, '23-08-2017 10:07:59', '49.213.43.2', 0),
(1, 1, 'Name', 'Text Box', 0, 'Yes', 4, '23-08-2017 10:04:37', '49.213.43.2', 0),
(4, 1, 'Address', 'Text Box', 0, 'No', 4, '23-08-2017 10:08:48', '49.213.43.2', 0),
(5, 1, 'Type of Locality', 'Text Box', 0, 'No', 4, '23-08-2017 10:09:05', '49.213.43.2', 0),
(6, 1, 'Ownership Status', 'Text Box', 0, 'No', 4, '23-08-2017 10:09:29', '49.213.43.2', 0),
(7, 1, 'Name of Owner', 'Text Box', 0, 'No', 4, '23-08-2017 10:09:54', '49.213.43.2', 0),
(8, 1, 'Ownership & Name of Owner', 'Text Box', 0, 'Yes', 4, '23-08-2017 10:10:52', '49.213.43.2', 0),
(13, 1, 'Property Type', 'Drop Drown', 3, 'Yes', 0, '23-08-2017 10:25:28', '49.213.43.2', 0),
(14, 2, 'DOB', 'Text Box', 0, 'Yes', 4, '23-08-2017 10:26:01', '49.213.43.2', 0),
(15, 2, 'Age', 'Text Box', 0, 'Yes', 4, '23-08-2017 10:26:20', '49.213.43.2', 0),
(16, 2, 'QUALIFICATION', 'Text Box', 0, 'Yes', 4, '23-08-2017 10:26:58', '49.213.43.2', 0),
(17, 2, 'VINTAGE AT CURRENT REIDENCE', 'Text Box', 0, 'Yes', 4, '23-08-2017 10:27:39', '49.213.43.2', 0),
(18, 2, 'PARMANENT ADDRESS', 'Text Box', 0, 'Yes', 4, '23-08-2017 10:28:03', '49.213.43.2', 0),
(19, 2, 'DETAILS OF DEPENDENTS', 'Text Box', 0, 'No', 4, '23-08-2017 10:28:29', '49.213.43.2', 0),
(20, 2, 'ADDRESS OF NATIVE PLAC, WHEREVER APPLICABLE', 'Text Box', 0, 'No', 4, '23-08-2017 10:29:13', '49.213.43.2', 0),
(21, 2, 'LOAN AMOUNT APPLIED FOR', 'Text Box', 0, 'Yes', 4, '23-08-2017 10:29:37', '49.213.43.2', 0),
(22, 2, 'END USE OF LOAN', 'Text Box', 0, 'Yes', 4, '23-08-2017 10:29:57', '49.213.43.2', 0),
(23, 4, 'NAME OF THE BUSINESS /  EMPLOYEEMENT', 'Text Box', 0, 'No', 4, '23-08-2017 10:41:29', '49.213.43.2', 0),
(24, 4, 'NATURE OF BUSINESS ENTIRY', 'Drop Drown', 4, 'No', 0, '23-08-2017 10:41:54', '49.213.43.2', 0),
(25, 4, 'NO OF YEARS IN CURRENT BSUINESS', 'Text Box', 0, 'No', 4, '23-08-2017 10:43:01', '49.213.43.2', 0),
(26, 4, 'TOTAL WORK EXPERIENCE', 'Text Box', 0, 'No', 4, '23-08-2017 10:43:16', '49.213.43.2', 0),
(27, 4, 'PREVIOUS WORK HISTORY', 'Text Box', 0, 'No', 4, '23-08-2017 10:43:32', '49.213.43.2', 0),
(28, 4, 'TYPE OF BUSINESS AND KEY PRODUCTS & THEIR END USE', 'Text Box', 0, 'No', 4, '23-08-2017 10:44:04', '49.213.43.2', 0),
(29, 4, 'LEVEL OF ACTIVITY & STOCKS ALONG WITYH OBSERVATIONS', 'Text Box', 0, 'No', 4, '23-08-2017 10:44:34', '49.213.43.2', 0),
(30, 4, 'ANY OTHER BUSINESS /SOURCE OF INCOME', 'Text Box', 0, 'No', 4, '23-08-2017 10:44:57', '49.213.43.2', 0),
(31, 4, 'MAJOR CUSTOMERS (CONTACT PERSON WITH NO)', 'Text Box', 0, 'No', 4, '23-08-2017 10:45:31', '49.213.43.2', 0),
(32, 4, 'MAJOR SUPPLIERS (CONTACT PERSON WITH NO)', 'Text Box', 0, 'No', 4, '23-08-2017 10:45:59', '49.213.43.2', 0),
(33, 4, 'ASSET DETAILS (INCLUDING PROPERTIES, VEHICLES, INVESTMENTS, ETS)', 'Text Box', 0, 'No', 4, '23-08-2017 10:46:33', '49.213.43.2', 0),
(34, 4, 'OTHER IMPORTANT DETAILS', 'Text Box', 0, 'No', 4, '23-08-2017 10:46:55', '49.213.43.2', 0),
(35, 4, 'THIRD PARTY CHECK', 'Text Box', 0, 'No', 4, '23-08-2017 10:47:30', '49.213.43.2', 0),
(36, 4, 'NO OF EMPLOYEE', 'Text Box', 0, 'No', 4, '23-08-2017 10:47:50', '49.213.43.2', 0),
(37, 4, 'TOTAL SALARIES PER MONTH', 'Text Box', 0, 'No', 4, '23-08-2017 10:48:10', '49.213.43.2', 0),
(38, 4, 'ANNUAL SALES', 'Text Box', 0, 'No', 4, '23-08-2017 10:48:31', '49.213.43.2', 0),
(39, 4, 'OVER ALL COSTS', 'Text Box', 0, 'No', 4, '23-08-2017 10:49:22', '49.213.43.2', 0),
(40, 4, 'MAJOR COST HEADS', 'Text Box', 0, 'No', 4, '23-08-2017 10:49:39', '49.213.43.2', 0),
(41, 4, 'GROSS MARGIN % & AMOUNT (RS.)', 'Text Box', 0, 'No', 4, '23-08-2017 10:50:10', '49.213.43.2', 0),
(42, 4, 'NET MARGIN % & AMOUNT (Rs.)', 'Text Box', 0, 'No', 4, '23-08-2017 10:50:41', '49.213.43.2', 0),
(43, 4, 'DEBATORS CYCLE', 'Text Box', 0, 'No', 4, '23-08-2017 10:51:08', '49.213.43.2', 0),
(44, 4, 'CREDITORS CYCLE', 'Text Box', 0, 'No', 4, '23-08-2017 10:51:23', '49.213.43.2', 0),
(45, 4, 'CAPITAL INVESTED', 'Text Box', 0, 'No', 4, '23-08-2017 10:51:42', '49.213.43.2', 0),
(46, 4, 'LOAN FUNDS INCL CC LIMIT', 'Text Box', 0, 'No', 4, '23-08-2017 10:52:14', '49.213.43.2', 0),
(47, 4, 'STOCK MAINTAINED', 'Text Box', 0, 'No', 4, '23-08-2017 10:52:36', '49.213.43.2', 0),
(48, 4, 'BUSINESS BANK ACCOUNTS', 'Text Box', 0, 'No', 4, '23-08-2017 10:52:58', '49.213.43.2', 0),
(49, 4, 'OTHER INFORMATION IF ANY', 'Text Box', 0, 'No', 4, '23-08-2017 10:53:24', '49.213.43.2', 0),
(50, 5, 'NAME OF THE COMPANY', 'Text Box', 0, 'No', 4, '23-08-2017 11:15:05', '49.213.43.2', 0),
(51, 5, 'CONSTITUTION OF THE COMPANY', 'Text Box', 0, 'No', 4, '23-08-2017 11:15:18', '49.213.43.2', 0),
(52, 5, 'NAME OF THE REPORTING AUTHORITY', 'Text Box', 0, 'No', 4, '23-08-2017 11:15:35', '49.213.43.2', 0),
(53, 5, 'EMPLOYER DETAILS', 'Text Box', 0, 'No', 4, '23-08-2017 11:15:49', '49.213.43.2', 0),
(54, 5, 'EMPLOYMENT STATUS', 'Text Box', 0, 'No', 4, '23-08-2017 11:16:03', '49.213.43.2', 0),
(55, 5, 'CURRENT DESIGNATION', 'Text Box', 0, 'No', 4, '23-08-2017 11:16:17', '49.213.43.2', 0),
(56, 5, 'DEPARTMENT', 'Text Box', 0, 'No', 4, '23-08-2017 11:16:31', '49.213.43.2', 0),
(57, 5, 'EMPLOYEE ID', 'Text Box', 0, 'No', 4, '23-08-2017 11:16:51', '49.213.43.2', 0),
(58, 5, 'SALARY MODE', 'Text Box', 0, 'No', 4, '23-08-2017 11:17:11', '49.213.43.2', 0),
(59, 5, 'GROSS MONTHLY SALARY', 'Text Box', 0, 'No', 4, '23-08-2017 11:17:27', '49.213.43.2', 0),
(60, 5, 'NET MONTHLY SALARY', 'Text Box', 0, 'No', 4, '23-08-2017 11:17:44', '49.213.43.2', 0),
(61, 5, 'TERMS OF EMPLOYMENT', 'Text Box', 0, 'No', 4, '23-08-2017 11:18:00', '49.213.43.2', 0),
(62, 5, 'TOTAL WORK EXPERIENCE', 'Text Box', 0, 'No', 4, '23-08-2017 11:18:16', '49.213.43.2', 0),
(63, 5, 'DETAILS OF PREVIOUS EMPLOYMENTS WITH VINTAGE (if applicable)', 'Text Box', 0, 'No', 4, '23-08-2017 11:18:39', '49.213.43.2', 0),
(64, 5, 'THIRD PARTY CHECK', 'Text Box', 0, 'No', 4, '23-08-2017 11:18:57', '49.213.43.2', 0),
(65, 6, 'NAME', 'Text Box', 0, 'No', 4, '23-08-2017 11:20:27', '49.213.43.2', 0),
(66, 6, 'DOB', 'Text Box', 0, 'No', 4, '23-08-2017 11:20:55', '49.213.43.2', 0),
(67, 6, 'AGE', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:21:18', '49.213.43.2', 0),
(68, 6, 'RELATIONSHIP WITH THE MAIN APPLICANT', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:21:35', '49.213.43.2', 0),
(69, 6, 'PROFILE', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:21:55', '49.213.43.2', 0),
(70, 6, 'WORK DETAILS', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:22:08', '49.213.43.2', 0),
(71, 6, 'INCOME', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:22:28', '49.213.43.2', 0),
(72, 6, 'ANY OTHER SOURCE OF INCOME OF THE MAIN APPLICANT', 'Text Box', 0, 'No', 4, '23-08-2017 11:22:42', '49.213.43.2', 0),
(73, 6, 'LIC PAYMENT /INSURANCE /MEDICLAIM ETC.', 'Text Box', 0, 'No', 4, '23-08-2017 11:23:12', '49.213.43.2', 0),
(74, 6, 'SHARE /MUTUAL FUND INVESTMENTS', 'Text Box', 0, 'No', 4, '23-08-2017 11:23:36', '49.213.43.2', 0),
(75, 6, 'CARS/TW OWNED', 'Text Box', 0, 'No', 4, '23-08-2017 11:52:02', '49.213.43.2', 0),
(100, 6, 'OWNED OTHER ASSETS {(FIXED E.G. FLAT/HOUSE/SHOP/PLOT) AND FD, PPF ETC.}', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:52:39', '49.213.43.2', 0),
(76, 14, '(A) Total Monthly Net Income (Turnover*margin %)', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:28:32', '49.213.43.2', 0),
(77, 14, '(B) General Family expenses per month', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:28:52', '49.213.43.2', 0),
(78, 14, 'Rental expenses per month', 'Text Box', 0, 'No', 4, '23-08-2017 11:26:33', '49.213.43.2', 0),
(79, 14, '(C) Net income Available for servicing loans ( 60% of A)', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:29:11', '49.213.43.2', 0),
(80, 14, '(D) PL / Auto Loan EMI', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:29:31', '49.213.43.2', 0),
(81, 14, '(E) Other Loan EMI', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:29:47', '49.213.43.2', 0),
(82, 14, '(F) Proposed EMI', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:30:02', '49.213.43.2', 0),
(83, 14, '(G) Total Monthly obligations (D+E+F)', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:30:20', '49.213.43.2', 0),
(84, 14, '(H) Net Surplus available after all expenses & Obligations (A-B-G).Rental expense can be added back', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:30:43', '49.213.43.2', 0),
(85, 14, 'Net surplus (H) to be at least 20% of total monthly net income i.e. (20%*A)', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:30:57', '49.213.43.2', 0),
(86, 15, 'Do you expect to receive any other income during the current F.Y. that you havenâ€™t yet told us about?', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:31:43', '49.213.43.2', 0),
(87, 15, 'Do you expect to receive any income from lump sums or from other taxable/non taxable income sources?', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:32:00', '49.213.43.2', 0),
(88, 15, 'Do you expect to receive any income from property lettings?', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:32:17', '49.213.43.2', 0),
(89, 15, 'General Lifestyle', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:32:35', '49.213.43.2', 0),
(90, 15, 'Other Remarks', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:32:50', '49.213.43.2', 0),
(91, 16, 'INTERVIEWERâ€™S COMMENTS, ALONG WITH EXPLANATIONS FOR CREDIT COMMENTS (WITH A BRIEF SUMMARY REPORT )', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:34:01', '49.213.43.2', 0),
(92, 16, 'ALTERNATE MOBILE / LL NUMBER', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:34:26', '49.213.43.2', 0),
(93, 16, 'PD STATUS', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:34:49', '49.213.43.2', 0),
(94, 16, 'INTERVIEWERâ€™S NAME', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:35:02', '49.213.43.2', 0),
(95, 1, 'Report Date', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:45:17', '49.213.43.2', 0),
(96, 1, 'Size of Premises', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:46:45', '49.213.43.2', 0),
(97, 1, 'RESIDENCE ADDRESS', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:47:30', '49.213.43.2', 0),
(98, 1, 'Area of Property', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:48:09', '49.213.43.2', 0),
(99, 1, 'Value as per customer', 'Text Box', 0, 'Yes', 4, '23-08-2017 11:48:30', '49.213.43.2', 0);

-- --------------------------------------------------------

--
-- Table structure for table `dynamicformmaster`
--

CREATE TABLE IF NOT EXISTS `dynamicformmaster` (
  `dynamicFMId` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(1000) DEFAULT NULL,
  `familyBackGroundId` int(11) NOT NULL DEFAULT '0',
  `otherLiabilitiesId` int(11) NOT NULL DEFAULT '0',
  `isSystemRecord` int(11) NOT NULL DEFAULT '1',
  `page` varchar(1000) DEFAULT NULL,
  `strEntryDate` varchar(50) DEFAULT NULL,
  `strIP` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`dynamicFMId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;

--
-- Dumping data for table `dynamicformmaster`
--

INSERT INTO `dynamicformmaster` (`dynamicFMId`, `Name`, `familyBackGroundId`, `otherLiabilitiesId`, `isSystemRecord`, `page`, `strEntryDate`, `strIP`) VALUES
(1, 'Name - Location - Owner Detail', 0, 0, 1, 'DynamicForm.php', '23-08-2017 10:04:02', '49.213.43.2'),
(3, 'Family Background Form', 0, 0, 0, 'FamilyBackground.php', '16-08-2017 04:05:16', '::1'),
(7, 'OtherLiabilities Form', 0, 0, 0, 'OtherLiabilities.php', '16-08-2017 04:05:21', '::1'),
(2, 'Basic Details of Applicant', 0, 0, 1, 'DynamicForm.php', '16-08-2017 04:05:40', '::1'),
(4, 'Details for Self Employed Profile', 0, 0, 1, 'DynamicForm.php', '23-08-2017 10:30:56', '49.213.43.2'),
(5, 'DETAILS FOR SALARIED PROFILE', 0, 0, 1, 'DynamicForm.php', '23-08-2017 11:13:05', '49.213.43.2'),
(6, 'COMMON INFORMATION ON CO-APPLICANT', 0, 0, 1, 'DynamicForm.php', '23-08-2017 11:19:42', '49.213.43.2'),
(14, 'BUDGET ANALYSIS', 0, 0, 1, 'DynamicForm.php', '23-08-2017 11:25:35', '49.213.43.2'),
(15, 'OTHER QUESTIONS', 0, 0, 1, 'DynamicForm.php', '23-08-2017 11:31:24', '49.213.43.2'),
(16, 'OTHER DETAILS', 0, 0, 1, 'DynamicForm.php', '23-08-2017 11:33:25', '49.213.43.2');

-- --------------------------------------------------------

--
-- Table structure for table `submitdynamicform`
--

CREATE TABLE IF NOT EXISTS `submitdynamicform` (
  `submitDynamicFormId` int(11) NOT NULL AUTO_INCREMENT,
  `appID` int(11) NOT NULL DEFAULT '0',
  `companyID` int(11) NOT NULL DEFAULT '0',
  `CategoryID` int(11) NOT NULL DEFAULT '0',
  `dynamicFormMID` int(11) NOT NULL DEFAULT '0',
  `dynamicFormDetailId` int(11) NOT NULL DEFAULT '0',
  `formValue` varchar(10000) DEFAULT NULL,
  `strEntryDate` varchar(50) DEFAULT NULL,
  `strIP` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`submitDynamicFormId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;

--
-- Dumping data for table `submitdynamicform`
--

INSERT INTO `submitdynamicform` (`submitDynamicFormId`, `appID`, `companyID`, `CategoryID`, `dynamicFormMID`, `dynamicFormDetailId`, `formValue`, `strEntryDate`, `strIP`) VALUES
(11, 11, 1, 7, 1, 3, '9685741235', '21-08-2017 06:21:18', '::1'),
(10, 11, 1, 7, 1, 2, 'Bhavesh Dhakecha', '21-08-2017 06:21:18', '::1'),
(9, 11, 1, 7, 1, 1, 'Dhakecha Dipali', '21-08-2017 06:21:18', '::1'),
(16, 10, 1, 7, 1, 4, 'Credit Balance', '21-08-2017 06:30:55', '::1'),
(15, 10, 1, 7, 1, 3, '9687956412', '21-08-2017 06:30:55', '::1'),
(14, 10, 1, 7, 1, 2, '', '21-08-2017 06:30:55', '::1'),
(13, 10, 1, 7, 1, 1, 'krunal Shah', '21-08-2017 06:30:55', '::1'),
(12, 11, 1, 7, 1, 4, 'Debit Balance', '21-08-2017 06:21:18', '::1');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
