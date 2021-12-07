<?php

declare(strict_types=1);

namespace App\SoapServer\Avantern\Shipment\StructType;

use InvalidArgumentException;
use WsdlToPhp\PackageBase\AbstractStructBase;
use App\SoapServer\Avantern\Shipment\StructType\Orders;

/**
 * This class stands for score StructType
 * Meta information extracted from the WSDL
 * - documentation: точка
 * @subpackage Structs
 */
class Score extends AbstractStructBase
{
    /**
     * The score
     * Meta information extracted from the WSDL
     * - documentation: код торговой точки
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $score = null;
    /**
     * The name
     * Meta information extracted from the WSDL
     * - documentation: вывеска
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $name = null;
    /**
     * The legal_name
     * Meta information extracted from the WSDL
     * - documentation: юридическое название
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $legal_name = null;
    /**
     * The adres
     * Meta information extracted from the WSDL
     * - documentation: адрес торговой точки
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $adres = null;
    /**
     * The long
     * Meta information extracted from the WSDL
     * - documentation: долгота
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $long = null;
    /**
     * The lat
     * Meta information extracted from the WSDL
     * - documentation: широта
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $lat = null;
    /**
     * The date
     * Meta information extracted from the WSDL
     * - documentation: плановая дата поставки
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $date = null;
    /**
     * The arrive_from
     * Meta information extracted from the WSDL
     * - documentation: плановое время прибытия с
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $arrive_from = null;
    /**
     * The arrive_to
     * Meta information extracted from the WSDL
     * - documentation: плановое время прибытия по
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $arrive_to = null;
    /**
     * The turn
     * Meta information extracted from the WSDL
     * - documentation: номер в очереде посещения
     * - minOccurs: 0
     * @var string|null
     */
    protected ?string $turn = null;
    /**
     * The orders
     * Meta information extracted from the WSDL
     * - minOccurs: 0
     * @var Orders|null
     */
    protected ?Orders $orders = null;
    /**
     * Constructor method for score
     * @uses Score::setScore()
     * @uses Score::setName()
     * @uses Score::setLegal_name()
     * @uses Score::setAdres()
     * @uses Score::setLong()
     * @uses Score::setLat()
     * @uses Score::setDate()
     * @uses Score::setArrive_from()
     * @uses Score::setArrive_to()
     * @uses Score::setTurn()
     * @uses Score::setOrders()
     * @param string $score
     * @param string $name
     * @param string $legal_name
     * @param string $adres
     * @param string $long
     * @param string $lat
     * @param string $date
     * @param string $arrive_from
     * @param string $arrive_to
     * @param string $turn
     * @param Orders $orders
     */
    public function __construct(?string $score = null, ?string $name = null, ?string $legal_name = null, ?string $adres = null, ?string $long = null, ?string $lat = null, ?string $date = null, ?string $arrive_from = null, ?string $arrive_to = null, ?string $turn = null, ?\StructType\Orders $orders = null)
    {
        $this
            ->setScore($score)
            ->setName($name)
            ->setLegal_name($legal_name)
            ->setAdres($adres)
            ->setLong($long)
            ->setLat($lat)
            ->setDate($date)
            ->setArrive_from($arrive_from)
            ->setArrive_to($arrive_to)
            ->setTurn($turn)
            ->setOrders($orders);
    }
    /**
     * Get score value
     * @return string|null
     */
    public function getScore(): ?string
    {
        return $this->score;
    }
    /**
     * Set score value
     * @param string $score
     * @return Score
     */
    public function setScore(?string $score = null): self
    {
        // validation for constraint: string
        if (!is_null($score) && !is_string($score)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($score, true), gettype($score)), __LINE__);
        }
        $this->score = $score;

        return $this;
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
     * @return Score
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
     * Get legal_name value
     * @return string|null
     */
    public function getLegal_name(): ?string
    {
        return $this->legal_name;
    }
    /**
     * Set legal_name value
     * @param string $legal_name
     * @return Score
     */
    public function setLegal_name(?string $legal_name = null): self
    {
        // validation for constraint: string
        if (!is_null($legal_name) && !is_string($legal_name)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($legal_name, true), gettype($legal_name)), __LINE__);
        }
        $this->legal_name = $legal_name;

        return $this;
    }
    /**
     * Get adres value
     * @return string|null
     */
    public function getAdres(): ?string
    {
        return $this->adres;
    }
    /**
     * Set adres value
     * @param string $adres
     * @return Score
     */
    public function setAdres(?string $adres = null): self
    {
        // validation for constraint: string
        if (!is_null($adres) && !is_string($adres)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($adres, true), gettype($adres)), __LINE__);
        }
        $this->adres = $adres;

        return $this;
    }
    /**
     * Get long value
     * @return string|null
     */
    public function getLong(): ?string
    {
        return $this->long;
    }
    /**
     * Set long value
     * @param string $long
     * @return Score
     */
    public function setLong(?string $long = null): self
    {
        // validation for constraint: string
        if (!is_null($long) && !is_string($long)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($long, true), gettype($long)), __LINE__);
        }
        $this->long = $long;

        return $this;
    }
    /**
     * Get lat value
     * @return string|null
     */
    public function getLat(): ?string
    {
        return $this->lat;
    }
    /**
     * Set lat value
     * @param string $lat
     * @return Score
     */
    public function setLat(?string $lat = null): self
    {
        // validation for constraint: string
        if (!is_null($lat) && !is_string($lat)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($lat, true), gettype($lat)), __LINE__);
        }
        $this->lat = $lat;

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
     * @return Score
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
     * Get arrive_from value
     * @return string|null
     */
    public function getArrive_from(): ?string
    {
        return $this->arrive_from;
    }
    /**
     * Set arrive_from value
     * @param string $arrive_from
     * @return Score
     */
    public function setArrive_from(?string $arrive_from = null): self
    {
        // validation for constraint: string
        if (!is_null($arrive_from) && !is_string($arrive_from)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($arrive_from, true), gettype($arrive_from)), __LINE__);
        }
        $this->arrive_from = $arrive_from;

        return $this;
    }
    /**
     * Get arrive_to value
     * @return string|null
     */
    public function getArrive_to(): ?string
    {
        return $this->arrive_to;
    }
    /**
     * Set arrive_to value
     * @param string $arrive_to
     * @return Score
     */
    public function setArrive_to(?string $arrive_to = null): self
    {
        // validation for constraint: string
        if (!is_null($arrive_to) && !is_string($arrive_to)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($arrive_to, true), gettype($arrive_to)), __LINE__);
        }
        $this->arrive_to = $arrive_to;

        return $this;
    }
    /**
     * Get turn value
     * @return string|null
     */
    public function getTurn(): ?string
    {
        return $this->turn;
    }
    /**
     * Set turn value
     * @param string $turn
     * @return Score
     */
    public function setTurn(?string $turn = null): self
    {
        // validation for constraint: string
        if (!is_null($turn) && !is_string($turn)) {
            throw new InvalidArgumentException(sprintf('Invalid value %s, please provide a string, %s given', var_export($turn, true), gettype($turn)), __LINE__);
        }
        $this->turn = $turn;

        return $this;
    }
    /**
     * Get orders value
     * @return Orders|null
     */
    public function getOrders(): ?Orders
    {
        return $this->orders;
    }
    /**
     * Set orders value
     * @param Orders $orders
     * @return Score
     */
    public function setOrders(?Orders $orders = null): self
    {
        $this->orders = $orders;

        return $this;
    }
}
