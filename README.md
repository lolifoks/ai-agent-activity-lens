# AI Agent Activity Lens

AI Agent Activity Lens is a WordPress security and observability plugin for monitoring REST API activity authenticated through Application Passwords.

## Why I built it

WordPress Application Passwords allow external tools and AI agents to access the REST API, but WordPress does not provide a detailed per-credential activity log or rate limiting.

This plugin adds visibility and guardrails without replacing WordPress authentication or the official MCP Adapter.

## Features

- Logs REST requests authenticated through Application Passwords
- Records credential, user, route, HTTP method, status, duration, IP, and timestamp
- Lets administrators tag credentials as AI-agent credentials
- Filters the dashboard to tagged credentials
- Applies configurable per-credential rate limits
- Returns HTTP 429 when a tagged credential exceeds its limit
- Automatically removes activity older than the configured retention period
- Uses WordPress capabilities, nonces, prepared queries, user meta, transients, and WP-Cron

## How it works

1. WordPress authenticates an Application Password.
2. The plugin captures the credential UUID and user ID.
3. REST requests using that credential are logged.
4. Tagged AI-agent credentials can be filtered and rate-limited.
5. Old activity rows are deleted daily according to the retention setting.

## Screenshots

Add screenshots here after capturing:

1. Activity dashboard
2. Tagged-only filter
3. User-profile credential tagging
4. Rate-limit and retention settings
5. HTTP 429 response

## Installation

1. Download or clone the repository.
2. Copy `ai-agent-activity-lens` into `wp-content/plugins/`.
3. Activate AI Agent Activity Lens.
4. Create an Application Password for a WordPress user.
5. Mark the credential as an AI-agent credential from the user profile.
6. Configure rate limiting under Settings > AI Activity Lens.

Application Passwords require HTTPS, or a WordPress environment configured as `local`.

## Development setup

The plugin was developed against a local Docker Compose WordPress environment with MariaDB and WP-CLI.

Example authenticated request:

```bash
curl -u "username:application-password" \
  http://localhost:8080/wp-json/wp/v2/users/me
