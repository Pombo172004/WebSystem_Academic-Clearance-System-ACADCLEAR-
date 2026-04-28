# Update Workflow (Two Laptops)

This project now uses a tracked `VERSION` file for app version.
That means the version is synced through Git (same value on all laptops after pull).

## Do I need to deploy first?
Yes.

For another laptop to be notified about a newer version, you need to:
1. Push your code to GitHub.
2. Push a newer Git tag (example: `v1.0.3`).

The app checks your GitHub repo tags/releases and compares them to local `VERSION`.

## Laptop A (Developer) - Release v1.0.3
From project root (`exampleapp`):

```powershell
cd .\scripts
.\release-version.ps1 -Version v1.0.3 -Branch master
```

What this does:
1. Verifies clean working tree.
2. Updates `VERSION`.
3. Commits release version.
4. Creates tag.
5. Pushes commit + tag to GitHub.

## Laptop B (Older Version) - See Notification + Update
1. Login as `school_admin`.
2. Open **Update** page in the app (`/admin/update`).
3. If GitHub has newer tag (like `v1.0.3`), app shows **New version available**.
4. Click **Pull and Install Update** on the app update page.
5. The app will run `scripts/apply-latest-update.ps1` for this machine.
6. Refresh app and confirm footer/version matches latest `VERSION`.

## Notes
- The Update page button now runs `apply-latest-update.ps1`, which performs `git fetch`, `git pull`, dependency install, asset build, migrations, and cache clearing.
- You can still run `apply-latest-update.ps1` manually on each laptop/server if preferred.
- Keep branch name consistent (`master` in current repo).
