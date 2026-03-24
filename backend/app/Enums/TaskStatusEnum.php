<?php

namespace App\Enums;

enum TaskStatusEnum: string
{
    case Todo       = 'todo';
    case InProgress = 'in_progress';
    case Completed  = 'completed';
    case Archived   = 'archived';
}
