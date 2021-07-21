<?php declare(strict_types=1);

namespace Melv\CompanyCustomerGroup\Subscriber;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\Framework\Event\DataMappingEvent;
use Shopware\Core\Checkout\Customer\CustomerEvents;

class MappingRegisterCustomer implements EventSubscriberInterface {

    /**
     * @var EntityRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    public function __construct(
        EntityRepositoryInterface $customerRepository,
        SystemConfigService $systemConfigService
    ) {
        $this->customerRepository = $customerRepository;
        $this->systemConfigService = $systemConfigService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CustomerEvents::MAPPING_REGISTER_CUSTOMER => 'setCompanyCustomerGroup',
        ];
    }

    public function setCompanyCustomerGroup(DataMappingEvent $event): bool
    {
        $salesChannelId = $event->getContext()->getSource()->getSalesChannelId();
        $companyCustomerGroup = $this->systemConfigService->get('MelvCompanyCustomerGroup.config.companyCustomerGroup', $salesChannelId);

        if (!$companyCustomerGroup) {
            return true;
        }

        $inputData = $event->getInput();
        $outputData = $event->getOutput();

        if ($inputData->get('accountType') == 'business') {
            $outputData['groupId'] = $companyCustomerGroup;
        }

        $event->setOutput($outputData);
        return true;
    }
}
