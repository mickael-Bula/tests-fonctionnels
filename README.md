- Avoir installé php 8 avec ses dépendances
- Avoir un Compte Github
- Avoir composer, nodeJS et Yarn d'installé
- Lancer un composer install après avoir récupéré le projet ```composer install```
- Installer driver sqlite3 ubuntu ```sudo apt install php8.0-sqlite3 ```
- Installer la base de données 
  - ```php bin/console doctrine:database:create --env=test```
  - ```php bin/console doctrine:schema:create --env=test```
  - ```php bin/console doctrine:migrations:migrate --env=test```
- ```yarn install``` Pour générer les assets css et js via encore
- ```yarn dev``` Pour regénérer les assets automatiquement
- ```symfony server:start```

  __Ne pas oublier de changer dans le fichier ```tests/Controller/DiaryControllerTest.php:21``` l'adresse mail en mettant celle qui est dans ce [lien](https://github.com/settings/emails),
  qui est celle utilisée sur Github__

- Décommentez les lignes 22 et 23 en retirant le `#` dans le ```.env``` les valeurs suivantes que vous récupérez depuis [Github](https://github.com/settings/developers)
- #GITHUB_ID=
- #GITHUB_SECRET=

---
Installation d'une application dans github:
1. Se rendre sur [Github](https://github.com/settings/developers) en cliquant sur le lien
2. Cliquer sur le bouton New OAuth App
3. Remplir le formulaire
   - Application name: food-diary
   - Homepage URL: http://127.0.0.1:8000/
   - Application description : Laisser vide ou y indiquer ce qui vous plaira ;)
   - Authorization callback URL: http://127.0.0.1:8000/auth
4. Clic sur le bouton Register application
5. Nous sommes redirigés vers la page de cette application dans gitHub ou nous pouvons récupérer
les credentials **GITHUB_ID** (qui correspond au Client ID) et le **GITHUB_SECRET** (qui correspond au Client secrets)
qu'il faudra générer en cliquant sur le bouton **Generate a new client secret**
6. Vous copiez ces valeurs dans le .env comme ceci (ces valeurs sont des exemples, copiez les vôtres ;)
   - GITHUB_ID=b6adf978b8aaa4804b47
   - GITHUB_SECRET=dcdcc25c1220c661d0fc8c901d0a569050b14858
7. Avec tout cela vous pouvez vous connecter à l'application

## Installation et connexion à la BDD SQLite

Sur un appareil Windows, j'ai téléchargé le fichier `sqlite-tools-win32-x86-3410200.zip` sur la page SQLite : https://www.sqlite.org/download.html.
Il m'a ensuite suffi de dézipper le contenu du fichier dans un répertoire dédié sous C:\sqlite (cf. https://www.sqlitetutorial.net/download-install-sqlite/).

>NOTE : Pour lancer le moteur SQLite, il faut naviguer jusqu'au répertoire contenant les fichiers, puis de lancer la commande `sqlite3` dans un terminal.

La BDD SQLite est créée lors du lancement des commandes proposées au début de ce README.md. Elles installent une base dans le projet sous `./var/data.db`.

## Troubleshooting

### Problème de certificat à la première connexion
Ceci est un commentaire de ma propre expérience de connexion lors de l'installation de l'appli.
Après avoir suivi les instructions d'authentification OAuth, j'ai rencontré un problème de certificat.
L'erreur signalée était : ![img.png](img.png)

Pour résoudre le problème, j'ai dû installer le certificat demandé, que j'ai téléchargé à l'adresse http://curl.haxx.se/ca/cacert.pem

Après enregistrement du fichier sur mon poste et récupération de son path, j'ai fourni l'information dans le fichier php.ini, sous la clé `curl.cainfo` de la version de php utilisée pour lancer l'application courante (donc ici la version php 8.0.13) :

```ìni
curl.cainfo = C:\Users\bulam\OneDrive\Documents\cacert.pem
```

Après avoir relancé le serveur symfony, j'ai pû me connecter à l'application et être authentifié auprès de l'api de Github.

### Problème de vérification du message affiché après soumission du formulaire sur la page add-new-record

Le message affiché dans le code original n'était pas le bon. J'ai donc fait la modification suivante l.68 de DiaryController :

```php
$this->addFlash('success', $translator->trans('logEntry.added').'.');
```

L'information de modification est disponible dans le fichier `message+intl-icu.yaml`, sous l'entrée logEntry.
Le message original référençait `logEntry.add`.

### Problème `$client->followRedirect()`

Le tutoriel conseille de placer `$client->followRedirects()` juste après l'instanciation du `$client` pour ne pas avoir à ajouter la déclaration à chaque requête entraînant une redirection.
Cela fonctionne, mais il faut bien veiller à appeler la méthode `followRedirects()` au pluriel, `followRedirect()` existant pour les requêtes individuelles.
(https://symfony.com/doc/current/testing.html#making-requests)

## Lancement des tests

Pour rediriger la sortie standard vers un fichier du répertoire public :

```bash
symfony php vendor/bin/phpunit --filter=testHomepageIsUp > public/resultTest.html
```

## Procédure des tests

Les tests étant réalisés en isolation, il faut veiller à ce que le client, ainsi que tout ce qui est nécessaire pour procéder à la requête, soit défini.
Pour éviter la répétition du code, une bonne pratique est de déclarer ces éléments dans la méthode setup.

## Création de mon propre repository Github à partir du tutoriel

Pour conserver l'historique des modifications apportées, j'utilise la procédure récupérée dans un autre tutoriel [Openclassrooms](https://openclassrooms.com/fr/courses/4087056-testez-unitairement-votre-application-php-symfony/7828729-mettez-en-place-travis) :
Il faut commencer par réinitialiser les liens vers le repository original, pour ensuite les renvoyer vers le repository personnel créé pour l'occasion :

```bash
$ git init  # réinitialisation du repo local original afin d'y faire des modifications
$ git remote rm origin  # suppression du lien vers le dépôt git distant original
$ git remote add origin git@github.com:mickael-Bula/tests-fonctionnels.git  # ajout du dépôt nouvellement créé pour l'occasion
$ git branch -M main  # je m'assure que la branche maîtresse se nomme main
$ git push -u origin main # je pousse mes modification sur le repo perso
```
