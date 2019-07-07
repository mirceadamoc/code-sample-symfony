<?php
declare(strict_types=1);
/**
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace App\Entity;


use App\Entity\CreditRequest\Product;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use App\Dto\ExternalDependency;

/**
 * Credit request
 *
 * @ORM\Table(name="credit_request", indexes={
 *     @ORM\Index(name="idx_type_status_created_by", columns={"type", "status", "created_by"}),
 *     @ORM\Index(name="idx_status", columns={"status"})
 * })
 * @ORM\Entity(repositoryClass="App\Repository\CreditRequestRepository")
 */
class CreditRequest implements TrackableInterface, EntityInterface
{

    public const STATUS_NEW = 'new';                        // credit request is created
    public const STATUS_IN_PROGRESS = 'in_progress';        // until request to BC was sent
    public const STATUS_REJECTED = 'rejected';              // only if all
    public const STATUS_APPROVED = 'approved';              // if at least one FI approved the request
    public const STATUS_SIGNED = 'signed';                  // contract signed, uploaded and sent to FI
    public const STATUS_DONE = 'done';                      // manual status - the credit request is closed
    public const STATUS_CANCELLED = 'canceled';             // manual status - see the attached for transitions
    public const STATUS_NMI = 'nmi';                        // manual status - Need More Info status

    const TRANSITIONS = [
        self::STATUS_NEW                => 'to_new',
        self::STATUS_IN_PROGRESS        => 'to_in_progress',
        self::STATUS_REJECTED           => 'to_rejected',
        self::STATUS_APPROVED           => 'to_approved',
        self::STATUS_SIGNED             => 'to_signed',
        self::STATUS_DONE               => 'to_done',
        self::STATUS_CANCELLED          => 'to_cancelled',
        self::STATUS_NMI                => 'to_nmi'
    ];

    const STATUSES = [
        self::STATUS_NEW                => 'New',
        self::STATUS_IN_PROGRESS        => 'In progress',
        self::STATUS_REJECTED           => 'Rejected',
        self::STATUS_APPROVED           => 'Approved',
        self::STATUS_SIGNED             => 'Signed',
        self::STATUS_DONE               => 'Done',
        self::STATUS_CANCELLED          => 'Cancelled',
        self::STATUS_NMI                => 'Need More Info'
    ];

    const CANCEL_STATUSES = [
        self::STATUS_NEW,
        self::STATUS_IN_PROGRESS,
        self::STATUS_APPROVED,
        self::STATUS_SIGNED,
        self::STATUS_NMI
    ];

    const TYPE_BUY_GOODS = 1;
    const TYPE_CREDIT = 2;

