<?php

class LoginTest extends \Codeception\Test\Unit
{
    /**
     * @var \FunctionalTester
     */
    protected $tester;

    public function testSuccessfulLogin()
    {
        $I = $this->tester;
        $I->amOnPage('/login/login.php');
        $I->fillField('username', 'doctor1');
        $I->fillField('password', 'password123');
        $I->click('Login');
        $I->seeInCurrentUrl('/My Clinic/Dashboard/doctor_dashboard.php');
    }
}
