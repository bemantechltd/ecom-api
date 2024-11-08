# API Snop Store

## Setup from scratch for the first time 

NB. using orbstack 

1. cd to project root
2. run `docker-compose up -d --build`
3. run `docker-compose exec app composer install`
4. run `docker-compose exec app php artisan key:generate`
5. run `docker-compose exec app php artisan migrate`
6. access api on `webserver` container ip or domain

for now on to start project just step (1) # ecom-api
