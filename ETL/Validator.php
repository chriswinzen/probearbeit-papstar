<?php
declare(strict_types=1);

class Validator
{
    protected const string ERROR_MESSAGE_FILE_DOES_NOT_EXIST = 'File does not exist: "%s"';

    protected const string ERROR_MESSAGE_FILE_NOT_READABLE = 'File is not readable: "%s"';

    protected const string ERROR_MESSAGE_SKU_INVALID = 'SKU is invalid: "%s"';

    protected const string ERROR_MESSAGE_PRICE_INVALID = 'Price is invalid: "%s"';

    protected const string ERROR_MESSAGE_QUANTITY_INVALID = 'Quantity is invalid: "%s"';

    /**
     * @param string $filepath
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function validateFilepath(string $filepath): void
    {
        if (!file_exists($filepath)) {
            throw new InvalidArgumentException(sprintf(static::ERROR_MESSAGE_FILE_DOES_NOT_EXIST, $filepath));
        }

        if (!is_readable($filepath)) {
            throw new InvalidArgumentException(sprintf(static::ERROR_MESSAGE_FILE_NOT_READABLE, $filepath));
        }
    }

    /**
     * @param mixed $sku
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     *
     */
    public function validateSku(mixed $sku): bool
    {
        if (!is_string($sku) || empty($sku)) {
            throw new InvalidArgumentException(
                sprintf(static::ERROR_MESSAGE_SKU_INVALID, $sku),
            );
        }

        return true;
    }

    /**
     * @param ?float $price
     * @param mixed $originalValue
     *
     * @return bool
     *
     */
    public function validatePrice(?float $price, mixed $originalValue): bool
    {
        if (!is_numeric($price) || $price < 0) {
            throw new InvalidArgumentException(
                sprintf(static::ERROR_MESSAGE_PRICE_INVALID, (string)$originalValue),
            );
        }

        return true;
    }

    /**
     * @param ?int $quantity
     * @param mixed $originalValue
     *
     * @return bool
     *
     */
    public function validateQuantity(?int $quantity, mixed $originalValue): bool
    {
        if ($quantity < 0) {
            throw new InvalidArgumentException(
                sprintf(static::ERROR_MESSAGE_QUANTITY_INVALID, (string)$originalValue),
            );
        }

        return true;
    }
}