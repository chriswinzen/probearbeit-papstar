<?php
declare(strict_types=1);

namespace Papstar\PimMiddleware\ETL;

use RuntimeException;
use Throwable;

use function array_combine;
use function fclose;
use function fgetcsv;
use function fopen;

class Extractor
{
    protected const string ROW_NUMBER_KEY = 'line';

    protected const string FILE_MODE_READ = 'r';

    protected const string ROW_IMPORT_TIMESTAMP = 'import_timestamp';

    protected const string ERROR_MESSAGE_ERROR_READING_CSV = 'Error while reading CSV file "%s": %s';

    /**
     * @param Validator $validator
     */
    public function __construct(
        protected readonly Validator $validator,
    ) {}

    /**
     * TODO: For bigger files consider using a generator to yield each row and/or using chunks
     *
     * @param string $csvFilepath
     *
     * @return array<int, array<string,string>>
     *
     * @throws RuntimeException
     */
    public function extractCsvFile(string $csvFilepath): array
    {
        $this->validator->validateFilepath($csvFilepath);

        try {
            // Ignores encoding. For this example we assume the CSV file is UTF-8 encoded.
            // If not, we could use mb_convert_encoding() to convert it to UTF-8.
            $fileHandle = fopen($csvFilepath, static::FILE_MODE_READ);

            $readRow = static fn() => fgetcsv(stream: $fileHandle, separator: ';');

            $header = $readRow();
            $header[] = static::ROW_NUMBER_KEY;
            $header[] = static::ROW_IMPORT_TIMESTAMP;
            $data = [];

            $currentLineNumber = 2; // Start from line 2 since line 1 (human-readable) is the header.

            while (($row = $readRow()) !== false) {
                $row[static::ROW_NUMBER_KEY] = $currentLineNumber++;
                $row[static::ROW_IMPORT_TIMESTAMP] = REQUEST_TIMESTAMP;

                $data[] = array_combine($header, $row);
            }

            return $data;
        } catch (Throwable $exception) {
            throw new RuntimeException(
                message: sprintf(
                    static::ERROR_MESSAGE_ERROR_READING_CSV,
                    $csvFilepath,
                    $exception->getMessage()),
                previous: $exception,
            );
        } finally {
            fclose($fileHandle);
        }
    }
}