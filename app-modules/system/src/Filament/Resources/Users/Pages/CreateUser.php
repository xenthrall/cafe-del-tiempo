<?php

namespace Tequia\System\Filament\Resources\Users\Pages;

use Filament\Resources\Pages\CreateRecord;
use Tequia\System\Filament\Resources\Users\UserResource;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
