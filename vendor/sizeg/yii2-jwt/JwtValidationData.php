<?php

namespace sizeg\jwt;

use Lcobucci\JWT\ValidationData;
use yii\base\Component;

/**
 * Class JwtValidationData
 *
 * @author SiZE <sizemail@gmail.com>
 */
class JwtValidationData extends Component
{

    /**
     * @var int|null Current time
     */
    public $currentTime = null;

    /**
     * @var int The leeway (in seconds) to use when validating time claims
     */
    public $leeway = 0;

    /**
     * @var ValidationData
     */
    protected $validationData;

    /**
     * ValidationData constructor.
     * @param ValidationData $validationData
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->validationData = new ValidationData($this->currentTime, $this->leeway);
        parent::__construct($config);
    }

    /**
     * @return ValidationData
     */
    public function getValidationData()
    {
        return $this->validationData;
    }
}