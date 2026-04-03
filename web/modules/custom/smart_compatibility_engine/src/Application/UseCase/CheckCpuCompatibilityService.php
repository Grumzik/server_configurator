<?php
namespace Drupal\smart_compatibility_engine\Application\UseCase;

use Drupal\smart_compatibility_engine\Application\Dto;

final class CheckCpuCompatibilityService {

  public function __construct(
    private PlatformRepository $platforms,
    private CpuRepository $cpus,
    private CompatibilityEngine $engine
  ) {}

  public function handle(
    CheckCpuCompatibilityRequest $dto
  ): CompatibilityResponseDTO {

    $platform = $this->platforms->get($dto->platformId);
    $cpu = $this->cpus->get($dto->cpuId);

    $config = new Configuration($platform, $cpu);

    $result = $this->engine->check($config);

    return new CompatibilityResponseDTO(
      $result->isCompatible(),
      $result->messages()
    );
  }
}
