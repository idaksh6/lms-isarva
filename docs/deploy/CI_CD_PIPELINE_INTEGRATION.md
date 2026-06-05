# ISARVA LMS — CI/CD Pipeline Integration Guide

**Project:** Data Science LMS (Laravel 13)  
**Production URL:** https://lms.isarvait.com  
**Repository:** https://github.com/idaksh6/lms-isarva  
**Server:** CyberPanel on 139.84.143.214  
**SSH user:** lmsis1337  
**App path:** /home/lms.isarvait.com  
**Web root:** /home/lms.isarvait.com/public_html  

**Document version:** 1.0  
**Last updated:** June 2026  

---

## 1. What is CI/CD?

**CI (Continuous Integration)** — Every time code is pushed to Git, it is built and checked automatically (e.g. `npm run build` for CSS/JS).

**CD (Continuous Deployment)** — If the build succeeds, the latest code is deployed to the live server without manual zip upload.

### Our pipeline flow

```
Developer (Mac) → git push main → GitHub → GitHub Actions → SSH → CyberPanel server → lms.isarvait.com
```

This is the same *pattern* as **Laravel Forge** (UK client projects): you connect a Git repo to a server path; push triggers deploy. Forge provides a UI; we use **GitHub Actions** + **CyberPanel**.

| Component | Role |
|-----------|------|
| **GitHub** | Stores code, history, branches |
| **GitHub Actions** | Runs build + deploy workflow (`.github/workflows/deploy.yml`) |
| **Server** | Runs the live app; updated on each successful deploy |
| **`.env` on server** | Never in Git; stays only on the server |

---

## 2. Prerequisites

- Laravel project on GitHub (`main` branch)
- CyberPanel site created: `lms.isarvait.com`, **PHP 8.3**
- MySQL database created in CyberPanel
- Initial site working (login page loads)
- Mac with Git, Node.js, SSH access to server

**Laravel requirement:** PHP **8.3+** (website and deploy script use PHP 8.3 CLI).

---

## 3. Server folder layout (CyberPanel)

CyberPanel serves **`public_html`**. Laravel expects **`public/`** for Vite manifest.

```
/home/lms.isarvait.com/
├── app/, bootstrap/, config/, database/, resources/, routes/, storage/, vendor/
├── .env                    ← server only, not in Git
├── artisan, composer.json
├── public/
│   └── build/              ← Laravel @vite() reads manifest here
└── public_html/            ← web root (browser)
    ├── index.php, .htaccess
    └── build/              ← same assets as public/build/
```

---

## 4. One-time setup — Part A (Mac)

### Step 1 — Push workflow to GitHub

```bash
cd "/path/to/LMS ISARVA"
git add .
git commit -m "Add CI/CD deploy workflow"
git push origin main
```

Confirm on GitHub: `.github/workflows/deploy.yml` exists.

### Step 2 — Create deploy SSH key (Mac)

```bash
ssh-keygen -t ed25519 -C "github-actions-lms" -f ~/.ssh/lms_deploy -N ""
cat ~/.ssh/lms_deploy.pub    # → server authorized_keys (Step 5)
cat ~/.ssh/lms_deploy        # → GitHub secret SSH_PRIVATE_KEY (Step 3)
```

### Step 3 — GitHub Actions secrets

**GitHub → Repository → Settings → Secrets and variables → Actions → New repository secret**

Add **four** secrets (one at a time):

| Secret name | Value |
|-------------|--------|
| `SSH_HOST` | `139.84.143.214` |
| `SSH_USER` | `lmsis1337` |
| `SSH_PRIVATE_KEY` | Full contents of `~/.ssh/lms_deploy` (private key) |
| `SSH_APP_DIR` | `/home/lms.isarvait.com` |

---

## 5. One-time setup — Part B (Server)

SSH in:

```bash
ssh lmsis1337@139.84.143.214
```

### Step 4 — Backup `.env`

```bash
cd /home/lms.isarvait.com
cp .env ~/lms.env.backup
```

### Step 5 — Allow GitHub Actions to SSH (key auth)

```bash
mkdir -p ~/.ssh && chmod 700 ~/.ssh
nano ~/.ssh/authorized_keys
```

Paste the **public** key from `lms_deploy.pub` (one line). Save.

```bash
chmod 600 ~/.ssh/authorized_keys
```

**Test from Mac** (not from inside server):

```bash
ssh -i ~/.ssh/lms_deploy lmsis1337@139.84.143.214 "echo SSH OK"
```

Must print `SSH OK` **without** password prompt.

### Step 6 — Server deploy key (Git pull from GitHub)

On server:

```bash
ssh-keygen -t ed25519 -f ~/.ssh/lms_github_deploy -N ""
cat ~/.ssh/lms_github_deploy.pub
```

**GitHub → Repository → Settings → Deploy keys → Add deploy key**  
Paste public key, read-only access.

