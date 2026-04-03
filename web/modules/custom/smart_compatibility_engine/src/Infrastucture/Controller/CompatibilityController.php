<?php

namespace Drupal\smart_compatibility_engine\Infrastructure\Controller;

use Drupal\smart_compatibility_engine\Application\UseCase;
class CompatibilityController {

  public function check(
    Request $request,
    CheckCpuCompatibilityService $service
  ) {
    $dto = new CheckCpuCompatibilityRequest(
      $request->get('platform_id'),
      $request->get('cpu_id')
    );

    $result = $service->handle($dto);

    return new JsonResponse([
      'compatible' => $result->compatible,
      'messages' => $result->messages
    ]);
  }
}
