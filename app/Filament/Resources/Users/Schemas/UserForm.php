<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make([
                    Grid::make(1)
                        ->schema([
                            Select::make('role')
                                ->label('Grupo')
                                ->required()
                                ->options(function(){
                                    return Role::orderBy('name')
                                            ->get()
                                            ->pluck('name', 'name');
                                })
                        ])->columns(2),
                    Grid::make(1)
                    ->schema([
                        TextInput::make('name')
                        ->label('Nome')
                        ->required(),
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required(),
                        TextInput::make('password')
                            ->label('Senha')
                            ->same('passwordConfirmation')
                            ->password()
                            ->maxLength(255)
                            ->required(fn($record) => $record === null)
                            ->dehydrated(fn ($state): bool => filled($state)),
                        TextInput::make('passwordConfirmation')
                            ->label('Confirmação de senha')
                            ->password()
                            ->maxLength(255)
                            ->required(fn($record) => $record === null)
                    ])->columns(2),
                ])
            ])->columns(1);
    }
}