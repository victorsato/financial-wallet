<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;

class ManageTransactions extends ManageRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalSubmitActionLabel('Enviar')
                ->createAnother(false)
                ->mutateDataUsing(function (array $data): array {
                    $data['user_from_id'] = auth()->id();
                    
                    return $data;
                })
                ->before(function (CreateAction $action, array $data) {
                    // verifica se possui saldo para transferência
                    if($data['type'] == 'transfer' && auth()->user()->current_balance < $data['amount']) {
                        Notification::make()
                            ->danger()
                            ->color('danger')
                            ->title('Transferência não realizada. Saldo insuficiente.')
                            ->body('')
                            ->send();

                        $action->halt();
                    }
                })
                ->after(function (Model $record) {
                    if($record->type == 'deposit') {
                        $current_balance = $record->user_to()->first()->current_balance + $record->amount;
                        $record->user_to()->update(['current_balance' => $current_balance]);
                    } elseif($record->type == 'transfer') {
                        $current_balance = auth()->user()->current_balance - $record->amount;
                        auth()->user()->update(['current_balance' => $current_balance]);

                        $current_balance = $record->user_to()->first()->current_balance + $record->amount;
                        $record->user_to()->update(['current_balance' => $current_balance]);
                    }
                })
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->color('success')
                        ->title('Transação realizada com sucesso!'),
                )
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
