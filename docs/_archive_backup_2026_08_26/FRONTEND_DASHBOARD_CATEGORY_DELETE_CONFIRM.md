# شاشة الفئات — الحذف مع تنبيه بالعناصر المرتبطة

نفس نمط خصائص الصنف (`category-attributes`).

## التدفق في الداشبورد

1. المستخدم يضغط "حذف".
2. نادِ `GET /api/admin/categories/{id}/delete-impact` واعرض `warnings` في نافذة التأكيد.
3. تبويبان:
   - **التنبيه**: رسائل `warnings`
   - **العناصر المرتبطة**: `GET /api/admin/categories/{id}/linked-items?page=1&per_page=10`
4. عند التأكيد: `DELETE /api/admin/categories/{id}?confirm=true`

بدون `confirm` ومع وجود ارتباطات → **409** مع `requires_confirmation: true` ونفس بيانات التأثير.  
**لا تعرض 409 كخطأ أحمر في التوست** — اعتبره طلب تأكيد.

## Endpoints

| الغرض | Method | Endpoint |
|---|---|---|
| ملخّص الأثر | GET | `/api/admin/categories/{id}/delete-impact` |
| العناصر المرتبطة | GET | `/api/admin/categories/{id}/linked-items` |
| تنفيذ الحذف | DELETE | `/api/admin/categories/{id}?confirm=true` |

## ماذا يحدث عند التأكيد

| المفتاح | المعنى | عند التأكيد |
|---|---|---|
| `child_categories` | فئات فرعية | **تُحذف** |
| `products` | منتجات في الشجرة | **حذف ناعم** + فك الارتباط؛ الطلبات تبقى |
| `baskets` | سلال | **تُحذف** |
| `pages` | صفحة الفئة وأقسامها | **تُحذف** |
| `recipe_links` | روابط وصفات | **تُفك فقط** |

## مثال

```js
async function deleteCategory(id) {
  const { data: impact } = await api.get(`/admin/categories/${id}/delete-impact`);

  if (impact.data.requires_confirmation) {
    const confirmed = await openDeleteDialog({
      title: 'تأكيد الحذف',
      warnings: impact.data.warnings.map((w) => w.message),
      loadLinkedItems: (page) =>
        api.get(`/admin/categories/${id}/linked-items`, { params: { page, per_page: 10 } }),
    });
    if (!confirmed) return;
  }

  await api.delete(`/admin/categories/${id}`, { params: { confirm: true } });
}
```
