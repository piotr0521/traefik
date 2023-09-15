#!/bin/sh

set -x
set -e

echo "Remove all doctrine migrations, database and images"
set +e
bin/console doctrine:database:drop --if-exists --force --env=test -q
set -e

echo "Create database, generate first migration and run it"
bin/console doctrine:database:create --if-not-exists --env=test -q
bin/console doctrine:schema:create --env=test -q
php -d memory_limit=1G bin/console doctrine:fixtures:load --env=test -n