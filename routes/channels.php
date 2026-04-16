<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('project.{projectId}', function ($user, $projectId) {
    return true;
});

Broadcast::channel('dashboard', function () {
    return true;
});