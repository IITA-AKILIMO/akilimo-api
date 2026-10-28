<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Collections\CropResourceCollection;
use App\Models\Crop;
use App\Traits\HasPaginationParams;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;

class CropController extends Controller
{
    use HasPaginationParams;

    /**
     * List crops
     *
     * The crops AKILIMO offers, in display order. `advice_available` says whether recommendations exist for
     * the crop; the rest are listed for prices and information. Switched-off crops are not listed.
     *
     * @unauthenticated
     */
    #[QueryParameter(name: 'per_page', description: 'Number of items per page.', type: 'int')]
    #[QueryParameter(name: 'page', description: 'Page number.', type: 'int')]
    public function index(Request $request): CropResourceCollection
    {
        return $this->listing($request, Crop::query()->active());
    }

    /**
     * List crops offered in a country
     *
     * Crops with no country list (offered everywhere) and those that name the country.
     *
     * @unauthenticated
     */
    #[QueryParameter(name: 'per_page', description: 'Number of items per page.', type: 'int')]
    #[QueryParameter(name: 'page', description: 'Page number.', type: 'int')]
    public function byCountry(Request $request, string $countryCode): CropResourceCollection
    {
        return $this->listing($request, Crop::query()->active()->forCountry($countryCode));
    }

    private function listing(Request $request, $query): CropResourceCollection
    {
        $crops = $query->orderBy('sort_order')->orderBy('name')->paginate($this->getPerPage($request));

        return CropResourceCollection::make($crops);
    }
}
