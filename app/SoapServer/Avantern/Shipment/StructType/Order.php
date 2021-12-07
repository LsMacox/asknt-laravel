<?php

declare(strict_types=1);

namespace App\SoapServer\Avantern\Shipment\StructType;

use InvalidArgumentException;
use WsdlToPhp\PackageBase\AbstractStructBase;

/**
 * This class stands for order StructType
 * Meta information extracted from the WSDL
 * - documentation: заказ
 * @subpackage Structs
 */
class Order extends AbstractStructBase
{
    /**
     * The order
     * Meta information extracted from the WSDL
     * - documentation: номер заказа
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $order = null;
    /**
     * The return
     * Meta information extracted from the WSDL
     * - documentation: (0 - "Не возврат", 1 - "Возврат" )
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $return = null;
    /**
     * The product
     * Meta information extracted from the WSDL
     * - documentation: груз
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $product = null;
    /**
     * The weight
     * Meta information extracted from the WSDL
     * - documentation: вес нетто заказа
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $weight = null;
    /**
     * Constructor method for order
     * @uses Order::setOrder()
     * @uses Order::setReturn()
     * @uses Order::setProduct()
     * @uses Order::setWeight()
     * @param string|null $order
     * @param string|null $return
     * @param string|null $product
     * @param string|null $weight
     */
    public function __construct(?string $order = null, ?string $return = null, ?string $product = null, ?string $weight = null)
    {
        $this
            ->setOrder($order)
            ->setReturn($return)
            ->setProduct($product)
            ->setWeight($weight);
    }
    /**
     * Get order value
     * @return string|null
     */
    public function getOrder(): ?string
    {
        return $this->order;
    }
    /**
     * Set order value
     * @param string|null $order
     * @return $this
     */
    public function setOrder(?string $order = null): self
    {
        // validation for constraint: string
        if (!is_null($order) && !is_string($order)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($order, true), gettype($order)), __LINE__);
        }
        $this->order = $order;

        return $this;
    }
    /**
     * Get return value
     * @return string|null
     */
    public function getReturn(): ?string
    {
        return $this->return;
    }
    /**
     * Set return value
     * @param string|null $return
     * @return $this
     */
    public function setReturn(?string $return = null): self
    {
        // validation for constraint: string
        if (!is_null($return) && !is_string($return)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($return, true), gettype($return)), __LINE__);
        }
        $this->return = $return;

        return $this;
    }
    /**
     * Get product value
     * @return string|null
     */
    public function getProduct(): ?string
    {
        return $this->product;
    }
    /**
     * Set product value
     * @param string|null $product
     * @return $this
     */
    public function setProduct(?string $product = null): self
    {
        // validation for constraint: string
        if (!is_null($product) && !is_string($product)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($product, true), gettype($product)), __LINE__);
        }
        $this->product = $product;

        return $this;
    }
    /**
     * Get weight value
     * @return string|null
     */
    public function getWeight(): ?string
    {
        return $this->weight;
    }

    /**
     * Set weight value
     * @param string|null $weight
     * @return $this
     */
    public function setWeight(?string $weight = null): self
    {
        // validation for constraint: string
        if (!is_null($weight) && !is_string($weight)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($weight, true), gettype($weight)), __LINE__);
        }
        $this->weight = $weight;

        return $this;
    }
}
