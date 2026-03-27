# Product Variant Cascade Delete Bugfix Design

## Overview

This bugfix addresses a critical data integrity issue where soft-deleted ProductVariants cause 500 errors when accessed through their related ShopProductVariants. The bug occurs because the ProductService uses a delete-create pattern that soft-deletes all old variants without cascading the deletion to related ShopProductVariants, leaving orphaned references in the database.

The fix implements two complementary solutions:
1. **Cascade Soft Delete**: Add model event listeners to ProductVariant that automatically cascade soft deletes to related ShopProductVariants
2. **Update-or-Create Pattern**: Replace the delete-create pattern in ProductService with an update-or-create pattern that preserves existing relationships

This dual approach ensures both immediate resolution (cascade) and prevention of future occurrences (update-or-create pattern).

## Glossary

- **Bug_Condition (C)**: The condition that triggers the bug - when ProductVariants are soft-deleted without cascading to ShopProductVariants
- **Property (P)**: The desired behavior - ShopProductVariants should be automatically soft-deleted when their parent ProductVariant is soft-deleted
- **Preservation**: Existing product creation, variant querying, and hard delete behaviors that must remain unchanged
- **ProductVariant**: The model in `app/Models/ProductVariant.php` that represents product variations with different attributes
- **ShopProductVariant**: The model in `app/Models/ShopProductVariant.php` that links ProductVariants to specific shops with pricing and inventory
- **ProductService::handleRelations()**: The method in `app/Services/Admin/ProductService.php` that manages product variant creation/updates
- **Delete-Create Pattern**: The current approach that deletes all existing variants and creates new ones, breaking relationships
- **Update-or-Create Pattern**: The improved approach that matches existing variants, updates them, and only creates truly new variants

## Bug Details

### Fault Condition

The bug manifests when a Product is updated with new variant data. The `ProductService::handleRelations()` method calls `$object->variants()->delete()` which soft-deletes all existing ProductVariants without triggering any cascade logic to related ShopProductVariants. This leaves ShopProductVariants in the database with `product_variant_id` references pointing to soft-deleted ProductVariants.

**Formal Specification:**
```
FUNCTION isBugCondition(input)
  INPUT: input of type { operation: string, productId: int, variantsData: array }
  OUTPUT: boolean
  
  RETURN input.operation == 'update'
         AND input.variantsData IS NOT EMPTY
         AND existingVariantsExist(input.productId)
         AND existingVariantsHaveShopVariants(input.productId)
         AND NOT cascadeSoftDeleteImplemented()
END FUNCTION
```

### Examples

- **Example 1**: Admin updates Product ID 5 with new variant data. The system soft-deletes 3 existing ProductVariants (IDs 10, 11, 12) that have 8 related ShopProductVariants. The ShopProductVariants remain active with references to soft-delete
d variants. When a user tries to view products from a shop, the system attempts to load ShopProductVariant->productVariant relationship and encounters soft-deleted records, causing a 500 error.

- **Example 2**: Admin updates Product ID 8 changing variant attributes. The system soft-deletes 2 existing ProductVariants that are referenced by 15 ShopProductVariants across 5 different shops. Basket items, order items, and rating queries that join through ShopProductVariant to ProductVariant fail with 500 errors.reservation Requirements

**Unchanged Behaviors:**
- Creating a new Product with variants must continue to create all variants normally
- Querying ProductVariants with soft delete scope must continue to exclude soft-deleted variants
- Querying ShopProductVariants with soft delete scope must continue to exclude soft-deleted shop variants
- Hard-deleting (force delete) a ProductVariant must continue to follow existing hard delete behavior
- Updating a Product without changing variant data must continue to leave variants unchanged
- Creating ProductVariants without ShopProductVariants must continue to work normally

**Scope:**
All operations that do NOT involve soft-deleting ProductVariants that have related ShopProductVariants should be completely unaffected by this fix. This includes:
- New product creation with variants
- Variant queries and filtering
- Hard delete operations
- Products without variants
- Variants without shop relationships

## Hypothesized Root Cause

Based on the bug description and code analysis, the root causes are:

1. **Missing Cascade Logic**: The ProductVariant model does not implement any model event listeners (deleting, deleted) to cascade soft deletes to related ShopProductVariants
   - Laravel's SoftDeletes trait does not automatically cascade to relationships
   - The `shopVariants()` relationship exists but has no cascade behavior defined

