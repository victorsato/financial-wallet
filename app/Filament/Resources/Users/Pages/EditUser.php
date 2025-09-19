<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use App\Models\User;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterFill(): void
    {
        $role = $this->record->getRoleNames()->first();
        $this->data['role'] = $role;
    }

    protected function afterSave(): void
    {
        // atribuir grupo para novo usuário criado
        $user = User::find($this->record->id);
        $user->assignRole($this->data['role']);
    }
}
