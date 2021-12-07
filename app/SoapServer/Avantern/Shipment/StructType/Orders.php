<?php

declare(strict_types=1);

namespace App\SoapServer\Avantern\Shipment\StructType;

use InvalidArgumentException;
use WsdlToPhp\PackageBase\AbstractStructBase;
use App\SoapServer\Avantern\Shipment\StructType\Order;

/**
 * This class stands for orders StructType
 * Meta information extracted from the WSDL
 * - documentation: список заказов
 * @subpackage Structs
 */
class Orders extends AbstractStructBase
{
    /**
     * The order
     * Meta information extracted from the WSDL
     * - maxOccurs: unbounded
     * - minOccurs: 0
     * @var Order[]
     */
    protected ?array $order = null;
    /**
     * Constructor method for orders
     * @uses Orders::setOrder()
     * @param Order[] $order
     */
    public function __construct(?array $order = null)
    {
        $this
            ->setOrder($order);
    }
    /**
     * Get order value
     * @return Order[]
     */
    public function getOrder(): ?array
    {
        return $this->order;
    }
    /**
     * This method is responsible for validating the values passed to the setOrder method
     * This method is willingly generated in order to preserve the one-line inline validation within the setOrder method
     * @param array $values
     * @return string A non-empty message if the values does not match the validation rules
     */
    public static function validateOrderForArrayConstraintsFromSetOrder(?array $values = []): string
    {
        if (!is_array($values)) {
            return '';
        }
        $message = '';
        $invalidValues = [];
        foreach ($values as $ordersOrderItem) {
            // validation for constraint: itemType
            if (!$ordersOrderItem instanceof Order) {
                $invalidValues[] = is_object($ordersOrderItem) ? get_class($ordersOrderItem) : sprintf('%s(%s)', gettype($ordersOrderItem), var_export($ordersOrderItem, true));
            }
        }
        if (!empty($invalidValues)) {
            $message = sprintf('The order property can only contain items of type Order, %s given', is_object($invalidValues) ? get_class($invalidValues) : (is_array($invalidValues) ? implode(', ', $invalidValues) : gettype($invalidValues)));
        }
        unset($invalidValues);

        return $message;
    }
    /**
     * Set order value
     * @throws InvalidArgumentException
     * @param Order[] $order
     * @return Orders
     */
    public function setOrder(?array $order = null): self
    {
        // validation for constraint: array
        if ('' !== ($orderArrayErrorMessage = self::validateOrderForArrayConstraintsFromSetOrder($order))) {
            throw new InvalidArgumentException($orderArrayErrorMessage, __LINE__);
        }
        $this->order = $order;

        return $this;
    }
    /**
     * Add item to order value
     * @throws InvalidArgumentException
     * @param Order $item
     * @return Orders
     */
    public function addToOrder(Order $item): self
    {
        // validation for constraint: itemType
        if (!$item instanceof Order) {
            throw new InvalidArgumentException(sprintf('The order property can only contain items of type Order, %s given', is_object($item) ? get_class($item) : (is_array($item) ? implode(', ', $item) : gettype($item))), __LINE__);
        }
        $this->order[] = $item;

        return $this;
    }
}
