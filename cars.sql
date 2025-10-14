CREATE DATABASE IF NOT EXISTS `cars` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `cars`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `car_brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `car_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brand_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`),
  CONSTRAINT `car_categories_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `car_brands`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `car_cars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `discount` decimal(12,2) DEFAULT NULL,
  `monthly_payment` decimal(12,2) DEFAULT NULL,
  `warranty_type` varchar(100) DEFAULT NULL,
  `car_condition` varchar(50) DEFAULT NULL,
  `mileage` int(11) DEFAULT NULL,
  `low_mileage` enum('yes','no') DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `exterior_color` varchar(50) DEFAULT NULL,
  `interior_color` varchar(50) DEFAULT NULL,
  `imported_from` varchar(100) DEFAULT NULL,
  `fuel_type` varchar(50) DEFAULT NULL,
  `gear_type` varchar(50) DEFAULT NULL,
  `cylinders` int(11) DEFAULT NULL,
  `engine_size` varchar(50) DEFAULT NULL,
  `drive_type` varchar(50) DEFAULT NULL,
  `keys_count` int(11) DEFAULT NULL,
  `seats` int(11) DEFAULT NULL,
  `agent_note` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `added_to_place` tinyint(1) DEFAULT 0,
  `tax_price` decimal(12,2) DEFAULT 0.00,
  `customs_fee` decimal(12,2) DEFAULT 0.00,
  `customs_clearer_fee` decimal(12,2) DEFAULT 0.00,
  `warranty_price` decimal(12,2) DEFAULT 0.00,
  `shipping_korea` decimal(12,2) DEFAULT 0.00,
  `shipping_saudi` decimal(12,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `car_cars_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `car_brands`(`id`),
  CONSTRAINT `car_cars_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `car_categories`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `car_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `car_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `car_id` (`car_id`),
  CONSTRAINT `car_images_ibfk_1` FOREIGN KEY (`car_id`) REFERENCES `car_cars`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `car_comfort_features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `car_id` int(11) NOT NULL,
  `feature` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `car_id` (`car_id`),
  CONSTRAINT `car_comfort_features_ibfk_1` FOREIGN KEY (`car_id`) REFERENCES `car_cars`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `car_exterior_features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `car_id` int(11) NOT NULL,
  `feature` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `car_id` (`car_id`),
  CONSTRAINT `car_exterior_features_ibfk_1` FOREIGN KEY (`car_id`) REFERENCES `car_cars`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `car_safety_features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `car_id` int(11) NOT NULL,
  `feature` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `car_id` (`car_id`),
  CONSTRAINT `car_safety_features_ibfk_1` FOREIGN KEY (`car_id`) REFERENCES `car_cars`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `car_tech_features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `car_id` int(11) NOT NULL,
  `feature` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `car_id` (`car_id`),
  CONSTRAINT `car_tech_features_ibfk_1` FOREIGN KEY (`car_id`) REFERENCES `car_cars`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `slider` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image_path` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `trends` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `brand_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`),
  CONSTRAINT `trends_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `car_brands`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `vehicle_imports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uploader_id` int(11) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
  `rows_total` int(11) DEFAULT 0,
  `rows_processed` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `processed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uploader_id` (`uploader_id`),
  CONSTRAINT `vehicle_imports_ibfk_1` FOREIGN KEY (`uploader_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `metric` varchar(100) NOT NULL,
  `value` bigint(20) DEFAULT 0,
  `meta` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `client_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `resource` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `client_tracking_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `finances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('income','expense') NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'USD',
  `description` text,
  `related_invoice_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `related_invoice_id` (`related_invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total` decimal(14,2) NOT NULL,
  `tax` decimal(12,2) DEFAULT 0.00,
  `customs` decimal(12,2) DEFAULT 0.00,
  `shipping` decimal(12,2) DEFAULT 0.00,
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text,
  `is_read` tinyint(1) DEFAULT 0,
  `meta` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `admin_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `email_notifications` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `admin_settings_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `target_table` varchar(100) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `meta` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `vehicle_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `import_id` int(11) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `import_id` (`import_id`),
  CONSTRAINT `vehicle_files_ibfk_1` FOREIGN KEY (`import_id`) REFERENCES `vehicle_imports`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `email_verifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `avatar` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `car_brands` (`id`,`name`,`photo`,`created_at`) VALUES
(2,'بي ام دبليو','uploads/brands/6897a601d09fd_15.png','2025-08-09 22:48:17');

INSERT INTO `car_categories` (`id`,`brand_id`,`category_name`) VALUES
(2,2,'m5');

INSERT INTO `car_cars` (`id`,`title`,`price`,`discount`,`monthly_payment`,`warranty_type`,`car_condition`,`mileage`,`low_mileage`,`brand_id`,`category_id`,`model`,`exterior_color`,`interior_color`,`imported_from`,`fuel_type`,`gear_type`,`cylinders`,`engine_size`,`drive_type`,`keys_count`,`seats`,`agent_note`,`created_at`,`added_to_place`,`tax_price`,`customs_fee`,`customs_clearer_fee`,`warranty_price`,`shipping_korea`,`shipping_saudi`,`is_active`,`is_deleted`) VALUES
(8,'bmw m5',50000.00,1600.00,1500.00,'ضمان الوكالة','جديدة',0,'yes',2,2,'m5','#000','#000','المانيا','بنززين','ل',5,'35','كاش',5,6,'المتحد','2025-08-12 14:20:56',1,0.00,0.00,0.00,0.00,0.00,0.00,1,0);

INSERT INTO `car_images` (`id`,`car_id`,`image`) VALUES
(13,8,'uploads/cars/689b2398bc6ed.jpg'),
(14,8,'uploads/cars/689b2398bdaf2.jpg');

INSERT INTO `car_comfort_features` (`id`,`car_id`,`feature`) VALUES
(12,8,'سسسسسسسسسس');

INSERT INTO `car_exterior_features` (`id`,`car_id`,`feature`) VALUES
(11,8,'سسسسسسسسسس');

INSERT INTO `car_safety_features` (`id`,`car_id`,`feature`) VALUES
(15,8,'وساده');

INSERT INTO `car_tech_features` (`id`,`car_id`,`feature`) VALUES
(11,8,'سسسسسسسسس');

INSERT INTO `slider` (`id`,`image_path`,`link`) VALUES
(2,'uploads/slider/689c7fb8ccb74_wallpaperflare.com_wallpaper (1).jpg','https://syarah.com/');

INSERT INTO `trends` (`id`,`name`,`photo`,`brand_id`,`created_at`) VALUES
(1,'ام كشاف ','uploads/trends/68a1e56ec1e3b_wallpaperflare.com_wallpaper__2_.jpg',2,'2025-08-17 17:21:34');

INSERT INTO `users` (`id`,`first_name`,`last_name`,`phone`,`email`,`password_hash`,`role`,`avatar`,`is_verified`,`created_at`) VALUES
(1,'نال','','0000000000','admin@aeentabcommercial.com','$2y$10$abcdefghijklmnopqrstuvABCDEFGHIJKLmnopqrs','admin',NULL,1,'2025-08-17 17:41:00');

INSERT INTO `statistics` (`metric`,`value`,`meta`) VALUES
('total_cars',1,JSON_OBJECT('note','initial count'));

COMMIT;
