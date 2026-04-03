<?php

namespace Drupal\smart_compatibility_engine\Domain\Rule;

final class RuleResult {

  public function __construct(
    private readonly bool $passed,
    private readonly string $message
  ) {}

  public function passed(): bool {
    return $this->passed;
  }

  public function message(): string {
    return $this->message;
  }
}
