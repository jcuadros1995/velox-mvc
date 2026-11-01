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

## Security hardening notes

- `database/velox_core.sql` is a schema-only export. Keep real data and development fixtures outside Git.
- Set a canonical `APP_URL` in the protected `.env` file or hosting environment. Production must use HTTPS; do not derive reset links from the incoming Host header.
- The built-in file-backed rate limiter is suitable for a single application host. Multi-node deployments must replace it with shared Redis/database storage so limits apply across all nodes.
- If a database dump, password hash, session token, API secret, or customer record was ever committed, deleting it from the latest revision is not sufficient. Rotate affected credentials/tokens and purge the sensitive objects from every reachable Git history/ref using a coordinated history rewrite.
- Public reservation lookup requires both the reservation code and the customer's email. Keep both values private.
