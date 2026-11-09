<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Support\Documentation;

use Dedoc\Scramble\Extensions\OperationExtension;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\RouteInfo;
use Illuminate\Support\Str;

class GoToDefinitionOperationExtension extends OperationExtension
{
    public const REPO_URL = 'https://github.com/IITA-AKILIMO/new-akilimo-api/blob/main';

    public function handle(Operation $operation, RouteInfo $routeInfo): void
    {
        $url = self::REPO_URL . Str::replace(base_path(), '', $routeInfo->reflectionMethod()->getFileName());
        $line = $routeInfo->reflectionMethod()->getStartLine();
        $methodLineUrl = "{$url}#L{$line}";

        $operation->description(
            $operation->description . " [View source]({$methodLineUrl})"
        );
    }
}
