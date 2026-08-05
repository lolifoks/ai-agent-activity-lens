# Changelog

## 0.2.0 - 2026-08-05

### Added
- Application Password authentication capture
- Rate limiting per tagged AI-agent credential (configurable per-minute limit)
- User profile tagging UI for marking Application Passwords as AI-agent credentials
- Admin dashboard filter to show only tagged credentials
- Configurable retention policy with daily cron cleanup
- Suggested privacy policy content
- Clean uninstall (drops table, options, transients, and user meta)

### Changed
- Database schema now includes `credential_uuid` column with index

## 0.1.0 - Initial release

- Plugin scaffolding
- Custom `wp_aal_activity` table with 8 columns
- REST request logging via `rest_pre_dispatch` and `rest_post_dispatch`