On server:

```bash
nano ~/.ssh/config
```

Add:

```
Host github.com
  HostName github.com
  User git
  IdentityFile ~/.ssh/lms_github_deploy
  IdentitiesOnly yes
```

```bash
chmod 600 ~/.ssh/config
ssh -T git@github.com
```

### Step 7 — Connect site folder to Git

```bash
cd /home/lms.isarvait.com
git init
git remote add origin git@github.com:idaksh6/lms-isarva.git
git fetch origin main
git checkout -B main origin/main
cp ~/lms.env.backup .env
```

### Step 8 — Sync build and public_html

```bash
composer install --no-dev --optimize-autoloader
# npm run build — optional on server; GitHub Actions builds assets
rsync -a --delete public/build/ public_html/build/
php artisan storage:link
php artisan migrate --force
php artisan config:cache
chmod -R 775 storage bootstrap/cache
```

Use PHP 8.3 for CLI if default is 8.0:

```bash
/usr/local/lsws/lsphp83/bin/php artisan migrate --force
```

---

## 6. What the pipeline does on each push

File: `.github/workflows/deploy.yml`

**Trigger:** Push to branch `main`

| Step | Action |
|------|--------|
| 1 | Checkout code on GitHub runner |
| 2 | `npm ci` + `npm run build` |
| 3 | SCP `public/build/` to server |
| 4 | SSH: `git fetch` + `reset --hard origin/main` |
| 5 | `composer install --no-dev` (PHP 8.3) |
| 6 | Rsync `build/` → `public/build` and `public_html/build` |
| 7 | `php artisan migrate`, config/route/view cache |
| 8 | Fix `storage` permissions |

---

## 7. Daily workflow (after setup)

**Always on your Mac:**

```bash
cd "/path/to/LMS ISARVA"
# edit code...
git add .
git commit -m "Describe your change"
git push origin main
```

1. Open https://github.com/idaksh6/lms-isarva/actions  
2. Wait for green checkmark (~1–2 minutes)  
3. Verify https://lms.isarvait.com  

**Do not:** edit production code only in CyberPanel File Manager (next deploy overwrites).  
**Do not:** `git push` from the server.

---

## 8. What stays on the server only

| Item | In Git? | Updated by deploy? |
|------|---------|-------------------|
| `.env` | No | No |
| `storage/app/` uploads | No | No |
| Database data | No | Migrations only |
| Application code | Yes | Yes |
| `vendor/` | No (in .gitignore) | Yes (`composer install`) |
| `public/build/` | No (in .gitignore) | Yes (built in Actions) |

---

## 9. Comparison with Laravel Forge

| | Laravel Forge | ISARVA LMS (this setup) |
|--|---------------|-------------------------|
| Git connection | UI: repo URL + branch | GitHub Actions + server `git remote` |
| Deploy trigger | Push / webhook | Push to `main` |
| See code in UI? | No (path only) | CyberPanel File Manager optional |
| Server path | e.g. `/home/forge/site.com` | `/home/lms.isarvait.com` |
| Pipeline type | CI/CD (deploy) | CI/CD (build + deploy) |

---

## 10. Troubleshooting

| Problem | Solution |
|---------|----------|
| SSH password asked from Mac | Add `lms_deploy.pub` to `~/.ssh/authorized_keys` on server |
| Actions: Permission denied | Check `SSH_USER` = `lmsis1337`, private key secret correct |
| Vite manifest not found | Ensure `public/build` and `public_html/build` both have `manifest.json` |
| PHP 8.0 errors on server SSH | Workflow uses PHP 8.3 path; set PHP 8.3 in CyberPanel for site |
| 500 after deploy | Read `storage/logs/laravel.log` on server |
| Site OK but HTTPS warning | Issue SSL in CyberPanel after DNS points to server |

**Manual deploy (fallback):**

```bash
ssh lmsis1337@139.84.143.214
cd /home/lms.isarvait.com
git pull origin main
bash scripts/deploy.sh
```

---

## 11. Production checklist

- [ ] `APP_DEBUG=false` in server `.env`
- [ ] `LMS_SHOW_DEMO_CREDENTIALS=false`
- [ ] `APP_URL=https://lms.isarvait.com`
- [ ] SSL issued in CyberPanel
- [ ] Demo passwords changed
- [ ] GitHub Actions deploy green on last push

---

## 12. Related files in this project

| File | Purpose |
|------|---------|
| `.github/workflows/deploy.yml` | CI/CD workflow definition |
| `scripts/deploy.sh` | Manual server deploy script |
| `scripts/server-setup-step4-8.sh` | One-shot initial server setup |
| `docs/deploy/ZIP_TO_AUTO_DEPLOY.md` | Zip → Git migration steps |
| `docs/deploy/GITHUB_AUTO_DEPLOY.md` | Short reference |

---

**ISARVA Infotech — LMS CI/CD documentation**
