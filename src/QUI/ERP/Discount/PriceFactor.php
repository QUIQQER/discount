<?php

/**
 * This file contains QUI\ERP\Discount\PriceFactor
 */

namespace QUI\ERP\Discount;

use QUI;

/**
 * Class PriceFactor
 * This price factor is used by Handler::DISCOUNT_SCOPE_TOTAL
 *
 * @package QUI\ERP\Discount
 */
class PriceFactor extends QUI\ERP\Products\Utils\PriceFactor implements
    QUI\ERP\Products\Interfaces\PriceFactorWithVatInterface
{
    /**
     * @var string
     */
    protected string $type = Handler::DISCOUNT_PRICEFACTOR_TYPE;

    /**
     * PriceFactor constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        parent::__construct($params);

        if (isset($params['vat'])) {
            $this->vat = $params['vat'];
        }
    }

    /**
     * Return the vat type of the discount price factor
     *
     * @return QUI\ERP\Tax\TaxType
     * @throws QUI\Exception
     */
    public function getVatType(): QUI\ERP\Tax\TaxType
    {
        if (!$this->vat) {
            return QUI\ERP\Tax\Utils::getShopTaxType();
        }

        $standardTax = explode(':', (string)$this->vat);

        if (!isset($standardTax[1])) {
            return QUI\ERP\Tax\Utils::getShopTaxType();
        }

        try {
            $Handler = new QUI\ERP\Tax\Handler();

            return $Handler->getTaxType($standardTax[1]);
        } catch (QUI\Exception) {
        }

        return QUI\ERP\Tax\Utils::getShopTaxType();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $attributes = parent::toArray();

        $attributes['vat'] = $this->vat;

        return $attributes;
    }
}
