<?php

namespace App\Exceptions;

use Exception;

/**
 * تُرمى عندما يكون الحذف مسموحاً لكنه يؤثر على بيانات مرتبطة،
 * فيُطلب من الواجهة عرض التنبيه ثم إعادة الطلب مع confirm=true.
 */
class DeleteConfirmationRequiredException extends Exception
{
    public function __construct(
        protected array $impact,
        protected string $translationKey = 'custom.products.delete_impact.requires_confirmation',
        protected int $status = 409
    ) {
        parent::__construct();
    }

    public function getImpact(): array
    {
        return $this->impact;
    }

    public function render()
    {
        return response()->json([
            'status' => false,
            'message' => __($this->translationKey),
            'requires_confirmation' => true,
            'data' => $this->impact,
        ], $this->status);
    }
}
