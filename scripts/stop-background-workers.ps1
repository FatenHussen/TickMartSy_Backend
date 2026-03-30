$ErrorActionPreference = "Stop"

$root = Split-Path -Parent $PSScriptRoot
$frameworkDir = Join-Path $root "storage\framework"

$pidFiles = @(
    (Join-Path $frameworkDir "scheduler.pid"),
    (Join-Path $frameworkDir "queue-worker.pid")
)

foreach ($pidFile in $pidFiles) {
    if (-not (Test-Path $pidFile)) {
        continue
    }

    $processId = Get-Content $pidFile -ErrorAction SilentlyContinue

    if ($processId) {
        $process = Get-Process -Id $processId -ErrorAction SilentlyContinue
        if ($process) {
            Stop-Process -Id $processId -Force
            Write-Output "Stopped process $processId"
        }
    }

    Remove-Item $pidFile -Force -ErrorAction SilentlyContinue
}
