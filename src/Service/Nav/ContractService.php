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
use App\Service\CreditRequestContractService;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class ContractService
 *
 * @package App\Service
 */
class ContractService extends BaseService
{
    const URL_CONTRACT          = 'Page/ContractList';
    const URL_CONTRACT_GOODS    = 'Page/ContractGoods';
    const EXCHANGE_RATE         = 1;
    const CURRENCY_CODE         = ''; // if RON leave empty
    const PAYMENT_SCHEDULE_TYPE = 'Equal'; // [Equal, Descending]
    const CONTRACT_STATUS       = 'Draft'; // [Draft, Validated, Canceled, Closed]
    const INSURANCE_VENDOR_NO   = 'FZ-000003'; // TODO hardcoded at the moment, IFN has only one insurer

    const CONTRACT_TYPE_GOODS = CreditRequest::TYPE_BUY_GOODS;
    const CONTRACT_TYPE_NEEDS = CreditRequest::TYPE_CREDIT;

    const CONTRACT_TYPES = [
        self::CONTRACT_TYPE_GOODS  => 'Goods',
        self::CONTRACT_TYPE_NEEDS  => 'Personal_Needs',
        'Auto'                     => 'Auto'
    ];

    /** @var CreditRequestContractService */
    protected $contractService;

    /**
     * @param CreditRequestContractService $service
     * @required
     */
    public function setContractService(CreditRequestContractService $service): void
    {
        $this->contractService = $service;
    }

    /**
     * @param CreditRequest $cr
     * @param string $createdBy
     * @param array $offerDetails client accepted offer
     * @param string $vendorId
     * @param string $navCustomerNumber from NAV
     * @return mixed
     * @throws UnexpectedNavResponseException
     * @throws \Throwable
     */
    public function createContract(
        CreditRequest $cr,
        string $createdBy,
        array $offerDetails,
        string $vendorId,
        string $navCustomerNumber
    ) {
        if (!array_key_exists($cr->getType(), self::CONTRACT_TYPES)) {
            throw new InvalidArgumentException("Undefined contract type");
        }

        $soap = $this->initSoap(static::URL_CONTRACT);
        $contract = $soap->Create($this->prepareContractPayload($cr, $createdBy, $offerDetails, $navCustomerNumber));
        if (!property_exists($contract, 'ContractList')) {
            throw new UnexpectedNavResponseException("Error adding contract in NAV");
        }
        $response = (array)$contract->ContractList;

        // add contract goods, if any
        if ($cr->getProducts()->count() > 0) {
            $response['goods'] = $this->addContractGoods($cr, $vendorId, $contract->ContractList->Contract_No);
        }

        // validate contract in NAV
        $this->validateContract($contract->ContractList->Key);

        return $response;
    }

    /**
     * @param string $navContractKey
     * @throws \Throwable
     */
    protected function validateContract($navContractKey)
    {
        $soap = $this->initSoap(static::URL_CONTRACT);
        $soap->ValidateContract(['contract' => $navContractKey]);
    }

    /**
     * @param string $contractNo
     * @return mixed
     * @throws \Exception
     */
    public function getContract(string $contractNo)
    {
        $soap = $this->initSoap(static::URL_CONTRACT);
        $response = $soap->ReadByRecId(['recId' => 'Credit Contract: '.$contractNo]);
        if (empty($response) || !property_exists($response, 'ContractList')) {
            return null;
        }
        return (array)$response->ContractList;
    }

    /**
     * @param array $filter
     * @param int $resultsPerPage
     * @return mixed
     * @throws \Exception
     */
    public function getContracts(array $filter = [], int $resultsPerPage = 0)
    {
        $navFilter = null;
        if (!empty($filter)) {
            $navFilter = ['Field' => \key($filter), 'Criteria' => \current($filter)];
        }

        $soap = $this->initSoap(static::URL_CONTRACT);
        $response = $soap->ReadMultiple(['filter' => $navFilter, 'setSize' => $resultsPerPage]);
        if (!property_exists($response->ReadMultiple_Result, 'ContractList')) {
            return null;
        }
        return $response->ReadMultiple_Result->ContractList;
    }

    /**
     * @param string $navContractKey
     * @return mixed
     * @throws UnexpectedNavResponseException
     * @throws \Exception
     */
    public function deleteContract($navContractKey)
    {
        $soap = $this->initSoap(static::URL_CONTRACT);
        $response = $soap->Delete(['Key' => $navContractKey]);

        if (!$response->Delete_Result) {
            throw new UnexpectedNavResponseException("Error deleting contract from NAV");
        }

        return $response->Delete_Result;
    }


    /**
     * @param CreditRequest $cr
     * @param string $contractNumber
     * @param string $vendorId
     * @throws \Throwable
     * @throws UnexpectedNavResponseException
     * @return array
     */
    protected function addContractGoods(CreditRequest $cr, string $vendorId, string $contractNumber)
    {
        $soap = $this->initSoap(static::URL_CONTRACT_GOODS);
        $response = $soap->CreateMultiple($this->prepareContractGoodsPayload($cr, $vendorId, $contractNumber));

        if (!property_exists($response, 'ContractGoods_List')) {
            throw new UnexpectedNavResponseException("Error adding contract products in NAV");
        }

        $contractGoods = $response->ContractGoods_List->ContractGoods;
        return (is_array($contractGoods)) ?
            array_map(function ($val) {
                return (array)$val;
            }, $contractGoods) :
            (array)$contractGoods;
    }

