# Scheduler And Queue Setup

This project sends scheduled basket reminders through Laravel's scheduler.
Because the reminder is dispatched as a queued job, both services must be running:

- Laravel scheduler
- Laravel queue worker

## Local Windows

Start both workers in the background from the project root:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\start-background-workers.ps1
```

Stop them:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\stop-background-workers.ps1
```

Logs:

- `storage/logs/scheduler.log`
- `storage/logs/scheduler-error.log`
- `storage/logs/queue-worker.log`
- `storage/logs/queue-worker-error.log`

PID files:

- `storage/framework/scheduler.pid`
- `storage/framework/queue-worker.pid`

## Linux Server

Ready-made `systemd` units are included in:

- `deploy/systemd/tikmool-scheduler.service`
- `deploy/systemd/tikmool-queue.service`

Update these placeholders before enabling them:

- `User=www-data`
- `Group=www-data`
- `WorkingDirectory=/var/www/tikmool`
- PHP path if needed

Copy them to the server:

```bash
sudo cp deploy/systemd/tikmool-scheduler.service /etc/systemd/system/
sudo cp deploy/systemd/tikmool-queue.service /etc/systemd/system/
```

Reload and enable:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now tikmool-scheduler.service
sudo systemctl enable --now tikmool-queue.service
```

Check status:

```bash
sudo systemctl status tikmool-scheduler.service
sudo systemctl status tikmool-queue.service
```

View logs:

```bash
journalctl -u tikmool-scheduler.service -f
journalctl -u tikmool-queue.service -f
```

Restart after deploy:

```bash
sudo systemctl restart tikmool-scheduler.service
sudo systemctl restart tikmool-queue.service
```

## Required App Conditions

- `QUEUE_CONNECTION=database` is already configured in `.env`
- `jobs`, `job_batches`, and `failed_jobs` tables must exist
- database must be reachable
- FCM tokens must exist if push notifications are expected

## Reminder Flow

- Scheduler process runs continuously through `php artisan schedule:work`
- At `09:00` the app dispatches `SendScheduledBasketReminderJob`
- Queue worker picks up the job and stores database notifications
- FCM notifications are sent if the user has tokens
