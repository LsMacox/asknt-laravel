<?php

declare(strict_types=1);

namespace App\SoapServer\Avantern\Shipment\StructType;

use InvalidArgumentException;
use WsdlToPhp\PackageBase\AbstractStructBase;
use App\SoapServer\Avantern\Shipment\StructType\Temperature;
use App\SoapServer\Avantern\Shipment\StructType\Scores;
use App\SoapServer\Avantern\Shipment\StructType\Stock;

/**
 * This class stands for waybill StructType
 * @subpackage Structs
 */
class Waybill extends AbstractStructBase
{
    /**
     * The number
     * Meta information extracted from the WSDL
     * - documentation: номер маршрутного листа
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $number = null;
    /**
     * The status
     * Meta information extracted from the WSDL
     * - documentation: статус маршрутного листа
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $status = null;
    /**
     * The timestamp
     * Meta information extracted from the WSDL
     * - documentation: TIMESTAMP(версия отправки документа)
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $timestamp = null;
    /**
     * The date
     * Meta information extracted from the WSDL
     * - documentation: дата исполнения маршрутного листа
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $date = null;
    /**
     * The time
     * Meta information extracted from the WSDL
     * - documentation: время начала исполнения маршрутного листа
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $time = null;
    /**
     * The carrier
     * Meta information extracted from the WSDL
     * - documentation: Название перевозчика
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $carrier = null;
    /**
     * The car
     * Meta information extracted from the WSDL
     * - documentation: Номер машины
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $car = null;
    /**
     * The trailer
     * Meta information extracted from the WSDL
     * - documentation: Номер прицепа
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $trailer = null;
    /**
     * The weight
     * Meta information extracted from the WSDL
     * - documentation: Грузоподъемность
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $weight = null;
    /**
     * The mark
     * Meta information extracted from the WSDL
     * - documentation: Метка 0-Собственный/1-Наемный
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $mark = null;
    /**
     * The driver
     * Meta information extracted from the WSDL
     * - documentation: ФИО водителя
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $driver = null;
    /**
     * The phone
     * Meta information extracted from the WSDL
     * - documentation: номер телефона
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $phone = null;
    /**
     * The temperature
     * Meta information extracted from the WSDL
     * - minOccurs: 0
     * @var Temperature|null
     */
    protected ?Temperature $temperature = null;
    /**
     * The stock
     * Meta information extracted from the WSDL
     * - minOccurs: 0
     * @var Stock|null
     */
    protected ?Stock $stock = null;
    /**
     * The scores
     * Meta information extracted from the WSDL
     * - minOccurs: 0
     * @var Scores|null
     */
    protected ?Scores $scores = null;
    /**
     * Constructor method for waybill
     * @uses Waybill::setNumber()
     * @uses Waybill::setStatus()
     * @uses Waybill::setTimestamp()
     * @uses Waybill::setDate()
     * @uses Waybill::setTime()
     * @uses Waybill::setCarrier()
     * @uses Waybill::setCar()
     * @uses Waybill::setTrailer()
     * @uses Waybill::setWeight()
     * @uses Waybill::setMark()
     * @uses Waybill::setDriver()
     * @uses Waybill::setPhone()
     * @uses Waybill::setTemperature()
     * @uses Waybill::setStock()
     * @uses Waybill::setScores()
     * @param string $number
     * @param string $status
     * @param string $timestamp
     * @param string $date
     * @param string $time
     * @param string $carrier
     * @param string $car
     * @param string $trailer
     * @param string $weight
     * @param string $mark
     * @param string $driver
     * @param string $phone
     * @param Temperature $temperature
     * @param Stock $stock
     * @param Scores $scores
     */
    public function __construct(?string $number = null, ?string $status = null, ?string $timestamp = null, ?string $date = null, ?string $time = null, ?string $carrier = null, ?string $car = null, ?string $trailer = null, ?string $weight = null, ?string $mark = null, ?string $driver = null, ?string $phone = null, ?Temperature $temperature = null, ?Stock $stock = null, ?Scores $scores = null)
    {
        $this
            ->setNumber($number)
            ->setStatus($status)
            ->setTimestamp($timestamp)
            ->setDate($date)
            ->setTime($time)
            ->setCarrier($carrier)
            ->setCar($car)
            ->setTrailer($trailer)
            ->setWeight($weight)
            ->setMark($mark)
            ->setDriver($driver)
            ->setPhone($phone)
            ->setTemperature($temperature)
            ->setStock($stock)
            ->setScores($scores);
    }
    /**
     * Get number value
     * @return string|null
     */
    public function getNumber(): ?string
    {
        return $this->number;
    }
    /**
     * Set number value
     * @param string $number
     * @return Waybill
     */
    public function setNumber(?string $number = null): self
    {
        // validation for constraint: string
        if (!is_null($number) && !is_string($number)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($number, true), gettype($number)), __LINE__);
        }
        $this->number = $number;

        return $this;
    }
    /**
     * Get status value
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }
    /**
     * Set status value
     * @param string $status
     * @return Waybill
     */
    public function setStatus(?string $status = null): self
    {
        // validation for constraint: string
        if (!is_null($status) && !is_string($status)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($status, true), gettype($status)), __LINE__);
        }
        $this->status = $status;

        return $this;
    }
    /**
     * Get timestamp value
     * @return string|null
     */
    public function getTimestamp(): ?string
    {
        return $this->timestamp;
    }
    /**
     * Set timestamp value
     * @param string $timestamp
     * @return Waybill
     */
    public function setTimestamp(?string $timestamp = null): self
    {
        // validation for constraint: string
        if (!is_null($timestamp) && !is_string($timestamp)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($timestamp, true), gettype($timestamp)), __LINE__);
        }
        $this->timestamp = $timestamp;

        return $this;
    }
    /**
     * Get date value
     * @return string|null
     */
    public function getDate(): ?string
    {
        return $this->date;
    }
    /**
     * Set date value
     * @param string $date
     * @return Waybill
     */
    public function setDate(?string $date = null): self
    {
        // validation for constraint: string
        if (!is_null($date) && !is_string($date)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($date, true), gettype($date)), __LINE__);
        }
        $this->date = $date;

        return $this;
    }
    /**
     * Get time value
     * @return string|null
     */
    public function getTime(): ?string
    {
        return $this->time;
    }
    /**
     * Set time value
     * @param string $time
     * @return Waybill
     */
    public function setTime(?string $time = null): self
    {
        // validation for constraint: string
        if (!is_null($time) && !is_string($time)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($time, true), gettype($time)), __LINE__);
        }
        $this->time = $time;

        return $this;
    }
    /**
     * Get carrier value
     * @return string|null
     */
    public function getCarrier(): ?string
    {
        return $this->carrier;
    }
    /**
     * Set carrier value
     * @param string $carrier
     * @return Waybill
     */
    public function setCarrier(?string $carrier = null): self
    {
        // validation for constraint: string
        if (!is_null($carrier) && !is_string($carrier)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($carrier, true), gettype($carrier)), __LINE__);
        }
        $this->carrier = $carrier;

        return $this;
    }
    /**
     * Get car value
     * @return string|null
     */
    public function getCar(): ?string
    {
        return $this->car;
    }
    /**
     * Set car value
     * @param string $car
     * @return Waybill
     */
    public function setCar(?string $car = null): self
    {
        // validation for constraint: string
        if (!is_null($car) && !is_string($car)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($car, true), gettype($car)), __LINE__);
        }
        $this->car = $car;

        return $this;
    }
    /**
     * Get trailer value
     * @return string|null
     */
    public function getTrailer(): ?string
    {
        return $this->trailer;
    }
    /**
     * Set trailer value
     * @param string $trailer
     * @return Waybill
     */
    public function setTrailer(?string $trailer = null): self
    {
        // validation for constraint: string
        if (!is_null($trailer) && !is_string($trailer)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($trailer, true), gettype($trailer)), __LINE__);
        }
        $this->trailer = $trailer;

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
     * @param string $weight
     * @return Waybill
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
    /**
     * Get mark value
     * @return string|null
     */
    public function getMark(): ?string
    {
        return $this->mark;
    }
    /**
     * Set mark value
     * @param string $mark
     * @return Waybill
     */
    public function setMark(?string $mark = null): self
    {
        // validation for constraint: string
        if (!is_null($mark) && !is_string($mark)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($mark, true), gettype($mark)), __LINE__);
        }
        $this->mark = $mark;

        return $this;
    }
    /**
     * Get driver value
     * @return string|null
     */
    public function getDriver(): ?string
    {
        return $this->driver;
    }
    /**
     * Set driver value
     * @param string $driver
     * @return Waybill
     */
    public function setDriver(?string $driver = null): self
    {
        // validation for constraint: string
        if (!is_null($driver) && !is_string($driver)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($driver, true), gettype($driver)), __LINE__);
        }
        $this->driver = $driver;

        return $this;
    }
    /**
     * Get phone value
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }
    /**
     * Set phone value
     * @param string $phone
     * @return Waybill
     */
    public function setPhone(?string $phone = null): self
    {
        // validation for constraint: string
        if (!is_null($phone) && !is_string($phone)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($phone, true), gettype($phone)), __LINE__);
        }
        $this->phone = $phone;

        return $this;
    }
    /**
     * Get temperature value
     * @return Temperature|null
     */
    public function getTemperature(): ?Temperature
    {
        return $this->temperature;
    }
    /**
     * Set temperature value
     * @param Temperature $temperature
     * @return Waybill
     */
    public function setTemperature(?Temperature $temperature = null): self
    {
        $this->temperature = $temperature;

        return $this;
    }
    /**
     * Get stock value
     * @return Stock|null
     */
    public function getStock(): ?Stock
    {
        return $this->stock;
    }
    /**
     * Set stock value
     * @param Stock $stock
     * @return Waybill
     */
    public function setStock(?Stock $stock = null): self
    {
        $this->stock = $stock;

        return $this;
    }
    /**
     * Get scores value
     * @return Scores|null
     */
    public function getScores(): ?Scores
    {
        return $this->scores;
    }
    /**
     * Set scores value
     * @param Scores $scores
     * @return Waybill
     */
    public function setScores(?Scores $scores = null): self
    {
        $this->scores = $scores;

        return $this;
    }
}
