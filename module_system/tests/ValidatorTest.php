<?php

namespace Kajona\System\Tests;
require_once __DIR__."../../../core/module_system/system/Testbase.php";

use Kajona\System\System\Testbase;
use Kajona\System\System\Validators\IntValidator;
use Kajona\System\System\Validators\PosintValidator;

class ValidatorTest extends Testbase  {



    public function testIntValidator() {
        $objValidator = new IntValidator();
        $this->assertEquals($objValidator->validate("-1"), true);
        $this->assertEquals($objValidator->validate("0"), true);
        $this->assertEquals($objValidator->validate("1"), true);
        $this->assertEquals($objValidator->validate("1.1"), false);
        $this->assertEquals($objValidator->validate("-1.1"), false);
        $this->assertEquals($objValidator->validate(""), false);
        $this->assertEquals($objValidator->validate("abc"), false);
        $this->assertEquals($objValidator->validate("-abc"), false);
        $this->assertEquals($objValidator->validate("-1abc"), false);
        $this->assertEquals($objValidator->validate("1-abc"), false);
    }

    public function testPosIntValidator() {
        $objValidator = new PosintValidator();

        $this->assertEquals($objValidator->validate("-1"), false);
        $this->assertEquals($objValidator->validate("0"), true);
        $this->assertEquals($objValidator->validate("1"), true);
        $this->assertEquals($objValidator->validate("1.1"), false);
        $this->assertEquals($objValidator->validate("-1.1"), false);
        $this->assertEquals($objValidator->validate(""), false);
        $this->assertEquals($objValidator->validate("abc"), false);
        $this->assertEquals($objValidator->validate("-abc"), false);
        $this->assertEquals($objValidator->validate("-1abc"), false);
        $this->assertEquals($objValidator->validate("1-abc"), false);
    }




}

