<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Block\Adminhtml\System\Config\Form;

class InfoExtra extends InfoPlan
{

    /**
     * Get min plan
     *
     * @return string
     */
    protected function getMinPlan(): string
    {
        return 'Extra';
    }

    /**
     * Get section json
     *
     * @return string
     */
    protected function getSectionsJson(): string
    {
        $sections = json_encode([
            'mfsecurity_disposable_email_address'
        ]);
        return $sections;
    }

    /**
     * Get text
     *
     * @return string
     */
    protected function getText(): string
    {
        return (string)__("This option is available in <strong>%1</strong> plan only.", $this->getMinPlan());
    }
}
