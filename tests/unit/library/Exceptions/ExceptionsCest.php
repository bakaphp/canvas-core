<?php

namespace Canvas\Tests\unit\library\Dto;

use Canvas\Exception\HttpException;
use Canvas\Http\Exception\BadRequestException;
use Canvas\Http\Exception\ForbiddenException;
use Canvas\Http\Exception\InternalServerErrorException;
use Canvas\Http\Exception\NotFoundException;
use Canvas\Http\Exception\UnauthorizedException;
use Canvas\Http\Exception\UnprocessableEntityException;
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
