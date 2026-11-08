<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    abstract public function index(Request $request);
}
