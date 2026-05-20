# Tiscom News Auto

Drupal 10 module for automatic preparation of hardware news drafts.

## What it does

- Fetches recent hardware news from TechSpot Hardware.
- Selects the most relevant items for a server hardware audience.
- Rewrites them in Russian through the OpenAI Responses API.
- Creates unpublished Drupal `news` nodes.
- Downloads article images into `public://news-auto/` when enabled.
- Stores processed source URLs in `tiscom_news_auto_source_item` to avoid duplicates.
- Sends Telegram messages with inline buttons: View, Publish, Reject.

## Drupal fields used

- Content type: `news`
- Announcement: `field_news_anons`
- Body: `body`
- Image: `field_image`

## Install

1. Put the module into `web/modules/custom/tiscom_news_auto`.
2. Enable it:
   `drush en tiscom_news_auto -y`
3. Open `/admin/config/tiscom/news-auto`.
4. Add OpenAI API key, Telegram bot token, Telegram chat ID, and webhook secret.
5. Save settings.
6. Set the Telegram webhook:
   `https://api.telegram.org/bot<TELEGRAM_BOT_TOKEN>/setWebhook?url=https://tiscom.ru/tiscom-news-auto/telegram/webhook/<WEBHOOK_SECRET>`
7. Run the manual import once from the settings page.

## Important

Downloading TechSpot images is implemented as a temporary test mode. Check image usage rights before using this on production pages.
