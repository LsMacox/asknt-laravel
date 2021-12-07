<?php

declare(strict_types=1);

namespace App\SoapServer\Avantern\Shipment\StructType;

use InvalidArgumentException;
use WsdlToPhp\PackageBase\AbstractStructBase;

use App\SoapServer\Avantern\Shipment\StructType\Waybill;

/**
 * This class stands for shipmentData StructType
 * @subpackage Structs
 */
class ShipmentData extends AbstractStructBase
{
    /**
     * The system
     * Meta information extracted from the WSDL
     * - documentation: система-отправитель
     * @var string|null
     */
    protected ?string $system = null;
    /**
     * The waybill
     * @var Waybill|null
     */
    protected ?Waybill $waybill = null;
    /**
     * Constructor method for shipmentData
     * @uses ShipmentData::setSystem()
     * @uses ShipmentData::setWaybill()
     * @param string $system
     * @param Waybill $waybill
     */
    public function __construct(?string $system = null, ?Waybill $waybill = null)
    {
        $this
            ->setSystem($system)
            ->setWaybill($waybill);
    }
    /**
     * Get system value
     * @return string|null
     */
    public function getSystem(): ?string
    {
        return $this->system;
    }
    /**
     * Set system value
     * @param string $system
     * @return ShipmentData
     */
    public function setSystem(?string $system = null): self
    {
        // validation for constraint: string
        if (!is_null($system) && !is_string($system)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($system, true), gettype($system)), __LINE__);
        }
        $this->system = $system;

        return $this;
    }
    /**
     * Get waybill value
     * @return Waybill|null
     */
    public function getWaybill(): ?Waybill
    {
        return $this->waybill;
    }
    /**
     * Set waybill value
     * @param Waybill $waybill
     * @return ShipmentData
     */
    public function setWaybill(?Waybill $waybill = null): self
    {
        $this->waybill = $waybill;

        return $this;
    }
}
