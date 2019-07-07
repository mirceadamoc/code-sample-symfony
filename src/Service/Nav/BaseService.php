<?php
/**
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace App\Service\Nav;

use App\Service\EnvironmentService;
use App\Traits\TranslatorTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;

abstract class BaseService
{
    use LoggerAwareTrait, TranslatorTrait;

    /** @var LoggerInterface */
    protected $logger;

    /** @var EnvironmentService $env */
    protected $env;

    /**
     * CreditRequestPersistService constructor.
     * @param LoggerInterface $logger
     * @param EnvironmentService $environment
     */
    public function __construct(LoggerInterface $logger, EnvironmentService $environment)
    {
        $this->setLogger($logger);
        $this->env = $environment;
    }

    /**
     * @param string $url
     * @return \SoapClient
     * @throws \Throwable
     */
    public function initSoap($url)
    {
        $url = $this->env->getNavBaseUrl().$url;
        try {
            $options = array(
                'login' => $this->env->getNavUser(),
                'password' => $this->env->getNavPass(),
            );
            return new \SoapClient($url, $options);
        } catch (\Throwable $exception) {
            $this->logger->alert($exception);
            throw $exception;
        }
    }
}
