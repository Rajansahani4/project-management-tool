Run all Laravel optimization and cache clearing commands for the backend.

Execute the following commands in sequence using `cd backend &&` prefix for each:

```bash
cd backend && php artisan cache:clear
cd backend && php artisan config:clear
cd backend && php artisan config:cache
cd backend && php artisan route:clear
cd backend && php artisan route:cache
cd backend && php artisan view:clear
cd backend && php artisan view:cache
cd backend && php artisan event:clear
cd backend && php artisan event:cache
cd backend && php artisan optimize:clear
cd backend && php artisan optimize
```

Run each command one by one, report the output of each, and confirm when all are complete.
