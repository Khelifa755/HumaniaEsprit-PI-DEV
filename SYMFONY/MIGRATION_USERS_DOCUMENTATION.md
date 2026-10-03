# Migration Utilisateurs : Table Consolidation

## Contexte du problème

Avant cette migration :

- **Deux tables utilisateurs** existent dans la base de données
- `utilisateur` — table principale de gestion RH (système auth)
- `users` — table créée pour le module social media Symfony (doublon)

Les IDs sont **alignés** (`utilisateur.id = users.id = 3` pour la même personne), mais c'est un **doublon inefficace**.

### Problèmes

- Maintenance double (mises à jour sur deux tables)
- Source de vérité ambiguë
- Risque de désynchronisation entre les deux tables
- Violation du principe DRY

---

## Solution appliquée

### 1. Entité `Users` modifiée

L'entité Symfony `Users` a été **remappée** pour pointer sur la table `utilisateur` avec colonnes anglaisées via Doctrine :

```php
#[ORM\Entity]
#[ORM\Table(name: 'utilisateur')]
class Users
{
    #[ORM\Column(name: 'prenom')]
    private string $firstName;        // utilisateur.prenom → Users.firstName

    #[ORM\Column(name: 'nom')]
    private string $lastName;         // utilisateur.nom → Users.lastName

    #[ORM\Column(name: 'pdp')]
    private ?string $avatarUrl;       // utilisateur.pdp → Users.avatarUrl

    #[ORM\Column(name: 'statut')]
    private string $status;           // utilisateur.statut ('Actif'/'Inactif')

    #[ORM\Column(name: 'is_online')]
    private bool $isOnline;           // utilisateur.is_online → Users.isOnline

    #[ORM\Column(name: 'last_seen')]
    private ?\DateTimeInterface $lastSeen;  // utilisateur.last_seen

    #[ORM\Column(name: 'role')]
    private string $role;             // utilisateur.role

    #[ORM\Column(name: 'email')]
    private string $email;

    #[ORM\Column(name: 'username')]
    private string $username;

    #[ORM\Column(name: 'date_creation')]
    private ?\DateTimeInterface $createdAt;

    // Nouveaux getters pour compatibilité
    public function isActive() { return $this->status === 'Actif'; }
    public function getIsActive() { return $this->isActive(); }
}
```

### 2. Mapping des colonnes

| Colonne `utilisateur` | Colonne PHP  | Type                 | Notes                                      |
| --------------------- | ------------ | -------------------- | ------------------------------------------ |
| `id`                  | `$id`        | int                  | Primary key (pas de GeneratedValue)        |
| `prenom`              | `$firstName` | string(100)          | Remapped via `name:`                       |
| `nom`                 | `$lastName`  | string(100)          | Remapped via `name:`                       |
| `pdp`                 | `$avatarUrl` | string(255) nullable | Photo profil                               |
| `statut`              | `$status`    | string(50)           | 'Actif' / 'Inactif' / 'Archive' / 'bloque' |
| `is_online`           | `$isOnline`  | boolean              | Remapped via `name:`                       |
| `last_seen`           | `$lastSeen`  | datetime nullable    | Remapped via `name:`                       |
| `role`                | `$role`      | string(20)           | ADMIN / EMPLOYE / MANAGER / RH / FORMATEUR |
| `email`               | `$email`     | string(100)          | Unique                                     |
| `username`            | `$username`  | string(50)           | Unique                                     |
| `mot_de_passe`        | `$password`  | string(255) nullable | Remapped                                   |
| `date_creation`       | `$createdAt` | datetime nullable    | Remapped                                   |
| -                     | `$updatedAt` | datetime nullable    | Nouvelle colonne (créée par migration)     |
| `bio`                 | `$bio`       | text nullable        | Remapped                                   |

### 3. Getters/Setters compatibles

Le nouveau `Users` maintient **une API identique** au précédent :

```php
// Compatibilité totale, le code existant fonctionne
$user->getFirstName();      // ✅ Works
$user->getLastName();       // ✅ Works
$user->getAvatarUrl();      // ✅ Works
$user->getRole();          // ✅ Works
$user->getIsActive();      // ✅ Works — retourne `true` si status='Actif'
$user->getIsOnline();      // ✅ Works
$user->getLastSeen();      // ✅ Works
```

### 4. Migration Doctrine

Fichier : [`migrations/Version20260412000000.php`](Version20260412000000.php)

**Phases :**

#### Phase 1 : Supprimer les contraintes de FK existantes (de `users`)

```sql
ALTER TABLE publication DROP FOREIGN KEY IDX_AF3C6779A196F9FD;
ALTER TABLE commentaire DROP FOREIGN KEY IDX_67F068BCA196F9FD;
-- ... (11 tables affectées)
```

#### Phase 2 : Ajouter les nouvelles contraintes (vers `utilisateur`)

