<?php

namespace App\Command;

use App\Enums\CurrencyEnum;
use App\Service\CurrencyRateService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:save-rates',
    description: 'Saves currencies rates for USD.',
    aliases: ['app:save-rates'],
    hidden: false
)]
class SaveUSDCurrencyRatesCommand extends Command
{

    public function __construct(
        private readonly CurrencyRateService $currencyRateService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $fileName = sprintf(
            'rates/usd/%s.json',
            date('ymd')
        );
        $filesystem = new Filesystem();
        $filesystem->remove($fileName);

        $rates = $this->currencyRateService->getRates('usd');

        $resultArray = [];
        foreach ($rates as $rate) {
            $resultArray[] = [
                'rate' => $rate->rate,
                'code' => $rate->code
            ];
        }

        try {
            $filesystem->dumpFile(
                $fileName,
                json_encode($resultArray)
            );
        } catch (IOExceptionInterface $exception) {
            $output->writeln("An error occurred while creating your directory at ".$exception->getPath());
        }

        return Command::SUCCESS;

    }

}