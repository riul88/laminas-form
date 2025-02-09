<?php

declare(strict_types=1);

namespace LaminasTest\Form\TestAsset;

use Laminas\Form\Element;
use Laminas\InputFilter\InputProviderInterface;
use LaminasTest\Form\TestAsset\StringToArrayFilter;

class ElementWithStringToArrayFilter extends Element implements InputProviderInterface
{
    /**
     * @return array
     */
    public function getInputSpecification()
    {
        return [
            'name'     => $this->getName(),
            'required' => true,
            'filters'  => [
                ['name' => StringToArrayFilter::class],
            ],
        ];
    }
}
