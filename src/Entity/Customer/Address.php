<?php
/**
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
declare(strict_types=1);
namespace App\Entity\Customer;

use App\Entity\EntityInterface;
use App\Entity\TrackableInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use App\Entity\PhaseInterface;

/**
 * Address
 *
 * @ORM\Table(name="address")
 * @ORM\Entity
 *
 * @Assert\GroupSequence({
 *     "Address",
 *     PhaseInterface::PERSONAL_DATA,
 *     PhaseInterface::OTHER
 * })
 *
 * @Nomenclature(groups={
 *     "Address",
 *     PhaseInterface::PERSONAL_DATA,
 *     PhaseInterface::OTHER
 * })
 */
class Address implements TrackableInterface, EntityInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @Serializer\Type("int")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="apartment_number", type="string", length=45, nullable=true)
     * @Serializer\Type("string")
     */
    private $apartmentNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="floor", type="string", length=45, nullable=true)
     * @Serializer\Type("string")
     */
    private $floor;

    /**
     * @var int
     *
     * @ORM\Column(name="region_id", type="integer", nullable=false)
     * @Serializer\Type("integer")
     *
     * @Assert\NotBlank(groups={
     *     PhaseInterface::PERSONAL_DATA,
     *     PhaseInterface::OTHER,
     *     Nomenclature::GROUP_NAME
     * })
     */
    private $regionId;

    /**
     * @var integer
     *
     * @ORM\Column(name="locality_id", type="integer", length=45, nullable=false)
     * @Serializer\Type("integer")
     *
     * @Assert\NotBlank(groups={
     *     PhaseInterface::PERSONAL_DATA,
     *     PhaseInterface::OTHER,
     *     Nomenclature::GROUP_NAME
     * })
     */
    protected $localityId;

    /**
     * @var string
     *
     * @ORM\Column(name="postal_code", type="string", length=45, nullable=true)
     * @Serializer\Type("string")
     *
     */
    private $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(name="staircase", type="string", length=45, nullable=true)
     * @Serializer\Type("string")
     */
    private $staircase;

    /**
     * @var string
     *
     * @ORM\Column(name="building", type="string", length=45, nullable=true)
     * @Serializer\Type("string")
     *
     */
    private $building;

    /**
     * @var string
     *
     * @ORM\Column(name="street", type="string", length=45, nullable=false)
     * @Serializer\Type("string")
     *
     * @Assert\NotBlank(groups={PhaseInterface::PERSONAL_DATA, PhaseInterface::OTHER})
     */
    private $street;

    /**
     * @var string
     *
     * @ORM\Column(name="street_number", type="string", length=45, nullable=true)
     * @Serializer\Type("string")
     *
     * @Assert\NotBlank(groups={PhaseInterface::PERSONAL_DATA, PhaseInterface::OTHER})
     */
    private $streetNumber;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Address
     */
    public function setId(int $id): Address
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getApartmentNumber(): ?string
    {
        return $this->apartmentNumber;
    }

    /**
     * @param string $apartmentNumber
     * @return Address
     */
    public function setApartmentNumber(?string $apartmentNumber): Address
    {
        $this->apartmentNumber = $apartmentNumber;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFloor(): ?string
    {
        return $this->floor;
    }

    /**
     * @param string $floor
     * @return Address
     */
    public function setFloor(?string $floor): Address
    {
        $this->floor = $floor;
        return $this;
    }

    /**
     * @return int
     */
    public function getRegionId(): int
    {
        return $this->regionId;
    }

    /**
     * @param int $regionId
     * @return Address
     */
    public function setRegionId(int $regionId): Address
    {
        $this->regionId = $regionId;
        return $this;
    }

    /**
     * @return integer
     */
    public function getLocalityId(): int
    {
        return $this->localityId;
    }

    /**
     * @param integer $localityId
     * @return Address
     */
    public function setLocalityId(int $localityId): Address
    {
        $this->localityId = $localityId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     * @return Address
     */
    public function setPostalCode(?string $postalCode): Address
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStaircase(): ?string
    {
        return $this->staircase;
    }

    /**
     * @param string $staircase
     * @return Address
     */
    public function setStaircase(?string $staircase): Address
    {
        $this->staircase = $staircase;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBuilding(): ?string
    {
        return $this->building;
    }

    /**
     * @param string $building
     * @return Address
     */
    public function setBuilding(?string $building): Address
    {
        $this->building = $building;
        return $this;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @param string $street
     * @return Address
     */
    public function setStreet(string $street): Address
    {
        $this->street = $street;
        return $this;
    }

    /**
     * @return string
     */
    public function getStreetNumber(): ?string
    {
        return $this->streetNumber;
    }

    /**
     * @param string $streetNumber
     * @return Address
     */
    public function setStreetNumber(?string $streetNumber): Address
    {
        $this->streetNumber = $streetNumber;
        return $this;
    }

    /**
     * @return array
     */
    public function getFullAddressFields(): array
    {
        $addressFields = [
            'Street' => $this->getStreet(),
            'Number' => $this->getStreetNumber(),
            'Building' => $this->getBuilding(),
            'Staircase' => $this->getStaircase(),
            'Floor' => $this->getFloor(),
            'Apartment' => $this->getApartmentNumber()
        ];

        return array_filter($addressFields);
    }
}
