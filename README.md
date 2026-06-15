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

Use `SecretField` in place of `TextField` for any SiteConfig secret, then declare which fields the reveal endpoint may return.

```php
use Anytech\SecretField\SecretField;

SecretField::create('ApiKey', 'API key');
SecretField::create('ServiceAccountJSON', 'Service account JSON')->setMultiline(true);
```

Declare the revealable fields (config arrays merge across projects):

```yaml
Anytech\SecretField\SecretRevealController:
  secret_fields:
    - ApiKey
    - ServiceAccountJSON
```

- The field shows a masked hint when a value is saved; leaving it blank on save keeps the stored value.
- Reveal is restricted to `ADMIN` and CSRF-protected via the CMS security token.
- Works on disabled fields too (e.g. autofilled tokens) - reveal only reads.

## License

MIT
