<?php

namespace Canvas\Contracts\Auth;


interface SocialProviderInterface {

    public function getInfo(string $token): array;

}