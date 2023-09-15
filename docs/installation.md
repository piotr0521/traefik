### Prerequisites
- Make sure you can checkout the main repo: git@bitbucket.org:wizardz/groshy.git

This project does not use anything special, simple database, webserver and php installed locally should be good enough:
- php 8.1. It uses some new features of php 8.1
- mariaDb 10.x. The project is not tested on MySQL or previous versions of mariaDb

### Installation
Current doc assumes that you are running php and webserver locally and use Docker for the database and emails
- Install `git`
- Make sure you have `php` 8.2+ locally installed
- Install `docker-compose`
- Install [Symfony CLI](https://symfony.com/download).
- Install composer
- Make sure your setup is ready for Symfony
```shell
symfony check:requirements
```
- Clone the repo git@bitbucket.org:wizardz/groshy.git
```shell
git clone git@bitbucket.org:wizardz/groshy.git
```

### Running the project
- Install all dependencies
```shell
symfony composer install
```
- Install Node modules
```shell
npm install
```
- Start docker compose, it takes time to build some containers for the first time
```
docker-compose up
```
You can also start it in the background mode
```
docker-compose up -d
```
- Next URLs should be available after that
 - `http://localhost:8026/` - phpMyAdmin
 - `http://localhost:8025/` - MailHog
 - `http://localhost:8080/dashboard/#/` - traffik dashboard
- Start Symfony webserver
```shell
symfony server:start
```
- Create dev database
```shell
./bin/reset-dev.sh
```
This command does not work on Windows, you can manually run console commands from the file. Please feel free to submit a PR to fix this
- Next URLs should be available after that:
 - `http://localhost:8000/` - website
 - `http://localhost:8000/api` - API documentation. It requires authorization, use user2/user2 or any user from fixtures
- Create test database
```shell
./bin/reset-test.sh
```
- Make sure all tests pass
```shell
./bin/phpunit
```