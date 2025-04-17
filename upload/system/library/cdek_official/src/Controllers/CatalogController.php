<?php

namespace CDEK\Controllers;

use CDEK\Actions\Catalog\Checkout\GetFrontendParamsAction;
use CDEK\Actions\Catalog\Checkout\GetOfficesAction;
use CDEK\Actions\Catalog\Checkout\CacheOfficeCodeAction;
use CDEK\Actions\Catalog\Checkout\SaveOrderMetaAction;
use CDEK\Actions\Catalog\Checkout\ValidateOfficeCodeAction;
use CDEK\Contracts\ControllerContract;
use JsonException;

class CatalogController extends ControllerContract
{
    /**
     * @throws JsonException
     */
    final public function map(): void
    {
        (new GetOfficesAction)();
    }

    /** @noinspection PhpUnused */
    final public function cacheOfficeCode(): void
    {
        (new CacheOfficeCodeAction)();
    }

    /**
     * @noinspection PhpUnused
     * @throws JsonException
     */
    final public function validateOfficeCode(): ?bool
    {
        return (new ValidateOfficeCodeAction)();
    }

    /**
     * @noinspection PhpUnused
     */
    final public function saveOfficeCode(): void
    {
        (new SaveOrderMetaAction)();
    }

    /**
     * @throws JsonException
     */
    final public function getParams(): void
    {
        (new GetFrontendParamsAction)();
    }

    /** @noinspection PhpUnused */
    final public function addCheckoutHeaderScript(string &$route, array &$data): void
    {
        $data['scripts'][] = 'catalog/view/javascript/shipping/cdek_official.js';
    }
}
