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
 * for. Projects declare which fields are revealable via the secret_fields config.
 */
class SecretRevealController extends Controller
{
    // SiteConfig fields this endpoint may return. Declared per project via YAML.
    private static $secret_fields = [];

    private static $url_segment = 'secret-reveal';

    private static $allowed_actions = ['reveal'];

    public static function reveal_link(): string
    {
        return SecurityToken::inst()->addToUrl('/secret-reveal/reveal');
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
        if (!in_array($field, (array)static::config()->get('secret_fields'), true)) {
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
