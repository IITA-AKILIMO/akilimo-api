<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Models;

use App\Models\Base\VwFertilizer as BaseVwFertilizer;

/**
 * Read-only: `vw_fertilizers` is a database view over fertilizer_types (the
 * fertiliser: type, label, name, bag weight) and country_fertilizers (where it is
 * sold and how it is used), with fertilizer_key computed from the type and the
 * country. Write through FertilizerRepo, which updates those two tables.
 */
class VwFertilizer extends BaseVwFertilizer {}
