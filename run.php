#!/opt/homebrew/bin/php
<?php

require_once "SchemaMaker.php";
use Learnosity\Schemas\SchemaMaker;

$shortOptions = "t:";
$longOptions = ["types"];
$options = getopt($shortOptions, $longOptions);
$shortOptionsArray = explode(":", $shortOptions);
foreach ($longOptions as $key => $longOption) {
    if (!isset($options[$longOption]) && !isset($options[$shortOptionsArray[$key]])) {
        die(<<<EOS
        \033[1m
        ---------------------------------------------------------
        Usage: ./run.php -t=comma,separated,question,types
        ---------------------------------------------------------\033[0m

            Example: ./run.php -t=mcq,association

        NOTE: You can use `t=all` to get a full schema
        \n
        EOS);
    } else {
        $$longOption = $options[$longOption] ?? $options[$shortOptionsArray[$key]];
    }
}

if ($types == "all") {
    $typesToInfer = [];
} else {
    $typesToInfer = explode(",", trim($types));
}

$schemaMaker = new SchemaMaker($typesToInfer);
$newSchema = $schemaMaker->makeSchema();

$filename = "inferedSchema.json";
file_put_contents($filename, json_encode($newSchema, JSON_PRETTY_PRINT));
$msg = "Saved schema to {$filename}";
$separator = str_repeat("-", strlen($msg));
echo "\n{$separator}\n{$msg}\n{$separator}\n\n";
