# Rendu-APi-MB
 groupe  : Mehdi Bellam

 # Rendu-APi-MB

Ce document README décrit les différentes routes disponibles dans le `CommandController.php` pour l'API de gestion des commandes.

## Routes

### Récupérer une Commande Spécifique par ID

- **URL** : `/api/commandes/{id}`
- **Méthode** : `GET`
- **Description** : Permet de récupérer une commande spécifique en utilisant son ID.
- **Réponse en cas de succès** : Un objet JSON représentant la commande demandée.
- **Réponse en cas d'échec** : Un message d'erreur avec le statut HTTP 404 si la commande n'est pas trouvée.

### Lister les Commandes en Cours

- **URL** : `/api/commandes/en-cours`
- **Méthode** : `GET`
- **Description** : Liste toutes les commandes qui sont actuellement en cours de préparation.
- **Réponse en cas de succès** : Un tableau d'objets JSON représentant les commandes en cours.
- **Réponse en cas d'échec** : Un message d'erreur avec le statut HTTP 403 si l'utilisateur n'a pas les droits nécessaires.

### Assigner une Commande

- **URL** : `/api/commandes/{id}/assign`
- **Méthode** : `PATCH`
- **Description** : Assigner une commande à un utilisateur (barman) en fonction de l'ID de commande.
- **Réponse en cas de succès** : Un message confirmant l'assignation de la commande.
- **Réponse en cas d'échec** : Un message d'erreur avec le statut HTTP 403 si l'utilisateur n'a pas le rôle requis.
- 
### Payer une Commande

- **URL** : `/api/commandes/pay`
- **Méthode** : `POST`
- **Description** : Permet de marquer une commande comme payée en fournissant l'ID de la commande.
- **Réponse en cas de succès** : Un message confirmant que la commande a été payée.
- **Réponse en cas d'échec** : Un message d'erreur avec le statut HTTP 400 si l'ID de la commande est manquant ou incorrect, ou le statut HTTP 404 si la commande n'est pas trouvée.

### Mettre à Jour la Boisson d'une Commande

- **URL** : `/api/commandes/{id}/update-boisson`
- **Méthode** : `PATCH`
- **Description** : Permet de mettre à jour la boisson d'une commande spécifique en utilisant son ID.
- **Réponse en cas de succès** : Un message confirmant la mise à jour de la boisson dans la commande.
- **Réponse en cas d'échec** : Un message d'erreur avec le statut HTTP 403 si l'utilisateur n'a pas le rôle `ROLE_SERVEUR`, le statut HTTP 400 si l'ID de la boisson est manquant ou incorrect, ou le statut HTTP 404 si la commande ou la boisson n'est pas trouvée.

### Créer une Commande

- **URL** : `/api/commandes/create`
- **Méthode** : `POST`
- **Description** : Permet de créer une nouvelle commande avec les détails spécifiés dans le corps de la requête.
- **Réponse en cas de succès** : Un objet JSON représentant la commande créée.
- **Réponse en cas d'échec** : Un message d'erreur avec le statut HTTP 400 si les données nécessaires à la création de la commande sont manquantes ou incorrectes.

## Sécurité

Les routes `/api/commandes/en-cours` et `/api/commandes/{id}/assign` nécessitent que l'utilisateur soit authentifié et possède les rôles appropriés (`ROLE_BARMAN` ou `ROLE_PATRON`) pour accéder aux ressources.

## Utilisation

Pour utiliser ces routes, vous devez envoyer des requêtes HTTP aux URL spécifiées avec les méthodes appropriées. Assurez-vous d'inclure tout jeton d'authentification requis dans l'en-tête de votre requête pour les routes sécurisées.
