<?php

namespace App\Support;

use App\Models\User;

class LarvisMcpOwner
{
    /**
     * Resolve the configured local MCP owner.
     */
    public function resolve(): ?User
    {
        return User::query()
            ->where('email', config('larvis.mcp_owner_email'))
            ->first();
    }
}
