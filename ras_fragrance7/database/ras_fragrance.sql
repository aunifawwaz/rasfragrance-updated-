-- Create the database (if not already created)
CREATE DATABASE IF NOT EXISTS ras_fragrance;
USE ras_fragrance;

-- Drop tables if they exist (to reset the database during testing)
DROP TABLE IF EXISTS Invoice, Payment, Inventory, `Order`, Favourite, Cart, Quiz, Perfume, User, Administrator;

-- Create the User table
CREATE TABLE User (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone_no VARCHAR(15),
    address TEXT,
    email VARCHAR(100) UNIQUE NOT NULL
);

-- Create the Administrator table
CREATE TABLE Administrator (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL
);

-- Create the Perfume table
CREATE TABLE Perfume (
    perfume_id INT AUTO_INCREMENT PRIMARY KEY,
    perfume_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT
);

-- Create the Inventory table
CREATE TABLE Inventory (
    inventory_id INT AUTO_INCREMENT PRIMARY KEY,
    perfume_id INT NOT NULL,
    stock_quantity INT NOT NULL,
    restock_date DATE,
    FOREIGN KEY (perfume_id) REFERENCES Perfume(perfume_id) ON DELETE CASCADE
);

-- Create the Quiz table
CREATE TABLE Quiz (
    quizQuestion_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quiz_details TEXT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE
);

-- Create the Cart table
CREATE TABLE Cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    perfume_id INT NOT NULL,
    quantity_cart INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
    FOREIGN KEY (perfume_id) REFERENCES Perfume(perfume_id) ON DELETE CASCADE
);

-- Create the Favourite table
CREATE TABLE Favourite (
    favourite_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    perfume_id INT NOT NULL,
    total_amount DECIMAL(10, 2),
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
    FOREIGN KEY (perfume_id) REFERENCES Perfume(perfume_id) ON DELETE CASCADE
);

