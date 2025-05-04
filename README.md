# Gated Content Block

A WordPress plugin that provides a Gutenberg block for gating content behind a name/email form with reCAPTCHA v3. Submissions are stored in the database and the block supports AJAX unlocking, "remember me" for 30 days, and full customization in the block editor.

## Features
- Gutenberg block for gating content
- Name and email form with privacy notice
- reCAPTCHA v3 integration (site/secret keys via settings)
- AJAX form submission (no reload)
- Stores submissions (name, email, time, post) in a custom DB table
- "Remember me" option (30 days, via localStorage)
- Only one block per post (greys out if already used)
- All text/labels customizable in the editor
- Default WordPress styles
- Bedrock/Trellis compatible

## Setup
1. **Build JS assets:**
   - Run `npm install`
   - Run `npm run build` (or `npm run start` for development)
2. **Activate the plugin** in WordPress admin.
3. **Configure reCAPTCHA v3 keys** in Settings > Gated Content Block.
4. **Add the block** to your post/page using the block editor.

## Privacy
Your information will not be used for any other purpose.

## Development
- JS build uses `@wordpress/scripts`.
- PHP and JS code follow WordPress best practices.

---

For Bedrock/Trellis: Place the plugin in `site/web/app/plugins/` or use Composer if desired.
