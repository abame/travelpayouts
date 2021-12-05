NOCOLOR='\033[0m'
ORANGE='\033[0;33m'

echo -e "${ORANGE}ANALYZING PSALM${NOCOLOR}"
vendor/bin/psalm src/ -c psalm.xml --threads=5 --no-cache  --show-info=true

echo -e "${ORANGE}ANALYZING CODE SNIFFER${NOCOLOR}"
vendor/bin/phpcs

echo -e "${ORANGE}ANALYZING PHPSTAN${NOCOLOR}"
vendor/bin/phpstan analyse -c phpstan.neon src/

echo -e "${ORANGE}ANALYZING MESS DETECTOR${NOCOLOR}"
vendor/bin/phpmd src/ xml phpmd.xml