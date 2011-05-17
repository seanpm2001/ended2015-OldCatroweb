<?php
/*    Catroid: An on-device graphical programming language for Android devices
 *    Copyright (C) 2010-2011 The Catroid Team
 *    (<http://code.google.com/p/catroid/wiki/Credits>)
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/* Set TESTS_BASE_PATH in testsBootstrap.php to your catroid www-root */
require_once 'testsBootstrap.php';

class RegistrationTests extends PHPUnit_Framework_TestCase
{
  private $selenium;

  public function ajaxWait()
  {
    for($second = 0; $second <= 600; $second++) {
      if($second >= 6) break;
      try {
        if($this->selenium->isElementPresent("xpath=//input[@id='ajax-loader'][@value='off']")) {
          break;
        }
      } catch (Exception $e) {}
      sleep(1);
    }
  }

  public function setUp()
  {
    $path= 'http://'.str_replace('http://', '', TESTS_BASE_PATH).'catroid/';
    $this->selenium = new Testing_Selenium(TESTS_BROWSER, $path);
    if (TESTS_SLOW_MODE==TRUE) {
      $this->selenium->setSpeed(TESTS_SLOW_MODE_SPEED);
    } else {
      $this->selenium->setSpeed(1);
    }
    $this->selenium->start();
  }

  public function tearDown()
  {
    $this->selenium->stop();
  }

