<?php

namespace Drupal\Tests\jsonlog\Unit;

use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\StringTranslation\PluralTranslatableMarkup;

/**
 * Mock translation service.
 */
class MockTranslation implements TranslationInterface {

  /**
   * {@inheritDoc}
   */
  public function translate($string, array $args = [], array $options = []) {
    return new TranslatableMarkup($string, $args, $options, $this);
  }

  /**
   * {@inheritDoc}
   */
  public function translateString(TranslatableMarkup $translated_string) {
    return $translated_string->getUntranslatedString();
  }

  /**
   * {@inheritDoc}
   */
  public function formatPlural($count, $singular, $plural, array $args = [], array $options = []) {
    return new PluralTranslatableMarkup($count, $singular, $plural, $args, $options, $this);
  }

}
