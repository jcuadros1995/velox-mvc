# Security policy

## Reporting a vulnerability

Do not disclose vulnerabilities in public issues. Contact the repository owner privately through GitHub's security advisory mechanism.

## Production requirements

- Never commit production credentials, API keys, payment secrets, private keys, database dumps, or customer data.
- Configure secrets in the hosting environment, not in source control.
- Deploy only reviewed commits from protected branches.
- Run the Security and Quality workflow on every pull request and push.
- Keep PHP and third-party dependencies on supported, patched versions.
- Serve over HTTPS and set secure session cookies.
- Point the web server document root to public/.
- Deny direct HTTP access to app/, config/, core/, database/, services/, and backup files.
- Disable display_errors and enable server-side error logging.
- Use a dedicated least-privilege database user.
- Never run demo seeders in production.
