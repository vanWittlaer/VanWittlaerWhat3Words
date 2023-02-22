<?php declare(strict_types=1);

namespace VanWittlaer\WhatThreeWords;

use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class VanWittlaerWhatThreeWords extends Plugin
{
    public const CUSTOM_FIELD_SET_ID = '99651ebfc1584250b5faf5f08bbb2ea8';

    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);
        $this->createCustomFields($installContext->getContext());
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);
        if ($uninstallContext->keepUserData()) {
            return;
        }
        $this->removeCustomFields($uninstallContext->getContext());
    }

    private function createCustomFields(Context $context): void
    {
        if ($this->customFieldSetExists($context)) {
            return;
        }

        $customFieldSetRepository = $this->container->get('custom_field_set.repository');
        $customFieldSetRepository->create([
            [
                'id' => self::CUSTOM_FIELD_SET_ID,
                'name' => 'what3words_address_set',
                'global' => true, // set this to true to prevent accidental editing in admin
                'config' => [
                    'label' => [
                        'en-GB' => '///what3words',
                        'de-DE' => '///what3words',
                    ],
                ],
                'customFields' => [
                    [
                        'name' => 'what3words',
                        'type' => CustomFieldTypes::TEXT,
                        'allowCustomerWrite' => true,
                        'config' => [
                            'label' => [
                                'en-GB' => 'what3words Address',
                                'de-DE' => 'what3words-Adresse',
                            ],
                            'customFieldPosition' => 1,
                        ],
                    ],
                ],
                'relations' => [
                    [
                        'entityName' => CustomerAddressDefinition::ENTITY_NAME,
                    ],
                    [
                        'entityName' => OrderAddressDefinition::ENTITY_NAME,
                    ],
                ],
            ],
        ], $context);
    }

    private function removeCustomFields(Context $context): void
    {
        if (!$this->customFieldSetExists($context)) {
            return;
        }

        $customFieldSetRepository = $this->container->get('custom_field_set.repository');
        $customFieldSetRepository->delete([
            ['id' => self::CUSTOM_FIELD_SET_ID],
        ], $context);
    }

    private function customFieldSetExists(Context $context): bool
    {
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        return ($customFieldSetRepository->search(new Criteria([self::CUSTOM_FIELD_SET_ID]), $context)->getTotal() > 0);
    }
}
