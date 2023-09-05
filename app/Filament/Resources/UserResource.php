<?php

namespace App\Filament\Resources;

use App\Components\PasswordGenerator;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserResource extends Resource
{
    protected static ?string $model = User::class;


    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;


    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('photo')->avatar()->disk('public')->directory('user/photo'),
                Grid::make()->schema([
                    TextInput::make('name'),
                    TextInput::make('email')->email(),
                    TextInput::make('telp')->tel(),
                ])->columns(3),
                Grid::make()->schema([
                    Forms\Components\Toggle::make('reset_password')
                        ->columnSpan('full')
                        ->reactive()
                        ->dehydrated(false)
                        ->hiddenOn('create'),
                    TextInput::make('password')
                        ->columnSpan('full')
                        ->visible(fn($livewire, $get) => $livewire instanceof CreateUser || $get('reset_password') == true)
                        ->rules(config('filament-breezy.password_rules', 'max:25'))
                        ->required()
                        // ->helperText('maximum 8 characters')
                        ->dehydrateStateUsing(function ($state) {
                            return Hash::make($state);
                        }),
                    Textarea::make('address'),
                    Select::make('role_id')->multiple()
                        ->relationship('roles', 'name'),
                ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    ImageColumn::make('photo')
                        ->circular()
                        ->grow(false),
                    TextColumn::make('name')
                        ->weight(FontWeight::Bold)
                        ->searchable()
                        ->sortable(),
                    Stack::make([
                        TextColumn::make('telp')
                            ->icon('heroicon-m-phone'),
                        TextColumn::make('email')
                            ->icon('heroicon-m-envelope'),
                    ])->visibleFrom('md'),
                ])
            ])
            ->filters([
                //
            ])
            ->actions([
                Impersonate::make(),
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->label('More actions')
                    ->dropdownWidth('xs')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size(ActionSize::Small)
                    ->color('primary')
                    // ->button()

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Split::make([
                    Components\Grid::make(1)
                        ->schema([
                            Components\Group::make([
                                Components\TextEntry::make('name'),
                                Components\TextEntry::make('email'),
                                Components\TextEntry::make('telp'),

                                Components\TextEntry::make('roles.name')
                                    ->badge()
                                    ->color('success'),
                                Components\TextEntry::make('address'),
                                Components\TextEntry::make('created_at')
                                    ->badge()
                                    ->date()
                                    ->color('success'),
                            ]),
                            Components\Group::make([
                                // Components\TextEntry::make('address'),
                                // Components\TextEntry::make('created_at')
                                //     ->badge()
                                //     ->date()
                                //     ->color('success'),
                            ]),
                        ]),
                    Components\ImageEntry::make('photo')
                        ->hiddenLabel()
                        ->grow(false),
                ])->from('lg'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}