<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Block\Adminhtml\System\Config\Form;

class Info extends \Magefan\Community\Block\Adminhtml\System\Config\Form\Info
{
    /**
     * Return extension url
     *
     * @return string
     */
    protected function getModuleUrl()
    {
        return 'https://mage' . 'fan.com/';
    }

    /**
     * Return extension title
     *
     * @return string
     */
    protected function getModuleTitle()
    {
        return 'Security';
    }
}
