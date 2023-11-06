<?php

declare(strict_types=1);

// use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
// use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;
// use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
// use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->skip([
        '**/vendor/*',
        '**/node_modules/*',
        '**/wordpress/*',
        'themes/freesite-2020-theme/src/Api/Posts.php',
        'themes/freesite-2020-theme/lib/commerce.php',
        NewlineAfterStatementRector::class,
    ]);

    // register a single rule
    // $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);
    // $rectorConfig->rule(AddClosureReturnTypeRector::class);
    // $rectorConfig->rule(AddParamTypeDeclarationRector::class);
    // $rectorConfig->rule(AddReturnTypeDeclarationRector::class);
    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_82,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
        SetList::DEAD_CODE,
    ]);

    $rectorConfig->importNames();
    $rectorConfig->parallel();
};
