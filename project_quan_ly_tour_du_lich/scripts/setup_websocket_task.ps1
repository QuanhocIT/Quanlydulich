param(
    [string]$TaskName = "Aventura_WebSocket_Server",
    [string]$RunAsUser = $env:USERNAME,
    [string]$StartTime = "00:00",
    [switch]$UseOnStartup
)

$ErrorActionPreference = "Stop"

$projectRoot = Split-Path -Parent $PSScriptRoot
$serverScriptPath = Join-Path $projectRoot "scripts\websocket_server.php"

if (-not (Test-Path $serverScriptPath)) {
    throw "Cannot find websocket server script: $serverScriptPath"
}

$phpCmd = Get-Command php -ErrorAction SilentlyContinue
if (-not $phpCmd) {
    throw "Cannot find php executable in PATH."
}

$phpExe = $phpCmd.Source

$phpCommand = '"{0}" "{1}"' -f $phpExe, $serverScriptPath
$taskCommand = "cmd /c $phpCommand"
$startupFolder = [Environment]::GetFolderPath('Startup')
$startupCmdPath = Join-Path $startupFolder "$TaskName.cmd"

function Write-StartupShortcut {
    param(
        [string]$FilePath,
        [string]$ProjectRoot,
        [string]$PhpExePath,
        [string]$WebSocketScriptPath
    )

    $content = @(
        '@echo off',
        ('cd /d "{0}"' -f $ProjectRoot),
        ('start "Aventura WebSocket" /min "{0}" "{1}"' -f $PhpExePath, $WebSocketScriptPath)
    )

    Set-Content -Path $FilePath -Value $content -Encoding ASCII
}

Write-Host "Creating task $TaskName (logon + daily self-heal trigger)..."

schtasks.exe /Create /TN $TaskName /SC ONLOGON /TR $taskCommand /RU $RunAsUser /F | Out-Null
if ($LASTEXITCODE -ne 0) {
    Write-Host "Warning: Cannot create Scheduled Task $TaskName (access denied)."
    Write-Host "Using Startup folder fallback: $startupCmdPath"
    Write-StartupShortcut -FilePath $startupCmdPath -ProjectRoot $projectRoot -PhpExePath $phpExe -WebSocketScriptPath $serverScriptPath
}

# Optional second trigger so task can be manually started from Task Scheduler even after long uptime.
$dailyTaskName = "$TaskName`_Daily"
schtasks.exe /Create /TN $dailyTaskName /SC DAILY /ST $StartTime /TR $taskCommand /RU $RunAsUser /F | Out-Null
if ($LASTEXITCODE -ne 0) {
    Write-Host "Warning: Cannot create Scheduled Task $dailyTaskName (access denied)."
}

if ($UseOnStartup.IsPresent) {
    $startupTaskName = "$TaskName`_Startup"
    Write-Host "Creating optional startup task $startupTaskName ..."
    schtasks.exe /Create /TN $startupTaskName /SC ONSTART /TR $taskCommand /RU $RunAsUser /F | Out-Null
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Warning: Cannot create $startupTaskName (likely needs elevated PowerShell)."
    } else {
        Write-Host "Created $startupTaskName"
    }
}

Write-Host "Done."
Write-Host "Tasks created:"
Write-Host " - $TaskName"
Write-Host " - $dailyTaskName"
if (Test-Path $startupCmdPath) {
    Write-Host "Startup fallback created: $startupCmdPath"
}
Write-Host "Note: Ensure REALTIME_WS_ENABLED=1 in .env before starting tasks."
