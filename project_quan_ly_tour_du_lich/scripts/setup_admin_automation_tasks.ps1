param(
    [int]$EveryMinutes = 5,
    [string]$TaskPrefix = "Aventura_Admin_Automation"
)

$ErrorActionPreference = "Stop"

if ($EveryMinutes -lt 5) {
    throw "EveryMinutes must be >= 5"
}

$projectRoot = Split-Path -Parent $PSScriptRoot
$runnerPath = Join-Path $projectRoot "scripts\run_admin_automation.php"

if (-not (Test-Path $runnerPath)) {
    throw "Cannot find runner script: $runnerPath"
}

$phpCmd = Get-Command php -ErrorAction SilentlyContinue
if (-not $phpCmd) {
    throw "Cannot find php executable in PATH."
}

$phpExe = $phpCmd.Source
$taskAll = "$TaskPrefix`_All"
$taskDigest = "$TaskPrefix`_DailyDigest"

$cmdAll = '"{0}" "{1}" all' -f $phpExe, $runnerPath
$cmdDigest = '"{0}" "{1}" reconcile_digest' -f $phpExe, $runnerPath

Write-Host "Creating task $taskAll (every $EveryMinutes minutes)..."
schtasks.exe /Create /TN $taskAll /SC MINUTE /MO $EveryMinutes /TR $cmdAll /F
if ($LASTEXITCODE -ne 0) {
    throw "Failed to create task $taskAll"
}

Write-Host "Creating task $taskDigest (daily 23:55)..."
schtasks.exe /Create /TN $taskDigest /SC DAILY /ST 23:55 /TR $cmdDigest /F
if ($LASTEXITCODE -ne 0) {
    throw "Failed to create task $taskDigest"
}

Write-Host "Done."
Write-Host "Tasks created:"
Write-Host " - $taskAll"
Write-Host " - $taskDigest"
