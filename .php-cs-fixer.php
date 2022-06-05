<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
;
$config = new PhpCsFixer\Config();
return $config
  ->setRules([
        '@Symfony' => true,
        'yoda_style' => false,
        'indentation_type' => true,
        'array_indentation' => true,
        'concat_space' => ["spacing" => "one"],
        'class_attributes_separation' => ['elements' => ['property' => 'one', 'method' => 'one']],
    ])
    ->setIndent("  ")
    ->setFinder($finder)
;