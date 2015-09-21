<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in(__DIR__)
;

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers(array(
        'align_double_arrow',
        'align_equals',
        'concat_with_spaces',
        'ordered_use',
        'extra_empty_lines',
        'phpdoc_params',
        'remove_lines_between_uses',
        'return',
        'unused_use',
        'whitespacy_lines',
        'short_array_syntax'
    ))
    ->finder($finder);
