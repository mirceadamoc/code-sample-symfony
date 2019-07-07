<?php
declare(strict_types=1);
/**
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace App\Handler\Flow;

use App\Dto\HandlerFlowPayloadInterface;
use App\Service\Nav\ClientService;
use App\Service\Nav\ContractService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class NavHandler
 * @package App\Handler
 */
class NavHandler extends BaseHandler
{

    /** @var ClientService*/
    private $navClientService;

    /** @var ContractService*/
    private $navContractService;

    /**
     * @return string
     */
    public static function getType(): string
    {
        return NavHandler::class;
    }

    /**
     * @param ClientService $service
     * @required
     */
    public function setNavClientService(ClientService $service): void
    {
        $this->navClientService = $service;
    }

    /**
     * @param ContractService $service
     * @required
     */
    public function setNavContractService(ContractService $service): void
    {
        $this->navContractService = $service;
    }

    /**
     * @param HandlerFlowPayloadInterface $payload
     * @throws \Throwable
     */
    public function sendContractToNav(HandlerFlowPayloadInterface $payload): void
    {

        $content = $payload->getContent();
        $creditRequest = $payload->getContentParam('credit_request');
        $createdBy = $payload->getContentParam('createdBy');
        $localityName = $payload->getContentParam('localityName');
        $offerDetails = $payload->getContentParam('offerDetails');
        $vendorId = $payload->getContentParam('vendorId');

        if (empty($creditRequest)) {
            $this->logger->error('Credit Request not properly defined!');
            $payload->addMessage('Credit Request not properly defined!', Response::HTTP_INTERNAL_SERVER_ERROR);
            $payload->stopPropagation();
            return;
        }
        if (empty($offerDetails)) {
            $this->logger->error('Credit request offer not properly defined!');
            $payload->addMessage('Credit request offer not properly defined!', Response::HTTP_INTERNAL_SERVER_ERROR);
            $payload->stopPropagation();
            return;
        }

        try {
            $client = $this->navClientService->createClient($creditRequest, $localityName);
            $content['navClient'] = $client;
        } catch (\Exception $e) {
            $this->logger->error($e);
            $payload->addMessage('Creating client in NAV failed!', Response::HTTP_INTERNAL_SERVER_ERROR);
            $payload->stopPropagation();
            return;
        }

        try {
            $contract = $this->navContractService->createContract($creditRequest, $createdBy, $offerDetails, $vendorId, $client['No']);
            $content['navContract'] = $contract;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $payload->addMessage('Creating contract in NAV failed!', Response::HTTP_INTERNAL_SERVER_ERROR);
            $payload->stopPropagation();
            $this->navClientService->deleteClient($client['Key']);
            return;
        }

        $payload->update($content);
    }
}
