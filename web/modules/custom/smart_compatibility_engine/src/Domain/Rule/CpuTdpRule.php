<?php

namespace Drupal\smart_compatibility_engine\Domain\Rule;

use Drupal\smart_compatibility_engine\Domain\Model\Configuration;
final class CpuTdpRule implements CompatibilityRuleInterface {

  public function supports(Configuration $config): bool {
    return $config->cpu() !== null;
  }

  public function check(Configuration $config): RuleResult {
    $cpu = $config->cpu();
    $platform = $config->platform();

    if ($cpu->tdp() <= $platform->maxTdp()) {
      return new RuleResult(true, 'CPU TDP is compatible');
    }

    return new RuleResult(
      false,
      'CPU TDP exceeds platform limit'
    );
  }
}

