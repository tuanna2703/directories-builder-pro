# Directories Builder Pro

A premium, Elementor-inspired local business directory plugin for WordPress.

## Changelog

### Template Architecture Refactoring (Phase 3)
- **Centralized Template Rendering**: Moved all HTML output previously scattered across modules and services into a centralized `Template_Module`. All templates now use `Template_Manager::render()` or the `dbp_template()` facade.
- **Three-Level Resolution**: Introduced strict fallback order for template overriding: `Child Theme > Parent Theme > Plugin Core`.
- **Data Contracts**: Every template file now defines a strict `$args` data contract using `@args` docblocks. Missing parameters in `WP_DEBUG` mode trigger clear developer notices.
- **Form Module Integration**: Refactored the Form Module's `Form_Renderer` to output dynamic schemas through the centralized template architecture (`templates/forms/form.php` and partials).
- **Admin UI Isolation**: Admin UI templates are fully migrated to `/templates/admin/` and are protected from theme-level overrides for stability.
- **Singleton & Autoloading Wiring**: Verified Manager initialization order in `Plugin::instance()` to ensure dependencies (like `Template_Manager`, `Ajax_Manager`, and `Form_Manager`) are loaded prior to `Module_Manager` instantiation.
