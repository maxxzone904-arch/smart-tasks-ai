# Release Notes

## v1.3.0 - Living Glass UI & Rich Text
* **Premium Design**: Completely overhauled the frontend to use a "Living Glass" aesthetic, featuring frosted glass cards, dynamic background orbs, and gradients across the Auth and Dashboard pages.
* **Rich Text Editing**: Integrated Quill.js for the task description field, allowing users to write and save complex HTML-formatted task instructions.
* **UI Enhancements**: 
  - Added a Tailwind confirmation modal for the logout action to prevent accidental logouts.
  - Upgraded the navbar with a sleek, automatically generated user profile avatar chip.
* **Database Optimization**: Dropped the unused `is_ai_generated` column from the `tasks` schema to keep the database lean and performant.

## v1.2.0 - Security & Login Refactor
* **UI/UX Enhancement**: Refactored the login page to use the modern asynchronous `fetch` API, providing a seamless experience with loading states and no page reloads.
* **Premium Design**: Added a high-end, Context7-inspired HTML 500 error screen that displays if the database goes offline.
* **Security Improvements**: Mitigated session fixation attacks on login (`session_regenerate_id`) and protected against username enumeration timing attacks.
* **Architecture**: Decoupled database connection error handling, implementing strict exceptions (`MYSQLI_REPORT_STRICT`) and application-specific custom logging (`logs/app-error.log`).
## v1.1.0 - Inline Editing & AI Update
* **UI/UX Enhancement**: Overhauled the dashboard task list to support full inline editing. Users can now edit the task title, description, priority, and status without navigating to a new page.
* **AI Model Upgrade**: Updated the Gemini API integration from `gemini-1.5-flash` to Google's newer `gemini-2.5-flash` model.
* **Bug Fix**: Fixed a local SSL verification issue by migrating from `file_get_contents` to `cURL` for API requests.

## v1.0.0 - Initial Release
* **Core Functionality**: Setup complete user authentication flow (Registration, Login, Logout) with secure password hashing.
* **Smart Task Creation**: Integrated the "Brain Dump" feature that uses Google Gemini to parse unstructured text (like chat logs) into actionable tasks.
* **Modular Architecture**: Built an `AIServiceInterface` to allow easy swapping of AI models (e.g., to ChatGPT or Claude) in the future.
* **Premium Design**: Fully styled with Tailwind CSS, including a functional Dark Mode toggle.
* **Routing**: Implemented `.htaccess` rules for clean, extension-less URLs.
* **Security**: Migrated configuration variables (API Keys, DB credentials) out of tracked PHP files and into a `.env` file.
