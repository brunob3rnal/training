# prueba-spec

## Mission

Validar el flujo de foundry-mcp creando una spec mínima en el repo training.

## Objectives

- Crear una spec con una fase y dos tareas
- Confirmar que las herramientas spec y task leen y actualizan su estado

## Success Criteria

- [ ] La spec aparece en `spec list`
- [ ] Las tareas se pueden iniciar y completar con `task`

## Assumptions

- El servidor foundry-mcp carga con el `.mcp.json` corregido

## Constraints

- No modificar código de la aplicación Laravel

## Risks

| Risk | Likelihood | Impact | Mitigation |
|------|------------|--------|------------|
| La revisión con IA no tiene proveedor configurado | high | low | Usar un archivo de revisión manual |

## Open Questions

- ¿Qué proveedor de IA se usará para las revisiones?

## Dependencies

- foundry-mcp con `mcp<2` y `pypdf`

## Phases

### Phase 1: Verificación del flujo

**Goal:** Comprobar que el ciclo de spec y tareas funciona.

**Description:** Fase mínima para probar la creación y el seguimiento de tareas.

#### Tasks

- **Revisar README** `investigation` `low`
  - Description: Leer el README del proyecto para entender su propósito
  - File: README.md
  - Acceptance criteria:
    - Se resume el propósito del proyecto en una línea
  - Depends on: none

- **Listar rutas de la app** `investigation` `low`
  - Description: Ejecutar `php artisan route:list` y anotar las rutas principales
  - File: routes/web.php
  - Acceptance criteria:
    - Se identifican las rutas de login y registro
  - Depends on: Revisar README

#### Verification

- **Run tests:** none
- **Fidelity review:** Compare implementation to spec
- **Manual checks:** none
