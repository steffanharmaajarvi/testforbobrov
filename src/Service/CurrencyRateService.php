<?php

namespace App\Service;

use App\DTO\CurrencyConversionRateDTO;
use App\DTO\CurrencyRateDTO;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CurrencyRateService
{
    private const BASE_COIN_PAPRIKA_URL = 'https://api.coinpaprika.com/v1/exchanges/coinbase/markets?quotes=%s';
    private const BASE_FLOATRATES_URL = 'https://www.floatrates.com/daily/%s.json';

    public function __construct(
        private HttpClientInterface $client,
    ) {}

    /**
     * @param string $currencyCode
     * @return CurrencyRateDTO[]
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getRates(
        string $currencyCode
    ): array
    {
        $url = sprintf(self::BASE_COIN_PAPRIKA_URL, $currencyCode);

        $response = $this->client->request('GET', $url);

        $data = $response->toArray();

        $currencyRateArray = [];
        foreach ($data as $currencyPair) {
            $currencyRateDto = new CurrencyRateDTO(
                $currencyPair['quotes'][strtoupper($currencyCode)]['price'],
                strtoupper(explode('/', $currencyPair['pair'])[0]),
            );
            $currencyRateArray[] = $currencyRateDto;
        }

        $url = sprintf(self::BASE_FLOATRATES_URL, $currencyCode);

        $response = $this->client->request('GET', $url);

        $data = $response->toArray();

        foreach ($data as $code => $currencyPair) {
            $currencyRateDto = new CurrencyRateDTO(
                $currencyPair['rate'],
                strtoupper($code),
            );
            $currencyRateArray[] = $currencyRateDto;
        }

        $currencyRateArray[] = new CurrencyRateDTO(
            1,
            $currencyCode
        );

        return $currencyRateArray;
    }

    /**
     * @param string $currencyEnum
     * @throws \Exception
     * @return array
     */
    public function getCachedRates(
        string $currencyEnum
    ): array
    {

        $dirName = sprintf(
            __DIR__ . '/../../rates/%s',
            strtolower($currencyEnum),
        );
        $fileName = date('ymd') . '.json';
        $finder = new Finder();
        $finder->files()->in($dirName)->name($fileName);

        $rates = '{}';
        foreach ($finder as $file) {
            $rates = $file->getContents();
        }

        return json_decode($rates, true);
    }

    /**
     * @param string $from
     * @param string $to
     * @throws \Exception
     * @return array
     */
    public function getCachedRate(
        string $from,
        string $to,
    ): CurrencyRateDTO
    {
        $allRates = $this->getCachedRates($to);

        $ratesDataFiltered = array_filter(
            $allRates,
            static fn (array $currencyRate) => strtolower($currencyRate['code']) === strtolower($from)
        );
        $rateData = array_shift($ratesDataFiltered);

        if ($rateData === null) {
            throw new \Exception('Cached rate now found');
        }

        return new CurrencyRateDTO(
            $rateData['rate'],
            $rateData['code'],
        );
    }

    /**
     * @param string $from
     * @param string $to
     * @param float $amount
     * @return CurrencyConversionRateDTO
     * @throws \Exception
     */
    public function convert(
        string $from,
        string $to,
        float $amount
    ): CurrencyConversionRateDTO
    {
        $baseRate = $this->getCachedRate($from, $to);

        $convertedAmount = $baseRate->rate * $amount;

        return new CurrencyConversionRateDTO(
            $convertedAmount,
            new CurrencyRateDTO(
                1 / $baseRate->rate,
                $from
            ),
            new CurrencyRateDTO(
                $amount,
                $to
            )
        );

    }
}