param(
    [Parameter(Mandatory = $true)]
    [string]$Version,

    [string]$Branch = "master"
)

$ErrorActionPreference = "Stop"

if ($Version -notmatch '^[vV]?\d+\.\d+\.\d+$') {
    throw "Version must be in semantic format like v1.0.3 or 1.0.3"
}

$normalizedVersion = if ($Version.StartsWith("v") -or $Version.StartsWith("V")) {
    "v" + $Version.Substring(1)
} else {
    "v$Version"
}

$repoRoot = Split-Path -Parent $PSScriptRoot
Push-Location $repoRoot

try {
    git rev-parse --is-inside-work-tree | Out-Null

    $status = git status --short
    if ($status) {
        Write-Host "Uncommitted changes found:" -ForegroundColor Yellow
        $status | ForEach-Object { Write-Host "  $_" }
        throw "Commit/stash your current changes first, then run this release script again."
    }

    Set-Content -Path (Join-Path $repoRoot "VERSION") -Value $normalizedVersion -NoNewline
    Add-Content -Path (Join-Path $repoRoot "VERSION") -Value ""

    git add VERSION
    git commit -m "release: $normalizedVersion"

    git tag -a $normalizedVersion -m "Release $normalizedVersion"
    git push origin $Branch
    git push origin $normalizedVersion

    Write-Host "Release completed: $normalizedVersion" -ForegroundColor Green
    Write-Host "Next: Create a GitHub Release from tag $normalizedVersion (optional but recommended)." -ForegroundColor Cyan
}
finally {
    Pop-Location
}