    /**
     * @param array $filter
     * @param int $resultsPerPage
     * @return mixed
     * @throws \Exception
     */
    public function getContractGoods(array $filter = [], int $resultsPerPage = 0)
    {
        $navFilter = null;
        if (!empty($filter)) {
            $navFilter = ['Field' => \key($filter), 'Criteria' => \current($filter)];
        }

        $soap = $this->initSoap(static::URL_CONTRACT_GOODS);
        $response = $soap->ReadMultiple(['filter' => $navFilter, 'setSize' => $resultsPerPage]);
        if (!property_exists($response->ReadMultiple_Result, 'ContractGoodsList')) {
            return null;
        }
        return $response->ReadMultiple_Result->ContractGoodsList;
    }

    /**
     * @param array $filter
     * @param int $resultsPerPage
     * @return mixed
     * @throws \Exception
     */
    public function deleteContractGoods(array $filter = [], int $resultsPerPage = 0)
    {
        $navFilter = null;
        if (!empty($filter)) {
            $navFilter = ['Field' => \key($filter), 'Criteria' => \current($filter)];
        }

        $soap = $this->initSoap(static::URL_CONTRACT_GOODS);
        $response = $soap->Delete(['filter' => $navFilter, 'setSize' => $resultsPerPage]);
        if (!property_exists($response->ReadMultiple_Result, 'ContractGoodsList')) {
            return null;
        }
        return $response->ReadMultiple_Result->ContractGoodsList;
    }

    /**
     * @param CreditRequest $cr
     * @param string $responsibleEmployee
     * @param array $offerDetails
     * @param string $navCustomerNumber
     * @return array
     * @throws \Throwable
     */
    protected function prepareContractPayload(
        CreditRequest $cr,
        string $responsibleEmployee,
        array $offerDetails,
        string $navCustomerNumber
    ) {
        $contractType = self::CONTRACT_TYPES[$cr->getType()];
        $contract = $this->contractService->getContractInformation($cr);
        $advancePercent = $cr->getDetail(true)->getDownPayment()*100/$cr->getDetail(true)->getLoanAmount();

        return [
            'ContractList' => [
                'Contract_No'               => $contract->getNumber(),
                'Contract_Date'             => $cr->getContract()->getCreatedAt()->format('Y-m-d'),
                'Contract_Status'           => self::CONTRACT_STATUS,
                'Customer_No'               => $navCustomerNumber,
                'Responsible_Employee'      => $responsibleEmployee,
                'Payment_Schedule_Type'     => self::PAYMENT_SCHEDULE_TYPE,
                'No_of_Installments'        => $cr->getDetail(true)->getInstallments(),
                'Payment_Day_of_Month'      => $cr->getDetail(true)->getPaymentDay(),
                'Contract_Type'             => $contractType,
                'Financial_Product'         => $offerDetails['financial_product'],
                'Currency_Code'             => self::CURRENCY_CODE,
                'Exchange_Rate'             => self::EXCHANGE_RATE,
                'Item_Price'                => 0, // for Contract_Type:Goods (decimal)
                'Funded_Amount'             => $cr->getDetail(true)->getLoanAmount(),
                'Advance_Percent'           => $advancePercent,
                'Interest'                  => $offerDetails['interest'],
                'Insurance_Percent'         => $offerDetails['insurance'],
                'Analysis_Fee'              => $offerDetails['analysis_fee'],
                'AEGRM'                     => $offerDetails['aegrm'],
                'Broker_Fee'                => $offerDetails['broker_fee'],
                'Monthly_Fee'               => $offerDetails['monthly_fee'],
                'Insurance_Vendor_No'       => self::INSURANCE_VENDOR_NO,
            ]
        ];
    }

    /**
     * @param CreditRequest $cr
     * @param string $vendorId
     * @param string $contractNo
     * @return array
     * @throws \Throwable
     */
    protected function prepareContractGoodsPayload(CreditRequest $cr, string $vendorId, string $contractNo)
    {
        $navProducts = [];
        $crProducts = $cr->getProducts()->toArray();
        foreach ($crProducts as $count => $crProduct) {
            array_push($navProducts, [
                'Contract_No'   => $contractNo,
                'Line_No'       => $count,  // increment
                'Object'        => $crProduct->getName(), // product name
                'Quantity'      => 1,  // (decimal)
                'Amount'        => $crProduct->getPrice(),  // product price (decimal)
                'Vendor_No'     => $vendorId, // FK from vendors (string)
                'Description'   => $crProduct->getName()  // (string)
            ]);
        }

        return [
            'ContractGoods_List' => $navProducts
        ];
    }
}
