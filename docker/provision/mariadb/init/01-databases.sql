# create databases
CREATE DATABASE IF NOT EXISTS `groshy-dev`;
CREATE DATABASE IF NOT EXISTS `groshy-test`;

# create groshy user and grant rights
CREATE USER 'groshy'@'localhost' IDENTIFIED BY 'groshy';
GRANT ALL ON *.* TO 'groshy'@'%';