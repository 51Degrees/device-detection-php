<?php
/* *********************************************************************
 * This Original Work is copyright of 51 Degrees Mobile Experts Limited.
 * Copyright 2025 51 Degrees Mobile Experts Limited, Davidson House,
 * Forbury Square, Reading, Berkshire, United Kingdom RG1 3EU.
 *
 * This Original Work is licensed under the European Union Public Licence
 * (EUPL) v.1.2 and is subject to its terms as set out below.
 *
 * If a copy of the EUPL was not distributed with this file, You can obtain
 * one at https://opensource.org/licenses/EUPL-1.2.
 *
 * The 'Compatible Licences' set out in the Appendix to the EUPL (as may be
 * amended by the European Commission) shall be deemed incompatible for
 * the purposes of the Work and the provisions of the compatibility
 * clause in Article 5 of the EUPL shall not apply.
 *
 * If using the Work as, or as part of, a network application, by
 * including the attribution notice(s) required under Article 5 of the EUPL
 * in the end user terms of the application under an appropriate heading,
 * such notice(s) shall fulfill the requirements of that article.
 * ********************************************************************* */



return (new PhpCsFixer\Config())
    ->setRules([
        '@PER' => true,
        '@PSR12' => true,
        '@Symfony' => true,
        '@PhpCsFixer' => true,
        'no_useless_return' => false,
        'php_unit_test_class_requires_covers' => false,
        'php_unit_internal_class' => false,
        'phpdoc_align' => false,
        'phpdoc_no_empty_return' => false,
        'phpdoc_separation' => false,
        'yoda_style' => false,
        'trailing_comma_in_multiline' => false,
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'no_multi_line'
        ],
        'concat_space' => [
            'spacing' => 'one'
        ],
        'not_operator_with_space' => false,
        'operator_linebreak' => [
            'position' => 'end',
            'only_booleans' => true
        ],
        'blank_line_before_statement' => [
            'statements' => [
                'return',
                'throw',
                'try',
                'declare'
            ]
        ]
    ])
    ->setFinder(PhpCsFixer\Finder::create()
        ->in([
            __DIR__ . '/src',
            __DIR__ . '/tests',
            __DIR__ . '/tests/classes',
            __DIR__ . '/examples/cloud/classes',
        ])
    );
