mkdir var
mkdir log
mkdir var\log
docker-compose -p web-skeleton up -d --build --force-recreate
docker exec -it web-skeleton-php /bin/bash
