<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude(['vendor', 'var'])
    ->in(__DIR__);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules(
        [
            'declare_strict_types' => true,
            '@Symfony' => true,
            '@PSR2' => true,
            '@PSR1' => true,
            'array_syntax' => ['syntax' => 'short'],
            'ordered_imports' => true,
            'strict_comparison' => true,
            'strict_param' => true,
            'phpdoc_order' => true,
            'no_useless_return' => true,
            'no_useless_else' => true,
            'ereg_to_preg' => true,
            'php_unit_construct' => true,
            'combine_consecutive_unsets' => true,
            'concat_space' => ['spacing' => 'one'],
            'binary_operator_spaces' => ['default' => 'align_single_space', 'operators' => ['=' => null]],
        ]
    )
    ->setFinder($finder)
    ->setUsingCache(true)
    ->setCacheFile('var/ci/phpcsfixercache');
