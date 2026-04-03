# Server Configurator

Custom Drupal 10 module for implementing a server hardware configurator.

The module provides frontend and backend logic for selecting compatible server components
(CPU, platform, memory, etc.) based on predefined compatibility rules.

---

## Purpose

The purpose of this module is to:

- implement a configurable server configurator as a standalone Drupal module;
- encapsulate all configurator-related logic (JS, filters, compatibility rules);
- avoid coupling configurator logic to themes or unrelated modules;
- provide a foundation for further extension and integration.

This module is part of a larger server catalog and configuration system.

---

## Scope (current state)

At the current stage, the module:

- attaches a JavaScript-based configurator to server node pages;
- uses Drupal libraries system for JS loading;
- limits execution to nodes of bundle `server`;
- prepares the groundwork for:
  - compatibility filtering,
  - dynamic UI behavior,
  - future backend integration.

Business logic is intentionally minimal at this stage.

---

## Architecture Overview

### Backend

- Drupal 10 custom module
- Uses `hook_page_attachments()` to attach assets conditionally
- Node type: `server`
- Future integration points:
  - Views
  - Custom services
  - Configuration entities

### Frontend

- JavaScript attached via `libraries.yml`
- Uses:
  - `Drupal.behaviors`
  - `core/once`
- Metadata is expected to be passed via `data-*` attributes (no HTML parsing)

---

## Installation

1. Place the module in:
   /modules/custom/server_configurator
2. Enable the module:
- via admin UI, or
- via Drush:
  ```
  drush en server_configurator
  ```

3. Clear caches: drush cr
---

## Configuration

Currently, the module does not provide an admin configuration UI.

All behavior is defined in code and attached automatically to:
- `entity.node.canonical`
- nodes of type `server`

---

## Usage

- Open a node of type `server`
- The configurator JS library is automatically attached
- Frontend behavior is initialized via `Drupal.behaviors`

---

## Dependencies

- Drupal Core 10.x
- Core libraries:
- `core/drupal`
- `core/once`

No contributed modules are required at this stage.

---

## Development Notes

- The module is intentionally isolated from themes
- All configurator-related JS should live in:
  /modules/custom/server_configurator/js/
- Avoid adding business logic directly to templates
- Prefer structured metadata via `data-*` attributes or `drupalSettings`

---

## Roadmap

Planned future improvements:

- backend compatibility model
- server ↔ platform ↔ CPU relationships
- exposed filters and dynamic validation
- integration with Views and Webform
- optional REST or AJAX endpoints

---

## Status

🚧 Active development / prototype stage

Not intended for production use yet.

---

## Maintainer

Internal development module.

## Frontend
Events → меняют state → вызывают engine.recalculate() → engine обновляет UI

