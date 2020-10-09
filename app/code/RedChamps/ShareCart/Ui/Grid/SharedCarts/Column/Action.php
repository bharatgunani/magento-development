<?php
namespace RedChamps\ShareCart\Ui\Grid\SharedCarts\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Action extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item['status'] == 'Available') {
                    $item[$this->getData('name')]['view'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'share_cart/action_sharedCarts/redirect',
                            ['unique_id'=>$item['unique_id'], 'store_id' => 1]
                        ),
                        'label' => __('View Cart'),
                        'target' => '_blank',
                        'hidden' => false,
                        '__disableTmpl' => true
                    ];
                    $item[$this->getData('name')]['create_order'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'sales/order_create/sharedCart',
                            ['unique_id'=>$item['unique_id'], 'store_id' => 1]
                        ),
                        'label' => __('Create Order'),
                        'target' => '_blank',
                        'hidden' => false,
                        '__disableTmpl' => true
                    ];
                }
            }
        }

        return $dataSource;
    }
}
