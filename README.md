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

Use `SecretField` in place of `TextField` for any SiteConfig secret. That's all - rendering the field registers it as revealable for the current admin, so no allowlist config is needed.

```php
use Anytech\SecretField\SecretField;

SecretField::create('ApiKey', 'API key');
SecretField::create('ServiceAccountJSON', 'Service account JSON')->setMultiline(true);
```

- The field shows a masked hint when a value is saved; leaving it blank on save keeps the stored value.
- Reveal is restricted to `ADMIN` and CSRF-protected via the CMS security token.
- Only fields actually rendered as a `SecretField` to the current admin are revealable.
- Works on disabled fields too (e.g. autofilled tokens) - reveal only reads.

### Optional static allowlist

If a value must be revealable without a `SecretField` rendering first, declare it explicitly (config arrays merge across projects):

```yaml
Anytech\SecretField\SecretRevealController:
  secret_fields:
    - ApiKey
    - ServiceAccountJSON
```

## License

MIT
