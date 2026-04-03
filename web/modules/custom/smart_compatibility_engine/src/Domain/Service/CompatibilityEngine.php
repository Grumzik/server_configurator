<?php

namespace Drupal\smart_compatibility_engine\Domain\Service;

use Drupal\smart_compatibility_engine\Domain\Model\Configuration;
use Drupal\smart_compatibility_engine\Domain\Rule\CompatibilityRuleInterface;
use Drupal\smart_compatibility_engine\Domain\Rule\CompatibilityResult;

final class CompatibilityEngine {

  /**
   * @param CompatibilityRuleInterface[] $rules
   */
  public function __construct(
    private readonly array $rules
  ) {}

  public function check(Configuration $config): CompatibilityResult {
    $results = [];

    foreach ($this->rules as $rule) {
      if (!$rule->supports($config)) {
        continue;
      }

      $results[] = $rule->check($config);
    }

    return new CompatibilityResult($results);
  }
}
