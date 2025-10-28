<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Api;

interface SecurityCheckerInterface
{

    public const CRITICAL = 1;
    public const NOTICE = 2;
    public const CANT_CHECK = 3;
    public const OK = 4;

    public const XML_ADMIN_SECURITY_PASSWORD_IS_FORCED = 'admin/security/password_is_forced';
    public const XML_PATH_ADMIN_ACCOUNT_SHARING = 'admin/security/admin_account_sharing';
    public const XML_PATH_USE_SECURE_KEY = 'admin/security/use_form_key';
    public const XML_PATH_RECAPTCHA_ADMIN_LOGIN = 'recaptcha_backend/type_for/user_login';
    public const XML_PATH_RECAPTCHA_RESET_PASSWORD = 'recaptcha_backend/type_for/user_forgot_password';

    /**
     * Load cache
     *
     * @return mixed
     */
    public function loadCache();

    /**
     * Update cache
     *
     * @return mixed
     */
    public function updateCache();

    /**
     * Check if issue exist
     *
     * @return mixed
     */
    public function issueExists();

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get type
     *
     * @return int
     */
    public function getType(): int;

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition(): int;

    /**
     * Get total
     *
     * @return int
     */
    public function getTotal(): int;

    /**
     * Get details
     *
     * @return array
     */
    public function getDetails(): array;

    /**
     * Get suggestions
     *
     * @return string
     */
    public function getSuggestions(): string;
}
