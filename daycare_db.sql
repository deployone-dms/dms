-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 28, 2025 at 04:17 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `daycare_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `enrollees`
--

CREATE TABLE `enrollees` (
  `ID` int(11) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_initial` varchar(255) NOT NULL,
  `birthday` varchar(100) NOT NULL,
  `age` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollees`
--

INSERT INTO `enrollees` (`ID`, `photo`, `last_name`, `first_name`, `middle_initial`, `birthday`, `age`) VALUES
(0, 'uploads/1758645114_644e27dd-5596-4952-ac33-137102f29ad6.jfif', 'blahblah', 'blahbalh', 'blahbalh', '2003-10-12', 22);

-- --------------------------------------------------------

--
-- Table structure for table `grossmotor`
--

CREATE TABLE `grossmotor` (
  `eval1[]` int(2) NOT NULL,
  `eval2` int(2) NOT NULL,
  `eval3` int(2) NOT NULL,
  `eval4` int(2) NOT NULL,
  `eval5` int(2) NOT NULL,
  `eval6` int(2) NOT NULL,
  `eval7` int(2) NOT NULL,
  `eval8` int(2) NOT NULL,
  `eval9` int(2) NOT NULL,
  `eval10` int(2) NOT NULL,
  `eval11` int(2) NOT NULL,
  `eval12` int(2) NOT NULL,
  `eval13` int(2) NOT NULL,
  `eval14` int(2) NOT NULL,
  `eval15` int(2) NOT NULL,
  `eval16` int(2) NOT NULL,
  `eval17` int(2) NOT NULL,
  `eval18` int(2) NOT NULL,
  `eval19` int(2) NOT NULL,
  `eval20` int(2) NOT NULL,
  `eval21` int(2) NOT NULL,
  `eval22` int(2) NOT NULL,
  `eval23` int(2) NOT NULL,
  `eval24` int(2) NOT NULL,
  `eval25` int(2) NOT NULL,
  `eval26` int(2) NOT NULL,
  `eval27` int(2) NOT NULL,
  `eval28` int(2) NOT NULL,
  `eval29` int(2) NOT NULL,
  `eval30` int(2) NOT NULL,
  `eval31` int(2) NOT NULL,
  `eval32` int(2) NOT NULL,
  `eval33` int(2) NOT NULL,
  `eval34` int(2) NOT NULL,
  `eval35` int(2) NOT NULL,
  `eval36` int(2) NOT NULL,
  `eval37` int(2) NOT NULL,
  `eval38` int(2) NOT NULL,
  `eval39` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grossmotor_submissions`
--

