# TODO - Spiritual Monitoring UI

- [ ] Step 1: Inspect existing devotional/prayer/service models + migration fields/columns.
- [ ] Step 2: Update `SpiritualPolicy.php` to match required permission matrix.
- [ ] Step 3: Refactor/extend `SpiritualController.php`:
  - [ ] employee scoping helper for role-based visibility
  - [ ] initialize records for any selected date (devotional/prayer/sunday)
  - [ ] get table endpoints returning scoped employees rows (never empty)
  - [ ] update endpoints enforce permissions + update timestamps/monitored_by
  - [ ] bulk update prayer/sunday
  - [ ] reminder endpoints (at least return success / placeholder dispatch)
- [ ] Step 4: Update `routes/api.php` with missing bulk/reminder endpoints.
- [ ] Step 5: Update `resources/js/Pages/Spiritual/Dashboard.vue`:
  - [ ] accurate summary cards
  - [ ] devotional table: status dropdown + notes; auto-save + toast
  - [ ] prayer table: attended checkbox + reason; auto-save + blur handling
  - [ ] sunday table: same as prayer
  - [ ] filters (department/status) and date pickers
  - [ ] bulk present/absent + reminder buttons
  - [ ] visual indicators (badges, check/x)
- [ ] Step 6: Update `resources/js/components/AppSidebar.vue` to add submenu links under Spiritual Formation.
- [ ] Step 7: Add seeder for initialization (current date) + wire into `DatabaseSeeder.php`.
- [ ] Step 8: Run migration/seeder and verify via browser.