2. **Inefficient Delete-Create Pattern**: The ProductService uses `$object->variants()->delete()` followed by creating all variants fresh
   - This pattern assumes variants are disposable and have no important relationships
   - It does not attempt to match existing variants with incoming data
   - It unnecessarily breaks relationships even when variants haven't changed

3. **No Relationship Awareness**: The handleRelations method does not consider that ProductVariants may have dependent ShopProductVariants
   - The code treats variants as simple child records rather than entities with their own relationships
   - No validation or cascade logic exists before deletion

4. **Lack of Matching Logic**: The current implementation has no algorithm to match existing variants with incoming variant data
   - Variants could be matched by attributes_values_ids to determine if they're the same variant
   - Without matching, the system cannot distinguish between "update existing" vs "create new"

## Correctness Properties

Property 1: Fault Condition - Cascade Soft Delete to ShopProductVariants

_For any_ ProductVariant soft delete operation where the variant has related ShopProductVariants, the fixed code SHALL automatically cascade the soft delete to all related ShopProductVariants, ensuring no orphaned references remain in the database.

**Validates: Requirements 2.1, 2.3**

Property 2: Fault Condition - Update-or-Create Pattern Preserves Relationships

_For any_ Product update operation with variant data where existing variants can be matched with incoming data, the fixed code SHALL update the matched variants in place rather than deleting and recreating them, preserving all existing ShopProductVariant relationships.

**Validates: Requirements 2.2, 2.4**

Property 3: Preservation - Non-Variant Operations Unchanged

_For any_ operation that does NOT involve soft-deleting ProductVariants with related ShopProductVariants (new product creation, variant queries, hard deletes, products without variants), the fixed code SHALL produce exactly the same behavior as the original code.

**Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6**

## Fix Implementation

### Changes Required

Assuming our root cause analysis is correct:

**File**: `app/Models/ProductVariant.php`

**Function**: Add model event listeners

**Specific Changes**:
1. **Add Deleting Event Listener**: Implement a `deleting` event in the `boot()` method
   - Check if the deletion is a soft delete (not force delete)
   - Cascade soft delete to all related ShopProductVariants via `$this->shopVariants()->delete()`
   - This ensures automatic cascade whenever a ProductVariant is soft-deleted

**File**: `app/Services/Admin/ProductService.php`

**Function**: `handleRelations()`

**Specific Changes**:
2. **Replace Delete-Create with Update-or-Create**: Replace the `$object->variants()->delete()` pattern
   - Build a map of existing variants keyed by attributes_values_ids (sorted for consistent matching)
   - Iterate through incoming variant data
   - For each incoming variant, check if a matching existing variant exists (same attributes_values_ids)
   - If match found: update the existing variant with new data (is_trend, etc.)
   - If no match: create a new variant
   - After processing all incoming variants, soft-delete any existing variants that were not matched

3. **Preserve Variant IDs**: When updating matched variants, preserve their IDs
   - This maintains all foreign key relationships in ShopProductVariants
   - Update the $variantIndexMap to use existing variant IDs for matched variants

4. **Handle Shop Variants Correctly**: Ensure shop_variants logic works with both new and updated variants
   - The existing $variantIndexMap approach should work seamlessly
   - Shop variants will reference preserved variant IDs for matched variants

5. **Handle Variant Images**: Ensure image handling works for both new and updated variants
   - The existing image handling logic should work with updated variants
   - Preserve existing images when updating variants

## Testing Strategy

### Validation Approach

The testing strategy follows a two-phase approach: first, surface counterexamples that demonstrate the bug on unfixed code, then verify the fix works correctly and preserves existing behavior.

### Exploratory Fault Condition Checking

**Goal**: Surface counterexamples that demonstrate the bug BEFORE implementing the fix. Confirm or refute the root cause analysis. If we refute, we will need to re-hypothesize.

**Test Plan**: Write tests that create a Product with variants and ShopProductVariants, then update the product with new variant data. Run these tests on the UNFIXED code to observe that ShopProductVariants remain active while their ProductVariants are soft-deleted, causing 500 errors when accessed.

**Test Cases**:
1. **Basic Cascade Failure Test**: Create Product with 2 variants, each with 2 ShopProductVariants. Update product with new variant data. Assert that old ProductVariants are soft-deleted but ShopProductVariants are NOT soft-deleted (will fail on unfixed code - demonstrates the bug).

