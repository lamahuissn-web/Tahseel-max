# Verification Quickstart

## Automated

```bash
vendor/bin/phpunit --filter=Sas4Gateway
vendor/bin/phpunit tests/Feature/ClientSasStatusServiceTest.php tests/Feature/ClientSasStatusEndpointTest.php tests/Feature/ClientSasStatusBoundsTest.php tests/Feature/Sas4ApiServiceTimeoutTest.php
vendor/bin/phpunit
```

## Static checks

```bash
php -l app/Services/Sas4/Sas4Gateway.php
php -l app/Services/Sas4/Sas4ApiService.php
php -l app/Http/Controllers/Admin/ClientController.php
php artisan route:list | grep -i sas4
```

## Safety assertions

- No migration exists in the diff.
- No `.env` or credential value exists in the diff.
- No automated verification invokes create/enable/disable/disconnect/profile-change against live SAS4.
- Production worktree remains on `production` and clean.
- Failure responses distinguish unavailable from offline.
- Maximum status batch does not increase SAS request count.
