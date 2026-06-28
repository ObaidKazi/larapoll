<?php

namespace App\Filament\Resources\Polls\Schemas;

use Filament\Schemas\Schema;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;



class PollForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Poll Configuration')
                    ->description('Define the core question and timeline for your poll.')
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->schema([
                        TextInput::make('question')
                            ->label('Question')
                            ->placeholder('e.g., What is your favorite backend framework?')
                            ->required()
                            ->columnSpanFull()
                            ->maxLength(255),

                        Grid::make(2)->schema([
                            DateTimePicker::make('ends_at')
                                ->label('Ends At')
                                ->helperText('Leave empty if the poll never expires.'),

                            Toggle::make('is_active')
                                ->label('Poll Status')
                                ->default(true)
                                ->inline(false)
                                ->helperText('Toggle off to manually close voting.'),
                        ]),
                    ]),

                Section::make('Poll Options')
                    ->description('Add the choices voters can select from.')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        Repeater::make('options')
                            ->relationship()
                            ->orderColumn('sort_order') 
                            ->hiddenLabel()
                            ->addActionLabel('Add New Option')
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(fn(array $state): ?string => $state['label'] ?? 'New Option')
                            ->schema([
                                
                                TextInput::make('label')
                                    ->label('Option Text')
                                    ->placeholder('e.g., Laravel')
                                    ->required(),
                            ])
                    ]),
            ]);
    }
}
