$ErrorActionPreference = "Stop"

$root = Split-Path -Parent $PSScriptRoot
$php = (Get-Command php).Source
$powershellExe = (Get-Command powershell).Source

$logDir = Join-Path $root "storage\logs"
$frameworkDir = Join-Path $root "storage\framework"

New-Item -ItemType Directory -Force -Path $logDir | Out-Null
New-Item -ItemType Directory -Force -Path $frameworkDir | Out-Null

$schedulerPidFile = Join-Path $frameworkDir "scheduler.pid"
$queuePidFile = Join-Path $frameworkDir "queue-worker.pid"

function Stop-If-Running($pidFile) {
    if (Test-Path $pidFile) {
        $oldPid = Get-Content $pidFile -ErrorAction SilentlyContinue
        if ($oldPid) {
            $process = Get-Process -Id $oldPid -ErrorAction SilentlyContinue
            if ($process) {
                Stop-Process -Id $oldPid -Force
            }
        }
        Remove-Item $pidFile -Force -ErrorAction SilentlyContinue
    }
}

Stop-If-Running $schedulerPidFile
Stop-If-Running $queuePidFile

$schedulerLog = Join-Path $logDir "scheduler.log"
$schedulerErrorLog = Join-Path $logDir "scheduler-error.log"
$queueLog = Join-Path $logDir "queue-worker.log"
$queueErrorLog = Join-Path $logDir "queue-worker-error.log"

$schedulerCommand = "Set-Location '$root'; & '$php' artisan schedule:work"
$queueCommand = "Set-Location '$root'; & '$php' artisan queue:work --queue=default --sleep=3 --tries=3 --timeout=120 --max-time=3600"

$scheduler = Start-Process -FilePath $powershellExe `
    -ArgumentList "-NoProfile", "-Command", $schedulerCommand `
    -WindowStyle Hidden `
    -RedirectStandardOutput $schedulerLog `
    -RedirectStandardError $schedulerErrorLog `
    -PassThru

$queue = Start-Process -FilePath $powershellExe `
    -ArgumentList "-NoProfile", "-Command", $queueCommand `
    -WindowStyle Hidden `
    -RedirectStandardOutput $queueLog `
    -RedirectStandardError $queueErrorLog `
    -PassThru

Set-Content -Path $schedulerPidFile -Value $scheduler.Id
Set-Content -Path $queuePidFile -Value $queue.Id

Write-Output "Scheduler started with PID $($scheduler.Id)"
Write-Output "Queue worker started with PID $($queue.Id)"
