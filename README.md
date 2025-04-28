# Every.org Nonprofit Profile WordPress Plugin

## Description

This WordPress plugin provides an enhanced Gutenberg block and a shortcode to easily display nonprofit profiles from Every.org on your website. It connects to the Every.org API to fetch nonprofit information and offers various display styles and customization options.

## Features

* **Gutenberg Block:** An intuitive block for searching and embedding nonprofit profiles directly within the WordPress editor.
* **Shortcode:** Use the `[nonprofit_profile]` shortcode for embedding profiles in classic editor or other areas.
* **Every.org API Integration:** Fetches up-to-date nonprofit data including name, description, logo, location, and tags.
* **Multiple Display Styles:** Choose from Standard, Card, Featured, and Minimal display templates.
* **Customizable:** Control description length, visibility of tags/description, button text, and button color.
* **Admin Settings:** Configure your Every.org API key and set default display options.
* **API Connection Test:** Verify your API key connection directly from the settings page.
* **REST API Endpoints:** Provides endpoints for searching nonprofits and fetching details, used by the block editor.
* **Caching:** Caches API responses to improve performance and reduce API calls.
* **Frontend Enhancements:** Includes optional features like donation progress bars and tag tooltips (requires `frontend.js`).
* **TinyMCE Button:** Adds a button to the classic editor for easy shortcode generation.

## Requirements

