# HR VPS Deployment Assets

This folder contains HR-ready deployment assets aligned to VPS_System_Upgrade templates.

## Contents

- env/.env.vps-system-upgrade.example: production env baseline for Redis-backed cache/session/queue
- nginx/hr.conf: Nginx virtual host template
- php-fpm/hr.conf: dedicated PHP-FPM pool for HR
- supervisor/hr-worker.conf: queue workers (high/default/low)
- supervisor/hr-scheduler.conf: Laravel scheduler loop
- scripts/deploy_laravel.sh: repeatable Laravel deploy sequence

## Notes

- Apply in staging first, then production.
- Replace placeholder domains, paths, and credentials.
