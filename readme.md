


# Installation

cd app
cp .env.example .env
cd ..
docker compose up -d
docker compose exec app bash -c "php artisan key:generate"
docker compose exec app bash -c "php artisan db:seed "

## 1) créer un user admin dans Ayon 

user and password : admin/admin 

http://localhost:5000/

## 2) ajouter un talent dans l'app laravel 

connectez-vous avec ce user:  admin@example.com / password : admin 

http://localhost:8000/talents


Vous pouvez voir les appels vers Ayon dans Telescope :

http://localhost:8000/telescope/client-requests



Note : je n'ai pas mis le role dans le model Talent, car je crois qu'on ne peut pas le mettre dans ayon via l'api users 