-- Create the Order table
CREATE TABLE `orders` (
    `order_id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,           -- Reference to user (could be used if user login is implemented)
    `total_price` DECIMAL(10, 2) NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'pending', -- Order status (e.g., pending, completed)
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`order_id`)
);

CREATE TABLE `order_items` (
    `order_item_id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NOT NULL,  -- Foreign key reference to the orders table
    `perfume_name` VARCHAR(255) NOT NULL,
    `quantity` INT(11) NOT NULL,
    `price` DECIMAL(10, 2) NOT NULL,
    `total` DECIMAL(10, 2) NOT NULL,
    PRIMARY KEY (`order_item_id`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`)
);


-- Create the Payment table
CREATE TABLE Payment (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_status VARCHAR(50) NOT NULL,
    payment_details TEXT,
    FOREIGN KEY (order_id) REFERENCES `Order`(order_id) ON DELETE CASCADE
);

-- Create the Invoice table
CREATE TABLE Invoice (
    invoice_id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id INT NOT NULL,
    invoice_date DATE NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (payment_id) REFERENCES Payment(payment_id) ON DELETE CASCADE
);

-- Insert sample data into the tables for testing

-- Insert into User table
INSERT INTO User (name, phone_no, address, email)
VALUES 
('John Doe', '123456789', '123 Street, City', 'john.doe@example.com'),
('Jane Smith', '987654321', '456 Avenue, City', 'jane.smith@example.com');

-- Insert into Administrator table
INSERT INTO Administrator (username, email)
VALUES 
('admin1', 'admin1@example.com'),
('admin2', 'admin2@example.com');

-- Insert into Perfume table
ALTER TABLE Perfume ADD COLUMN image VARCHAR(255);

INSERT INTO Perfume (perfume_name, quantity, price, description, image)
VALUES 
('Amber', 50, 49.99, 'A delightful floral scent perfect for casual outings.', 'images/amber.jpeg'),
('Coral', 30, 59.99, 'A refreshing citrus aroma for everyday wear.', 'images/coral.jpeg'),
('Quartz', 20, 79.99, 'A sophisticated woody fragrance for formal events.', 'images/quartz.jpeg');


-- Insert into Inventory table
INSERT INTO Inventory (perfume_id, stock_quantity, restock_date)
VALUES 
(1, 50, '2024-01-01'),
(2, 30, '2024-01-02'),
(3, 20, '2024-01-03');

-- Insert into Cart table
INSERT INTO Cart (user_id, perfume_id, quantity_cart)
VALUES 
(1, 1, 2),
(2, 3, 1);

-- Insert into Favourite table
INSERT INTO Favourite (user_id, perfume_id, total_amount)
VALUES 
(1, 1, 99.98),
(2, 2, 59.99);

-- Insert into Order table
INSERT INTO `Order` (user_id, perfume_id, order_status, order_date)
VALUES 
(1, 1, 'Completed', '2024-01-10'),
(2, 2, 'Pending', '2024-01-11');

-- Insert into Payment table
INSERT INTO Payment (order_id, payment_status, payment_details)
VALUES 
(1, 'Paid', 'Credit Card'),
(2, 'Pending', 'Bank Transfer');

-- Insert into Invoice table
INSERT INTO Invoice (payment_id, invoice_date, total_amount)
VALUES 
(1, '2024-01-12', 99.98),
(2, '2024-01-13', 59.99);

-- Insert into Quiz table
INSERT INTO Quiz (user_id, quiz_details)
VALUES 
(1, '{"scent_intensity": "light", "scent_notes": "floral", "occasion": "casual"}'),
(2, '{"scent_intensity": "strong", "scent_notes": "woody", "occasion": "formal"}');

-- Disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- Drop the dependent tables first
DROP TABLE IF EXISTS Invoice;
DROP TABLE IF EXISTS Payment;
DROP TABLE IF EXISTS `Order`;
DROP TABLE IF EXISTS Favourite;
DROP TABLE IF EXISTS Cart;
DROP TABLE IF EXISTS Quiz;

-- Drop the User table
DROP TABLE IF EXISTS `User`;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Recreate the User table with the new structure
CREATE TABLE `User` (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL, -- Username as a unique identifier
    email VARCHAR(100) UNIQUE NOT NULL,  -- Email as a unique field
    password VARCHAR(255) NOT NULL,      -- Password (hashed for security)
    phone_no VARCHAR(15),                -- Optional phone number
    address TEXT,                        -- Optional address
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Record creation time
);

-- Insert sample data into the updated User table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- Example data
INSERT INTO categories (name) VALUES 
('woody'),
('floral'),
('citrus'),
('fresh');

CREATE TABLE blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Example data
INSERT INTO blogs (title, content, image) VALUES
('How to Make the Scent Last Longer?', 'Discover the best tips for long-lasting fragrances...', 'images/blogs/scent_tips.jpg'),
('Understanding Perfume Notes', 'Learn about the top, middle, and base notes of perfumes...', 'images/blogs/perfume_notes.jpg');

ALTER TABLE `User` ADD role TINYINT(1) NOT NULL DEFAULT 0;
INSERT INTO `User` (username, email, password, role) 
VALUES ('Admin', 'admin@example.com', 'admin123', '1');

DELETE FROM Perfume
WHERE perfume_id = 1,2,3;

ALTER TABLE Perfume
ADD COLUMN category_id INT;

INSERT INTO Perfume (perfume_name, quantity, price, description, category_id, image) VALUES 
('Sandalwood Bliss', 50, 29.99, 'A rich, woody fragrance with notes of sandalwood.', 1, 'images/quartz.jpeg'), 
('Rose Garden', 30, 19.99, 'A delicate floral scent with a touch of rose petals.', 2, 'images/amethyst.jpeg'),
('Lemon Zest', 40, 24.99, 'A refreshing citrus fragrance perfect for summer.', 3, 'images/onyx.jpeg'),
('Ocean Breeze', 25, 34.99, 'A fresh, airy fragrance inspired by the sea.', 4, 'images/ruby.jpeg');

DELETE FROM Perfume
WHERE perfume_id IN (1, 2, 3, 7, 8, 9, 10);

UPDATE Perfume
SET image = 'images/quartz.jpeg'
WHERE perfume_name = 'Sandalwood Bliss';

UPDATE Perfume
SET image = 'images/amethyst.jpeg'
WHERE perfume_name = 'Rose Garden';

UPDATE Perfume
SET image = 'images/onyx.jpeg'
WHERE perfume_name = 'Lemon Zest';

UPDATE Perfume
SET image = 'images/ruby.jpeg'
WHERE perfume_name = 'Ocean Breeze';

UPDATE categories
SET name = CONCAT(UPPER(SUBSTRING(name, 1, 1)), LOWER(SUBSTRING(name FROM 2)))
WHERE name IN ('woody', 'floral', 'citrus', 'fresh');

UPDATE Perfume
SET price = 45.00
WHERE perfume_name = 'Sandalwood Bliss';

UPDATE Perfume
SET price = 45.00
WHERE perfume_name = 'Rose Garden';

UPDATE Perfume
SET price = 45.00
WHERE perfume_name = 'Ocean Breeze';

UPDATE Perfume
SET price = 45.00
WHERE perfume_name = 'Lemon Zest';

INSERT INTO Perfume (perfume_name, quantity, price, description, category_id, image) VALUES 
('Jasmine', 50, 45.00, 'A rich, woody fragrance with notes of sandalwood.', 2, 'images/amber.jpeg'), 
('Ceral Breeze', 30, 45.00, 'A delicate floral scent with a touch of rose petals.', 1, 'images/coral.jpeg'), 
('Sandalwood Smoke', 40, 45.00, 'A refreshing citrus fragrance perfect for summer.', 1, 'images/emerald.jpeg'), 
('Orange Blossom', 25, 45.00, 'A fresh, airy fragrance inspired by the sea.', 3, 'images/lolite.jpeg');