<?php

namespace App\Filament\Resources\Transactions;

use App\Filament\Resources\Transactions\Pages\ManageTransactions;
use App\Models\Transaction;
use App\Models\Reversal;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Banknotes;

    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $modelLabel = 'Transação';
    protected static ?string $pluralModelLabel = 'Transações';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Saldo atual')
                    ->description(function(){
                        $amount = number_format(auth()->user()->current_balance,2,",",".");

                        return "R$ {$amount}";
                    })
                    ->schema([
                    Select::make('type')
                        ->label('Tipo da operação')
                        ->required()
                        ->reactive()
                        ->options(function(){
                            $list = [];
                            $list['deposit'] = 'Depósito';
                            if(auth()->user()->current_balance > 0) {
                                $list['transfer'] = 'Transferência';
                            }
                            //$list['reversal'] = 'Reversão';

                            return $list;
                        }),
                    TextInput::make('amount')
                        ->label('Valor')
                        ->required()
                        ->extraFieldWrapperAttributes([
                                // x-data vazio apenas para garantir Alpine ativo no wrapper
                                'x-data' => '{}',
                                'x-init' => <<<'JS'
                                    (function () {
                                        const wrapper = $el;
                                        if (wrapper.__reverseMoneyInit) return;
                                        wrapper.__reverseMoneyInit = true;

                                        const formatFromDigits = function (digits) {
                                            if (!digits) digits = '0';
                                            while (digits.length < 3) digits = '0' + digits;
                                            const cents = digits.slice(-2);
                                            let intPart = digits.slice(0, -2).replace(/^0+/, '') || '0';
                                            intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                            return intPart + ',' + cents;
                                        };

                                        const attachToInput = function (input) {
                                            if (!input || input.__reverseMoneyAttached) return;
                                            input.__reverseMoneyAttached = true;

                                            // normalizar valor atual
                                            const initDigits = (input.value.match(/\d/g) || []).join('');
                                            if (initDigits) input.value = formatFromDigits(initDigits);

                                            input.addEventListener('paste', function (ev) {
                                                ev.preventDefault();
                                                const text = (ev.clipboardData || window.clipboardData).getData('text') || '';
                                                const digits = (text.match(/\d/g) || []).join('');
                                                input.value = digits ? formatFromDigits(digits) : '';
                                                input.dispatchEvent(new Event('input', { bubbles: true }));
                                            });

                                            input.addEventListener('input', function () {
                                                const digits = (input.value.match(/\d/g) || []).join('');
                                                input.value = digits ? formatFromDigits(digits) : '';
                                            });

                                            // bloquear teclas não numéricas
                                            input.addEventListener('keydown', function (e) {
                                                if (e.ctrlKey || e.metaKey || e.altKey) return;
                                                const allowed = ['Backspace','ArrowLeft','ArrowRight','Delete','Tab','Home','End'];
                                                if (allowed.includes(e.key)) return;
                                                if (!/^\d$/.test(e.key)) e.preventDefault();
                                            });
                                        };

                                        attachToInput(wrapper.querySelector('input, textarea'));

                                        const mo = new MutationObserver(function () {
                                            attachToInput(wrapper.querySelector('input, textarea'));
                                        });
                                        mo.observe(wrapper, { childList: true, subtree: true });

                                        const observerCleanup = new MutationObserver(function (records) {
                                            for (const r of records) {
                                                for (const node of r.removedNodes) {
                                                    if (node === wrapper) {
                                                        mo.disconnect();
                                                        observerCleanup.disconnect();
                                                    }
                                                }
                                            }
                                        });
                                        (document.body || document).querySelectorAll && observerCleanup.observe(document.body, { childList: true, subtree: true });
                                    })();
                                JS
                        ])
                        ->dehydrateStateUsing(function ($state) {
                            if ($state === null || $state === '') {
                                return null;
                            }
                            
                            return str_replace(',', '.', str_replace('.', '', $state));
                        }),        
                    Select::make('user_to_id')
                        ->label('Destino')
                        ->required()
                        ->reactive()
                        ->disabled(function(callable $get) {
                            $disabled = true;
                            if(!empty($get('type'))) {
                                $disabled = false;
                            }

                            return $disabled;
                        })
                        ->options(
                            function(callable $get) {
                                $users = User::select('id', 'name')
                                                ->withoutRole('Gerente')
                                                ->when($get('type') == 'transfer', function($query){
                                                    $query->whereNotIn('id', [auth()->user()->id]);
                                                })
                                                ->orderBy('name')
                                                ->get();

                                return $users->pluck('name', 'id');
                            }
                        ),
                ])->columns(2)
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Transações')
            ->columns([
                TextColumn::make('amount')
                    ->label('Valor')
                    ->money('BRL'),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(function(string $state) {
                        $type = 'Depósito';
                        if($state == 'transfer') {
                            $type = 'Transferência';
                        }
                        if($state == 'reversal') {
                            $type = 'Reversão';
                        }

                        return $type;
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'deposit' => 'gray',
                        'transfer' => 'info',
                        'reversal' => 'danger',
                    }),
                TextColumn::make('description')
                    ->label('Descrição')
                    ->getStateUsing(function (Model $record): string {
                        $type = 'Depósito';
                        if($record->type == 'transfer') {
                            $type = 'Transferência';
                        }
                        $user_from = $record->user_from()->first();
                        $user_to = $record->user_to()->first();
                        

                        return "{$type} de {$user_from->name} para {$user_to->name}";
                    }),
                TextColumn::make('created_at')
                    ->label('Data')
                    ->date('d/m/Y H:i'),
            ])
            ->defaultSort('created_at', direction: 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                //EditAction::make(),
                //DeleteAction::make(),
                Action::make('reversal')
                    ->label('Reverter')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(function($record){
                        $is_visible = true;
                        if(auth()->user()->hasRole('Cliente')) {
                            $is_visible = false;
                        }
                        if($record->status == 'reversed') {
                            $is_visible = false;
                        }
                        if($record->user_from_id == $record->user_to_id) {
                            $is_visible = false;
                        }

                        return $is_visible;
                    })
                    //->requiresConfirmation()
                    ->schema([
                        Textarea::make('reason')
                            ->label('Motivo')
                            ->placeholder('Descreva o motivo da reversão...')
                            ->required(),
                    ])
                    ->action(function($record, array $data): void {
                        $reason = $data['reason'];
                        
                        $reversal = new Reversal;
                        $reversal->transaction_id = $record->id;
                        $reversal->reason = $reason;
                        $reversal->save();

                        $record->update([
                            'status' => 'reversed'
                        ]);

                        // nova transação
                        $transaction = new Transaction;
                        $transaction->user_from_id = $record->user_to_id;
                        $transaction->user_to_id = $record->user_from_id;
                        $transaction->type = 'reversal';
                        $transaction->amount = $record->amount;
                        $transaction->reference_id = $record->id;
                        $transaction->save();
                        
                        // incrementa saldo para quem enviou
                        $current_balance = $record->user_from()->first()->current_balance + $record->amount;
                        $record->user_from()->update(['current_balance' => $current_balance]);

                        // decrementa saldo para quem recebeu
                        $current_balance = $record->user_to()->first()->current_balance - $record->amount;
                        $record->user_to()->update(['current_balance' => $current_balance]);

                        \Filament\Notifications\Notification::make()
                            ->title('Transação revertida com sucesso!')
                            ->body('Motivo: ' . $reason)
                            ->success()
                            ->color('success')
                            ->send();
                    })
            ])
            ->toolbarActions([
                /*BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),*/
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        if(auth()->user()->hasRole('Cliente')) {
            $query->where('user_from_id', auth()->user()->id)
                    ->orWhere('user_to_id', auth()->user()->id);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTransactions::route('/'),
        ];
    }
}
