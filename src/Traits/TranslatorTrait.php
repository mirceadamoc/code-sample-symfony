<?php
/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Traits;


use Symfony\Contracts\Translation\TranslatorInterface;

trait TranslatorTrait
{
    /** @var TranslatorInterface */
    protected $translator;

    /**
     * Setter method for translator property.
     *
     * @required
     * @param TranslatorInterface $translator
     * @return TranslatorTrait
     */
    public function setTranslator(TranslatorInterface $translator): self
    {
        $this->translator = $translator;

        return $this;
    }

    /**
     * Getter method for translator property.
     *
     * @@return TranslatorInterface|null
     */
    public function getTranslator(): ?TranslatorInterface
    {
        return $this->translator;
    }
}