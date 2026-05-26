
CREATE DATABASE IF NOT EXISTS `tour_guide_prishtina`
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

USE `tour_guide_prishtina`;

CREATE TABLE IF NOT EXISTS `users` (
  `id`            INT(11)      NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(50)  NOT NULL,
  `password`      VARCHAR(255) NOT NULL,
  `role`          ENUM('admin','user') NOT NULL DEFAULT 'user',
  `name`          VARCHAR(100) NOT NULL,
  `email`         VARCHAR(100) DEFAULT NULL,
  `phone`         VARCHAR(20)  DEFAULT NULL,
  `profile_image` VARCHAR(255) DEFAULT NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tours` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100) NOT NULL,
  `hours`       DECIMAL(4,1) NOT NULL,
  `price`       DECIMAL(8,2) NOT NULL,
  `spots`       INT(11)      NOT NULL DEFAULT '0',
  `image`       VARCHAR(255) DEFAULT NULL,
  `description` TEXT,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `tours` (`name`, `hours`, `price`, `spots`, `description`) VALUES
('Observatori', 2.0,  2.00,  20, 'Vizito observatorin e Prishtinës dhe shiho qytetin nga lartësia.'),
('Muzeu',       3.0,  7.00,  50, 'Eksploro historinë e Kosovës përmes ekspozitave të muzeuacional.'),
('Katedralja',  1.5,  1.50,  25, 'Vizito katedralen Nënë Tereza – simbol i Prishtinës moderne.'),
('Deep Space',  2.0, 10.00,  20, 'Tur eksklusiv nate me teleskop dhe guidë astronomike.');

CREATE TABLE IF NOT EXISTS `bookings` (
  `id`           INT(11)       NOT NULL AUTO_INCREMENT,
  `user_id`      INT(11)       NOT NULL,
  `tour_id`      INT(11)       NOT NULL,
  `persons`      INT(11)       NOT NULL,
  `total`        DECIMAL(10,2) NOT NULL,
  `booking_date` DATETIME      DEFAULT CURRENT_TIMESTAMP,
  `status`       ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_tour_id` (`tour_id`),
  CONSTRAINT `fk_booking_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_booking_tour` FOREIGN KEY (`tour_id`)
    REFERENCES `tours` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
