<?php
declare(strict_types=1);

namespace Papstar\PimMiddleware\ETL;

class TransformerResponseDTO
{
    /**
     * @var array <string, array<string,string>>
     */
    public array $data = [];

    /**
     * @var array<int,array<string,string>>
     */
    public array $skipped = [];

    /**
     * @var array<int,array<string,string>>
     */
    public array $errors = [];
}