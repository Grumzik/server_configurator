<?php
namespace Drupal\smart_compatibility_engine\Domain\Rule;

final class CompatibilityResult {

  public function __construct(
    private readonly array $ruleResults
  ) {}

  public function isCompatible(): bool {
    foreach ($this->ruleResults as $result) {
      if (!$result->passed()) {
        return false;
      }
    }
    return true;
  }

  public function messages(): array {
    return array_map(
      fn($r) => $r->message(),
      $this->ruleResults
    );
  }
}
