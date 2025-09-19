# Prayer Wall (PHP + MySQL + Bootstrap)

This is a minimal prayer request app you can host on any PHP 8 + MySQL server and embed in WordPress via an `<iframe>`.

## Features
- Prayer Wall: list of **approved** requests with counters for prayers and comments
- `+ PRAY` button (like) stored per user/IP (guests allowed)
- Detail view with **Share** (modal with URL), **Flag** (modal with reasons), comments list
- Add comment from **dropdown** (canned messages)
- Submit **Request Prayer** (max 1000 chars, anonymous switch, moderation notice)
- Account page with **Account Settings** and **Your Activity** (requests, prayers, comments counts)
- Basic admin tab (for users with `is_admin=1`) to approve/reject pending requests
- Fully responsive with **Bootstrap 5**; modals use Bootstrap (no custom JS needed).

## Install
1. Create a MySQL database (e.g., `prayerwall`) and user.
2. Import `schema.sql`.
3. Copy `/public` to your web root (or keep structure as-is).
4. Copy `config.sample.php` to `config.php` and set DB credentials and `APP_BASE_URL`.
5. Visit `/public/index.php` in your browser.
6. **First login** creates a user automatically. Use SQL to set yourself as admin:
   ```sql
   UPDATE users SET is_admin=1 WHERE email='you@example.com';
   ```

## Embed in WordPress
Create a page and add an **HTML block** with:
```html
<iframe src="https://yourdomain.com/prayerwall/public/index.php" style="width:100%;min-height:1200px;border:0;" loading="lazy"></iframe>
```

## Notes
- No custom JavaScript is required; Bootstrap's JS bundle handles modals. The Share modal shows the URL ready to copy manually to clipboard.
- All actions use standard POST forms with CSRF protection and full-page reloads.
