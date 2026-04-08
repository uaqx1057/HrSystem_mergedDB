# HR Deployment Guide (VPS Alignment)

This document aligns HR deployment with VPS_System_Upgrade templates.

## 1. One-time VPS setup

```bash
cd /var/www/hr/current
cp deploy/env/.env.vps-system-upgrade.example .env
# update DB, Redis, and app secrets
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan storage:link
```

## 2. Install Nginx and PHP-FPM templates

```bash
# Nginx
sudo cp deploy/nginx/hr.conf /etc/nginx/sites-available/hr.conf
sudo ln -s /etc/nginx/sites-available/hr.conf /etc/nginx/sites-enabled/hr.conf

# PHP-FPM pool
sudo cp deploy/php-fpm/hr.conf /etc/php/8.2/fpm/pool.d/hr.conf

sudo php-fpm8.2 -t
sudo nginx -t
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx
```

## 3. Install Supervisor worker and scheduler

```bash
cd /var/www/hr/current
sudo cp deploy/supervisor/hr-worker.conf /etc/supervisor/conf.d/
sudo cp deploy/supervisor/hr-scheduler.conf /etc/supervisor/conf.d/
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start hr-worker:*
sudo supervisorctl start hr-scheduler
```

## 4. Deploy app updates

```bash
cd /var/www/hr/current
bash deploy/scripts/deploy_laravel.sh /var/www/hr/current
```

## 5. Verify

```bash
sudo supervisorctl status hr-worker:*
sudo supervisorctl status hr-scheduler
curl -I https://hr.yourdomain.com
```

## 6. Notes

- Keep production .env only on server.
- If your app path differs from /var/www/hr/current, update command and directory in supervisor conf files.
- Apply in staging first, then production.

## 7. Preflight validation script

```bash
cd /var/www/hr/current
bash deploy/scripts/vps_preflight_check.sh /var/www/hr/current https://hr.yourdomain.com
```
