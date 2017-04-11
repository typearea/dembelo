<?php

use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class DembeloTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var RemoteWebDriver
     */
    protected $webDriver;

    protected $url = 'http://localhost:8000/app_selenium.php';

    public function setUp()
    {
        parent::setUp();

        $capabilities = array(
            WebDriverCapabilityType::BROWSER_NAME => 'phantomjs',
            'phantomjs.page.settings.userAgent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:25.0) Gecko/20100101 Firefox/25.0'
        );
        $this->webDriver = RemoteWebDriver::create('127.0.0.1:8910', $capabilities);

        $this->webDriver->manage()->window()->maximize();

        $this->webDriver->manage()->deleteAllCookies();
        shell_exec('php bin/console doctrine:mongodb:fixtures:load -n');
    }

    public function testDembeloHome()
    {
        $this->webDriver->get($this->url);
        $this->assertEquals('was zu lesen', $this->webDriver->getTitle());
    }

    public function testDembeloLogin()
    {
        //$this->webDriver->manage()->window()->maximize();

        $this->loginAsAdmin();

    }

    protected function loginAsAdmin()
    {
        $this->webDriver->get($this->url);

        $menuSettings = $this->webDriver->findElement(
            WebDriverBy::className('menu-icon')
        );
        $menuSettings->click();

        $menuLogin = $this->webDriver->findElement(
            WebDriverBy::className('ion-person')
        );
        $menuLogin->click();

        $this->webDriver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(
                WebDriverBy::id('form__username')
            )
        );

        $formEmail = $this->webDriver->findElement(
            WebDriverBy::id('form__username')
        );
        $formEmail->sendKeys('admin@dembelo.tld');

        $formPassword = $this->webDriver->findElement(
            WebDriverBy::id('form_password')
        );
        $formPassword->sendKeys('dembelo')->submit();

        $this->webDriver->wait(20)->until(
            WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(
                WebDriverBy::className('ion-android-exit')
            )
        );

    }
}
