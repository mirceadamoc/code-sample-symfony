<?php
declare(strict_types=1);
/**
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace App\Entity;

use App\Entity\Customer\Address;
use App\Entity\Customer\Agreement;
use App\Entity\Customer\Detail;
use App\Entity\Customer\Employment;
use App\Entity\Customer\IdentityCard;
use App\Entity\Customer\Political;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use App\Entity\Customer\ProfessionalStatus;
use App\Validator\Constraint as AppAssert;

/**
 * Customer
 *
 * @ORM\Table(name="customer")
 * @ORM\Entity
 *
 * @Assert\GroupSequence({
 *     "Customer",
 *     PhaseInterface::PERSONAL_DATA,
 *     PhaseInterface::FINANCIAL_DOCUMENTS,
 *     PhaseInterface::OTHER,
 *     PhaseInterface::ANAF
 * })
 */
class Customer implements TrackableInterface, EntityInterface
{
    public const GENDER_MALE = 'm';
    public const GENDER_FEMALE = 'f';

    public const GENDERS = [
        self::GENDER_MALE => 'Male',
        self::GENDER_FEMALE => 'Female'
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @Serializer\Type("int")
     * @Serializer\Groups({EntityInterface::LISTING, EntityInterface::UPDATE})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=false)
     *
     * @Assert\NotBlank(groups={PhaseInterface::PERSONAL_DATA})
     *
     * @Assert\Length(
     *     max = 255,
     *     maxMessage="""first_name"": This value is too long. It should have {{ limit }} character or less.",
     *     groups={PhaseInterface::PERSONAL_DATA}
     * )
     *
     * @Serializer\Type("string")
     * @Serializer\Groups({EntityInterface::LISTING, EntityInterface::CREATE, EntityInterface::UPDATE})
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=false)
     * @Assert\NotBlank(groups={PhaseInterface::PERSONAL_DATA})
     * @Assert\Length(
     *     max = 255,
     *     maxMessage="""last_name"": This value is too long. It should have {{ limit }} character or less.",
     *     groups={PhaseInterface::PERSONAL_DATA}
     * )
     * @Serializer\Type("string")
     * @Serializer\Groups({EntityInterface::LISTING, EntityInterface::CREATE, EntityInterface::UPDATE})
     */
    protected $lastName;

    /**
     * @var IdentityCard|null
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Customer\IdentityCard",
     *     cascade={"persist"},
     * )
     *
     * @Assert\Valid()
     *
     * @Serializer\Accessor(setter="setIdentityCard")
     * @Serializer\Type("App\Entity\Customer\IdentityCard")
     * @Serializer\Groups({EntityInterface::LISTING, EntityInterface::GET})
     */
    protected $identityCard;

    /**
     * @var ArrayCollection|ProfessionalStatus[]
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Customer\ProfessionalStatus",
     *     mappedBy="customer",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     *
     * @Assert\Valid(groups={PhaseInterface::FINANCIAL_DOCUMENTS, PhaseInterface::ANAF})
     * @Assert\NotBlank(groups={PhaseInterface::FINANCIAL_DOCUMENTS, PhaseInterface::ANAF, PhaseInterface::PERSONAL_DATA})
     * @Assert\Count(
     *     min="1",
     *     groups={PhaseInterface::FINANCIAL_DOCUMENTS, PhaseInterface::ANAF, PhaseInterface::PERSONAL_DATA},
     *     minMessage="Professional Status should contain {{ limit }} element or more.|Professional Status should contain {{ limit }} elements or more."
     * )
     * @Serializer\Groups({EntityInterface::LISTING, EntityInterface::CREATE, EntityInterface::UPDATE})
     * @Serializer\Type("ArrayCollection<App\Entity\Customer\ProfessionalStatus>")
     * @AppAssert\ProfessionalStatus(groups={PhaseInterface::FINANCIAL_DOCUMENTS, PhaseInterface::ANAF, PhaseInterface::PERSONAL_DATA})
     */
    protected $professionalStatus;

    /**
     * @var Detail|null
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Customer\Detail",
     *     cascade={"persist"},
     * )
     *
     * @Assert\Valid(groups={
     *     PhaseInterface::OTHER,
     *     PhaseInterface::FINANCIAL_DOCUMENTS,
     *     PhaseInterface::ANAF
     * })
     *
     * @Serializer\Accessor(setter="setDetail", getter="getDetail")
     * @Serializer\Type("App\Entity\Customer\Detail")
     */
    protected $detail;

    /**
     * @var Employment|null
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Customer\Employment",
     *     cascade={"persist"},
     *     mappedBy="customer"
     * )
     *
     * @Assert\Valid(groups={PhaseInterface::OTHER, PhaseInterface::ANAF})
     *
     * @Serializer\Accessor(setter="setEmployment", getter="getEmployment")
     * @Serializer\Type("App\Entity\Customer\Employment")
     */
    protected $employment;

