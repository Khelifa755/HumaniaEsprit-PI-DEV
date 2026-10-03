# 🚀 Users Table Consolidation Migration

> Consolider les utilisateurs : pointer tous les services sociaux vers la table `utilisateur` au lieu d'une table `users` dupliquée.

---

## 📋 Fichiers générés

```
src/Entity/Users.php                          ← Entité modifiée (pointant sur 'utilisateur')
migrations/Version20260412000000.php          ← Migration Doctrine
migrations_manual/migration_users_consolidation.sql  ← Script SQL manuel
MIGRATION_USERS_DOCUMENTATION.md              ← Documentation complète
scripts/pre_migration_backup.sh               ← Sauvegarde avant migration
scripts/post_migration_verify.sh              ← Vérification après migration
```

---

## ⚡ Quick Start

### 1️⃣ Sauvegarder les données (OBLIGATOIRE)

```bash
# Linux/Mac
bash scripts/pre_migration_backup.sh localhost root password humania_full

# Windows (Git Bash/WSL)
bash scripts/pre_migration_backup.sh localhost root password humania_full
```

✅ Crée un backup complet dans `backups/pre_migration_*/`

### 2️⃣ Exécuter la migration

```bash
# Option A : Via Doctrine CLI (recommandé)
php bin/console doctrine:migrations:migrate

# Option B : SQL manuel
mysql -h localhost -u root -p humania_full < migrations_manual/migration_users_consolidation.sql
```

### 3️⃣ Vérifier la migration

```bash
# Linux/Mac
bash scripts/post_migration_verify.sh localhost root password humania_full

# Windows (Git Bash/WSL)
bash scripts/post_migration_verify.sh localhost root password humania_full
```

✅ Vérifie que tout s'est bien passé

### 4️⃣ Tester l'application

```bash
# Exécuter les tests
php bin/phpunit

# Vérifier les features sociales manuellement
# - Créer une publication
# - Ajouter un commentaire
# - Réagir avec un emoji
# - Voir les notifications
```

---

## 📊 Avant & Après

### ❌ Avant (problématique)

```
utilisateur (auth/RH)  ←───┐
                           ├─ Même IDs (3 = 3)
users (social media)   ←───┘

publicaation ──fk──> users
commentaire ──fk──> users
reaction ───fk──> users
notification ──fk──> users
... 10+ autres tables
```

**Problèmes :**

- Doublon de données
- Synchronisation manuelle requise
- Source de vérité ambiguë

### ✅ Après (consolidé)

```
utilisateur (UNIQUE - auth + social)
    ↑
    └─ fk ── publication
    └─ fk ── commentaire
    └─ fk ── reaction
    └─ fk ── notification
    └─ fk ── ... (11 autres tables)
```

**Avantages :**

- Source unique
- Pas de duplication
- Maintenance simplifiée
- Prêt pour Symfony Security

---

## 🔍 Points clés de la migration

### 1. Entité Users remappée

L'entité `Users` pointe maintenant sur `utilisateur` :

```php
#[ORM\Table(name: 'utilisateur')]
class Users {
    #[ORM\Column(name: 'prenom')]
    private string $firstName;        // prenom → firstName

    #[ORM\Column(name: 'nom')]
    private string $lastName;         // nom → lastName

    #[ORM\Column(name: 'pdp')]
    private ?string $avatarUrl;       // pdp → avatarUrl

    #[ORM\Column(name: 'statut')]
    private string $status;           // statut ('Actif'/'Inactif')
}
```

### 2. API sauvegardée

**Aucun changement de code requis** — tous les getters/setters fonctionnent :

```php
$user->getFirstName();      // ✅ Fonctionne (mappe prenom)
$user->getLastName();       // ✅ Fonctionne (mappe nom)
$user->getAvatarUrl();      // ✅ Fonctionne (mappe pdp)
$user->getIsActive();       // ✅ Fonctionne (dérivé de statut)
```

### 3. Tables affectées

12 tables sociales sont mises à jour pour pointer vers `utilisateur` :

- publication, commentaire, reaction
- savedpost, share, notification
- groupmember, groups, userprofile
- follow

Plus 1 table supprimée : `users`

---

## 🛡️ Sécurité & Rollback

### Backup automatique

```bash
ls backups/pre_migration_*/
   ├── full_dump.sql              ← Dump complet
   ├── users_backup.sql
   ├── publication_backup.sql
   ├── ... (une pour chaque table)
   └── consistency_report.txt
```

### Restaurer après échec

```bash
mysql -h localhost -u root -p humania_full < backups/pre_migration_XXXXX/full_dump.sql
```

### Rollback de la migration Doctrine

```bash
php bin/console doctrine:migrations:migrate Version20260411999999
```

---

## ✅ Liste de vérification

Avant d'exécuter la migration :

- [ ] Backup complet created (`pre_migration_backup.sh`)
- [ ] Consistency check passed (aucun orphaned FK)
- [ ] Application shutdown (ou en maintenance mode)
- [ ] Équipe informée (mail/slack)

Après la migration :

- [ ] `users` table supprimée
- [ ] Tous les FKs pointent vers `utilisateur`
- [ ] Post-migration checks passed
- [ ] Tests unitaires passent
- [ ] Tests API passent
- [ ] Features sociales testées manuellement

---

## 📞 Troubleshooting

### ❌ "Unknown table users"

C'est normal si la migration a réussi. Les controllers continuent de fonctionner parce que Doctrine mappe maintenant sur `utilisateur`.

### ❌ "Foreign key constraint fails"

Votre base a des données orphaned. Exécutez le backup script avec `--check-only` :

```bash
bash scripts/pre_migration_backup.sh
# Regardez consistency_report.txt
```

### ❌ "Column 'firstName' doesn't exist"

Vous êtes dans la mauvaise table. Vérifiez que Doctrine mappe sur `utilisateur` (voir Users.php).

### ❌ Migration échouée

Restaurez depuis le backup :

```bash
mysql -h localhost -u root -p humania_full < backups/pre_migration_XXXXX/full_dump.sql
```

---

## 📚 Documentation complète

Pour un détail complet, voir : [MIGRATION_USERS_DOCUMENTATION.md](MIGRATION_USERS_DOCUMENTATION.md)

**Contenu :**

- ✅ Mapping détaillé des colonnes
- ✅ Explication de chaque phase
- ✅ Tables affectées
- ✅ Vérifications post-migration
- ✅ Impact sur le code existant

---

## 🎯 Prochaines étapes après migration

1. **Supprimer `FAKE_USER_ID`** dans les controllers

   ```php
   // Avant
   private const FAKE_USER_ID = 3;

   // Après : utiliser Symfony Security
   $user = $this->getUser();
   ```

2. **Implémenter Symfony Security** pour authentification réelle

3. **Optimiser les notifications** avec WebSocket/Redis

4. **Ajouter des tests** pour la consolidation

---

## 📝 Version

- **Date** : 2026-04-12
- **Risque** : ⚠️ Bas (suppression de table dupliquée)
- **Downtime** : ⏱️ < 1 minute
- **Rollback** : ✅ Possible (sauvegarde fournie)

---

**Avez-vous des questions ?** Consultez la documentation complète ou creez une issue.
