<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.co.ke
 *
 * For licence information, see the LICENCE file.
 */

declare(strict_types=1);

namespace App\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Enforces named arguments on calls that have two or more positional arguments.
 *
 * Named arguments make call sites self-documenting and prevent silent breakage
 * when a function signature changes parameter order.
 *
 * Configurable via phpstan.neon:
 *
 *   parameters:
 *       enforceNamedArguments:
 *           minimumArgCount: 2          # only enforce when call has >= N args
 *           allowedFunctions:           # built-ins / helpers exempt from the rule
 *               - compact
 *               - array_map
 *
 * @implements Rule<Node\Expr>
 */
class EnforceNamedArgumentsRule implements Rule
{
    /**
     * Functions that are universally exempt (PHP built-ins or framework helpers
     * where named args are impractical or not supported).
     */
    private const DEFAULT_ALLOWED = [
        // Array helpers — variadic or semantically ordered
        'compact',
        'extract',
        'implode',
        'explode',
        'array_map',
        'array_filter',
        'array_walk',
        'array_chunk',
        'array_slice',
        'array_splice',
        'array_search',
        'array_keys',
        'array_values',
        'array_merge',
        'array_push',
        'array_pop',
        'array_shift',
        'array_unshift',
        'array_combine',
        'array_diff',
        'array_intersect',
        'array_unique',
        'array_flip',
        'array_column',
        'array_fill',
        'array_pad',
        'array_sum',
        'array_count_values',
        'in_array',
        'sort',
        'usort',
        'uasort',
        'uksort',
        'arsort',
        'asort',
        'krsort',
        'ksort',
        'rsort',

        // String helpers
        'str_replace',
        'str_pad',
        'str_repeat',
        'str_contains',
        'str_starts_with',
        'str_ends_with',
        'substr',
        'substr_replace',
        'substr_count',
        'strpos',
        'strrpos',
        'stripos',
        'strripos',
        'str_split',
        'sprintf',
        'vsprintf',
        'printf',
        'number_format',
        'preg_match',
        'preg_match_all',
        'preg_replace',
        'preg_split',
        'preg_replace_callback',
        'trim',
        'ltrim',
        'rtrim',
        'wordwrap',
        'chunk_split',
        'nl2br',
        'htmlspecialchars',
        'htmlspecialchars_decode',
        'htmlentities',

        // Math
        'round',
        'max',
        'min',
        'abs',
        'pow',
        'fmod',
        'intdiv',

        // Date/time
        'mktime',
        'strtotime',
        'date',
        'checkdate',

        // I/O
        'fopen',
        'fwrite',
        'fread',
        'fgets',
        'file_put_contents',
        'file_get_contents',
        'json_encode',
        'json_decode',

        // Misc
        'isset',
        'unset',
        'list',
        'call_user_func',
        'call_user_func_array',
        'header',
        'setcookie',
        'ob_start',
        'ob_get_clean',
        'class_exists',
        'method_exists',
        'property_exists',
        'function_exists',
        'is_a',
        'is_subclass_of',
        'get_class',
        'get_parent_class',
        'instanceof',

        // Laravel/testing helpers
        'abort',
        'abort_if',
        'abort_unless',
        'redirect',
        'response',
        'view',
        'event',
        'dispatch',
        'resolve',
        'app',
        'config',
        'env',
        'trans',
        '__',
        'trans_choice',
        'now',
        'today',
        'dd',
        'dump',
        'tap',
        'with',
        'throw_if',
        'throw_unless',
        'rescue',
        'retry',
        'data_get',
        'data_set',
        'data_fill',
        'filled',
        'blank',
        'optional',
        'value',
        'once',
        'cache',
        'session',
        'cookie',
        'request',
        'route',
        'url',
        'asset',
        'action',
        'back',
        'old',
        'mix',
        'secure_url',
        'secure_asset',
    ];

    /** @param list<string> $allowedFunctions */
    public function __construct(
        private readonly int $minimumArgCount = 2,
        private readonly array $allowedFunctions = [],
    ) {}

    public function getNodeType(): string
    {
        return Node\Expr::class;
    }

    /** @param Node\Expr $node */
    public function processNode(Node $node, Scope $scope): array
    {
        if (
            !$node instanceof MethodCall
            && !$node instanceof StaticCall
            && !$node instanceof FuncCall
        ) {
            return [];
        }

        $args = $node->args;

        // Only Arg instances count — skip unpacked/named spread entries
        $positionalArgs = array_filter(
            $args,
            fn ($arg) => $arg instanceof Arg && $arg->name === null && !$arg->unpack,
        );

        if (count($positionalArgs) < $this->minimumArgCount) {
            return [];
        }

        // Resolve the callable name for the allowlist check
        $callableName = $this->resolveCallableName($node);

        if ($callableName !== null && $this->isAllowed($callableName)) {
            return [];
        }

        $positionalCount = count($positionalArgs);
        $label = $this->buildLabel($node);

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Call to %s passes %d positional argument%s. Use named arguments to make the call self-documenting.',
                    $label,
                    $positionalCount,
                    $positionalCount === 1 ? '' : 's',
                )
            )
                ->identifier('namedArguments.required')
                ->tip('Replace positional args with named args, e.g. foo(bar: $value, baz: $other).')
                ->build(),
        ];
    }

    private function resolveCallableName(Node\Expr $node): ?string
    {
        if ($node instanceof FuncCall && $node->name instanceof Name) {
            return strtolower($node->name->getLast());
        }

        if ($node instanceof MethodCall) {
            $name = $node->name;

            return $name instanceof Node\Identifier ? strtolower($name->name) : null;
        }

        if ($node instanceof StaticCall) {
            $name = $node->name;

            return $name instanceof Node\Identifier ? strtolower($name->name) : null;
        }

        return null;
    }

    private function isAllowed(string $name): bool
    {
        $allowed = array_map('strtolower', array_merge(self::DEFAULT_ALLOWED, $this->allowedFunctions));

        return in_array($name, $allowed, true);
    }

    private function buildLabel(Node\Expr $node): string
    {
        if ($node instanceof FuncCall) {
            $name = $node->name;

            return $name instanceof Name ? $name->toString() . '()' : 'dynamic function';
        }

        if ($node instanceof MethodCall) {
            $name = $node->name;

            return $name instanceof Node\Identifier ? '->' . $name->name . '()' : 'dynamic method';
        }

        if ($node instanceof StaticCall) {
            $class = $node->class;
            $name = $node->name;
            $cls = $class instanceof Name ? $class->getLast() : 'dynamic';
            $meth = $name instanceof Node\Identifier ? $name->name : 'dynamic';

            return $cls . '::' . $meth . '()';
        }

        return 'callable';
    }
}
