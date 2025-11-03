<?php

namespace App\Models\Tenant;

use App\Enum\CustomerFeedbackStatusEnum;
use App\Enum\FeedbackSourceEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerFeedback extends BaseTenantModel
{
    protected $fillable = [
        'name',
        'email',
        'feedback_category_id',
        'rating',
        'detailed_review',
        'source',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
        'source' => FeedbackSourceEnum::class,
        'status' => CustomerFeedbackStatusEnum::class,
    ];

    public function feedbackCategory(): BelongsTo
    {
        return $this->belongsTo(FeedbackCategory::class, 'feedback_category_id');
    }
}
