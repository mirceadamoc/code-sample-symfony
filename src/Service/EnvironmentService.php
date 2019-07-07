<?php
/**
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace App\Service;

/**
 * Class EnvironmentService
 * @package App\Service
 */
class EnvironmentService
{
    /** @var string */
    private $navBaseUrl;

    /** @var string */
    private $navUser;

    /** @var string */
    private $navPass;

    /**
     * @param string $navBaseUrl
     * @param string $navUser
     * @param string $navPass
     * @required
     */
    public function setNav(
        string $navBaseUrl,
        string $navUser,
        string $navPass
    ) {
        $this->navBaseUrl = $navBaseUrl;
        $this->navUser = $navUser;
        $this->navPass = $navPass;
    }

    /**
     * @return string
     */
    public function getNavBaseUrl(): string
    {
        return $this->navBaseUrl;
    }

    /**
     * @return string
     */
    public function getNavUser(): string
    {
        return $this->navUser;
    }

    /**
     * @return string
     */
    public function getNavPass(): string
    {
        return $this->navPass;
    }
}
