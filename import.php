<?php
declare(strict_types=1);

// this is really oldschool :-D
// but for this simple example it might be sufficient
require_once __DIR__ . '/autoload.php';

define('REQUEST_TIMESTAMP', microtime(true));
define('PROJECT_ROOT', __DIR__ . DIRECTORY_SEPARATOR);

// TODO: if the app gets more complex, we could ...
//       - implement a command line argument parser like symfony/console.
//       - implement a symfony command to handle the import process.
// Init ETL components
$factory = new Factory();
$extractor = $factory->createExtractor();
$transformer = $factory->createTransformer();
$loader = $factory->createLoader();

// Define filesystem files from cli arguments.
// With symfony/concole we could validate this. For probearbeit this is enough.
$inputFile = $argv[1] ?? null;
$outputFile = $argv[2] ?? null;
$errorsFile = 'errors.json';

// ETL (extract, transform, load) processing
try {
    // TODO: a progressbar would be nice!
    $data = $extractor->extractCsvFile(PROJECT_ROOT . $inputFile);
    $transformerResponseDTO = $transformer->transformCsvData($data);
    $countImported = $loader->writeToFilesystem($transformerResponseDTO->data, PROJECT_ROOT . $outputFile);
    $countErrors = $loader->writeToFilesystem($transformerResponseDTO->errors, PROJECT_ROOT . $errorsFile);
} catch (Throwable $exception) {
    echo 'Laufzeitfehler: ' . $exception->getMessage() . PHP_EOL;
    exit(1);
}

// Following code is output summary only
$logNumberFormatter = static fn($number): string => str_pad((string) $number, 4, ' ', STR_PAD_LEFT);

$countImported = $logNumberFormatter($countImported);
$countErrors = $logNumberFormatter($countErrors);
$countSkipped = $logNumberFormatter(count($transformerResponseDTO->skipped));
$countData = $logNumberFormatter(count($data));
$runtime = microtime(true) - REQUEST_TIMESTAMP;

echo <<< OUTPUT
Processing complete...
Read:     {$countData} datasets ({$inputFile})
Imported: {$countImported} datasets ({$outputFile})
Skipped:  {$countSkipped} datasets
Errors:   {$countErrors} datasets ({$errorsFile})
Runtime: {$runtime} seconds

OUTPUT;