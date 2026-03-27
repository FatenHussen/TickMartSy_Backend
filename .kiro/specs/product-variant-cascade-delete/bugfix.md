# Bugfix Requirements Document

## Introduction

The system currently has a critical bug where soft-deleted ProductVariants cause 500 errors when accessed through their related ShopProductVariants. This occurs because the product update logic uses a delete-create pattern that soft-deletes all old variants without cascading the deletion to related ShopProductVariants. This bugfix implements two complementary solutions: (1) cascade soft delete from ProductVariant to ShopProductVariant, and (2) replace the delete-create pattern with an update-or-create pattern that preserves existing relationships.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN a Product is updated with new variant data THEN the system soft-deletes all existing ProductVariants without cascading to related ShopProductVariants

1.2 WHEN a ProductVariant is soft-deleted THEN related ShopProductVariants remain active in the database with references to soft-deleted variants

1.3 WHEN other features attempt to access a ShopProductVariant and navigate to its soft-deleted ProductVariant THEN the system returns a 500 error

1.4 WHEN updating product variants using the delete-create pattern THEN existing relationships (ShopProductVariants) are orphaned and cause data integrity issues

### Expected Behavior (Correct)

2.1 WHEN a ProductVariant is soft-deleted THEN the system SHALL automatically cascade soft delete to all related ShopProductVariants using model events

2.2 WHEN a Product is updated with new variant data THEN the system SHALL match existing variants with new data, update matched variants, soft-delete unmatched variants, and create only truly new variants

2.3 WHEN other features attempt to access a ShopProductVariant after its ProductVariant is soft-deleted THEN the system SHALL not return 500 errors because the ShopProductVariant is also soft-deleted

2.4 WHEN updating product variants using the update-or-create pattern THEN existing relationships SHALL be preserved for matched variants

### Unchanged Behavior (Regression Prevention)

3.1 WHEN a ProductVariant is created without being linked to any ShopProductVariants THEN the system SHALL CONTINUE TO create the variant normally

3.2 WHEN a ProductVariant is hard-deleted (force delete) THEN the system SHALL CONTINUE TO follow existing hard delete behavior

3.3 WHEN querying ProductVariants with soft delete scope THEN the system SHALL CONTINUE TO exclude soft-deleted variants from results

3.4 WHEN querying ShopProductVariants with soft delete scope THEN the system SHALL CONTINUE TO exclude soft-deleted shop variants from results

3.5 WHEN creating a new Product with variants THEN the system SHALL CONTINUE TO create all variants normally

3.6 WHEN updating a Product without changing variant data THEN the system SHALL CONTINUE TO leave variants unchanged
