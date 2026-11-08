<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Support\Documentation;

use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\Type;
use Illuminate\Support\Str;

trait DefinitionLink
{
    abstract public function getClassName(ObjectType $type): string;

    private function getGoToDefinitionLink(Type $type): string
    {
        if (!$type instanceof ObjectType) {
            return '';
        }

        $className = $this->getClassName($type);

        if (!class_exists($className)) {
            return '';
        }

        $classReflection = new \ReflectionClass($className);

        $url = GoToDefinitionOperationExtension::REPO_URL . Str::replace(base_path(), '', $classReflection->getFileName());

        return "[Open on GitHub]({$url})";
    }
}
