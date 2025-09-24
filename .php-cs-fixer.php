<?php

$year = date('Y');
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('public')
    ->exclude('vendor');
;
$config = new PhpCsFixer\Config();
return $config
  ->setRules([
        '@Symfony' => true,
        '@PSR12' => true,
        'yoda_style' => false,
        'indentation_type' => true,
        'array_indentation' => true,
        'array_syntax' => ['syntax' => 'short'],
        'combine_consecutive_unsets' => true,
        'single_quote' => true,
        'no_useless_else' => true,
        'phpdoc_order' => true,
        'concat_space' => ["spacing" => "one"],
        'class_attributes_separation' => ['elements' => ['property' => 'one', 'method' => 'one']],
        'header_comment' => [
          'header' => "This file is part of the SvcTotp bundle.\n\n(c) " . $year . " Sven Vetter <dev@sv-systems.com>.\n\nFor the full copyright and license information, please view the LICENSE\nfile that was distributed with this source code.",
          'separate' => 'both'
      ]
    ])
//    ->setIndent("  ")
    ->setFinder($finder)
;
