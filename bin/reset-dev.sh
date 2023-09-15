#!/bin/sh

set -x
set -e

echo "Remove all doctrine migrations, database and images"
set +e
bin/console doctrine:database:drop --if-exists --force -q
set -e

echo "Create database, generate first migration and run it"
bin/console doctrine:database:create --if-not-exists -q
bin/console doctrine:schema:create -q
php -d memory_limit=1G bin/console doctrine:fixtures:load -n