# SilverStripe Secret Field

A masked form field for SilverStripe SiteConfig secrets (API keys, tokens, service-account JSON). The stored value is never rendered into the page source; an admin-only reveal button fetches it on demand.

## Requirements

- SilverStripe `^6.1`
- PHP `^8.3`

## Installation

```bash
composer require anytech/silverstripe-secretfield
```

## Usage

Use `SecretField` in place of `TextField` for any SiteConfig secret. That's all - no config to declare.

```php
use Anytech\SecretField\SecretField;

SecretField::create('ApiKey', 'API key');
SecretField::create('ServiceAccountJSON', 'Service account JSON')->setMultiline(true);
```

- The field shows a masked hint when a value is saved; leaving it blank on save keeps the stored value.
- Reveal is restricted to `ADMIN` and CSRF-protected via the CMS security token.
- A field is revealable only if it is declared as a `SecretField` on SiteConfig.
- Works on disabled fields too (e.g. autofilled tokens) - reveal only reads.

## License

MIT
