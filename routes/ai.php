<?php

use App\Mcp\Servers\LarvisServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::local('larvis', LarvisServer::class);
