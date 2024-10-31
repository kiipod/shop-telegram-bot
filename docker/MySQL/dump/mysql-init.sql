CREATE DATABASE IF NOT EXISTS `shop`;

CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED BY 'password';

GRANT ALL PRIVILEGES ON `shop`.* TO 'root'@'%';
GRANT ALL PRIVILEGES ON `shop`.* TO 'root'@'%';

GRANT SELECT  ON `information\_schema`.* TO 'root'@'%';
FLUSH PRIVILEGES;

SET GLOBAL time_zone = 'Europe/Moscow';
