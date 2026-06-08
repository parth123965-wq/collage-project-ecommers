CREATE DATABASE IF NOT EXISTS `arabic_ecommerce` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `arabic_ecommerce`;

-- 1. Category Table
CREATE TABLE IF NOT EXISTS `Category` (
    `CategoryId` INT AUTO_INCREMENT PRIMARY KEY,
    `CategoryName` VARCHAR(255) NOT NULL,
    `CategoryDescription` TEXT
) ENGINE=InnoDB;

-- 2. User Table
CREATE TABLE IF NOT EXISTS `User` (
    `userId` INT AUTO_INCREMENT PRIMARY KEY,
    `userName` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL, -- Increased length for secure password hashing
    `address` TEXT,
    `phonenu` VARCHAR(20), -- Changed to VARCHAR to preserve leading zeros in phone numbers
    `dob` DATE,
    `role` VARCHAR(20) NOT NULL DEFAULT 'customer'
) ENGINE=InnoDB;

-- 3. Product Table
CREATE TABLE IF NOT EXISTS `Product` (
    `productId` INT AUTO_INCREMENT PRIMARY KEY,
    `productName` VARCHAR(255) NOT NULL,
    `productDescription` TEXT,
    `productPrice` DECIMAL(10, 2) NOT NULL, -- Decimal is better than float for currency
    `productQuantity` INT NOT NULL DEFAULT 0,
    `productImage` VARCHAR(255),
    `CategoryId` INT,
    FOREIGN KEY (`CategoryId`) REFERENCES `Category`(`CategoryId`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 4. Cart Table
CREATE TABLE IF NOT EXISTS `Cart` (
    `cart_id` INT AUTO_INCREMENT PRIMARY KEY, -- Primary key for the cart record
    `p_id` INT,
    `p_name` VARCHAR(255),
    `p_des` TEXT,
    `p_price` DECIMAL(10, 2),
    `p_pricewithtax` DECIMAL(10, 2),
    `p_image` VARCHAR(255),
    `p_qty` INT NOT NULL DEFAULT 1,
    `CategoryName` VARCHAR(255),
    `u_id` INT,
    `u_name` VARCHAR(150),
    `u_address` TEXT,
    `u_phonenu` VARCHAR(20),
    `u_email` VARCHAR(150),
    FOREIGN KEY (`p_id`) REFERENCES `Product`(`productId`) ON DELETE CASCADE,
    FOREIGN KEY (`u_id`) REFERENCES `User`(`userId`) ON DELETE CASCADE
) ENGINE=InnoDB;