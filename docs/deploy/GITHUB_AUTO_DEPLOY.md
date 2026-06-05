# Auto-deploy: Git push → live server

Push to **`main`** on GitHub → GitHub Actions builds assets → SSH updates `/home/lms.isarvait.com`.

## One-time server setup

### 1. Replace manual upload with Git clone

SSH into the VPS:

```bash
cd /home/lms.isarvait.com

# Backup .env first if the site already works
cp .env /root/lms.env.backup

# If folder is messy, move old files aside, then clone:
# mv public_html public_html.bak
git clone git@github.com:idaksh6/lms-isarva.git .

# Restore .env (never commit this file)
cp /root/lms.env.backup .env

composer install --no-dev --optimize-autoloader
npm ci && npm run build
# sync build + public files as you did manually
```

### 2. Deploy SSH key (for GitHub Actions)

On your **Mac**:

```bash
ssh-keygen -t ed25519 -C "github-deploy-lms" -f ~/.ssh/lms_deploy -N ""
```

On the **server**, add the **public** key to `~/.ssh/authorized_keys` for the user that owns the site (often `root` or the CyberPanel site user):

```bash
cat lms_deploy.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

Test:

```bash
ssh -i ~/.ssh/lms_deploy USER@139.84.143.214 "cd /home/lms.isarvait.com && git status"
```

### 3. GitHub deploy key (server can `git pull`)

CyberPanel server must pull from GitHub:

- **GitHub repo → Settings → Deploy keys → Add**  
  Paste the server’s **public** key (`ssh-keygen` on server, or use a read-only deploy key).  
  Allow read access.

Or use HTTPS + token on server (less ideal).

### 4. GitHub Actions secrets

Repo → **Settings → Secrets and variables → Actions → New repository secret**:

| Secret | Example |
|--------|---------|
| `SSH_HOST` | `139.84.143.214` |
| `SSH_USER` | `lmsis1337` |
| `SSH_PRIVATE_KEY` | Full contents of `~/.ssh/lms_deploy` (private key) |
| `SSH_APP_DIR` | `/home/lms.isarvait.com` |

### 5. Branch

Workflow deploys on push to **`main`**. Use `main` as your default branch or edit `.github/workflows/deploy.yml`.

## Day-to-day workflow

```bash
git add .
git commit -m "Your change"
git push origin main
```

Then open **GitHub → Actions** and watch **Deploy to production**. In ~1–2 minutes the live site updates.

## What is never overwritten

- **`.env`** on the server (in `.gitignore`) — DB password, `APP_KEY`, etc. stay on the server.
- **`storage/app/`** uploads — not in Git; kept on server.

## Manual deploy (without Actions)

SSH and run:

```bash
bash /home/lms.isarvait.com/scripts/deploy.sh
```

## Troubleshooting

| Problem | Fix |
|---------|-----|
| Permission denied (SSH) | Check `SSH_USER`, key in `authorized_keys` |
| `git pull` fails | Add deploy key on GitHub; `git remote -v` on server |
| Vite manifest error | Workflow runs `npm run build` and rsyncs `public/build` |
| 500 after deploy | `storage/logs/laravel.log`; run `php artisan migrate --force` |

## Optional: deploy only on tag / manual button

Change `deploy.yml` to use `workflow_dispatch` or `tags: ['v*']` if you do not want every push to go live immediately.
