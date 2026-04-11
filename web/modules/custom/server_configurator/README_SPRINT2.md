Sprint 2 for server_configurator

What changed:
- backend orchestration moved from procedural include files into first classes/services
- server_configurator.module is now thin: hooks + thin wrappers for Drupal callbacks
- new services/classes:
  - Service/ConfiguratorSettingsBuilder
  - Service/ConfiguratorBootstrapManager
  - Service/ConfiguratorFieldLocator
  - Service/FormAlterRouter
  - Form/MainConfiguratorFormAlter
  - Form/MainConfiguratorDependencyFeature
  - Form/MainConfiguratorProcessorFeature

Important:
- Delete old Sprint 1 include files if they exist and are still required from module:
  - includes/server_configurator.bootstrap.inc
  - includes/server_configurator.form_helpers.inc
  - includes/server_configurator.form_dependencies.inc
  - includes/server_configurator.form_processors.inc
  - includes/server_configurator.form_main.inc
- After replacing files run: drush cr

Smoke tests:
1. Main Configurator:
   - cpu_vendor -> cpu_generation_entity_selection
   - cpu_generation_entity_selection + form_factor_units -> platform_entity_selection
   - show_processors_button shows compatible processors
   - storage limits still work
2. Small configurator:
   - page still loads
   - summary works
   - storage behavior still works
