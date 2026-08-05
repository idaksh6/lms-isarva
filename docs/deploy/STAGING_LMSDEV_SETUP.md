# Staging: lmsdev.isarva.in

Deploy the **full LMS** to the **demo/staging server only**. Production (`lms.isarvait.com` on `139.84.143.214`) is **not** changed by this process.

| | Production (do not touch) | Staging (this guide) |
|---|---|---|
| **URL** | https://lms.isarvait.com | https://lmsdev.isarva.in |
| **Server IP** | 139.84.143.214 | 139.84.140.77 |
| **Panel** | CyberPanel | CyberPanel `:8090` |
| **App path** | `/home/lms.isarvait.com` | `/home/lmsdev.isarva.in` |

---

## Part 1 — DNS (once)

Add an **A record** in your DNS for `isarva.in`:

| Host | Type | Value |
|------|------|--------|
| `lmsdev` | A | `139.84.140.77` |

Wait until `lmsdev.isarva.in` resolves to `139.84.140.77` before issuing SSL.

---

## Part 2 — Create site in CyberPanel (only this domain)

1. Open **https://139.84.140.77:8090/** and sign in.
2. **Websites → Create Website**
   - **Domain:** `lmsdev.isarva.in`
   - **Email:** your admin email
   - **Package:** Default (or any spare package)
   - **PHP:** **8.3**
   - **SSL:** issue after DNS works (Let's Encrypt)
3. Do **not** edit or delete other websites on this server.

Note the **SSH username** CyberPanel creates (often the domain name or a short user). You need it for Part 4.

---

## Part 3 — MySQL database (staging only)

In CyberPanel → **Databases → Create database**:

| Field | Suggested value |
|-------|-----------------|
| Database name | `lmsdev_db` |
| Database user | `lmsdev_user` |
| Password | strong random password |

Grant this user full access to `lmsdev_db` only.

---

## Part 4 — Deploy code (SSH as site user)

From your Mac (after CyberPanel created the site):

```bash
ssh <SITE_SSH_USER>@139.84.140.77
```

On the server:

```bash
cd /home/lmsdev.isarva.in   # or CyberPanel path for this site
git clone git@github.com:idaksh6/lms-isarva.git .
# Or upload via SFTP if git is not configured yet

cp .env.staging.example .env
nano .env   # set DB_* and confirm APP_URL=https://lmsdev.isarva.in

bash scripts/staging-server-setup.sh
```

If the server cannot `git clone` from GitHub, add a **read-only deploy key** on the repo (same pattern as production — see `docs/deploy/GITHUB_AUTO_DEPLOY.md`).

---

## Part 5 — Verify

- https://lmsdev.isarva.in/up → should return OK
- https://lmsdev.isarva.in/login → demo logins visible (staging only)
- Production https://lms.isarvait.com still works unchanged

---

## Ongoing staging deploys

On the staging server:

```bash
cd /home/lmsdev.isarva.in
bash scripts/staging-deploy.sh
```

Optional: GitHub Actions workflow **Deploy to staging** (manual button only) — see `.github/workflows/deploy-staging.yml`. Uses **separate** secrets from production.

---

## Troubleshooting

| Issue | Fix |
|-------|-----|
| CyberPanel API returns "API Access Disabled" | Enable **API Access** for admin under **Users → Edit admin**, or use the panel UI + SSH instead |
| SSH `Permission denied` for root | Use the **site SSH user** from CyberPanel, not root |
| 500 error | `tail storage/logs/laravel.log`; check `.env` DB credentials |
| CSS missing | Run `npm run build` on server or deploy `public/build` from CI |
| Wrong site updated | Confirm `APP_DIR` is `/home/lmsdev.isarva.in`, never `lms.isarvait.com` |

---

## Security notes (staging)

- `LMS_SHOW_DEMO_CREDENTIALS=true` is intentional on staging so demos work without sharing passwords in public docs.
- Do **not** copy production `.env` to staging — use a **separate database**.
- Staging and production share **no** deploy script paths or GitHub Action secrets.
