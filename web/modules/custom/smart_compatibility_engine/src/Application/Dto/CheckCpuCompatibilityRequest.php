<?php
namespace Drupal\smart_compatibility_engine\Application\Dto;
final class CheckCpuCompatibilityRequest {

  public function __construct(
    public int $platformId,
    public int $cpuId
  ) {}
}
