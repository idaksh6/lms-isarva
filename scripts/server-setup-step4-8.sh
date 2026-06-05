#!/usr/bin/env bash
# Run ON THE SERVER as the CyberPanel site user (e.g. lmsis1337).
#   ssh lmsis1337@139.84.143.214
# One-shot setup after GitHub secrets (Step 3) are done.
set -euo pipefail

APP_DIR="/home/lms.isarvait.com"
ENV_BACKUP="${HOME}/lms.env.backup"

echo "=== LMS server setup (steps 4–8) ==="
echo "    User: $(whoami)"
cd "$APP_DIR"

echo "==> Step 4: Backup .env"
cp -a .env "$ENV_BACKUP"
echo "    Saved: $ENV_BACKUP"

echo "==> Step 5: Add GitHub Actions SSH public key (if not already)"
mkdir -p ~/.ssh
chmod 700 ~/.ssh
ACTIONS_PUB='ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIGfksNK5l+xiQEHjn+p9Ihu923CAUmUaTWBRDgMgKXr9 github-actions-lms'
if ! grep -qF 'github-actions-lms' ~/.ssh/authorized_keys 2>/dev/null; then
  echo "$ACTIONS_PUB" >> ~/.ssh/authorized_keys
  echo "    Added github-actions-lms key"
else
  echo "    Key already present"
fi
chmod 600 ~/.ssh/authorized_keys

echo "==> Step 6: GitHub deploy key for git pull"
if [[ ! -f ~/.ssh/lms_github_deploy ]]; then
  ssh-keygen -t ed25519 -f ~/.ssh/lms_github_deploy -N "" -C "server-git-pull-lms"
fi
if ! grep -q 'Host github.com' ~/.ssh/config 2>/dev/null; then
  cat >> ~/.ssh/config <<'EOF'

Host github.com
  HostName github.com
  User git
  IdentityFile ~/.ssh/lms_github_deploy
  IdentitiesOnly yes
EOF
  chmod 600 ~/.ssh/config
fi
echo ""
echo ">>> ADD THIS DEPLOY KEY ON GITHUB (Settings → Deploy keys):"
cat ~/.ssh/lms_github_deploy.pub
echo ""
read -r -p "Press Enter after you added the deploy key on GitHub..."

ssh -T -o StrictHostKeyChecking=accept-new git@github.com || true

echo "==> Step 7: Connect folder to Git"
if [[ ! -d .git ]]; then
  git init
  git remote add origin git@github.com:idaksh6/lms-isarva.git 2>/dev/null || \
    git remote set-url origin git@github.com:idaksh6/lms-isarva.git
fi
git fetch origin main
git checkout -B main origin/main || {
  git fetch origin main
  git reset --hard origin/main
}
cp -a "$ENV_BACKUP" .env
echo "    Git on main, .env restored"

echo "==> Step 8: Composer, npm, sync public_html"
composer install --no-dev --optimize-autoloader --no-interaction
if command -v npm >/dev/null 2>&1; then
  npm ci --ignore-scripts
  npm run build
else
  echo "    WARN: npm missing — rely on GitHub Actions for public/build"
fi
mkdir -p public/build public_html/build
rsync -a --delete public/build/ public_html/build/ 2>/dev/null || true
rsync -a public/build/ public/build/ 2>/dev/null || true
for item in index.php .htaccess robots.txt favicon.ico images; do
  [[ -e "public/$item" ]] && rsync -a "public/$item" public_html/
done
rm -f public_html/storage public/storage 2>/dev/null || true
php artisan storage:link 2>/dev/null || true
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo ""
echo "=== DONE ==="
echo "1. GitHub secret SSH_USER must be: $(whoami)  (e.g. lmsis1337)"
echo "2. On Mac: git push origin main"
echo "3. Check: https://github.com/idaksh6/lms-isarva/actions"
echo "4. Open: https://lms.isarvait.com/login"
