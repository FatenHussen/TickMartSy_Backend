# Frontend React Guide - Vendor Accounting (Admin)
# دليل ربط محاسبة البائعين للفرونت (React)

**Target:** Admin Frontend (React)  
**Base URL:** `/api/admin`  
**Auth:** Bearer Token (admin)

---

## 1) الفكرة بسرعة

في الواجهة الأمامية عندك 4 شاشات رئيسية:

1. **Accounting Summary**  
   تعرض أرقام عامة: إجمالي المبيعات، العمولة، الصافي، المدفوع، المتبقي.

2. **Vendors Accounting List**  
   جدول البائعين مع الفلاتر والـ pagination.

3. **Vendor Statement (Details)**  
   كشف حساب بائع واحد + طلبات السحب الخاصة فيه.

4. **Withdraw Requests Management**  
   شاشة إدارة طلبات سحب البائعين (pending / paid / rejected).

---

## 2) الـ Endpoints المستخدمة

### Vendor Accounting

- `GET /api/admin/vendor-accounting/summary`
- `GET /api/admin/vendor-accounting/vendors`
- `GET /api/admin/vendor-accounting/vendors/{vendorId}`

### Vendor Withdraw Requests

- `GET /api/admin/vendor-withdraw-requests`
- `GET /api/admin/vendor-withdraw-requests/{id}`
- `PUT /api/admin/vendor-withdraw-requests/{id}`

---

## 3) شكل الاستجابة

معظم APIs ترجع بهذا الشكل:

```json
{
  "status": true,
  "message": "Success",
  "data": {}
}
```

---

## 4) بنية ملفات React مقترحة

```txt
src/
  modules/
    vendorAccounting/
      api/
        vendorAccounting.api.ts
      hooks/
        useVendorAccounting.ts
      pages/
        VendorAccountingSummaryPage.tsx
        VendorAccountingVendorsPage.tsx
        VendorAccountingVendorDetailsPage.tsx
        VendorWithdrawRequestsPage.tsx
      components/
        SummaryCards.tsx
        VendorsAccountingTable.tsx
        VendorStatementCard.tsx
        WithdrawRequestsTable.tsx
        UpdateWithdrawStatusModal.tsx
      types/
        vendorAccounting.types.ts
```

---

## 5) Types (TypeScript)

```ts
// src/modules/vendorAccounting/types/vendorAccounting.types.ts
export type Pagination = {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
};

export type VendorWallet = {
  orders_count: number;
  commission_rate: number;
  gross_sales: number;
  platform_commission: number;
  discounts_share: number;
  refunds: number;
  net_due: number;
  paid: number;
  pending_withdrawals: number;
  remaining_after_paid: number;
  available_for_withdraw: number;
};

export type VendorItem = {
  id: number;
  name: Record<string, string>;
  name_translations: Record<string, string>;
  owner_name: string;
  commission_rate: number;
  is_active: boolean;
  created_at: string;
};

export type VendorAccountingRow = {
  vendor: VendorItem;
  wallet: VendorWallet;
};

export type VendorAccountingSummary = {
  vendors_count: number;
  active_vendors_count: number;
  gross_sales: number;
  platform_commission: number;
  discounts_share: number;
  refunds: number;
  net_due: number;
  paid: number;
  pending_withdrawals: number;
  remaining_after_paid: number;
  available_for_withdraw: number;
};

export type WithdrawRequest = {
  id: number;
  amount: number;
  status: "pending" | "paid" | "rejected";
  payment_method: "bank_transfer" | "cash" | "wallet" | "other" | null;
  transfer_reference: string | null;
  note: string | null;
  rejection_reason: string | null;
  requested_at: string | null;
  processed_at: string | null;
  created_at: string | null;
};
```

---

## 6) API Service (Axios)

```ts
// src/modules/vendorAccounting/api/vendorAccounting.api.ts
import { apiClient } from "@/shared/apiClient"; // axios instance

export const vendorAccountingApi = {
  getSummary: (params?: { from_date?: string; to_date?: string }) =>
    apiClient.get("/api/admin/vendor-accounting/summary", { params }),

  getVendors: (params?: {
    search?: string;
    is_active?: boolean;
    from_date?: string;
    to_date?: string;
    page?: number;
    per_page?: number;
  }) => apiClient.get("/api/admin/vendor-accounting/vendors", { params }),

  getVendorStatement: (
    vendorId: number,
    params?: {
      from_date?: string;
      to_date?: string;
      withdraw_status?: "pending" | "paid" | "rejected";
      withdraw_per_page?: number;
      page?: number;
    }
  ) => apiClient.get(`/api/admin/vendor-accounting/vendors/${vendorId}`, { params }),

  getWithdrawRequests: (params?: {
    status?: "pending" | "paid" | "rejected";
    vendor_id?: number;
    payment_method?: "bank_transfer" | "cash" | "wallet" | "other";
    from?: string;
    to?: string;
    min_amount?: number;
    max_amount?: number;
    search?: string;
    sort_field?: string;
    sort_order?: "asc" | "desc";
    page?: number;
    per_page?: number;
  }) => apiClient.get("/api/admin/vendor-withdraw-requests", { params }),

  getWithdrawRequest: (id: number) =>
    apiClient.get(`/api/admin/vendor-withdraw-requests/${id}`),

  updateWithdrawRequest: (
    id: number,
    payload:
      | {
          status: "paid";
          payment_method: "bank_transfer" | "cash" | "wallet" | "other";
          transfer_reference?: string;
          note?: string;
        }
      | {
          status: "rejected";
          rejection_reason: string;
          note?: string;
        }
  ) => apiClient.put(`/api/admin/vendor-withdraw-requests/${id}`, payload),
};
```