    /**
     * Represents the credit request generated in the flow of a request of patch / create
     * @var int
     *
     * @Serializer\Exclude()
     */
    public static $ID;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @Serializer\Type("int")
     * @Serializer\Groups({EntityInterface::LISTING, EntityInterface::CREATE, EntityInterface::UPDATE})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=50, nullable=false)
     *
     * @Assert\NotBlank(groups={PhaseInterface::PERSONAL_DATA, PhaseInterface::STATUS})
     * @Assert\Choice(callback="getStatuses", groups={PhaseInterface::PERSONAL_DATA, PhaseInterface::STATUS})
     *
     * @Serializer\Type("string")
     * @Serializer\Groups({EntityInterface::LISTING, EntityInterface::CREATE, EntityInterface::UPDATE})
     */
    protected $status = self::STATUS_NEW;

    /**
     * @var string
     *
     * @ORM\Column(name="created_by", type="string", nullable=false)
     *
     * @Assert\NotBlank(groups={PhaseInterface::PERSONAL_DATA})
     *
     * @Serializer\Exclude()
     */
    protected $createdBy;

    /**
     * @var string
     *
     * @ORM\Column(name="assigned_to", type="string", nullable=false)
     *
     * @Assert\NotBlank(groups={PhaseInterface::PERSONAL_DATA})
     *
     * @Serializer\Exclude()
     */
    protected $assignedTo;

    /**
     * @var Customer|null
     *
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Customer",
     *     cascade={"persist"},
     *     fetch="LAZY"
     * )
     *
     * @Assert\Valid(groups={
     *     PhaseInterface::PERSONAL_DATA,
     *     PhaseInterface::FINANCIAL_DOCUMENTS,
     *     PhaseInterface::OTHER,
     *     PhaseInterface::ANAF
     * })
     * @Serializer\Type("App\Entity\Customer")
     * @Serializer\Groups({EntityInterface::LISTING})
     * @Serializer\Accessor(setter="setCustomer")
     * @Assert\Valid()
     */
    protected $customer;

    /**
     * @var string
     *
     * @ORM\Column(name="order_no", type="string", length=255, nullable=true)
     *
     * @Assert\Length(
     *     max = 255,
     *     maxMessage="""orderNo"": This value is too long. It should have {{ limit }} character or less."
     * )
     *
     * @Serializer\Type("string")
     * @Serializer\Groups({EntityInterface::LISTING, EntityInterface::CREATE, EntityInterface::UPDATE})
     */
    protected $orderNo;

    /**
     * @var CreditRequestContract
     *
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\CreditRequestContract",
     *     fetch="LAZY",
     *     mappedBy="creditRequest",
     *     cascade={"persist"}
     * )
     */
    protected $contract;

    /**
     * @var \DateTime
     * @ORM\Column(
     *     type="datetime",
     *     nullable=true,
     *     name="created_at",
     *     options={"default": "CURRENT_TIMESTAMP"},
     *     columnDefinition="DATETIME on update CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
     * )
     * @Serializer\Groups({EntityInterface::LISTING, EntityInterface::GET})
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(
     *     type="datetime",
     *     nullable=true,
     *     name="updated_at",
     *     options={"default": "CURRENT_TIMESTAMP"},
     *     columnDefinition="DATETIME on update CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
     * )
     * @Serializer\Groups({EntityInterface::LISTING, EntityInterface::GET})
     */
    protected $updatedAt;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\CreditRequest\Product",
     *     fetch="LAZY",
     *     mappedBy="creditRequest",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     * @Serializer\Type("ArrayCollection<App\Entity\CreditRequest\Product>")
     * @CreditTypeProduct(groups={PhaseInterface::OTHER})
     * @Assert\Valid(groups={PhaseInterface::OTHER})
     */
    protected $products;

    /**
     * @var string
     * @Serializer\Type(name="string")
     */
    protected $productCode;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->createdAt = $this->updatedAt = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function hasCustomer(): bool
    {
        return null !== $this->customer;
    }

    /**
     * @param bool $forceCreate
     * @return Customer|null
     */
    public function getCustomer(bool $forceCreate = false): ?Customer
    {
        if ($forceCreate && !$this->hasCustomer()) {
            $this->setCustomer(new Customer());
        }

        return $this->customer;
    }

    /**
     * @param Customer $customer
     * @return CreditRequest
     */
    public function setCustomer(Customer $customer): CreditRequest
    {
        $this->customer = $customer;
        $customer->setCreditRequest($this);
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
     * @param \DateTime $createdAt
     * @return CreditRequest
     */
    public function setCreatedAt(\DateTime $createdAt): CreditRequest
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return CreditRequest
     */
    public function setUpdatedAt(\DateTime $updatedAt): CreditRequest
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return array
     */
    public static function getStatuses(): array
    {
        return array_flip(self::STATUSES);
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return CreditRequest
     */
    public function setStatus(string $status): CreditRequest
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedBy(): string
    {
        return $this->createdBy;
    }

    /**
     * @param string $createdBy
     * @return CreditRequest
     */
    public function setCreatedBy(string $createdBy): CreditRequest
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    /**
     * @return string
     */
    public function getAssignedTo(): string
    {
        return $this->assignedTo;
    }

    /**
     * @param string $assignedTo
     * @return CreditRequest
     */
    public function setAssignedTo(string $assignedTo): CreditRequest
    {
        $this->assignedTo = $assignedTo;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderNo(): string
    {
        return $this->orderNo;
    }

    /**
     * @param string $orderNo
     * @return CreditRequest
     */
    public function setOrderNo(string $orderNo): CreditRequest
    {
        $this->orderNo = $orderNo;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    /**
     * @param bool $forceCreate
     * @return ExternalDependency|null
     */
    public function getExternalDependency(bool $forceCreate = false): ?ExternalDependency
    {
        if ($forceCreate && null === $this->externalDependency) {
            $this->externalDependency = new ExternalDependency();
        }

        return $this->externalDependency;
    }

    /**
     * @return CreditRequestContract
     */
    public function getContract(): CreditRequestContract
    {
        return $this->contract;
    }

    /**
     * @param CreditRequestContract $contract
     * @return CreditRequest
     */
    public function setContract(CreditRequestContract $contract): CreditRequest
    {
        $this->contract = $contract;
        return $this;
    }

    public function reset(): void
    {
        $this->id = null;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return '';
    }

    public function toSimulator(): void
    {
        $this->id = null;
    }

    public function autoAdvanceStatus(): void
    {
        if (static::STATUS_NEW == $this->status) {
            $this->status = static::STATUS_IN_PROGRESS;
        }
    }

    /**
     * @param Product $product
     * @return CreditRequest
     */
    public function addProducts(Product $product): CreditRequest
    {
        if (!$this->products->contains($product)) {
            if (!$this->products->isDirty()) {
                /** @var Product $value */
                foreach ($this->products->getValues() as $oldProduct) {
                    $this->products->removeElement($oldProduct);
                    $oldProduct->resetCreditRequest();
                }
            }
            $product->setCreditRequest($this);
            $this->products[] = $product;
        }

        return $this;
    }

    /**
     * @param string $productCode
     * @return CreditRequest
     */
    public function setProductCode(string $productCode): CreditRequest
    {
        $this->productCode = $productCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getProductCode(): string
    {
        return $this->productCode;
    }
}
