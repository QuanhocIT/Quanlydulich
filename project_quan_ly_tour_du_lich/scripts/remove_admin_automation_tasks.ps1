param(
    [string]$TaskPrefix = "Aventura_Admin_Automation"
)

$ErrorActionPreference = "Continue"

$taskAll = "$TaskPrefix`_All"
$taskDigest = "$TaskPrefix`_DailyDigest"

Write-Host "Removing $taskAll ..."
schtasks.exe /Delete /TN $taskAll /F | Out-Null
if ($LASTEXITCODE -eq 0) {
    Write-Host "Removed $taskAll"
} else {
    Write-Host "$taskAll not found or cannot be removed"
}

Write-Host "Removing $taskDigest ..."
schtasks.exe /Delete /TN $taskDigest /F | Out-Null
if ($LASTEXITCODE -eq 0) {
    Write-Host "Removed $taskDigest"
} else {
    Write-Host "$taskDigest not found or cannot be removed"
}

Write-Host "Done."
