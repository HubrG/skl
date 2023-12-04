# Fonctionnement technique de Scrilab

## Deployer sur Cloudways

_Tout est automatisé via le fichier `.github/workflows/main.yaml`, et les credentials sont enregistrés dans les_ **secrets keys** _de GitHub : https://github.com/HubrG/skl/settings/secrets/actions_

- `git add .`
- `git commit -m "message"`
- ``git push origin main`
- Vérifier l'état du déploiement ici : https://github.com/HubrG/skl/actions
- Tous les credentials API sont stockés dans les **secrets keys** de GitHub et automatiquement enregistrés dans le `.env.local` : https://github.com/HubrG/skl/settings/secrets/actions

#### Pour modifier les variables d'environnement sur _.env.local_ :

1. `nano .env.local`
2. Ajouter les variables d'environnement
3. Enregistrer : `CTRL+S`
4. Appuyer sur **entrer**

## DOCKER

### Docker Build - local

1. `docker build . -f ./docker/Dockerfile -t scrilab`
2. récupérer l’ID de l’image, et mettre à jour docker-compose
3. `cd docker`
4. `docker-compose -up -d`
5. On entre dans le docker : `docker exec -it docker-app-1 /bin/bash`
6. Et on peut lancer des commandes `php bin/console`

#### Docker push - à chaque nouvelle version de mon conteneur :

1. `docker tag scrilab:latest hubrg/scrilab:app-1-v1.x`
2. `docker tag mysql:8.0 hubrg/scrilab:database-1-v1.x`
3. `docker tag phpmyadmin/phpmyadmin:5.0.4 hubrg/scrilab:phpmyadmin-1-v1.x`

##### Envoi sur Docker Hub

4. `docker push hubrg/scrilab:app-1-v1.x`
5. `docker push hubrg/scrilab:database-1-v1.x `
6. `docker push hubrg/scrilab:phpmyadmin-1-v1.x`

#### Envoi sur GCP

7. `docker tag scrilab:latest gcr.io/scrilab/app-1-v1.0`
8. `docker push gcr.io/scrilab/app-1-v1.0`
9. `docker tag mysql:8.0 gcr.io/scrilab/database-1-v1.0`
10. `docker push gcr.io/scrilab/database-1-v1.0`
11. `docker tag phpmyadmin/phpmyadmin:5.0.4 gcr.io/scrilab/phpmyadmin-1-v1.0`
12. `docker push gcr.io/scrilab/phpmyadmin-1-v1.0`

#### Pour le cloud

1. `docker buildx build . -f ./docker/docker-cloud/Dockerfile --platform linux/amd64 -t scrilab-cloud`
2. `docker tag scrilab-cloud:latest gcr.io/scrilab/app-1-v1.0-cloud`
3. `docker push gcr.io/scrilab/app-1-v1.0-cloud`
