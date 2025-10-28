<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Block\Adminhtml\System\Config\Form;

class InfoPlus extends InfoPlan
{

    /**
     * Get min plan
     *
     * @return string
     */
    protected function getMinPlan(): string
    {
        return 'Plus';
    }

    /**
     * Get sections json
     *
     * @return string
     */
    protected function getSectionsJson(): string
    {
        $sections = json_encode([

        ]);
        return $sections;
    }

    /**
     *  Get text
     *
     * @return string
     */
    protected function getText(): string
    {
        return (string)__("This option is available in <strong>Plus or Extra</strong> plans only.");
    }
}