  /**
   * @dataProvider registrationData
   */
  public function testLogin($regData)
  {
    //log out if necessary
    $this->selenium->open(TESTS_BASE_PATH.'catroid/login/');
    $this->selenium->waitForPageToLoad(10000);

    //wiki username creation
    $wikiUsername = ucfirst(strtolower($regData['registrationUsername']));

    $this->selenium->open(TESTS_BASE_PATH.'catroid/registration/');
    $this->selenium->waitForPageToLoad(10000);
    $this->assertTrue($this->selenium->isElementPresent("xpath=//input[@name='registrationUsername']"));
    $this->assertTrue($this->selenium->isElementPresent("xpath=//input[@name='registrationPassword']"));
    $this->assertTrue($this->selenium->isElementPresent("xpath=//input[@name='registrationEmail']"));
    $this->assertTrue($this->selenium->isElementPresent("xpath=//select[@name='registrationGender']"));
    $this->assertTrue($this->selenium->isElementPresent("xpath=//select[@name='registrationMonth']"));
    $this->assertTrue($this->selenium->isElementPresent("xpath=//select[@name='registrationYear']"));
    $this->assertTrue($this->selenium->isElementPresent("xpath=//select[@name='registrationCountry']"));
    $this->assertTrue($this->selenium->isElementPresent("xpath=//input[@name='registrationCity']"));
    $this->assertTrue($this->selenium->isElementPresent("xpath=//input[@name='registrationSubmit']"));

    $this->selenium->type("xpath=//input[@name='registrationUsername']", $regData['registrationUsername']);
    $this->selenium->type("xpath=//input[@name='registrationPassword']", $regData['registrationPassword']);
    $this->selenium->type("xpath=//input[@name='registrationEmail']", $regData['registrationEmail']);
    $this->selenium->type("xpath=//select[@name='registrationGender']", $regData['registrationGender']);
    $this->selenium->type("xpath=//select[@name='registrationMonth']", $regData['registrationMonth']);
    $this->selenium->type("xpath=//select[@name='registrationYear']", $regData['registrationYear']);
    $this->selenium->type("xpath=//select[@name='registrationCountry']", $regData['registrationCountry']);
    $this->selenium->type("xpath=//input[@name='registrationCity']", $regData['registrationCity']);

    $this->selenium->click("xpath=//input[@name='registrationSubmit']");
    $this->selenium->waitForCondition('', 3000);
    $this->assertTrue($this->selenium->isTextPresent("CATROID registration successfull!"));
    $this->assertTrue($this->selenium->isTextPresent("BOARD registration successfull!"));
    $this->assertTrue($this->selenium->isTextPresent("WIKI registration successfull!"));

    $this->selenium->open(TESTS_BASE_PATH.'catroid/login/');
    $this->selenium->waitForPageToLoad(10000);

    $this->selenium->type("xpath=//input[@name='loginUsername']", $regData['registrationUsername']);
    $this->selenium->type("xpath=//input[@name='loginPassword']", $regData['registrationPassword']);

    $this->selenium->click("xpath=//input[@name='loginSubmit']");
    $this->selenium->waitForPageToLoad(10000);
    $this->ajaxWait();
    $this->assertTrue($this->selenium->isTextPresent("Newest Projects"));

    $this->selenium->click("headerMenuButton");
    $this->selenium->waitForPageToLoad(10000);

    $this->assertTrue($this->selenium->isVisible("xpath=//button[@id='menuLogoutButton']"));

    $this->selenium->click("menuForumButton");
    $this->selenium->selectWindow("board");
    $this->selenium->waitForPageToLoad(10000);
    $this->assertFalse($this->selenium->isTextPresent("Login"));
    $this->assertTrue($this->selenium->isTextPresent("Logout"));
    $this->selenium->close();
    $this->selenium->selectWindow(null);

    $this->selenium->click("menuWikiButton");
    $this->selenium->selectWindow("wiki");
    $this->selenium->waitForPageToLoad(10000);
    $this->assertTrue($this->selenium->isTextPresent($wikiUsername));
    $this->selenium->click("xpath=//li[@id='pt-preferences']/a");
    $this->selenium->waitForPageToLoad(10000);
    $this->assertEquals('Preferences', $this->selenium->getText("firstHeading"));
    $this->assertFalse($this->selenium->isTextPresent("Not logged in"));
    $this->selenium->close();
    $this->selenium->selectWindow(null);

    $this->selenium->click("menuLogoutButton");
    $this->ajaxWait();
    $this->assertTrue($this->selenium->isTextPresent("Newest Projects"));

    $this->selenium->open(TESTS_BASE_PATH.'catroid/login/');
    $this->selenium->waitForPageToLoad(10000);
    $this->assertTrue($this->selenium->isElementPresent("xpath=//input[@name='loginUsername']"));
    $this->assertTrue($this->selenium->isElementPresent("xpath=//input[@name='loginPassword']"));
    $this->assertTrue($this->selenium->isElementPresent("xpath=//input[@name='loginSubmit']"));

    $this->selenium->click("headerMenuButton");
    $this->selenium->waitForPageToLoad(10000);

    $this->assertTrue($this->selenium->isVisible("menuLoginButton"));

    $this->selenium->click("menuForumButton");
    $this->selenium->selectWindow("board");
    $this->selenium->waitForPageToLoad(10000);
    $this->assertTrue($this->selenium->isTextPresent("Login"));
    $this->assertFalse($this->selenium->isTextPresent("Logout"));
    $this->selenium->close();
    $this->selenium->selectWindow(null);

    $this->selenium->click("menuWikiButton");
    $this->selenium->selectWindow("wiki");
    $this->selenium->waitForPageToLoad(10000);
    $this->assertFalse($this->selenium->isTextPresent($wikiUsername));
    $this->selenium->close();
    $this->selenium->selectWindow(null);
  }

