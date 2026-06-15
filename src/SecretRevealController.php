<?php

namespace Anytech\SecretField;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Security\Permission;
use SilverStripe\Security\SecurityToken;
use SilverStripe\SiteConfig\SiteConfig;

class SecretRevealController extends Controller {
    private static $url_segment = 'secret-reveal';

    private static $allowed_actions = ['reveal'];

    public static function reveal_link(): string {
        return SecurityToken::inst()->addToUrl('/secret-reveal/reveal');
    }

    public function reveal(): HTTPResponse {
        if (!Permission::check('ADMIN')) {
            return $this->jsonResponse(['error' => 'Forbidden'], 403);
        }
        if (!SecurityToken::inst()->checkRequest($this->getRequest())) {
            return $this->jsonResponse(['error' => 'Invalid token'], 400);
        }

        $siteConfig = SiteConfig::current_site_config();
        $field = (string)$this->getRequest()->getVar('field');
        if (!$siteConfig->getCMSFields()->dataFieldByName($field) instanceof SecretField) {
            return $this->jsonResponse(['error' => 'Unknown field'], 400);
        }

        return $this->jsonResponse(['value' => (string)$siteConfig->getField($field)]);
    }

    private function jsonResponse(array $body, int $code = 200): HTTPResponse {
        $response = HTTPResponse::create(json_encode($body), $code);
        $response->addHeader('Content-Type', 'application/json');
        return $response;
    }
}
