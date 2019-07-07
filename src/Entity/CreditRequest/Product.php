<?php
/**
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
declare(strict_types=1);
namespace App\Entity\CreditRequest;

use App\Entity\CreditRequest;
use App\Entity\EntityInterface;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\PhaseInterface;
use App\Validator\Constraint as AppAssert;

/**
 * @ORM\Table(name="credit_request_product")
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 *
 * @Assert\GroupSequence({"Product", PhaseInterface::OTHER})
 * @AppAssert\Product()
 */
class Product implements EntityInterface
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
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(name="category_id", type="integer", nullable=false)
     * @Assert\NotBlank(groups={PhaseInterface::OTHER})
     *
     * @Serializer\Type("int")
     * @Serializer\Groups({EntityInterface::LISTING})
     */
    protected $categoryId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     * @Assert\NotBlank(groups={PhaseInterface::OTHER})
     *
     * @Serializer\Type("string")
     * @Serializer\Groups({EntityInterface::LISTING})
     */
    protected $name;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=false)
     * @Assert\NotBlank(groups={PhaseInterface::OTHER})
     *
     * @Serializer\Type("float")
     * @Serializer\Groups({EntityInterface::LISTING})
     */
    protected $price = 0.00;

    /**
     * @var \DateTime
     *
     * @ORM\Column(
     *     type="datetime",
     *     nullable=false,
     *     name="created_at",
     *     options={"default": "CURRENT_TIMESTAMP"},
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(
     *     type="datetime",
     *     nullable=false,
     *     name="updated_at",
     *     columnDefinition="DATETIME on update CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
     * )
     */
    protected $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CreditRequest", cascade={"all"})
     * @ORM\JoinColumn(name="credit_request_id", referencedColumnName="id")
     * @Serializer\Exclude()
     * @Assert\NotBlank()
     */
    protected $creditRequest;

    public function __construct()
    {
        $this->createdAt = $this->updatedAt = new \DateTime();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Product
     */
    public function setName(string $name): Product
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return Product
     */
    public function setPrice(float $price): Product
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @param CreditRequest $creditRequest
     * @return Product
     */
    public function setCreditRequest(CreditRequest $creditRequest): Product
    {
        $this->creditRequest = $creditRequest;
        return $this;
    }

    /**
     * @return Product
     */
    public function resetCreditRequest(): Product
    {
        $this->creditRequest = null;
        return $this;
    }

    /**
     * @return CreditRequest
     */
    public function getCreditRequest(): CreditRequest
    {
        return $this->creditRequest;
    }

    /**
     * @param int $categoryId
     * @return Product
     */
    public function setCategoryId(int $categoryId): Product
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    /**
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->categoryId;
    }
}
