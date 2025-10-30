<?php

namespace App\Http\Controllers\Api\Landlord;

use App\Enum\FeatureGroupEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Landlord\FeatureResource;
use App\Services\Landlord\FeatureService;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
    public function __construct(private readonly FeatureService $featureService) {}

    public function index(Request $request)
    {
        $filters = $request->all();
        $filters['is_active'] = true;
        $filters['group'] = $request->group ?? FeatureGroupEnum::LIMIT->value;

        $features = $this->featureService->getFeatures(filters: $filters);

        return FeatureResource::collection($features);
    }
}
