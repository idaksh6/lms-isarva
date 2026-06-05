# Step-by-step: zip upload → Git auto-deploy

You already have the LMS working from a **zip on the server**. Follow these steps in order.

**Server path:** `/home/lms.isarvait.com`  
**Web root:** `/home/lms.isarvait.com/public_html`  
**Repo:** `git@github.com:idaksh6/lms-isarva.git`

---

## Part A — On your Mac (one time)

### Step 1 — Push latest code to GitHub

```bash
cd "/Users/saikiran/ISARVA PROJECT/LMS ISARVA"

git status
git add .
git commit -m "Add GitHub Actions deploy workflow"
git push origin main
```

If `main` does not exist yet:

```bash
git branch -M main
git push -u origin main
```

Confirm on GitHub you see:

- `.github/workflows/deploy.yml`
- `scripts/deploy.sh`
- `docs/deploy/`

---

### Step 2 — Create SSH key for GitHub Actions

```bash
ssh-keygen -t ed25519 -C "github-actions-lms" -f ~/.ssh/lms_deploy -N ""
```

Show the **public** key (you will add it on the server):

```bash
cat ~/.ssh/lms_deploy.pub
```

Show the **private** key (for GitHub secret only — never share publicly):

```bash
cat ~/.ssh/lms_deploy
```

Copy the **entire** private key including `BEGIN` / `END` lines.

---

### Step 3 — Add GitHub Actions secrets

Open: **https://github.com/idaksh6/lms-isarva/settings/secrets/actions**

Click **New repository secret** for each:

| Name | Value |
|------|--------|
| `SSH_HOST` | `139.84.143.214` |
| `SSH_USER` | `lmsis1337` |
| `SSH_PRIVATE_KEY` | Contents of `~/.ssh/lms_deploy` (private key) |
| `SSH_APP_DIR` | `/home/lms.isarvait.com` |

---

## Part B — On the server (one time)

Use **CyberPanel → SSH** or Terminal on your Mac:

```bash
ssh lmsis1337@139.84.143.214
```

---

### Step 4 — Backup what must not be lost

```bash
cd /home/lms.isarvait.com

cp .env ~/lms.env.backup
cp -a storage/app/public ~/lms-storage-backup 2>/dev/null || true
```

Your live `.env` and any uploaded files in `storage` are safe.

---

### Step 5 — Let the server pull from GitHub

**5a) Add GitHub Actions public key to server** (from Step 2):

```bash
mkdir -p ~/.ssh
chmod 700 ~/.ssh
nano ~/.ssh/authorized_keys
```

Paste the line from `lms_deploy.pub`, save, then:

```bash
chmod 600 ~/.ssh/authorized_keys
```

**5b) Create a deploy key so the server can read the repo:**

```bash
ssh-keygen -t ed25519 -C "server-git-pull-lms" -f ~/.ssh/lms_github_deploy -N ""
cat ~/.ssh/lms_github_deploy.pub
```

Copy that output.

**5c) Add deploy key on GitHub:**

1. **https://github.com/idaksh6/lms-isarva/settings/keys**
2. **Add deploy key**
3. Title: `CyberPanel LMS server`
4. Paste the public key
5. **Read-only** is enough
6. Save

**5d) Configure SSH for GitHub on the server:**

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

You should see: `Hi idaksh6/lms-isarva!` (or similar success message).

---

### Step 6 — Connect your existing folder to Git

Still in `/home/lms.isarvait.com`:

```bash
cd /home/lms.isarvait.com

# If git was never used here:
git init
git remote add origin git@github.com:idaksh6/lms-isarva.git

# Download latest from GitHub (does not delete .env yet if untracked)
git fetch origin main
git checkout -B main origin/main

# Put your live .env back (Git must not replace it)
cp ~/lms.env.backup .env
```

If `git checkout` complains about local files, backup and force once:

```bash
cp /root/lms.env.backup /root/lms.env.backup2
git fetch origin main
git reset --hard origin/main
cp /root/lms.env.backup2 .env
```

---

### Step 7 — Fix Laravel paths (CyberPanel layout)

```bash
cd /home/lms.isarvait.com

composer install --no-dev --optimize-autoloader

npm ci && npm run build

mkdir -p public/build public_html/build
rsync -a --delete public/build/ public_html/build/
rsync -a public/build/ public/build/

for item in index.php .htaccess robots.txt favicon.ico images; do
  [ -e "public/$item" ] && rsync -a "public/$item" public_html/
done

rm -f public_html/storage public/storage 2>/dev/null
php artisan storage:link

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

chmod -R 775 storage bootstrap/cache
```

Open **https://lms.isarvait.com/login** — site should still work.

---

### Step 8 — Test SSH deploy from your Mac

```bash
ssh -i ~/.ssh/lms_deploy lmsis1337@139.84.143.214 "cd /home/lms.isarvait.com && git status"
```

If that works, GitHub Actions can reach the server.

---

## Part C — First automatic deploy

### Step 9 — Trigger GitHub Actions

On your Mac, make a tiny change (or empty commit):

```bash
cd "/Users/saikiran/ISARVA PROJECT/LMS ISARVA"
git commit --allow-empty -m "Test auto deploy"
git push origin main
```

### Step 10 — Watch the workflow

1. **https://github.com/idaksh6/lms-isarva/actions**
2. Open **Deploy to production**
3. Wait for green checkmark

If it fails, click the failed step and read the log (usually SSH key, wrong `SSH_USER`, or `git` path).

### Step 11 — Verify live site

- **https://lms.isarvait.com/login**
- Log in as admin
- Optional: upload a small file on an assignment

---

## Part D — Every day after setup

```bash
cd "/Users/saikiran/ISARVA PROJECT/LMS ISARVA"
# edit code...
git add .
git commit -m "Describe your change"
git push origin main
```

Wait ~1–2 minutes → site updates. No more zip upload.

---

## Quick reference

| What | Stays on server only |
|------|----------------------|
| `.env` | Yes — never in Git |
| `storage/app/` uploads | Yes |
| `vendor/` | Rebuilt by `composer install` on deploy |
| `public/build/` | Rebuilt by GitHub Actions + rsync |

## If something breaks

```bash
ssh lmsis1337@139.84.143.214
cd /home/lms.isarvait.com
cp ~/lms.env.backup .env
tail -50 storage/logs/laravel.log
bash scripts/deploy.sh
```

## Manual deploy (without waiting for Actions)

```bash
ssh lmsis1337@139.84.143.214
cd /home/lms.isarvait.com
git pull origin main
bash scripts/deploy.sh
```
