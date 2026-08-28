<?php

namespace App\Command;

use Aws\S3\S3Client;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(name: 'shelfit:configure-cors')]
readonly class ConfigureS3CorsCommand
{
    private const CORS_ALLOWED_METHODS = ['GET', 'PUT', 'HEAD', 'OPTIONS'];
    private const CORS_ALLOWED_ORIGINS = [
        'http://localhost:28029',
    ];

    public function __construct(
        private S3Client $s3Client,
    ) {
    }

    public function __invoke(#[Argument] string $bucketName, InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->s3Client->putBucketCors([
                'Bucket' => $bucketName,
                'CORSConfiguration' => ['CORSRules' => [[
                    'AllowedOrigins' => self::CORS_ALLOWED_ORIGINS,
                    'AllowedMethods' => self::CORS_ALLOWED_METHODS,
                    'AllowedHeaders' => ['*'],
                    'ExposeHeaders' => ['ETag'],
                    'MaxAgeSeconds' => 3000,
                ]]]
            ]);
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->info('CORS configured successfully for bucket ' . $bucketName);
        return Command::SUCCESS;
    }
}