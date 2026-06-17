=== Auto Ping Booster ===
Contributors: same2cool
Tags: seo, indexing, indexnow, ping, booster, google indexing
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 2.10
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Instantly notify search engines and indexing networks using modern protocols like IndexNow when content is published or updated.

== Description ==

Stop relying on legacy, outdated XML-RPC ping networks that search engines ignore. **Auto Ping Booster** modernizes your site's SEO discovery pipeline by dropping dead technologies and utilizing lightning-fast indexing mechanisms.

When you transition a post, page, or custom post type to live status, Auto Ping Booster securely formats an automated ping payload, bypassing draft update spamming to ensure search spiders index your fresh content cleanly.

### ⚡ Free Core Features:
* **IndexNow Engine Integration:** Instantly ping Bing, Yandex, Seznam, and other supporting crawlers simultaneously with zero performance footprint.
* **Smart State Transitions:** Optimized framework fires requests *only* when status truly changes to publish—protecting your API quotas from draft/typo save spamming.
* **Dynamic Key Verification:** Automated endpoint hosting handles internal `.txt` authorization keys seamlessly without forcing you to modify server root structures manually.
* **Integrated Activity Logs:** Optional background logging system tracks HTTP return payloads to let you troubleshoot response states directly.

### 🚀 Upcoming Premium Upgrades:
We are currently building advanced enterprise features to take your automated search traffic further:
1. **Google Indexing API Engine (Pro):** Bypasses traditional discovery loops by passing URLs directly to Google's real-time API cluster using secure service accounts.
2. **Autonomous AI Schema & Semantic Meta Tier (AI Tier):** Deep contextual post analysis automatically generates JSON-LD data structures and optimized snippet content before triggering server indexes.

== Installation ==

1. Upload the entire `auto-ping-booster` directory to your `/wp-content/plugins/` pipeline.
2. Activate the application inside the native WordPress **Plugins** control panel.
3. Access the tailored interface via the **APB Pro** dash module down your sidebar navigation to enter your IndexNow key and set parameters.

== Frequently Asked Questions ==

= Does this plugin still utilize legacy XML-RPC pings? =
No. Legacy protocols have been entirely removed. This tool builds natively on top of direct API endpoints and verified JSON handshakes.

= Will this instantly improve my organic keyword positioning? =
It accelerates crawl discovery and priority index scheduling. While indexing is a mandatory requirement for organic visibility, your actual keyword ranking relies on content quality and core optimization principles.

= Does running background handshakes impact frontend loading metrics? =
Not at all. The underlying logic executes exclusively during admin content status changes and processes as isolated server requests, keeping public frontend site delivery completely unaffected.

= Does it support WooCommerce products or custom post structures? =
Yes. By decoupling the core mechanism and shifting to status changes, the underlying handler is ready to process any content classification schema.

== Changelog ==

= 2.10 =
* Performance: Refactored operational hooks to `transition_post_status` to stop api multi-call flooding on typo patches.
* Feature: Introduced automated internal key rewrite handlers to serve dynamic verification documents automatically.
* UI: Rebuilt settings panel architecture to lay foundations for upcoming premium extensions.
* Version Check: Full compatibility certification up to WordPress 6.9.

= 2.01 =
* Complete plugin rewrite.
* Removed outdated legacy ping loops.
* Implemented modern IndexNow base layer.