<?php
declare(strict_types=1);

namespace Papstar\PimMiddleware\ETL;

/**
 * TODO: Normally factories are returning interfaces. But for this simple
 *       example we are defining direct classes directly.
 */
class Factory
{
    /**
     * @return Extractor
     */
    public function createExtractor(): Extractor
    {
        return new Extractor(
            $this->createValidator(),
        );
    }

    /**
     * @return Transformer
     */
    public function createTransformer(): Transformer
    {
        return new Transformer(
            $this->createValidator(),
        );
    }

    /**
     * @return Loader
     */
    public function createLoader(): Loader
    {
        return new Loader();
    }

    /**
     * @return Validator
     */
    protected function createValidator(): Validator
    {
        return new Validator();
    }
}