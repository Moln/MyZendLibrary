<?php

namespace My\Service;

interface ProtocolInterface
{
    public function __construct($serviceName);
    public function handle();
}