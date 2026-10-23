<?php
declare(strict_types=1);

namespace Papstar\PimMiddleware\ETL;

use InvalidArgumentException;
use Throwable;

use function floatval;
use function is_array;
use function is_numeric;
use function ksort;
use function preg_replace;
use function str_replace;
use function trim;

class Transformer
{
    const string VALUE_PRICE_KOSTENLOS = 'kostenlos';

    const string COLUMN_NAME_PRICE = 'preis';

    const string COLUMN_NAME_QUANTITY = 'bestand';

    const string COLUMN_NAME_SKU = 'sku';

    const string COLUMN_NAME_ERROR = 'error';

    /**
     * @param Validator $validator
     */
    public function __construct(
        protected readonly Validator $validator,
    ) {}

    /**
     * @param array<int, array<string,string> $data
     *
     * @return TransformerResponseDTO
     */
    public function transformCsvData(array $data): TransformerResponseDTO
    {
        $transformerResponseDTO = new TransformerResponseDTO();

        foreach($data as $row) {
            try {
                $row = $this->transformSku($row);
                $row = $this->transformQuantity($row);
                $row = $this->transformPrice($row);
            } catch (Throwable $exception) {
                $transformerResponseDTO->errors[] = [
                    'line' => $row['line'],
                    static::COLUMN_NAME_ERROR => $exception->getMessage(),
                ];

                continue;
            }

            $sku = $row[static::COLUMN_NAME_SKU];

            $skippedRow = $transformerResponseDTO->data[$sku] ?? null;

            if (is_array($skippedRow)) {
                $transformerResponseDTO->skipped[] = $skippedRow;
            }

            $transformerResponseDTO->data[$sku] = $row;
        }

        ksort($transformerResponseDTO->data);

        return $transformerResponseDTO;
    }

    /**
     * @param array<string,mixed> $row
     *
     * @return array<string,mixed>
     *
     * @throws InvalidArgumentException
     */
    protected function transformSku(array $row): array
    {
        $this->validator->validateSku($row[static::COLUMN_NAME_SKU]);

        return $row;
    }

    protected function transformQuantity(array $row): array
    {
        $originalValue = $row[static::COLUMN_NAME_QUANTITY];
        $quantity = is_numeric($originalValue) ? (int)$originalValue : null;

        $this->validator->validateQuantity($quantity, $originalValue);

        $row[static::COLUMN_NAME_QUANTITY] = $quantity;

        return $row;
    }

    protected function transformPrice(array $row): array
    {
        $originalValue = $row[static::COLUMN_NAME_PRICE];
        $price = trim($originalValue);

        if ($price === '') {
            $this->validator->validatePrice(null, $originalValue);
        }

        if($row[static::COLUMN_NAME_PRICE] === static::VALUE_PRICE_KOSTENLOS) {
            $price = 0;
        }

        // Remove any non-numeric characters except for commas, dots, and signs
        $price = preg_replace('/[^\d,.\-+]/u', '', trim((string)$price));
        // Replace commas with dots for decimal conversion
        $price = str_replace(',', '.', $price);

        $price = floatval($price);

        $this->validator->validatePrice($price, $originalValue);
        
        $row[static::COLUMN_NAME_PRICE] = number_format($price, 2, '.', '');

        return $row;
    }
}