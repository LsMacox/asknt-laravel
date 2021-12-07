<?php

declare(strict_types=1);

namespace App\SoapServer\Avantern\Shipment\StructType;

use InvalidArgumentException;
use WsdlToPhp\PackageBase\AbstractStructBase;
use App\SoapServer\Avantern\Shipment\StructType\Score;

/**
 * This class stands for scores StructType
 * Meta information extracted from the WSDL
 * - documentation: список торговых точек
 * @subpackage Structs
 */
class Scores extends AbstractStructBase
{
    /**
     * The score
     * Meta information extracted from the WSDL
     * - maxOccurs: unbounded
     * - minOccurs: 0
     * @var Score[]
     */
    protected ?array $score = null;
    /**
     * Constructor method for scores
     * @uses Scores::setScore()
     * @param Score[] $score
     */
    public function __construct(?array $score = null)
    {
        $this
            ->setScore($score);
    }
    /**
     * Get score value
     * @return Score[]
     */
    public function getScore(): ?array
    {
        return $this->score;
    }
    /**
     * This method is responsible for validating the values passed to the setScore method
     * This method is willingly generated in order to preserve the one-line inline validation within the setScore method
     * @param array $values
     * @return string A non-empty message if the values does not match the validation rules
     */
    public static function validateScoreForArrayConstraintsFromSetScore(?array $values = []): string
    {
        if (!is_array($values)) {
            return '';
        }
        $message = '';
        $invalidValues = [];
        foreach ($values as $scoresScoreItem) {
            // validation for constraint: itemType
            if (!$scoresScoreItem instanceof Score) {
                $invalidValues[] = is_object($scoresScoreItem) ? get_class($scoresScoreItem) : sprintf('%s(%s)', gettype($scoresScoreItem), var_export($scoresScoreItem, true));
            }
        }
        if (!empty($invalidValues)) {
            $message = sprintf('The score property can only contain items of type Score, %s given', is_object($invalidValues) ? get_class($invalidValues) : (is_array($invalidValues) ? implode(', ', $invalidValues) : gettype($invalidValues)));
        }
        unset($invalidValues);

        return $message;
    }
    /**
     * Set score value
     * @throws InvalidArgumentException
     * @param Score[] $score
     * @return Scores
     */
    public function setScore(?array $score = null): self
    {
        // validation for constraint: array
        if ('' !== ($scoreArrayErrorMessage = self::validateScoreForArrayConstraintsFromSetScore($score))) {
            throw new InvalidArgumentException($scoreArrayErrorMessage, __LINE__);
        }
        $this->score = $score;

        return $this;
    }
    /**
     * Add item to score value
     * @throws InvalidArgumentException
     * @param Score $item
     * @return Scores
     */
    public function addToScore(Score $item): self
    {
        // validation for constraint: itemType
        if (!$item instanceof Score) {
            throw new InvalidArgumentException(sprintf('The score property can only contain items of type Score, %s given', is_object($item) ? get_class($item) : (is_array($item) ? implode(', ', $item) : gettype($item))), __LINE__);
        }
        $this->score[] = $item;

        return $this;
    }
}
