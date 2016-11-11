<?php

use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\Remote\RemoteWebDriver;

class DembeloTest extends PHPUnit_Framework_TestCase {
    /**
     * @var RemoteWebDriver
     */
    protected $webDriver;

    public function setUp()
    {
        $capabilities = array(
            WebDriverCapabilityType::BROWSER_NAME => 'phantomjs',
            'phantomjs.page.settings.userAgent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:25.0) Gecko/20100101 Firefox/25.0'
        );
        //$this->webDriver = RemoteWebDriver::create('127.0.0.1:8910', $capabilities);
        $this->webDriver = RemoteWebDriver::create('127.0.0.1:8910', $capabilities);
    }

    protected $url = 'http://localhost:8000/app.php';

    public function testDembeloHome()
    {
        $this->webDriver->get($this->url);
        $this->assertEquals('was zu lesen', $this->webDriver->getTitle());
    }
}