    /**
     * @var string
     *
     * @ORM\Column(name="mobile_phone", type="string", length=255, nullable=true)
     *
     * @Assert\NotBlank(groups={PhaseInterface::OTHER})
     * @Assert\Regex(
     *     message="This values is not a valid phone.",
     *     pattern="/^(\+\d{1,3}[- ]?|\d{3})?\d{10}$/",
     *     groups={PhaseInterface::OTHER}
     * )
     * @Assert\Length(
     *     max = 255,
     *     maxMessage="""mobile_phone"": This value is too long. It should have {{ limit }} character or less.",
     *     groups={PhaseInterface::OTHER}
     * )
     *
     * @Serializer\Type("string")
     * @Serializer\Groups({EntityInterface::LISTING, EntityInterface::CREATE, EntityInterface::UPDATE})
     */
    protected $mobilePhone;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     *
     * @Assert\Regex(
     *     message="This values is not a valid phone.",
     *     pattern="/^(\+\d{1,3}[- ]?|\d{3})?\d{10}$/",
     *     groups={PhaseInterface::OTHER}
     * )
     * @Assert\Length(
     *     max = 255,
     *     maxMessage="""phone"": This value is too long. It should have {{ limit }} character or less.",
     *     groups={PhaseInterface::OTHER}
     * )
     *
     * @Serializer\Type("string")
     * @Serializer\Groups({EntityInterface::LISTING, EntityInterface::CREATE, EntityInterface::UPDATE})
     */
    protected $phone;

    /**
     * @var string|null
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     *
     * @Assert\Email(groups={PhaseInterface::OTHER})
     * @Assert\Length(
     *     max = 255,
     *     maxMessage="""phone"": This value is too long. It should have {{ limit }} character or less.",
     *     groups={PhaseInterface::OTHER}
     * )
     *
     * @Serializer\Type("string")
     * @Serializer\Groups({EntityInterface::LISTING, EntityInterface::CREATE, EntityInterface::UPDATE})
     */
    protected $email;

    /**
     * @var string|null
     *
     * @ORM\Column(name="gender", type="string", length=1, nullable=true)
     *
     * @Assert\NotBlank(
     *     groups={PhaseInterface::OTHER}
     * )
     * @Assert\Choice(
     *     choices={Customer::GENDER_MALE, Customer::GENDER_FEMALE},
     *     groups={PhaseInterface::OTHER, PhaseInterface::PERSONAL_DATA}
     * )
     *
     * @Serializer\Type("string")
     * @Serializer\Groups({EntityInterface::LISTING, EntityInterface::CREATE, EntityInterface::UPDATE})
     */
    protected $gender;

    /**
     * @var Address
     *
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Customer\Address",
     *     cascade={"persist"},
     *     fetch="LAZY"
     * )
     *
     * @Assert\Valid(groups={
     *     PhaseInterface::OTHER,
     *     PhaseInterface::PERSONAL_DATA
     * })
     * @Serializer\Type("App\Entity\Customer\Address")
     */
    protected $correspondenceAddress;

    /**
     * @var Political|null
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Customer\Political",
     *     fetch="LAZY", mappedBy="customer",
     *     cascade={"persist"}
     * )
     *
     * @Serializer\Accessor(setter="setPolitical", getter="getPolitical")
     * @Serializer\Type("App\Entity\Customer\Political")
     *
     * @Assert\Valid(groups={PhaseInterface::FINANCIAL_DOCUMENTS})
     */
    protected $political;

    /**
     * @var CreditRequest
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\CreditRequest",
     *     fetch="LAZY",
     *     mappedBy="customer"
     * )
     * @Serializer\Type("App\Entity\CreditRequest")
     */
    protected $creditRequest;

    /**
     * @var Agreement|null
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Customer\Agreement",
     *     fetch="LAZY",
     *     mappedBy="customer",
     *     cascade={"persist"}
     * )
     * @Serializer\Type("App\Entity\Customer\Agreement")
     * @Serializer\Accessor(setter="setAgreement", getter="getAgreement")
     * @Assert\Valid(groups={PhaseInterface::FINANCIAL_DOCUMENTS})
     */
    protected $agreement;

    /**
     * @ORM\Column(
     *     type="datetime",
     *     nullable=false,
     *     name="created_at",
     *     options={"default": "CURRENT_TIMESTAMP"},
     * )
     * @Serializer\Groups({EntityInterface::LISTING})
     * @Serializer\Type("DateTime<'Y-m-d H:i:s'>")
     */
    protected $createdAt;

    /**
     * @ORM\Column(
     *     name="updated_at",
     *     type="datetime",
     *     nullable=false,
     *     columnDefinition="DATETIME NOT NULL on update CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
     * )
     * @Serializer\Groups({EntityInterface::LISTING})
     * @Serializer\Type("DateTime<'Y-m-d H:i:s'>")
     */
    protected $updatedAt;

    public function __construct()
    {
        $this->createdAt = $this->updatedAt = new \DateTime();
        $this->professionalStatus = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Customer
     */
    public function setId(int $id): Customer
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return Customer
     */
    public function setFirstName(string $firstName): Customer
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return Customer
     */
    public function setLastName(string $lastName): Customer
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return \implode(' ', [$this->firstName, $this->lastName]);
    }

