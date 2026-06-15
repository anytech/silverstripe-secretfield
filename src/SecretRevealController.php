<?php

namespace Anytech\SecretField;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Security\Permission;
use SilverStripe\Security\SecurityToken;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Backs the SecretField reveal button. Returns a stored SiteConfig secret to
 * admins only, so the plaintext stays out of the rendered CMS page until asked
 * for. A field is revealable once a SecretField for it has been rendered to the
 * current admin (registered in session); the optional secret_fields config is a
 * static allowlist for cases where no field is rendered first.
 */
class SecretRevealController extends Controller
{
    // Static allowlist of SiteConfig fields this endpoint may return. Optional:
    // rendering a SecretField registers the field automatically.
    private static $secret_fields = [];

    private static $url_segment = 'secret-reveal';

    private static $allowed_actions = ['reveal'];

    private const SESSION_KEY = 'SecretField.revealable';

    public static function reveal_link(): string
    {
        return SecurityToken::inst()->addToUrl('/secret-reveal/reveal');
    }

    // Records that a SecretField for $field has been rendered, making it revealable.
    public static function register(string $field): void
    {
        $session = Controller::curr()->getRequest()->getSession();
        $registered = (array)$session->get(self::SESSION_KEY);
        if (!in_array($field, $registered, true)) {
            $registered[] = $field;
            $session->set(self::SESSION_KEY, $registered);
        }
    }

    private function isRevealable(string $field): bool
    {
        if (in_array($field, (array)static::config()->get('secret_fields'), true)) {
            return true;
        }
        $registered = (array)$this->getRequest()->getSession()->get(self::SESSION_KEY);
        return in_array($field, $registered, true);
    }

    public function reveal(): HTTPResponse
    {
        if (!Permission::check('ADMIN')) {
            return $this->jsonResponse(['error' => 'Forbidden'], 403);
        }
        if (!SecurityToken::inst()->checkRequest($this->getRequest())) {
            return $this->jsonResponse(['error' => 'Invalid token'], 400);
        }

        $field = (string)$this->getRequest()->getVar('field');
        if (!$this->isRevealable($field)) {
            return $this->jsonResponse(['error' => 'Unknown field'], 400);
        }

        return $this->jsonResponse(['value' => (string)SiteConfig::current_site_config()->getField($field)]);
    }

    private function jsonResponse(array $body, int $code = 200): HTTPResponse
    {
        $response = HTTPResponse::create(json_encode($body), $code);
        $response->addHeader('Content-Type', 'application/json');
        return $response;
    }
}
