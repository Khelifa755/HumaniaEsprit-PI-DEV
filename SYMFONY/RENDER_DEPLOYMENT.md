# Deploy Humania on Render

## Important before deployment

1. Rotate every credential previously present in `.env`. Treat those values as compromised because they were committed to Git history. Rotate Zoom, Google OAuth, Brevo SMTP/API, Supabase, reCAPTCHA, Groq, and YouTube keys.
2. This application currently uses MySQL-specific Doctrine migrations (`AUTO_INCREMENT`, MySQL collations, `ENUM`, and `LONGTEXT`). Render PostgreSQL is not compatible with the current migration set. Use an external MySQL-compatible provider, or rewrite and test every migration for PostgreSQL before switching databases.
3. Uploaded files under `public/uploads` are local filesystem data. Render services have ephemeral filesystems. Use object storage (Supabase Storage/S3-compatible storage) before relying on uploads in production, or attach a Render persistent disk and accept its single-instance limitation.

## 1. Prepare the repository

The Symfony application is in the `SYMFONY` subdirectory. Commit and push these files:

- `SYMFONY/render.yaml`
- `SYMFONY/Dockerfile`
- `SYMFONY/.env` with only local placeholders
- `SYMFONY/RENDER_DEPLOYMENT.md`

Do not commit real secrets. Keep `.env.local` out of Git.

## 2. Create the database

Create a MySQL-compatible production database with your database provider. Record its private connection string in this form:

```text
mysql://USER:PASSWORD@HOST:3306/DATABASE?charset=utf8mb4
```

URL-encode special characters in the username or password. Take a backup of any existing Humania database before running migrations.

## 3. Create the Render service

1. In Render, select **New > Blueprint**.
2. Connect the Git repository and select the branch to deploy.
3. If the repository root is the parent directory, `render.yaml` uses `rootDir: SYMFONY`. If you connect a repository whose root is already `SYMFONY`, remove `rootDir`, `dockerfilePath: ./Dockerfile`, and `dockerContext: ./` adjustments as appropriate.
4. Review the service and deploy it.
5. The Docker image runs Apache with Symfony's `public/` directory as the document root.

The service health check is `/`, and Render supplies the public port through its Docker runtime. Apache listens on port 80 inside the container.

## 4. Configure environment variables

`render.yaml` creates the complete variable list. Set every `sync: false` value in the Render dashboard under **Environment**.

Required core variables:

| Variable | Value |
|---|---|
| `APP_ENV` | `prod` |
| `APP_DEBUG` | `0` |
| `APP_SECRET` | Generate a new long random value; Render can generate it |
| `DATABASE_URL` | MySQL-compatible production URL |
| `APP_DEFAULT_TIMEZONE` | `Europe/Paris` or your business timezone |
| `PART_EVENEMENT_STATIC_EMPLOYE_ID` | Existing employee ID, if required |

Email variables:

- `MAILER_DSN`: Brevo SMTP DSN, URL-encoded if credentials contain reserved URL characters.
- `MAILER_FROM`
- `MAILER_FROM_NAME`
- `EMAIL_RH_NOTIFICATIONS`
- `BREVO_API_KEY`
- `BREVO_VERIFY_SSL=true`

Third-party variables used by the application:

- Zoom: `ZOOM_ACCOUNT_ID`, `ZOOM_CLIENT_ID`, `ZOOM_CLIENT_SECRET`, `ZOOM_USER_ID`
- Google OAuth: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`
- Supabase: `SUPABASE_URL`, `SUPABASE_ANON_KEY`
- reCAPTCHA: `RECAPTCHA_SITE_KEY`, `RECAPTCHA_SECRET_KEY`
- AI/video integrations: `GROQ_API_KEY`, `YOUTUBE_API_KEY`

Set `MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0`. The current Messenger configuration uses synchronous delivery and a Doctrine failure transport; it does not require a worker for the current deployment.

## 5. Update provider callbacks and allowed origins

After the first deploy, copy the Render URL and update:

- Google OAuth authorized redirect URI to the application's actual `/connect/google/check` route.
- Google OAuth authorized JavaScript origins to the Render HTTPS origin.
- reCAPTCHA allowed domains to the Render hostname and final custom domain.
- Supabase URL/origin policies if Supabase is used by browser-facing flows.
- Zoom webhook/redirect settings if configured in the Zoom application.
- Brevo sender/domain authentication and sender address.

Use HTTPS URLs only in production.

## 6. Run database migrations

After setting `DATABASE_URL`, run migrations once from the Render Shell or an equivalent controlled release environment:

```bash
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
```

Verify the migration status:

```bash
php bin/console doctrine:migrations:status --env=prod
```

Do not run `doctrine:schema:update --force` in production. Review migrations and take a database backup before every release that changes schema.

## 7. Validate the deployment

Check the Render logs for a clean Apache start, then verify:

```bash
php bin/console about --env=prod
php bin/console lint:container --env=prod
php bin/console debug:router --env=prod
```

Manually test login, Google OAuth, password reset email, reCAPTCHA, MFA, uploads, PDF generation, Zoom meetings, and every database-backed module. Confirm that sensitive values never appear in logs.

## 8. Production operations

- Configure Render automatic deploys only from the protected production branch.
- Keep database backups and test restoration separately.
- Store uploads outside the container filesystem; a redeploy can delete local uploads.
- Add a persistent disk only if the app must remain single-instance and local uploads are temporary.
- Monitor stderr logs and external API quotas.
- Change `APP_SECRET` only during a planned session invalidation because it invalidates remember-me cookies.
- Use Render Secret Files only for files that genuinely must be mounted; environment variables are sufficient for the current application.

## Known deployment blockers

- Existing migrations are MySQL-specific; PostgreSQL is not a drop-in replacement.
- `public/uploads` is not durable on a normal Render web service.
- The committed historical `.env` exposed credentials; rotate all previously exposed keys before production.
- No separate queue worker is defined because Messenger is currently configured synchronously. Add a worker service only after introducing an asynchronous transport.
