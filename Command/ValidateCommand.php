<?php

namespace Recognize\DwhApplication\Command;

use Recognize\DwhApplication\Service\ValidationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ValidateCommand
 * @package Recognize\DwhApplication\Command
 * @author Bart Wesselink <b.wesselink@recognize.nl>
 */
class ValidateCommand extends Command
{
    /** @var ValidationService */
    private $validationService;

    /**
     * ValidateCommand constructor.
     * @param ValidationService $validationService
     */
    public function __construct(ValidationService $validationService)
    {
        parent::__construct();
        $this->validationService = $validationService;
    }


    protected function configure()
    {
        $this
            ->setName('recognize:dwh:validate')
            ->setDescription('Validates whether the mapping still corresponds with the current data model')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->comment('[Recognize DWH] Validating mapping...');

        $errors = $this->validationService->validate();
        $valid = count($errors) === 0;

        if ($valid) {
            $io->success('The entity mapping is valid.');

            return 0;
        } else {
            $io->error(sprintf('The entity mapping does not match the current data model: %s* %s', PHP_EOL, implode(PHP_EOL.'* ', $errors)));

            return 1;
        }
    }
}
