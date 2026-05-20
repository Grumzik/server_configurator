<?php

namespace Drupal\tiscom_news_auto\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\tiscom_news_auto\Service\NewsImporter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Manual import controller.
 */
class ManualRunController extends ControllerBase {

  public function __construct(
    protected NewsImporter $importer,
    protected MessengerInterface $messenger,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('tiscom_news_auto.importer'),
      $container->get('messenger')
    );
  }

  public function run(): RedirectResponse {
    $result = $this->importer->runNow();
    $this->messenger->addStatus($this->t('Import completed. Created: @created. Skipped: @skipped.', [
      '@created' => $result['created'],
      '@skipped' => $result['skipped'],
    ]));

    return $this->redirect('tiscom_news_auto.settings');
  }

}
