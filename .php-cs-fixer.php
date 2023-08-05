<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->path(['src', 'public', 'tests', 'config', 'bin']);

$config = new PhpCsFixer\Config();

return $config->setRules([
    '@Symfony' => true,
])->setFinder($finder);