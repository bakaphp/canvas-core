<?php

namespace Canvas\Contracts\Auth;


interface SocialInterface {

    public function getInfo(string $token): array;

}