2. **500 Error Reproduction Test**: Create Product with variants and ShopProductVariants. Update product. Attempt to query ShopProductVariants with productVariant relationship loaded. Assert that query succeeds without 500 error (will fail on unfixed code).

3. **Orphaned Reference Test**: Create Product with variants and ShopProductVariants. Update product. Query ShopProductVariants and check if their product_variant_id references point to soft-deleted records (will fail on unfixed code - demonstrates orphaned references).

4. **Relationship Preservation Test**: Create Product with 3 variants. Update product with same variant data (no actual changes). Assert that variant IDs remain the same and ShopProductVariants still reference the same variant IDs (will fail on unfixed code - demonstrates unnecessary deletion).

**Expected Counterexamples**:
- ShopProductVariants remain active (deleted_at IS NULL) while their ProductVariants have deleted_at set
- Queries that eager load productVariant relationship on ShopProductVariant fail with 500 errors or return null
- Variant IDs change even when variant data hasn't changed, breaking ShopProductVariant references

### Fix Checking

**Goal**: Verify that for all inputs where the bug condition holds, the fixed function produces the expected behavior.

**Pseudocode:**
```
FOR ALL input WHERE isBugCondition(input) DO
  result := handleRelations_fixed(input)
  ASSERT cascadeSoftDeleteOccurred(result)
  ASSERT matchedVariantsWereUpdated(result)
  ASSERT onlyUnmatchedVariantsWereDeleted(result)
  ASSERT noOrphanedShopVariantsExist(result)
END FOR
```

### Preservation Checking

**Goal**: Verify that for all inputs where the bug condition does NOT hold, the fixed function produces the same result as the original function.

**Pseudocode:**
```
FOR ALL input WHERE NOT isBugCondition(input) DO
  ASSERT handleRelations_original(input) = handleRelations_fixed(input)
END FOR
```

**Testing Approach**: Property-based testing is recommended for preservation checking because:
- It generates many test cases automatically across the input domain
- It catches edge cases that manual unit tests might miss
- It provides strong guarantees that behavior is unchanged for all non-buggy inputs

**Test Plan**: Observe behavior on UNFIXED code first for new product creation, variant queries, and hard deletes, then write property-based tests capturing that behavior.

**Test Cases**:
1. **New Product Creation Preservation**: Observe that creating a new Product with variants works correctly on unfixed code, then write test to verify this continues after fix (variants are created, no errors occur).

2. **Variant Query Preservation**: Observe that querying ProductVariants with soft delete scope correctly excludes soft-deleted variants on unfixed code, then write test to verify this continues after fix.

3. **Hard Delete Preservation**: Observe that force deleting a ProductVariant works correctly on unfixed code, then write test to verify this continues after fix (no cascade to ShopProductVariants on hard delete).

4. **No Variant Update Preservation**: Observe that updating a Product without changing variant data leaves variants unchanged on unfixed code, then write test to verify this continues after fix.

### Unit Tests

- Test ProductVariant model deleting event cascades to ShopProductVariants
- Test ProductVariant model does not cascade on hard delete (force delete)
- Test ProductService matches existing variants correctly by attributes_values_ids
- Test ProductService updates matched variants instead of deleting them
- Test ProductService creates only new variants that don't match existing ones
- Test ProductService soft-deletes unmatched existing variants
- Test ProductService preserves variant IDs for matched variants
- Test edge case: Product with no existing variants (all variants are new)
- Test edge case: Product update with no variant changes (all variants match)
- Test edge case: Product update with all new variants (no matches)
- Test edge case: Product update with mix of matched, new, and deleted variants

### Property-Based Tests

- Generate random product configurations with varying numbers of variants and shop variants, update with random new variant data, verify no orphaned ShopProductVariants exist
- Generate random variant attribute combinations, verify matching logic correctly identifies same variants
- Generate random product update scenarios, verify variant IDs are preserved when variants match
- Test across many scenarios that new product creation always works correctly

### Integration Tests

- Test full product update flow: create product with variants and shop variants, update product, verify shop variants remain accessible
- Test basket item flow: create product with variants in basket, update product, verify basket items still work
- Test order item flow: create order with shop product variants, update product, verify order history still displays correctly
- Test rating flow: create ratings on shop product variants, update product, verify ratings remain accessible
