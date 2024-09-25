mkdir var
mkdir log
mkdir var\log
docker-compose -p web-conference up -d --build --force-recreate
docker exec -it web-conference-php /bin/bash