  /**
   * @dataProvider invalidRegistrationData
   */
  public function testInvalidLogin($regData)
  {
    //log out if necessary
    $this->selenium->open(TESTS_BASE_PATH.'catroid/login/');
    $this->selenium->waitForPageToLoad(10000);

    //wiki username creation
    $wikiUsername = ucfirst(strtolower($regData['registrationUsername']));

    $this->selenium->open(TESTS_BASE_PATH.'catroid/registration/');
    $this->selenium->waitForPageToLoad(10000);
    $this->assertTrue($this->selenium->isElementPresent("xpath=//input[@name='registrationUsername']"));
    $this->assertTrue($this->selenium->isElementPresent("xpath=//input[@name='registrationPassword']"));
    $this->assertTrue($this->selenium->isElementPresent("xpath=//input[@name='registrationEmail']"));
    $this->assertTrue($this->selenium->isElementPresent("xpath=//select[@name='registrationGender']"));
    $this->assertTrue($this->selenium->isElementPresent("xpath=//select[@name='registrationMonth']"));
    $this->assertTrue($this->selenium->isElementPresent("xpath=//select[@name='registrationYear']"));
    $this->assertTrue($this->selenium->isElementPresent("xpath=//select[@name='registrationCountry']"));
    $this->assertTrue($this->selenium->isElementPresent("xpath=//input[@name='registrationCity']"));
    $this->assertTrue($this->selenium->isElementPresent("xpath=//input[@name='registrationSubmit']"));

    $this->selenium->type("xpath=//input[@name='registrationUsername']", $regData['registrationUsername']);
    $this->selenium->type("xpath=//input[@name='registrationPassword']", $regData['registrationPassword']);
    $this->selenium->type("xpath=//input[@name='registrationEmail']", $regData['registrationEmail']);
    $this->selenium->type("xpath=//select[@name='registrationGender']", $regData['registrationGender']);
    $this->selenium->type("xpath=//select[@name='registrationMonth']", $regData['registrationMonth']);
    $this->selenium->type("xpath=//select[@name='registrationYear']", $regData['registrationYear']);
    $this->selenium->type("xpath=//select[@name='registrationCountry']", $regData['registrationCountry']);
    $this->selenium->type("xpath=//input[@name='registrationCity']", $regData['registrationCity']);

    $this->selenium->click("xpath=//input[@name='registrationSubmit']");
    $this->selenium->waitForCondition('', 3000);
    $this->assertTrue($this->selenium->isTextPresent("The nickname is invalid."));
    $this->assertFalse($this->selenium->isTextPresent("CATROID registration successfull!"));
    $this->assertFalse($this->selenium->isTextPresent("BOARD registration successfull!"));
    $this->assertFalse($this->selenium->isTextPresent("WIKI registration successfull!"));

    $this->selenium->open(TESTS_BASE_PATH.'catroid/login/');
    $this->selenium->waitForPageToLoad(10000);

    $this->selenium->type("xpath=//input[@name='loginUsername']", $regData['registrationUsername']);
    $this->selenium->type("xpath=//input[@name='loginPassword']", $regData['registrationPassword']);

    $this->selenium->click("xpath=//input[@name='loginSubmit']");
    $this->selenium->waitForCondition('', 3000);
    $this->assertTrue($this->selenium->isTextPresent("The catroid authentication failed."));

    $this->selenium->click("headerMenuButton");
    $this->selenium->waitForPageToLoad(10000);

    $this->assertTrue($this->selenium->isVisible("menuLoginButton"));

    $this->selenium->click("menuForumButton");
    $this->selenium->selectWindow("board");
    $this->selenium->waitForPageToLoad(10000);
    $this->assertTrue($this->selenium->isTextPresent("Login"));
    $this->assertFalse($this->selenium->isTextPresent("Logout"));
    $this->selenium->close();
    $this->selenium->selectWindow(null);

    $this->selenium->click("menuWikiButton");
    $this->selenium->selectWindow("wiki");
    $this->selenium->waitForPageToLoad(10000);
    $this->assertFalse($this->selenium->isTextPresent($wikiUsername));
    $this->selenium->close();
    $this->selenium->selectWindow(null);
  }

  /* *** DATA PROVIDERS *** */
  public function registrationData() {
    $random = rand(100, 999999);
    $dataArray = array(
    array(
    array('registrationUsername'=>'myUnitTest'.$random, 'registrationPassword'=>'myPassword123',
    	    'registrationEmail'=>'test_'.$random.'@selenium.at',
          'registrationGender'=>'male', 'registrationMonth'=>'2', 'registrationYear'=>'1980',
    	    'registrationCountry'=>'AT', 'registrationCity'=>'Graz'))
    );
    return $dataArray;
  }

  /* *** DATA PROVIDERS *** */
  public function invalidRegistrationData() {
    $random = rand(100, 999999);
    $dataArray = array(
    array(
    array('registrationUsername'=>'my_UnitTest'.$random, 'registrationPassword'=>'myPassword123',
    	    'registrationEmail'=>'test_'.$random.'@selenium.at',
          'registrationGender'=>'male', 'registrationMonth'=>'2', 'registrationYear'=>'1981',
    	    'registrationCountry'=>'AT', 'registrationCity'=>'Graz'))
    );
    return $dataArray;
  }

}
?>

