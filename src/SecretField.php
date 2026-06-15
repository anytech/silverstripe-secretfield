<?php

namespace Anytech\SecretField;

use SilverStripe\Core\Convert;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\View\Requirements;

/**
 * Stored secret with a masked display and a reveal button. The plaintext is
 * never rendered into the page source: when a value is saved the field shows a
 * masked hint, and the reveal button fetches the value from SecretRevealController
 * (admins only). Submitting blank keeps the stored value (password pattern).
 *
 * Reveal works on disabled fields too (e.g. autofilled tokens) since it only reads.
 */
class SecretField extends TextField
{
    protected bool $multiline = false;

    public function setMultiline(bool $multiline): static
    {
        $this->multiline = $multiline;
        return $this;
    }

    public function Field($properties = [])
    {
        Requirements::javascript('anytech/silverstripe-secretfield:client/js/secret-field.js');
        Requirements::css('anytech/silverstripe-secretfield:client/css/secret-field.css');

        $stored = (string)$this->dataValue();
        $hasValue = $stored !== '';
        $hint = $hasValue ? 'Saved (ends ' . substr($stored, -4) . ') - leave blank to keep' : 'Enter value';

        $name = Convert::raw2att($this->getName());
        $id = Convert::raw2att($this->ID());
        $placeholder = Convert::raw2att($hint);
        $revealUrl = Convert::raw2att(SecretRevealController::reveal_link());
        $disabled = $this->isDisabled() || $this->isReadonly() ? ' disabled' : '';

        if ($this->multiline) {
            $control = "<textarea name=\"{$name}\" id=\"{$id}\" class=\"text\" rows=\"4\" autocomplete=\"off\""
                . " placeholder=\"{$placeholder}\" data-secret-field data-field=\"{$name}\""
                . " data-reveal-url=\"{$revealUrl}\"{$disabled}></textarea>";
        } else {
            $control = "<input type=\"password\" name=\"{$name}\" id=\"{$id}\" class=\"text\" autocomplete=\"new-password\""
                . " placeholder=\"{$placeholder}\" data-secret-field data-field=\"{$name}\""
                . " data-reveal-url=\"{$revealUrl}\"{$disabled}>";
        }

        $button = $hasValue
            ? "<button type=\"button\" class=\"btn btn-outline-secondary secret-field__reveal\" data-target=\"{$id}\">Reveal</button>"
            : '';

        $html = "<div class=\"secret-field\">{$control}{$button}</div>";

        return DBHTMLText::create($this->getName() . '_Field')->setValue($html);
    }

    public function saveInto($record): void
    {
        // Blank submission means "leave unchanged" - never wipe a stored secret.
        if ($this->value === null || $this->value === '') {
            return;
        }
        parent::saveInto($record);
    }
}
