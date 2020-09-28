<?php
namespace Srp\CategoryAttribute\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

class InstallData implements InstallDataInterface
{

	private $eavSetupFactory;

	public function __construct(EavSetupFactory $eavSetupFactory)
	{
		$this->eavSetupFactory = $eavSetupFactory;
	}

	public function install(
		ModuleDataSetupInterface $setup,
		ModuleContextInterface $context
	)
	{
		$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

		$eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'is_landing', [
	        'type'     => 'int',
	        'label'    => 'Is Home Category',
	        'input'    => 'boolean',
	        'source'   => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
	        'visible'  => true,
	        'default'  => '0',
	        'required' => false,
	        'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
	        'group'    => 'Display Settings',
	    ]);

	    $eavSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'show_landing', [
	        'type'     => 'int',
	        'label'    => 'Show Landing',
	        'input'    => 'boolean',
	        'source'   => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
	        'visible'  => true,
	        'default'  => '0',
	        'required' => false,
	        'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
	        'group'    => 'Display Settings',
	    ]);
    }
}