---

## 7) React Query Hooks

```ts
// src/modules/vendorAccounting/hooks/useVendorAccounting.ts
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { vendorAccountingApi } from "../api/vendorAccounting.api";

export const useVendorAccountingSummary = (params?: { from_date?: string; to_date?: string }) =>
  useQuery({
    queryKey: ["vendor-accounting-summary", params],
    queryFn: async () => (await vendorAccountingApi.getSummary(params)).data.data,
  });

export const useVendorsAccounting = (params: Record<string, unknown>) =>
  useQuery({
    queryKey: ["vendor-accounting-vendors", params],
    queryFn: async () => (await vendorAccountingApi.getVendors(params)).data.data,
  });

export const useVendorStatement = (vendorId: number, params?: Record<string, unknown>) =>
  useQuery({
    queryKey: ["vendor-accounting-statement", vendorId, params],
    queryFn: async () => (await vendorAccountingApi.getVendorStatement(vendorId, params)).data.data,
    enabled: !!vendorId,
  });

export const useWithdrawRequests = (params: Record<string, unknown>) =>
  useQuery({
    queryKey: ["vendor-withdraw-requests", params],
    queryFn: async () => (await vendorAccountingApi.getWithdrawRequests(params)).data.data,
  });

export const useUpdateWithdrawRequest = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, payload }: { id: number; payload: any }) =>
      vendorAccountingApi.updateWithdrawRequest(id, payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["vendor-withdraw-requests"] });
      queryClient.invalidateQueries({ queryKey: ["vendor-accounting-summary"] });
      queryClient.invalidateQueries({ queryKey: ["vendor-accounting-vendors"] });
      queryClient.invalidateQueries({ queryKey: ["vendor-accounting-statement"] });
    },
  });
};
```

---

## 8) UI Flow مقترح

### الصفحة 1: Summary

- Date range picker (`from_date`, `to_date`)
- Cards:
  - `gross_sales`
  - `platform_commission`
  - `net_due`
  - `paid`
  - `available_for_withdraw`

### الصفحة 2: Vendors Table

- فلاتر:
  - search
  - is_active
  - from_date/to_date
- أعمدة:
  - vendor name
  - owner name
  - gross sales
  - commission
  - net due
  - paid
  - available for withdraw
- زر `View Statement`

### الصفحة 3: Vendor Statement

- بطاقة ملخص البائع + wallet
- جدول طلبات السحب للبائع
- filter by `withdraw_status`

### الصفحة 4: Withdraw Requests

- جدول شامل لكل طلبات السحب
- زر `Process`
- Modal:
  - إذا `paid`: `payment_method` + `transfer_reference` + `note`
  - إذا `rejected`: `rejection_reason` + `note`

---

## 9) مثال Modal تحديث حالة السحب

```tsx
const onSubmit = (values: any) => {
  if (values.status === "paid") {
    mutate({
      id: selectedId,
      payload: {
        status: "paid",
        payment_method: values.payment_method,
        transfer_reference: values.transfer_reference,
        note: values.note,
      },
    });
    return;
  }

  mutate({
    id: selectedId,
    payload: {
      status: "rejected",
      rejection_reason: values.rejection_reason,
      note: values.note,
    },
  });
};
```

---

## 10) حالات يجب مراعاتها بالفرونت

1. لو API رجعت validation error أو business error (`amount_exceeds_balance`) اعرض الرسالة كما هي.
2. لا تسمح في UI بتحديث طلب غير `pending`.
3. عند تحويل الحالة إلى `paid` أو `rejected` اقفل الـ modal وحدث البيانات (`invalidate`).
4. القيم المالية اعرضها بـ format ثابت (مثل `toLocaleString`) حسب عملتكم.
5. تاريخ `requested_at` ممكن يكون `null`، fallback إلى `created_at`.

---

## 11) Checklist سريعة للفريق

- [ ] صفحة Summary جاهزة وتقرأ endpoint الصحيح.
- [ ] صفحة Vendors تدعم pagination + filters.
- [ ] صفحة Vendor Statement تعرض wallet + withdraw requests.
- [ ] شاشة Withdraw Requests تدعم update status.
- [ ] تم عمل refetch بعد أي تحديث حالة.
- [ ] تم التعامل مع رسائل الأخطاء القادمة من الـ backend.

