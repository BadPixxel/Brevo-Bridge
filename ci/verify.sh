################################################################################
#
#  Copyright (C) BadPixxel <www.badpixxel.com>
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
#
#  For the full copyright and license information, please view the LICENSE
#  file that was distributed with this source code.
#
################################################################################

set -e

################################################################################
# Docker Compose Container you want to check
CONTAINERS="php-8.2,php-8.1"
################################################################################
# Start Docker Compose Stack
echo '===> Start Docker Stack'
docker compose up -d
######################################
# Run Grumphp Test Suites Locally
php vendor/bin/grumphp run --testsuite=travis
php vendor/bin/grumphp run --testsuite=csfixer

######################################
# Walk on Docker Compose Container
for ID in $(echo $CONTAINERS | tr "," "\n")
do
    echo "===> Checks Php $ID"
    docker compose exec $ID pwd
    # Ensure Git is Installed
    docker compose exec $ID apt install git -y
    docker compose exec $ID composer update -q || docker compose exec $ID composer update
    # Run Grumphp Test Suites
    docker compose exec $ID php vendor/bin/grumphp run --testsuite=travis
    docker compose exec $ID php vendor/bin/grumphp run --testsuite=phpstan
done