* WordPress 5.0 or higher (for Gutenberg block support)
* PHP 7.0 or higher
* An Every.org Public API Key (Get one from [Every.org Partners](https://partners.every.org))

## Installation

1.  Download the plugin files (or clone the repository).
2.  Upload the `every-org-nonprofit-profile` folder to your `/wp-content/plugins/` directory.
3.  Activate the plugin through the 'Plugins' menu in WordPress.
4.  Navigate to `Settings` > `Every.org API`.
5.  Enter your Every.org Public API Key and save the settings.
6.  Test the API connection using the button provided on the settings page.

## Usage

### Gutenberg Block

1.  Edit a post or page using the WordPress block editor.
2.  Click the '+' icon to add a new block.
3.  Search for "Nonprofit Profile" or find it under the "Nonprofit Tools" category.
4.  Use the search bar within the block placeholder to find nonprofits by name or cause.
5.  Select a nonprofit from the search results.
6.  Customize the appearance using the block settings in the Inspector Controls sidebar (styles, button text/color, content visibility).

### Shortcode

You can also embed profiles using the shortcode `[nonprofit_profile]`.

**Basic Usage:**

```shortcode
[nonprofit_profile id="everydotorg"]
```

**Available Attributes:**

* `id` (required): The ID (slug or EIN) of the nonprofit on Every.org.
* `style`: The display style (`standard`, `card`, `featured`, `minimal`). Defaults to the style set in plugin settings or `standard`.
* `cta_text`: Text for the donation button. Defaults to the text set in plugin settings or "Donate Now".
* `cta_color`: Hex color code for the donation button (e.g., `#007bff`).
* `show_description`: `true` or `false`. Whether to display the description. Defaults to `true`.
* `description_length`: Maximum number of characters for the description snippet. Defaults to `150`.
* `show_tags`: `true` or `false`. Whether to display the nonprofit tags. Defaults to `true`.
* `show_stats`: `true` or `false`. *(Currently used for potential future donation progress display)*. Defaults to `true`.

**Example with attributes:**

```shortcode
[nonprofit_profile id="doctorswithoutborders" style="card" cta_text="Support Now" cta_color="#ff5733" show_description="false"]
```

A TinyMCE button is available in the classic editor to help generate this shortcode.

## Settings

Access the plugin settings under `Settings` > `Every.org API` in the WordPress admin area.

* **Public API Key:** Your required key from Every.org Partners.
* **Default Display Settings:** Set the default block style and CTA button text for new profiles.
* **Connection Test:** Verify that your API key is working correctly.

## Templates

The plugin uses PHP template files located in the `templates/` directory to render the different profile styles.

* `nonprofit-standard.php`
* `nonprofit-card.php`
* `nonprofit-featured.php`
* `nonprofit-minimal.php`

These templates can be overridden by copying them into your theme's directory within a folder named `enpb-templates`. For example: `wp-content/themes/your-theme/enpb-templates/nonprofit-standard.php`.

Helper functions for use within templates:

* `enpb_process_description($description, $length)`: Trims and cleans the description text.
* `enpb_format_location($location)`: Formats the location string.
* `enpb_get_donation_url($profile_url)`: Generates the direct donation URL.

## Frontend Features (`frontend.js`)

The `assets/js/frontend.js` file provides optional enhancements:

* **Analytics Tracking:** Sends events to Google Analytics or GTM when donation buttons are clicked (if analytics tools are present).
* **Tag Tooltips:** Adds simple descriptive tooltips when hovering over nonprofit tags.
* **Donation Progress Bar:** Can display a progress bar if `data-donation-goal` and `data-donation-current` attributes are present on the `.enpb-nonprofit-profile` element (Requires manual data attribute addition or extension).
* **Card Hover Effects:** Adds subtle hover animations to card and featured styles.
* **Expandable Descriptions:** Provides "Read more" / "Show less" functionality for descriptions exceeding the specified length in standard and card styles.

## Files Included

* `every-org-nonprofit-profile.php`: Main plugin file.
* `includes/`: Contains core PHP functionality.
    * `api.php`: Handles REST API endpoints and Every.org communication.
    * `settings.php`: Manages the admin settings page.
    * `shortcode.php`: Implements the shortcode functionality.
    * `templates.php`: Manages template loading and helper functions.
* `assets/`: Contains CSS and JavaScript files.
    * `css/`: Stylesheets for the editor and frontend.
    * `js/`: JavaScript for the block editor, frontend interactions, and TinyMCE button.
* `templates/`: Contains the default HTML templates for profile styles.



# WordPress Plugin Market Strategy for Every.org 

## Introduction
Every.org, a tech-for-good nonprofit, simplifies online giving by enabling donors to support over 1 million nonprofits through a single platform, with diverse payment methods like credit cards, cryptocurrency, stocks, and donor-advised funds (DAF). Its Charity API allows developers to create innovative giving experiences, making it a strong candidate for expanding into the WordPress plugin market. WordPress powers over 43% of websites, and many nonprofits rely on it for their online presence, presenting a significant opportunity for Every.org to reach developers and nonprofits. This report outlines a strategy for Every.org to develop and market a WordPress plugin, targeting developers to enhance its platform’s adoption and impact.
## Background
Every.org’s mission is to accelerate social impact by streamlining fundraising for nonprofits and donors. Since launching in March 2020, it has helped over 1,600 nonprofits raise $4.5 million, with 46% of donations being recurring. The platform offers no platform fees, only third-party transaction fees (e.g., 2.2% + $0.30 for credit cards), and provides an open-source donate button and a Charity API for developers. The WordPress plugin market is competitive, with established players like GiveWP offering robust donation solutions. However, Every.org’s unique features, such as multi-nonprofit donations and advanced payment methods, position it to carve out a niche by appealing to developers who value flexibility and innovation.
### Strategic Objectives:
 - Expand Reach: Integrate Every.org’s donation capabilities into WordPress sites, reaching nonprofits and donors using the platform.
 - Engage Developers: Attract WordPress developers with a user-friendly, well-documented plugin that leverages Every.org’s API.
 - Differentiate from Competitors: Highlight unique features to stand out in a crowded market.
 - Align with Mission: Maintain a free or low-cost plugin to support Every.org’s nonprofit ethos.

### Plugin Development Strategy
#### Plugin Features
To appeal to developers and nonprofits, the plugin should include:

 - Multi-Nonprofit Donation Forms: Allow users to create forms that enable donors to support multiple nonprofits in one transaction, a feature unique to Every.org.
 - Nonprofit Search Integration: Leverage the Nonprofit Search API to enable users to search and select from over 1 million 501(c)(3) organizations directly within WordPress.
 - Fundraiser Creation: Enable users to create and manage fundraising campaigns, using the Fundraisers API to track progress and share links.
 - Analytics Dashboard: Provide a WordPress dashboard displaying metrics like total donations, donor counts, and campaign performance, powered by API data.
 - Advanced Payment Methods: Support cryptocurrency, stock donations, DAF, and standard methods (e.g., PayPal, Apple Pay), setting it apart from competitors.
 - Customization Options: Offer shortcodes, Gutenberg blocks, and Elementor widgets for flexible integration with page builders.
 - Developer Hooks: Include actions and filters for developers to extend functionality, enhancing customization.

#### Technical Considerations

 - API Integration: Use Every.org’s Charity API (Every.org Charity API) for donation processing, nonprofit search, and fundraiser management. The plugin should handle public API keys for non-commercial use, ensuring compliance with API terms.
 - Open-Source Model: Release the plugin as open-source under the GPL license to align with WordPress.org guidelines and Every.org’s nonprofit mission.
 - Security: Implement best practices for handling sensitive data, such as encrypting API keys and securing donation transactions, to build trust.
 - Compatibility: Ensure compatibility with popular WordPress themes (e.g., Astra, GeneratePress) and page builders (e.g., Elementor, Divi).
 - Performance: Optimize API calls to minimize latency, caching nonprofit data where appropriate to enhance user experience.

#### Development Approach

 - In-House vs. Agency: Every.org, as a tech-focused nonprofit, may have the capacity to develop the plugin in-house. Alternatively, partnering with a reputable WordPress development agency could accelerate development while ensuring quality.
 - Timeline: Aim for an initial release within 6-9 months, starting with core features (donation forms, nonprofit search) and adding advanced features (analytics, fundraisers) in subsequent updates.
 - Testing: Conduct thorough testing across WordPress versions, themes, and plugins to ensure compatibility and reliability.

#### Marketing Strategy
Target Audience
The primary audience is WordPress developers, including:

 - Freelance Developers: Building sites for nonprofits and seeking easy-to-use donation solutions.
 - Agencies: Managing multiple nonprofit clients and needing scalable tools.
 - Nonprofit Staff: With basic WordPress knowledge, looking for plug-and-play solutions.

Key Marketing Tactics

WordPress.org Repository Listing:
Submit the plugin to the WordPress.org plugin repository (WordPress Plugins) for maximum visibility, as it’s the primary source for WordPress users.
Ensure the plugin meets WordPress guidelines, including open-source licensing and clear documentation.


Comprehensive Documentation:
Create a dedicated developer portal on Every.org’s website with tutorials, code snippets, and API guides (Every.org Charity API Docs).
Include video walkthroughs and sample use cases (e.g., embedding a multi-nonprofit donation form).


Community Engagement:
Participate in WordPress forums, Reddit (e.g., r/WordPress), and X to answer questions and promote the plugin.
Attend WordCamps and sponsor events to network with developers and nonprofits.
Contribute to WordPress open-source projects to build credibility.


Content Marketing:
Publish blog posts on Every.org’s site comparing the plugin to competitors like GiveWP, highlighting unique features.
Create tutorials on platforms like WPBeginner (WPBeginner) to reach nonprofit audiences.
Share case studies of nonprofits successfully using the plugin.


Partnerships:
Collaborate with popular WordPress themes (e.g., Astra) and page builders (e.g., Elementor) for co-marketing or bundled integrations.
Partner with nonprofit networks to promote the plugin to their members.


Developer Incentives:
Offer badges or certifications for developers who complete a plugin integration course.
Feature top implementations on Every.org’s website or social media.


Social Proof:
Secure endorsements from prominent WordPress developers or nonprofit leaders.
Highlight Every.org’s track record (e.g., $4.5 million raised for 1,600 nonprofits) to build trust.



Unique Selling Propositions (USPs)

Multi-Nonprofit Donations: Unlike most donation plugins, the plugin allows donors to support multiple nonprofits in one transaction.
Advanced Payment Methods: Support for cryptocurrency, stocks, and DAF appeals to innovative nonprofits and donors.
No Platform Fees: Only third-party transaction fees (e.g., 2.2% + $0.30 for cards) make it cost-effective (Every.org).
Nonprofit Database: Access to over 1 million verified nonprofits simplifies setup for users.
Mission Alignment: As a nonprofit, Every.org resonates with nonprofit users, fostering trust.

Competitive Landscape
The WordPress donation plugin market is competitive, with key players like GiveWP and Charitable. Below is a comparison to inform Every.org’s differentiation strategy:



Feature
Every.org Plugin (Proposed)
GiveWP
Charitable



Multi-Nonprofit Donations
Yes
No
No


Payment Methods
Crypto, stocks, DAF, standard
Standard + limited crypto
Standard


Nonprofit Search
Yes (1M+ nonprofits)
Limited
Limited


Fundraiser Creation
Yes
Yes (add-on)
Yes


Platform Fees
None
None (add-on fees)
None


Open-Source
Yes
Yes
Yes


Developer Documentation
Planned (extensive)
Extensive
Moderate



GiveWP: Offers customizable forms, donor management, and add-ons for peer-to-peer fundraising, but lacks multi-nonprofit donations and advanced payment methods (GiveWP).
Charitable: Focuses on simplicity and crowdfunding, but has fewer integrations and no nonprofit database.
Every.org Advantage: The proposed plugin’s multi-nonprofit capability, advanced payment options, and nonprofit database provide a unique edge, while its free model competes with GiveWP’s add-on costs.

Implementation Plan

Phase 1: Research and Planning (1-2 months):
Analyze competitor plugins (e.g., GiveWP, Charitable) for feature gaps.
Define plugin requirements based on API capabilities and user needs.
Decide on in-house development or agency partnership.


Phase 2: Development (4-6 months):
Build core features: donation forms, nonprofit search, and basic analytics.
Integrate with Gutenberg and Elementor for user-friendly setup.
Test for compatibility, security, and performance.


Phase 3: Launch and Marketing (2-3 months):
Submit to WordPress.org and launch a developer portal.
Promote through WordPress forums, nonprofit networks, and X posts.
Collect user feedback for iterative improvements.


Phase 4: Growth and Iteration (Ongoing):
Add advanced features (e.g., fundraiser management, detailed analytics).
Expand partnerships with WordPress ecosystem players.
Monitor adoption and refine marketing based on user data.



Potential Challenges and Mitigations

Competition: Established plugins like GiveWP have large user bases. Mitigation: Emphasize unique features and leverage Every.org’s nonprofit credibility.
Developer Adoption: Developers may resist switching from existing solutions. Mitigation: Offer migration guides and highlight ease of use.
Technical Complexity: API integration and WordPress compatibility require expertise. Mitigation: Invest in quality development and testing.
Awareness: As a newer player, Every.org may lack visibility. Mitigation: Aggressive community engagement and content marketing.

Expected Outcomes

Increased Adoption: Reach thousands of WordPress-based nonprofits, expanding Every.org’s user base.
Developer Engagement: Build a loyal developer community through robust support and documentation.
Enhanced Impact: Enable more nonprofits to raise funds efficiently, aligning with Every.org’s mission.
Market Positioning: Establish Every.org as a key player in the WordPress donation plugin market.

Conclusion
Every.org can successfully break into the WordPress plugin market by developing a free, open-source plugin that leverages its Charity API to offer multi-nonprofit donation forms, nonprofit search, fundraiser creation, and advanced payment methods. By providing extensive documentation, engaging with the WordPress and nonprofit communities, and highlighting cost savings and mission alignment, Every.org can attract developers and nonprofits. Differentiating from competitors like GiveWP through unique features and a seamless user experience will ensure the plugin’s success, furthering Every.org’s goal of accelerating social impact.

