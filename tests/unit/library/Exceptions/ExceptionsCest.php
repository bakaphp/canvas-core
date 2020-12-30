<?php

namespace Canvas\Tests\unit\library\Dto;

use Canvas\Exception\HttpException;
use Baka\Http\Exception\BadRequestException;
use Baka\Http\Exception\ForbiddenException;
use Baka\Http\Exception\InternalServerErrorException;
use Baka\Http\Exception\NotFoundException;
use Baka\Http\Exception\UnauthorizedException;
use Baka\Http\Exception\UnprocessableEntityException;
use UnitTester;

class ExceptionsCest
{
    public function checkCanvasDefault(UnitTester $I)
    {
        $I->assertTrue(is_object(new BadRequestException()));
        $I->assertTrue(is_object(new ForbiddenException()));
        $I->assertTrue(is_object(new InternalServerErrorException()));
        $I->assertTrue(is_object(new NotFoundException()));
        $I->assertTrue(is_object(new UnauthorizedException()));
        $I->assertTrue(is_object(new UnprocessableEntityException()));
        $I->assertTrue(is_object(new HttpException()));
    }

    public function checkExecptionStatusMessage(UnitTester $I)
    {
        $httpException = new HttpException();
        $I->assertTrue(is_integer($httpException->getHttpCode()));
        $I->assertTrue(is_string($httpException->getHttpMessage()));
        $I->assertTrue(is_array($httpException->getData()));
    }
}
