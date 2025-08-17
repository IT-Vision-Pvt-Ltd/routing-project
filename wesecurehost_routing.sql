-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 17, 2025 at 11:01 AM
-- Server version: 5.7.44
-- PHP Version: 8.1.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wesecurehost_routing`
--

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `mobile` varchar(100) DEFAULT NULL,
  `fax` varchar(100) DEFAULT NULL,
  `bill_addr_1` varchar(255) DEFAULT NULL,
  `bill_addr_2` varchar(255) DEFAULT NULL,
  `bill_addr_3` varchar(255) DEFAULT NULL,
  `bill_addr_4` varchar(255) DEFAULT NULL,
  `bill_addr_5` varchar(255) DEFAULT NULL,
  `bill_addr_city` varchar(100) DEFAULT NULL,
  `bill_addr_state` varchar(100) DEFAULT NULL,
  `bill_addr_postal_code` varchar(40) DEFAULT NULL,
  `bill_addr_country` varchar(100) DEFAULT NULL,
  `customer_type_id` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `import_logs`
--

CREATE TABLE `import_logs` (
  `id` bigint(20) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `total_rows` int(11) NOT NULL DEFAULT '0',
  `success_rows` int(11) NOT NULL DEFAULT '0',
  `error_rows` int(11) NOT NULL DEFAULT '0',
  `errors` longtext,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) NOT NULL,
  `operation_id` varchar(100) DEFAULT NULL,
  `account_reference_number` varchar(190) DEFAULT NULL,
  `client_id` bigint(20) NOT NULL,
  `job_name` varchar(255) DEFAULT NULL,
  `is_recurring` tinyint(1) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `location_id` varchar(100) DEFAULT NULL,
  `total_price` decimal(12,2) DEFAULT NULL,
  `total_payments` decimal(12,2) DEFAULT NULL,
  `total_sales_tax` decimal(12,2) DEFAULT NULL,
  `total_cogs` decimal(12,2) DEFAULT NULL,
  `total_expenses` decimal(12,2) DEFAULT NULL,
  `total_costs` decimal(12,2) DEFAULT NULL,
  `proposal_date` datetime DEFAULT NULL,
  `converted_to_active` tinyint(1) DEFAULT NULL,
  `current_job_stage` enum('Approved','Scheduled','Completed','Other') DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT NULL,
  `on_hold` tinyint(1) DEFAULT NULL,
  `proposal_accepted_at` datetime DEFAULT NULL,
  `submitted_by_user_id` varchar(100) DEFAULT NULL,
  `is_eligible_for_renewal` tinyint(1) DEFAULT NULL,
  `client_notes` text,
  `internal_notes` text,
  `crew_notes` text,
  `progress_billing_type` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `line_items`
--

CREATE TABLE `line_items` (
  `id` bigint(20) NOT NULL,
  `job_id` bigint(20) NOT NULL,
  `item_id` varchar(100) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `identifier` varchar(190) DEFAULT NULL,
  `option_type_cd` varchar(100) DEFAULT NULL,
  `option_accepted` tinyint(1) DEFAULT NULL,
  `quantity` decimal(12,4) DEFAULT NULL,
  `completed_quantity` decimal(12,4) DEFAULT NULL,
  `unit_price` decimal(12,4) DEFAULT NULL,
  `completed_unit_cost` decimal(12,4) DEFAULT NULL,
  `unit_markup` decimal(12,4) DEFAULT NULL,
  `total` decimal(12,4) DEFAULT NULL,
  `is_actual` tinyint(1) DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT NULL,
  `is_billable` tinyint(1) DEFAULT NULL,
  `is_estimate` tinyint(1) DEFAULT NULL,
  `is_locked` tinyint(1) DEFAULT NULL,
  `is_percentage_discount` tinyint(1) DEFAULT NULL,
  `is_reimbursable` tinyint(1) DEFAULT NULL,
  `is_sales_tax` tinyint(1) DEFAULT NULL,
  `is_subcontracted` tinyint(1) DEFAULT NULL,
  `is_tax_discount` tinyint(1) DEFAULT NULL,
  `is_taxable` tinyint(1) DEFAULT NULL,
  `is_taxed` tinyint(1) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `visit_id` varchar(100) DEFAULT NULL,
  `vendor_id` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(190) DEFAULT NULL,
  `role` enum('admin','manager','sales','readonly') NOT NULL DEFAULT 'admin',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_clients_display_name` (`display_name`),
  ADD KEY `idx_clients_email` (`email`),
  ADD KEY `idx_clients_city_state` (`bill_addr_city`,`bill_addr_state`);

--
-- Indexes for table `import_logs`
--
ALTER TABLE `import_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_reference_number` (`account_reference_number`),
  ADD KEY `idx_jobs_stage` (`current_job_stage`),
  ADD KEY `idx_jobs_client` (`client_id`);

--
-- Indexes for table `line_items`
--
ALTER TABLE `line_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_line_items_job` (`job_id`),
  ADD KEY `idx_line_items_item` (`item_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `import_logs`
--
ALTER TABLE `import_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `line_items`
--
ALTER TABLE `line_items`
  ADD CONSTRAINT `line_items_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
