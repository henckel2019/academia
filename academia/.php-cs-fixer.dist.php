<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__ . '/app', __DIR__ . '/bin', __DIR__ . '/database/migrations', __DIR__ . '/tests'])
    ->append([__DIR__ . '/public/index.php', __DIR__ . '/public/router.php']);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(false)
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => ['default' => 'single_space'],
        'concat_space' => ['spacing' => 'one'],
        'cast_spaces' => ['space' => 'single'],
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'whitespace_after_comma_in_array' => true,
        'blank_line_between_import_groups' => true,
        'class_attributes_separation' => ['elements' => ['method' => 'one']],
        'no_unused_imports' => true,
    ])
    ->setCacheFile(__DIR__ . '/.local/code-style/php-cs-fixer.cache')
    ->setFinder($finder);
