<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use App\Models\Base\FertilizerPrice as BaseFertilizerPrice;

/**
 * Read-only: `vw_fertilizer_prices` is a database view computed from the price
 * catalogue (migration 2026_11_10_000002), so rows cannot be inserted or edited.
 * Change a catalogue price (price_catalogue) and the options follow.
 */
class FertilizerPrice extends BaseFertilizerPrice {}
