param(
    [string]$TaskName = "Aventura_WebSocket_Server"
)

$ErrorActionPreference = "Continue"

$dailyTaskName = "$TaskName`_Daily"
$startupTaskName = "$TaskName`_Startup"
$startupFolder = [Environment]::GetFolderPath('Startup')
$startupCmdPath = Join-Path $startupFolder "$TaskName.cmd"

Write-Host "Removing $TaskName ..."
schtasks.exe /Delete /TN $TaskName /F | Out-Null
if ($LASTEXITCODE -eq 0) {
    Write-Host "Removed $TaskName"
} else {
    Write-Host "$TaskName not found or cannot be removed"
}

Write-Host "Removing $dailyTaskName ..."
schtasks.exe /Delete /TN $dailyTaskName /F | Out-Null
if ($LASTEXITCODE -eq 0) {
    Write-Host "Removed $dailyTaskName"
} else {
    Write-Host "$dailyTaskName not found or cannot be removed"
}

Write-Host "Removing $startupTaskName ..."
schtasks.exe /Delete /TN $startupTaskName /F | Out-Null
if ($LASTEXITCODE -eq 0) {
    Write-Host "Removed $startupTaskName"
} else {
    Write-Host "$startupTaskName not found or cannot be removed"
}

if (Test-Path $startupCmdPath) {
    Remove-Item -Path $startupCmdPath -Force -ErrorAction SilentlyContinue
    if (Test-Path $startupCmdPath) {
        Write-Host "Cannot remove startup fallback file: $startupCmdPath"
    } else {
        Write-Host "Removed startup fallback file: $startupCmdPath"
    }
}

Write-Host "Done."
