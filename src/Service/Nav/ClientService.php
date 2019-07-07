<?php
declare(strict_types=1);
/**
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace App\Service\Nav;

use App\Entity\CreditRequest;
use App\Exceptions\UnexpectedNavResponseException;

/**
 * Class ClientService
 *
 * @package App\Service\Nav
 */
class ClientService extends BaseService
{
    const URL_CLIENT = 'Page/CustomerList';
    const COUNTRY_CODE = 'RO';

    /**
     * @param CreditRequest $cr
     * @param string $localityName
     * @return array
     * @throws \Exception
     * @throws \Throwable
     * @throws UnexpectedNavResponseException
     */
    public function createClient(CreditRequest $cr, string $localityName): array
    {
        $soap = $this->initSoap(static::URL_CLIENT);
        try {
            $response = $soap->Create($this->prepareClientPayload($cr, $localityName));
        } catch (\Throwable $exception) {
            $this->logger->alert($exception);
             throw $exception;
        }
        if (!property_exists($response, 'CustomerList')) {
            throw new UnexpectedNavResponseException("Error adding client in NAV");
        }

        return (array)$response->CustomerList;
    }

    /**
     * @param string $clientNo
     * @return array|null
     * @throws \Throwable
     */
    public function getClient(string $clientNo): ?array
    {
        $soap = $this->initSoap(static::URL_CLIENT);
        $response = $soap->ReadByRecId(['recId' => 'Credit Contract: '.$clientNo]);
        if (empty($response) || !property_exists($response, 'CustomerList')) {
            return null;
        }
        return (array)$response->CustomerList;
    }

    /**
     * @return array|null
     * @throws \Throwable
     */
    public function getClients(): array
    {
        $soap = $this->initSoap(static::URL_CLIENT);
        $list = $soap->ReadMultiple();
        if (!property_exists($list->ReadMultiple_Result, 'CustomerList')) {
            return null;
        }

        return (array)$list->ReadMultiple_Result->CustomerList;
    }

    /**
     * @param string $navClientKey
     * @return mixed
     * @throws UnexpectedNavResponseException
     * @throws \Throwable
     */
    public function deleteClient($navClientKey)
    {
        $soap = $this->initSoap(static::URL_CLIENT);
        $response = $soap->Delete(['Key' => $navClientKey]);

        if (!$response->Delete_Result) {
            throw new UnexpectedNavResponseException("Error deleting client in NAV");
        }

        return $response->Delete_Result;
    }

    /**
     * $localityName is
     *
     * @param CreditRequest $cr
     * @param string $localityName
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    protected function prepareClientPayload(CreditRequest $cr, string $localityName): array
    {
        $addressFields = $cr->getCustomer(true)->getIdentityCard(true)->getAddress(true)->getFullAddressFields();
        $fullAddress = $this->composeTranslatedAddress($addressFields);
        $explodedAddress = explode("\n", wordwrap($fullAddress, 50));

        return [
            'CustomerList' => [
                'Name'                  => $cr->getCustomer(true)->getFullName(),
                'Address'               => $explodedAddress[0] ?? '',
                'Address_2'             => $explodedAddress[1] ?? '',
                'City'                  => $localityName,
                'Phone_No'              => $cr->getCustomer(true)->getPhone(),
                'VAT_Registration_No'   => $cr->getCustomer(true)->getCnp(),
                'Post_Code'             => $cr->getCustomer(true)->getIdentityCard(true)
                                            ->getAddress(true)->getPostalCode(),
                'County'                => self::COUNTRY_CODE,
                'E_Mail'                => $cr->getCustomer(true)->getEmail(),
                'Country_Region_Code'   => self::COUNTRY_CODE
            ]
        ];
    }

    /**
     * Compose address in a human readable form,
     * from fields and label in the provided order
     *
     * @param array $addressFields
     * @return string
     * @throws \Exception
     */
    protected function composeTranslatedAddress(array $addressFields): string
    {
        $elements = $values = [];
        foreach ($addressFields as $label => $field) {
            $elements[] =  $label.' %'.$label.'%';
            $values['%'.$label.'%'] = $field;
        }
        $template = implode(', ', $elements);

        return $this->getTranslator()->trans($template, $values);
    }
}
