<?php
declare(strict_types=1);

class Loader
{
    /**
     * @param array<mixed> $data
     * @param string $outputFile
     *
     * @return int  number of datasets written to the output file
     */
    public function writeToFilesystem(array $data, string $outputFile): int
    {
        file_put_contents($outputFile, json_encode($data, JSON_PRETTY_PRINT));

        return count($data);
    }
}