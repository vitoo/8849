


## 1) Installation

Attention : ces conteneurs vont utiliser les ports local : 5000, 8000, 5432, 6379, 3306 

Lancez ces commandes dans l'ordre :

    cd app
    cp .env.example .env
    cd ..
    
    docker compose run app bash -c "composer install"
    docker compose run app bash -c "php artisan key:generate"
    docker compose run app bash -c "php artisan migrate --seed"
    docker compose run app bash -c "npm i ; npm run build"
    docker compose up -d


## 2) Créer un user admin dans Ayon 

user and password : admin/admin 

http://localhost:5000/

## 3) Ajouter un talent dans l'app laravel 

Connectez-vous à l'app laravel : http://localhost:8000/talents

Avec ce user admin :

    email : admin@example.com
    password : admin 


Vous pouvez voir les appels vers Ayon dans Telescope :

http://localhost:8000/telescope/client-requests


Note : je n'ai pas mis le role dans le model Talent, car la doc d'Ayon ne montre pas comment gérer ça via l'url /api/users  


## Contenu

    app/ 
    AyonSyncJob
    TalentController
    Vue : 
    app/resources/js/pages/Talents
    test :
    TalentControllerTest

    packages/laravel-ayon-connector/
    AyonService
    AyonServiceTest
