ORANGE='\033[0;33m'

echo -e "${ORANGE}RUN TESTS${NOCOLOR}"
php -d memory_limit=-1 vendor/bin/phpunit --configuration phpunit.xml