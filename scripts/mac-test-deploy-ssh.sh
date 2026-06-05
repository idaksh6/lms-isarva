#!/usr/bin/env bash
# Run on your Mac after server-setup-step4-8.sh finished.
set -euo pipefail

KEY="${HOME}/.ssh/lms_deploy"
HOST="139.84.143.214"
USER="${SSH_USER:-lmsis1337}"

echo "Testing SSH for GitHub Actions (${USER}@${HOST})..."
ssh -i "$KEY" -o StrictHostKeyChecking=accept-new "${USER}@${HOST}" "cd /home/lms.isarvait.com && git rev-parse --short HEAD && php artisan --version"

echo "OK — push to main to trigger deploy:"
echo "  git push origin main"
