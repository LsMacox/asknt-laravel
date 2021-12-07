<?php

declare(strict_types=1);

namespace App\SoapServer\Avantern\Shipment\StructType;

use InvalidArgumentException;
use WsdlToPhp\PackageBase\AbstractStructBase;

/**
 * This class stands for stock StructType
 * Meta information extracted from the WSDL
 * - documentation: склад загрузки
 * @subpackage Structs
 */
class Stock extends AbstractStructBase
{
    /**
     * The name
     * Meta information extracted from the WSDL
     * - documentation: название склада загрузки
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $name = null;
    /**
     * The id1c
     * Meta information extracted from the WSDL
     * - documentation: id склада из 1С, должно передаваться только одно значение 1С или SAP
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $id1c = null;
    /**
     * The idsap
     * Meta information extracted from the WSDL
     * - documentation: id склада из SAP, должно передаваться только одно значение 1С или SAP
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $idsap = null;
    /**
     * Constructor method for stock
     * @uses Stock::setName()
     * @uses Stock::setId1c()
     * @uses Stock::setIdsap()
     * @param string $name
     * @param string $id1c
     * @param string $idsap
     */
    public function __construct(?string $name = null, ?string $id1c = null, ?string $idsap = null)
    {
        $this
            ->setName($name)
            ->setId1c($id1c)
            ->setIdsap($idsap);
    }
    /**
     * Get name value
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }
    /**
     * Set name value
     * @param string $name
     * @return Stock
     */
    public function setName(?string $name = null): self
    {
        // validation for constraint: string
        if (!is_null($name) && !is_string($name)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($name, true), gettype($name)), __LINE__);
        }
        $this->name = $name;

        return $this;
    }
    /**
     * Get id1c value
     * @return string|null
     */
    public function getId1c(): ?string
    {
        return $this->id1c;
    }
    /**
     * Set id1c value
     * @param string $id1c
     * @return Stock
     */
    public function setId1c(?string $id1c = null): self
    {
        // validation for constraint: string
        if (!is_null($id1c) && !is_string($id1c)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($id1c, true), gettype($id1c)), __LINE__);
        }
        $this->id1c = $id1c;

        return $this;
    }
    /**
     * Get idsap value
     * @return string|null
     */
    public function getIdsap(): ?string
    {
        return $this->idsap;
    }
    /**
     * Set idsap value
     * @param string $idsap
     * @return Stock
     */
    public function setIdsap(?string $idsap = null): self
    {
        // validation for constraint: string
        if (!is_null($idsap) && !is_string($idsap)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($idsap, true), gettype($idsap)), __LINE__);
        }
        $this->idsap = $idsap;

        return $this;
    }
}
