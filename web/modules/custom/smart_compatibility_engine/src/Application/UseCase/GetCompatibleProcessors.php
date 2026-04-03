<?php
// НЕ СДЕЛАНО,  МАЛЕНЬКИЙ КУСОЧЕК, КОТОРЫЙ БУДЕТ В КОДЕ:
$processors = $viewResult;
$results = [];

foreach ($processors as $cpu) {
  $config = new Configuration($platform, $cpu);
  $results[] = $engine->check($config);
}
