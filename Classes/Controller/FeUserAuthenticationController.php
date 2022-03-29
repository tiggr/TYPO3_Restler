<?php
namespace Aoe\Restler\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2021 AOE GmbH <dev@aoe.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Aoe\Restler\System\TYPO3\Loader as TYPO3Loader;
use Luracast\Restler\iAuthenticate;
use Luracast\Restler\Restler;
use Luracast\Restler\Scope;

/**
 * This class checks, if client is allowed to access the requested and protected API-class
 * This class checks, if FE-user is logged in
 */
class FeUserAuthenticationController implements iAuthenticate
{
    /**
     * This property defines (when it's set), the argument-name, which contains the pageId,
     * which should be used to initialize TYPO3
     * This property will be automatically set by restler, when in
     * the API-class/controller this is configured (in PHPdoc/annotations)
     *
     * Where do we set this property?
     * When the property should be used, than it must be set inside the PHPdoc-comment of
     * the API-class-method, which handle the API-request
     *
     * Syntax:
     * The PHPdoc-comment must look like this:
     * @class [className] {@[propertyName] [propertyValue]}
     *
     * Example:
     * When this controller should use a specific pageId while initializing TYPO3 (this is needed, when we want to
     * render TYPO3-contentElements, after the user is authenticated), than the PHPdoc-comment must look like this:
     * @class Aoe\Restler\Controller\FeUserAuthenticationController {@argumentNameOfPageId pageId}
     *
     * @see \Aoe\RestlerExamples\Controller\ContentController::getContentElementByUidForLoggedInFeUser
     *
     * @var string
     */
    public $argumentNameOfPageId = '';
    /**
     * This property defines (when it's set), that this controller should check authentication
     * This property will be automatically set by restler, when in the API-class/controller this
     * is configured (in PHPdoc/annotations)
     *
     * Where do we set this property?
     * When the property should be used, than it must be set inside the PHPdoc-comment of the API-class-method,
     * which handle the API-request
     *
     * Syntax:
     * The PHPdoc-comment must look like this:
     * @class [className] {@[propertyName] [propertyValue]}
     *
     * Example:
     * When this controller should be used for authentication-checks, than the PHPdoc-comment must look like this:
     * @class Aoe\Restler\Controller\FeUserAuthenticationController {@checkAuthentication true}
     *
     * @see \Aoe\RestlerExamples\Controller\FeUserController::getDataOfLoggedInFeUser
     * @see \Aoe\RestlerExamples\Controller\ContentController::getContentElementByUidForLoggedInFeUser
     *
     * @var boolean
     */
    public $checkAuthentication = false;
    /**
     * @var TYPO3Loader
     */
    private $typo3Loader;

    /**
     * Instance of Restler class injected at runtime.
     *
     * @var Restler
     */
    public $restler;

    /**
     * @param TYPO3Loader $typo3Loader
     */
    public function __construct(TYPO3Loader $typo3Loader)
    {
        $this->typo3Loader = $typo3Loader;
        $this->restler = Scope::get('Restler');
    }

    /**
     * This method checks, if client is allowed to access the requested API-class
     *
     * @return boolean
     */
    public function __isAllowed()
    {
        if ($this->checkAuthentication !== true) {
            // this controller is not responsible for the authentication
            return false;
        }

        $this->typo3Loader->initializeFrontendUser($this->determinePageId());

        return $this->typo3Loader->hasActiveFrontendUser();
    }

    /**
     * return dummy string, because we DON'T need that for our case (we use NO base-authentification via REST-API)
     *
     * @return string
     * @see \Luracast\Restler\iAuthenticate
     */
    public function __getWWWAuthenticateString()
    {
        return '';
    }

    /**
     * List of page IDs (comma separated) or page ID where to look for frontend user records
     *
     * @return string
     */
    private function determinePageId()
    {
        if (false === empty($this->argumentNameOfPageId)) {
            return $this->argumentNameOfPageId;
        }

        return $this->determinePageIdFromArguments();
    }

    /**
     * determine pageId from arguments, which restler has detected
     * We need the pageId, when we want to render TYPO3-contentElements, after the user is authenticated
     *
     * @return string
     */
    private function determinePageIdFromArguments()
    {
        if (false === array_key_exists($this->argumentNameOfPageId, $this->restler->apiMethodInfo->arguments)) {
            return '0';
        }

        $index = $this->restler->apiMethodInfo->arguments[$this->argumentNameOfPageId];
        return $this->restler->apiMethodInfo->parameters[$index];
    }
}
