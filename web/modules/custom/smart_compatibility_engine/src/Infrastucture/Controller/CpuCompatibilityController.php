<?php

namespace Drupal\smart_compatibility_engine\Infrastructure\Drupal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\smart_compatibility_engine\Application\UseCase\CheckCpuCompatibilityService;
use Drupal\smart_compatibility_engine\Application\Dto\CheckCpuCompatibilityRequest;

final class CpuCompatibilityController extends ControllerBase implements ContainerInjectionInterface {

  public function __construct(
    private readonly CheckCpuCompatibilityService $service
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get(CheckCpuCompatibilityService::class)
    );
  }

  public function check(Request $request): JsonResponse {
    $platformId = (int) $request->get('platform_id');
    $cpuId = (int) $request->get('cpu_id');

    $dto = new CheckCpuCompatibilityRequest(
      platformId: $platformId,
      cpuId: $cpuId
    );

    $result = $this->service->handle($dto);

    return new JsonResponse([
      'compatible' => $result->isCompatible,
      'messages' => $result->messages,
    ]);
  }
}