    /**
     * @return string
     */
    public function getMobilePhone(): string
    {
        return $this->mobilePhone;
    }

    /**
     * @param string $mobilePhone
     * @return Customer
     */
    public function setMobilePhone(string $mobilePhone): Customer
    {
        $this->mobilePhone = $mobilePhone;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return Customer
     */
    public function setPhone(string $phone): Customer
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     * @return Customer
     */
    public function setEmail(?string $email): Customer
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGender(): ?string
    {
        return $this->gender;
    }

    /**
     * @param string|null $gender
     * @return Customer
     */
    public function setGender(?string $gender): Customer
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * @return Address
     */
    public function getCorrespondenceAddress(): Address
    {
        return $this->correspondenceAddress;
    }

    /**
     * @param Address $correspondenceAddress
     * @return Customer
     */
    public function setCorrespondenceAddress(Address $correspondenceAddress): Customer
    {
        $this->correspondenceAddress = $correspondenceAddress;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param bool $forceCreate
     * @return Detail|null
     */
    public function getDetail(bool $forceCreate = false): ?Detail
    {
        if ($forceCreate && null === $this->detail) {
            $this->setDetail(new Detail());
        }

        return $this->detail;
    }

    /**
     * @param Detail $detail
     * @return Customer
     */
    public function setDetail(Detail $detail): Customer
    {
        $this->detail = $detail;
        $this->detail->setCustomer($this);
        return $this;
    }

    /**
     * @param bool $forceCreate
     * @return Employment|null
     */
    public function getEmployment(bool $forceCreate = false): ?Employment
    {
        if ($forceCreate && null === $this->employment) {
            $this->setEmployment(new Employment());
        }

        return $this->employment;
    }

    /**
     * @param Employment $employment
     * @return Customer
     */
    public function setEmployment(Employment $employment): Customer
    {
        $this->employment = $employment;
        $this->employment->setCustomer($this);
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getProfessionalStatus(): Collection
    {
        return $this->professionalStatus;
    }

    /**
     * @param ProfessionalStatus $professionalStatus
     * @return Customer
     */
    public function addProfessionalStatus(ProfessionalStatus $professionalStatus): self
    {
        if (!$this->professionalStatus->contains($professionalStatus)) {
            if (!$this->professionalStatus->isDirty()) {
                /** @var ProfessionalStatus $values */
                foreach ($this->professionalStatus->getValues() as $values) {
                    $this->professionalStatus->removeElement($values);
                    $values->setCustomer(null);
                }
            }


            $professionalStatus->setCustomer($this);
            $this->professionalStatus[] = $professionalStatus;
        }

        return $this;
    }

    /**
     * @param ProfessionalStatus $professionalStatus
     * @return Customer
     */
    public function setProfessionalStatus(ProfessionalStatus $professionalStatus): self
    {
        $professionalStatus->setCustomer($this);
        $this->professionalStatus[] = $professionalStatus;

        return $this;
    }

    /**
     * @param bool $forceCreate
     * @return Agreement|null
     */
    public function getAgreement(bool $forceCreate = false): ?Agreement
    {
        if ($forceCreate && null === $this->agreement) {
            $this->setAgreement(new Agreement());
        }

        return $this->agreement;
    }

    /**
     * @param Agreement $agreement
     * @return Customer
     */
    public function setAgreement(Agreement $agreement): Customer
    {
        $this->agreement = $agreement;
        $this->agreement->setCustomer($this);
        return $this;
    }

    /**
     * @param bool $forceCreate
     * @return Political|null
     */
    public function getPolitical(bool $forceCreate = false): ?Political
    {
        if ($forceCreate && null === $this->political) {
            $this->setPolitical(new Political());
        }

        return $this->political;
    }

    /**
     * @param Political $political
     * @return Customer
     */
    public function setPolitical(Political $political): Customer
    {
        $this->political = $political;
        $this->political->setCustomer($this);
        return $this;
    }

    /**
     * @param IdentityCard $card
     * @return Customer
     */
    public function setIdentityCard(IdentityCard $card): Customer
    {
        $this->identityCard = $card;
        $this->identityCard->setCustomer($this);
        return $this;
    }

    /**
     * @param bool $forceCreate
     * @return IdentityCard|null
     */
    public function getIdentityCard(bool $forceCreate = false): ?IdentityCard
    {
        if ($forceCreate && null === $this->identityCard) {
            $this->setIdentityCard(new IdentityCard());
        }

        return $this->identityCard;
    }

    /**
     * @return CreditRequest
     */
    public function getCreditRequest(): CreditRequest
    {
        return $this->creditRequest;
    }

    /**
     * @param CreditRequest $creditRequest
     * @return Customer
     */
    public function setCreditRequest(CreditRequest $creditRequest): Customer
    {
        $this->creditRequest = $creditRequest;
        return $this;
    }
}
