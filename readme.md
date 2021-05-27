# Magic The Gathering

## Réalisation de la commande

* Exécuter la commande suivante pour récupérer une collection ainsi que les cartes associées en fonction du nom de la collection.

``` php bin/console SetCollectionCommand```

Pour voir la liste des collections, taper Entrée sans renseigner le nom d'une collection.

## Les interfaces 

- [page d'accueil avec la liste des collections téléchargées](http://127.0.0.1:8000/)

Pour voir l'ensemble des cartes d'une collection, il vous suffit de suivre le lien 'Voir les cartes' au niveau d'une collection

## Tests

* Pour lancer l'ensemble des tests, il vous suffit d'utiliser la commande :

``` php ./vendor/bin/phpunit```

## Le projet

Ce projet a pour but de récupérer simplement (via une commande Symfony), les collections de cartes disponibles sur l'API https://scryfall.com/docs/api et ensuite afficher sur une page html le résultat.

Il n'y a pas de framework CSS, ni d'utilisation de JS obligatoire.
Les commits sur la branche doivent être clairs et compréhensibles. 

Une fois le développement effectué sur une branche spécifique, il faudra faire une Pull Request sur le dépôt.

## Pré-requis
- PHP 7.4
- Composer
- Git
- Symfony cli (optionnel)


## Installation
- Cloner ce dépôt
- Installer les dépendances avec composer

## Fonctionnalités à ajouter
Actuellement ce projet Symfony ne contient que très peu de composants, il sera donc nécessaire de les ajouter en fonction du besoin de chaques fonctionnalités.

### Récupération d'un set de cartes en fonction de la collection
Dans les cartes Magic, chaque cartes appartient à une collection.

Via une commande Symfony, je souhaite pouvoir récupérer les cartes d'une collection spécifique en fonction de son nom. 
Si jamais il y a plusieurs correspondances, il faudrait que la console propose les résultats et que l'on choisisse la collection à récupérer.

Cette collection doit être stockée en base de données. 
Les entités de base attendues : 
- Collection
- Carte 


Les cartes doivent avoir une relation avec les collections. 
Chaque collection doit avoir son code, date de sortie, nom, icone svg d'enregistré.
Chaque carte doit avoir son nom, image, type, couleur, description et nom de l'artiste d'enregistré.

Les images des cartes doivent être stocké dans le dossier public/cards/ et le chemin relatif doit être stocké en BDD pour pouvoir retourner le chemin de l'image.


**Partie optionnelle**

Les entités complémentaires :
- Les couleurs de cartes possibles
- Les types de personnages (Sorcery etc.)

Chaque carte a une relation obligatoire avec une couleur et un type


### Afficher les collections sur une page HTML
Les différentes collections doivent être visibles sur une page.

On peut sélectionner une collection pour afficher toutes les cartes liées à celle-ci.

Sur la page des cartes il doit être possible de filtrer les cartes : 
- soit par leur nom
- soit par leur couleur
- soit par leur type

Sur chaque carte on doit pouvoir afficher l'image, le nom, le type de carte, sa couleur, la description ainsi que le nom de l'artiste.
