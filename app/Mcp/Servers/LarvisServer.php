<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\TasksCompleteTool;
use App\Mcp\Tools\TasksCreateTool;
use App\Mcp\Tools\TasksListTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('larvis')]
#[Version('0.0.1')]
#[Instructions('Manage the configured Larvis owner\'s personal tasks. Use only the available task tools; task ownership is enforced by Laravel.')]
class LarvisServer extends Server
{
    protected array $tools = [
        TasksListTool::class,
        TasksCreateTool::class,
        TasksCompleteTool::class,
    ];
}
