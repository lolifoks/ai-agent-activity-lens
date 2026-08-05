=== AI Agent Activity Lens ===
Contributors: marijalekic
Tags: application passwords, rest api, ai agents, security, observability
Requires at least: 6.0
Requires PHP: 7.4
Stable tag: 0.2.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Observability and rate limiting for AI-agent credentials using WordPress Application Passwords.

== Description ==

AI Agent Activity Lens records REST API activity authenticated through WordPress Application Passwords. Administrators can tag credentials as AI-agent credentials, review their activity, filter the dashboard, apply per-credential rate limits, and automatically delete old activity records.

This file is an initial placeholder and should be expanded before wordpress.org submission.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate AI Agent Activity Lens.
3. Create an Application Password for a WordPress user.
4. Tag it as an AI-agent credential on the user profile.
5. Configure rate limiting and retention under Settings, AI Activity Lens.

== Changelog ==

= 0.2.0 =
* Added Application Password activity logging, credential tagging, dashboard filtering, rate limiting, and retention.