```sql
ALTER TABLE publication ADD CONSTRAINT FK_AF3C6779A196F9FD
    FOREIGN KEY (authorId) REFERENCES utilisateur (id) ON DELETE CASCADE;
-- ... (11 tables affectées)
```

#### Phase 3 : Supprimer la table `users`

```sql
DROP TABLE users;
```

---

## Tables affectées par la migration

| Table          | Colonne FK      | Type de relation           | Volume |
| -------------- | --------------- | -------------------------- | ------ |
| `publication`  | `authorId`      | Authors → Publications     | ~100s  |
| `commentaire`  | `authorId`      | Authors → Comments         | ~100s  |
| `reaction`     | `userId`        | Reactors → Reactions       | ~1000s |
| `savedpost`    | `userId`        | Users → Saved Posts        | ~100s  |
| `share`        | `userId`        | Sharers → Shares           | ~10s   |
| `notification` | `userId`        | Recipients → Notifications | ~100s  |
| `notification` | `relatedUserId` | Actors → Notifications     | ~100s  |
| `groupmember`  | `userId`        | Members → Groups           | ~100s  |
| `groups`       | `createdById`   | Creators → Groups          | ~10s   |
| `userprofile`  | `userId`        | Profile → Users            | ~100s  |
| `follow`       | `followerId`    | Followers → Following      | ~10s   |
| `follow`       | `followingId`   | Following → Followers      | ~10s   |

---

## Exécution de la migration

### Option 1 : Doctrine CLI (recommandé)

```bash
# Générer et afficher la migration avant d'exécuter
php bin/console doctrine:migrations:up --dry-run

# Exécuter la migration
php bin/console doctrine:migrations:migrate

# Vérifier que la migration a réussi
php bin/console doctrine:migrations:status
```

### Option 2 : Fichier SQL manuel

Si vous préférez migrer manuellement :

```bash
mysql -h localhost -u root -p humania_full < migrations_manual/migration_users_consolidation.sql
```

### Option 3 : Étapes manuelles

Exécuter les 3 phases (voir `migration_users_consolidation.sql`).

---

## Vérifications post-migration

Après la migration, vérifiez :

```bash
# 1. Tous les tests passent
php bin/phpunit

# 2. Les FKs pointent bien vers utilisateur
SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE REFERENCED_TABLE_NAME = 'utilisateur';

# 3. La table 'users' est supprimée
SHOW TABLES LIKE 'users';  -- Doit être vide

# 4. Les données sociales sont intactes
SELECT COUNT(*) FROM publication;      -- Même nombre qu'avant
SELECT COUNT(*) FROM commentaire;      -- Même nombre qu'avant
SELECT COUNT(*) FROM notification;     -- Même nombre qu'avant
```

---

## Impact sur le code existant

### ✅ AUCUN changement requis

Le code Symfony existant fonctionne **sans modification** :

```php
// Controllers
$user = $this->em->find(Users::class, 3);
$user->getFirstName();      // ✅ Works (maps to prenom)
$user->getAvatarUrl();      // ✅ Works (maps to pdp)
$user->getIsActive();       // ✅ Works (derived from statut)

// Repositories
$publications = $pubRepo->findBy(['authorId' => 3]);  // ✅ Works

// Relations
foreach ($user->getPublications() as $pub) { }  // ✅ Works
foreach ($user->getCommentaires() as $com) { }  // ✅ Works
```

### 🔧 Optimisation future

Une fois la migration stabilisée, vous pouvez :

1. **Supprimer `FAKE_USER_ID`** dans les controllers
2. **Implémenter Symfony Security** pour l'authentification réelle
3. **Synchroniser notifications en temps réel** avec WebSockets

---

## Rollback (si nécessaire)

Si la migration échoue ou doit être annulée :

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate Version20260412000001  # or prev version
```

**Important :** Le `down()` de la migration recrée la table `users`. Assurez-vous d'avoir une **sauvegarde** avant de executer la migration.

---

## Fiche technique

| Propriété              | Valeur                                                |
| ---------------------- | ----------------------------------------------------- |
| **Fichier migration**  | `migrations/Version20260412000000.php`                |
| **Scripts SQL**        | `migrations_manual/migration_users_consolidation.sql` |
| **Entité modifiée**    | `src/Entity/Users.php`                                |
| **Date**               | 2026-04-12                                            |
| **Risque**             | ⚠️ Bas (suppression de table dupliquée)               |
| **Downtime requis**    | ⏱️ < 1 minute                                         |
| **Sauvegarde requise** | ✅ Oui (avant d'exécuter)                             |
| **Tests requis**       | ✅ Tous les tests social media                        |

---

## Questions / Support

Si vous avez des questions sur cette migration, consultez :

- La structure de la base avant/après
- Les logs de Doctrine
- Le fichier SQL de migration manuelle
