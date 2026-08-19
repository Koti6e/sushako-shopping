# Seller OS V2 Reset Notes

Before running any destructive Seller OS reset in production, create an audited database backup and verify that it can be restored.

Recommended backup command:

```bash
mysqldump --single-transaction --routines --triggers --events "$DB_DATABASE" > "database/backups/seller-os-v2-pre-reset-$(date +%Y%m%d_%H%M%S).sql"
```

Reset scope for obsolete seller data:

- seller user accounts with role `vendor`
- vendor shop profile rows and seller onboarding state
- seller delivery, settlement, bank, plan, notification, audit, category request, and storefront category records
- seller-linked development/test products where no real order or payment history depends on them

Protected scope:

- superadmin users
- customer users and customer profile/order history
- orders, order items, invoices, payment audit data, ledger entries, settlements, and labels that represent real accounting history
- global marketplace categories, operational settings, and customer storefront configuration

When seller-linked historical orders or payment records exist, preserve accounting integrity with snapshots, soft deletion, or detachment instead of deleting records directly. This implementation did not execute destructive data changes; it only adds Seller OS V2-compatible fields and flow updates.
