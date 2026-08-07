== Description ==

AI Agent Activity Lens provides observability and guardrails for external tools and AI agents that access WordPress through Application Passwords.

WordPress provides basic details about Application Passwords, but it does not include a detailed activity history for each credential. This plugin records REST API requests authenticated through Application Passwords and allows administrators to tag credentials used by AI agents.

Features:

* Logs REST requests authenticated through Application Passwords
* Records user, credential, route, method, response status, duration, IP address, and timestamp
* Lets administrators tag credentials as AI-agent credentials
* Filters activity to tagged credentials
* Applies a configurable per-credential request limit
* Returns HTTP 429 when a tagged credential exceeds its limit
* Automatically deletes old activity according to the configured retention period

The plugin does not automatically detect whether a credential is used by an AI agent. Administrators explicitly tag applicable credentials.

No data is transmitted to an external service.

== Installation ==

1. Upload the `ai-agent-activity-lens` folder to `/wp-content/plugins/`.
2. Activate AI Agent Activity Lens from the Plugins screen.
3. Create an Application Password for a WordPress user.
4. Open the user profile and mark the credential as used by an AI agent.
5. Configure rate limiting and retention under Settings > AI Activity Lens.
6. View recorded activity under AI Activity.

Application Password authentication normally requires HTTPS. It is also available in local environments where `WP_ENVIRONMENT_TYPE` is set to `local`.

== Frequently Asked Questions ==

= Does this replace the WordPress MCP Adapter? =

No. The plugin works alongside tools that authenticate through WordPress Application Passwords and use the REST API.

= Does it automatically detect AI agents? =

No. An administrator explicitly marks an Application Password as an AI-agent credential.

= Are anonymous REST requests logged? =

No. Only REST requests authenticated successfully through Application Passwords are logged.

= What happens when a credential exceeds its limit? =

The plugin returns an HTTP 429 response until the current rate-limit window ends.

= Does the plugin transmit activity externally? =

No. Activity is stored in the local WordPress database.

== Screenshots ==

1. Application Password activity dashboard.
2. Activity filtered to tagged AI-agent credentials.
3. AI-agent credential controls on a user profile.
4. Rate-limit and activity-retention settings.

== Changelog ==

= 0.2.0 =

* Added Application Password request logging.
* Added the activity dashboard.
* Added AI-agent credential tagging.
* Added tagged-credential filtering.
* Added per-credential rate limiting.
* Added configurable activity retention.
* Added privacy-policy integration.

== Privacy ==

AI Agent Activity Lens stores REST API activity in a custom database table.

Stored information may include:

* WordPress user ID
* Application Password UUID
* REST route and request method
* Response status code
* Request duration
* Source IP address
* Request timestamp

This information is stored locally for security monitoring and troubleshooting. The plugin does not transmit it to an external service.

Administrators can configure how long activity records are retained. Deleting the plugin removes its custom table, options, tagged credential metadata, scheduled event, and rate-limit transients.