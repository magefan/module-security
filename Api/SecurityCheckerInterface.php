<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Api;

interface SecurityCheckerInterface
{

    const CRITICAL = 1;
    const NOTICE = 2;
    const CANT_CHECK = 3;
    const OK = 4;

    const XML_ADMIN_SECURITY_PASSWORD_IS_FORCED = 'admin/security/password_is_forced';
    const XML_PATH_ADMIN_ACCOUNT_SHARING = 'admin/security/admin_account_sharing';
    const XML_PATH_USE_SECURE_KEY = 'admin/security/use_form_key';
    const XML_PATH_RECAPTCHA_ADMIN_LOGIN = 'recaptcha_backend/type_for/user_login';
    const XML_PATH_RECAPTCHA_RESET_PASSWORD = 'recaptcha_backend/type_for/user_forgot_password';

    /**
     * @return mixed
     */
    public function loadCache();

    /**
     * @return mixed
     */
    public function updateCache();

    /**
     * @return mixed
     */
    public function issueExists();

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return int
     */
    public function getType(): int;

    /**
     * @return int
     */
    public function getPosition(): int;

    /**
     * @return int
     */
    public function getTotal(): int;

    /**
     * @return array
     */
    public function getDetails(): array;

    /**
     * @return string
     */
    public function getSuggestions(): string;
}
