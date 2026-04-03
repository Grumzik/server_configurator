<?php
namespace Drupal\smart_compatibility_engine\Domain\Rule;

use Drupal\smart_compatibility_engine\Domain\Model\Configuration;

interface CompatibilityRuleInterface {

  public function supports(Configuration $config): bool;

  public function check(Configuration $config): RuleResult;
}


