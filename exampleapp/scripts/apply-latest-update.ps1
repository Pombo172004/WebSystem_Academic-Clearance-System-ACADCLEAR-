param(
    [string]$Branch = "master"
)

$ErrorActionPreference = "Stop"
$repoRoot = Split-Path -Parent $PSScriptRoot

Push-Location $repoRoot

try {
    git rev-parse --is-inside-work-tree | Out-Null

    $status = git status --short
    if ($status) {
        Write-Host "Uncommitted changes found:" -ForegroundColor Yellow
        $status | ForEach-Object { Write-Host "  $_" }
        throw "Commit/stash your current changes first, then run update script again."
    }

    Write-Host "Fetching latest changes..." -ForegroundColor Cyan
    git fetch origin
    git checkout $Branch
    git pull origin $Branch

    if (Test-Path "composer.json") {
        Write-Host "Installing PHP dependencies..." -ForegroundColor Cyan
        composer install --no-interaction --prefer-dist
    }

    if (Test-Path "package.json") {
        Write-Host "Installing Node dependencies and building assets..." -ForegroundColor Cyan
        npm install
        npm run build
    }

    Write-Host "Running Laravel update tasks..." -ForegroundColor Cyan
    php artisan migrate --force
    php artisan optimize:clear

    Write-Host "Update complete. Current version:" -ForegroundColor Green
    Get-Content "VERSION"
}
finally {
    Pop-Location
}