CREATE TABLE `grossmotor_submissions` (
  `id` int(11) NOT NULL,
  `payload` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grossmotor_submissions`
--

INSERT INTO `grossmotor_submissions` (`id`, `payload`, `created_at`) VALUES
(1, '[{\"item\":1,\"eval1\":12,\"eval2\":null,\"eval3\":null},{\"item\":2,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":3,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":4,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":5,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":6,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":7,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":8,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":9,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":10,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":11,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":12,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":13,\"eval1\":null,\"eval2\":null,\"eval3\":null}]', '2025-09-18 16:33:10'),
(2, '[{\"item\":1,\"eval1\":11,\"eval2\":null,\"eval3\":null},{\"item\":2,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":3,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":4,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":5,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":6,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":7,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":8,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":9,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":10,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":11,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":12,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":13,\"eval1\":null,\"eval2\":null,\"eval3\":null}]', '2025-09-18 16:33:50'),
(3, '[{\"item\":1,\"eval1\":11,\"eval2\":null,\"eval3\":null},{\"item\":2,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":3,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":4,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":5,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":6,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":7,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":8,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":9,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":10,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":11,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":12,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":13,\"eval1\":null,\"eval2\":null,\"eval3\":null}]', '2025-09-18 16:40:02'),
(4, '[{\"item\":1,\"eval1\":1,\"eval2\":null,\"eval3\":null},{\"item\":2,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":3,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":4,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":5,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":6,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":7,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":8,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":9,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":10,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":11,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":12,\"eval1\":null,\"eval2\":null,\"eval3\":null},{\"item\":13,\"eval1\":null,\"eval2\":null,\"eval3\":null}]', '2025-09-18 16:48:44'),
(5, '[{\"item\":1,\"eval1\":1,\"eval2\":1,\"eval3\":1},{\"item\":2,\"eval1\":1,\"eval2\":1,\"eval3\":1},{\"item\":3,\"eval1\":1,\"eval2\":1,\"eval3\":1},{\"item\":4,\"eval1\":1,\"eval2\":1,\"eval3\":1},{\"item\":5,\"eval1\":1,\"eval2\":1,\"eval3\":1},{\"item\":6,\"eval1\":1,\"eval2\":1,\"eval3\":1},{\"item\":7,\"eval1\":1,\"eval2\":1,\"eval3\":1},{\"item\":8,\"eval1\":1,\"eval2\":1,\"eval3\":1},{\"item\":9,\"eval1\":1,\"eval2\":1,\"eval3\":1},{\"item\":10,\"eval1\":1,\"eval2\":1,\"eval3\":1},{\"item\":11,\"eval1\":1,\"eval2\":1,\"eval3\":1},{\"item\":12,\"eval1\":1,\"eval2\":1,\"eval3\":1},{\"item\":13,\"eval1\":1,\"eval2\":1,\"eval3\":1}]', '2025-09-18 16:49:24'),
(6, '[{\"item\":1,\"eval1\":13,\"eval2\":null,\"eval3\":null},{\"item\":2,\"eval1\":12,\"eval2\":null,\"eval3\":null},{\"item\":3,\"eval1\":25,\"eval2\":null,\"eval3\":null},{\"item\":4,\"eval1\":44,\"eval2\":null,\"eval3\":null},{\"item\":5,\"eval1\":12,\"eval2\":null,\"eval3\":null},{\"item\":6,\"eval1\":1,\"eval2\":null,\"eval3\":null},{\"item\":7,\"eval1\":1,\"eval2\":null,\"eval3\":null},{\"item\":8,\"eval1\":1,\"eval2\":null,\"eval3\":null},{\"item\":9,\"eval1\":1,\"eval2\":null,\"eval3\":null},{\"item\":10,\"eval1\":5,\"eval2\":null,\"eval3\":null},{\"item\":11,\"eval1\":2,\"eval2\":null,\"eval3\":null},{\"item\":12,\"eval1\":5,\"eval2\":null,\"eval3\":null},{\"item\":13,\"eval1\":2,\"eval2\":null,\"eval3\":null}]', '2025-09-18 17:03:36');

-- --------------------------------------------------------

--
-- Table structure for table `student_form`
--

CREATE TABLE `student_form` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `psa_birth_certificate` varchar(255) DEFAULT NULL,
  `immunization_card` varchar(255) DEFAULT NULL,
  `qc_parent_id` varchar(255) DEFAULT NULL,
  `solo_parent_id` varchar(255) DEFAULT NULL,
  `four_ps_id` varchar(255) DEFAULT NULL,
  `pwd_id` varchar(255) DEFAULT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_informations`
--

CREATE TABLE `student_informations` (
  `ID` int(11) NOT NULL,
  `picture` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_initial` varchar(255) NOT NULL,
  `birth_date` varchar(100) NOT NULL,
  `age` int(100) NOT NULL,
  `sex` varchar(100) NOT NULL,
  `birth_city` varchar(255) NOT NULL,
  `birth_province` varchar(255) NOT NULL,
  `house_no` varchar(255) NOT NULL,
  `street_name` varchar(255) NOT NULL,
  `area` varchar(255) NOT NULL,
  `village` varchar(255) NOT NULL,
  `barangay` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `mother_name` varchar(255) NOT NULL,
  `mother_contact` varchar(255) NOT NULL,
  `father_name` varchar(255) NOT NULL,
  `father_contact` varchar(255) NOT NULL,
  `psa_birth_certificate` varchar(255) NOT NULL,
  `immunization_card` varchar(255) NOT NULL,
  `qc_parent_id` varchar(255) NOT NULL,
  `solo_parent_id` varchar(255) NOT NULL,
  `4ps_id` varchar(255) NOT NULL,
  `pwd_id` varchar(255) NOT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_infos`
--

CREATE TABLE `student_infos` (
  `ID` int(11) NOT NULL,
  `student_id` int(255) NOT NULL,
  `picture` varchar(255) NOT NULL,
  `psa_birth_certificate` varchar(255) NOT NULL,
  `immunization_card` varchar(255) NOT NULL,
  `qc_parent_id` varchar(255) NOT NULL,
  `solo_parent_id` varchar(255) NOT NULL,
  `4ps_id` varchar(255) NOT NULL,
  `pwd_id` varchar(255) NOT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_name` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_initial` varchar(255) NOT NULL,
  `birth_date` varchar(255) NOT NULL,
  `age` int(100) NOT NULL,
  `sex` varchar(255) NOT NULL,
  `birth_city` varchar(255) NOT NULL,
  `birth_province` varchar(255) NOT NULL,
  `house_no` varchar(255) NOT NULL,
  `street_name` varchar(255) NOT NULL,
  `area` varchar(255) NOT NULL,
  `village` varchar(255) NOT NULL,
  `barangay` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `mother_name` varchar(255) NOT NULL,
  `mother_contact` varchar(255) NOT NULL,
  `father_name` varchar(255) NOT NULL,
  `father_contact` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_infos`
--

INSERT INTO `student_infos` (`ID`, `student_id`, `picture`, `psa_birth_certificate`, `immunization_card`, `qc_parent_id`, `solo_parent_id`, `4ps_id`, `pwd_id`, `submission_date`, `last_name`, `first_name`, `middle_initial`, `birth_date`, `age`, `sex`, `birth_city`, `birth_province`, `house_no`, `street_name`, `area`, `village`, `barangay`, `city`, `mother_name`, `mother_contact`, `father_name`, `father_contact`) VALUES
(15, 0, '', '', '', '', '', '', '', '2025-09-24 03:06:24', 'blahblah', 'Potpot Marie', 'blahbalh', '2020-10-25', 4, 'Female', 'Caloocan City', 'NCR', 'N/A', 'Wilson De Alca St.', 'Lower Empire', 'N/A', 'Payatas B', 'Quezon City', 'Nancy J. Bureros', '09483104268', 'Edgar P. Bureros', '09104058255'),
(17, 0, '', '', '', '', '', '', '', '2025-09-24 03:31:57', 'Batumbakal', 'Potpot Marie', 'J.', '2021-10-25', 3, 'Female', 'Caloocan City', 'NCR', 'N/A', 'Wilson De Alca St.', 'Lower Empire', 'N/A', 'Payatas B', 'Quezon City', 'Nancy J. Bureros', '09483104268', 'Edgar P. Bureros', '09104058255'),
(18, 1, 'uploads/picture_1758684735_10d1e03f.jfif', 'uploads/psa_birth_certificate_1758684735_49bfa4b8.png', 'uploads/immunization_card_1758684735_674c35cb.jpg', 'uploads/qc_parent_id_1758684735_749cd5df.jpg', '', '', '', '2025-09-24 03:32:15', '', '', '', '', 0, '', '', '', '', '', '', '', '', '', '', '', '', ''),
(19, 0, '', '', '', '', '', '', '', '2025-09-24 03:39:18', 'Batumbakal', 'Potpot Marie', 'J.', '2021-10-25', 3, 'Female', 'Caloocan City', 'NCR', 'N/A', 'Wilson De Alca St.', 'Lower Empire', 'N/A', 'Payatas B', 'Quezon City', 'Nancy J. Bureros', '09483104268', 'Edgar P. Bureros', '09104058255'),
(20, 1, 'uploads/picture_1758685175_c5eae436.jpg', 'uploads/psa_birth_certificate_1758685175_2b431d72.png', '', '', '', '', '', '2025-09-24 03:39:35', '', '', '', '', 0, '', '', '', '', '', '', '', '', '', '', '', '', ''),
(21, 1, 'uploads/picture_1758704756_8fc72ad3.png', '', '', '', '', '', '', '2025-09-24 09:05:56', '', '', '', '', 0, '', '', '', '', '', '', '', '', '', '', '', '', ''),
(22, 1, 'uploads/picture_1758719415_3469534b.png', '', '', '', '', '', '', '2025-09-24 13:10:15', '', '', '', '', 0, '', '', '', '', '', '', '', '', '', '', '', '', ''),
(23, 1, 'uploads/picture_1758720570_391324bb.jpg', '', '', '', '', '', '', '2025-09-24 13:29:30', '', '', '', '', 0, '', '', '', '', '', '', '', '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `ID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Contact` varchar(255) NOT NULL,
  `Address` varchar(255) NOT NULL,
  `District` varchar(255) NOT NULL,
  `Daycare_Center` varchar(255) NOT NULL,
  `Barangay` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`ID`, `Name`, `Contact`, `Address`, `District`, `Daycare_Center`, `Barangay`) VALUES
(2, 'Elsa B. Costales', '09196589458', '40 Forestry St.', '1', 'Vasra I', 'Vasra'),
(3, 'Clarinel Rose O. Dichoso', '0975 713 5963', '40 Forestry St.', '1', 'Vasra II', 'Vasra '),
(4, 'Maria Elena U. Guevara', '0922 217 1187', 'Road 9 cor. Road 11', '1', 'Bagong Pag-asa', 'Bagong Pag-asa'),
(5, 'Angelita B. Taguinod', '0999 764 7072', 'Hona St., San Roque', '1', 'Kampilan I', 'Bagong Pag-asa'),
(6, 'Ana Lyn Torreliza', '0926 930 3661', '5 Romblon St.', '1', 'Sto. Cristo', 'Sto. Cristo'),
(7, 'Lory F. Fabul', '0935 658 1299', '4 Misamis Ext. RSCC Compound', '1', 'Bahay Bulilit', 'Sto. Cristo'),
(8, 'Nida H. Escobedo', '0956 139 6919', 'Ilocos Sur St.', '1', 'Ramon Magsaysay I', 'Ramon Magsaysay '),
(9, 'Lourdes Bernarte', '0915 692 0414', 'Ilocos Sur St.', '1', 'Ramon Magsaysay II', 'Ramon Magsaysay '),
(10, 'Melcah Utlang', '09057036384', '#2 Batangas St.', '1', 'Alicia', 'Alicia'),
(11, 'Elma Romero', '0927 603 0267', 'Alley 3  Project 6', '1', 'Project 6', 'Project 6'),
(12, 'Leonora Benavidez', '0922 342 8097', '14 Bayanihan Drive', '1', 'Sitio Maligaya I', 'Bahay Toro'),
(13, 'Cherry Samson', '0923 416 1357', '14 Bayanihan Drive', '1', 'Sitio Maligaya II', 'Bahay Toro'),
(14, 'Ludivina Sicat', '0999 535 0845', 'Road 19 Bahay Toro, Proj. 8', '1', 'San Jose Seminary I', 'Bahay Toro'),
(15, 'Ma. Paz Dejucos	', '0932 237 7050', 'Road 19 Bahay Toro Proj. 8', '1', 'San Jose Seminary II', 'Bahay Toro'),
(16, 'Luzviminda Morales', '0919 271 5651', 'Alley 25 Pook Masagana', '1', 'Pook Masagana', 'Bahay Toro'),
(17, 'Lilibeth Sacramento', '0921 417 9970', 'E. Beltran St.', '1', 'Katipunan', 'Katipunan'),
(18, 'Victorina Moron	', '0923 728 0372', '173 West Riverside St.', '1', 'San Antonio I', 'San Antonio'),
(19, 'Merline Ramos', '0922 479 3589', '47 Guerrero St.', '1', 'San Antonio II', 'San Antonio'),
(20, 'Jonna Derpo', '0912 417 9825', 'Lot 5 Alley 7, 31 San Jose St.', '1', 'San Antonio III', 'San Antonio'),
(21, 'Verania Saulan', '0963 855 0339', '#3 Palomaria St. cor. Bansalangin St.', '1', 'Veterans Village	', 'Veterans Village'),
(22, 'Emma Buban', '0955 617 5681', '37 Sorsogon St.', '1', 'Nayong Kanluran', 'Nayong Kanluran'),
(23, 'Alma Mancilla', '0963 663 0017', 'Sanches St. cor. Supnet St.', '1', 'Bungad I', 'Bungad '),
(24, 'Irene Jacolbe', '0908 304 2311', 'Sanches.St.corner Supnet St.', '1', 'Bungad II', 'Bungad'),
(25, 'Evelyn Morales	', '0975 027 3041', 'Brgy. Hall, Mendoza cor. Basa', '1', 'Paltok', 'Paltok'),
(26, 'Grachelle Musico', '0935 321 6711', '75 Anakbayan Dulo St.', '1', 'East Anak Bayan', 'Paltok'),
(27, 'Romela Balones', '0926 656 5726', '2nd Flr. Multi-purpose Hall 23D Gen. Lim St.', '1', 'Sta. Cruz', 'Sta. Cruz'),
(28, 'Joan Taylan', '0966 634 9257', '#1 Bernardo St. cor. San Pedro Bautista', '1', 'Mariblo', 'Mariblo'),
(29, 'Marilyn Chan', '0966 230 1416', '21 Florencia West SFDM Multipurpose Hall', '1', 'Bukal ng Pag-asa', 'Del Monte'),
(30, 'Marilyn Chan', '0966 230 1416', '21 Florencia West SFDM Multipurpose Hall', '1', 'Del Monte', 'Del Monte'),
(31, 'Melinda Marte', '0995 826 4037', '30 San Vicente St.', '1', 'Damayan', 'Damayan'),
(32, 'Sonia Astudillo', '0947 298 9614', '4 Capuas St.', '1', 'Saint John Masambong I', 'Masambong'),
(33, 'Charlyn Salazar', '0910 252 9184', '4 Capuas St.', '1', 'Saint John Masambong II', 'Masambong'),
(34, 'Meditha Besas', '0935 968 9306', '199 Calamba ext. Talayan Village', '1', 'Talayan Calamba', 'Talayan'),
(35, 'Emerlita Cabatian	', '0906 786 6237', '14 Maria Clara St.', '1', 'Sto. Domingo', 'Sto. Domingo'),
(36, 'Maria Victoria Tiemsen', '0955 415 1890', 'Biak na Bato cor. Makaturing', '1', 'Manresa', 'Manresa'),
(37, 'Maria Victoria Tiemsen', '0955 415 1890', 'Multi-Purpose Hall', '1', 'Matutum', 'Manresa'),
(38, 'Janice Jose Palomar', '0921 566 6437', '80 Tendido St.', '1', 'San Jose', 'San Jose'),
(39, 'Catherine Sanchez', '0935 084 0864', '23 J. Pineda St.', '1', 'Pag-ibig sa Nayon', 'Pag-ibig sa Nayon'),
(40, 'Analiza Ala', '0922 713 2075', '11 Road Harmony St.', '1', 'Tibagan', 'Balingasa'),
(41, 'Rosemarie Gonzaga', '0905 429 0093', '#25 Sitio Sto. Cristo', '1', 'Sitio Sto. Cristo', 'Balingasa'),
(42, 'Jzyl Alboro', '0929 496 8756', 'Barangay Hall Angelo St.', '1', 'N.S. Amoranto', 'N.S. Amoranto');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `grossmotor_submissions`
--
ALTER TABLE `grossmotor_submissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_form`
--
ALTER TABLE `student_form`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_informations`
--
ALTER TABLE `student_informations`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `student_infos`
--
ALTER TABLE `student_infos`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `grossmotor_submissions`
--
ALTER TABLE `grossmotor_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `student_form`
--
ALTER TABLE `student_form`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_informations`
--
ALTER TABLE `student_informations`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_infos`
--
ALTER TABLE `student_infos`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
